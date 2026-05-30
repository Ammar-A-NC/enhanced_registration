<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Service;

use OCP\IConfig;

class LldapService {
    public function __construct(private IConfig $config) {}


    private function requiredGroupId(string $key, string $label): int {
        $value = trim($this->config->getAppValue('enhanced_registration', $key, ''));

        if ($value === '' || !ctype_digit($value) || (int)$value <= 0) {
            throw new \RuntimeException($label . ' ist nicht konfiguriert. Bitte in den Enhanced Registration Einstellungen setzen.');
        }

        return (int)$value;
    }

    private function getToken(): string {
        $graphql = $this->config->getAppValue('enhanced_registration', 'lldap_url');
        $base = preg_replace('#/api/graphql$#', '', $graphql);

        $user = $this->config->getAppValue('enhanced_registration', 'lldap_admin_user');
        $password = $this->config->getAppValue('enhanced_registration', 'lldap_admin_password');

        $payload = json_encode([
            'username' => $user,
            'password' => $password,
        ]);

        $ch = curl_init($base . '/auth/simple/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode((string)$response, true);

        if (!isset($json['token'])) {
            throw new \RuntimeException('LLDAP Token fehlt');
        }

        return $json['token'];
    }

    private function query(string $query, array $variables = []): array {
        $url = $this->config->getAppValue('enhanced_registration', 'lldap_url');
        $token = $this->getToken();

        $payload = json_encode([
            'query' => $query,
            'variables' => $variables,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode((string)$response, true) ?: [];

        if (isset($json['errors'])) {
            throw new \RuntimeException('LLDAP GraphQL Fehler: ' . json_encode($json['errors']));
        }

        return $json;
    }

    public function createPendingUser(string $username, string $email, string $displayName, string $password): void {
        $group = $this->requiredGroupId('lldap_pending_group_id', 'Pending-Gruppe');

        $userInput = [
            'id' => $username,
            'displayName' => $displayName,
        ];

        if ($this->config->getAppValue('enhanced_registration', 'store_user_email_in_ldap', '1') === '1') {
            $userInput['email'] = $email;
        }

        $this->query(
            'mutation CreateUser($user: CreateUserInput!) {
                createUser(user: $user) { id }
            }',
            [
                'user' => $userInput,
            ]
        );

        $this->setUserPassword($username, $password);
        $this->addUserToGroup($username, $group);
    }

    public function getPendingUsers(): array {
        $pendingGroupId = $this->requiredGroupId('lldap_pending_group_id', 'Pending-Gruppe');
        $response = $this->query(
            'query {
                groups {
                    id
                    displayName
                    users {
                        id
                        email
                        displayName
                    }
                }
            }'
        );

        foreach ($response['data']['groups'] ?? [] as $group) {
            if ((int)$group['id'] === $pendingGroupId) {
                return $group['users'] ?? [];
            }
        }

        return [];
    }

    public function addUserToGroup(string $userId, int $groupId): void {
        $this->query(
            'mutation AddUserToGroup($userId: String!, $groupId: Int!) {
                addUserToGroup(userId: $userId, groupId: $groupId) { ok }
            }',
            [
                'userId' => $userId,
                'groupId' => $groupId,
            ]
        );
    }

    public function removeUserFromGroup(string $userId, int $groupId): void {
        $this->query(
            'mutation RemoveUserFromGroup($userId: String!, $groupId: Int!) {
                removeUserFromGroup(userId: $userId, groupId: $groupId) { ok }
            }',
            [
                'userId' => $userId,
                'groupId' => $groupId,
            ]
        );
    }

    public function getGroups(): array {
        $response = $this->query(
            'query { groups { id displayName } }'
        );

        return $response["data"]["groups"] ?? [];
    }

    public function approveUser(string $userId, array $targetGroups): void {
        $pendingGroupId = $this->requiredGroupId('lldap_pending_group_id', 'Pending-Gruppe');
        $this->removeUserFromGroup($userId, $pendingGroupId);

        foreach ($targetGroups as $groupId) {
            $groupId = (int)$groupId;
            if ($groupId > 0 && $groupId !== $pendingGroupId) {
                $this->addUserToGroup($userId, $groupId);
            }
        }
    }

    public function blacklistUser(string $userId): void {
        $pendingGroupId = $this->requiredGroupId('lldap_pending_group_id', 'Pending-Gruppe');
        $blacklistGroupId = $this->requiredGroupId('lldap_blacklist_group_id', 'Blacklist-Gruppe');

        $this->removeUserFromGroup($userId, $pendingGroupId);
        $this->addUserToGroup($userId, $blacklistGroupId);
    }
    public function rejectUser(string $userId, string $action): void {
        $pendingGroupId = $this->requiredGroupId('lldap_pending_group_id', 'Pending-Gruppe');

        if ($action === 'delete_user') {
            $this->deleteUser($userId);
            return;
        }

        $this->removeUserFromGroup($userId, $pendingGroupId);

        if ($action === 'remove_pending') {
            return;
        }

        $blacklistGroupId = $this->requiredGroupId('lldap_blacklist_group_id', 'Blacklist-Gruppe');
        $this->addUserToGroup($userId, $blacklistGroupId);
    }

    public function deleteUser(string $userId): void {
        $this->query(
            'mutation DeleteUser($userId: String!) {
                deleteUser(userId: $userId) { ok }
            }',
            [
                'userId' => $userId,
            ]
        );
    }

    public function getUserById(string $userId): ?array {
        foreach ($this->getPendingUsers() as $user) {
            if (($user["id"] ?? "") === $userId) {
                return $user;
            }
        }
        return null;
    }

    public function findUserByEmail(string $email): ?array {
        $response = $this->query(
            'query {
                users {
                    id
                    email
                    displayName
                }
            }'
        );

        foreach ($response["data"]["users"] ?? [] as $user) {
            if (strtolower((string)($user["email"] ?? "")) === strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    public function setUserPassword(string $userId, string $password): void {
        $bridgeSecret = trim($this->config->getAppValue('enhanced_registration', 'bridge_secret', ''));
        if ($bridgeSecret === '') {
            throw new \RuntimeException('LLDAP Bridge Secret ist nicht konfiguriert.');
        }

        $payload = json_encode([
            'secret' => $bridgeSecret,
            'username' => $userId,
            'password' => $password,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ]);

        $bridgeUrl = trim($this->config->getAppValue(
            'enhanced_registration',
            'bridge_url',
            ''
        ));

        if ($bridgeUrl === '') {
            throw new \RuntimeException('Passwort-Bridge URL ist nicht konfiguriert. Bitte in den Enhanced Registration Einstellungen setzen.');
        }

        $response = @file_get_contents($bridgeUrl, false, $context);

        if ($response === false || trim($response) !== 'ok') {
            throw new \RuntimeException('LLDAP Passwortänderung fehlgeschlagen: ' . (string)$response);
        }
    }

    public function getUsersWithGroups(): array {
        $response = $this->query(
            'query {
                groups {
                    id
                    displayName
                    users {
                        id
                        email
                        displayName
                    }
                }
            }'
        );

        $users = [];

        foreach ($response["data"]["groups"] ?? [] as $group) {
            $groupId = (int)($group["id"] ?? 0);
            $groupName = (string)($group["displayName"] ?? "");

            foreach ($group["users"] ?? [] as $user) {
                $userId = (string)($user["id"] ?? "");
                if ($userId === "") {
                    continue;
                }

                if (!isset($users[$userId])) {
                    $users[$userId] = [
                        "id" => $userId,
                        "email" => (string)($user["email"] ?? ""),
                        "displayName" => (string)($user["displayName"] ?? ""),
                        "groups" => [],
                    ];
                }

                $users[$userId]["groups"][] = [
                    "id" => $groupId,
                    "displayName" => $groupName,
                ];
            }
        }

        uasort($users, function ($a, $b) {
            return strcasecmp($a["id"] ?? "", $b["id"] ?? "");
        });

        return array_values($users);
    }


    public function getAssignableGroups(): array {
        $pendingGroupId = $this->requiredGroupId('lldap_pending_group_id', 'Pending-Gruppe');
        $blacklistGroupId = $this->requiredGroupId('lldap_blacklist_group_id', 'Blacklist-Gruppe');

        $protectedNames = array_filter(array_map('trim', explode(',', strtolower(
            $this->config->getAppValue('enhanced_registration', 'protected_group_names', 'pending-users,blacklist')
        ))));

        $protectedPrefixes = array_filter(array_map('trim', explode(',', strtolower(
            $this->config->getAppValue('enhanced_registration', 'protected_group_prefixes', 'lldap_')
        ))));

        $groups = $this->getGroups();

        return array_values(array_filter($groups, function ($group) use ($pendingGroupId, $blacklistGroupId, $protectedNames, $protectedPrefixes) {
            $id = (int)($group['id'] ?? 0);
            $name = strtolower((string)($group['displayName'] ?? ''));

            if ($id === $pendingGroupId || $id === $blacklistGroupId) {
                return false;
            }

            if (in_array($name, $protectedNames, true)) {
                return false;
            }

            foreach ($protectedPrefixes as $prefix) {
                if ($prefix !== '' && strpos($name, $prefix) === 0) {
                    return false;
                }
            }

            return true;
        }));
    }

    public function updateUserGroups(string $userId, array $selectedGroupIds): void {
        $assignableGroups = $this->getAssignableGroups();

        $assignableIds = array_map(function ($group) {
            return (int)($group['id'] ?? 0);
        }, $assignableGroups);

        $assignableIds = array_values(array_filter($assignableIds));

        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $selectedGroupIds))));
        $selectedIds = array_values(array_intersect($selectedIds, $assignableIds));

        $currentIds = [];

        foreach ($this->getUsersWithGroups() as $user) {
            if (($user['id'] ?? '') !== $userId) {
                continue;
            }

            foreach ($user['groups'] ?? [] as $group) {
                $currentIds[] = (int)($group['id'] ?? 0);
            }

            break;
        }

        $currentIds = array_values(array_unique(array_filter($currentIds)));

        foreach ($assignableIds as $groupId) {
            $currentlyInGroup = in_array($groupId, $currentIds, true);
            $shouldBeInGroup = in_array($groupId, $selectedIds, true);

            if ($shouldBeInGroup && !$currentlyInGroup) {
                $this->addUserToGroup($userId, $groupId);
            }

            if (!$shouldBeInGroup && $currentlyInGroup) {
                $this->removeUserFromGroup($userId, $groupId);
            }
        }
    }


}
