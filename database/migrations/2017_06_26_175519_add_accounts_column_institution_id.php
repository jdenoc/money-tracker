<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnInstitutionId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedInteger('institution_id')->after('account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('institution_id');
        });
    }

}