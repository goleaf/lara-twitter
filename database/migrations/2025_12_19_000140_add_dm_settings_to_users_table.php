<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dm_policy')->default('everyone')->after('is_premium');
            $table->boolean('dm_allow_requests')->default(true)->after('dm_policy');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dm_policy', 'dm_allow_requests']);
        });
    }
};

