<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * CreateRbacTables Migration
 * 
 * Membuat tabel untuk Dynamic RBAC (Role-Based Access Control)
 * Tabel ini terpisah dari Shield untuk mendukung manajemen role & permission via UI
 * 
 * Tables:
 * - auth_groups (roles/groups)
 * - auth_permissions (permissions)
 * - auth_groups_permissions (pivot table)
 * 
 * @package App\Database\Migrations
 * @author  SPK Development Team
 * @version 1.0.0
 */
class CreateRbacTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ========================================
        // 1. TABLE: auth_groups (Roles/Groups)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Nama role/group (e.g., Super Admin, Pengurus)',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Deskripsi role/group',
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
        $this->forge->addUniqueKey('title');
        $this->forge->createTable('auth_groups', true);

        echo "✓ Table 'auth_groups' created.\n";

        // ========================================
        // 2. TABLE: auth_permissions
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
                'comment'    => 'Permission key (e.g., member.view, member.create)',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Deskripsi permission',
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('auth_permissions', true);

        echo "✓ Table 'auth_permissions' created.\n";

        // ========================================
        // 3. TABLE: auth_groups_permissions (Pivot)
        // ========================================
        $this->forge->addField([
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey(['group_id', 'permission_id']);
        $this->forge->addForeignKey('group_id', 'auth_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'auth_permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_groups_permissions', true);

        echo "✓ Table 'auth_groups_permissions' created.\n";

        echo "\n";
        echo "==========================================\n";
        echo "  RBAC TABLES CREATED SUCCESSFULLY!      \n";
        echo "==========================================\n";
        echo "✓ auth_groups (roles)\n";
        echo "✓ auth_permissions\n";
        echo "✓ auth_groups_permissions (pivot)\n";
        echo "==========================================\n\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop tables in reverse order (respect foreign keys)
        $this->forge->dropTable('auth_groups_permissions', true);
        $this->forge->dropTable('auth_permissions', true);
        $this->forge->dropTable('auth_groups', true);

        echo "✓ RBAC tables dropped.\n";
    }
}
