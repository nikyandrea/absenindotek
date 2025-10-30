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
        // Add daily_report to attendance_sessions
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->text('daily_report')->nullable()->after('work_detail'); // Apa yang dikerjakan hari ini
        });

        // Add incentive fields to attendance_daily
        Schema::table('attendance_daily', function (Blueprint $table) {
            $table->decimal('incentive_on_time', 10, 2)->default(0)->after('overtime_amount'); // Insentif datang tepat waktu
            $table->decimal('incentive_out_of_town', 10, 2)->default(0)->after('incentive_on_time'); // Insentif dinas luar kota
            $table->decimal('incentive_holiday', 10, 2)->default(0)->after('incentive_out_of_town'); // Insentif masuk hari libur
            $table->integer('monthly_late_count')->default(0)->after('late_minutes'); // Total telat dalam sebulan (untuk cek hangus insentif ontime)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn('daily_report');
        });

        Schema::table('attendance_daily', function (Blueprint $table) {
            $table->dropColumn([
                'incentive_on_time',
                'incentive_out_of_town',
                'incentive_holiday',
                'monthly_late_count'
            ]);
        });
    }
};
