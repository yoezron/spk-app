-- ================================================
-- FIX: LOGIN REDIRECT ISSUE - GROUP NAME CASE
-- ================================================
-- Script untuk memperbaiki redirect issue di mana user dengan role
-- Pengurus di-redirect ke /member/dashboard instead of /admin/dashboard
--
-- ROOT CAUSE:
-- - Database menyimpan group sebagai 'Pengurus' (Title case)
-- - Kode PHP mengecek $user->inGroup('pengurus') (lowercase)
-- - Mismatch ini menyebabkan kondisi if ($user->inGroup('pengurus'))
--   SELALU RETURN FALSE, sehingga user jatuh ke default redirect
--
-- SOLUSI:
-- Update semua group name dari Title case ke lowercase untuk
-- match dengan AuthGroups config
--
-- CARA MENJALANKAN:
-- 1. BACKUP DATABASE DULU!
-- 2. Jalankan diagnose_login_redirect.sql dulu untuk confirm masalah
-- 3. Jika confirmed, jalankan script ini di phpMyAdmin
-- ================================================

-- ================================================
-- BACKUP: LIHAT DATA SEBELUM UPDATE
-- ================================================
SELECT
    u.id,
    u.username,
    u.email,
    agu.group as current_group,
    'Will be changed to lowercase' as action
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE agu.group != LOWER(agu.group)  -- Cari yang bukan lowercase
ORDER BY agu.group;

-- ================================================
-- FIX: UPDATE SEMUA GROUP KE LOWERCASE
-- ================================================
-- Ini akan update:
-- - 'Pengurus' -> 'pengurus'
-- - 'Anggota' -> 'anggota'
-- - 'Calon Anggota' -> 'calon_anggota' (KHUSUS CASE INI)
-- - 'Super Admin' -> 'superadmin' (KHUSUS CASE INI)
-- - 'Koordinator' -> 'koordinator'
-- ================================================

-- Update Pengurus
UPDATE auth_groups_users
SET `group` = 'pengurus'
WHERE `group` IN ('Pengurus', 'PENGURUS', 'pengurus ');  -- Include typos

-- Update Anggota
UPDATE auth_groups_users
SET `group` = 'anggota'
WHERE `group` IN ('Anggota', 'ANGGOTA', 'anggota ');

-- Update Calon Anggota (IMPORTANT: underscore not space!)
UPDATE auth_groups_users
SET `group` = 'calon_anggota'
WHERE `group` IN ('Calon Anggota', 'calon anggota', 'Calon anggota', 'CALON_ANGGOTA');

-- Update Super Admin (IMPORTANT: no space!)
UPDATE auth_groups_users
SET `group` = 'superadmin'
WHERE `group` IN ('Super Admin', 'super admin', 'SuperAdmin', 'SUPERADMIN', 'super_admin');

-- Update Koordinator
UPDATE auth_groups_users
SET `group` = 'koordinator'
WHERE `group` IN ('Koordinator', 'KOORDINATOR', 'koordinator ', 'Koordinator Wilayah', 'koordinator_wilayah');

-- Update User
UPDATE auth_groups_users
SET `group` = 'user'
WHERE `group` IN ('User', 'USER', 'user ');

-- ================================================
-- VERIFIKASI: CEK HASIL UPDATE
-- ================================================
-- Seharusnya semua group sekarang lowercase
SELECT
    `group` as group_name,
    COUNT(*) as user_count,
    CASE
        WHEN `group` = LOWER(`group`) THEN '✅ CORRECT (lowercase)'
        ELSE '❌ STILL WRONG (not lowercase)'
    END as status
FROM auth_groups_users
GROUP BY `group`
ORDER BY `group`;

-- ================================================
-- VERIFIKASI: CEK USER PENGURUS
-- ================================================
SELECT
    u.username,
    u.email,
    agu.group,
    CASE
        WHEN agu.group = 'pengurus' THEN '✅ FIXED'
        ELSE '❌ STILL WRONG'
    END as status
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE agu.group LIKE '%pengurus%' OR agu.group LIKE '%Pengurus%'
ORDER BY u.id;

-- ================================================
-- TEST QUERY: SIMULATE inGroup('pengurus')
-- ================================================
-- Query ini simulate apa yang dilakukan kode PHP
SELECT
    u.id,
    u.username,
    u.email,
    agu.group,
    CASE
        WHEN agu.group = 'pengurus' THEN 'WILL REDIRECT TO /admin/dashboard ✅'
        ELSE 'WILL FALL TO DEFAULT /member/dashboard ❌'
    END as redirect_behavior
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
AND (agu.group LIKE '%pengurus%' OR agu.group LIKE '%Pengurus%');

-- ================================================
-- EXPECTED RESULT AFTER FIX
-- ================================================
-- Semua user dengan role pengurus sekarang punya agu.group = 'pengurus'
-- Ketika login:
-- 1. LoginController->redirectBasedOnRole() dijalankan
-- 2. $user->inGroup('pengurus') return TRUE
-- 3. User di-redirect ke /admin/dashboard ✅
-- 4. Admin sidebar dengan menu pengurus ditampilkan
-- ================================================

-- ================================================
-- IMPORTANT: AFTER RUNNING THIS FIX
-- ================================================
-- 1. User dengan role Pengurus harus LOGOUT
-- 2. Clear browser cache (Ctrl+Shift+Delete)
-- 3. LOGIN ULANG
-- 4. Seharusnya di-redirect ke /admin/dashboard
-- 5. Sidebar menampilkan menu sesuai permission pengurus
-- ================================================
