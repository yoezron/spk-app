<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSurveyTables extends Migration
{
    public function up()
    {
        // ========================================
        // 1. TABEL SURVEYS (Survei Utama)
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
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User yang membuat survei (Admin/Pengurus)'
            ],

            // Survey Settings
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'closed', 'archived'],
                'default'    => 'draft',
            ],
            'is_anonymous' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Responden anonim atau tidak'
            ],
            'allow_multiple_responses' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Boleh mengisi berkali-kali'
            ],
            'show_results' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Tampilkan hasil ke responden'
            ],

            // Target Audience
            'target_audience' => [
                'type'       => 'ENUM',
                'constraint' => ['all', 'region', 'university', 'custom'],
                'default'    => 'all',
                'comment'    => 'Target responden'
            ],
            'target_region_ids' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of region IDs'
            ],
            'target_university_ids' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of university IDs'
            ],

            // Schedule
            'start_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Mulai bisa diisi'
            ],
            'end_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Batas akhir pengisian'
            ],

            // Statistics
            'responses_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah responden'
            ],
            'questions_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah pertanyaan'
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
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'closed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('slug');
        $this->forge->addKey('status');
        $this->forge->addKey(['status', 'start_date', 'end_date']);
        $this->forge->createTable('surveys');

        // ========================================
        // 2. TABEL SURVEY_QUESTIONS (Pertanyaan Survei)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'survey_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'question_text' => [
                'type' => 'TEXT',
            ],
            'question_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'text',
                    'textarea',
                    'single_choice',
                    'multiple_choice',
                    'rating',
                    'scale',
                    'date',
                    'number'
                ],
                'default'    => 'text',
            ],
            'is_required' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Settings for specific question types
            'min_value' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Untuk rating/scale/number'
            ],
            'max_value' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Untuk rating/scale/number'
            ],
            'step_value' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Untuk scale (step increment)'
            ],
            'min_selection' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Untuk multiple_choice: minimum pilihan'
            ],
            'max_selection' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Untuk multiple_choice: maksimum pilihan'
            ],
            'placeholder' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Placeholder text untuk input'
            ],

            // Conditional Logic (Optional - untuk advanced feature)
            'parent_question_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Pertanyaan parent untuk conditional display'
            ],
            'conditional_value' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Nilai yang harus dipenuhi dari parent question'
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
        $this->forge->addForeignKey('survey_id', 'surveys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_question_id', 'survey_questions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('survey_id');
        $this->forge->addKey(['survey_id', 'display_order']);
        $this->forge->createTable('survey_questions');

        // ========================================
        // 3. TABEL SURVEY_QUESTION_OPTIONS (Opsi Jawaban)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'question_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'option_text' => [
                'type' => 'TEXT',
            ],
            'option_value' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Value jika berbeda dengan text'
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_other_option' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Opsi "Lainnya" dengan text input'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('question_id', 'survey_questions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('question_id');
        $this->forge->addKey(['question_id', 'display_order']);
        $this->forge->createTable('survey_question_options');

        // ========================================
        // 4. TABEL SURVEY_RESPONSES (Jawaban Responden)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'survey_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL jika anonymous'
            ],
            'response_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'comment'    => 'Token unik untuk tracking anonymous response'
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['in_progress', 'completed'],
                'default'    => 'in_progress',
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addForeignKey('survey_id', 'surveys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('survey_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('response_token');
        $this->forge->addKey(['survey_id', 'user_id']);
        $this->forge->createTable('survey_responses');

        // ========================================
        // 5. TABEL SURVEY_RESPONSE_DETAILS (Detail Jawaban)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'response_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'question_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'option_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL untuk text/textarea/number'
            ],
            'answer_text' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Untuk text/textarea atau "other" option'
            ],
            'answer_number' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'comment'    => 'Untuk number/rating/scale'
            ],
            'answer_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Untuk date type'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('response_id', 'survey_responses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('question_id', 'survey_questions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('option_id', 'survey_question_options', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('response_id');
        $this->forge->addKey('question_id');
        $this->forge->addKey(['response_id', 'question_id']);
        $this->forge->createTable('survey_response_details');
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('survey_response_details', true);
        $this->forge->dropTable('survey_responses', true);
        $this->forge->dropTable('survey_question_options', true);
        $this->forge->dropTable('survey_questions', true);
        $this->forge->dropTable('surveys', true);
    }
}
