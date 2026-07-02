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
    Schema::table('events', function (Blueprint $table) {
        // Adding the missing description column
        if (!Schema::hasColumn('events', 'description')) {
            $table->text('description')->nullable()->after('distance_km');
        }
        
        // Adding map coordinates (if not already there)
        if (!Schema::hasColumn('events', 'latitude')) {
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
