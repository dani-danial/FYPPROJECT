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
    // Drop the table first if it exists to fix the 'id' issue
    Schema::dropIfExists('notifications');

    Schema::create('notifications', function (Blueprint $table) {
        $table->id(); // This automatically creates an AUTO_INCREMENT primary key
        $table->string('title');
        $table->text('message');
        $table->string('type')->default('info');
        $table->string('status')->default('sent');
        $table->integer('recipients_count')->default(0);
        $table->timestamp('scheduled_at')->nullable();
        $table->timestamps(); // Creates created_at and updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
