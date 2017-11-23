<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTagsTagColumnToName extends Migration {

    /**
     * Rename tags.tag to tags.name
     *
     * @return void
     */
    public function up(){
        Schema::table('tags', function (Blueprint $table) {
            $table->renameColumn('tag', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('tags', function (Blueprint $table) {
            $table->renameColumn('name', 'tag');
        });
    }
}
