<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('is_reply_like');
            $table->timestamp('scheduled_for')->nullable()->after('is_published');
            $table->string('location', 80)->nullable()->after('scheduled_for');

            $table->index(['is_published', 'created_at']);
            $table->index(['scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'created_at']);
            $table->dropIndex(['scheduled_for']);

            $table->dropColumn(['location', 'scheduled_for', 'is_published']);
        });
    }
};

