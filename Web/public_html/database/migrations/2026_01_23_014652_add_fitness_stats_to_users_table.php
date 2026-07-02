<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFitnessStatsToUsersTable extends Migration
{
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'weight_kg')) {
            $table->decimal('weight_kg', 5, 2)->nullable();
        }
        if (!Schema::hasColumn('users', 'height_cm')) {
            $table->decimal('height_cm', 5, 2)->nullable();
        }
        if (!Schema::hasColumn('users', 'base_pace_min_km')) {
            $table->decimal('base_pace_min_km', 4, 2)->nullable();
        }
    });
}

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['weight_kg', 'height_cm', 'base_pace_min_km']);
        });
    }
}