<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $blueprint) {
            // Adding the state column after the location column
            $blueprint->string('state')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $blueprint) {
            $blueprint->dropColumn('state');
        });
    }
};