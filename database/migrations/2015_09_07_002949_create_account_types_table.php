<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountTypesTable extends Migration {

    /**
     * Create `account_types` table
     *
     * @return void
     */
    public function up(){
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', array(
                'checking','savings','credit card','debit card'
            ));
            $table->integer('last_digits');
            $table->string('type_name', 21);
            $table->unsignedInteger('account_group');
            $table->unsignedTinyInteger('disabled')->default(0)->index();
            $table->timestamp('last_updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('account_types');
    }

}