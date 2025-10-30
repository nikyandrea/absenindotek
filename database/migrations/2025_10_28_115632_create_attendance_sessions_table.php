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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Check-in data
            $table->timestamp('check_in_at')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_in_accuracy', 8, 2)->nullable(); // GPS accuracy in meters
            $table->decimal('check_in_face_score', 3, 2)->nullable();
            $table->string('check_in_photo_path')->nullable(); // optional untuk audit

            // Check-out data
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('check_out_accuracy', 8, 2)->nullable();
            $table->decimal('check_out_face_score', 3, 2)->nullable();
            $table->string('check_out_photo_path')->nullable();

            // Status & flags
            $table->enum('check_in_location_status', ['in_geofence', 'out_geofence'])->default('in_geofence');
            $table->enum('check_out_location_status', ['in_geofence', 'out_geofence'])->default('in_geofence');
            $table->boolean('is_out_of_town')->default(false);
            $table->boolean('needs_approval')->default(false);
            $table->boolean('is_approved')->nullable(); // null = pending, true = approved, false = rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Work details
            $table->text('work_detail')->nullable(); // detail pekerjaan hari ini
            $table->boolean('is_overtime')->default(false);
            $table->text('overtime_reason')->nullable(); // alasan lembur pagi/sore

            // Valid time (untuk perhitungan)
            $table->timestamp('valid_start_at')->nullable(); // jam valid mulai
            $table->timestamp('valid_end_at')->nullable(); // jam valid selesai

            // Device info
            $table->json('device_info')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'check_in_at']);
            $table->index(['user_id', 'check_out_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
