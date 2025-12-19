<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('description', 280)->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['owner_id', 'created_at']);
            $table->index(['is_public', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moments');
    }
};

