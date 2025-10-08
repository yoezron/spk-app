<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ProvinsiSeeder
 * 
 * Seeder untuk master data 38 provinsi Indonesia
 * Data ini digunakan untuk dropdown wilayah dan filtering regional
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Data 38 Provinsi Indonesia (updated 2024)
        $provinsi = [
            ['code' => '11', 'name' => 'Aceh'],
            ['code' => '12', 'name' => 'Sumatera Utara'],
            ['code' => '13', 'name' => 'Sumatera Barat'],
            ['code' => '14', 'name' => 'Riau'],
            ['code' => '15', 'name' => 'Jambi'],
            ['code' => '16', 'name' => 'Sumatera Selatan'],
            ['code' => '17', 'name' => 'Bengkulu'],
            ['code' => '18', 'name' => 'Lampung'],
            ['code' => '19', 'name' => 'Kepulauan Bangka Belitung'],
            ['code' => '21', 'name' => 'Kepulauan Riau'],
            ['code' => '31', 'name' => 'DKI Jakarta'],
            ['code' => '32', 'name' => 'Jawa Barat'],
            ['code' => '33', 'name' => 'Jawa Tengah'],
            ['code' => '34', 'name' => 'DI Yogyakarta'],
            ['code' => '35', 'name' => 'Jawa Timur'],
            ['code' => '36', 'name' => 'Banten'],
            ['code' => '51', 'name' => 'Bali'],
            ['code' => '52', 'name' => 'Nusa Tenggara Barat'],
            ['code' => '53', 'name' => 'Nusa Tenggara Timur'],
            ['code' => '61', 'name' => 'Kalimantan Barat'],
            ['code' => '62', 'name' => 'Kalimantan Tengah'],
            ['code' => '63', 'name' => 'Kalimantan Selatan'],
            ['code' => '64', 'name' => 'Kalimantan Timur'],
            ['code' => '65', 'name' => 'Kalimantan Utara'],
            ['code' => '71', 'name' => 'Sulawesi Utara'],
            ['code' => '72', 'name' => 'Sulawesi Tengah'],
            ['code' => '73', 'name' => 'Sulawesi Selatan'],
            ['code' => '74', 'name' => 'Sulawesi Tenggara'],
            ['code' => '75', 'name' => 'Gorontalo'],
            ['code' => '76', 'name' => 'Sulawesi Barat'],
            ['code' => '81', 'name' => 'Maluku'],
            ['code' => '82', 'name' => 'Maluku Utara'],
            ['code' => '91', 'name' => 'Papua'],
            ['code' => '92', 'name' => 'Papua Barat'],
            ['code' => '93', 'name' => 'Papua Selatan'],
            ['code' => '94', 'name' => 'Papua Tengah'],
            ['code' => '95', 'name' => 'Papua Pegunungan'],
            ['code' => '96', 'name' => 'Papua Barat Daya'],
        ];

        // Insert data provinsi
        $inserted = 0;
        $skipped = 0;

        foreach ($provinsi as $prov) {
            // Cek apakah provinsi sudah ada
            $existing = $this->db->table('provinces')
                ->where('code', $prov['code'])
                ->get()
                ->getRow();

            if (!$existing) {
                // Insert provinsi baru
                $this->db->table('provinces')->insert([
                    'code'       => $prov['code'],
                    'name'       => $prov['name'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            } else {
                $skipped++;
            }
        }

        echo "\n";
        echo "==========================================\n";
        echo "  PROVINSI SEEDER COMPLETED               \n";
        echo "==========================================\n";
        echo "Total Provinsi: " . count($provinsi) . "\n";
        echo "✓ Inserted: {$inserted}\n";
        echo "→ Skipped (already exists): {$skipped}\n";
        echo "==========================================\n";
        echo "\nRegion Coverage:\n";
        echo "- Sumatera: 10 provinsi\n";
        echo "- Jawa & Bali: 7 provinsi\n";
        echo "- Nusa Tenggara: 2 provinsi\n";
        echo "- Kalimantan: 5 provinsi\n";
        echo "- Sulawesi: 6 provinsi\n";
        echo "- Maluku: 2 provinsi\n";
        echo "- Papua: 6 provinsi\n";
        echo "==========================================\n\n";
    }
}
