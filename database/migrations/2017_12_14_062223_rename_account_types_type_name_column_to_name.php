<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAccountTypesTypeNameColumnToName extends Migration
{
    /**
     * Rename account_types.type_name to account_types.name
     *
     * @return void
     */
    public function up(){
        // Because there is a column of type ENUM in the account_types table, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE `type_name` `name` VARCHAR(100);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE `name` `type_name` VARCHAR(100);");
    }
}
