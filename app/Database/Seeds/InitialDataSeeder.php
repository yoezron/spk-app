<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Insert Roles
        $roles = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'super-admin',
                'description' => 'Administrator with full system access',
                'level'       => 4,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Pengurus',
                'slug'        => 'pengurus',
                'description' => 'SPK Management Staff',
                'level'       => 3,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Koordinator Wilayah',
                'slug'        => 'koordinator-wilayah',
                'description' => 'Regional Coordinator with regional data access',
                'level'       => 2,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Anggota',
                'slug'        => 'anggota',
                'description' => 'Active Member',
                'level'       => 1,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Calon',
                'slug'        => 'calon',
                'description' => 'Candidate Member',
                'level'       => 0,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('roles')->insertBatch($roles);

        // Insert Permissions
        $resources = ['dashboard', 'members', 'users', 'roles', 'permissions', 'regions', 'forums', 'surveys', 'tickets', 'menus', 'reports'];
        $actions = ['view', 'create', 'edit', 'delete', 'export'];
        
        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'name'        => ucfirst($action) . ' ' . ucfirst($resource),
                    'slug'        => $action . '-' . $resource,
                    'resource'    => $resource,
                    'action'      => $action,
                    'description' => 'Permission to ' . $action . ' ' . $resource,
                    'created_at'  => date('Y-m-d H:i:s'),
                ];
            }
        }
        $this->db->table('permissions')->insertBatch($permissions);

        // Assign all permissions to Super Admin
        $superAdminRole = $this->db->table('roles')->where('slug', 'super-admin')->get()->getRow();
        $allPermissions = $this->db->table('permissions')->get()->getResult();
        
        $rolePermissions = [];
        foreach ($allPermissions as $permission) {
            $rolePermissions[] = [
                'role_id'       => $superAdminRole->id,
                'permission_id' => $permission->id,
                'created_at'    => date('Y-m-d H:i:s'),
            ];
        }
        $this->db->table('role_permissions')->insertBatch($rolePermissions);

        // Insert Sample Regions
        $regions = [
            [
                'name'        => 'DKI Jakarta',
                'code'        => 'JKT',
                'province'    => 'DKI Jakarta',
                'city'        => 'Jakarta',
                'description' => 'Wilayah DKI Jakarta',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Jawa Barat',
                'code'        => 'JABAR',
                'province'    => 'Jawa Barat',
                'city'        => 'Bandung',
                'description' => 'Wilayah Jawa Barat',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Jawa Tengah',
                'code'        => 'JATENG',
                'province'    => 'Jawa Tengah',
                'city'        => 'Semarang',
                'description' => 'Wilayah Jawa Tengah',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Jawa Timur',
                'code'        => 'JATIM',
                'province'    => 'Jawa Timur',
                'city'        => 'Surabaya',
                'description' => 'Wilayah Jawa Timur',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('regions')->insertBatch($regions);

        // Insert Super Admin User
        $this->db->table('users')->insert([
            'username'   => 'admin',
            'email'      => 'admin@spk.or.id',
            'password'   => password_hash('admin123', PASSWORD_BCRYPT),
            'role_id'    => $superAdminRole->id,
            'full_name'  => 'Super Administrator',
            'phone'      => '081234567890',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Insert Menus
        $menus = [
            ['parent_id' => null, 'name' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-home', 'order' => 1, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => null, 'name' => 'Keanggotaan', 'url' => '#', 'icon' => 'fas fa-users', 'order' => 2, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => null, 'name' => 'Forum', 'url' => '/forum', 'icon' => 'fas fa-comments', 'order' => 3, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => null, 'name' => 'Survey', 'url' => '/surveys', 'icon' => 'fas fa-poll', 'order' => 4, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => null, 'name' => 'Pengaduan', 'url' => '/tickets', 'icon' => 'fas fa-ticket-alt', 'order' => 5, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => null, 'name' => 'Administrasi', 'url' => '#', 'icon' => 'fas fa-cog', 'order' => 6, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('menus')->insertBatch($menus);

        // Get inserted menu IDs
        $keanggotaanMenu = $this->db->table('menus')->where('name', 'Keanggotaan')->get()->getRow();
        $administrasiMenu = $this->db->table('menus')->where('name', 'Administrasi')->get()->getRow();

        // Insert Sub-menus
        $subMenus = [
            ['parent_id' => $keanggotaanMenu->id, 'name' => 'Daftar Anggota', 'url' => '/members', 'icon' => 'fas fa-list', 'order' => 1, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => $keanggotaanMenu->id, 'name' => 'Pendaftaran Baru', 'url' => '/members/register', 'icon' => 'fas fa-user-plus', 'order' => 2, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => $keanggotaanMenu->id, 'name' => 'Import Data', 'url' => '/members/import', 'icon' => 'fas fa-file-upload', 'order' => 3, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => $administrasiMenu->id, 'name' => 'Manajemen User', 'url' => '/admin/users', 'icon' => 'fas fa-users-cog', 'order' => 1, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => $administrasiMenu->id, 'name' => 'Role & Permission', 'url' => '/admin/roles', 'icon' => 'fas fa-key', 'order' => 2, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => $administrasiMenu->id, 'name' => 'Wilayah', 'url' => '/admin/regions', 'icon' => 'fas fa-map-marked-alt', 'order' => 3, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['parent_id' => $administrasiMenu->id, 'name' => 'Menu Management', 'url' => '/admin/menus', 'icon' => 'fas fa-bars', 'order' => 4, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('menus')->insertBatch($subMenus);

        // Assign menus to Super Admin role
        $allMenus = $this->db->table('menus')->get()->getResult();
        $roleMenus = [];
        foreach ($allMenus as $menu) {
            $roleMenus[] = [
                'role_id'    => $superAdminRole->id,
                'menu_id'    => $menu->id,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        $this->db->table('role_menus')->insertBatch($roleMenus);

        // Insert Forum Categories
        $forumCategories = [
            ['name' => 'Umum', 'slug' => 'umum', 'description' => 'Diskusi umum tentang SPK', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Pengaduan', 'slug' => 'pengaduan', 'description' => 'Pengaduan anggota', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Info & Berita', 'slug' => 'info-berita', 'description' => 'Informasi dan berita terkini', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('forum_categories')->insertBatch($forumCategories);
    }
}
