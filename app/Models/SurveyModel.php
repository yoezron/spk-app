<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SurveyModel
 * 
 * Model untuk mengelola survei/polling untuk advokasi SPK
 * Digunakan untuk mengumpulkan data dari anggota (contoh: survei gaji dosen)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SurveyModel extends Model
{
    protected $table            = 'surveys';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false; // Disabled: surveys table doesn't have deleted_at column
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'slug',
        'description',
        'instructions',
        'status',
        'start_date',
        'end_date',
        'target_audience',
        'target_region_ids',
        'target_university_ids',
        'is_anonymous',
        'allow_multiple_responses',
        'show_results_to_participants',
        'created_by',
        'published_at',
        'closed_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Not used: soft deletes disabled

    // Validation
    protected $validationRules = [
        'title'                        => 'required|min_length[5]|max_length[255]',
        'slug'                         => 'permit_empty|max_length[255]|is_unique[surveys.slug,id,{id}]',
        'description'                  => 'permit_empty',
        'status'                       => 'required|in_list[draft,active,closed]',
        'start_date'                   => 'permit_empty|valid_date',
        'end_date'                     => 'permit_empty|valid_date',
        'target_audience'              => 'required|in_list[all,region,university,custom]',
        'is_anonymous'                 => 'permit_empty|in_list[0,1]',
        'allow_multiple_responses'     => 'permit_empty|in_list[0,1]',
        'show_results_to_participants' => 'permit_empty|in_list[0,1]',
        'created_by'                   => 'required|integer|is_not_unique[users.id]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Judul survei harus diisi',
            'min_length' => 'Judul minimal 5 karakter',
            'max_length' => 'Judul maksimal 255 karakter',
        ],
        'slug' => [
            'is_unique' => 'Slug survei sudah digunakan',
        ],
        'status' => [
            'required' => 'Status survei harus dipilih',
            'in_list'  => 'Status tidak valid',
        ],
        'target_audience' => [
            'required' => 'Target audiens harus dipilih',
            'in_list'  => 'Target audiens tidak valid',
        ],
        'created_by' => [
            'required'      => 'Creator harus ada',
            'is_not_unique' => 'User tidak ditemukan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate slug from title
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data)
    {
        if (isset($data['data']['title']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = url_title($data['data']['title'], '-', true) . '-' . time();
        }
        return $data;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get survey with creator data
     * 
     * @return object
     */
    public function withCreator()
    {
        return $this->select('surveys.*, users.username as creator_username')
            ->select('member_profiles.full_name as creator_name')
            ->join('users', 'users.id = surveys.created_by', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');
    }

    /**
     * Get survey with questions count
     * 
     * @return object
     */
    public function withQuestionsCount()
    {
        return $this->select('surveys.*')
            ->select('(SELECT COUNT(*) FROM survey_questions WHERE survey_questions.survey_id = surveys.id) as questions_count');
    }

    /**
     * Get survey with responses count
     * 
     * @return object
     */
    public function withResponsesCount()
    {
        return $this->select('surveys.*')
            ->select('(SELECT COUNT(DISTINCT user_id) FROM survey_responses WHERE survey_responses.survey_id = surveys.id) as responses_count');
    }

    /**
     * Get survey with complete statistics
     * 
     * @return object
     */
    public function withStatistics()
    {
        return $this->select('surveys.*')
            ->select('(SELECT COUNT(*) FROM survey_questions WHERE survey_questions.survey_id = surveys.id) as questions_count')
            ->select('(SELECT COUNT(DISTINCT user_id) FROM survey_responses WHERE survey_responses.survey_id = surveys.id) as responses_count')
            ->select('(SELECT COUNT(*) FROM survey_responses WHERE survey_responses.survey_id = surveys.id) as total_answers');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active surveys (currently running)
     * 
     * @return array
     */
    public function getActive()
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('status', 'active')
            ->where('start_date <=', $now)
            ->groupStart()
            ->where('end_date >=', $now)
            ->orWhere('end_date', null)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get draft surveys
     * 
     * @return array
     */
    public function getDraft()
    {
        return $this->where('status', 'draft')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get closed surveys
     * 
     * @return array
     */
    public function getClosed()
    {
        return $this->where('status', 'closed')
            ->orderBy('closed_at', 'DESC')
            ->findAll();
    }

    /**
     * Get surveys by creator
     * 
     * @param int $userId
     * @return array
     */
    public function getByCreator($userId)
    {
        return $this->where('created_by', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get available surveys for user
     * Check target audience and if user already responded
     * 
     * @param int $userId
     * @return array
     */
    public function getAvailableForUser($userId)
    {
        $now = date('Y-m-d H:i:s');

        // Get user profile to check region/university
        $userProfile = $this->db->table('member_profiles')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        $builder = $this->where('status', 'active')
            ->where('start_date <=', $now)
            ->groupStart()
            ->where('end_date >=', $now)
            ->orWhere('end_date', null)
            ->groupEnd();

        // Apply target audience filter
        $builder->groupStart()
            ->where('target_audience', 'all');

        if ($userProfile && $userProfile->region_id) {
            $builder->orWhere('target_audience', 'region')
                ->where("JSON_CONTAINS(target_region_ids, '\"$userProfile->region_id\"')");
        }

        if ($userProfile && $userProfile->university_id) {
            $builder->orWhere('target_audience', 'university')
                ->where("JSON_CONTAINS(target_university_ids, '\"$userProfile->university_id\"')");
        }

        $builder->groupEnd();

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Check if user has responded to survey
     * 
     * @param int $surveyId
     * @param int $userId
     * @return bool
     */
    public function hasUserResponded($surveyId, $userId)
    {
        $count = $this->db->table('survey_responses')
            ->where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Get survey by slug
     * 
     * @param string $slug
     * @return object|null
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Search surveys
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        return $this->like('title', $keyword)
            ->orLike('description', $keyword)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Publish survey (change status to active)
     * 
     * @param int $id
     * @return bool
     */
    public function publish($id)
    {
        return $this->update($id, [
            'status'       => 'active',
            'published_at' => date('Y-m-d H:i:s'),
            'start_date'   => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Close survey
     * 
     * @param int $id
     * @return bool
     */
    public function close($id)
    {
        return $this->update($id, [
            'status'    => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
            'end_date'  => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reopen closed survey
     * 
     * @param int $id
     * @return bool
     */
    public function reopen($id)
    {
        return $this->update($id, [
            'status'    => 'active',
            'closed_at' => null,
            'end_date'  => null
        ]);
    }

    /**
     * Extend survey end date
     * 
     * @param int $id
     * @param string $newEndDate
     * @return bool
     */
    public function extend($id, $newEndDate)
    {
        return $this->update($id, ['end_date' => $newEndDate]);
    }

    /**
     * Duplicate survey
     * 
     * @param int $id
     * @param int $creatorId
     * @return int|bool New survey ID or false
     */
    public function duplicate($id, $creatorId)
    {
        $survey = $this->find($id);

        if (!$survey) {
            return false;
        }

        // Prepare new survey data
        $newData = [
            'title'                        => $survey->title . ' (Copy)',
            'description'                  => $survey->description,
            'instructions'                 => $survey->instructions,
            'status'                       => 'draft',
            'target_audience'              => $survey->target_audience,
            'target_region_ids'            => $survey->target_region_ids,
            'target_university_ids'        => $survey->target_university_ids,
            'is_anonymous'                 => $survey->is_anonymous,
            'allow_multiple_responses'     => $survey->allow_multiple_responses,
            'show_results_to_participants' => $survey->show_results_to_participants,
            'created_by'                   => $creatorId
        ];

        return $this->insert($newData) ? $this->insertID() : false;
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total surveys count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get surveys count by status
     * 
     * @param string $status
     * @return int
     */
    public function getCountByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }

    /**
     * Get response rate
     * 
     * @param int $surveyId
     * @return float Percentage
     */
    public function getResponseRate($surveyId)
    {
        $survey = $this->find($surveyId);

        if (!$survey) {
            return 0;
        }

        // Get total target users
        $totalTarget = $this->getTotalTargetUsers($survey);

        if ($totalTarget == 0) {
            return 0;
        }

        // Get total responses
        $totalResponses = $this->db->table('survey_responses')
            ->where('survey_id', $surveyId)
            ->countAllResults(false);

        return round(($totalResponses / $totalTarget) * 100, 2);
    }

    /**
     * Get total target users for survey
     * 
     * @param object $survey
     * @return int
     */
    private function getTotalTargetUsers($survey)
    {
        $builder = $this->db->table('member_profiles');

        if ($survey->target_audience === 'region' && $survey->target_region_ids) {
            $regionIds = json_decode($survey->target_region_ids, true);
            $builder->whereIn('region_id', $regionIds);
        } elseif ($survey->target_audience === 'university' && $survey->target_university_ids) {
            $universityIds = json_decode($survey->target_university_ids, true);
            $builder->whereIn('university_id', $universityIds);
        }

        return $builder->countAllResults();
    }

    /**
     * Get completion rate
     * Percentage of users who completed all questions
     * 
     * @param int $surveyId
     * @return float
     */
    public function getCompletionRate($surveyId)
    {
        $totalQuestions = $this->db->table('survey_questions')
            ->where('survey_id', $surveyId)
            ->countAllResults();

        if ($totalQuestions == 0) {
            return 0;
        }

        $totalResponders = $this->db->table('survey_responses')
            ->select('user_id')
            ->where('survey_id', $surveyId)
            ->distinct()
            ->countAllResults(false);

        if ($totalResponders == 0) {
            return 0;
        }

        $completedUsers = $this->db->query("
            SELECT COUNT(DISTINCT user_id) as count
            FROM survey_responses
            WHERE survey_id = ?
            GROUP BY user_id
            HAVING COUNT(DISTINCT question_id) = ?
        ", [$surveyId, $totalQuestions])->getRow();

        $completed = $completedUsers ? $completedUsers->count : 0;

        return round(($completed / $totalResponders) * 100, 2);
    }

    /**
     * Get surveys distribution by status
     * 
     * @return array
     */
    public function getStatusDistribution()
    {
        return $this->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();
    }

    /**
     * Get most popular surveys (by responses)
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopular($limit = 10)
    {
        return $this->select('surveys.*, COUNT(DISTINCT survey_responses.user_id) as responses_count')
            ->join('survey_responses', 'survey_responses.survey_id = surveys.id', 'left')
            ->groupBy('surveys.id')
            ->orderBy('responses_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
