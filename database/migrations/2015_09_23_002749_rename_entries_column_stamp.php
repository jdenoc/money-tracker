<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnStamp extends Migration {

    /**
     * Add column entries.create_stamp
     * Move values from entries.stamp to entries.create_stamp
     * Delete column entries.stamp
     *
     * @return void
     */
    public function up(){
        // add a new column
        Schema::table('entries', function (Blueprint $table) {
            $table->timestamp('create_stamp')->nullable();
            $table->timestamp('modified_stamp')->useCurrent();
        });

        // move data from old column to new column
        $entries = App\Entry::all('id');
        foreach($entries as $entry){
            $entry_stamp = DB::table('entries')->select('stamp')->where('id', $entry->id)->get();
            DB::table('entries')->where('id', $entry->id)->update(['create_stamp'=>$entry_stamp[0]->stamp]);
        }

        // delete old column
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('stamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // add a new column
        Schema::table('entries', function (Blueprint $table) {
            $table->timestamp('stamp')->default(DB::raw("CURRENT_TIMESTAMP"));
        });

        // move data from old column to new column
        $entries = App\Entry::all('id');
        foreach($entries as $entry){
            $entry_stamp = DB::table('entries')->select('create_stamp')->where('id', $entry->id)->get();
            DB::table('entries')->where('id', $entry->id)->update(['stamp'=>$entry_stamp[0]->create_stamp]);
        }

        // delete old column
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('modified_stamp');
            $table->dropColumn('create_stamp');
        });
    }

}