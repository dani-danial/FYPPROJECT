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
    Schema::table('notifications', function (Blueprint $table) {
        // Add title and message if they don't exist
        if (!Schema::hasColumn('notifications', 'title')) {
            $table->string('title')->after('id');
        }
        if (!Schema::hasColumn('notifications', 'message')) {
            $table->text('message')->after('title');
        }
        
        // Add metadata columns
        if (!Schema::hasColumn('notifications', 'type')) {
            $table->string('type')->default('info');
        }
        if (!Schema::hasColumn('notifications', 'status')) {
            $table->string('status')->default('sent');
        }
        
        // Add count and timing columns
        if (!Schema::hasColumn('notifications', 'recipients_count')) {
            $table->integer('recipients_count')->default(0);
        }
        if (!Schema::hasColumn('notifications', 'scheduled_at')) {
            $table->timestamp('scheduled_at')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
