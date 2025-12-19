<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['moment_id', 'post_id']);
            $table->index(['moment_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moment_items');
    }
};

