<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('run_summaries', function (Blueprint $table) {
            $table->text('ai_evaluation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('run_summaries', function (Blueprint $table) {
            $table->dropColumn('ai_evaluation');
        });
    }
};
