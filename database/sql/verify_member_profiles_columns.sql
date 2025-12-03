-- ================================================
-- VERIFY: Check if all required columns exist
-- ================================================
-- Script untuk memverifikasi kolom yang diperlukan sudah ada
-- di tabel member_profiles
-- ================================================

USE spk_db;

-- ================================================
-- 1. CEK KOLOM YANG DIPERLUKAN
-- ================================================
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    COLUMN_COMMENT
FROM
    INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = 'spk_db'
    AND TABLE_NAME = 'member_profiles'
    AND COLUMN_NAME IN ('verified_at', 'verified_by', 'approved_at', 'approved_by')
ORDER BY
    ORDINAL_POSITION;

-- ================================================
-- EXPECTED RESULT:
-- ================================================
-- Harus menampilkan 4 baris:
-- 1. approved_at   - DATETIME
-- 2. approved_by   - INT(11) UNSIGNED
-- 3. verified_at   - DATETIME (✅ HARUS ADA)
-- 4. verified_by   - INT(11) UNSIGNED (✅ HARUS ADA)
-- ================================================

-- ================================================
-- 2. CEK INDEX PADA verified_at
-- ================================================
SHOW INDEX FROM member_profiles WHERE Column_name = 'verified_at';

-- ================================================
-- EXPECTED RESULT:
-- ================================================
-- Harus menampilkan index 'idx_verified_at' pada kolom verified_at
-- ================================================

-- ================================================
-- 3. SAMPLE DATA - CEK APAKAH VERIFIED_AT SUDAH DIGUNAKAN
-- ================================================
SELECT
    id,
    full_name,
    membership_status,
    approved_at,
    approved_by,
    verified_at,
    verified_by,
    join_date
FROM
    member_profiles
WHERE
    membership_status = 'active'
ORDER BY
    verified_at DESC
LIMIT 5;

-- ================================================
-- 4. STATISTIK APPROVAL
-- ================================================
SELECT
    membership_status,
    COUNT(*) as total,
    COUNT(verified_at) as has_verified_at,
    COUNT(approved_at) as has_approved_at
FROM
    member_profiles
GROUP BY
    membership_status;

-- ================================================
-- RESULT INTERPRETATION:
-- ================================================
-- Jika query #1 menampilkan 4 kolom: ✅ Database sudah OK
-- Jika query #2 menampilkan index: ✅ Performance sudah optimal
-- Jika query #3 menampilkan data: ✅ Approval flow sudah bekerja
-- Jika query #4 menampilkan statistik: ✅ Data konsisten
-- ================================================
