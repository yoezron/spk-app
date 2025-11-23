<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create Missing Master Data Tables
 *
 * Creates tables that were referenced in models but missing from initial migration:
 * - payers: Entities that pay member salaries (university, foundation, etc.)
 * - university_types: Types of universities (PTN, PTS, etc.)
 */
class CreateMissingMasterTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL PAYERS (Pemberi Gaji)
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
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('is_active');
        $this->forge->createTable('payers');

        // ========================================
        // 2. TABEL UNIVERSITY_TYPES (Jenis PT)
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
                'constraint' => 100,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('code');
        $this->forge->addKey('is_active');
        $this->forge->createTable('university_types');

        // ========================================
        // 3. INSERT DEFAULT DATA - PAYERS
        // ========================================
        $payers = [
            [
                'name' => 'Perguruan Tinggi',
                'description' => 'Gaji dibayarkan oleh Perguruan Tinggi',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Yayasan',
                'description' => 'Gaji dibayarkan oleh Yayasan',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Pemerintah',
                'description' => 'Gaji dibayarkan oleh Pemerintah',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Pemberi gaji lainnya',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('payers')->insertBatch($payers);

        // ========================================
        // 4. INSERT DEFAULT DATA - UNIVERSITY_TYPES
        // ========================================
        $universityTypes = [
            [
                'name' => 'Perguruan Tinggi Negeri (PTN)',
                'code' => 'PTN',
                'description' => 'Perguruan tinggi yang dikelola oleh pemerintah',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Perguruan Tinggi Swasta (PTS)',
                'code' => 'PTS',
                'description' => 'Perguruan tinggi yang dikelola oleh swasta/yayasan',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Perguruan Tinggi Keagamaan Negeri (PTKN)',
                'code' => 'PTKN',
                'description' => 'Perguruan tinggi keagamaan negeri',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Perguruan Tinggi Keagamaan Swasta (PTKS)',
                'code' => 'PTKS',
                'description' => 'Perguruan tinggi keagamaan swasta',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Perguruan Tinggi Luar Negeri',
                'code' => 'PTLN',
                'description' => 'Perguruan tinggi luar negeri',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('university_types')->insertBatch($universityTypes);
    }

    public function down()
    {
        $this->forge->dropTable('university_types', true);
        $this->forge->dropTable('payers', true);
    }
}
