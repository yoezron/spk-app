<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * RolesSeeder
 * 
 * Seeder untuk inisialisasi roles (peran) default dalam sistem SPK
 * 5 Roles: Super Admin, Pengurus, Koordinator Wilayah, Anggota, Calon Anggota
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Data roles yang akan di-insert
        $roles = [
            [
                'name'        => 'Super Admin',
                'description' => 'Administrator tertinggi dengan akses penuh ke seluruh sistem termasuk role management, permission management, dan menu management',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Pengurus',
                'description' => 'Pengurus Pusat SPK yang menjalankan operasional harian, dapat mengelola anggota, konten, survei, dan pengaduan',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Koordinator Wilayah',
                'description' => 'Koordinator di tingkat provinsi/wilayah, dapat mengelola anggota dan melihat data hanya di wilayahnya',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Anggota',
                'description' => 'Anggota aktif SPK yang telah diverifikasi, dapat mengakses portal anggota, forum, survei, dan fitur internal',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Calon Anggota',
                'description' => 'Pendaftar baru yang menunggu verifikasi dari pengurus, akses terbatas hanya ke profil dan dokumen publik',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data ke tabel auth_groups (Shield uses this table for roles)
        foreach ($roles as $role) {
            // Cek apakah role sudah ada
            $existingRole = $this->db->table('auth_groups')
                ->where('title', $role['name'])
                ->get()
                ->getRow();

            if (!$existingRole) {
                // Insert role baru
                $this->db->table('auth_groups')->insert([
                    'title'       => $role['name'],
                    'description' => $role['description'],
                    'created_at'  => $role['created_at'],
                ]);

                echo "✓ Role '{$role['name']}' berhasil dibuat.\n";
            } else {
                echo "→ Role '{$role['name']}' sudah ada, skip.\n";
            }
        }

        echo "\n";
        echo "========================================\n";
        echo "  ROLES SEEDER COMPLETED SUCCESSFULLY   \n";
        echo "========================================\n";
        echo "Total Roles: 5\n";
        echo "- Super Admin\n";
        echo "- Pengurus\n";
        echo "- Koordinator Wilayah\n";
        echo "- Anggota\n";
        echo "- Calon Anggota\n";
        echo "========================================\n\n";
    }
}
