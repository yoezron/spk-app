<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImportLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'imported_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User ID yang melakukan import (FK to users)'
            ],
            'filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Nama file Excel yang diupload'
            ],
            'total_rows' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Total baris yang diproses (termasuk invalid)'
            ],
            'success_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Jumlah data berhasil diimport'
            ],
            'failed_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Jumlah data gagal (validasi error)'
            ],
            'duplicate_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Jumlah data duplicate (email/NIK sudah ada)'
            ],
            'error_details' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Detail error per row dalam format JSON'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['processing', 'completed', 'failed'],
                'default'    => 'processing',
                'comment'    => 'Status proses import'
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
        $this->forge->addKey('imported_by');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->addForeignKey('imported_by', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('import_logs');
    }

    public function down()
    {
        $this->forge->dropTable('import_logs');
    }
}
