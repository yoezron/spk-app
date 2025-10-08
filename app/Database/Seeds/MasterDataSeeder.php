<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MasterDataSeeder
 * 
 * Seeder untuk master data kepegawaian dan perguruan tinggi
 * - Status Kepegawaian (Dosen, Tendik, dll)
 * - Pemberi Gaji (Kampus, Kementerian, dll)
 * - Range Gaji
 * - Jenis Perguruan Tinggi
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ==========================================
        // 1. STATUS KEPEGAWAIAN
        // ==========================================
        $statusKepegawaian = [
            ['name' => 'Dosen Tetap PNS', 'description' => 'Dosen tetap berstatus Pegawai Negeri Sipil'],
            ['name' => 'Dosen Tetap Non PNS', 'description' => 'Dosen tetap yayasan/swasta bukan PNS'],
            ['name' => 'Dosen Tidak Tetap/Honorer', 'description' => 'Dosen honorer atau kontrak'],
            ['name' => 'Dosen Luar Biasa (DLB)', 'description' => 'Dosen tamu/tidak tetap'],
            ['name' => 'Tendik PNS', 'description' => 'Tenaga Kependidikan PNS'],
            ['name' => 'Tendik Non PNS', 'description' => 'Tenaga Kependidikan non PNS/yayasan'],
            ['name' => 'Staf Administrasi PNS', 'description' => 'Staff administrasi berstatus PNS'],
            ['name' => 'Staf Administrasi Non PNS', 'description' => 'Staff administrasi swasta/kontrak'],
            ['name' => 'Tenaga Kebersihan', 'description' => 'Petugas kebersihan/cleaning service'],
            ['name' => 'Tenaga Keamanan', 'description' => 'Satpam/security'],
            ['name' => 'Teknisi/Laboran', 'description' => 'Teknisi laboratorium/workshop'],
            ['name' => 'Pustakawan', 'description' => 'Pengelola perpustakaan'],
            ['name' => 'Lainnya', 'description' => 'Status kepegawaian lainnya'],
        ];

        echo "Inserting Status Kepegawaian...\n";
        $this->insertData('employment_statuses', $statusKepegawaian);

        // ==========================================
        // 2. PEMBERI GAJI
        // ==========================================
        // Note: Tabel salary_providers tidak ada di migration existing
        // Skip untuk sekarang, bisa ditambahkan nanti jika diperlukan
        echo "Skipping Pemberi Gaji (table not exists)...\n";
        echo "  → salary_providers table not found, skipped\n";

        // ==========================================
        // 3. RANGE GAJI
        // ==========================================
        $rangeGaji = [
            ['name' => 'Di bawah Rp 1.500.000', 'min_amount' => 0, 'max_amount' => 1500000, 'display_order' => 1, 'is_active' => 1],
            ['name' => 'Rp 1.500.000 - Rp 3.000.000', 'min_amount' => 1500000, 'max_amount' => 3000000, 'display_order' => 2, 'is_active' => 1],
            ['name' => 'Rp 3.000.001 - Rp 6.000.000', 'min_amount' => 3000001, 'max_amount' => 6000000, 'display_order' => 3, 'is_active' => 1],
            ['name' => 'Rp 6.000.001 - Rp 10.000.000', 'min_amount' => 6000001, 'max_amount' => 10000000, 'display_order' => 4, 'is_active' => 1],
            ['name' => 'Rp 10.000.001 - Rp 15.000.000', 'min_amount' => 10000001, 'max_amount' => 15000000, 'display_order' => 5, 'is_active' => 1],
            ['name' => 'Di atas Rp 15.000.000', 'min_amount' => 15000001, 'max_amount' => 999999999, 'display_order' => 6, 'is_active' => 1],
            ['name' => 'Golongan I (Ia, Ib, Ic, Id)', 'min_amount' => 0, 'max_amount' => 0, 'display_order' => 7, 'is_active' => 1],
            ['name' => 'Golongan II (IIa, IIb, IIc, IId)', 'min_amount' => 0, 'max_amount' => 0, 'display_order' => 8, 'is_active' => 1],
            ['name' => 'Golongan III (IIIa, IIIb, IIIc, IIId)', 'min_amount' => 0, 'max_amount' => 0, 'display_order' => 9, 'is_active' => 1],
            ['name' => 'Golongan IV (IVa, IVb, IVc, IVd, IVe)', 'min_amount' => 0, 'max_amount' => 0, 'display_order' => 10, 'is_active' => 1],
        ];

        echo "Inserting Range Gaji...\n";
        $this->insertData('salary_ranges', $rangeGaji);

        // ==========================================
        // 4. JENIS PERGURUAN TINGGI
        // ==========================================
        // Note: Tabel university_types tidak ada di migration existing
        // Skip untuk sekarang
        echo "Skipping Jenis Perguruan Tinggi (table not exists)...\n";
        echo "  → university_types table not found, skipped\n";

        // ==========================================
        // 5. STATUS KAMPUS (Negeri/Swasta)
        // ==========================================
        // Note: Tabel university_statuses tidak ada di migration existing
        // Skip untuk sekarang
        echo "Skipping Status Kampus (table not exists)...\n";
        echo "  → university_statuses table not found, skipped\n";

        // ==========================================
        // SUMMARY
        // ==========================================
        echo "\n";
        echo "==========================================\n";
        echo "  MASTER DATA SEEDER COMPLETED            \n";
        echo "==========================================\n";
        echo "✓ Status Kepegawaian: " . count($statusKepegawaian) . " records\n";
        echo "→ Pemberi Gaji: SKIPPED (table not exists)\n";
        echo "✓ Range Gaji: " . count($rangeGaji) . " records\n";
        echo "→ Jenis PT: SKIPPED (table not exists)\n";
        echo "→ Status Kampus: SKIPPED (table not exists)\n";
        echo "==========================================\n";
        echo "Total Master Data Inserted: " . (
            count($statusKepegawaian) +
            count($rangeGaji)
        ) . " records\n";
        echo "==========================================\n";
        echo "\n⚠ NOTE: Some tables were skipped because:\n";
        echo "  - Tables don't exist in current migration\n";
        echo "\nSkipped master data can be added via:\n";
        echo "  1. Admin panel bulk import (recommended)\n";
        echo "  2. Manual entry through admin interface\n";
        echo "  3. Custom migration if needed\n";
        echo "==========================================\n\n";
    }

    /**
     * Helper function to insert data with duplicate check
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return void
     */
    private function insertData(string $table, array $data): void
    {
        $inserted = 0;
        $skipped = 0;

        foreach ($data as $item) {
            // Cek duplikasi berdasarkan nama
            $existing = $this->db->table($table)
                ->where('name', $item['name'] ?? $item['range'] ?? '')
                ->get()
                ->getRow();

            if (!$existing) {
                // Tambahkan timestamp
                $item['created_at'] = date('Y-m-d H:i:s');
                $item['updated_at'] = date('Y-m-d H:i:s');

                // Insert data
                $this->db->table($table)->insert($item);
                $inserted++;
            } else {
                $skipped++;
            }
        }

        echo "  ✓ {$inserted} inserted, → {$skipped} skipped\n";
    }
}
