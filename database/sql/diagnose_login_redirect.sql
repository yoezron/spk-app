-- ================================================
-- DIAGNOSTIC: LOGIN REDIRECT ISSUE (FIXED VERSION)
-- ================================================
-- Script untuk mengecek kenapa user dengan role Pengurus
-- di-redirect ke /member/dashboard padahal seharusnya ke /admin/dashboard
--
-- UPDATED: Fixed untuk struktur tabel CodeIgniter Shield
-- - Email disimpan di auth_identities, BUKAN di users
-- - users table hanya punya: id, username, status, active
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin
-- 2. Pilih database spk_db
-- 3. Copy-paste dan jalankan query ini satu per satu
-- 4. Share hasil query ke developer
-- ================================================

-- ================================================
-- 1. CEK STRUKTUR TABEL
-- ================================================
DESCRIBE users;
DESCRIBE auth_identities;
DESCRIBE auth_groups_users;

-- ================================================
-- 2. LIHAT SEMUA USER DENGAN ROLE-NYA (WITH EMAIL)
-- ================================================
-- Ini akan menunjukkan apakah group disimpan sebagai 'pengurus' atau 'Pengurus'
SELECT
    u.id,
    u.username,
    ai.secret as email,
    agu.group as role_stored_in_db,
    LENGTH(agu.group) as role_length,
    u.active,
    agu.created_at as role_assigned_at
FROM users u
LEFT JOIN auth_identities ai ON ai.user_id = u.id AND ai.type = 'email_password'
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
ORDER BY agu.group, u.username;

-- ================================================
-- 3. CEK APAKAH ADA ROLE 'pengurus' (lowercase)
-- ================================================
SELECT
    COUNT(*) as total_pengurus_lowercase,
    'Ini yang BENAR sesuai config' as note
FROM auth_groups_users
WHERE `group` = 'pengurus';  -- lowercase

-- ================================================
-- 4. CEK APAKAH ADA ROLE 'Pengurus' (Title Case)
-- ================================================
SELECT
    COUNT(*) as total_pengurus_titlecase,
    'Ini yang SALAH - menyebabkan redirect error' as note
FROM auth_groups_users
WHERE `group` = 'Pengurus';  -- Title case

-- ================================================
-- 5. CEK SEMUA UNIQUE GROUP VALUES
-- ================================================
-- Ini akan menunjukkan SEMUA nilai group yang ada di database
SELECT DISTINCT
    `group` as group_value,
    CONCAT('Length: ', LENGTH(`group`)) as info,
    COUNT(*) as user_count,
    CASE
        WHEN `group` = LOWER(REPLACE(REPLACE(`group`, ' ', '_'), 'super admin', 'superadmin')) THEN '✅ CORRECT'
        ELSE '❌ WRONG (should be lowercase + underscore)'
    END as status
FROM auth_groups_users
GROUP BY `group`
ORDER BY `group`;

-- ================================================
-- 6. DETAIL USER PENGURUS (YANG BERMASALAH)
-- ================================================
SELECT
    u.id,
    u.username,
    ai.secret as email,
    agu.group as role_stored,
    u.active,
    CASE
        WHEN agu.group = 'pengurus' THEN '✅ CORRECT (lowercase)'
        WHEN agu.group = 'Pengurus' THEN '❌ WRONG (Title case - should be lowercase)'
        ELSE '⚠️ UNKNOWN'
    END as diagnosis,
    CASE
        WHEN agu.group = 'pengurus' THEN 'WILL REDIRECT TO: /admin/dashboard ✅'
        WHEN agu.group = 'Pengurus' THEN 'WILL REDIRECT TO: /member/dashboard ❌ (WRONG!)'
        ELSE 'REDIRECT UNCERTAIN'
    END as redirect_behavior
FROM users u
LEFT JOIN auth_identities ai ON ai.user_id = u.id AND ai.type = 'email_password'
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
AND (agu.group LIKE '%pengurus%' OR agu.group LIKE '%Pengurus%')
ORDER BY u.id DESC;

-- ================================================
-- 7. CEK SEMUA ROLE DENGAN EXPECTED vs ACTUAL
-- ================================================
SELECT
    `group` as actual_in_database,
    CASE
        WHEN `group` = 'superadmin' THEN '✅ superadmin'
        WHEN `group` = 'Super Admin' THEN '❌ Super Admin → should be: superadmin'
        WHEN `group` = 'pengurus' THEN '✅ pengurus'
        WHEN `group` = 'Pengurus' THEN '❌ Pengurus → should be: pengurus'
        WHEN `group` = 'koordinator' THEN '✅ koordinator'
        WHEN `group` = 'Koordinator Wilayah' THEN '❌ Koordinator Wilayah → should be: koordinator'
        WHEN `group` = 'koordinator_wilayah' THEN '⚠️ koordinator_wilayah → should be: koordinator'
        WHEN `group` = 'anggota' THEN '✅ anggota'
        WHEN `group` = 'Anggota' THEN '❌ Anggota → should be: anggota'
        WHEN `group` = 'calon_anggota' THEN '✅ calon_anggota'
        WHEN `group` = 'Calon Anggota' THEN '❌ Calon Anggota → should be: calon_anggota'
        ELSE '⚠️ Unknown role'
    END as diagnosis,
    COUNT(*) as user_count
FROM auth_groups_users
GROUP BY `group`
ORDER BY `group`;

-- ================================================
-- 8. SIMULATE LOGIN REDIRECT LOGIC
-- ================================================
-- Query ini simulate apa yang dilakukan LoginController
SELECT
    u.id,
    u.username,
    ai.secret as email,
    agu.group as current_role,
    CASE
        WHEN agu.group = 'superadmin' THEN '/super/dashboard'
        WHEN agu.group = 'Super Admin' THEN '/member/dashboard (WRONG! Should go to /super/dashboard)'
        WHEN agu.group = 'pengurus' THEN '/admin/dashboard'
        WHEN agu.group = 'Pengurus' THEN '/member/dashboard (WRONG! Should go to /admin/dashboard)'
        WHEN agu.group = 'koordinator' THEN '/admin/dashboard'
        WHEN agu.group = 'Koordinator Wilayah' THEN '/member/dashboard (WRONG! Should go to /admin/dashboard)'
        WHEN agu.group = 'koordinator_wilayah' THEN '/member/dashboard (WRONG! Should use: koordinator)'
        WHEN agu.group = 'anggota' THEN '/member/dashboard'
        WHEN agu.group = 'Anggota' THEN '/member/dashboard (but by accident, should be lowercase)'
        WHEN agu.group = 'calon_anggota' THEN '/member/dashboard'
        WHEN agu.group = 'Calon Anggota' THEN '/member/dashboard (but by accident, should be lowercase)'
        ELSE '/member/dashboard (DEFAULT - role not recognized)'
    END as redirect_target,
    CASE
        WHEN agu.group = LOWER(REPLACE(REPLACE(agu.group, ' ', '_'), 'super admin', 'superadmin')) THEN '✅ OK'
        ELSE '❌ WILL CAUSE REDIRECT ERROR!'
    END as status
FROM users u
LEFT JOIN auth_identities ai ON ai.user_id = u.id AND ai.type = 'email_password'
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
ORDER BY agu.group, u.username;

-- ================================================
-- HASIL YANG DIHARAPKAN:
-- ================================================
-- Query #3 harus return angka > 0 (ada user dengan group = 'pengurus')
-- Query #4 harus return 0 (TIDAK ADA user dengan group = 'Pengurus')
--
-- JIKA SEBALIKNYA (query #4 return > 0):
-- Artinya database menyimpan 'Pengurus' (Title case)
-- Sedangkan kode PHP mengecek 'pengurus' (lowercase)
-- SOLUSI: Jalankan fix_login_redirect_group_case.sql
-- ================================================

-- ================================================
-- QUICK FIX (OPTIONAL - untuk testing cepat)
-- ================================================
-- Jika Anda ingin langsung coba fix untuk 1 user:
--
-- UPDATE auth_groups_users
-- SET `group` = 'pengurus'
-- WHERE user_id = YOUR_USER_ID_HERE
-- AND `group` = 'Pengurus';
--
-- Lalu logout, clear cache, dan login ulang
-- ================================================
