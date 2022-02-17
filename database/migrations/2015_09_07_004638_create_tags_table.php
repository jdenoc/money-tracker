<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration {

    private static $TABLE = 'tags';

    /**
     * Create `tags` table
     *
     * @return void
     */
    public function up(){
        Schema::create(self::$TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag', 50)->index();
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

}