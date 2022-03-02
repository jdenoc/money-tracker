<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\DB;

trait TruncateDatabaseTables {

    /**
     * Truncates all database tables related to this connection, except for the "migrations" table
     * @link http://stackoverflow.com/a/18910102/4152012
     */
    private function truncateDatabaseTables(array $ignore_tables=[]){
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach($tables as $table){
            if(in_array($table, $ignore_tables)){
                // don't want to truncate the these table
                continue;
            }
            DB::table($table)->truncate();
        }
    }

}