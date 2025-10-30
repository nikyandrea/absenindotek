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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['cuti_tahunan', 'izin', 'sakit', 'libur_khusus']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days')->default(1);
            $table->text('reason');
            $table->string('attachment_path')->nullable(); // untuk surat dokter
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('submitted');
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('approval_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
