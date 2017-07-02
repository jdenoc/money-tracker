<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration {

    /**
     * Create `accounts` table
     *
     * @return void
     */
    public function up(){
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account', 100);
            $table->decimal('total', 10, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('accounts');
    }

}