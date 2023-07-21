<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyAccountsTableTotalColumnToInteger extends Migration {

    private static string $TABLE = 'accounts';
    private static string $COLUMN_TOTAL = 'total';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $new_column = 'int_total';
        Schema::table(self::$TABLE, function(Blueprint $table) use ($new_column) {
            $table->integer($new_column)
                ->default(0)
                ->nullable(false)
                ->after(self::$COLUMN_TOTAL);
        });

        $this->copyDataAndReplaceColumn(
            $new_column,
            [$new_column=>DB::raw(self::$COLUMN_TOTAL.'*100')]  // insert total*100 into int_total
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $new_column = 'decimal_total';
        Schema::table(self::$TABLE, function(Blueprint $table) use ($new_column) {
            $table->decimal($new_column, 10, 2)
                ->default(0.00)
                ->after(self::$COLUMN_TOTAL);
        });

        $this->copyDataAndReplaceColumn(
            $new_column,
            [$new_column=>DB::raw(self::$COLUMN_TOTAL.'/100')]  // insert total/100 into decimal_total
        );
    }

    private function copyDataAndReplaceColumn($new_column, $update_values) {
        DB::table(self::$TABLE)->update($update_values);

        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN_TOTAL);
        });

        Schema::table(self::$TABLE, function(Blueprint $table) use ($new_column) {
            $table->renameColumn($new_column, self::$COLUMN_TOTAL);
        });
    }

}
