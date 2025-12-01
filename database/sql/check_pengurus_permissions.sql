-- ================================================
-- CHECK PENGURUS ROLE PERMISSIONS
-- ================================================
-- Script untuk memeriksa permission apa saja yang sudah
-- diassign ke role "Pengurus"
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin atau MySQL client
-- 2. Pilih database spk_db (atau database yang digunakan)
-- 3. Copy-paste script ini dan jalankan
-- ================================================

-- ================================================
-- 1. CEK ROLE PENGURUS
-- ================================================
SELECT
    id,
    title,
    description
FROM auth_groups
WHERE title = 'Pengurus' OR title = 'pengurus';

-- ================================================
-- 2. CEK SEMUA PERMISSIONS YANG TERSEDIA
-- ================================================
SELECT
    id,
    name as permission_key,
    description
FROM auth_permissions
ORDER BY name;

-- ================================================
-- 3. CEK PERMISSIONS YANG SUDAH DIASSIGN KE PENGURUS
-- ================================================
SELECT
    ag.title as role_name,
    ap.name as permission_key,
    ap.description as permission_description,
    agp.created_at as assigned_at
FROM auth_groups_permissions agp
JOIN auth_groups ag ON ag.id = agp.group_id
JOIN auth_permissions ap ON ap.id = agp.permission_id
WHERE ag.title IN ('Pengurus', 'pengurus')
ORDER BY ap.name;

-- ================================================
-- 4. CEK PERMISSIONS YANG BELUM DIASSIGN KE PENGURUS
-- ================================================
SELECT
    ap.name as permission_key,
    ap.description as permission_description
FROM auth_permissions ap
WHERE ap.id NOT IN (
    SELECT agp.permission_id
    FROM auth_groups_permissions agp
    JOIN auth_groups ag ON ag.id = agp.group_id
    WHERE ag.title IN ('Pengurus', 'pengurus')
)
ORDER BY ap.name;

-- ================================================
-- 5. SUMMARY: BERAPA PERMISSIONS YANG SUDAH/BELUM ASSIGNED
-- ================================================
SELECT
    'Total Available Permissions' as info,
    COUNT(*) as count
FROM auth_permissions

UNION ALL

SELECT
    'Permissions Assigned to Pengurus' as info,
    COUNT(*) as count
FROM auth_groups_permissions agp
JOIN auth_groups ag ON ag.id = agp.group_id
WHERE ag.title IN ('Pengurus', 'pengurus')

UNION ALL

SELECT
    'Permissions NOT Assigned to Pengurus' as info,
    COUNT(*) as count
FROM auth_permissions ap
WHERE ap.id NOT IN (
    SELECT agp.permission_id
    FROM auth_groups_permissions agp
    JOIN auth_groups ag ON ag.id = agp.group_id
    WHERE ag.title IN ('Pengurus', 'pengurus')
);

-- ================================================
-- 6. CEK MENU VS PERMISSIONS (DEBUG)
-- ================================================
-- Menu yang permission_key-nya tidak ada di auth_permissions
SELECT
    m.id,
    m.title as menu_title,
    m.permission_key as menu_permission,
    'PERMISSION NOT FOUND!' as status
FROM menus m
WHERE m.permission_key IS NOT NULL
AND m.permission_key NOT IN (
    SELECT name FROM auth_permissions
)
ORDER BY m.sort_order;
