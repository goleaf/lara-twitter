<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muted_terms', function (Blueprint $table) {
            $table->index(['user_id', 'mute_timeline', 'expires_at']);
            $table->index(['user_id', 'mute_notifications', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('muted_terms', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'mute_timeline', 'expires_at']);
            $table->dropIndex(['user_id', 'mute_notifications', 'expires_at']);
        });
    }
};
