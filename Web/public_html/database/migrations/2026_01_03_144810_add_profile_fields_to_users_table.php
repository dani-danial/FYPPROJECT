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
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'about')) {
                // renamed to 'about' to match standard naming
                $table->text('about')->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'profile_picture')) {
                // using profile_picture to keep it simple for storage
                $table->string('profile_picture')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'about', 'profile_picture']);
        });
    }
};