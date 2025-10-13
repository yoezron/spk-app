<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SurveyResponseModel
 * 
 * Model untuk mengelola jawaban/respons anggota pada survei
 * Digunakan untuk menyimpan dan menganalisis hasil survei SPK
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SurveyResponseModel extends Model
{
    protected $table            = 'survey_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'survey_id',
        'question_id',
        'user_id',
        'answer_text',
        'answer_choice',
        'answer_number',
        'submitted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'survey_id'   => 'required|integer|is_not_unique[surveys.id]',
        'question_id' => 'required|integer|is_not_unique[survey_questions.id]',
        'user_id'     => 'required|integer|is_not_unique[users.id]',
    ];

    protected $validationMessages = [
        'survey_id' => [
            'required'      => 'Survey ID harus ada',
            'is_not_unique' => 'Survey tidak ditemukan',
        ],
        'question_id' => [
            'required'      => 'Question ID harus ada',
            'is_not_unique' => 'Question tidak ditemukan',
        ],
        'user_id' => [
            'required'      => 'User ID harus ada',
            'is_not_unique' => 'User tidak ditemukan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setSubmittedAt'];
    protected $beforeUpdate   = [];

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set submitted timestamp
     * 
     * @param array $data
     * @return array
     */
    protected function setSubmittedAt(array $data)
    {
        if (!isset($data['data']['submitted_at'])) {
            $data['data']['submitted_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get response with user data
     * 
     * @return object
     */
    public function withUser()
    {
        return $this->select('survey_responses.*, users.username')
            ->select('member_profiles.full_name as user_name, member_profiles.university_id, member_profiles.region_id')
            ->join('users', 'users.id = survey_responses.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');
    }

    /**
     * Get response with question data
     * 
     * @return object
     */
    public function withQuestion()
    {
        return $this->select('survey_responses.*, survey_questions.question_text, survey_questions.question_type, survey_questions.options')
            ->join('survey_questions', 'survey_questions.id = survey_responses.question_id', 'left');
    }

    /**
     * Get response with survey data
     * 
     * @return object
     */
    public function withSurvey()
    {
        return $this->select('survey_responses.*, surveys.title as survey_title, surveys.status as survey_status')
            ->join('surveys', 'surveys.id = survey_responses.survey_id', 'left');
    }

    /**
     * Get response with complete data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('survey_responses.*')
            ->select('users.username, member_profiles.full_name as user_name')
            ->select('surveys.title as survey_title')
            ->select('survey_questions.question_text, survey_questions.question_type')
            ->join('users', 'users.id = survey_responses.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('surveys', 'surveys.id = survey_responses.survey_id', 'left')
            ->join('survey_questions', 'survey_questions.id = survey_responses.question_id', 'left');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get responses by survey
     * 
     * @param int $surveyId
     * @return array
     */
    public function getBySurvey($surveyId)
    {
        return $this->where('survey_id', $surveyId)
            ->orderBy('submitted_at', 'DESC')
            ->findAll();
    }

    /**
     * Get responses by question
     * 
     * @param int $questionId
     * @return array
     */
    public function getByQuestion($questionId)
    {
        return $this->where('question_id', $questionId)
            ->orderBy('submitted_at', 'DESC')
            ->findAll();
    }

    /**
     * Get responses by user
     * 
     * @param int $userId
     * @param int|null $surveyId
     * @return array
     */
    public function getByUser($userId, $surveyId = null)
    {
        $builder = $this->where('user_id', $userId);

        if ($surveyId) {
            $builder->where('survey_id', $surveyId);
        }

        return $builder->orderBy('submitted_at', 'DESC')->findAll();
    }

    /**
     * Get user's response for specific question
     * 
     * @param int $userId
     * @param int $questionId
     * @return object|null
     */
    public function getUserQuestionResponse($userId, $questionId)
    {
        return $this->where('user_id', $userId)
            ->where('question_id', $questionId)
            ->first();
    }

    /**
     * Check if user has completed survey
     * 
     * @param int $surveyId
     * @param int $userId
     * @return bool
     */
    public function hasUserCompleted($surveyId, $userId)
    {
        // Get total questions in survey
        $totalQuestions = $this->db->table('survey_questions')
            ->where('survey_id', $surveyId)
            ->countAllResults();

        if ($totalQuestions == 0) {
            return false;
        }

        // Get user's answered questions count
        $answeredCount = $this->where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->countAllResults();

        return $answeredCount >= $totalQuestions;
    }

    /**
     * Get completion percentage for user
     * 
     * @param int $surveyId
     * @param int $userId
     * @return float
     */
    public function getUserCompletionPercentage($surveyId, $userId)
    {
        $totalQuestions = $this->db->table('survey_questions')
            ->where('survey_id', $surveyId)
            ->countAllResults();

        if ($totalQuestions == 0) {
            return 0;
        }

        $answeredCount = $this->where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->countAllResults();

        return round(($answeredCount / $totalQuestions) * 100, 2);
    }

    /**
     * Delete user's responses for survey
     * 
     * @param int $surveyId
     * @param int $userId
     * @return bool
     */
    public function deleteUserResponses($surveyId, $userId)
    {
        return $this->where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->delete();
    }

    // ========================================
    // AGGREGATION & ANALYSIS
    // ========================================

    /**
     * Get aggregated results for a question
     * For choice-based questions (radio, checkbox, select)
     * 
     * @param int $questionId
     * @return array
     */
    public function getAggregatedResults($questionId)
    {
        $question = $this->db->table('survey_questions')
            ->where('id', $questionId)
            ->get()
            ->getRow();

        if (!$question) {
            return [];
        }

        // For choice-based questions
        if (in_array($question->question_type, ['radio', 'select'])) {
            return $this->select('answer_text, COUNT(*) as count')
                ->where('question_id', $questionId)
                ->groupBy('answer_text')
                ->orderBy('count', 'DESC')
                ->findAll();
        }

        // For checkbox (multiple choices)
        if ($question->question_type === 'checkbox') {
            // answer_choice is stored as JSON array
            $responses = $this->where('question_id', $questionId)
                ->findAll();

            $aggregated = [];
            foreach ($responses as $response) {
                if ($response->answer_choice) {
                    $choices = json_decode($response->answer_choice, true);
                    foreach ($choices as $choice) {
                        if (!isset($aggregated[$choice])) {
                            $aggregated[$choice] = 0;
                        }
                        $aggregated[$choice]++;
                    }
                }
            }

            // Convert to array of objects
            $result = [];
            foreach ($aggregated as $choice => $count) {
                $result[] = (object)[
                    'answer_text' => $choice,
                    'count' => $count
                ];
            }

            // Sort by count descending
            usort($result, function ($a, $b) {
                return $b->count - $a->count;
            });

            return $result;
        }

        // For scale questions
        if ($question->question_type === 'scale') {
            $avg = $this->selectAvg('answer_number', 'average')
                ->where('question_id', $questionId)
                ->first();

            $distribution = $this->select('answer_number, COUNT(*) as count')
                ->where('question_id', $questionId)
                ->groupBy('answer_number')
                ->orderBy('answer_number', 'ASC')
                ->findAll();

            return [
                'average' => round($avg->average ?? 0, 2),
                'distribution' => $distribution
            ];
        }

        // For text-based questions, return all responses
        return $this->where('question_id', $questionId)
            ->orderBy('submitted_at', 'DESC')
            ->findAll();
    }

    /**
     * Get survey summary statistics
     * 
     * @param int $surveyId
     * @return object
     */
    public function getSurveySummary($surveyId)
    {
        $totalQuestions = $this->db->table('survey_questions')
            ->where('survey_id', $surveyId)
            ->countAllResults();

        $uniqueRespondents = $this->select('COUNT(DISTINCT user_id) as count')
            ->where('survey_id', $surveyId)
            ->first();

        $totalResponses = $this->where('survey_id', $surveyId)
            ->countAllResults();

        $avgCompletion = $totalQuestions > 0
            ? round(($totalResponses / ($uniqueRespondents->count * $totalQuestions)) * 100, 2)
            : 0;

        return (object)[
            'total_questions' => $totalQuestions,
            'total_respondents' => $uniqueRespondents->count,
            'total_responses' => $totalResponses,
            'average_completion' => $avgCompletion,
        ];
    }

    /**
     * Get responses by region
     * 
     * @param int $surveyId
     * @return array
     */
    public function getResponsesByRegion($surveyId)
    {
        return $this->select('regions.name as region_name, COUNT(DISTINCT survey_responses.user_id) as respondents_count')
            ->join('member_profiles', 'member_profiles.user_id = survey_responses.user_id', 'left')
            ->join('regions', 'regions.id = member_profiles.region_id', 'left')
            ->where('survey_responses.survey_id', $surveyId)
            ->groupBy('member_profiles.region_id')
            ->orderBy('respondents_count', 'DESC')
            ->findAll();
    }

    /**
     * Get responses by university
     * 
     * @param int $surveyId
     * @return array
     */
    public function getResponsesByUniversity($surveyId)
    {
        return $this->select('universities.name as university_name, COUNT(DISTINCT survey_responses.user_id) as respondents_count')
            ->join('member_profiles', 'member_profiles.user_id = survey_responses.user_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->where('survey_responses.survey_id', $surveyId)
            ->groupBy('member_profiles.university_id')
            ->orderBy('respondents_count', 'DESC')
            ->findAll();
    }

    /**
     * Get response timeline
     * Responses per day
     * 
     * @param int $surveyId
     * @param int $days
     * @return array
     */
    public function getResponseTimeline($surveyId, $days = 30)
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));

        return $this->select('DATE(submitted_at) as date, COUNT(DISTINCT user_id) as respondents')
            ->where('survey_id', $surveyId)
            ->where('submitted_at >=', $since)
            ->groupBy('DATE(submitted_at)')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    // ========================================
    // EXPORT FUNCTIONS
    // ========================================

    /**
     * Get export data for CSV
     * 
     * @param int $surveyId
     * @return array
     */
    public function getExportData($surveyId)
    {
        return $this->select('survey_responses.*, users.username, member_profiles.full_name')
            ->select('survey_questions.question_text, survey_questions.question_type')
            ->select('regions.name as region_name, universities.name as university_name')
            ->join('users', 'users.id = survey_responses.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('survey_questions', 'survey_questions.id = survey_responses.question_id', 'left')
            ->join('regions', 'regions.id = member_profiles.region_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->where('survey_responses.survey_id', $surveyId)
            ->orderBy('survey_responses.user_id', 'ASC')
            ->orderBy('survey_questions.display_order', 'ASC')
            ->findAll();
    }

    /**
     * Get pivot table data (users x questions)
     * Each row is a respondent, each column is a question
     * 
     * @param int $surveyId
     * @return array
     */
    public function getPivotData($surveyId)
    {
        // Get all questions
        $questions = $this->db->table('survey_questions')
            ->where('survey_id', $surveyId)
            ->orderBy('display_order', 'ASC')
            ->get()
            ->getResult();

        // Get all respondents
        $respondents = $this->select('DISTINCT user_id')
            ->select('users.username, member_profiles.full_name')
            ->join('users', 'users.id = survey_responses.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->where('survey_responses.survey_id', $surveyId)
            ->groupBy('user_id')
            ->findAll();

        // Build pivot data
        $pivotData = [];
        foreach ($respondents as $respondent) {
            $row = [
                'user_id' => $respondent->user_id,
                'username' => $respondent->username,
                'full_name' => $respondent->full_name,
            ];

            foreach ($questions as $question) {
                $response = $this->where('user_id', $respondent->user_id)
                    ->where('question_id', $question->id)
                    ->first();

                $answer = '';
                if ($response) {
                    if ($response->answer_text) {
                        $answer = $response->answer_text;
                    } elseif ($response->answer_choice) {
                        $choices = json_decode($response->answer_choice, true);
                        $answer = is_array($choices) ? implode(', ', $choices) : $response->answer_choice;
                    } elseif ($response->answer_number !== null) {
                        $answer = $response->answer_number;
                    }
                }

                $row['q_' . $question->id] = $answer;
            }

            $pivotData[] = $row;
        }

        return [
            'questions' => $questions,
            'data' => $pivotData
        ];
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total responses count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get responses count by survey
     * 
     * @param int $surveyId
     * @return int
     */
    public function getCountBySurvey($surveyId)
    {
        return $this->where('survey_id', $surveyId)->countAllResults();
    }

    /**
     * Get unique respondents count
     * 
     * @param int $surveyId
     * @return int
     */
    public function getUniqueRespondentsCount($surveyId)
    {
        return $this->select('DISTINCT user_id')
            ->where('survey_id', $surveyId)
            ->countAllResults(false);
    }
}
