<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_username');
            $table->text('content');
            $table->enum('category', ['achievement', 'question', 'general', 'event'])->default('general');
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->boolean('is_flagged')->default(false);
            $table->dateTime('posted_at')->default(now());
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};