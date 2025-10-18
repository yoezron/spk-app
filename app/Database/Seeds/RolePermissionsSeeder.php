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
            'superadmin' => [
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
                'role.manage',
                'permission.manage',
                'permission.create',
                'permission.edit',
                'permission.delete',

                // Menu Management - EXCLUSIVE
                'menu.view',
                'menu.create',
                'menu.edit',
                'menu.delete',
                'menu.manage',

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
                'survey.manage',

                // Ticket - FULL
                'ticket.view',
                'ticket.create',
                'ticket.respond',
                'ticket.close',
                'ticket.assign',
                'complaint.view',
                'complaint.manage',
                'complaint.reply',

                // Content - FULL
                'content.view',
                'content.create',
                'content.edit',
                'content.delete',
                'content.publish',
                'content.manage',

                // Master Data - FULL
                'master.view',
                'master.manage',
                'master.import',

                // Finance - FULL
                'finance.view',
                'finance.manage',

                // Payment - FULL
                'payment.view',
                'payment.manage',
                'payment.verify',
                'payment.export',

                // Organization - FULL
                'org.view',
                'org.manage',
                'org.create',
                'org.assign',

                // WhatsApp Group - FULL
                'wagroup.view',
                'wagroup.manage',
                'wagroup.create',
                'wagroup.edit',
                'wagroup.delete',

                // Statistics - FULL
                'stats.view',
                'stats.export',

                // Settings - EXCLUSIVE
                'settings.view',
                'settings.edit',
                'settings.manage',

                // Audit Log
                'audit.view',
                'audit.export',
            ],

            // ==========================================
            // PENGURUS - OPERATIONAL ADMIN
            // ==========================================
            'pengurus' => [
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
                'survey.manage',

                // Ticket - FULL
                'ticket.view',
                'ticket.create',
                'ticket.respond',
                'ticket.close',
                'ticket.assign',
                'complaint.view',
                'complaint.manage',
                'complaint.reply',

                // Content - FULL
                'content.view',
                'content.create',
                'content.edit',
                'content.delete',
                'content.publish',
                'content.manage',

                // Master Data - READ ONLY
                'master.view',

                // Finance - VIEW ONLY
                'finance.view',

                // Payment - VERIFICATION
                'payment.view',
                'payment.verify',
                'payment.export',

                // Organization - VIEW & ASSIGN
                'org.view',
                'org.assign',

                // WhatsApp Group - MANAGE
                'wagroup.view',
                'wagroup.manage',
                'wagroup.create',
                'wagroup.edit',

                // Statistics
                'stats.view',
                'stats.export',
            ],

            // ==========================================
            // KOORDINATOR WILAYAH - REGIONAL ADMIN
            // ==========================================
            'koordinator' => [
                // Dashboard
                'dashboard.view',

                // Member Management - REGIONAL SCOPE
                'member.view',
                'member.view_detail',
                'member.approve',
                'member.suspend',
                'member.export',

                // Forum - FULL
                'forum.view',
                'forum.create_thread',
                'forum.reply',
                'forum.edit_own',
                'forum.moderate',

                // Survey - PARTICIPATE
                'survey.view',
                'survey.respond',

                // Ticket - HANDLE
                'ticket.view',
                'ticket.create',
                'ticket.respond',

                // Content - VIEW
                'content.view',

                // Payment - VIEW REGIONAL
                'payment.view',

                // WhatsApp Group - VIEW
                'wagroup.view',

                // Statistics - REGIONAL
                'stats.view',
            ],

            // ==========================================
            // ANGGOTA - ACTIVE MEMBER
            // ==========================================
            'anggota' => [
                // Dashboard
                'dashboard.view',

                // Forum - PARTICIPATE
                'forum.view',
                'forum.create_thread',
                'forum.reply',
                'forum.edit_own',

                // Survey - PARTICIPATE
                'survey.view',
                'survey.respond',

                // Ticket - CREATE
                'ticket.view',
                'ticket.create',

                // Content - READ
                'content.view',

                // Payment - OWN ONLY
                'payment.view',

                // WhatsApp Group - VIEW
                'wagroup.view',
            ],

            // ==========================================
            // CALON ANGGOTA - PENDING MEMBER
            // ==========================================
            'calon_anggota' => [
                // Dashboard
                'dashboard.view',

                // Content - READ ONLY
                'content.view',
            ],
        ];

        // ==========================================
        // INSERT MAPPINGS
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

        // Count permissions safely
        $superadminCount = isset($rolePermissions['superadmin']) ? count($rolePermissions['superadmin']) : 0;
        $pengurusCount = isset($rolePermissions['pengurus']) ? count($rolePermissions['pengurus']) : 0;
        $koordinatorCount = isset($rolePermissions['koordinator']) ? count($rolePermissions['koordinator']) : 0;
        $anggotaCount = isset($rolePermissions['anggota']) ? count($rolePermissions['anggota']) : 0;
        $calonCount = isset($rolePermissions['calon_anggota']) ? count($rolePermissions['calon_anggota']) : 0;

        echo "- superadmin: {$superadminCount} permissions (FULL ACCESS)\n";
        echo "- pengurus: {$pengurusCount} permissions (OPERATIONAL)\n";
        echo "- koordinator: {$koordinatorCount} permissions (REGIONAL)\n";
        echo "- anggota: {$anggotaCount} permissions (MEMBER)\n";
        echo "- calon_anggota: {$calonCount} permissions (LIMITED)\n";
        echo "==========================================\n\n";
    }
}
