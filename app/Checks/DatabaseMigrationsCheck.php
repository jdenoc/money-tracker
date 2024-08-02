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
        if (count($unrun_migrations) === 0) {
            return $result->ok();
        } else {
            return $result->failed("The following migrations have not been run: ".implode(", ", $unrun_migrations));
        }
    }

    protected function migrationStatusOutput(): string {
        Artisan::call("migrate:status", ["--pending"=>true]);
        return trim(Artisan::output());
    }

    public function findUnRunMigrations($migration_status_output): array {
        $migration_status_rows = explode("\n", $migration_status_output);
        array_shift($migration_status_rows);    // remove header row

        $unrun_migrations = [];
        foreach ($migration_status_rows as $migration_status_row) {
            $migration_status_row_cells = explode(" ", trim($migration_status_row));
            $unrun_migrations[] = $migration_status_row_cells[0];
        }
        return $unrun_migrations;
    }

}
