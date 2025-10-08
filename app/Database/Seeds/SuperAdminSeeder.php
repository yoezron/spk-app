<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

/**
 * SuperAdminSeeder
 * 
 * Seeder untuk membuat akun Super Admin pertama
 * Akun ini digunakan untuk login awal dan setup sistem
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Konfigurasi Super Admin
        $adminEmail = 'admin@spk.or.id';
        $adminUsername = 'superadmin';
        $adminPassword = 'SuperAdmin123!'; // CHANGE THIS AFTER FIRST LOGIN!
        $adminName = 'Super Administrator';

        // Cek apakah Super Admin sudah ada (by username)
        $existingUser = $this->db->table('users')
            ->where('username', $adminUsername)
            ->get()
            ->getRow();

        if ($existingUser) {
            echo "→ Super Admin dengan username '{$adminUsername}' sudah ada, skip.\n";
            return;
        }

        // Buat user Super Admin menggunakan Shield
        $users = auth()->getProvider();

        $user = new User([
            'username' => $adminUsername,
            'email'    => $adminEmail,
            'password' => $adminPassword,
        ]);

        // Simpan user
        $users->save($user);

        // Ambil ID user yang baru dibuat
        $userId = $users->getInsertID();

        // Aktifkan user
        $user = $users->findById($userId);
        $user->activate();

        // Ambil role "Super Admin"
        $superAdminRole = $this->db->table('auth_groups')
            ->where('title', 'Super Admin')
            ->get()
            ->getRow();

        if (!$superAdminRole) {
            echo "⚠ Error: Role 'Super Admin' tidak ditemukan! Jalankan RolesSeeder terlebih dahulu.\n";
            return;
        }

        // Assign role Super Admin ke user (direct insert ke pivot table)
        // Bypass Shield's group validation karena kita pakai dynamic RBAC dari database
        $this->db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => $superAdminRole->title,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Buat profile di member_profiles
        $this->db->table('member_profiles')->insert([
            'user_id'       => $userId,
            'full_name'     => $adminName,
            'member_number' => 'SPK-ADMIN-001',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        echo "\n";
        echo "==========================================\n";
        echo "  SUPER ADMIN CREATED SUCCESSFULLY!       \n";
        echo "==========================================\n";
        echo "Email    : {$adminEmail}\n";
        echo "Username : {$adminUsername}\n";
        echo "Password : {$adminPassword}\n";
        echo "Role     : Super Admin\n";
        echo "User ID  : {$userId}\n";
        echo "==========================================\n";
        echo "⚠ IMPORTANT SECURITY NOTICE:\n";
        echo "   1. Change the password immediately after first login!\n";
        echo "   2. Enable Two-Factor Authentication (2FA)\n";
        echo "   3. Keep this credential secure\n";
        echo "==========================================\n\n";
    }
}
