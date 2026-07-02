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
        Schema::table('events', function (Blueprint $col) {
            // latitude: 10 total digits, 8 after the decimal (Precision for GPS)
            $col->decimal('latitude', 10, 8)->nullable()->after('location');
            
            // longitude: 11 total digits, 8 after the decimal
            $col->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $col) {
            $col->dropColumn(['latitude', 'longitude']);
        });
    }
};