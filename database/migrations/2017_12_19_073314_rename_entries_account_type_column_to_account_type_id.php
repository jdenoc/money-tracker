<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesAccountTypeColumnToAccountTypeId extends Migration {

    /**
     * Rename entries.account_type to entries.account_type_id
     *
     * @return void
     */
    public function up(){
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('account_type', 'account_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('account_type_id', 'account_type');
        });
    }

}