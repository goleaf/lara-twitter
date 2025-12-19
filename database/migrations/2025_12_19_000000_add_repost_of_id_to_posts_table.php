<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table
                ->foreignId('repost_of_id')
                ->nullable()
                ->after('reply_to_id')
                ->constrained('posts')
                ->nullOnDelete();

            $table->index(['repost_of_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['repost_of_id', 'created_at']);
            $table->dropConstrainedForeignId('repost_of_id');
        });
    }
};

