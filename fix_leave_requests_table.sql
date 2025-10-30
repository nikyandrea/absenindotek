-- Fix leave_requests table structure
-- Run this SQL directly in your database

-- 1. Rename columns
ALTER TABLE `leave_requests` CHANGE `type` `leave_type` ENUM('cuti_tahunan', 'izin', 'sakit', 'libur_khusus') NOT NULL;
ALTER TABLE `leave_requests` CHANGE `total_days` `duration_days` INT NOT NULL DEFAULT 1;

-- 2. Make reason nullable (for cuti_tahunan)
ALTER TABLE `leave_requests` MODIFY `reason` TEXT NULL;

-- 3. Update status enum to include 'cancelled' and change default
ALTER TABLE `leave_requests` MODIFY `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending';

-- 4. Optional: Update existing records with 'submitted' status to 'pending'
UPDATE `leave_requests` SET `status` = 'pending' WHERE `status` = 'submitted';
UPDATE `leave_requests` SET `status` = 'pending' WHERE `status` = 'draft';
