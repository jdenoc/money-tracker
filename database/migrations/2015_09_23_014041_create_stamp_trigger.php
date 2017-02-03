<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStampTrigger extends Migration {

    /**
     * Create a MySQL trigger to create a trigger on the entries table to add the current time to the entries.create_stamp when inserting a new record
     *
     * @return void
     */
    public function up(){
        DB::unprepared(
            "CREATE TRIGGER trigger_entry_creation_timestamp
            BEFORE INSERT ON entries
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
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_entry_creation_timestamp");
    }

}