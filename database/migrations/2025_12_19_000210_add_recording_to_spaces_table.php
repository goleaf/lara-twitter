<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->boolean('recording_enabled')->default(false)->after('scheduled_for');
            $table->timestamp('recording_available_until')->nullable()->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['recording_enabled', 'recording_available_until']);
        });
    }
};

