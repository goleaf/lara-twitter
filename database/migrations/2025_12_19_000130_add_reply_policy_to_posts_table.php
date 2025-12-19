<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('reply_policy')->default('everyone')->after('repost_of_id');
            $table->index(['reply_policy', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['reply_policy', 'created_at']);
            $table->dropColumn('reply_policy');
        });
    }
};

