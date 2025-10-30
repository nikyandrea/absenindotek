<?php
/**
 * Script untuk memperbaiki struktur tabel leave_requests
 * Jalankan dengan: php fix-database.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Memperbaiki Struktur Tabel leave_requests ===\n\n";

try {
    // 1. Cek apakah kolom 'type' ada
    echo "1. Memeriksa kolom 'type'...\n";
    $columns = Schema::getColumnListing('leave_requests');
    
    if (in_array('type', $columns)) {
        echo "   - Mengubah 'type' menjadi 'leave_type'...\n";
        DB::statement("ALTER TABLE `leave_requests` CHANGE `type` `leave_type` ENUM('cuti_tahunan', 'izin', 'sakit', 'libur_khusus') NOT NULL");
        echo "   ✅ Berhasil!\n";
    } elseif (in_array('leave_type', $columns)) {
        echo "   ✅ Kolom 'leave_type' sudah ada\n";
    }
    
    // 2. Cek apakah kolom 'total_days' ada
    echo "\n2. Memeriksa kolom 'total_days'...\n";
    $columns = Schema::getColumnListing('leave_requests');
    
    if (in_array('total_days', $columns)) {
        echo "   - Mengubah 'total_days' menjadi 'duration_days'...\n";
        DB::statement("ALTER TABLE `leave_requests` CHANGE `total_days` `duration_days` INT NOT NULL DEFAULT 1");
        echo "   ✅ Berhasil!\n";
    } elseif (in_array('duration_days', $columns)) {
        echo "   ✅ Kolom 'duration_days' sudah ada\n";
    }
    
    // 3. Ubah kolom 'reason' menjadi nullable
    echo "\n3. Mengubah kolom 'reason' menjadi nullable...\n";
    DB::statement("ALTER TABLE `leave_requests` MODIFY `reason` TEXT NULL");
    echo "   ✅ Berhasil!\n";
    
    // 4. Update enum status
    echo "\n4. Mengupdate enum 'status'...\n";
    DB::statement("ALTER TABLE `leave_requests` MODIFY `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
    echo "   ✅ Berhasil!\n";
    
    // 5. Update data lama jika ada
    echo "\n5. Mengupdate data lama...\n";
    $updated = DB::table('leave_requests')
        ->whereIn('status', ['submitted', 'draft'])
        ->update(['status' => 'pending']);
    echo "   ✅ {$updated} record diupdate\n";
    
    echo "\n=== SELESAI! Struktur tabel berhasil diperbaiki ===\n";
    echo "\nSekarang Anda bisa mencoba ajukan cuti lagi!\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nDetail:\n";
    echo $e->getTraceAsString() . "\n";
}
