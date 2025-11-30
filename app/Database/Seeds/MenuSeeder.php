<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MenuSeeder
 *
 * Populates menus table with dynamic menu structure
 * Linked to permissions for role-based access control
 *
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MenuSeeder extends Seeder
{
    public function run()
    {
        // ========================================
        // ADMIN/PENGURUS MENUS
        // ========================================

        $menus = [
            // Dashboard
            [
                'id' => 1,
                'parent_id' => null,
                'title' => 'Dashboard',
                'url' => 'admin/dashboard',
                'route_name' => 'admin.dashboard',
                'icon' => 'dashboard',
                'permission_key' => 'admin.dashboard',
                'is_active' => 1,
                'sort_order' => 1,
                'description' => 'Dashboard admin untuk pengurus SPK',
            ],

            // ========================================
            // MANAJEMEN ANGGOTA
            // ========================================
            [
                'id' => 10,
                'parent_id' => null,
                'title' => 'Kelola Anggota',
                'url' => '#',
                'icon' => 'group',
                'permission_key' => 'member.view',
                'is_active' => 1,
                'sort_order' => 10,
                'description' => 'Manajemen data anggota SPK',
            ],
            [
                'id' => 11,
                'parent_id' => 10,
                'title' => 'Daftar Anggota',
                'url' => 'admin/members',
                'route_name' => 'admin.members.index',
                'icon' => 'list',
                'permission_key' => 'member.view',
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 12,
                'parent_id' => 10,
                'title' => 'Calon Anggota',
                'url' => 'admin/members/pending',
                'route_name' => 'admin.members.pending',
                'icon' => 'pending',
                'permission_key' => 'member.approve',
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'id' => 13,
                'parent_id' => 10,
                'title' => 'Export Data',
                'url' => 'admin/members/export',
                'route_name' => 'admin.members.export',
                'icon' => 'download',
                'permission_key' => 'member.export',
                'is_active' => 1,
                'sort_order' => 3,
            ],

            // Import Anggota
            [
                'id' => 14,
                'parent_id' => null,
                'title' => 'Import Anggota',
                'url' => 'admin/bulk-import',
                'route_name' => 'admin.bulk.import',
                'icon' => 'upload_file',
                'permission_key' => 'member.import',
                'is_active' => 1,
                'sort_order' => 11,
            ],

            // Statistik & Laporan
            [
                'id' => 15,
                'parent_id' => null,
                'title' => 'Statistik & Laporan',
                'url' => 'admin/statistics',
                'route_name' => 'admin.statistics',
                'icon' => 'analytics',
                'permission_key' => 'statistics.view',
                'is_active' => 1,
                'sort_order' => 12,
            ],

            // ========================================
            // KEUANGAN
            // ========================================
            [
                'id' => 20,
                'parent_id' => null,
                'title' => 'Pembayaran',
                'url' => '#',
                'icon' => 'payments',
                'permission_key' => 'payment.view',
                'is_active' => 1,
                'sort_order' => 20,
                'description' => 'Manajemen pembayaran anggota',
            ],
            [
                'id' => 21,
                'parent_id' => 20,
                'title' => 'Daftar Pembayaran',
                'url' => 'admin/payment',
                'route_name' => 'admin.payment.index',
                'icon' => 'list',
                'permission_key' => 'payment.view',
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 22,
                'parent_id' => 20,
                'title' => 'Perlu Verifikasi',
                'url' => 'admin/payment/pending',
                'route_name' => 'admin.payment.pending',
                'icon' => 'pending_actions',
                'permission_key' => 'payment.verify',
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'id' => 23,
                'parent_id' => 20,
                'title' => 'Laporan Keuangan',
                'url' => 'admin/payment/report',
                'route_name' => 'admin.payment.report',
                'icon' => 'receipt_long',
                'permission_key' => 'payment.report',
                'is_active' => 1,
                'sort_order' => 3,
            ],

            // ========================================
            // STRUKTUR ORGANISASI
            // ========================================
            [
                'id' => 30,
                'parent_id' => null,
                'title' => 'Struktur Organisasi',
                'url' => '#',
                'icon' => 'corporate_fare',
                'permission_key' => 'org_structure.view',
                'is_active' => 1,
                'sort_order' => 30,
            ],
            [
                'id' => 31,
                'parent_id' => 30,
                'title' => 'Lihat Struktur',
                'url' => 'admin/org-structure',
                'route_name' => 'admin.org.structure',
                'icon' => 'account_tree',
                'permission_key' => 'org_structure.view',
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 32,
                'parent_id' => 30,
                'title' => 'Kelola Jabatan',
                'url' => 'admin/org-structure/manage',
                'route_name' => 'admin.org.manage',
                'icon' => 'manage_accounts',
                'permission_key' => 'org_structure.manage',
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'id' => 33,
                'parent_id' => 30,
                'title' => 'Penugasan',
                'url' => 'admin/org-structure/assign',
                'route_name' => 'admin.org.assign',
                'icon' => 'assignment_ind',
                'permission_key' => 'org_structure.assign',
                'is_active' => 1,
                'sort_order' => 3,
            ],

            // ========================================
            // KOMUNITAS
            // ========================================

            // Forum
            [
                'id' => 40,
                'parent_id' => null,
                'title' => 'Moderasi Forum',
                'url' => 'admin/forum',
                'route_name' => 'admin.forum',
                'icon' => 'forum',
                'permission_key' => 'forum.moderate',
                'is_active' => 1,
                'sort_order' => 40,
            ],

            // Survey
            [
                'id' => 41,
                'parent_id' => null,
                'title' => 'Kelola Survei',
                'url' => '#',
                'icon' => 'poll',
                'permission_key' => 'survey.manage',
                'is_active' => 1,
                'sort_order' => 41,
            ],
            [
                'id' => 42,
                'parent_id' => 41,
                'title' => 'Daftar Survei',
                'url' => 'admin/survey',
                'route_name' => 'admin.survey.index',
                'icon' => 'list',
                'permission_key' => 'survey.manage',
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 43,
                'parent_id' => 41,
                'title' => 'Buat Survei Baru',
                'url' => 'admin/survey/create',
                'route_name' => 'admin.survey.create',
                'icon' => 'add_circle',
                'permission_key' => 'survey.create',
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'id' => 44,
                'parent_id' => 41,
                'title' => 'Lihat Respon',
                'url' => 'admin/survey/responses',
                'route_name' => 'admin.survey.responses',
                'icon' => 'ballot',
                'permission_key' => 'survey.view_results',
                'is_active' => 1,
                'sort_order' => 3,
            ],

            // Pengaduan/Complaint
            [
                'id' => 45,
                'parent_id' => null,
                'title' => 'Pengaduan',
                'url' => 'admin/complaint',
                'route_name' => 'admin.complaint',
                'icon' => 'support',
                'permission_key' => 'complaint.view',
                'is_active' => 1,
                'sort_order' => 45,
            ],

            // WhatsApp Groups
            [
                'id' => 46,
                'parent_id' => null,
                'title' => 'WhatsApp Groups',
                'url' => 'admin/wa-groups',
                'route_name' => 'admin.wa.groups',
                'icon' => 'groups',
                'permission_key' => 'wa_group.manage',
                'is_active' => 1,
                'sort_order' => 46,
            ],

            // ========================================
            // KONTEN
            // ========================================
            [
                'id' => 50,
                'parent_id' => null,
                'title' => 'Konten & Blog',
                'url' => '#',
                'icon' => 'article',
                'permission_key' => 'content.manage',
                'is_active' => 1,
                'sort_order' => 50,
            ],
            [
                'id' => 51,
                'parent_id' => 50,
                'title' => 'Artikel/Blog',
                'url' => 'admin/content/posts',
                'route_name' => 'admin.content.posts',
                'icon' => 'article',
                'permission_key' => 'content.manage',
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 52,
                'parent_id' => 50,
                'title' => 'Halaman Statis',
                'url' => 'admin/content/pages',
                'route_name' => 'admin.content.pages',
                'icon' => 'web',
                'permission_key' => 'content.manage',
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'id' => 53,
                'parent_id' => 50,
                'title' => 'Kategori',
                'url' => 'admin/content/categories',
                'route_name' => 'admin.content.categories',
                'icon' => 'category',
                'permission_key' => 'content.manage',
                'is_active' => 1,
                'sort_order' => 3,
            ],
        ];

        // Insert menus
        $builder = $this->db->table('menus');

        // Clear existing data
        $builder->truncate();

        // Insert all menus
        foreach ($menus as $menu) {
            $menu['created_at'] = date('Y-m-d H:i:s');
            $menu['updated_at'] = date('Y-m-d H:i:s');

            $builder->insert($menu);
        }

        echo "MenuSeeder: Inserted " . count($menus) . " menu items.\n";
    }
}
