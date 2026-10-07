<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // 1. Tambah kolom di tabel attendances
    Schema::table('attendances', function (Blueprint $table) {
        $table->boolean('is_overtime')->default(false)->after('status');
        $table->integer('overtime_minutes')->default(0)->after('is_overtime');
        $table->text('overtime_reason')->nullable()->after('overtime_minutes');
        $table->enum('overtime_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('overtime_reason');
    });

    // 2. Tambah aturan minimal lembur di tabel office_settings
    Schema::table('office_settings', function (Blueprint $table) {
        $table->integer('min_overtime_minutes')->default(60)->after('end_time');
    });
}

public function down(): void
{
    Schema::table('attendances', function (Blueprint $table) {
        $table->dropColumn(['is_overtime', 'overtime_minutes', 'overtime_reason', 'overtime_status']);
    });
    Schema::table('office_settings', function (Blueprint $table) {
        $table->dropColumn('min_overtime_minutes');
    });
}
};
