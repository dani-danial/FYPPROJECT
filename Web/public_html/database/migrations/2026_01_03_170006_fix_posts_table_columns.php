<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // 1. Ensure image_url exists
            if (!Schema::hasColumn('posts', 'image_url')) {
                $table->string('image_url')->nullable()->after('content');
            }

            // 2. Ensure author_name exists AND make it nullable (Prevent Crash)
            if (!Schema::hasColumn('posts', 'author_name')) {
                $table->string('author_name')->nullable()->after('user_id');
            } else {
                $table->string('author_name')->nullable()->change();
            }
            
            // 3. Ensure user_id exists
             if (!Schema::hasColumn('posts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
        });
    }

    public function down(): void
    {
        // No down needed for this fix
    }
};