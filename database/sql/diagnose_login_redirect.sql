-- ================================================
-- DIAGNOSTIC: LOGIN REDIRECT ISSUE
-- ================================================
-- Script untuk mengecek kenapa user dengan role Pengurus
-- di-redirect ke /member/dashboard padahal seharusnya ke /admin/dashboard
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin
-- 2. Pilih database spk_db
-- 3. Copy-paste dan jalankan query ini satu per satu
-- 4. Share hasil query ke developer
-- ================================================

-- ================================================
-- 1. CEK STRUKTUR TABEL auth_groups_users
-- ================================================
DESCRIBE auth_groups_users;

-- ================================================
-- 2. LIHAT SEMUA USER DENGAN ROLE-NYA
-- ================================================
-- Ini akan menunjukkan apakah group disimpan sebagai 'pengurus' atau 'Pengurus'
SELECT
    u.id,
    u.username,
    u.email,
    agu.group as role_stored_in_db,
    LENGTH(agu.group) as role_length,
    agu.created_at as role_assigned_at
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
ORDER BY agu.group, u.username;

-- ================================================
-- 3. CEK APAKAH ADA ROLE 'pengurus' (lowercase)
-- ================================================
SELECT
    COUNT(*) as total_pengurus_lowercase
FROM auth_groups_users
WHERE `group` = 'pengurus';  -- lowercase

-- ================================================
-- 4. CEK APAKAH ADA ROLE 'Pengurus' (Title Case)
-- ================================================
SELECT
    COUNT(*) as total_pengurus_titlecase
FROM auth_groups_users
WHERE `group` = 'Pengurus';  -- Title case

-- ================================================
-- 5. CEK SEMUA UNIQUE GROUP VALUES
-- ================================================
-- Ini akan menunjukkan SEMUA nilai group yang ada di database
SELECT DISTINCT
    `group` as group_value,
    CONCAT('Length: ', LENGTH(`group`)) as info,
    COUNT(*) as user_count
FROM auth_groups_users
GROUP BY `group`
ORDER BY `group`;

-- ================================================
-- 6. DETAIL USER YANG JADI MASALAH
-- ================================================
-- Ganti 'USERNAME_DISINI' dengan username pengurus yang bermasalah
-- Misalnya: WHERE u.username = 'pengurus1'
SELECT
    u.id,
    u.username,
    u.email,
    agu.group as role_stored,
    CASE
        WHEN agu.group = 'pengurus' THEN '✅ CORRECT (lowercase)'
        WHEN agu.group = 'Pengurus' THEN '❌ WRONG (Title case - should be lowercase)'
        ELSE '⚠️ UNKNOWN'
    END as diagnosis
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
-- Uncomment dan ganti dengan username yang bermasalah:
-- AND u.username = 'USERNAME_DISINI'
ORDER BY u.id DESC
LIMIT 10;

-- ================================================
-- 7. CEK TABEL auth_groups (Master Data)
-- ================================================
-- Seharusnya tidak ada tabel ini di Shield, tapi cek aja
SHOW TABLES LIKE 'auth_groups';

-- ================================================
-- HASIL YANG DIHARAPKAN:
-- ================================================
-- Query #3 harus return angka > 0 (ada user dengan group = 'pengurus')
-- Query #4 harus return 0 (TIDAK ADA user dengan group = 'Pengurus')
--
-- JIKA SEBALIKNYA (query #4 return > 0):
-- Artinya database menyimpan 'Pengurus' (Title case)
-- Sedangkan kode PHP mengecek 'pengurus' (lowercase)
-- SOLUSI: Update semua group dari 'Pengurus' ke 'pengurus'
-- ================================================
