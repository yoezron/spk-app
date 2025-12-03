-- ================================================
-- Migration: Add verified_at and verified_by to member_profiles
-- ================================================
-- Menambahkan kolom verified_at dan verified_by untuk tracking approval
-- Kolom ini digunakan oleh ApproveMemberService
-- ================================================

USE spk_db;

-- ================================================
-- 1. Check existing columns
-- ================================================
SHOW COLUMNS FROM member_profiles LIKE '%verified%';

-- ================================================
-- 2. Add verified_at column
-- ================================================
ALTER TABLE member_profiles
ADD COLUMN verified_at DATETIME NULL COMMENT 'Tanggal Verifikasi Anggota' AFTER approved_by;

-- ================================================
-- 3. Add verified_by column
-- ================================================
ALTER TABLE member_profiles
ADD COLUMN verified_by INT(11) UNSIGNED NULL COMMENT 'User ID yang Memverifikasi' AFTER verified_at;

-- ================================================
-- 4. Add index for better query performance
-- ================================================
CREATE INDEX idx_verified_at ON member_profiles(verified_at);

-- ================================================
-- 5. Migrate existing data from approved_at to verified_at (OPTIONAL)
-- ================================================
-- Jika ingin mengkopi data dari approved_at ke verified_at untuk member yang sudah active
-- Uncomment query berikut:

-- UPDATE member_profiles
-- SET
--     verified_at = approved_at,
--     verified_by = approved_by
-- WHERE
--     membership_status = 'active'
--     AND approved_at IS NOT NULL
--     AND verified_at IS NULL;

-- ================================================
-- 6. Verify the changes
-- ================================================
SHOW COLUMNS FROM member_profiles WHERE Field IN ('approved_at', 'approved_by', 'verified_at', 'verified_by');

-- ================================================
-- 7. Check sample data
-- ================================================
SELECT
    id,
    full_name,
    membership_status,
    approved_at,
    approved_by,
    verified_at,
    verified_by
FROM member_profiles
ORDER BY id DESC
LIMIT 5;

-- ================================================
-- EXPECTED RESULT:
-- ================================================
-- Tabel member_profiles sekarang punya 4 kolom:
-- 1. approved_at   (legacy, untuk backward compatibility)
-- 2. approved_by   (legacy)
-- 3. verified_at   (baru, digunakan oleh ApproveMemberService)
-- 4. verified_by   (baru)
-- ================================================
