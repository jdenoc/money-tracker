<?php

namespace App\Helpers;

class TimestampType extends \Illuminate\Database\DBAL\TimestampType {

    /**
     * Get the SQL declaration for MySQL.
     * Extends existing Laravel code, but excludes timestamp precision.
     *
     * @param  array  $fieldDeclaration
     * @return string
     */
    protected function getMySqlPlatformSQLDeclaration(array $fieldDeclaration) {
        $columnType = 'TIMESTAMP';

        $notNull = $fieldDeclaration['notnull'] ?? false;

        if (! $notNull) {
            return $columnType.' NULL';
        }

        return $columnType;
    }

}
