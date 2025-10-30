-- Migration 1: Add report fields to attendance tables
-- Add daily_report to attendance_sessions
ALTER TABLE attendance_sessions ADD COLUMN daily_report TEXT;

-- Add incentive fields to attendance_daily  
ALTER TABLE attendance_daily ADD COLUMN incentive_on_time DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE attendance_daily ADD COLUMN incentive_out_of_town DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE attendance_daily ADD COLUMN incentive_holiday DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE attendance_daily ADD COLUMN monthly_late_count INTEGER DEFAULT 0;

-- Migration 2: Create monthly_adjustments table
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
);

CREATE INDEX idx_monthly_adjustments_user_period ON monthly_adjustments(user_id, year, month);
CREATE INDEX idx_monthly_adjustments_type ON monthly_adjustments(type);

-- Migration 3: Add rates to users table
ALTER TABLE users ADD COLUMN hourly_overtime_rate DECIMAL(10, 2) DEFAULT 5000;
ALTER TABLE users ADD COLUMN incentive_on_time DECIMAL(10, 2) DEFAULT 10000;
ALTER TABLE users ADD COLUMN incentive_out_of_town DECIMAL(10, 2) DEFAULT 50000;
ALTER TABLE users ADD COLUMN incentive_holiday DECIMAL(10, 2) DEFAULT 25000;

-- Insert migration records
INSERT INTO migrations (migration, batch) VALUES 
('2025_10_29_000001_add_report_fields_to_attendance_tables', 2),
('2025_10_29_000002_create_monthly_adjustments_table', 2),
('2025_10_29_000003_add_rates_to_users_table', 2);
