<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnCurrency extends Migration {

    private $_table = "accounts";
    private $_new_column = "currency";

    /**
     * Add accounts.currency
     *
     * @return void
     */
    public function up(){
        Schema::table($this->_table, function (Blueprint $table) {
            $table->char($this->_new_column, 3)->after("total")->default("USD")
                ->comment("values conform to the ISO4217 standard");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table($this->_table, function (Blueprint $table) {
            $table->dropColumn($this->_new_column);
        });
    }
}
