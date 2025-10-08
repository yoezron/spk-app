<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrgStructureTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL ORG_UNITS (Unit Organisasi)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Nama unit organisasi'
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'unique'     => true,
                'comment'    => 'Slug untuk URL'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi unit'
            ],

            // Hierarchy
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Parent unit (untuk sub-unit)'
            ],
            'level' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'comment'    => 'Level hierarki (1=pusat, 2=wilayah, dst)'
            ],

            // Scope
            'scope' => [
                'type'       => 'ENUM',
                'constraint' => ['pusat', 'wilayah', 'kampus', 'departemen', 'divisi', 'seksi'],
                'default'    => 'pusat',
                'comment'    => 'Cakupan unit organisasi'
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID wilayah (jika scope = wilayah)'
            ],
            'university_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kampus (jika scope = kampus)'
            ],

            // Period/Masa Jabatan
            'period_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama periode (contoh: "2023-2026")'
            ],
            'period_start' => [
                'type' => 'DATE',
                'comment' => 'Awal masa jabatan'
            ],
            'period_end' => [
                'type' => 'DATE',
                'comment' => 'Akhir masa jabatan'
            ],
            'is_current' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Periode aktif saat ini'
            ],

            // Unit Info
            'vision' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Visi unit'
            ],
            'mission' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Misi unit'
            ],
            'objectives' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Tujuan unit'
            ],
            'programs' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Program kerja (JSON atau text)'
            ],

            // Display Settings
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan tampilan'
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Warna untuk visualisasi (hex code)'
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Icon class'
            ],

            // Status
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Aktif, 0 = Nonaktif'
            ],
            'show_in_chart' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Tampilkan di bagan organisasi'
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('parent_id', 'org_units', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('university_id', 'universities', 'id', 'SET NULL', 'CASCADE');

        $this->forge->addKey('parent_id');
        $this->forge->addKey('scope');
        $this->forge->addKey('region_id');
        $this->forge->addKey(['is_current', 'period_start', 'period_end']);
        $this->forge->addKey(['is_active', 'display_order']);
        $this->forge->addKey('level');
        $this->forge->createTable('org_units');

        // ========================================
        // 2. TABEL ORG_POSITIONS (Jabatan)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Unit organisasi'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Nama jabatan'
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Slug jabatan'
            ],
            'short_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Singkatan/sebutan jabatan'
            ],

            // Position Type
            'position_type' => [
                'type'       => 'ENUM',
                'constraint' => ['executive', 'structural', 'functional', 'coordinator', 'staff'],
                'default'    => 'structural',
                'comment'    => 'Tipe jabatan'
            ],
            'position_level' => [
                'type'       => 'ENUM',
                'constraint' => ['top', 'middle', 'lower'],
                'default'    => 'middle',
                'comment'    => 'Level jabatan'
            ],

            // Job Description
            'job_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Uraian tugas'
            ],
            'responsibilities' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Tanggung jawab (JSON atau text)'
            ],
            'authorities' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Wewenang (JSON atau text)'
            ],
            'requirements' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Persyaratan jabatan'
            ],

            // Reporting Line
            'reports_to' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Atasan langsung (position_id)'
            ],

            // Display Settings
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan tampilan dalam unit'
            ],
            'is_leadership' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Jabatan pimpinan utama (highlight)'
            ],

            // Capacity
            'max_holders' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'comment'    => 'Maksimal pemegang jabatan (1=tunggal, >1=plural)'
            ],
            'current_holders' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah pemegang jabatan saat ini'
            ],

            // Status
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_vacant' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Lowong/belum terisi'
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('unit_id', 'org_units', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reports_to', 'org_positions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('unit_id');

        $this->forge->addKey('reports_to');
        $this->forge->addKey(['unit_id', 'display_order']);
        $this->forge->addKey(['is_active', 'is_vacant']);
        $this->forge->createTable('org_positions');

        // ========================================
        // 3. TABEL ORG_ASSIGNMENTS (Penempatan/Penugasan)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Jabatan'
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Anggota yang ditempatkan'
            ],

            // Assignment Type
            'assignment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['permanent', 'acting', 'temporary', 'concurrent'],
                'default'    => 'permanent',
                'comment'    => 'Tipe penugasan: definitif/plt/plh/rangkap'
            ],
            'assignment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'completed', 'terminated', 'on_leave'],
                'default'    => 'active',
            ],

            // Period
            'started_at' => [
                'type' => 'DATE',
                'comment' => 'Tanggal mulai menjabat'
            ],
            'ended_at' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Tanggal selesai (NULL = masih menjabat)'
            ],
            'expected_end' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Perkiraan akhir masa jabatan'
            ],

            // Appointment Details
            'appointment_decree' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nomor SK pengangkatan'
            ],
            'appointment_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Tanggal SK'
            ],
            'appointment_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file SK'
            ],

            // Termination Details
            'termination_reason' => [
                'type'       => 'ENUM',
                'constraint' => ['completed', 'resigned', 'dismissed', 'transferred', 'retired', 'other'],
                'null'       => true,
                'comment'    => 'Alasan berakhir'
            ],
            'termination_note' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan pemberhentian'
            ],
            'termination_decree' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nomor SK pemberhentian'
            ],

            // Performance & Notes
            'performance_note' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan kinerja'
            ],
            'achievements' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Pencapaian selama menjabat (JSON atau text)'
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan admin'
            ],

            // Approval
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User yang menyetujui penempatan'
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Contact & Display
            'show_in_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Tampilkan di struktur organisasi publik'
            ],
            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Email khusus jabatan'
            ],
            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'No. telp khusus jabatan'
            ],

            // Display Order (jika ada multiple holders)
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan jika ada multiple holders'
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('position_id', 'org_positions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('position_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('assignment_status');
        $this->forge->addKey(['position_id', 'assignment_status']);
        $this->forge->addKey(['user_id', 'assignment_status']);
        $this->forge->addKey(['started_at', 'ended_at']);
        $this->forge->addKey('show_in_public');
        $this->forge->createTable('org_assignments');

        // ========================================
        // 4. TABEL ORG_COMMITTEES (Komite/Panitia Ad-Hoc)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Nama komite/panitia'
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Committee Type
            'committee_type' => [
                'type'       => 'ENUM',
                'constraint' => ['standing', 'adhoc', 'task_force', 'working_group'],
                'default'    => 'adhoc',
                'comment'    => 'Tipe komite'
            ],
            'purpose' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Tujuan pembentukan komite'
            ],

            // Parent
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Unit organisasi induk'
            ],

            // Chairperson
            'chairperson_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Ketua komite'
            ],
            'secretary_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Sekretaris komite'
            ],

            // Period
            'formed_date' => [
                'type' => 'DATE',
                'comment' => 'Tanggal dibentuk'
            ],
            'dissolved_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Tanggal dibubarkan'
            ],

            // Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'completed', 'dissolved', 'on_hold'],
                'default'    => 'active',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],

            // Members count
            'members_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('unit_id', 'org_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('chairperson_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('secretary_user_id', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->addKey('status');
        $this->forge->addKey('committee_type');
        $this->forge->createTable('org_committees');

        // ========================================
        // 5. TABEL ORG_COMMITTEE_MEMBERS (Anggota Komite)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'committee_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Peran dalam komite (anggota, koordinator, dll)'
            ],
            'joined_at' => [
                'type' => 'DATE',
            ],
            'left_at' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('committee_id', 'org_committees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['committee_id', 'user_id']);
        $this->forge->addKey('committee_id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('org_committee_members');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('org_committee_members', true);
        $this->forge->dropTable('org_committees', true);
        $this->forge->dropTable('org_assignments', true);
        $this->forge->dropTable('org_positions', true);
        $this->forge->dropTable('org_units', true);
    }
}
