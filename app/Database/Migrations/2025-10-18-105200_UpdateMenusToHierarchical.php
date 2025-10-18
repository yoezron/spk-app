<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * UpdateMenusToHierarchical
 * 
 * Mengubah struktur menu dari 2 tabel (menus + sub_menus)
 * menjadi 1 tabel hierarki (menus dengan parent_id)
 * 
 * PERUBAHAN:
 * 1. Tambah kolom parent_id di tabel menus
 * 2. Rename beberapa kolom agar konsisten dengan MenuModel
 * 3. Drop tabel sub_menus
 * 
 * @package App\Database\Migrations
 * @author  SPK Development Team
 * @version 1.0.0
 */
class UpdateMenusToHierarchical extends Migration
{
    public function up()
    {
        // ========================================
        // 1. MODIFY TABEL MENUS - Add parent_id & adjust columns
        // ========================================

        // Add parent_id column
        $fields = [
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
                'comment'    => 'Parent menu ID untuk hierarki (NULL = top level)'
            ]
        ];
        $this->forge->addColumn('menus', $fields);

        // Add foreign key for parent_id (self-referencing)
        $this->forge->addForeignKey('parent_id', 'menus', 'id', 'CASCADE', 'CASCADE', 'menus_parent_id_foreign');

        // Rename 'route' to 'route_name' untuk konsistensi dengan MenuModel
        $this->forge->modifyColumn('menus', [
            'route' => [
                'name'       => 'route_name',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Route name (jika menggunakan named route)'
            ]
        ]);

        // Rename 'display_order' to 'sort_order' untuk konsistensi
        $this->forge->modifyColumn('menus', [
            'display_order' => [
                'name'       => 'sort_order',
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan tampilan menu'
            ]
        ]);

        // Add 'is_external' column
        $fields = [
            'is_external' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_active',
                'comment'    => '1 = External link, 0 = Internal'
            ]
        ];
        $this->forge->addColumn('menus', $fields);

        // Add 'css_class' column for custom styling
        $fields = [
            'css_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'description',
                'comment'    => 'Custom CSS class untuk menu item'
            ]
        ];
        $this->forge->addColumn('menus', $fields);

        // Remove 'is_header' column (not needed in hierarchical structure)
        $this->forge->dropColumn('menus', 'is_header');

        // Remove 'menu_type' column (tidak diperlukan lagi)
        $this->forge->dropColumn('menus', 'menu_type');

        // Add indexes for better performance
        $this->db->query('CREATE INDEX idx_menus_parent_id ON menus(parent_id)');
        $this->db->query('CREATE INDEX idx_menus_parent_sort ON menus(parent_id, sort_order)');
        $this->db->query('CREATE INDEX idx_menus_active_sort ON menus(is_active, sort_order)');

        // ========================================
        // 2. DROP TABEL SUB_MENUS
        // ========================================
        $this->forge->dropTable('sub_menus', true);
    }

    public function down()
    {
        // ========================================
        // ROLLBACK: Kembalikan struktur lama
        // ========================================

        // Recreate sub_menus table
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
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'route' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'permission_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'target' => [
                'type'       => 'ENUM',
                'constraint' => ['_self', '_blank'],
                'default'    => '_self',
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_divider' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'badge_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'badge_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
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
        $this->forge->addForeignKey('menu_id', 'menus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sub_menus');

        // Remove new columns from menus
        $this->forge->dropColumn('menus', 'parent_id');
        $this->forge->dropColumn('menus', 'is_external');
        $this->forge->dropColumn('menus', 'css_class');

        // Add back removed columns
        $fields = [
            'is_header' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'menu_type' => [
                'type'       => 'ENUM',
                'constraint' => ['sidebar', 'header', 'footer'],
                'default'    => 'sidebar',
            ]
        ];
        $this->forge->addColumn('menus', $fields);

        // Rename columns back
        $this->forge->modifyColumn('menus', [
            'route_name' => [
                'name'       => 'route',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ]
        ]);

        $this->forge->modifyColumn('menus', [
            'sort_order' => [
                'name'       => 'display_order',
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ]
        ]);

        // Drop indexes
        $this->db->query('DROP INDEX IF EXISTS idx_menus_parent_id ON menus');
        $this->db->query('DROP INDEX IF EXISTS idx_menus_parent_sort ON menus');
        $this->db->query('DROP INDEX IF EXISTS idx_menus_active_sort ON menus');
    }
}
