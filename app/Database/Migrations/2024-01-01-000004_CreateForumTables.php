<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateForumTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL FORUM_CATEGORIES (Kategori Forum)
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
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Material icon name'
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Hex color code'
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
        $this->forge->addKey('slug');
        $this->forge->createTable('forum_categories');

        // ========================================
        // 2. TABEL FORUM_THREADS (Thread Diskusi)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User yang membuat thread'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'content' => [
                'type' => 'TEXT',
            ],

            // Thread Settings
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Thread di-pin di atas'
            ],
            'is_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Thread dikunci (tidak bisa reply)'
            ],
            'is_solved' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Pertanyaan sudah terjawab'
            ],

            // Statistics
            'views_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'posts_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah reply/post'
            ],
            'likes_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Last Activity
            'last_post_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu post terakhir'
            ],
            'last_post_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],

            // Moderation
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['published', 'draft', 'pending', 'deleted'],
                'default'    => 'published',
            ],
            'moderated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Admin yang melakukan moderasi'
            ],
            'moderation_reason' => [
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
        $this->forge->addForeignKey('category_id', 'forum_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_post_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('moderated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('category_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('slug');
        $this->forge->addKey('status');
        $this->forge->addKey(['is_pinned', 'last_post_at']);
        $this->forge->createTable('forum_threads');

        // ========================================
        // 3. TABEL FORUM_POSTS (Post/Reply)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'thread_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reply to specific post (nested reply)'
            ],
            'content' => [
                'type' => 'TEXT',
            ],

            // Statistics
            'likes_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Moderation
            'is_solution' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Ditandai sebagai solusi/jawaban terbaik'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['published', 'pending', 'deleted'],
                'default'    => 'published',
            ],
            'moderated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'moderation_reason' => [
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
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('thread_id', 'forum_threads', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'forum_posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('moderated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('thread_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('parent_id');
        $this->forge->addKey('status');
        $this->forge->createTable('forum_posts');

        // ========================================
        // 4. TABEL FORUM_LIKES (Like/React)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'likeable_type' => [
                'type'       => 'ENUM',
                'constraint' => ['thread', 'post'],
                'comment'    => 'Thread atau Post'
            ],
            'likeable_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID thread atau post'
            ],
            'reaction_type' => [
                'type'       => 'ENUM',
                'constraint' => ['like', 'love', 'helpful', 'insightful'],
                'default'    => 'like',
                'comment'    => 'Jenis reaksi (bisa dikembangkan)'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['user_id', 'likeable_type', 'likeable_id']);
        $this->forge->addKey(['likeable_type', 'likeable_id']);
        $this->forge->createTable('forum_likes');

        // ========================================
        // 5. TABEL FORUM_REPORTS (Laporan Thread/Post)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User yang melaporkan'
            ],
            'reportable_type' => [
                'type'       => 'ENUM',
                'constraint' => ['thread', 'post'],
            ],
            'reportable_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'reason' => [
                'type'       => 'ENUM',
                'constraint' => ['spam', 'offensive', 'inappropriate', 'other'],
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'reviewed', 'resolved', 'dismissed'],
                'default'    => 'pending',
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'admin_notes' => [
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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reviewed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey(['reportable_type', 'reportable_id']);
        $this->forge->addKey('status');
        $this->forge->createTable('forum_reports');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('forum_reports', true);
        $this->forge->dropTable('forum_likes', true);
        $this->forge->dropTable('forum_posts', true);
        $this->forge->dropTable('forum_threads', true);
        $this->forge->dropTable('forum_categories', true);
    }
}
