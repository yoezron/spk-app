<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMemberProfilesTable extends Migration
{
    public function up()
    {
        // ========================================
        // TABEL MEMBER_PROFILES
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'member_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
                'null'       => true,
                'comment'    => 'Nomor Anggota Unik'
            ],

            // Personal Information
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Nomor Induk Kependudukan'
            ],
            'nidn_nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'comment'    => 'NIDN untuk Dosen atau NIP'
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['Laki-laki', 'Perempuan'],
                'null'       => true,
            ],
            'birth_place' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'birth_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'whatsapp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            // Address Information
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'province_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'regency_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],

            // Employment Information
            'university_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'study_program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'employment_status_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'salary_payer' => [
                'type'       => 'ENUM',
                'constraint' => ['KAMPUS', 'PEMERINTAH', 'YAYASAN', 'LAINNYA'],
                'default'    => 'KAMPUS',
                'comment'    => 'Pemberi Gaji'
            ],
            'salary_range_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'job_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Jabatan'
            ],
            'work_start_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Tanggal Mulai Bekerja'
            ],

            // Region & Membership
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Wilayah Koordinator'
            ],
            'membership_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'inactive', 'suspended', 'expired'],
                'default'    => 'pending',
            ],
            'join_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Tanggal Bergabung'
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal Disetujui'
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID yang Menyetujui'
            ],

            // Additional Information
            'skills' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Keahlian (semicolon separated)'
            ],
            'motivation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Motivasi Bergabung'
            ],

            // Profile Media
            'photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path to profile photo'
            ],
            'id_card_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path to ID Card (KTP)'
            ],
            'employment_letter_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path to employment letter'
            ],

            // Member Card
            'card_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'unique'     => true,
                'comment'    => 'Nomor Kartu Anggota'
            ],
            'card_issued_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal Kartu Diterbitkan'
            ],
            'card_valid_until' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Kartu Berlaku Sampai'
            ],
            'qr_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'unique'     => true,
                'comment'    => 'QR Code untuk verifikasi'
            ],

            // Migration from old system
            'legacy_member_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'ID dari sistem lama untuk migrasi'
            ],
            'is_legacy_member' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Flag anggota dari sistem lama'
            ],

            // Metadata
            'last_active_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Terakhir login/aktif'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan admin'
            ],

            // Timestamps
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Soft delete'
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id', true);

        // Foreign Keys
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('province_id', 'provinces', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('regency_id', 'regencies', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('university_id', 'universities', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('study_program_id', 'study_programs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('employment_status_id', 'employment_statuses', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('salary_range_id', 'salary_ranges', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'SET NULL', 'CASCADE');

        // Indexes for better performance
        $this->forge->addKey('user_id');

        $this->forge->addKey('membership_status');
        $this->forge->addKey('university_id');
        $this->forge->addKey('region_id');
        $this->forge->addKey(['province_id', 'regency_id']);

        $this->forge->addKey('legacy_member_id');

        $this->forge->createTable('member_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('member_profiles', true);
    }
}
