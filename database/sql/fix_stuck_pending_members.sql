-- ================================================
-- FIX: Stuck Pending Members
-- ================================================
-- Script untuk memperbaiki member yang sudah approved tapi
-- masih muncul di halaman pending karena membership_status
-- tidak ter-update dengan benar
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin
-- 2. Pilih database spk_db
-- 3. Copy-paste dan jalankan query ini satu per satu
-- ================================================

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
