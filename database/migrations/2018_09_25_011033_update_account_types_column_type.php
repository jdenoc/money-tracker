<?php

use Illuminate\Database\Migrations\Migration;

class UpdateAccountTypesColumnType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE type type ENUM('checking','savings','credit card','debit card','loan')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE type type ENUM('checking','savings','credit card','debit card')");
    }

}