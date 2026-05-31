<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000007Date20260531130000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('enhanced_password_resets')) {
            return null;
        }

        $table = $schema->getTable('enhanced_password_resets');

        if (!$table->hasColumn('code_hash')) {
            $table->addColumn('code_hash', 'string', [
                'notnull' => false,
                'length' => 64,
            ]);
        }

        if (!$table->hasIndex('er_pw_reset_code_hash_idx')) {
            $table->addIndex(['code_hash'], 'er_pw_reset_code_hash_idx');
        }

        return $schema;
    }
}
