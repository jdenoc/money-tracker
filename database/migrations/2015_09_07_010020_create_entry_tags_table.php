<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntryTagsTable extends Migration {

    /**
     * Create `entry_tags` table
     *
     * @return void
     */
    public function up(){
        Schema::create('entry_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entry_id');
            $table->unsignedInteger('tag_id');
            $table->timestamp('stamp')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('entry_tags');
    }

}