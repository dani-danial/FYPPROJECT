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
        Schema::create('run_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->float('distance_km')->default(0);
            $table->string('time')->nullable();
            $table->string('pace')->nullable();
            $table->date('date')->nullable();
            
            // This is the important column for syncing!
            $table->string('firebase_id')->nullable()->unique(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_summaries');
    }
};
