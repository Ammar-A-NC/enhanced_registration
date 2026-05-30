<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000004Date20260530200000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('enhanced_password_resets')) {
            $table = $schema->createTable('enhanced_password_resets');

            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);

            $table->addColumn('email', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);

            $table->addColumn('user_id', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);

            $table->addColumn('token_hash', 'string', [
                'length' => 64,
                'notnull' => true,
            ]);

            $table->addColumn('expires_at', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->addColumn('used', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->addColumn('created_at', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['email'], 'er_pw_reset_email_idx');
            $table->addUniqueIndex(['token_hash'], 'er_pw_reset_token_idx');
        } else {
            $table = $schema->getTable('enhanced_password_resets');

            if (!$table->hasColumn('token_hash')) {
                $table->addColumn('token_hash', 'string', [
                    'length' => 64,
                    'notnull' => false,
                ]);
            }

            if (!$table->hasColumn('created_at')) {
                $table->addColumn('created_at', 'integer', [
                    'notnull' => true,
                    'default' => 0,
                ]);
            }
        }

        return $schema;
    }
}
