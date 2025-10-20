<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActivationFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'activation_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Token untuk aktivasi akun member yang diimport'
            ],
            'activation_token_expires_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Waktu kadaluarsa token aktivasi (7 hari dari generate)'
            ],
            'activated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Waktu member berhasil aktivasi akun'
            ],
        ];

        $this->forge->addColumn('users', $fields);

        // Add index for faster token lookup (MySQL compatible)
        $this->db->query('CREATE INDEX idx_activation_token ON users(activation_token)');
    }

    public function down()
    {
        // Drop index first
        $this->db->query('DROP INDEX idx_activation_token ON users');

        // Drop columns
        $this->forge->dropColumn('users', [
            'activation_token',
            'activation_token_expires_at',
            'activated_at'
        ]);
    }
}
