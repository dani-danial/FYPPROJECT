<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // Only add the column if it DOES NOT exist already
            if (!Schema::hasColumn('groups', 'target_km')) {
                $table->decimal('target_km', 8, 2)->default(0)->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'target_km')) {
                $table->dropColumn('target_km');
            }
        });
    }
};