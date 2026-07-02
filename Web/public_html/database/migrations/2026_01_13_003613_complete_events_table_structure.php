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
        // Add organizer (Missing in your DB)
        if (!Schema::hasColumn('events', 'organizer')) {
            $table->string('organizer')->nullable()->after('title');
        }

        // Add description (Missing in your DB)
        if (!Schema::hasColumn('events', 'description')) {
            $table->text('description')->nullable()->after('location');
        }
        
        // Add coordinates (if not already added)
        if (!Schema::hasColumn('events', 'latitude')) {
            $table->decimal('latitude', 10, 8)->nullable()->after('description');
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
