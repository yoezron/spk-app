<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add verified_at and verified_by fields to member_profiles table
 *
 * Kolom-kolom ini digunakan untuk tracking approval anggota
 * Berbeda dengan approved_at/approved_by yang merupakan legacy fields
 */
class AddVerifiedFieldsToMemberProfiles extends Migration
{
    public function up()
    {
        $fields = [
            'verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'approved_by',
                'comment' => 'Tanggal Verifikasi Anggota'
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'verified_at',
                'comment'    => 'User ID yang Memverifikasi'
            ],
        ];

        $this->forge->addColumn('member_profiles', $fields);

        // Add index for better query performance
        $this->forge->addKey('verified_at', false, false, 'idx_verified_at');
        $this->db->query('ALTER TABLE member_profiles ADD INDEX idx_verified_at (verified_at)');
    }

    public function down()
    {
        // Drop index first
        $this->forge->dropKey('member_profiles', 'idx_verified_at');

        // Drop columns
        $this->forge->dropColumn('member_profiles', ['verified_at', 'verified_by']);
    }
}
