<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->boolean('is_request')->default(false)->after('last_read_at');
            $table->boolean('is_pinned')->default(false)->after('is_request');
            $table->string('role')->default('member')->after('is_pinned');
            $table->index(['user_id', 'is_request', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_request', 'is_pinned']);
            $table->dropColumn(['is_request', 'is_pinned', 'role']);
        });
    }
};

