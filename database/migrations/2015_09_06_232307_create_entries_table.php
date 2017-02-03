<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntriesTable extends Migration {

    /**
     * Create `entries` table
     *
     * @return void
     */
    public function up(){
        Schema::create('entries', function (Blueprint $table) {
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
    public function down(){
        Schema::dropIfExists('entries');
    }

}