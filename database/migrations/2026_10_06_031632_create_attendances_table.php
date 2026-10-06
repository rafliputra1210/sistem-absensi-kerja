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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->enum('status', ['hadir', 'telat', 'izin', 'alpha'])->default('hadir');
            $table->string('ip_address_in')->nullable();
            $table->decimal('lat_in', 10, 8)->nullable();
            $table->decimal('lng_in', 11, 8)->nullable();
            $table->string('photo_in')->nullable();
            $table->decimal('lat_out', 10, 8)->nullable();
            $table->decimal('lng_out', 11, 8)->nullable();
            $table->string('photo_out')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
