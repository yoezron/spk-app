<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWAGroupsTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL WA_GROUPS (WhatsApp Groups)
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
                'constraint' => 150,
                'comment'    => 'Nama grup WhatsApp'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi grup'
            ],

            // Group Scope
            'scope' => [
                'type'       => 'ENUM',
                'constraint' => ['national', 'regional', 'university', 'custom'],
                'default'    => 'regional',
                'comment'    => 'Cakupan grup'
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID wilayah (jika scope = regional)'
            ],
            'university_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kampus (jika scope = university)'
            ],

            // WhatsApp Link
            'invite_link' => [
                'type' => 'TEXT',
                'comment' => 'Link invite WhatsApp group'
            ],
            'link_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal kadaluarsa link (jika ada)'
            ],

            // Group Admin/Coordinator
            'admin_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Admin/koordinator grup'
            ],
            'co_admin_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Co-admin grup'
            ],

            // Group Settings
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Status aktif grup'
            ],
            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Publik (semua bisa lihat), 0 = Private'
            ],
            'auto_add_members' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Otomatis tambahkan anggota baru ke grup'
            ],
            'requires_approval' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Memerlukan approval admin untuk join'
            ],

            // Statistics
            'members_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah anggota di grup'
            ],
            'joined_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah yang sudah join WA'
            ],
            'pending_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah yang belum join'
            ],

            // Group Info
            'group_rules' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Aturan grup'
            ],
            'welcome_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Pesan sambutan untuk member baru'
            ],

            // WhatsApp Group Metadata (optional)
            'wa_group_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'WhatsApp Group ID (jika terintegrasi dengan API)'
            ],
            'wa_group_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Nama asli grup di WhatsApp'
            ],

            // Display Order
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan tampilan'
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
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('university_id', 'universities', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('admin_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('co_admin_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('scope');
        $this->forge->addKey('region_id');
        $this->forge->addKey('university_id');
        $this->forge->addKey(['is_active', 'is_public']);
        $this->forge->addKey('admin_user_id');
        $this->forge->addKey('display_order');
        $this->forge->createTable('wa_groups');

        // ========================================
        // 2. TABEL WA_GROUP_MEMBERS (Members Tracking)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID grup WhatsApp'
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID user/anggota'
            ],

            // Member Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['invited', 'joined', 'left', 'removed', 'pending_approval'],
                'default'    => 'invited',
                'comment'    => 'Status keanggotaan di grup'
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['member', 'admin'],
                'default'    => 'member',
                'comment'    => 'Role di grup WhatsApp'
            ],

            // Join Information
            'invited_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal diundang'
            ],
            'invited_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User yang mengundang'
            ],
            'joined_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal bergabung'
            ],
            'left_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal keluar/left'
            ],
            'removed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal dikeluarkan'
            ],
            'removed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Admin yang mengeluarkan'
            ],

            // Confirmation/Verification
            'is_verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Sudah dikonfirmasi join atau belum'
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Admin yang mengkonfirmasi'
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal konfirmasi'
            ],

            // Member Info
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Nomor WhatsApp member (untuk tracking)'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan admin tentang member'
            ],

            // Engagement Tracking
            'last_active_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Terakhir aktif di grup (jika ada integrasi API)'
            ],
            'messages_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah pesan (jika ada integrasi API)'
            ],

            // Notification Preferences
            'notification_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Notifikasi untuk member ini'
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
        $this->forge->addForeignKey('group_id', 'wa_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invited_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('removed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('verified_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey(['group_id', 'user_id']);
        $this->forge->addKey('group_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey(['group_id', 'status']);
        $this->forge->createTable('wa_group_members');

        // ========================================
        // 3. TABEL WA_GROUP_INVITATIONS (Undangan Pending)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'invitation_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'comment'    => 'Token unik untuk tracking klik link'
            ],

            // Invitation Info
            'invited_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'invitation_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Pesan khusus dari yang mengundang'
            ],

            // Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'clicked', 'joined', 'expired', 'declined'],
                'default'    => 'pending',
            ],
            'clicked_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Kapan link diklik'
            ],
            'joined_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Kapan berhasil join'
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Batas waktu undangan'
            ],

            // Reminder tracking
            'reminder_sent_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah reminder yang dikirim'
            ],
            'last_reminder_sent_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addForeignKey('group_id', 'wa_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invited_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('invitation_token');
        $this->forge->addKey('status');
        $this->forge->addKey(['group_id', 'user_id']);
        $this->forge->addKey(['status', 'expires_at']);
        $this->forge->createTable('wa_group_invitations');

        // ========================================
        // 4. TABEL WA_GROUP_ANNOUNCEMENTS (Pengumuman Grup)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
                'comment' => 'Pesan pengumuman'
            ],

            // Announcement Type
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['info', 'warning', 'urgent', 'event', 'reminder'],
                'default'    => 'info',
            ],

            // Sender
            'sent_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Admin yang mengirim'
            ],

            // Delivery Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'scheduled', 'sent', 'failed'],
                'default'    => 'draft',
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Jadwal pengiriman'
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Recipients
            'recipients_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'delivered_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'read_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addForeignKey('group_id', 'wa_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sent_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('group_id');
        $this->forge->addKey('status');
        $this->forge->addKey('scheduled_at');
        $this->forge->createTable('wa_group_announcements');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('wa_group_announcements', true);
        $this->forge->dropTable('wa_group_invitations', true);
        $this->forge->dropTable('wa_group_members', true);
        $this->forge->dropTable('wa_groups', true);
    }
}
