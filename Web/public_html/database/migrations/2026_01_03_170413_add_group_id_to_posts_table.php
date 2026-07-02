<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Add group_id if it doesn't exist
            if (!Schema::hasColumn('posts', 'group_id')) {
                // 'constrained' automatically links it to the 'groups' table
                // 'cascade' means if the group is deleted, delete the posts too
                $table->foreignId('group_id')->after('id')->constrained('groups')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'group_id')) {
                $table->dropForeign(['group_id']); // Drop foreign key first
                $table->dropColumn('group_id');
            }
        });
    }
};