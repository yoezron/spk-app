-- ================================================
-- FIX: Stuck Pending Members
-- ================================================
-- Script untuk memperbaiki member yang sudah approved tapi
-- masih muncul di halaman pending karena membership_status
-- tidak ter-update dengan benar
--
-- UPDATED: 2025-12-01
-- - Menambahkan kolom verified_at dan verified_by yang hilang
-- - Fix member yang stuck di pending status
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin
-- 2. Pilih database spk_db
-- 3. Copy-paste dan jalankan query ini satu per satu
-- ================================================

-- ================================================
-- 0. ADD MISSING COLUMNS (verified_at, verified_by)
-- ================================================
-- Kolom ini dibutuhkan oleh ApproveMemberService.php
-- tapi tidak ada di tabel member_profiles

-- Check if columns already exist
SELECT
    COUNT(*) as verified_at_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'spk_db'
AND TABLE_NAME = 'member_profiles'
AND COLUMN_NAME = 'verified_at';

-- If the query above returns 0, run these ALTER TABLE commands:

ALTER TABLE member_profiles
ADD COLUMN verified_at DATETIME NULL
COMMENT 'Timestamp when member was verified/approved'
AFTER join_date;

ALTER TABLE member_profiles
ADD COLUMN verified_by INT(11) UNSIGNED NULL
COMMENT 'User ID of admin who verified this member'
AFTER verified_at;

-- Add foreign key for verified_by
ALTER TABLE member_profiles
ADD CONSTRAINT fk_member_profiles_verified_by
FOREIGN KEY (verified_by) REFERENCES users(id)
ON DELETE SET NULL ON UPDATE CASCADE;

-- ================================================
-- 1. CEK MEMBER YANG STUCK (sudah punya nomor anggota tapi status pending)
-- ================================================
SELECT
    mp.id,
    mp.user_id,
    mp.full_name,
    mp.member_number,
    mp.membership_status,
    mp.join_date,
    mp.verified_at,
    ai.secret as email,
    agu.group as current_role
FROM member_profiles mp
LEFT JOIN auth_identities ai ON ai.user_id = mp.user_id AND ai.type = 'email_password'
LEFT JOIN auth_groups_users agu ON agu.user_id = mp.user_id
WHERE mp.membership_status = 'pending'
AND mp.member_number IS NOT NULL;  -- Jika sudah punya nomor anggota, artinya sudah approved

-- ================================================
-- 2. FIX: UPDATE STATUS KE 'active' UNTUK MEMBER YANG STUCK
-- ================================================
UPDATE member_profiles
SET
    membership_status = 'active',
    verified_at = COALESCE(verified_at, NOW()),  -- Jika NULL, set NOW()
    join_date = COALESCE(join_date, NOW())       -- Jika NULL, set NOW()
WHERE membership_status = 'pending'
AND member_number IS NOT NULL;

-- ================================================
-- 3. FIX: PASTIKAN USER ACCOUNT ACTIVE
-- ================================================
UPDATE users u
JOIN member_profiles mp ON mp.user_id = u.id
SET u.active = 1
WHERE mp.membership_status = 'active'
AND u.active = 0;

-- ================================================
-- 4. FIX: UPDATE ROLE DARI 'calon_anggota' KE 'anggota'
-- ================================================
-- Untuk member yang sudah approved tapi rolenya masih calon_anggota
UPDATE auth_groups_users
SET `group` = 'anggota'
WHERE user_id IN (
    SELECT user_id
    FROM member_profiles
    WHERE membership_status = 'active'
)
AND `group` = 'calon_anggota';

-- ================================================
-- 5. VERIFIKASI: CEK HASIL SETELAH FIX
-- ================================================
SELECT
    mp.id,
    mp.full_name,
    mp.member_number,
    mp.membership_status,
    agu.group as role,
    u.active as user_active,
    mp.verified_at,
    mp.join_date
FROM member_profiles mp
JOIN users u ON u.id = mp.user_id
LEFT JOIN auth_groups_users agu ON agu.user_id = mp.user_id
WHERE mp.member_number IS NOT NULL
ORDER BY mp.id;

-- ================================================
-- 6. CEK PENDING MEMBERS SEKARANG (SEHARUSNYA KOSONG atau HANYA YANG BELUM PUNYA NOMOR)
-- ================================================
SELECT
    COUNT(*) as total_pending,
    SUM(CASE WHEN member_number IS NOT NULL THEN 1 ELSE 0 END) as stuck_with_number,
    SUM(CASE WHEN member_number IS NULL THEN 1 ELSE 0 END) as genuine_pending
FROM member_profiles
WHERE membership_status = 'pending';

-- ================================================
-- EXPECTED RESULT:
-- ================================================
-- stuck_with_number = 0 (tidak ada member yang stuck)
-- genuine_pending = jumlah calon anggota yang belum diapprove
-- ================================================

-- ================================================
-- 7. SPECIAL CASE: MEMBER PENDING TANPA MEMBER NUMBER
-- ================================================
-- Cek jika ada member yang stuck di pending tapi belum punya member_number
-- Ini bisa terjadi jika proses approval gagal di tengah jalan

SELECT
    mp.id,
    mp.full_name,
    mp.member_number,
    mp.membership_status,
    agu.group as current_role,
    u.active as user_active,
    mp.created_at,
    ai.secret as email,
    CASE
        WHEN agu.group = 'anggota' AND mp.member_number IS NULL THEN '❌ ANOMALI: Role sudah anggota tapi no nomor anggota'
        WHEN agu.group = 'calon_anggota' AND mp.member_number IS NULL THEN '✅ NORMAL: Masih calon anggota, belum diapprove'
        WHEN mp.member_number IS NOT NULL AND mp.membership_status = 'pending' THEN '❌ STUCK: Punya nomor tapi status pending'
        ELSE '⚠️ CHECK MANUALLY'
    END as diagnosis
FROM member_profiles mp
JOIN users u ON u.id = mp.user_id
LEFT JOIN auth_identities ai ON ai.user_id = mp.user_id AND ai.type = 'email_password'
LEFT JOIN auth_groups_users agu ON agu.user_id = mp.user_id
WHERE mp.membership_status = 'pending'
ORDER BY mp.created_at DESC;

-- ================================================
-- 8. FIX ANOMALI: Member dengan role 'anggota' tapi belum punya nomor
-- ================================================
-- Jika ada member dengan role 'anggota' tapi member_number masih NULL,
-- kita perlu generate nomor anggota untuk mereka

-- STEP 1: Cek berapa banyak member seperti ini
SELECT COUNT(*) as anomaly_count
FROM member_profiles mp
JOIN auth_groups_users agu ON agu.user_id = mp.user_id
WHERE agu.group = 'anggota'
AND mp.member_number IS NULL;

-- STEP 2: Jika ada (count > 0), generate nomor anggota
-- Dapatkan nomor anggota terakhir
SELECT member_number
FROM member_profiles
WHERE member_number IS NOT NULL
ORDER BY member_number DESC
LIMIT 1;

-- STEP 3: Manual fix - Ganti XXX dengan nomor urut berikutnya
-- Contoh: jika nomor terakhir SPK-2025-00001, maka gunakan 00002, 00003, dst
-- UNCOMMENT dan EDIT query di bawah ini untuk setiap member yang perlu di-fix:

-- UPDATE member_profiles mp
-- JOIN auth_groups_users agu ON agu.user_id = mp.user_id
-- SET
--     mp.member_number = 'SPK-2025-00002',  -- ← GANTI dengan nomor yang benar
--     mp.membership_status = 'active',
--     mp.verified_at = NOW(),
--     mp.verified_by = 1  -- ← GANTI dengan user_id admin yang meng-approve
-- WHERE mp.id = XXX  -- ← GANTI dengan ID member yang perlu di-fix
-- AND agu.group = 'anggota'
-- AND mp.member_number IS NULL;

-- ================================================
-- OPTIONAL: LIHAT DETAIL MEMBER YANG BENAR-BENAR PENDING
-- ================================================
SELECT
    mp.id,
    mp.full_name,
    mp.member_number,
    mp.membership_status,
    ai.secret as email,
    u.created_at as registered_at
FROM member_profiles mp
JOIN users u ON u.id = mp.user_id
LEFT JOIN auth_identities ai ON ai.user_id = mp.user_id AND ai.type = 'email_password'
WHERE mp.membership_status = 'pending'
AND mp.member_number IS NULL
ORDER BY u.created_at DESC;
