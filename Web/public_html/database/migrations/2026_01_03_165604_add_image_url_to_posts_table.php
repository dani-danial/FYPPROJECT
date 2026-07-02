<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Add image_url column if it doesn't exist
            if (!Schema::hasColumn('posts', 'image_url')) {
                $table->string('image_url')->nullable()->after('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};