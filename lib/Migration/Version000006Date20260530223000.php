<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000006Date20260530223000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('enhanced_rate_limits')) {
            $table = $schema->createTable('enhanced_rate_limits');

            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);

            $table->addColumn('action', 'string', [
                'length' => 64,
                'notnull' => true,
            ]);

            $table->addColumn('identity_hash', 'string', [
                'length' => 64,
                'notnull' => true,
            ]);

            $table->addColumn('window_start', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->addColumn('last_attempt', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->addColumn('attempt_count', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->addColumn('updated_at', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['action', 'identity_hash'], 'er_rl_action_ident');
            $table->addIndex(['updated_at'], 'er_rl_updated_idx');
        }

        return $schema;
    }
}
