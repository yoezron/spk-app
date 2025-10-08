<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * PermissionsSeeder
 * 
 * Seeder untuk inisialisasi permissions (hak akses) granular dalam sistem SPK
 * Permissions dikelompokkan per modul untuk memudahkan manajemen
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Data permissions yang akan di-insert
        $permissions = [
            // ==========================================
            // DASHBOARD PERMISSIONS
            // ==========================================
            [
                'name'        => 'dashboard.view',
                'description' => 'Melihat dashboard statistik',
            ],
            [
                'name'        => 'dashboard.admin',
                'description' => 'Akses dashboard admin dengan statistik lengkap',
            ],

            // ==========================================
            // MEMBER MANAGEMENT PERMISSIONS
            // ==========================================
            [
                'name'        => 'member.view',
                'description' => 'Melihat daftar anggota',
            ],
            [
                'name'        => 'member.view_detail',
                'description' => 'Melihat detail profil anggota',
            ],
            [
                'name'        => 'member.create',
                'description' => 'Menambah anggota baru',
            ],
            [
                'name'        => 'member.edit',
                'description' => 'Mengedit data anggota',
            ],
            [
                'name'        => 'member.delete',
                'description' => 'Menghapus anggota',
            ],
            [
                'name'        => 'member.approve',
                'description' => 'Menyetujui/verifikasi calon anggota',
            ],
            [
                'name'        => 'member.suspend',
                'description' => 'Menangguhkan/menonaktifkan anggota',
            ],
            [
                'name'        => 'member.export',
                'description' => 'Export data anggota ke Excel/PDF',
            ],
            [
                'name'        => 'member.import',
                'description' => 'Import data anggota secara massal',
            ],

            // ==========================================
            // ROLE & PERMISSION MANAGEMENT (Super Admin Only)
            // ==========================================
            [
                'name'        => 'role.view',
                'description' => 'Melihat daftar roles',
            ],
            [
                'name'        => 'role.create',
                'description' => 'Membuat role baru',
            ],
            [
                'name'        => 'role.edit',
                'description' => 'Mengedit role',
            ],
            [
                'name'        => 'role.delete',
                'description' => 'Menghapus role',
            ],
            [
                'name'        => 'role.assign',
                'description' => 'Assign role ke user',
            ],
            [
                'name'        => 'permission.manage',
                'description' => 'Mengelola permissions (assign ke role)',
            ],

            // ==========================================
            // MENU MANAGEMENT (Super Admin Only)
            // ==========================================
            [
                'name'        => 'menu.view',
                'description' => 'Melihat daftar menu',
            ],
            [
                'name'        => 'menu.create',
                'description' => 'Membuat menu baru',
            ],
            [
                'name'        => 'menu.edit',
                'description' => 'Mengedit menu',
            ],
            [
                'name'        => 'menu.delete',
                'description' => 'Menghapus menu',
            ],

            // ==========================================
            // FORUM PERMISSIONS
            // ==========================================
            [
                'name'        => 'forum.view',
                'description' => 'Melihat forum diskusi',
            ],
            [
                'name'        => 'forum.create_thread',
                'description' => 'Membuat thread baru di forum',
            ],
            [
                'name'        => 'forum.reply',
                'description' => 'Membalas thread forum',
            ],
            [
                'name'        => 'forum.edit_own',
                'description' => 'Edit post sendiri di forum',
            ],
            [
                'name'        => 'forum.moderate',
                'description' => 'Moderasi forum (hapus, edit semua post)',
            ],

            // ==========================================
            // SURVEY PERMISSIONS
            // ==========================================
            [
                'name'        => 'survey.view',
                'description' => 'Melihat daftar survei',
            ],
            [
                'name'        => 'survey.create',
                'description' => 'Membuat survei baru',
            ],
            [
                'name'        => 'survey.edit',
                'description' => 'Mengedit survei',
            ],
            [
                'name'        => 'survey.delete',
                'description' => 'Menghapus survei',
            ],
            [
                'name'        => 'survey.respond',
                'description' => 'Mengisi survei',
            ],
            [
                'name'        => 'survey.view_results',
                'description' => 'Melihat hasil survei',
            ],
            [
                'name'        => 'survey.export',
                'description' => 'Export hasil survei',
            ],

            // ==========================================
            // COMPLAINT/TICKET PERMISSIONS
            // ==========================================
            [
                'name'        => 'ticket.view',
                'description' => 'Melihat daftar pengaduan',
            ],
            [
                'name'        => 'ticket.create',
                'description' => 'Membuat pengaduan baru',
            ],
            [
                'name'        => 'ticket.respond',
                'description' => 'Membalas/menangani pengaduan',
            ],
            [
                'name'        => 'ticket.close',
                'description' => 'Menutup pengaduan',
            ],
            [
                'name'        => 'ticket.assign',
                'description' => 'Assign pengaduan ke petugas',
            ],

            // ==========================================
            // CONTENT MANAGEMENT (CMS)
            // ==========================================
            [
                'name'        => 'content.view',
                'description' => 'Melihat konten/artikel',
            ],
            [
                'name'        => 'content.create',
                'description' => 'Membuat konten/artikel baru',
            ],
            [
                'name'        => 'content.edit',
                'description' => 'Mengedit konten/artikel',
            ],
            [
                'name'        => 'content.delete',
                'description' => 'Menghapus konten/artikel',
            ],
            [
                'name'        => 'content.publish',
                'description' => 'Publish konten ke publik',
            ],

            // ==========================================
            // MASTER DATA PERMISSIONS
            // ==========================================
            [
                'name'        => 'master.view',
                'description' => 'Melihat master data',
            ],
            [
                'name'        => 'master.manage',
                'description' => 'Mengelola master data (provinsi, kampus, dll)',
            ],
            [
                'name'        => 'master.import',
                'description' => 'Import master data secara massal',
            ],

            // ==========================================
            // FINANCE PERMISSIONS (Optional)
            // ==========================================
            [
                'name'        => 'finance.view',
                'description' => 'Melihat data keuangan',
            ],
            [
                'name'        => 'finance.manage',
                'description' => 'Mengelola data keuangan',
            ],

            // ==========================================
            // ORGANIZATION STRUCTURE PERMISSIONS
            // ==========================================
            [
                'name'        => 'org.view',
                'description' => 'Melihat struktur organisasi',
            ],
            [
                'name'        => 'org.manage',
                'description' => 'Mengelola struktur organisasi',
            ],

            // ==========================================
            // WHATSAPP GROUP PERMISSIONS
            // ==========================================
            [
                'name'        => 'wa_group.view',
                'description' => 'Melihat daftar grup WhatsApp',
            ],
            [
                'name'        => 'wa_group.manage',
                'description' => 'Mengelola link grup WhatsApp',
            ],

            // ==========================================
            // AUDIT LOG PERMISSIONS
            // ==========================================
            [
                'name'        => 'audit.view',
                'description' => 'Melihat audit log sistem',
            ],
        ];

        // Insert data ke tabel auth_permissions (Shield uses this table)
        $inserted = 0;
        $skipped = 0;

        foreach ($permissions as $permission) {
            // Cek apakah permission sudah ada
            $existingPermission = $this->db->table('auth_permissions')
                ->where('name', $permission['name'])
                ->get()
                ->getRow();

            if (!$existingPermission) {
                // Insert permission baru
                $this->db->table('auth_permissions')->insert([
                    'name'        => $permission['name'],
                    'description' => $permission['description'],
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            } else {
                $skipped++;
            }
        }

        echo "\n";
        echo "==========================================\n";
        echo "  PERMISSIONS SEEDER COMPLETED            \n";
        echo "==========================================\n";
        echo "Total Permissions: " . count($permissions) . "\n";
        echo "✓ Inserted: {$inserted}\n";
        echo "→ Skipped (already exists): {$skipped}\n";
        echo "==========================================\n";
        echo "\nPermissions Modules:\n";
        echo "- Dashboard (2)\n";
        echo "- Member Management (11)\n";
        echo "- Role & Permission (6)\n";
        echo "- Menu Management (4)\n";
        echo "- Forum (5)\n";
        echo "- Survey (7)\n";
        echo "- Ticket/Complaint (5)\n";
        echo "- Content/CMS (5)\n";
        echo "- Master Data (3)\n";
        echo "- Finance (2)\n";
        echo "- Organization (2)\n";
        echo "- WhatsApp Group (2)\n";
        echo "- Audit Log (1)\n";
        echo "==========================================\n\n";
    }
}
