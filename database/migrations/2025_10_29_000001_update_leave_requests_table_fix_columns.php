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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Rename columns to match controller code
            $table->renameColumn('type', 'leave_type');
            $table->renameColumn('total_days', 'duration_days');
            
            // Make reason nullable for cuti_tahunan
            $table->text('reason')->nullable()->change();
            
            // Update status enum to match controller
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Revert column names
            $table->renameColumn('leave_type', 'type');
            $table->renameColumn('duration_days', 'total_days');
            
            // Make reason not nullable again
            $table->text('reason')->nullable(false)->change();
            
            // Revert status enum
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'submitted'");
        });
    }
};
