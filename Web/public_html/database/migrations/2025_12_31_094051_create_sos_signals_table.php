<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sos_signals', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('user_identifier'); 
            $table->string('phone_number');
            $table->text('message'); 
            $table->string('location_name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['pending', 'ongoing', 'resolved'])->default('pending');
            $table->dateTime('signal_time')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sos_signals');
    }
};