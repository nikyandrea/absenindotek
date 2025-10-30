<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Office
        $office = Office::create([
            'name' => 'Kantor Pusat Jakarta',
            'timezone' => 'Asia/Jakarta',
            'geofence_type' => 'radius',
            'radius_meters' => 100,
            'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
            'latitude' => -6.208763,
            'longitude' => 106.845599,
        ]);

        $officeBandung = Office::create([
            'name' => 'Kantor Cabang Bandung',
            'timezone' => 'Asia/Jakarta',
            'geofence_type' => 'radius',
            'radius_meters' => 150,
            'address' => 'Jl. Dago No. 100, Bandung',
            'latitude' => -6.900360,
            'longitude' => 107.618763,
        ]);

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin HRD',
            'email' => 'admin@absensi.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567890',
            'role' => 'admin',
            'is_active' => true,
            'work_time_type' => 'tetap',
            'office_id' => $office->id,
            'overtime_rate_per_hour' => 50000,
            'ontime_incentive_per_day' => 20000,
            'out_of_town_incentive_per_day' => 100000,
            'holiday_incentive_per_day' => 150000,
            'annual_leave_quota' => 12,
            'annual_leave_remaining' => 12,
        ]);

        // Create Supervisor
        $supervisor = User::create([
            'name' => 'Supervisor Tim',
            'email' => 'supervisor@absensi.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567891',
            'role' => 'supervisor',
            'is_active' => true,
            'work_time_type' => 'tetap',
            'office_id' => $office->id,
            'overtime_rate_per_hour' => 40000,
            'ontime_incentive_per_day' => 20000,
            'out_of_town_incentive_per_day' => 100000,
            'holiday_incentive_per_day' => 150000,
            'annual_leave_quota' => 12,
            'annual_leave_remaining' => 12,
        ]);

        // Create Employee with Fixed Work Time
        $employee1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@absensi.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567892',
            'role' => 'karyawan',
            'is_active' => true,
            'work_time_type' => 'tetap',
            'office_id' => $office->id,
            'overtime_rate_per_hour' => 35000,
            'ontime_incentive_per_day' => 15000,
            'out_of_town_incentive_per_day' => 80000,
            'holiday_incentive_per_day' => 120000,
            'annual_leave_quota' => 12,
            'annual_leave_remaining' => 12,
        ]);

        // Create Schedule for Employee with Fixed Work Time
        Schedule::create([
            'user_id' => $employee1->id,
            'check_in_time' => '08:00:00',
            'check_out_time' => '17:00:00',
            'work_hours' => 8,
            'active_days' => '1,2,3,4,5',
            'break_duration' => 60,
            'effective_from' => Carbon::now()->startOfMonth(),
            'effective_until' => null,
        ]);

        // Create Employee with Flexible Work Time
        $employee2 = User::create([
            'name' => 'Siti Rahayu',
            'email' => 'siti@absensi.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567893',
            'role' => 'karyawan',
            'is_active' => true,
            'work_time_type' => 'bebas',
            'office_id' => $officeBandung->id,
            'overtime_rate_per_hour' => 35000,
            'ontime_incentive_per_day' => 0,
            'out_of_town_incentive_per_day' => 80000,
            'holiday_incentive_per_day' => 120000,
            'annual_leave_quota' => 12,
            'annual_leave_remaining' => 12,
        ]);

        // Create National Holidays
        $holidays = [
            ['2025-01-01', 'Tahun Baru 2025'],
            ['2025-02-12', 'Imlek 2576'],
            ['2025-03-29', 'Hari Raya Nyepi 1947'],
            ['2025-03-31', 'Isra Mikraj Nabi Muhammad SAW'],
            ['2025-04-18', 'Wafat Isa Almasih'],
            ['2025-04-20', 'Paskah'],
            ['2025-05-01', 'Hari Buruh Internasional'],
            ['2025-05-12', 'Kenaikan Yesus Kristus'],
            ['2025-05-29', 'Hari Raya Waisak 2569'],
            ['2025-06-01', 'Hari Lahir Pancasila'],
            ['2025-06-05', 'Hari Raya Idul Fitri 1446 H (cuti bersama)'],
            ['2025-06-06', 'Hari Raya Idul Fitri 1446 H'],
            ['2025-08-17', 'Hari Kemerdekaan RI'],
            ['2025-09-07', 'Hari Raya Idul Adha 1446 H'],
            ['2025-09-27', 'Tahun Baru Islam 1447 H'],
            ['2025-12-06', 'Maulid Nabi Muhammad SAW'],
            ['2025-12-25', 'Hari Raya Natal'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create([
                'date' => $holiday[0],
                'name' => $holiday[1],
                'office_id' => null,
                'type' => 'nasional',
                'is_global' => true,
            ]);
        }

        $this->command->info('Initial data seeded successfully!');
        $this->command->info('Admin: admin@absensi.com / password123');
        $this->command->info('Supervisor: supervisor@absensi.com / password123');
        $this->command->info('Employee (Fixed): budi@absensi.com / password123');
        $this->command->info('Employee (Flexible): siti@absensi.com / password123');
    }
}

