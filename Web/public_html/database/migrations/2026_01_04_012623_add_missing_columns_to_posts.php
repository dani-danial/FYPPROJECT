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
    Schema::table('posts', function (Blueprint $table) {
        if (!Schema::hasColumn('posts', 'username')) $table->string('username')->nullable();
        if (!Schema::hasColumn('posts', 'author_name')) $table->string('author_name')->nullable();
        if (!Schema::hasColumn('posts', 'author_username')) $table->string('author_username')->nullable();
        if (!Schema::hasColumn('posts', 'user_image')) $table->string('user_image')->nullable();
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
