<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyEntriesTableValueColumnToInteger extends Migration {

    private static string $TABLE = 'entries';
    private static string $COLUMN_VALUE = 'entry_value';

    /**
     * Run the migrations.
     */
    public function up() {
        $new_column = 'int_value';
        Schema::table(self::$TABLE, function(Blueprint $table) use ($new_column) {
            $table->integer($new_column)
                ->nullable(false)
                ->default(0)
                ->after(self::$COLUMN_VALUE);
        });

        $this->copyDataAndReplaceColumn(
            $new_column,
            [$new_column => DB::raw(self::$COLUMN_VALUE.'*100')]  // insert entry_value*100 into int_value
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        $new_column = 'decimal_value';
        Schema::table(self::$TABLE, function(Blueprint $table) use ($new_column) {
            $table->decimal($new_column, 10, 2)
                ->after(self::$COLUMN_VALUE);
        });

        $this->copyDataAndReplaceColumn(
            $new_column,
            [$new_column => DB::raw(self::$COLUMN_VALUE.'/100')]  // insert entry_value/100 into decimal_value
        );
    }

    private function copyDataAndReplaceColumn($new_column, $update_values) {
        DB::table(self::$TABLE)->update($update_values);

        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN_VALUE);
        });

        Schema::table(self::$TABLE, function(Blueprint $table) use ($new_column) {
            $table->renameColumn($new_column, self::$COLUMN_VALUE);
        });
    }

}
