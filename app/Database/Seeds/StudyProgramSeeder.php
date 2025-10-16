<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * StudyProgramSeeder
 * 
 * Seeder untuk program studi populer di Indonesia
 * Dikategorikan berdasarkan bidang ilmu
 * Total: 100+ program studi
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.0.0
 */
class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "==========================================\n";
        echo "  SEEDING STUDY PROGRAMS (100+ Prodi)     \n";
        echo "==========================================\n\n";

        // Study programs grouped by field
        $programs = [
            // Teknik & Teknologi
            ['name' => 'Teknik Informatika', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknik Elektro', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknik Mesin', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknik Sipil', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknik Industri', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknik Kimia', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Arsitektur', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Sistem Informasi', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknologi Informasi', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Teknik Komputer', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Manajemen Informatika', 'level' => 'D3', 'field' => 'Teknik'],
            ['name' => 'Teknik Telekomunikasi', 'level' => 'S1', 'field' => 'Teknik'],
            ['name' => 'Perencanaan Wilayah dan Kota', 'level' => 'S1', 'field' => 'Teknik'],

            // Ekonomi & Bisnis
            ['name' => 'Manajemen', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Akuntansi', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Ekonomi Pembangunan', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Ilmu Ekonomi', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Bisnis Digital', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Ekonomi Syariah', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Administrasi Bisnis', 'level' => 'S1', 'field' => 'Ekonomi'],
            ['name' => 'Kewirausahaan', 'level' => 'S1', 'field' => 'Ekonomi'],

            // Pendidikan
            ['name' => 'Pendidikan Matematika', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Bahasa Inggris', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Bahasa Indonesia', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Fisika', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Kimia', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Biologi', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Guru Sekolah Dasar', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'PGSD', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Anak Usia Dini', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Bimbingan dan Konseling', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Teknologi Pendidikan', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Sejarah', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Geografi', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Ekonomi', 'level' => 'S1', 'field' => 'Pendidikan'],
            ['name' => 'Pendidikan Jasmani', 'level' => 'S1', 'field' => 'Pendidikan'],

            // Sains & Matematika
            ['name' => 'Matematika', 'level' => 'S1', 'field' => 'MIPA'],
            ['name' => 'Fisika', 'level' => 'S1', 'field' => 'MIPA'],
            ['name' => 'Kimia', 'level' => 'S1', 'field' => 'MIPA'],
            ['name' => 'Biologi', 'level' => 'S1', 'field' => 'MIPA'],
            ['name' => 'Statistika', 'level' => 'S1', 'field' => 'MIPA'],
            ['name' => 'Ilmu Komputer', 'level' => 'S1', 'field' => 'MIPA'],

            // Pertanian & Kehutanan
            ['name' => 'Agroteknologi', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Agribisnis', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Teknologi Hasil Pertanian', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Teknologi Hasil Perkebunan', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Teknologi Industri Pertanian', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Peternakan', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Kehutanan', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Ilmu Tanah', 'level' => 'S1', 'field' => 'Pertanian'],
            ['name' => 'Budidaya Perairan', 'level' => 'S1', 'field' => 'Pertanian'],

            // Kesehatan
            ['name' => 'Kedokteran', 'level' => 'S1', 'field' => 'Kesehatan'],
            ['name' => 'Kedokteran Gigi', 'level' => 'S1', 'field' => 'Kesehatan'],
            ['name' => 'Keperawatan', 'level' => 'S1', 'field' => 'Kesehatan'],
            ['name' => 'Farmasi', 'level' => 'S1', 'field' => 'Kesehatan'],
            ['name' => 'Kesehatan Masyarakat', 'level' => 'S1', 'field' => 'Kesehatan'],
            ['name' => 'Gizi', 'level' => 'S1', 'field' => 'Kesehatan'],
            ['name' => 'Kebidanan', 'level' => 'D3', 'field' => 'Kesehatan'],
            ['name' => 'Psikologi', 'level' => 'S1', 'field' => 'Kesehatan'],

            // Hukum & Politik
            ['name' => 'Ilmu Hukum', 'level' => 'S1', 'field' => 'Hukum'],
            ['name' => 'Hukum', 'level' => 'S1', 'field' => 'Hukum'],
            ['name' => 'Ilmu Politik', 'level' => 'S1', 'field' => 'Politik'],
            ['name' => 'Politik', 'level' => 'S1', 'field' => 'Politik'],
            ['name' => 'Hubungan Internasional', 'level' => 'S1', 'field' => 'Politik'],
            ['name' => 'Administrasi Publik', 'level' => 'S1', 'field' => 'Politik'],
            ['name' => 'Ilmu Pemerintahan', 'level' => 'S1', 'field' => 'Politik'],

            // Sosial & Humaniora
            ['name' => 'Sosiologi', 'level' => 'S1', 'field' => 'Sosial'],
            ['name' => 'Antropologi', 'level' => 'S1', 'field' => 'Sosial'],
            ['name' => 'Ilmu Komunikasi', 'level' => 'S1', 'field' => 'Sosial'],
            ['name' => 'Jurnalistik', 'level' => 'S1', 'field' => 'Sosial'],
            ['name' => 'Sastra Indonesia', 'level' => 'S1', 'field' => 'Sastra'],
            ['name' => 'Sastra Inggris', 'level' => 'S1', 'field' => 'Sastra'],
            ['name' => 'Bahasa dan Sastra Arab', 'level' => 'S1', 'field' => 'Sastra'],
            ['name' => 'Sejarah', 'level' => 'S1', 'field' => 'Humaniora'],
            ['name' => 'Filsafat', 'level' => 'S1', 'field' => 'Humaniora'],
            ['name' => 'Linguistik', 'level' => 'S1', 'field' => 'Humaniora'],

            // Seni & Desain
            ['name' => 'Desain Komunikasi Visual', 'level' => 'S1', 'field' => 'Seni'],
            ['name' => 'Desain Grafis', 'level' => 'S1', 'field' => 'Seni'],
            ['name' => 'Desain Interior', 'level' => 'S1', 'field' => 'Seni'],
            ['name' => 'Seni Rupa', 'level' => 'S1', 'field' => 'Seni'],
            ['name' => 'Seni Musik', 'level' => 'S1', 'field' => 'Seni'],
            ['name' => 'Tari', 'level' => 'S1', 'field' => 'Seni'],
            ['name' => 'Film dan Televisi', 'level' => 'S1', 'field' => 'Seni'],

            // Agama & Syariah
            ['name' => 'Ilmu Alquran dan Tafsir', 'level' => 'S1', 'field' => 'Agama'],
            ['name' => 'Pendidikan Agama Islam', 'level' => 'S1', 'field' => 'Agama'],
            ['name' => 'Hukum Keluarga Islam', 'level' => 'S1', 'field' => 'Agama'],
            ['name' => 'Perbankan Syariah', 'level' => 'S1', 'field' => 'Agama'],
            ['name' => 'Manajemen Dakwah', 'level' => 'S1', 'field' => 'Agama'],

            // Perikanan & Kelautan
            ['name' => 'Ilmu Kelautan', 'level' => 'S1', 'field' => 'Kelautan'],
            ['name' => 'Perikanan', 'level' => 'S1', 'field' => 'Kelautan'],
            ['name' => 'Teknologi Hasil Perikanan', 'level' => 'S1', 'field' => 'Kelautan'],
            ['name' => 'Manajemen Sumberdaya Perairan', 'level' => 'S1', 'field' => 'Kelautan'],

            // Pariwisata
            ['name' => 'Pariwisata', 'level' => 'S1', 'field' => 'Pariwisata'],
            ['name' => 'Perhotelan', 'level' => 'D3', 'field' => 'Pariwisata'],
            ['name' => 'Manajemen Perhotelan', 'level' => 'S1', 'field' => 'Pariwisata'],

            // Administrasi
            ['name' => 'Administrasi Negara', 'level' => 'S1', 'field' => 'Administrasi'],
            ['name' => 'Administrasi Niaga', 'level' => 'S1', 'field' => 'Administrasi'],
            ['name' => 'Administrasi Perkantoran', 'level' => 'D3', 'field' => 'Administrasi'],

            // Lainnya
            ['name' => 'Perpustakaan dan Sains Informasi', 'level' => 'S1', 'field' => 'Lainnya'],
            ['name' => 'Kearsipan', 'level' => 'D3', 'field' => 'Lainnya'],
        ];

        // Insert study programs with duplicate check
        $inserted = 0;
        $skipped = 0;

        foreach ($programs as $program) {
            // Check if study program already exists
            $existing = $this->db->table('study_programs')
                ->where('name', $program['name'])
                ->where('level', $program['level'])
                ->get()
                ->getRow();

            if ($existing) {
                echo "  → Skipped: {$program['name']} ({$program['level']}) - already exists\n";
                $skipped++;
            } else {
                $this->db->table('study_programs')->insert([
                    'name' => $program['name'],
                    'level' => $program['level'],
                    'field' => $program['field'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                echo "  ✓ Inserted: {$program['name']} ({$program['level']}, {$program['field']})\n";
                $inserted++;
            }
        }

        echo "\n==========================================\n";
        echo "  STUDY PROGRAM SEEDER COMPLETED          \n";
        echo "==========================================\n";
        echo "✓ Inserted: {$inserted} programs\n";
        echo "→ Skipped: {$skipped} programs (duplicates)\n";
        echo "==========================================\n\n";
    }
}
