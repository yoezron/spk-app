-- ================================================
-- FIX: ASSIGN MISSING PERMISSIONS TO PENGURUS
-- ================================================
-- Script untuk assign permission yang kurang ke role Pengurus
-- sehingga semua menu yang seharusnya muncul bisa ditampilkan
--
-- PERMISSIONS YANG AKAN DI-ASSIGN:
-- - admin.dashboard (untuk Dashboard menu)
-- - payment.* (untuk menu Pembayaran)
-- - statistics.* (untuk menu Statistik & Laporan)
-- - org_structure.* (untuk menu Struktur Organisasi)
-- - wa_group.manage (untuk menu WhatsApp Groups)
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin
-- 2. Pilih database spk_db
-- 3. Copy-paste script ini dan jalankan
-- ================================================

-- ================================================
-- 1. CEK PERMISSION IDS YANG DIBUTUHKAN
-- ================================================
SELECT
    id,
    name as permission_key,
    description
FROM auth_permissions
WHERE name IN (
    'admin.dashboard',
    'payment.view',
    'payment.verify',
    'payment.report',
    'payment.export',
    'statistics.view',
    'statistics.export',
    'stats.view',
    'stats.export',
    'org_structure.view',
    'org_structure.manage',
    'org_structure.assign',
    'wa_group.manage',
    'wagroup.manage'
)
ORDER BY name;

-- ================================================
-- 2. ASSIGN PERMISSIONS KE PENGURUS (group_id = 2)
-- ================================================
-- Insert hanya jika belum ada (avoid duplicate)

INSERT INTO auth_groups_permissions (group_id, permission_id, created_at)
SELECT 2, ap.id, NOW()
FROM auth_permissions ap
WHERE ap.name IN (
    'admin.dashboard',
    'payment.view',
    'payment.verify',
    'payment.report',
    'payment.export',
    'statistics.view',
    'statistics.export',
    'stats.view',
    'stats.export',
    'org_structure.view',
    'org_structure.manage',
    'org_structure.assign',
    'wa_group.manage',
    'wagroup.manage'
)
AND NOT EXISTS (
    -- Jangan duplikasi jika sudah ada
    SELECT 1
    FROM auth_groups_permissions agp
    WHERE agp.group_id = 2
    AND agp.permission_id = ap.id
);

-- ================================================
-- 3. VERIFIKASI: CEK BERAPA PERMISSION YANG DI-ADD
-- ================================================
SELECT
    'Permissions added' as status,
    COUNT(*) as count
FROM auth_groups_permissions
WHERE group_id = 2
AND created_at >= NOW() - INTERVAL 1 MINUTE;

-- ================================================
-- 4. VERIFIKASI: LIHAT SEMUA PERMISSION PENGURUS
-- ================================================
SELECT
    ap.name as permission_key,
    ap.description
FROM auth_groups_permissions agp
JOIN auth_permissions ap ON ap.id = agp.permission_id
WHERE agp.group_id = 2
ORDER BY ap.name;

-- ================================================
-- 5. VERIFIKASI: CEK MENU YANG SEKARANG VISIBLE
-- ================================================
SELECT
    m.id,
    m.title,
    m.permission_key,
    CASE
        WHEN m.permission_key IS NULL THEN '✅ PUBLIC'
        WHEN m.permission_key IN (
            SELECT ap.name
            FROM auth_groups_permissions agp
            JOIN auth_permissions ap ON ap.id = agp.permission_id
            WHERE agp.group_id = 2
        ) THEN '✅ VISIBLE'
        ELSE '❌ HIDDEN'
    END as status
FROM menus m
WHERE m.is_active = 1
ORDER BY m.sort_order;

-- ================================================
-- 6. SUMMARY: BERAPA MENU VISIBLE vs HIDDEN
-- ================================================
SELECT
    SUM(CASE WHEN visibility = 'VISIBLE' THEN 1 ELSE 0 END) as visible_menus,
    SUM(CASE WHEN visibility = 'HIDDEN' THEN 1 ELSE 0 END) as hidden_menus,
    COUNT(*) as total_menus
FROM (
    SELECT
        CASE
            WHEN m.permission_key IS NULL THEN 'VISIBLE'
            WHEN m.permission_key IN (
                SELECT ap.name
                FROM auth_groups_permissions agp
                JOIN auth_permissions ap ON ap.id = agp.permission_id
                WHERE agp.group_id = 2
            ) THEN 'VISIBLE'
            ELSE 'HIDDEN'
        END as visibility
    FROM menus m
    WHERE m.is_active = 1
) as menu_visibility;

-- ================================================
-- 7. EXPECTED RESULT
-- ================================================
-- Setelah script ini dijalankan, Pengurus seharusnya:
-- - Punya ~70+ permissions (dari 57 menjadi 70+)
-- - Visible menus: 24-26 dari 26 total menus
-- - Hidden menus: 0-2 (hanya menu superadmin saja)
--
-- Menu yang sekarang seharusnya MUNCUL:
-- ✅ Dashboard
-- ✅ Pembayaran (dengan 3 sub-menu)
-- ✅ Statistik & Laporan
-- ✅ Struktur Organisasi (dengan 3 sub-menu)
-- ✅ WhatsApp Groups
-- ================================================
