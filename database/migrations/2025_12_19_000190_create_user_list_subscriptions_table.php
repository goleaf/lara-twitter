<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_list_subscriptions', function (Blueprint $table) {
            $table->foreignId('user_list_id')->constrained('user_lists')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_list_id', 'user_id']);
            $table->index(['user_id', 'user_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_list_subscriptions');
    }
};

