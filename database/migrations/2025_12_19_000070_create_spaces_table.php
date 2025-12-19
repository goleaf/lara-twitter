<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['started_at', 'ended_at', 'created_at']);
            $table->index(['host_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};

