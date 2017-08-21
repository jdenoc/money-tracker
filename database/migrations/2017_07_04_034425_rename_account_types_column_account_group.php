<?php

use Illuminate\Database\Migrations\Migration;

class RenameAccountTypesColumnAccountGroup extends Migration
{
    /**
     * Renamed account_types.account_group to account_types.account_id
     *
     * @return void
     */
    public function up(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE account_group account_id INT(10) UNSIGNED NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE account_id account_group INT(10) UNSIGNED NULL");
    }
}
