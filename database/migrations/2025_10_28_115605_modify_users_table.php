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
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['karyawan', 'admin', 'supervisor'])->default('karyawan')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->enum('work_time_type', ['bebas', 'tetap'])->default('bebas')->after('is_active');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null')->after('work_time_type');
            $table->decimal('overtime_rate_per_hour', 10, 2)->default(0)->after('office_id');
            $table->decimal('ontime_incentive_per_day', 10, 2)->default(0)->after('overtime_rate_per_hour');
            $table->decimal('out_of_town_incentive_per_day', 10, 2)->default(0)->after('ontime_incentive_per_day');
            $table->decimal('holiday_incentive_per_day', 10, 2)->default(0)->after('out_of_town_incentive_per_day');
            $table->integer('annual_leave_quota')->default(12)->after('holiday_incentive_per_day');
            $table->integer('annual_leave_remaining')->default(12)->after('annual_leave_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn([
                'phone',
                'role',
                'is_active',
                'work_time_type',
                'office_id',
                'overtime_rate_per_hour',
                'ontime_incentive_per_day',
                'out_of_town_incentive_per_day',
                'holiday_incentive_per_day',
                'annual_leave_quota',
                'annual_leave_remaining'
            ]);
        });
    }
};
