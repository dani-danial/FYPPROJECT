<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Add username if it doesn't exist
        if (!Schema::hasColumn('users', 'username')) {
            $table->string('username')->unique()->nullable()->after('name');
        }
        // Add about section
        if (!Schema::hasColumn('users', 'about')) {
            $table->text('about')->nullable()->after('email');
        }
        // Add profile photo path
        if (!Schema::hasColumn('users', 'profile_photo_path')) {
            $table->string('profile_photo_path', 2048)->nullable()->after('about');
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['username', 'about', 'profile_photo_path']);
    });
}
};
