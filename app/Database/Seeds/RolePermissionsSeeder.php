<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * RolePermissionsSeeder
 * 
 * Seeder untuk mapping permissions ke roles (matriks hak akses)
 * Mendefinisikan permission apa saja yang dimiliki oleh setiap role
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ambil semua roles dan permissions dari database
        $roles = $this->db->table('auth_groups')->get()->getResultArray();
        $permissions = $this->db->table('auth_permissions')->get()->getResultArray();

        // Buat mapping untuk kemudahan akses
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['title']] = $role['id'];
        }

        $permMap = [];
        foreach ($permissions as $perm) {
            $permMap[$perm['name']] = $perm['id'];
        }

        // ==========================================
        // DEFINE ROLE-PERMISSION MATRIX
        // ==========================================

        $rolePermissions = [
            // ==========================================
            // SUPER ADMIN - FULL ACCESS
            // ==========================================
            'Super Admin' => [
                // Dashboard
                'dashboard.view',
                'dashboard.admin',

                // Member Management - FULL
                'member.view',
                'member.view_detail',
                'member.create',
                'member.edit',
                'member.delete',
                'member.approve',
                'member.suspend',
                'member.export',
                'member.import',

                // Role & Permission - EXCLUSIVE
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',
                'role.assign',
                'permission.manage',

                // Menu Management - EXCLUSIVE
                'menu.view',
                'menu.create',
                'menu.edit',
                'menu.delete',

                // Forum - FULL
                'forum.view',
                'forum.create_thread',
                'forum.reply',
                'forum.edit_own',
                'forum.moderate',

                // Survey - FULL
                'survey.view',
                'survey.create',
                'survey.edit',
                'survey.delete',
                'survey.respond',
                'survey.view_results',
                'survey.export',

                // Ticket - FULL
                'ticket.view',
                'ticket.create',
                'ticket.respond',
                'ticket.close',
                'ticket.assign',

                // Content - FULL
                'content.view',
                'content.create',
                'content.edit',
                'content.delete',
                'content.publish',

                // Master Data - FULL
                'master.view',
                'master.manage',
                'master.import',

                // Finance - FULL
                'finance.view',
                'finance.manage',

                // Organization - FULL
                'org.view',
                'org.manage',

                // WhatsApp Group - FULL
                'wa_group.view',
                'wa_group.manage',

                // Audit Log
                'audit.view',
            ],

            // ==========================================
            // PENGURUS - OPERATIONAL ADMIN
            // ==========================================
            'Pengurus' => [
                // Dashboard
                'dashboard.view',
                'dashboard.admin',

                // Member Management - ALMOST FULL (no delete)
                'member.view',
                'member.view_detail',
                'member.create',
                'member.edit',
                'member.approve',
                'member.suspend',
                'member.export',
                'member.import',

                // Forum - FULL
                'forum.view',
                'forum.create_thread',
                'forum.reply',
                'forum.edit_own',
                'forum.moderate',

                // Survey - FULL
                'survey.view',
                'survey.create',
                'survey.edit',
                'survey.delete',
                'survey.respond',
                'survey.view_results',
                'survey.export',

                // Ticket - FULL
                'ticket.view',
                'ticket.create',
                'ticket.respond',
                'ticket.close',
                'ticket.assign',

                // Content - FULL
                'content.view',
                'content.create',
                'content.edit',
                'content.delete',
                'content.publish',

                // Master Data - VIEW ONLY
                'master.view',

                // Finance - VIEW ONLY
                'finance.view',

                // Organization - FULL
                'org.view',
                'org.manage',

                // WhatsApp Group - FULL
                'wa_group.view',
                'wa_group.manage',
            ],

            // ==========================================
            // KOORDINATOR WILAYAH - REGIONAL SCOPE
            // ==========================================
            'Koordinator Wilayah' => [
                // Dashboard
                'dashboard.view',

                // Member Management - LIMITED TO REGION
                'member.view',
                'member.view_detail',
                'member.edit',
                'member.export',

                // Forum - FULL ACCESS
                'forum.view',
                'forum.create_thread',
                'forum.reply',
                'forum.edit_own',

                // Survey - RESPOND & VIEW
                'survey.view',
                'survey.respond',
                'survey.view_results',

                // Ticket - CREATE & VIEW
                'ticket.view',
                'ticket.create',

                // Content - VIEW ONLY
                'content.view',

                // WhatsApp Group - VIEW & MANAGE (for their region)
                'wa_group.view',
                'wa_group.manage',
            ],

            // ==========================================
            // ANGGOTA - ACTIVE MEMBER
            // ==========================================
            'Anggota' => [
                // Dashboard
                'dashboard.view',

                // Member - VIEW OWN PROFILE
                'member.view_detail',

                // Forum - FULL PARTICIPATION
                'forum.view',
                'forum.create_thread',
                'forum.reply',
                'forum.edit_own',

                // Survey - RESPOND
                'survey.view',
                'survey.respond',

                // Ticket - CREATE & VIEW OWN
                'ticket.view',
                'ticket.create',

                // Content - VIEW ONLY
                'content.view',

                // Organization - VIEW
                'org.view',

                // WhatsApp Group - VIEW
                'wa_group.view',
            ],

            // ==========================================
            // CALON ANGGOTA - PENDING MEMBER
            // ==========================================
            'Calon Anggota' => [
                // Dashboard - LIMITED
                'dashboard.view',

                // Member - VIEW OWN PROFILE ONLY
                'member.view_detail',

                // Content - VIEW PUBLIC ONLY
                'content.view',

                // Organization - VIEW
                'org.view',
            ],
        ];

        // ==========================================
        // INSERT ROLE-PERMISSION MAPPINGS
        // ==========================================

        $totalInserted = 0;
        $totalSkipped = 0;

        foreach ($rolePermissions as $roleName => $permissions) {
            // Validasi role exists
            if (!isset($roleMap[$roleName])) {
                echo "⚠ Warning: Role '{$roleName}' tidak ditemukan!\n";
                continue;
            }

            $roleId = $roleMap[$roleName];

            foreach ($permissions as $permName) {
                // Validasi permission exists
                if (!isset($permMap[$permName])) {
                    echo "⚠ Warning: Permission '{$permName}' tidak ditemukan!\n";
                    continue;
                }

                $permId = $permMap[$permName];

                // Cek apakah mapping sudah ada
                $existing = $this->db->table('auth_groups_permissions')
                    ->where('group_id', $roleId)
                    ->where('permission_id', $permId)
                    ->get()
                    ->getRow();

                if (!$existing) {
                    // Insert mapping baru
                    $this->db->table('auth_groups_permissions')->insert([
                        'group_id'      => $roleId,
                        'permission_id' => $permId,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ]);
                    $totalInserted++;
                } else {
                    $totalSkipped++;
                }
            }
        }

        echo "\n";
        echo "==========================================\n";
        echo "  ROLE-PERMISSIONS MAPPING COMPLETED      \n";
        echo "==========================================\n";
        echo "✓ Mappings Inserted: {$totalInserted}\n";
        echo "→ Mappings Skipped: {$totalSkipped}\n";
        echo "==========================================\n";
        echo "\nAccess Matrix Summary:\n";
        echo "- Super Admin: " . count($rolePermissions['Super Admin']) . " permissions (FULL ACCESS)\n";
        echo "- Pengurus: " . count($rolePermissions['Pengurus']) . " permissions (OPERATIONAL)\n";
        echo "- Koordinator Wilayah: " . count($rolePermissions['Koordinator Wilayah']) . " permissions (REGIONAL)\n";
        echo "- Anggota: " . count($rolePermissions['Anggota']) . " permissions (MEMBER)\n";
        echo "- Calon Anggota: " . count($rolePermissions['Calon Anggota']) . " permissions (LIMITED)\n";
        echo "==========================================\n\n";
    }
}
