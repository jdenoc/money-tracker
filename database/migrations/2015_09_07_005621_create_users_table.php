<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    private static $TABLE = 'users';

    /**
     * Create `users` table
     *
     * @return void
     */
    public function up(){
        Schema::create(self::$TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists(self::$TABLE);
    }

};