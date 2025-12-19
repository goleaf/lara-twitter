<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutes', function (Blueprint $table) {
            $table->foreignId('muter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('muted_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['muter_id', 'muted_id']);
            $table->index(['muted_id', 'muter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutes');
    }
};

