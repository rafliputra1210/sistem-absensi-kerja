<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            // Menambahkan kolom jam masuk dan pulang dengan nilai default
            $table->time('start_time')->default('08:00:00')->after('radius_meter');
            $table->time('end_time')->default('17:00:00')->after('start_time');
        });
    }

    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};