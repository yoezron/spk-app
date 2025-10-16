<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * UniversitySeeder
 * 
 * Seeder untuk universitas/kampus major di Indonesia
 * Data diambil dari analisis user.sql existing members
 * Total: 100+ kampus yang paling sering muncul
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "==========================================\n";
        echo "  SEEDING UNIVERSITIES (Major Kampus)     \n";
        echo "==========================================\n\n";

        // Get provinces for FK reference
        $provinces = $this->db->table('provinces')->get()->getResult();
        $provinceMap = [];
        foreach ($provinces as $prov) {
            $provinceMap[$prov->name] = $prov->id;
        }

        // Major universities data (100+ kampus)
        // Format: [name, type, province_name]
        $universities = [
            // Sumatera
            ['Universitas Syiah Kuala', 'PTN', 'Aceh'],
            ['Universitas Malikussaleh', 'PTN', 'Aceh'],
            ['Universitas Jabal Ghafur', 'PTS', 'Aceh'],
            ['Universitas Sumatera Utara', 'PTN', 'Sumatera Utara'],
            ['Universitas Negeri Medan', 'PTN', 'Sumatera Utara'],
            ['Universitas Muhammadiyah Sumatera Utara', 'PTS', 'Sumatera Utara'],
            ['Universitas Andalas', 'PTN', 'Sumatera Barat'],
            ['Universitas Negeri Padang', 'PTN', 'Sumatera Barat'],
            ['Universitas Bung Hatta', 'PTS', 'Sumatera Barat'],
            ['Universitas Riau', 'PTN', 'Riau'],
            ['Universitas Islam Riau', 'PTS', 'Riau'],
            ['Universitas Jambi', 'PTN', 'Jambi'],
            ['Universitas Sriwijaya', 'PTN', 'Sumatera Selatan'],
            ['Universitas Muhammadiyah Palembang', 'PTS', 'Sumatera Selatan'],
            ['Politeknik Negeri Sriwijaya', 'Politeknik', 'Sumatera Selatan'],
            ['Universitas Bengkulu', 'PTN', 'Bengkulu'],
            ['Universitas Lampung', 'PTN', 'Lampung'],
            ['Universitas Bangka Belitung', 'PTN', 'Kepulauan Bangka Belitung'],
            ['Universitas Maritim Raja Ali Haji', 'PTN', 'Kepulauan Riau'],

            // Jakarta & Jawa Barat
            ['Universitas Indonesia', 'PTN', 'DKI Jakarta'],
            ['Universitas Negeri Jakarta', 'PTN', 'DKI Jakarta'],
            ['Universitas Trisakti', 'PTS', 'DKI Jakarta'],
            ['Universitas Pancasila', 'PTS', 'DKI Jakarta'],
            ['Universitas Mercu Buana', 'PTS', 'DKI Jakarta'],
            ['Universitas Pembangunan Nasional Veteran Jakarta', 'PTN', 'DKI Jakarta'],
            ['Politeknik Negeri Jakarta', 'Politeknik', 'DKI Jakarta'],
            ['Politeknik LP3I Jakarta', 'Politeknik', 'DKI Jakarta'],
            ['Universitas Padjadjaran', 'PTN', 'Jawa Barat'],
            ['Institut Teknologi Bandung', 'PTN', 'Jawa Barat'],
            ['Universitas Pendidikan Indonesia', 'PTN', 'Jawa Barat'],
            ['Universitas Islam Bandung', 'PTS', 'Jawa Barat'],
            ['Universitas Pasundan', 'PTS', 'Jawa Barat'],
            ['Politeknik Negeri Bandung', 'Politeknik', 'Jawa Barat'],

            // Jawa Tengah & DIY
            ['Universitas Gadjah Mada', 'PTN', 'DI Yogyakarta'],
            ['Universitas Negeri Yogyakarta', 'PTN', 'DI Yogyakarta'],
            ['Universitas Muhammadiyah Yogyakarta', 'PTS', 'DI Yogyakarta'],
            ['Universitas Diponegoro', 'PTN', 'Jawa Tengah'],
            ['Universitas Negeri Semarang', 'PTN', 'Jawa Tengah'],
            ['Universitas Sebelas Maret', 'PTN', 'Jawa Tengah'],
            ['Universitas Muhammadiyah Surakarta', 'PTS', 'Jawa Tengah'],
            ['Universitas Jenderal Soedirman', 'PTN', 'Jawa Tengah'],
            ['Universitas Islam Sultan Agung', 'PTS', 'Jawa Tengah'],
            ['Politeknik Negeri Semarang', 'Politeknik', 'Jawa Tengah'],
            ['STMIK Bina Patria', 'Sekolah Tinggi', 'Jawa Tengah'],
            ['UIN Walisongo Semarang', 'PTN', 'Jawa Tengah'],

            // Jawa Timur
            ['Universitas Airlangga', 'PTN', 'Jawa Timur'],
            ['Institut Teknologi Sepuluh Nopember', 'PTN', 'Jawa Timur'],
            ['Universitas Brawijaya', 'PTN', 'Jawa Timur'],
            ['Universitas Negeri Malang', 'PTN', 'Jawa Timur'],
            ['Universitas Negeri Surabaya', 'PTN', 'Jawa Timur'],
            ['Universitas Jember', 'PTN', 'Jawa Timur'],
            ['Universitas Trunojoyo Madura', 'PTN', 'Jawa Timur'],
            ['Universitas Muhammadiyah Malang', 'PTS', 'Jawa Timur'],
            ['Universitas Merdeka Madiun', 'PTS', 'Jawa Timur'],
            ['Politeknik Negeri Jember', 'Politeknik', 'Jawa Timur'],
            ['Politeknik Negeri Malang', 'Politeknik', 'Jawa Timur'],

            // Banten
            ['Universitas Sultan Ageng Tirtayasa', 'PTN', 'Banten'],
            ['Universitas Serang Raya', 'PTS', 'Banten'],

            // Bali & Nusa Tenggara
            ['Universitas Udayana', 'PTN', 'Bali'],
            ['Universitas Pendidikan Ganesha', 'PTN', 'Bali'],
            ['Universitas Mataram', 'PTN', 'Nusa Tenggara Barat'],
            ['Universitas Muhammadiyah Mataram', 'PTS', 'Nusa Tenggara Barat'],
            ['Universitas Teknologi Sumbawa', 'PTS', 'Nusa Tenggara Barat'],
            ['Universitas Nusa Tenggara Timur', 'PTN', 'Nusa Tenggara Timur'],
            ['Universitas Nusa Cendana', 'PTN', 'Nusa Tenggara Timur'],
            ['Politeknik Negeri Kupang', 'Politeknik', 'Nusa Tenggara Timur'],

            // Kalimantan
            ['Universitas Tanjungpura', 'PTN', 'Kalimantan Barat'],
            ['Politeknik Negeri Pontianak', 'Politeknik', 'Kalimantan Barat'],
            ['Universitas Palangka Raya', 'PTN', 'Kalimantan Tengah'],
            ['Universitas Lambung Mangkurat', 'PTN', 'Kalimantan Selatan'],
            ['Universitas Mulawarman', 'PTN', 'Kalimantan Timur'],
            ['Politeknik Negeri Samarinda', 'Politeknik', 'Kalimantan Timur'],
            ['Politeknik Pertanian Negeri Samarinda', 'Politeknik', 'Kalimantan Timur'],
            ['Institut Teknologi Kalimantan', 'PTN', 'Kalimantan Timur'],
            ['Universitas Borneo Tarakan', 'PTN', 'Kalimantan Utara'],

            // Sulawesi
            ['Universitas Hasanuddin', 'PTN', 'Sulawesi Selatan'],
            ['Universitas Negeri Makassar', 'PTN', 'Sulawesi Selatan'],
            ['Universitas Muslim Indonesia', 'PTS', 'Sulawesi Selatan'],
            ['Institut Teknologi dan Bisnis Kalla', 'PTS', 'Sulawesi Selatan'],
            ['Universitas Tadulako', 'PTN', 'Sulawesi Tengah'],
            ['Universitas Sam Ratulangi', 'PTN', 'Sulawesi Utara'],
            ['Universitas Negeri Manado', 'PTN', 'Sulawesi Utara'],
            ['Universitas Halu Oleo', 'PTN', 'Sulawesi Tenggara'],
            ['Universitas Sulawesi Barat', 'PTN', 'Sulawesi Barat'],
            ['Universitas Negeri Gorontalo', 'PTN', 'Gorontalo'],

            // Maluku & Papua
            ['Universitas Pattimura', 'PTN', 'Maluku'],
            ['Universitas Khairun', 'PTN', 'Maluku Utara'],
            ['Universitas Cenderawasih', 'PTN', 'Papua'],
            ['Universitas Musamus Merauke', 'PTN', 'Papua Selatan'],
            ['Universitas Papua', 'PTN', 'Papua Barat'],

            // Additional PTS & Politeknik populer dari user.sql
            ['Institut Modern Arsitektur dan Teknologi', 'Institut', 'DKI Jakarta'],
            ['Universitas Merdeka Malang', 'PTS', 'Jawa Timur'],
            ['Politeknik Negeri Media Kreatif', 'Politeknik', 'DKI Jakarta'],
        ];

        // Insert universities with duplicate check
        $inserted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($universities as $univ) {
            [$name, $type, $provinceName] = $univ;

            // Get province_id
            $provinceId = $provinceMap[$provinceName] ?? null;

            if (!$provinceId) {
                echo "  ✗ Error: Province '{$provinceName}' not found for '{$name}'\n";
                $errors++;
                continue;
            }

            // Check if university already exists
            $existing = $this->db->table('universities')
                ->where('name', $name)
                ->get()
                ->getRow();

            if ($existing) {
                echo "  → Skipped: {$name} (already exists)\n";
                $skipped++;
            } else {
                $this->db->table('universities')->insert([
                    'name' => $name,
                    'type' => $type,
                    'province_id' => $provinceId,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                echo "  ✓ Inserted: {$name} ({$type}, {$provinceName})\n";
                $inserted++;
            }
        }

        echo "\n==========================================\n";
        echo "  UNIVERSITY SEEDER COMPLETED             \n";
        echo "==========================================\n";
        echo "✓ Inserted: {$inserted} universities\n";
        echo "→ Skipped: {$skipped} universities (duplicates)\n";
        echo "✗ Errors: {$errors} universities (province not found)\n";
        echo "==========================================\n\n";
    }
}
