<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete()->unique();
            $table->timestamp('ends_at');
            $table->timestamps();

            $table->index(['ends_at']);
        });

        Schema::create('post_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_poll_id')->constrained()->cascadeOnDelete();
            $table->string('option_text', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['post_poll_id', 'sort_order']);
        });

        Schema::create('post_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_poll_id', 'user_id']);
            $table->index(['post_poll_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_poll_votes');
        Schema::dropIfExists('post_poll_options');
        Schema::dropIfExists('post_polls');
    }
};

