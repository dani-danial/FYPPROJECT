<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // Make columns nullable so the DB doesn't crash if they are empty
            $table->string('username')->nullable()->change();
            $table->string('author_username')->nullable()->change();
            
            // Add these if they are missing from your table
            if (!Schema::hasColumn('posts', 'author_name')) {
                $table->string('author_name')->nullable();
            }
            if (!Schema::hasColumn('posts', 'user_image')) {
                $table->string('user_image')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
            $table->string('author_username')->nullable(false)->change();
        });
    }
};