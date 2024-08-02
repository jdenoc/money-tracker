<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEntriesTable extends Migration {

    private static $TABLE = 'entries';

    /**
     * Create `entries` table
     *
     * @return void
     */
    public function up() {
        Schema::create(self::$TABLE, function(Blueprint $table) {
            $table->increments('id');
            $table->date('date')->index();
            $table->unsignedInteger('account_type');
            $table->decimal('value', 10, 2);
            $table->text('memo');
            $table->unsignedTinyInteger('expense')->default(1);
            $table->unsignedTinyInteger('confirm')->default(0)->comment('confirmed according to statements');
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->timestamp('stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists(self::$TABLE);
    }

}
