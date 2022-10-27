<?php

namespace App\Checks;

use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class DatabaseMigrationsCheck extends Check {

    public function run(): Result {
        $result = Result::make();

        $migration_status = $this->migrationStatusOutput();
        $unrun_migrations = $this->findUnRunMigrations($migration_status);
        if(count($unrun_migrations) === 0){
            return $result->ok();
        } else {
            return $result->failed("The following migrations have not been run: ".implode(", ", $unrun_migrations));
        }
    }

    protected function migrationStatusOutput(): string {
        Artisan::call("migrate:status");
        return trim(Artisan::output());
    }

    public function findUnRunMigrations($migration_status_output): array {
        $migration_status_rows = explode("\n", $migration_status_output);

        $table_index_h1 = 0;
        $table_index_h2 = 1;
        $table_index_h3 = 2;
        $table_index_foot = count($migration_status_rows)-1;
        unset($migration_status_rows[$table_index_h1], $migration_status_rows[$table_index_h2], $migration_status_rows[$table_index_h3], $migration_status_rows[$table_index_foot]);
        $row_index_ran = 1;
        $row_index_migration = 2;

        $unrun_migrations = [];
        foreach ($migration_status_rows as $migration_status_row){
            $migration_status_row_cells = explode("|", $migration_status_row);
            if("no" === strtolower(trim($migration_status_row_cells[$row_index_ran]))){
                $unrun_migrations[] = trim($migration_status_row_cells[$row_index_migration]);
            }
        }
        return $unrun_migrations;
    }

}