<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('description', 160)->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();

            $table->index(['owner_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lists');
    }
};

