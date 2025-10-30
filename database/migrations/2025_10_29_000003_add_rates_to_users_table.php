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
        Schema::table('users', function (Blueprint $table) {
            // Overtime and incentive rates
            $table->decimal('hourly_overtime_rate', 10, 2)->default(5000)->after('schedule_type'); // Upah lembur per jam
            $table->decimal('incentive_on_time', 10, 2)->default(10000)->after('hourly_overtime_rate'); // Insentif datang tepat waktu
            $table->decimal('incentive_out_of_town', 10, 2)->default(50000)->after('incentive_on_time'); // Insentif dinas luar kota
            $table->decimal('incentive_holiday', 10, 2)->default(25000)->after('incentive_out_of_town'); // Insentif masuk hari libur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hourly_overtime_rate',
                'incentive_on_time',
                'incentive_out_of_town',
                'incentive_holiday',
            ]);
        });
    }
};
