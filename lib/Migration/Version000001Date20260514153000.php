<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000001Date20260514153000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('enhanced_registrations')) {

            $table = $schema->createTable('enhanced_registrations');

            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);

            $table->addColumn('email', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);

            $table->addColumn('verification_code', 'string', [
                'length' => 32,
                'notnull' => true,
            ]);

            $table->addColumn('email_verified', 'boolean', [
                'default' => false,
            ]);

            $table->addColumn('username', 'string', [
                'length' => 64,
                'notnull' => false,
            ]);

            $table->addColumn('fullname', 'string', [
                'length' => 255,
                'notnull' => false,
            ]);

            $table->addColumn('phone', 'string', [
                'length' => 64,
                'notnull' => false,
            ]);

            $table->addColumn('password_hash', 'string', [
                'length' => 255,
                'notnull' => false,
            ]);

            $table->addColumn('approved', 'boolean', [
                'default' => false,
            ]);

            $table->setPrimaryKey(['id']);
        }

        return $schema;
    }
}
