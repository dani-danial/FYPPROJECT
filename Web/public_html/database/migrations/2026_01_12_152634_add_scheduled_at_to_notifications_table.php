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
        // Add all columns used by your NotificationController
        if (!Schema::hasColumn('notifications', 'type')) {
            $table->string('type')->nullable();
        }
        if (!Schema::hasColumn('notifications', 'status')) {
            $table->string('status')->default('scheduled');
        }
        if (!Schema::hasColumn('notifications', 'recipients_count')) {
            $table->integer('recipients_count')->default(0);
        }
        $table->timestamp('scheduled_at')->nullable();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
        });
    }
};
