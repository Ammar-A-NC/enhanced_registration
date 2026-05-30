<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000005Date20260530213000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if ($schema->hasTable('enhanced_registrations')) {
            $table = $schema->getTable('enhanced_registrations');

            if (!$table->hasColumn('token_hash')) {
                $table->addColumn('token_hash', 'string', [
                    'length' => 64,
                    'notnull' => false,
                ]);
            }

            if (!$table->hasColumn('code_hash')) {
                $table->addColumn('code_hash', 'string', [
                    'length' => 64,
                    'notnull' => false,
                ]);
            }

            if (!$table->hasIndex('er_reg_token_hash_idx')) {
                $table->addIndex(['token_hash'], 'er_reg_token_hash_idx');
            }

            if (!$table->hasIndex('er_reg_code_hash_idx')) {
                $table->addIndex(['code_hash'], 'er_reg_code_hash_idx');
            }
        }

        return $schema;
    }
}
