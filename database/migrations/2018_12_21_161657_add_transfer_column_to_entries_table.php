<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransferColumnToEntriesTable extends Migration {

    private $_table = "entries";
    private $_new_column = "transfer_entry_id";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table($this->_table, function (Blueprint $table) {
            $table->unsignedInteger($this->_new_column)->after('disabled')->nullable()
                ->comment("ID of an entry corresponding to a transfer");
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
