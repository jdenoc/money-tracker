<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAccountsColumnAccount extends Migration {

    /**
     * Rename column accounts.account to accounts.name
     *
     * @return void
     */
    public function up(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('account', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('name', 'account');
        });
    }

}