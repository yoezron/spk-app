-- ================================================
-- DIAGNOSTIC: PENGURUS PERMISSIONS VS MENUS
-- ================================================
-- Script untuk mengecek kenapa menu tidak muncul meskipun
-- permission sudah di-assign ke role Pengurus (group_id=2)
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin
-- 2. Pilih database spk_db
-- 3. Copy-paste dan jalankan query ini satu per satu
-- ================================================

-- ================================================
-- 1. CEK BERAPA MENU YANG ADA DI DATABASE
-- ================================================
SELECT
    COUNT(*) as total_menus,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_menus,
    SUM(CASE WHEN permission_key IS NOT NULL THEN 1 ELSE 0 END) as menus_with_permission
FROM menus;

-- ================================================
-- 2. LIHAT SEMUA PERMISSION YANG DIMILIKI PENGURUS
-- ================================================
-- Pengurus = group_id 2
SELECT
    ap.id,
    ap.name as permission_key,
    ap.description
FROM auth_groups_permissions agp
JOIN auth_permissions ap ON ap.id = agp.permission_id
WHERE agp.group_id = 2
ORDER BY ap.name;

-- ================================================
-- 3. CEK MENU APA SAJA YANG ADA DI DATABASE
-- ================================================
SELECT
    id,
    parent_id,
    title,
    url,
    permission_key,
    is_active,
    sort_order
FROM menus
WHERE is_active = 1
ORDER BY sort_order;

-- ================================================
-- 4. CROSS-CHECK: PERMISSION PENGURUS vs MENU
-- ================================================
-- Permissions yang dimiliki Pengurus TAPI TIDAK ADA MENU-nya
SELECT
    ap.id,
    ap.name as permission_key,
    ap.description,
    'NO MENU FOUND' as status
FROM auth_groups_permissions agp
JOIN auth_permissions ap ON ap.id = agp.permission_id
WHERE agp.group_id = 2
AND ap.name NOT IN (
    SELECT DISTINCT permission_key
    FROM menus
    WHERE permission_key IS NOT NULL
)
ORDER BY ap.name;

-- ================================================
-- 5. MENU YANG SEHARUSNYA MUNCUL UNTUK PENGURUS
-- ================================================
-- Menu yang permission_key-nya dimiliki oleh Pengurus
SELECT
    m.id,
    m.parent_id,
    m.title,
    m.url,
    m.permission_key,
    m.is_active,
    CASE
        WHEN m.permission_key IS NULL THEN 'PUBLIC (Always Show)'
        WHEN m.permission_key IN (
            SELECT ap.name
            FROM auth_groups_permissions agp
            JOIN auth_permissions ap ON ap.id = agp.permission_id
            WHERE agp.group_id = 2
        ) THEN 'VISIBLE FOR PENGURUS'
        ELSE 'HIDDEN (No Permission)'
    END as visibility_status
FROM menus m
WHERE m.is_active = 1
ORDER BY m.sort_order;

-- ================================================
-- 6. SUMMARY: BERAPA MENU YANG SEHARUSNYA MUNCUL
-- ================================================
SELECT
    'Total Active Menus' as metric,
    COUNT(*) as count
FROM menus
WHERE is_active = 1

UNION ALL

SELECT
    'Menus Without Permission (Public)' as metric,
    COUNT(*) as count
FROM menus
WHERE is_active = 1 AND permission_key IS NULL

UNION ALL

SELECT
    'Menus Visible for Pengurus' as metric,
    COUNT(*) as count
FROM menus m
WHERE m.is_active = 1
AND (
    m.permission_key IS NULL
    OR m.permission_key IN (
        SELECT ap.name
        FROM auth_groups_permissions agp
        JOIN auth_permissions ap ON ap.id = agp.permission_id
        WHERE agp.group_id = 2
    )
);

-- ================================================
-- 7. DETAIL: MENU MANA SAJA YANG HIDDEN
-- ================================================
SELECT
    m.id,
    m.title,
    m.permission_key,
    'HIDDEN - User tidak punya permission ini' as reason
FROM menus m
WHERE m.is_active = 1
AND m.permission_key IS NOT NULL
AND m.permission_key NOT IN (
    SELECT ap.name
    FROM auth_groups_permissions agp
    JOIN auth_permissions ap ON ap.id = agp.permission_id
    WHERE agp.group_id = 2
)
ORDER BY m.sort_order;

-- ================================================
-- 8. CEK: APAKAH TABEL MENUS KOSONG?
-- ================================================
SELECT
    CASE
        WHEN COUNT(*) = 0 THEN '⚠️ MASALAH: Tabel menus KOSONG! Jalankan seed_menus.sql'
        WHEN COUNT(*) < 20 THEN '⚠️ WARNING: Tabel menus hanya ada sedikit data'
        ELSE '✅ OK: Tabel menus punya data yang cukup'
    END as status,
    COUNT(*) as total_menus
FROM menus;
