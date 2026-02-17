<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->integer('operating_day')->after('closing_date')->default(1);
            $table->boolean('is_manually_started')->default(false)->after('operating_day');
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropColumn(['operating_day', 'is_manually_started']);
        });
    }
};