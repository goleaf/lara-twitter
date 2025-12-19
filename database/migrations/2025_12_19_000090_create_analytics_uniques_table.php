<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_uniques', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30); // post_view | profile_view
            $table->unsignedBigInteger('entity_id');
            $table->date('day');
            $table->string('viewer_key', 80);
            $table->timestamps();

            $table->unique(['type', 'entity_id', 'day', 'viewer_key']);
            $table->index(['type', 'entity_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_uniques');
    }
};

