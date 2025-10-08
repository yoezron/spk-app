<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL AUDIT_LOGS (Comprehensive Audit Trail)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User yang melakukan aksi (NULL untuk system)'
            ],

            // Action Details
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Tipe aksi: create, update, delete, view, login, logout, export, import, etc'
            ],
            'action_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi detail aksi'
            ],

            // Entity Information
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Tipe entity: User, MemberProfile, Post, Survey, etc'
            ],
            'entity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID entity yang diubah'
            ],
            'entity_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nama/identifier entity untuk display'
            ],

            // Changes Tracking
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Nilai lama (JSON)'
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Nilai baru (JSON)'
            ],
            'changed_fields' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Field yang berubah (comma separated)'
            ],

            // Request Information
            'url' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'URL yang diakses'
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'HTTP method: GET, POST, PUT, DELETE'
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'IP address user'
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Browser/device info'
            ],

            // Additional Context
            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Module: auth, member, forum, survey, etc'
            ],
            'severity' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default'    => 'low',
                'comment'    => 'Tingkat kepentingan log'
            ],
            'tags' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Tags untuk filtering (comma separated)'
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional metadata (JSON)'
            ],

            // Timestamp
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('module');
        $this->forge->addKey('severity');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addKey(['action', 'created_at']);
        $this->forge->createTable('audit_logs');

        // ========================================
        // 2. TABEL LOGIN_LOGS (Login Activity Tracking)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL jika login gagal'
            ],

            // Login Attempt Info
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Email yang digunakan untuk login'
            ],
            'login_type' => [
                'type'       => 'ENUM',
                'constraint' => ['email', 'username', 'social', 'sso'],
                'default'    => 'email',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['success', 'failed', 'blocked', 'locked'],
                'default'    => 'failed',
            ],
            'failure_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Alasan gagal login'
            ],

            // Session Info
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'remember_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // Request Details
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'device_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'desktop, mobile, tablet'
            ],
            'browser' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Windows, Mac, Linux, Android, iOS'
            ],

            // Location (optional, dari IP)
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            // Logout Info
            'logout_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'logout_type' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'timeout', 'forced'],
                'null'       => true,
            ],

            // Security
            'is_suspicious' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Flag aktivitas mencurigakan'
            ],
            'risk_score' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Skor risiko 0-100'
            ],

            // Timestamp
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('user_id');
        $this->forge->addKey('email');
        $this->forge->addKey('status');
        $this->forge->addKey('ip_address');
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addKey('is_suspicious');
        $this->forge->addKey('created_at');
        $this->forge->createTable('login_logs');

        // ========================================
        // 3. TABEL EMAIL_LOGS (Email Delivery Tracking)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // Recipient Info
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User tujuan (jika registered user)'
            ],
            'to_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'to_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cc' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'CC emails (comma separated)'
            ],
            'bcc' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'BCC emails (comma separated)'
            ],

            // Email Content
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'email_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'verification, reset_password, notification, newsletter, etc'
            ],
            'template' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Template yang digunakan'
            ],
            'body_preview' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Preview isi email (500 char pertama)'
            ],

            // Attachments
            'has_attachments' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'attachments' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'List attachments (JSON)'
            ],

            // Delivery Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'sent', 'delivered', 'failed', 'bounced', 'spam'],
                'default'    => 'pending',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'opened_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Pertama kali dibuka'
            ],
            'open_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'clicked_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Pertama kali link diklik'
            ],
            'click_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Error Info
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'bounce_type' => [
                'type'       => 'ENUM',
                'constraint' => ['hard', 'soft', 'undetermined'],
                'null'       => true,
            ],

            // Provider Info
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'SMTP, SendGrid, Mailgun, etc'
            ],
            'provider_message_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Message ID dari provider'
            ],

            // Sender Info
            'sent_by_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User yang trigger email'
            ],

            // Priority
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'normal', 'high'],
                'default'    => 'normal',
            ],

            // Retry
            'retry_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'next_retry_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Timestamp
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('sent_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('user_id');
        $this->forge->addKey('to_email');
        $this->forge->addKey('email_type');
        $this->forge->addKey('status');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addKey('sent_at');
        $this->forge->addKey('created_at');
        $this->forge->createTable('email_logs');

        // ========================================
        // 4. TABEL SYSTEM_LOGS (System Activity Logs)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // Log Level
            'level' => [
                'type'       => 'ENUM',
                'constraint' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                'default'    => 'info',
                'comment'    => 'PSR-3 Log Levels'
            ],

            // Message
            'message' => [
                'type' => 'TEXT',
                'comment' => 'Log message'
            ],

            // Context
            'context' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional context data (JSON)'
            ],

            // Source
            'channel' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Log channel: app, database, security, performance, etc'
            ],
            'source_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'File yang generate log'
            ],
            'source_line' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Line number'
            ],

            // Request Context
            'url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],

            // Exception Info (if error)
            'exception_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'exception_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'exception_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'stack_trace' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            // Performance
            'execution_time' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'null'       => true,
                'comment'    => 'Execution time in seconds'
            ],
            'memory_usage' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Memory usage in bytes'
            ],

            // Timestamp
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('level');
        $this->forge->addKey('channel');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['level', 'created_at']);
        $this->forge->addKey(['channel', 'created_at']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('system_logs');

        // ========================================
        // 5. TABEL FILE_UPLOADS (File Management Tracking)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // Uploader Info
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User yang upload'
            ],

            // File Info
            'original_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'stored_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Nama file yang disimpan di server'
            ],
            'file_path' => [
                'type' => 'TEXT',
                'comment' => 'Path lengkap file'
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'comment'    => 'Ukuran file dalam bytes'
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'file_extension' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],

            // Storage
            'disk' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'local',
                'comment'    => 'Storage disk: local, s3, minio, etc'
            ],
            'directory' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Subdirectory dalam disk'
            ],

            // Purpose & Context
            'purpose' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'profile_photo, payment_proof, attachment, document, etc'
            ],
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Tipe entity terkait'
            ],
            'entity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID entity terkait'
            ],

            // Security
            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Public accessible, 0 = Protected'
            ],
            'access_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Token untuk akses private file'
            ],
            'token_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Virus Scan
            'is_scanned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'scan_result' => [
                'type'       => 'ENUM',
                'constraint' => ['clean', 'infected', 'suspicious', 'pending'],
                'null'       => true,
            ],
            'scan_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Hash for integrity
            'file_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'comment'    => 'SHA256 hash untuk verifikasi integritas'
            ],

            // Usage Tracking
            'download_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_accessed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Metadata
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional metadata (dimensions for images, duration for videos, etc)'
            ],

            // Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'archived', 'deleted', 'quarantined'],
                'default'    => 'active',
            ],

            // IP tracking
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('user_id');
        $this->forge->addKey('purpose');
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('file_hash');
        $this->forge->addKey('status');
        $this->forge->addKey('mime_type');
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('file_uploads');

        // ========================================
        // 6. TABEL NOTIFICATION_LOGS (Push/In-App Notifications)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User penerima'
            ],

            // Notification Content
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'notification_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'info, warning, success, error, announcement, etc'
            ],

            // Action/Link
            'action_url' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'URL tujuan saat notifikasi diklik'
            ],
            'action_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            // Status
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_clicked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'clicked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Source
            'source_entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'source_entity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],

            // Sender
            'sent_by_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL jika system notification'
            ],

            // Priority
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'normal', 'high', 'urgent'],
                'default'    => 'normal',
            ],

            // Metadata
            'metadata' => [
                'type' => 'JSON',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sent_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['user_id', 'is_read']);
        $this->forge->addKey('notification_type');
        $this->forge->addKey('priority');
        $this->forge->addKey('created_at');
        $this->forge->createTable('notification_logs');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('notification_logs', true);
        $this->forge->dropTable('file_uploads', true);
        $this->forge->dropTable('system_logs', true);
        $this->forge->dropTable('email_logs', true);
        $this->forge->dropTable('login_logs', true);
        $this->forge->dropTable('audit_logs', true);
    }
}
