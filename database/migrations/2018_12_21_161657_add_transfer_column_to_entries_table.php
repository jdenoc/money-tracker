<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransferColumnToEntriesTable extends Migration {

    private static $TABLE = "entries";
    private static $NEW_COLUMN = "transfer_entry_id";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->unsignedInteger(self::$NEW_COLUMN)->after('disabled')->nullable()
                ->comment("ID of an entry corresponding to a transfer");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->dropColumn(self::$NEW_COLUMN);
        });
    }
}
