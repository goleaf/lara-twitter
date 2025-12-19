<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moment_items', function (Blueprint $table) {
            $table->string('caption', 280)->nullable()->after('post_id');
        });
    }

    public function down(): void
    {
        Schema::table('moment_items', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
    }
};

