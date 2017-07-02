<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseAccountTypesColumnTypeNameLength extends Migration {

    /**
     * Increase account_types.type_name length to 100 characters
     *
     * @return void
     */
    public function up(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE type_name type_name VARCHAR(100)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        // Data won't be trimmed automatically, so we'll have to do it before shrinking type_name length
        DB::update("UPDATE account_types SET type_name=SUBSTRING(type_name, 0, 21)");
        DB::statement("ALTER TABLE account_types CHANGE type_name type_name VARCHAR(21)");
    }

}