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
    Schema::table('users', function (Blueprint $table) {
        // Only add if they don't exist yet
        if (!Schema::hasColumn('users', 'username')) {
            $table->string('username')->unique()->nullable()->after('name');
        }
        if (!Schema::hasColumn('users', 'about_me')) {
            $table->text('about_me')->nullable()->after('username');
        }
        if (!Schema::hasColumn('users', 'profile_photo_path')) {
            $table->string('profile_photo_path', 2048)->nullable()->after('email');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
