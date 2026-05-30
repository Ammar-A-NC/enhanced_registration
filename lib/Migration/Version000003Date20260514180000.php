<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000003Date20260514180000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if ($schema->hasTable('enhanced_registrations')) {
            $table = $schema->getTable('enhanced_registrations');

            if (!$table->hasColumn('expires_at')) {
                $table->addColumn('expires_at', 'integer', [
                    'notnull' => true,
                    'default' => 0,
                ]);
            }

            if (!$table->hasColumn('used')) {
                $table->addColumn('used', 'integer', [
                    'notnull' => true,
                    'default' => 0,
                ]);
            }
        }

        return $schema;
    }
}
