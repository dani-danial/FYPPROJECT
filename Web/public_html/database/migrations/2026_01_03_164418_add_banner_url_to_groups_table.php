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
        // Remove ->after('icon_url') to prevent the 1054 error
        $table->string('banner_url')->nullable(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'banner_url')) {
                $table->dropColumn('banner_url');
            }
        });
    }
};