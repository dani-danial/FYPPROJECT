<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('monthly_km_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('goal_km')->default(50);
            $table->integer('year');
            $table->integer('month');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_km_goals');
    }
};
