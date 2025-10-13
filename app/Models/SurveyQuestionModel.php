<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SurveyQuestionModel
 * 
 * Model untuk mengelola pertanyaan dalam survei
 * Support berbagai tipe pertanyaan (text, radio, checkbox, scale, dll)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SurveyQuestionModel extends Model
{
    protected $table            = 'survey_questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'survey_id',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'display_order',
        'help_text',
        'validation_rules'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'survey_id'      => 'required|integer|is_not_unique[surveys.id]',
        'question_text'  => 'required|min_length[5]',
        'question_type'  => 'required|in_list[text,textarea,radio,checkbox,select,scale,date,email,number]',
        'is_required'    => 'permit_empty|in_list[0,1]',
        'display_order'  => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'survey_id' => [
            'required'      => 'Survey ID harus ada',
            'is_not_unique' => 'Survey tidak ditemukan',
        ],
        'question_text' => [
            'required'   => 'Teks pertanyaan harus diisi',
            'min_length' => 'Teks pertanyaan minimal 5 karakter',
        ],
        'question_type' => [
            'required' => 'Tipe pertanyaan harus dipilih',
            'in_list'  => 'Tipe pertanyaan tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDisplayOrder'];
    protected $beforeUpdate   = [];

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set display order automatically
     * 
     * @param array $data
     * @return array
     */
    protected function setDisplayOrder(array $data)
    {
        if (!isset($data['data']['display_order']) && isset($data['data']['survey_id'])) {
            $maxOrder = $this->where('survey_id', $data['data']['survey_id'])
                ->selectMax('display_order')
                ->first();

            $data['data']['display_order'] = $maxOrder ? (int)$maxOrder->display_order + 1 : 1;
        }
        return $data;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get question with survey data
     * 
     * @return object
     */
    public function withSurvey()
    {
        return $this->select('survey_questions.*, surveys.title as survey_title, surveys.status as survey_status')
            ->join('surveys', 'surveys.id = survey_questions.survey_id', 'left');
    }

    /**
     * Get question with responses count
     * 
     * @return object
     */
    public function withResponsesCount()
    {
        return $this->select('survey_questions.*')
            ->select('(SELECT COUNT(DISTINCT user_id) FROM survey_responses WHERE survey_responses.question_id = survey_questions.id) as responses_count');
    }

    /**
     * Get question with complete statistics
     * 
     * @return object
     */
    public function withStatistics()
    {
        return $this->select('survey_questions.*')
            ->select('(SELECT COUNT(DISTINCT user_id) FROM survey_responses WHERE survey_responses.question_id = survey_questions.id) as responses_count')
            ->select('(SELECT COUNT(*) FROM survey_responses WHERE survey_responses.question_id = survey_questions.id AND survey_responses.answer_text IS NOT NULL AND survey_responses.answer_text != "") as answered_count');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get questions by survey
     * 
     * @param int $surveyId
     * @param bool $orderedOnly
     * @return array
     */
    public function getBySurvey($surveyId, $orderedOnly = true)
    {
        $builder = $this->where('survey_id', $surveyId);

        if ($orderedOnly) {
            $builder->orderBy('display_order', 'ASC');
        }

        return $builder->findAll();
    }

    /**
     * Get required questions
     * 
     * @param int|null $surveyId
     * @return array
     */
    public function getRequired($surveyId = null)
    {
        $builder = $this->where('is_required', 1);

        if ($surveyId) {
            $builder->where('survey_id', $surveyId);
        }

        return $builder->orderBy('display_order', 'ASC')->findAll();
    }

    /**
     * Get questions by type
     * 
     * @param string $type
     * @param int|null $surveyId
     * @return array
     */
    public function getByType($type, $surveyId = null)
    {
        $builder = $this->where('question_type', $type);

        if ($surveyId) {
            $builder->where('survey_id', $surveyId);
        }

        return $builder->orderBy('display_order', 'ASC')->findAll();
    }

    /**
     * Get questions with choice options (radio, checkbox, select)
     * 
     * @param int $surveyId
     * @return array
     */
    public function getChoiceQuestions($surveyId)
    {
        return $this->where('survey_id', $surveyId)
            ->whereIn('question_type', ['radio', 'checkbox', 'select'])
            ->orderBy('display_order', 'ASC')
            ->findAll();
    }

    /**
     * Get text-based questions (text, textarea)
     * 
     * @param int $surveyId
     * @return array
     */
    public function getTextQuestions($surveyId)
    {
        return $this->where('survey_id', $surveyId)
            ->whereIn('question_type', ['text', 'textarea', 'email'])
            ->orderBy('display_order', 'ASC')
            ->findAll();
    }

    /**
     * Reorder questions
     * 
     * @param array $orders Array of ['id' => order]
     * @return bool
     */
    public function reorderQuestions(array $orders)
    {
        $this->db->transStart();

        foreach ($orders as $id => $order) {
            $this->update($id, ['display_order' => $order]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Move question up
     * 
     * @param int $id
     * @return bool
     */
    public function moveUp($id)
    {
        $question = $this->find($id);

        if (!$question || $question->display_order <= 1) {
            return false;
        }

        // Find previous question
        $previous = $this->where('survey_id', $question->survey_id)
            ->where('display_order <', $question->display_order)
            ->orderBy('display_order', 'DESC')
            ->first();

        if (!$previous) {
            return false;
        }

        // Swap orders
        $this->db->transStart();
        $this->update($id, ['display_order' => $previous->display_order]);
        $this->update($previous->id, ['display_order' => $question->display_order]);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Move question down
     * 
     * @param int $id
     * @return bool
     */
    public function moveDown($id)
    {
        $question = $this->find($id);

        if (!$question) {
            return false;
        }

        // Find next question
        $next = $this->where('survey_id', $question->survey_id)
            ->where('display_order >', $question->display_order)
            ->orderBy('display_order', 'ASC')
            ->first();

        if (!$next) {
            return false;
        }

        // Swap orders
        $this->db->transStart();
        $this->update($id, ['display_order' => $next->display_order]);
        $this->update($next->id, ['display_order' => $question->display_order]);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Duplicate question
     * 
     * @param int $id
     * @return int|bool New question ID or false
     */
    public function duplicate($id)
    {
        $question = $this->find($id);

        if (!$question) {
            return false;
        }

        // Get max order for the survey
        $maxOrder = $this->where('survey_id', $question->survey_id)
            ->selectMax('display_order')
            ->first();

        $newData = [
            'survey_id'        => $question->survey_id,
            'question_text'    => $question->question_text . ' (Copy)',
            'question_type'    => $question->question_type,
            'options'          => $question->options,
            'is_required'      => $question->is_required,
            'display_order'    => ($maxOrder ? (int)$maxOrder->display_order : 0) + 1,
            'help_text'        => $question->help_text,
            'validation_rules' => $question->validation_rules,
        ];

        return $this->insert($newData) ? $this->insertID() : false;
    }

    /**
     * Get options array from JSON
     * 
     * @param object $question
     * @return array
     */
    public function getOptions($question)
    {
        if (empty($question->options)) {
            return [];
        }

        return json_decode($question->options, true) ?: [];
    }

    /**
     * Set options from array to JSON
     * 
     * @param array $options
     * @return string
     */
    public function setOptions(array $options)
    {
        return json_encode($options);
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total questions count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get questions count by survey
     * 
     * @param int $surveyId
     * @return int
     */
    public function getCountBySurvey($surveyId)
    {
        return $this->where('survey_id', $surveyId)->countAllResults();
    }

    /**
     * Get questions count by type
     * 
     * @param string $type
     * @return int
     */
    public function getCountByType($type)
    {
        return $this->where('question_type', $type)->countAllResults();
    }

    /**
     * Get response rate per question
     * 
     * @param int $questionId
     * @return float Percentage
     */
    public function getResponseRate($questionId)
    {
        $question = $this->find($questionId);

        if (!$question) {
            return 0;
        }

        // Get total survey participants
        $totalParticipants = $this->db->table('survey_responses')
            ->where('survey_id', $question->survey_id)
            ->countAllResults(false);

        if ($totalParticipants == 0) {
            return 0;
        }

        // Get total answers for this question
        $totalAnswers = $this->db->table('survey_responses')
            ->where('question_id', $questionId)
            ->where('answer_text IS NOT NULL')
            ->where('answer_text !=', '')
            ->countAllResults();

        return round(($totalAnswers / $totalParticipants) * 100, 2);
    }

    /**
     * Get questions type distribution
     * 
     * @param int|null $surveyId
     * @return array
     */
    public function getTypeDistribution($surveyId = null)
    {
        $builder = $this->select('question_type, COUNT(*) as count')
            ->groupBy('question_type')
            ->orderBy('count', 'DESC');

        if ($surveyId) {
            $builder->where('survey_id', $surveyId);
        }

        return $builder->findAll();
    }

    /**
     * Get most answered questions
     * 
     * @param int $surveyId
     * @param int $limit
     * @return array
     */
    public function getMostAnswered($surveyId, $limit = 10)
    {
        return $this->select('survey_questions.*, COUNT(survey_responses.id) as answers_count')
            ->join('survey_responses', 'survey_responses.question_id = survey_questions.id', 'left')
            ->where('survey_questions.survey_id', $surveyId)
            ->groupBy('survey_questions.id')
            ->orderBy('answers_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get least answered questions (potential issues)
     * 
     * @param int $surveyId
     * @param int $limit
     * @return array
     */
    public function getLeastAnswered($surveyId, $limit = 10)
    {
        return $this->select('survey_questions.*, COUNT(survey_responses.id) as answers_count')
            ->join('survey_responses', 'survey_responses.question_id = survey_questions.id', 'left')
            ->where('survey_questions.survey_id', $surveyId)
            ->groupBy('survey_questions.id')
            ->orderBy('answers_count', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Check if question has responses
     * 
     * @param int $id
     * @return bool
     */
    public function hasResponses($id)
    {
        $count = $this->db->table('survey_responses')
            ->where('question_id', $id)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Get average completion time per question (if tracked)
     * This would require additional time tracking fields
     * 
     * @param int $questionId
     * @return float Seconds
     */
    public function getAverageCompletionTime($questionId)
    {
        // Placeholder for future implementation
        // Would need response_time field in survey_responses table
        return 0;
    }
}
