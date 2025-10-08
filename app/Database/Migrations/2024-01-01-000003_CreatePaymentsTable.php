<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        // ========================================
        // TABEL PAYMENTS (Pembayaran Iuran)
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
                'comment'    => 'User yang membayar'
            ],
            'payment_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
                'comment'    => 'Kode unik pembayaran'
            ],

            // Payment Details
            'payment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['membership_fee', 'monthly_fee', 'donation', 'other'],
                'default'    => 'monthly_fee',
                'comment'    => 'Jenis pembayaran'
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Jumlah pembayaran'
            ],
            'payment_period' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Format: YYYY-MM untuk iuran bulanan'
            ],

            // Payment Method
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['transfer', 'cash', 'e-wallet', 'other'],
                'default'    => 'transfer',
            ],
            'bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama bank pengirim'
            ],
            'account_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Nomor rekening pengirim'
            ],
            'account_holder' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nama pemilik rekening'
            ],

            // Payment Status
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'verified', 'rejected', 'cancelled'],
                'default'    => 'pending',
            ],
            'payment_date' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Tanggal pembayaran dilakukan'
            ],

            // Proof & Verification
            'proof_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path bukti transfer/pembayaran'
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal diverifikasi'
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID yang memverifikasi (Admin/Bendahara)'
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Alasan jika ditolak'
            ],

            // Additional Info
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan tambahan'
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan admin/bendahara'
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

        // Primary Key
        $this->forge->addKey('id', true);

        // Foreign Keys
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('verified_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Indexes
        $this->forge->addKey('user_id');
        $this->forge->addKey('payment_code');
        $this->forge->addKey('status');
        $this->forge->addKey('payment_period');
        $this->forge->addKey(['user_id', 'payment_period']);
        $this->forge->addKey('payment_date');

        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments', true);
    }
}
