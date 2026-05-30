<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Service;

use OCP\IDBConnection;

class RegistrationService {

    public function __construct(private IDBConnection $db) {}

    public function createRegistration(string $email): string {

        $check = $this->db->getQueryBuilder();
        $check->select("id")
            ->from("enhanced_registrations")
            ->where($check->expr()->eq("email", $check->createNamedParameter($email)))
            ->andWhere($check->expr()->eq("used", $check->createNamedParameter(0)))
            ->andWhere($check->expr()->gt("expires_at", $check->createNamedParameter(time())));

        if ($check->executeQuery()->fetchOne()) {
            throw new \RuntimeException("Für diese E-Mail-Adresse wurde bereits ein aktiver Bestätigungscode angefordert. Bitte prüfen Sie Ihr Postfach oder warten Sie 10 Minuten.");
        }

        $code = (string)random_int(100000, 999999);
        $expires = time() + 600;

        $query = $this->db->getQueryBuilder();

        $query->insert('enhanced_registrations')
            ->values([
                'email' => $query->createNamedParameter($email),
                'verification_code' => $query->createNamedParameter($code),
                'token' => $query->createNamedParameter($code),
                'expires_at' => $query->createNamedParameter($expires),
                'used' => $query->createNamedParameter(0),
            ]);

        $query->executeStatement();

        return $code;
    }

    public function getRegistrationByToken(string $code): ?array {

        $query = $this->db->getQueryBuilder();

        $query->select('*')
            ->from('enhanced_registrations')
            ->where(
                $query->expr()->eq(
                    'token',
                    $query->createNamedParameter($code)
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

    public function markTokenUsed(string $code): void {

        $query = $this->db->getQueryBuilder();

        $query->update('enhanced_registrations')
            ->set('used', $query->createNamedParameter(1))
            ->where(
                $query->expr()->eq(
                    'token',
                    $query->createNamedParameter($code)
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

    public function resendRegistration(string $email): string {
        $row = $this->getActiveRegistrationByEmail($email);

        if ($row) {
            return (string)($row['token'] ?? $row['verification_code']);
        }

        return $this->createRegistration($email);
    }

}
