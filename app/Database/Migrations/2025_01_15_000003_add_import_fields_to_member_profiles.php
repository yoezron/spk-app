<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImportFieldsToMemberProfiles extends Migration
{
    public function up()
    {
        $fields = [
            'imported_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Waktu data member diimport dari Excel'
            ],
            'import_batch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID batch import (FK to import_logs)'
            ],
        ];

        $this->forge->addColumn('member_profiles', $fields);

        // Add foreign key
        $this->forge->addForeignKey('import_batch_id', 'import_logs', 'id', 'SET NULL', 'CASCADE', 'member_profiles');

        // Add index for filtering imported members
        $this->forge->addKey('import_batch_id', false, false, 'idx_import_batch');
        $this->db->query('CREATE INDEX idx_import_batch ON member_profiles(import_batch_id) WHERE import_batch_id IS NOT NULL');
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('member_profiles', 'member_profiles_import_batch_id_foreign');

        // Drop index
        $this->forge->dropKey('member_profiles', 'idx_import_batch');

        // Drop columns
        $this->forge->dropColumn('member_profiles', [
            'imported_at',
            'import_batch_id'
        ]);
    }
}
