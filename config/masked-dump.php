<?php

use BeyondCode\LaravelMaskedDumper\DumpSchema;

return [
    /**
     * Use this dump schema definition to remove, replace or mask certain parts of your database tables.
     */
    'default' => DumpSchema::define()
        ->allTables()
];
