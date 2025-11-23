-- ====================================================================
-- Add Missing Fields to member_profiles Table
-- Created: 2025-11-23
-- Purpose: Add fields that exist in view but not in database
-- ====================================================================

USE spk_db;

-- Add religion field
ALTER TABLE `member_profiles`
ADD COLUMN `religion` ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya') NULL DEFAULT NULL
COMMENT 'Agama'
AFTER `gender`;

-- Add marital_status field
ALTER TABLE `member_profiles`
ADD COLUMN `marital_status` ENUM('Belum Menikah', 'Menikah', 'Cerai', 'Cerai Mati') NULL DEFAULT NULL
COMMENT 'Status Pernikahan'
AFTER `religion`;

-- Add employment_type field (different from employment_status_id)
ALTER TABLE `member_profiles`
ADD COLUMN `employment_type` ENUM('Dosen Tetap', 'Dosen Tidak Tetap', 'Tendik Tetap', 'Tendik Tidak Tetap', 'Honorer', 'Lainnya') NULL DEFAULT NULL
COMMENT 'Jenis Kepegawaian (Dosen/Tendik)'
AFTER `employment_status_id`;

-- Add employee_id field
ALTER TABLE `member_profiles`
ADD COLUMN `employee_id` VARCHAR(50) NULL DEFAULT NULL
COMMENT 'Nomor Induk Pegawai'
AFTER `nidn_nip`;

-- Add index for employee_id
ALTER TABLE `member_profiles`
ADD INDEX `idx_employee_id` (`employee_id`);

-- ====================================================================
-- Notes:
-- - religion: For storing member's religion
-- - marital_status: For storing member's marital status
-- - employment_type: Different from employment_status_id
--   employment_status_id = PNS/Non-PNS/Kontrak (from master table)
--   employment_type = Dosen/Tendik classification
-- - employee_id: Employee number (different from NIDN/NIP)
-- ====================================================================
