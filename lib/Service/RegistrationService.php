<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Service;

use OCP\IDBConnection;

class RegistrationService {

    public function __construct(private IDBConnection $db) {}

    private function tokenHash(string $value): string {
        return hash('sha256', $value);
    }

    private function newManualCode(): string {
        return (string)random_int(10000000, 99999999);
    }

    private function newLinkToken(): string {
        return bin2hex(random_bytes(32));
    }

    public function createRegistration(string $email): array {
        $check = $this->db->getQueryBuilder();
        $check->select('id')
            ->from('enhanced_registrations')
            ->where($check->expr()->eq('email', $check->createNamedParameter($email)))
            ->andWhere($check->expr()->eq('used', $check->createNamedParameter(0)))
            ->andWhere($check->expr()->gt('expires_at', $check->createNamedParameter(time())));

        if ($check->executeQuery()->fetchOne()) {
            throw new \RuntimeException('Für diese E-Mail-Adresse wurde bereits ein aktiver Bestätigungscode angefordert. Bitte prüfen Sie Ihr Postfach oder warten Sie 10 Minuten.');
        }

        $manualCode = $this->newManualCode();
        $linkToken = $this->newLinkToken();
        $expires = time() + 600;

        $query = $this->db->getQueryBuilder();

        $query->insert('enhanced_registrations')
            ->values([
                'email' => $query->createNamedParameter($email),
                'verification_code' => $query->createNamedParameter(''),
                'token' => $query->createNamedParameter(''),
                'token_hash' => $query->createNamedParameter($this->tokenHash($linkToken)),
                'code_hash' => $query->createNamedParameter($this->tokenHash($manualCode)),
                'expires_at' => $query->createNamedParameter($expires),
                'used' => $query->createNamedParameter(0),
            ]);

        $query->executeStatement();

        return [
            'code' => $manualCode,
            'token' => $linkToken,
        ];
    }

    public function getRegistrationByToken(string $credential): ?array {
        $credential = trim($credential);

        if ($credential === '') {
            return null;
        }

        $hash = $this->tokenHash($credential);
        $query = $this->db->getQueryBuilder();

        $query->select('*')
            ->from('enhanced_registrations')
            ->where(
                $query->expr()->orX(
                    $query->expr()->eq('token_hash', $query->createNamedParameter($hash)),
                    $query->expr()->eq('code_hash', $query->createNamedParameter($hash))
                )
            )
            ->andWhere($query->expr()->eq('used', $query->createNamedParameter(0)))
            ->andWhere($query->expr()->gt('expires_at', $query->createNamedParameter(time())))
            ->setMaxResults(1);

        $row = $query->executeQuery()->fetchAssociative();

        return $row ?: null;
    }

    public function markTokenUsed(string $credential): void {
        $credential = trim($credential);

        if ($credential === '') {
            return;
        }

        $hash = $this->tokenHash($credential);
        $query = $this->db->getQueryBuilder();

        $query->update('enhanced_registrations')
            ->set('used', $query->createNamedParameter(1))
            ->where(
                $query->expr()->orX(
                    $query->expr()->eq('token_hash', $query->createNamedParameter($hash)),
                    $query->expr()->eq('code_hash', $query->createNamedParameter($hash))
                )
            );

        $query->executeStatement();
    }

    public function getActiveRegistrationByEmail(string $email): ?array {
        $query = $this->db->getQueryBuilder();

        $query->select('*')
            ->from('enhanced_registrations')
            ->where($query->expr()->eq('email', $query->createNamedParameter($email)))
            ->andWhere($query->expr()->eq('used', $query->createNamedParameter(0)))
            ->andWhere($query->expr()->gt('expires_at', $query->createNamedParameter(time())))
            ->setMaxResults(1);

        $row = $query->executeQuery()->fetchAssociative();

        return $row ?: null;
    }

    public function resendRegistration(string $email): array {
        $row = $this->getActiveRegistrationByEmail($email);

        if ($row) {
            $manualCode = $this->newManualCode();
            $linkToken = $this->newLinkToken();
            $expires = time() + 600;

            $query = $this->db->getQueryBuilder();

            $query->update('enhanced_registrations')
                ->set('verification_code', $query->createNamedParameter(''))
                ->set('token', $query->createNamedParameter(''))
                ->set('code_hash', $query->createNamedParameter($this->tokenHash($manualCode)))
                ->set('token_hash', $query->createNamedParameter($this->tokenHash($linkToken)))
                ->set('expires_at', $query->createNamedParameter($expires))
                ->where($query->expr()->eq('id', $query->createNamedParameter((int)$row['id'])));

            $query->executeStatement();

            return [
                'code' => $manualCode,
                'token' => $linkToken,
            ];
        }

        throw new \RuntimeException('Keine aktive Registrierung für diese E-Mail-Adresse.');
    }
}
