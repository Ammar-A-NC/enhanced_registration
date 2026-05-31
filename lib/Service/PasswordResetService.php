<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Service;

use OCP\IDBConnection;

class PasswordResetService {

    public function __construct(
        private IDBConnection $db
    ) {
    }

    private function tokenHash(string $token): string {
        return hash('sha256', $token);
    }

    private function manualCode(): string {
        return str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    public function createReset(string $email, string $userId): array {
        $token = bin2hex(random_bytes(32));
        $code = $this->manualCode();
        $expires = time() + 600;

        $qb = $this->db->getQueryBuilder();
        $qb->delete('enhanced_password_resets')
            ->where($qb->expr()->eq('email', $qb->createNamedParameter($email)))
            ->executeStatement();

        $query = $this->db->getQueryBuilder();

        $query->insert('enhanced_password_resets')
            ->values([
                'email' => $query->createNamedParameter($email),
                'user_id' => $query->createNamedParameter($userId),
                'token_hash' => $query->createNamedParameter($this->tokenHash($token)),
                'code_hash' => $query->createNamedParameter($this->tokenHash($code)),
                'expires_at' => $query->createNamedParameter($expires),
                'used' => $query->createNamedParameter(0),
                'created_at' => $query->createNamedParameter(time()),
            ]);

        $query->executeStatement();

        return [
            'token' => $token,
            'code' => $code,
        ];
    }

    public function getValidReset(string $tokenOrCode): ?array {
        $tokenOrCode = trim($tokenOrCode);

        if ($tokenOrCode === '') {
            return null;
        }

        $hash = $this->tokenHash($tokenOrCode);

        $query = $this->db->getQueryBuilder();

        $query->select('*')
            ->from('enhanced_password_resets')
            ->where(
                $query->expr()->orX(
                    $query->expr()->eq('token_hash', $query->createNamedParameter($hash)),
                    $query->expr()->eq('code_hash', $query->createNamedParameter($hash))
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'used',
                    $query->createNamedParameter(0)
                )
            )
            ->andWhere(
                $query->expr()->gt(
                    'expires_at',
                    $query->createNamedParameter(time())
                )
            )
            ->setMaxResults(1);

        $row = $query->executeQuery()->fetchAssociative();

        return $row ?: null;
    }

    public function markUsed(string $tokenOrCode): void {
        $hash = $this->tokenHash(trim($tokenOrCode));

        $query = $this->db->getQueryBuilder();

        $query->update('enhanced_password_resets')
            ->set('used', $query->createNamedParameter(1))
            ->where(
                $query->expr()->orX(
                    $query->expr()->eq('token_hash', $query->createNamedParameter($hash)),
                    $query->expr()->eq('code_hash', $query->createNamedParameter($hash))
                )
            );

        $query->executeStatement();
    }

    public function getActiveResetByEmail(string $email): ?array {
        $query = $this->db->getQueryBuilder();

        $query->select('*')
            ->from('enhanced_password_resets')
            ->where($query->expr()->eq('email', $query->createNamedParameter($email)))
            ->andWhere($query->expr()->eq('used', $query->createNamedParameter(0)))
            ->andWhere($query->expr()->gt('expires_at', $query->createNamedParameter(time())))
            ->setMaxResults(1);

        $row = $query->executeQuery()->fetchAssociative();

        return $row ?: null;
    }
}
