<?php

use Illuminate\Database\Migrations\Migration;

class CreateTriggerAccountsCreationTimestamp extends Migration {

    /**
     * Create a MySQL trigger on the accounts table to add the current time to the accounts.create_stamp when inserting a new record
     *
     * @return void
     */
    public function up(){
        DB::unprepared(
            "CREATE TRIGGER trigger_accounts_creation_timestamp
            BEFORE INSERT ON accounts
            FOR EACH ROW
            SET NEW.create_stamp = NOW()"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_accounts_creation_timestamp");
    }

}