<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Service;

use OCP\IDBConnection;

class PasswordResetService {

    public function __construct(
        private IDBConnection $db
    ) {
    }

    public function createReset(string $email, string $userId): string {

        $token = (string)random_int(100000, 999999);
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
                'token' => $query->createNamedParameter($token),
                'expires_at' => $query->createNamedParameter($expires),
                'used' => $query->createNamedParameter(0),
            ]);

        $query->executeStatement();

        return $token;
    }

    public function getValidReset(string $token): ?array {

        $query = $this->db->getQueryBuilder();

        $query->select('*')
            ->from('enhanced_password_resets')
            ->where(
                $query->expr()->eq(
                    'token',
                    $query->createNamedParameter($token)
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
            );

        $row = $query->executeQuery()->fetchAssociative();

        return $row ?: null;
    }

    public function markUsed(string $token): void {

        $query = $this->db->getQueryBuilder();

        $query->update('enhanced_password_resets')
            ->set('used', $query->createNamedParameter(1))
            ->where(
                $query->expr()->eq(
                    'token',
                    $query->createNamedParameter($token)
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
