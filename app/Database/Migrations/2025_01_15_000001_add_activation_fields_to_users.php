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

        // Add index for faster token lookup
        $this->forge->addKey('activation_token', false, false, 'idx_activation_token');
        $this->db->query('CREATE INDEX idx_activation_token ON users(activation_token) WHERE activation_token IS NOT NULL');
    }

    public function down()
    {
        // Drop index first
        $this->forge->dropKey('users', 'idx_activation_token');

        // Drop columns
        $this->forge->dropColumn('users', [
            'activation_token',
            'activation_token_expires_at',
            'activated_at'
        ]);
    }
}
