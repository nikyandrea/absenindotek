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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->time('check_in_time')->nullable(); // jam masuk untuk jam kerja tetap
            $table->time('check_out_time')->nullable(); // jam pulang untuk jam kerja tetap
            $table->integer('work_hours')->default(8); // durasi kerja per hari (jam)
            $table->string('active_days')->default('1,2,3,4,5'); // bitmask hari aktif (1=Sen, 7=Min)
            $table->integer('break_duration')->nullable(); // durasi istirahat dalam menit (opsional)
            $table->date('effective_from')->nullable(); // untuk perubahan jadwal di tengah bulan
            $table->date('effective_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
