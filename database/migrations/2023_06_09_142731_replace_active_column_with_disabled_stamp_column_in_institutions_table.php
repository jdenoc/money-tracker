<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'institutions';
    private const COLUMN_DISABLE_STAMP = 'disabled_stamp';
    private const COLUMN_ACTIVE = 'active';

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->softDeletes(self::COLUMN_DISABLE_STAMP);
        });

        DB::table(self::TABLE)->where(self::COLUMN_ACTIVE, 0)->update([self::COLUMN_DISABLE_STAMP => DB::raw('modified_stamp')]);

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn(self::COLUMN_ACTIVE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->tinyInteger(self::COLUMN_ACTIVE)->default(1)->after('name');
        });

        DB::table(self::TABLE)->whereNull(self::COLUMN_DISABLE_STAMP)->update([self::COLUMN_ACTIVE => 1]);
        DB::table(self::TABLE)->whereNotNull(self::COLUMN_DISABLE_STAMP)->update([self::COLUMN_ACTIVE => 0]);

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropSoftDeletes(self::COLUMN_DISABLE_STAMP);
        });
    }
};
