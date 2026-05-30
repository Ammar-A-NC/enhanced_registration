<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0002Date20250514164200 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if ($schema->hasTable('enhanced_registrations')) {
            $table = $schema->getTable('enhanced_registrations');

            if (!$table->hasColumn('token')) {
                $table->addColumn('token', 'string', [
                    'length' => 64,
                    'default' => 'pending',
                    'notnull' => true,
                ]);
            }

            if (!$table->hasColumn('verification_code')) {
                $table->addColumn('verification_code', 'string', [
                    'length' => 32,
                    'default' => 'pending',
                    'notnull' => true,
                ]);
            }
        }

        return $schema;
    }
}
