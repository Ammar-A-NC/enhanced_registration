<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Service;

use OCP\IConfig;
use Psr\Log\LoggerInterface;

class LldapService {
    public function __construct(
        private IConfig $config,
        private LoggerInterface $logger
    ) {}

    private function requiredGroupId(string $key, string $label): int {
        $value = trim($this->config->getAppValue('enhanced_registration', $key, ''));

        if ($value === '' || !ctype_digit($value) || (int)$value <= 0) {
            throw new \RuntimeException($label . ' ist nicht konfiguriert. Bitte in den Enhanced Registration Einstellungen setzen.');
        }

        return (int)$value;
    }

    private function lldapGraphqlUrl(): string {
        $url = trim($this->config->getAppValue('enhanced_registration', 'lldap_url', ''));

        if ($url === '') {
            throw new \RuntimeException('LLDAP URL ist nicht konfiguriert.');
        }

        return $url;
    }

    private function curlJsonPost(string $url, array $payload, array $headers = []): array {
        $jsonPayload = json_encode($payload);

        if ($jsonPayload === false) {
            throw new \RuntimeException('JSON-Encoding fehlgeschlagen.');
        }

        $requestHeaders = array_merge(['Content-Type: application/json'], $headers);

        $ch = curl_init($url);

        if ($ch === false) {
            throw new \RuntimeException('curl_init fehlgeschlagen.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $this->logger->warning('Enhanced Registration: LLDAP HTTP request failed', [
                'url' => $url,
                'error' => $curlError,
            ]);

            throw new \RuntimeException('LLDAP HTTP-Anfrage fehlgeschlagen: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->logger->warning('Enhanced Registration: LLDAP HTTP request returned unexpected status', [
                'url' => $url,
                'status' => $httpCode,
                'response' => substr((string)$response, 0, 1000),
            ]);

            throw new \RuntimeException('LLDAP HTTP-Status unerwartet: ' . $httpCode);
        }

        $decoded = json_decode((string)$response, true);

        if (!is_array($decoded)) {
            $this->logger->warning('Enhanced Registration: LLDAP response was not valid JSON', [
                'url' => $url,
                'response' => substr((string)$response, 0, 1000),
            ]);

            throw new \RuntimeException('LLDAP Antwort ist kein gültiges JSON.');
        }

        return $decoded;
    }

    private function getToken(): string {
        $graphql = $this->lldapGraphqlUrl();
        $base = preg_replace('#/api/graphql$#', '', $graphql);

        if (!is_string($base) || trim($base) === '') {
            throw new \RuntimeException('LLDAP Basis-URL konnte nicht bestimmt werden.');
        }

        $user = $this->config->getAppValue('enhanced_registration', 'lldap_admin_user', '');
        $password = $this->config->getAppValue('enhanced_registration', 'lldap_admin_password', '');

        $json = $this->curlJsonPost($base . '/auth/simple/login', [
            'username' => $user,
            'password' => $password,
        ]);

        if (!isset($json['token']) || !is_string($json['token']) || $json['token'] === '') {
            throw new \RuntimeException('LLDAP Token fehlt.');
        }

        return $json['token'];
    }

    private function query(string $query, array $variables = []): array {
        $url = $this->lldapGraphqlUrl();
        $token = $this->getToken();

        $json = $this->curlJsonPost(
            $url,
            [
                'query' => $query,
                'variables' => $variables,
            ],
            [
                'Authorization: Bearer ' . $token,
            ]
        );

        if (isset($json['errors'])) {
            $this->logger->warning('Enhanced Registration: LLDAP GraphQL error', [
                'errors' => $json['errors'],
            ]);

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

        $created = false;

        try {
            $this->query(
                'mutation CreateUser($user: CreateUserInput!) {
                    createUser(user: $user) { id }
                }',
                [
                    'user' => $userInput,
                ]
            );

            $created = true;

            $this->setUserPassword($username, $password);
            $this->addUserToGroup($username, $group);
        } catch (\Throwable $e) {
            if ($created) {
                try {
                    $this->deleteUser($username);
                    $this->logger->warning('Enhanced Registration: cleaned up partially created LLDAP user', [
                        'user' => $username,
                    ]);
                } catch (\Throwable $cleanupError) {
                    $this->logger->error('Enhanced Registration: failed to clean up partially created LLDAP user', [
                        'user' => $username,
                        'error' => $cleanupError->getMessage(),
                    ]);
                }
            }

            throw $e;
        }
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

        $bridgeUrl = trim($this->config->getAppValue(
            'enhanced_registration',
            'bridge_url',
            ''
        ));

        if ($bridgeUrl === '') {
            throw new \RuntimeException('Passwort-Bridge URL ist nicht konfiguriert. Bitte in den Enhanced Registration Einstellungen setzen.');
        }

        $payload = json_encode([
            'secret' => $bridgeSecret,
            'username' => $userId,
            'password' => $password,
        ]);

        if ($payload === false) {
            throw new \RuntimeException('JSON-Encoding für Passwort-Bridge fehlgeschlagen.');
        }

        $ch = curl_init($bridgeUrl);

        if ($ch === false) {
            throw new \RuntimeException('curl_init für Passwort-Bridge fehlgeschlagen.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $this->logger->warning('Enhanced Registration: password bridge request failed', [
                'user' => $userId,
                'error' => $curlError,
            ]);

            throw new \RuntimeException('LLDAP Passwortänderung fehlgeschlagen: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300 || trim((string)$response) !== 'ok') {
            $this->logger->warning('Enhanced Registration: password bridge returned unexpected response', [
                'user' => $userId,
                'status' => $httpCode,
                'response' => substr((string)$response, 0, 1000),
            ]);

            throw new \RuntimeException('LLDAP Passwortänderung fehlgeschlagen.');
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
