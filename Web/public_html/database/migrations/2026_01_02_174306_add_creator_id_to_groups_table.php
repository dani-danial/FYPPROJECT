<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('groups', function (Blueprint $table) {
        // Add the creator_id column (default 0 for existing groups)
        $table->unsignedBigInteger('creator_id')->default(0)->after('id');
    });
}

public function down()
{
    Schema::table('groups', function (Blueprint $table) {
        $table->dropColumn('creator_id');
    });
}
};
