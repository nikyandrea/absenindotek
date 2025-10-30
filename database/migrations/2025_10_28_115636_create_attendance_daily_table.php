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
        Schema::create('attendance_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');

            // Duration calculations
            $table->integer('total_actual_duration')->default(0); // dalam menit
            $table->integer('total_valid_duration')->default(0); // dalam menit
            $table->integer('target_duration')->default(480); // 8 jam = 480 menit (default)

            // Overtime
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);

            // Flags
            $table->boolean('is_insufficient_duration')->default(false); // kurang dari target
            $table->boolean('is_on_time')->default(true); // untuk jam tetap
            $table->integer('late_count')->default(0); // jumlah terlambat hari ini
            $table->integer('late_minutes')->default(0); // total menit terlambat

            // Status
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_out_of_town')->default(false);
            $table->enum('day_type', ['weekday', 'weekend', 'holiday'])->default('weekday');

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Unique constraint
            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_daily');
    }
};
