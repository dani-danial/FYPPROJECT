<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adding columns if they don't exist
            if (!Schema::hasColumn('users', 'username')) $table->string('username')->unique()->nullable();
            if (!Schema::hasColumn('users', 'status')) $table->enum('status', ['active', 'inactive'])->default('active');
            if (!Schema::hasColumn('users', 'total_runs')) $table->integer('total_runs')->default(0);
            if (!Schema::hasColumn('users', 'distance_km')) $table->decimal('distance_km', 8, 2)->default(0.00);
            if (!Schema::hasColumn('users', 'about')) $table->text('about')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'status', 'total_runs', 'distance_km', 'about']);
        });
    }
};