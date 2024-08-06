<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    private static $TABLE = 'users';

    /**
     * Create `users` table
     */
    public function up(): void {
        Schema::create(self::$TABLE, function(Blueprint $table) {
            $table->increments('id');
            $table->string('email', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists(self::$TABLE);
    }

};
