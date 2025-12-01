<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVerificationFieldsToMemberProfiles extends Migration
{
    public function up()
    {
        $fields = [
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'join_date',
                'comment' => 'Timestamp when member was verified/approved'
            ],
            'verified_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'verified_at',
                'comment' => 'User ID of admin who verified this member'
            ],
        ];

        $this->forge->addColumn('member_profiles', $fields);

        // Add foreign key for verified_by
        $this->forge->addForeignKey(
            'verified_by',
            'users',
            'id',
            'SET NULL',
            'CASCADE',
            'fk_member_profiles_verified_by'
        );
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('member_profiles', 'fk_member_profiles_verified_by');

        // Drop columns
        $this->forge->dropColumn('member_profiles', ['verified_at', 'verified_by']);
    }
}
