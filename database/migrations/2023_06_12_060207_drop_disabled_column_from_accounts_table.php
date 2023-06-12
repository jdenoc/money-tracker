<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    private static string $TABLE = 'accounts';
    private static string $DROP_COLUMN = 'disabled';
    private static string $COLUMN_DISABLED_STAMP = 'disabled_stamp';

    /**
     * Run the migrations.
     */
    public function up() {
        DB::table(self::$TABLE)->where(self::$DROP_COLUMN, 1)->update([self::$COLUMN_DISABLED_STAMP=>DB::raw("modified_stamp")]);
        DB::table(self::$TABLE)->where(self::$DROP_COLUMN, 0)->update([self::$COLUMN_DISABLED_STAMP=>null]);
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$DROP_COLUMN);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->unsignedTinyInteger(self::$DROP_COLUMN)->after('institution_id')->default(0);
        });
        DB::table(self::$TABLE)->whereNotNull(self::$COLUMN_DISABLED_STAMP)->update([self::$DROP_COLUMN=>1]);
        DB::table(self::$TABLE)->whereNull(self::$COLUMN_DISABLED_STAMP)->update([self::$DROP_COLUMN=>0]);
    }

};
