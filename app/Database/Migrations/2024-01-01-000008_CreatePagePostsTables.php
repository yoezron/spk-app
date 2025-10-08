<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagePostsTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL PAGES (Halaman Statis)
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
                'constraint' => 255,
                'comment'    => 'Judul halaman'
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
                'comment'    => 'URL-friendly slug'
            ],
            'content' => [
                'type' => 'LONGTEXT',
                'comment' => 'Konten halaman (HTML)'
            ],
            'excerpt' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Ringkasan singkat'
            ],

            // Meta & SEO
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Title untuk SEO'
            ],
            'meta_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Meta description untuk SEO'
            ],
            'meta_keywords' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Keywords untuk SEO'
            ],

            // Page Settings
            'template' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'default',
                'comment'    => 'Template view yang digunakan'
            ],
            'page_type' => [
                'type'       => 'ENUM',
                'constraint' => ['standard', 'manifesto', 'ad_art', 'contact', 'about', 'custom'],
                'default'    => 'standard',
                'comment'    => 'Tipe halaman untuk handling khusus'
            ],
            'is_published' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Published, 0 = Draft'
            ],
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Tampil di featured area'
            ],
            'show_in_menu' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Tampilkan di menu navigasi'
            ],
            'menu_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan di menu'
            ],
            'allow_comments' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Izinkan komentar'
            ],

            // Access Control
            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['public', 'members_only', 'admin_only'],
                'default'    => 'public',
                'comment'    => 'Siapa yang bisa mengakses'
            ],

            // Author & Stats
            'author_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User yang membuat'
            ],
            'views_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah views'
            ],

            // Timestamps
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal publikasi'
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
                'comment' => 'Soft delete'
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('author_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->addKey('is_published');
        $this->forge->addKey('page_type');
        $this->forge->addKey(['is_published', 'published_at']);
        $this->forge->addKey(['show_in_menu', 'menu_order']);
        $this->forge->addKey('author_id');
        $this->forge->createTable('pages');

        // ========================================
        // 2. TABEL TAGS (Tag Konten)
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
                'unique'     => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Hex color code untuk badge'
            ],
            'posts_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah post dengan tag ini'
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
        $this->forge->createTable('tags');

        // ========================================
        // 3. TABEL POSTS (Blog/Artikel/Berita)
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
                'constraint' => 255,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'excerpt' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Ringkasan artikel'
            ],
            'content' => [
                'type' => 'LONGTEXT',
                'comment' => 'Konten lengkap (HTML)'
            ],

            // Media
            'featured_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path gambar utama'
            ],
            'featured_image_alt' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Alt text untuk gambar'
            ],
            'featured_image_caption' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Caption gambar'
            ],

            // Meta & SEO
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'meta_keywords' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Post Type & Category
            'post_type' => [
                'type'       => 'ENUM',
                'constraint' => ['article', 'news', 'press_release', 'announcement', 'event'],
                'default'    => 'article',
                'comment'    => 'Tipe konten'
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Kategori post'
            ],

            // Status & Publishing
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'pending', 'published', 'archived'],
                'default'    => 'draft',
            ],
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Post unggulan'
            ],
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Pin di bagian atas'
            ],
            'allow_comments' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],

            // Access Control
            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['public', 'members_only', 'admin_only'],
                'default'    => 'public',
            ],

            // Author & Editor
            'author_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Penulis'
            ],
            'editor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Editor yang mereview'
            ],

            // Statistics
            'views_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'comments_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'likes_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'shares_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Event-specific (if post_type = event)
            'event_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal event (jika post_type = event)'
            ],
            'event_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Lokasi event'
            ],

            // Timestamps
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Jadwal publikasi otomatis'
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
                'comment' => 'Soft delete'
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('author_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('editor_id', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->addKey('status');
        $this->forge->addKey('post_type');
        $this->forge->addKey('author_id');
        $this->forge->addKey(['status', 'published_at']);
        $this->forge->addKey(['is_featured', 'published_at']);
        $this->forge->addKey(['is_pinned', 'published_at']);
        $this->forge->addKey('published_at');
        $this->forge->createTable('posts');

        // ========================================
        // 4. TABEL POST_TAGS (Pivot Many-to-Many)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'post_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tag_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tag_id', 'tags', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['post_id', 'tag_id']);
        $this->forge->addKey('post_id');
        $this->forge->addKey('tag_id');
        $this->forge->createTable('post_tags');

        // ========================================
        // 5. TABEL POST_COMMENTS (Komentar Post)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'post_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL jika anonymous (guest comment)'
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reply to another comment'
            ],

            // Comment Content
            'author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama (jika guest comment)'
            ],
            'author_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Email (jika guest comment)'
            ],
            'content' => [
                'type' => 'TEXT',
            ],

            // Status & Moderation
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'spam'],
                'default'    => 'pending',
            ],
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Pin comment (highlighted)'
            ],
            'moderated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'moderation_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Engagement
            'likes_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Meta
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addForeignKey('post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'post_comments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('moderated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('post_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('parent_id');
        $this->forge->addKey('status');
        $this->forge->addKey(['post_id', 'status']);
        $this->forge->createTable('post_comments');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('post_comments', true);
        $this->forge->dropTable('post_tags', true);
        $this->forge->dropTable('posts', true);
        $this->forge->dropTable('tags', true);
        $this->forge->dropTable('pages', true);
    }
}
