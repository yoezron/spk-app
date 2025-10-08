<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenusTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL MENUS (Menu Utama)
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
                'comment'    => 'Judul menu yang ditampilkan'
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Icon class (Material Icons, FontAwesome, dll)'
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'URL static (jika ada)'
            ],
            'route' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Route name (jika menggunakan named route)'
            ],
            'permission_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Permission key yang diperlukan untuk akses menu'
            ],
            'menu_type' => [
                'type'       => 'ENUM',
                'constraint' => ['sidebar', 'header', 'footer'],
                'default'    => 'sidebar',
                'comment'    => 'Tipe menu (sidebar, header, footer)'
            ],
            'target' => [
                'type'       => 'ENUM',
                'constraint' => ['_self', '_blank'],
                'default'    => '_self',
                'comment'    => 'Target link'
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan tampilan menu'
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Active, 0 = Inactive'
            ],
            'is_header' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Header separator, tidak punya link'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi menu (untuk tooltip atau info)'
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
        $this->forge->addKey('permission_key');
        $this->forge->addKey('display_order');
        $this->forge->addKey(['is_active', 'display_order']);
        $this->forge->addKey('menu_type');
        $this->forge->createTable('menus');

        // ========================================
        // 2. TABEL SUB_MENUS (Sub Menu)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'menu_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key ke tabel menus'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Judul sub menu'
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Icon class (optional untuk sub menu)'
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'URL static'
            ],
            'route' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Route name'
            ],
            'permission_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Permission key yang diperlukan'
            ],
            'target' => [
                'type'       => 'ENUM',
                'constraint' => ['_self', '_blank'],
                'default'    => '_self',
                'comment'    => 'Target link'
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan tampilan dalam menu parent'
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Active, 0 = Inactive'
            ],
            'is_divider' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Divider/separator, tidak punya link'
            ],
            'badge_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Teks badge (contoh: "New", "Beta")'
            ],
            'badge_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Warna badge (contoh: "danger", "success", "warning")'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi sub menu'
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
        $this->forge->addForeignKey('menu_id', 'menus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('menu_id');
        $this->forge->addKey('permission_key');
        $this->forge->addKey(['menu_id', 'display_order']);
        $this->forge->addKey(['menu_id', 'is_active']);
        $this->forge->createTable('sub_menus');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('sub_menus', true);
        $this->forge->dropTable('menus', true);
    }
}
