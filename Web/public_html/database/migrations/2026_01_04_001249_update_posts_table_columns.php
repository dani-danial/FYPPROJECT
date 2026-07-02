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
    Schema::table('posts', function (Blueprint $table) {
        // Add missing columns if they don't exist
        if (!Schema::hasColumn('posts', 'username')) {
            $table->string('username')->nullable();
        }
        if (!Schema::hasColumn('posts', 'user_image')) {
            $table->string('user_image')->nullable();
        }
        if (!Schema::hasColumn('posts', 'author_name')) {
            $table->string('author_name')->nullable();
        }
        if (!Schema::hasColumn('posts', 'author_username')) {
            $table->string('author_username')->nullable();
        }
        if (!Schema::hasColumn('posts', 'category')) {
            $table->string('category')->default('general');
        }
        if (!Schema::hasColumn('posts', 'is_flagged')) {
            $table->boolean('is_flagged')->default(false);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }
};
