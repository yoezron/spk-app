-- ================================================
-- SEED MENU DATA FOR DYNAMIC SIDEBAR
-- ================================================
-- Script untuk populate tabel menus dengan data menu admin/pengurus
-- Jalankan script ini jika belum pernah run MenuSeeder
--
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin atau MySQL client
-- 2. Pilih database spk_db (atau database yang digunakan)
-- 3. Copy-paste script ini dan jalankan
-- ================================================

-- Truncate existing menu data (HATI-HATI: ini akan menghapus semua data menu yang ada)
TRUNCATE TABLE menus;

-- ================================================
-- INSERT MENU DATA
-- ================================================

-- Dashboard
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(1, NULL, 'Dashboard', 'admin/dashboard', 'admin.dashboard', 'dashboard', 'admin.dashboard', 1, 1, 'Dashboard admin untuk pengurus SPK', NOW(), NOW());

-- ================================================
-- MANAJEMEN ANGGOTA
-- ================================================
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(10, NULL, 'Kelola Anggota', '#', NULL, 'group', 'member.view', 1, 10, 'Manajemen data anggota SPK', NOW(), NOW()),
(11, 10, 'Daftar Anggota', 'admin/members', 'admin.members.index', 'list', 'member.view', 1, 1, NULL, NOW(), NOW()),
(12, 10, 'Calon Anggota', 'admin/members/pending', 'admin.members.pending', 'pending', 'member.approve', 1, 2, NULL, NOW(), NOW()),
(13, 10, 'Export Data', 'admin/members/export', 'admin.members.export', 'download', 'member.export', 1, 3, NULL, NOW(), NOW());

-- Import Anggota
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(14, NULL, 'Import Anggota', 'admin/bulk-import', 'admin.bulk.import', 'upload_file', 'member.import', 1, 11, NULL, NOW(), NOW());

-- Statistik & Laporan
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(15, NULL, 'Statistik & Laporan', 'admin/statistics', 'admin.statistics', 'analytics', 'statistics.view', 1, 12, NULL, NOW(), NOW());

-- ================================================
-- KEUANGAN
-- ================================================
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(20, NULL, 'Pembayaran', '#', NULL, 'payments', 'payment.view', 1, 20, 'Manajemen pembayaran anggota', NOW(), NOW()),
(21, 20, 'Daftar Pembayaran', 'admin/payment', 'admin.payment.index', 'list', 'payment.view', 1, 1, NULL, NOW(), NOW()),
(22, 20, 'Perlu Verifikasi', 'admin/payment/pending', 'admin.payment.pending', 'pending_actions', 'payment.verify', 1, 2, NULL, NOW(), NOW()),
(23, 20, 'Laporan Keuangan', 'admin/payment/report', 'admin.payment.report', 'receipt_long', 'payment.report', 1, 3, NULL, NOW(), NOW());

-- ================================================
-- STRUKTUR ORGANISASI
-- ================================================
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(30, NULL, 'Struktur Organisasi', '#', NULL, 'corporate_fare', 'org_structure.view', 1, 30, NULL, NOW(), NOW()),
(31, 30, 'Lihat Struktur', 'admin/org-structure', 'admin.org.structure', 'account_tree', 'org_structure.view', 1, 1, NULL, NOW(), NOW()),
(32, 30, 'Kelola Jabatan', 'admin/org-structure/manage', 'admin.org.manage', 'manage_accounts', 'org_structure.manage', 1, 2, NULL, NOW(), NOW()),
(33, 30, 'Penugasan', 'admin/org-structure/assign', 'admin.org.assign', 'assignment_ind', 'org_structure.assign', 1, 3, NULL, NOW(), NOW());

-- ================================================
-- KOMUNITAS
-- ================================================

-- Forum
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(40, NULL, 'Moderasi Forum', 'admin/forum', 'admin.forum', 'forum', 'forum.moderate', 1, 40, NULL, NOW(), NOW());

-- Survey
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(41, NULL, 'Kelola Survei', '#', NULL, 'poll', 'survey.manage', 1, 41, NULL, NOW(), NOW()),
(42, 41, 'Daftar Survei', 'admin/survey', 'admin.survey.index', 'list', 'survey.manage', 1, 1, NULL, NOW(), NOW()),
(43, 41, 'Buat Survei Baru', 'admin/survey/create', 'admin.survey.create', 'add_circle', 'survey.create', 1, 2, NULL, NOW(), NOW()),
(44, 41, 'Lihat Respon', 'admin/survey/responses', 'admin.survey.responses', 'ballot', 'survey.view_results', 1, 3, NULL, NOW(), NOW());

-- Pengaduan/Complaint
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(45, NULL, 'Pengaduan', 'admin/complaint', 'admin.complaint', 'support', 'complaint.view', 1, 45, NULL, NOW(), NOW());

-- WhatsApp Groups
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(46, NULL, 'WhatsApp Groups', 'admin/wa-groups', 'admin.wa.groups', 'groups', 'wa_group.manage', 1, 46, NULL, NOW(), NOW());

-- ================================================
-- KONTEN
-- ================================================
INSERT INTO menus (id, parent_id, title, url, route_name, icon, permission_key, is_active, sort_order, description, created_at, updated_at) VALUES
(50, NULL, 'Konten & Blog', '#', NULL, 'article', 'content.manage', 1, 50, NULL, NOW(), NOW()),
(51, 50, 'Artikel/Blog', 'admin/content/posts', 'admin.content.posts', 'article', 'content.manage', 1, 1, NULL, NOW(), NOW()),
(52, 50, 'Halaman Statis', 'admin/content/pages', 'admin.content.pages', 'web', 'content.manage', 1, 2, NULL, NOW(), NOW()),
(53, 50, 'Kategori', 'admin/content/categories', 'admin.content.categories', 'category', 'content.manage', 1, 3, NULL, NOW(), NOW());

-- ================================================
-- VERIFIKASI DATA
-- ================================================
-- Cek berapa banyak menu yang di-insert
SELECT 'Total menus inserted:' as info, COUNT(*) as count FROM menus;

-- Cek menu parent (top level)
SELECT 'Parent menus:' as info, COUNT(*) as count FROM menus WHERE parent_id IS NULL;

-- Cek menu child (sub-menu)
SELECT 'Child menus:' as info, COUNT(*) as count FROM menus WHERE parent_id IS NOT NULL;

-- Lihat sample menu dengan permission
SELECT id, parent_id, title, permission_key, is_active
FROM menus
WHERE permission_key IS NOT NULL
ORDER BY sort_order
LIMIT 10;
