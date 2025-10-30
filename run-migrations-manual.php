<?php

/**
 * Manual Migration Runner - Run directly with PHP
 * Usage: php run-migrations-manual.php
 */

$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    die("Database not found at: $dbPath\n");
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Check if columns already exist
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables: " . implode(', ', $tables) . "\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    echo "Running migrations...\n\n";
    
    // Migration 1: Add report fields to attendance tables
    echo "1. Adding daily_report to attendance_sessions...\n";
    try {
        $pdo->exec("ALTER TABLE attendance_sessions ADD COLUMN daily_report TEXT");
        echo "   ✓ daily_report column added\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "   ⚠ daily_report column already exists (skipped)\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n2. Adding incentive fields to attendance_daily...\n";
    $dailyColumns = ['incentive_on_time', 'incentive_out_of_town', 'incentive_holiday', 'monthly_late_count'];
    foreach ($dailyColumns as $column) {
        try {
            $default = ($column === 'monthly_late_count') ? '0' : '0.00';
            $type = ($column === 'monthly_late_count') ? 'INTEGER' : 'DECIMAL(10, 2)';
            $pdo->exec("ALTER TABLE attendance_daily ADD COLUMN $column $type DEFAULT $default");
            echo "   ✓ $column column added\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate column name') !== false) {
                echo "   ⚠ $column column already exists (skipped)\n";
            } else {
                throw $e;
            }
        }
    }
    
    // Migration 2: Create monthly_adjustments table
    echo "\n3. Creating monthly_adjustments table...\n";
    try {
        $pdo->exec("
            CREATE TABLE monthly_adjustments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                year INTEGER NOT NULL,
                month INTEGER NOT NULL,
                type VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                notes TEXT,
                created_by INTEGER NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        echo "   ✓ monthly_adjustments table created\n";
        
        $pdo->exec("CREATE INDEX idx_monthly_adjustments_user_period ON monthly_adjustments(user_id, year, month)");
        echo "   ✓ Index on (user_id, year, month) created\n";
        
        $pdo->exec("CREATE INDEX idx_monthly_adjustments_type ON monthly_adjustments(type)");
        echo "   ✓ Index on (type) created\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ⚠ monthly_adjustments table already exists (skipped)\n";
        } else {
            throw $e;
        }
    }
    
    // Migration 3: Add rates to users table
    echo "\n4. Adding rate columns to users table...\n";
    $rateColumns = [
        'hourly_overtime_rate' => '5000.00',
        'incentive_on_time' => '10000.00',
        'incentive_out_of_town' => '50000.00',
        'incentive_holiday' => '25000.00'
    ];
    
    foreach ($rateColumns as $column => $default) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column DECIMAL(10, 2) DEFAULT $default");
            echo "   ✓ $column column added (default: $default)\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate column name') !== false) {
                echo "   ⚠ $column column already exists (skipped)\n";
            } else {
                throw $e;
            }
        }
    }
    
    // Update existing users with default rates if columns were just added
    echo "\n5. Setting default rates for existing users...\n";
    $pdo->exec("
        UPDATE users 
        SET hourly_overtime_rate = 5000.00,
            incentive_on_time = 10000.00,
            incentive_out_of_town = 50000.00,
            incentive_holiday = 25000.00
        WHERE hourly_overtime_rate IS NULL 
           OR incentive_on_time IS NULL
           OR incentive_out_of_town IS NULL
           OR incentive_holiday IS NULL
    ");
    echo "   ✓ Default rates set for existing users\n";
    
    // Add migration records
    echo "\n6. Recording migrations in database...\n";
    $migrations = [
        '2025_10_29_000001_add_report_fields_to_attendance_tables',
        '2025_10_29_000002_create_monthly_adjustments_table',
        '2025_10_29_000003_add_rates_to_users_table'
    ];
    
    // Get current batch number
    $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
    $maxBatch = $stmt->fetchColumn();
    $newBatch = ($maxBatch ? $maxBatch : 0) + 1;
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO migrations (migration, batch) VALUES (?, ?)");
    foreach ($migrations as $migration) {
        $stmt->execute([$migration, $newBatch]);
        echo "   ✓ Recorded: $migration\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ All migrations completed successfully!\n\n";
    
    // Show summary
    echo "Summary:\n";
    echo "- attendance_sessions: +1 column (daily_report)\n";
    echo "- attendance_daily: +4 columns (incentive fields)\n";
    echo "- users: +4 columns (rate fields)\n";
    echo "- monthly_adjustments: new table created\n";
    echo "\nYou can now use the monthly report feature!\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
