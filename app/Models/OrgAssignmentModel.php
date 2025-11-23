<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * OrgAssignmentModel
 * 
 * Model untuk mengelola penugasan/penempatan anggota SPK ke posisi organisasi
 * Mencatat siapa memegang jabatan apa, kapan mulai dan berakhir
 * Mendukung history penugasan dan status tracking
 * 
 * Features:
 * - Assignment management (assign/unassign members)
 * - Period tracking (started_at, ended_at)
 * - Status management (active, ended, suspended)
 * - Assignment history
 * - Appointment letter tracking
 * - Performance notes
 * - Soft deletes
 * 
 * Relations:
 * - belongsTo: position (org_positions)
 * - belongsTo: user (users)
 * - belongsTo: member_profile (member_profiles)
 * - belongsTo: assigned_by (users)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class OrgAssignmentModel extends Model
{
    protected $table            = 'org_assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // Allowed fields for mass assignment
    protected $allowedFields = [
        'position_id',
        'user_id',
        'started_at',
        'ended_at',
        'status',
        'assignment_type',
        'appointment_letter_number',
        'appointment_letter_date',
        'appointment_letter_path',
        'dismissal_letter_number',
        'dismissal_letter_date',
        'dismissal_letter_path',
        'notes',
        'performance_notes',
        'assigned_by',
        'ended_by',
        'end_reason'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'position_id' => [
            'rules'  => 'required|integer|is_not_unique[org_positions.id]',
            'errors' => [
                'required'      => 'Posisi harus dipilih',
                'integer'       => 'Posisi ID tidak valid',
                'is_not_unique' => 'Posisi tidak ditemukan'
            ]
        ],
        'user_id' => [
            'rules'  => 'required|integer|is_not_unique[users.id]',
            'errors' => [
                'required'      => 'Anggota harus dipilih',
                'integer'       => 'User ID tidak valid',
                'is_not_unique' => 'User tidak ditemukan'
            ]
        ],
        'started_at' => [
            'rules'  => 'required|valid_date',
            'errors' => [
                'required'   => 'Tanggal mulai harus diisi',
                'valid_date' => 'Format tanggal tidak valid'
            ]
        ],
        'ended_at' => [
            'rules'  => 'permit_empty|valid_date',
            'errors' => [
                'valid_date' => 'Format tanggal tidak valid'
            ]
        ],
        'status' => [
            'rules'  => 'required|in_list[active,ended,suspended]',
            'errors' => [
                'required' => 'Status harus dipilih',
                'in_list'  => 'Status tidak valid'
            ]
        ],
        'assignment_type' => [
            'rules'  => 'required|in_list[permanent,temporary,acting,honorary]',
            'errors' => [
                'required' => 'Tipe penugasan harus dipilih',
                'in_list'  => 'Tipe penugasan tidak valid'
            ]
        ]
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['validateAssignment'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['validateAssignment'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Validate assignment before insert/update
     * Check for overlapping assignments and position capacity
     * 
     * @param array $data
     * @return array
     */
    protected function validateAssignment(array $data): array
    {
        if (!isset($data['data']['position_id']) || !isset($data['data']['user_id'])) {
            return $data;
        }

        $positionId = $data['data']['position_id'];
        $userId = $data['data']['user_id'];
        $assignmentId = $data['id'] ?? null;

        // Check position capacity
        $positionModel = new \App\Models\OrgPositionModel();
        $position = $positionModel->find($positionId);

        if ($position) {
            $activeCount = $this->where('position_id', $positionId)
                ->where('status', 'active');

            if ($assignmentId) {
                $activeCount->where('id !=', $assignmentId);
            }

            $activeCount = $activeCount->countAllResults();

            if ($activeCount >= $position['max_holders']) {
                throw new \RuntimeException('Posisi sudah penuh. Maksimal ' . $position['max_holders'] . ' orang.');
            }
        }

        // Check for overlapping active assignments for same user-position
        if (isset($data['data']['status']) && $data['data']['status'] === 'active') {
            $overlap = $this->where('position_id', $positionId)
                ->where('user_id', $userId)
                ->where('status', 'active');

            if ($assignmentId) {
                $overlap->where('id !=', $assignmentId);
            }

            if ($overlap->countAllResults() > 0) {
                throw new \RuntimeException('User sudah memiliki penugasan aktif di posisi ini.');
            }
        }

        return $data;
    }

    // =====================================================
    // QUERY SCOPES & FILTERS
    // =====================================================

    /**
     * Get assignments by position
     * 
     * @param int $positionId Position ID
     * @return $this
     */
    public function byPosition(int $positionId)
    {
        return $this->where('position_id', $positionId);
    }

    /**
     * Get assignments by user
     * 
     * @param int $userId User ID
     * @return $this
     */
    public function byUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Get only active assignments
     * 
     * @return $this
     */
    public function active()
    {
        return $this->where('status', 'active');
    }

    /**
     * Get ended assignments
     * 
     * @return $this
     */
    public function ended()
    {
        return $this->where('status', 'ended');
    }

    /**
     * Get suspended assignments
     * 
     * @return $this
     */
    public function suspended()
    {
        return $this->where('status', 'suspended');
    }

    /**
     * Get assignments by type
     * 
     * @param string $type Assignment type
     * @return $this
     */
    public function byType(string $type)
    {
        return $this->where('assignment_type', $type);
    }

    /**
     * Get current assignments (active and not ended)
     * 
     * @return $this
     */
    public function current()
    {
        return $this->where('status', 'active')
            ->groupStart()
            ->where('ended_at IS NULL')
            ->orWhere('ended_at >', date('Y-m-d'))
            ->groupEnd();
    }

    /**
     * Get assignments within date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return $this
     */
    public function betweenDates(string $startDate, string $endDate)
    {
        return $this->where('started_at >=', $startDate)
            ->where('started_at <=', $endDate);
    }

    /**
     * Order by start date
     * 
     * @param string $direction ASC or DESC
     * @return $this
     */
    public function orderedByDate(string $direction = 'DESC')
    {
        return $this->orderBy('started_at', $direction);
    }

    // =====================================================
    // RELATIONSHIP METHODS
    // =====================================================

    /**
     * Get assignment with position details
     * 
     * @param int $assignmentId Assignment ID
     * @return array|null
     */
    public function withPosition(int $assignmentId): ?array
    {
        $assignment = $this->find($assignmentId);

        if (!$assignment) {
            return null;
        }

        $positionModel = new \App\Models\OrgPositionModel();
        $assignment['position'] = $positionModel->withUnit($assignment['position_id']);

        return $assignment;
    }

    /**
     * Get assignment with user and member profile
     * 
     * @param int $assignmentId Assignment ID
     * @return array|null
     */
    public function withUser(int $assignmentId): ?array
    {
        $assignment = $this->find($assignmentId);

        if (!$assignment) {
            return null;
        }

        $userModel = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberProfileModel();

        $user = $userModel->find($assignment['user_id']);
        if ($user) {
            $assignment['user'] = $user;

            $member = $memberModel->where('user_id', $user->id)->first();
            if ($member) {
                $assignment['member'] = $member;
            }
        }

        return $assignment;
    }

    /**
     * Get full assignment data with all relations
     * 
     * @param int $assignmentId Assignment ID
     * @return array|null
     */
    public function getFullAssignment(int $assignmentId): ?array
    {
        $assignment = $this->find($assignmentId);

        if (!$assignment) {
            return null;
        }

        // Get position with unit
        $positionModel = new \App\Models\OrgPositionModel();
        $assignment['position'] = $positionModel->withUnit($assignment['position_id']);

        // Get user with member profile
        $userModel = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberProfileModel();

        $user = $userModel->find($assignment['user_id']);
        if ($user) {
            $assignment['user'] = $user;

            $member = $memberModel->where('user_id', $user->id)->first();
            if ($member) {
                $assignment['member'] = $member;
            }
        }

        // Get assigned_by user
        if ($assignment['assigned_by']) {
            $assignment['assigner'] = $userModel->find($assignment['assigned_by']);
        }

        // Get ended_by user
        if ($assignment['ended_by']) {
            $assignment['ender'] = $userModel->find($assignment['ended_by']);
        }

        return $assignment;
    }

    /**
     * Get assignments with full details (batch)
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getWithDetails(array $filters = []): array
    {
        $builder = $this->builder();

        // Apply filters
        if (!empty($filters['position_id'])) {
            $builder->where('position_id', $filters['position_id']);
        }
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (!empty($filters['assignment_type'])) {
            $builder->where('assignment_type', $filters['assignment_type']);
        }

        $assignments = $builder->orderBy('started_at', 'DESC')->findAll();

        $positionModel = new \App\Models\OrgPositionModel();
        $userModel = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberProfileModel();

        foreach ($assignments as &$assignment) {
            // Get position
            $assignment['position'] = $positionModel->withUnit($assignment['position_id']);

            // Get user with member
            $user = $userModel->find($assignment['user_id']);
            if ($user) {
                $assignment['user'] = $user;
                $member = $memberModel->where('user_id', $user->id)->first();
                if ($member) {
                    $assignment['member'] = $member;
                }
            }
        }

        return $assignments;
    }

    // =====================================================
    // ASSIGNMENT MANAGEMENT
    // =====================================================

    /**
     * Assign user to position
     * 
     * @param int $positionId Position ID
     * @param int $userId User ID
     * @param array $data Additional assignment data
     * @return int|false Assignment ID or false on failure
     */
    public function assign(int $positionId, int $userId, array $data = [])
    {
        $assignmentData = array_merge([
            'position_id' => $positionId,
            'user_id' => $userId,
            'started_at' => date('Y-m-d'),
            'status' => 'active',
            'assignment_type' => 'permanent',
            'assigned_by' => auth()->user()->id ?? null
        ], $data);

        try {
            return $this->insert($assignmentData);
        } catch (\Exception $e) {
            log_message('error', 'Assignment failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * End assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param string $reason End reason
     * @param string|null $endDate End date (default: today)
     * @return bool
     */
    public function endAssignment(int $assignmentId, string $reason = '', ?string $endDate = null): bool
    {
        $endDate = $endDate ?? date('Y-m-d');

        return $this->update($assignmentId, [
            'status' => 'ended',
            'ended_at' => $endDate,
            'end_reason' => $reason,
            'ended_by' => auth()->user()->id ?? null
        ]);
    }

    /**
     * Suspend assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param string $reason Suspension reason
     * @return bool
     */
    public function suspend(int $assignmentId, string $reason = ''): bool
    {
        return $this->update($assignmentId, [
            'status' => 'suspended',
            'notes' => $reason
        ]);
    }

    /**
     * Reactivate suspended assignment
     * 
     * @param int $assignmentId Assignment ID
     * @return bool
     */
    public function reactivate(int $assignmentId): bool
    {
        $assignment = $this->find($assignmentId);

        if (!$assignment || $assignment['status'] !== 'suspended') {
            return false;
        }

        return $this->update($assignmentId, ['status' => 'active']);
    }

    /**
     * Transfer user to another position
     * 
     * @param int $assignmentId Current assignment ID
     * @param int $newPositionId New position ID
     * @param array $data Additional data for new assignment
     * @return int|false New assignment ID or false on failure
     */
    public function transfer(int $assignmentId, int $newPositionId, array $data = [])
    {
        $currentAssignment = $this->find($assignmentId);

        if (!$currentAssignment) {
            return false;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // End current assignment
            $this->endAssignment($assignmentId, 'Transfer ke posisi lain');

            // Create new assignment
            $newAssignmentData = array_merge([
                'position_id' => $newPositionId,
                'user_id' => $currentAssignment['user_id'],
                'started_at' => date('Y-m-d'),
                'status' => 'active',
                'assignment_type' => $currentAssignment['assignment_type'],
                'assigned_by' => auth()->user()->id ?? null,
                'notes' => 'Transfer dari posisi sebelumnya'
            ], $data);

            $newAssignmentId = $this->insert($newAssignmentData);

            $db->transComplete();

            return $db->transStatus() ? $newAssignmentId : false;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Transfer failed: ' . $e->getMessage());
            return false;
        }
    }

    // =====================================================
    // HISTORY & REPORTING
    // =====================================================

    /**
     * Get assignment history for a user
     * 
     * @param int $userId User ID
     * @param bool $includeActive Include active assignments
     * @return array
     */
    public function getUserHistory(int $userId, bool $includeActive = true): array
    {
        $builder = $this->where('user_id', $userId);

        if (!$includeActive) {
            $builder->where('status', 'ended');
        }

        $assignments = $builder->orderBy('started_at', 'DESC')->findAll();

        $positionModel = new \App\Models\OrgPositionModel();
        $unitModel = new \App\Models\OrgUnitModel();

        foreach ($assignments as &$assignment) {
            $position = $positionModel->find($assignment['position_id']);
            if ($position) {
                $assignment['position'] = $position;
                $assignment['unit'] = $unitModel->find($position['unit_id']);
            }
        }

        return $assignments;
    }

    /**
     * Get assignment history for a position
     * 
     * @param int $positionId Position ID
     * @param bool $includeActive Include active assignments
     * @return array
     */
    public function getPositionHistory(int $positionId, bool $includeActive = true): array
    {
        $builder = $this->where('position_id', $positionId);

        if (!$includeActive) {
            $builder->where('status', 'ended');
        }

        $assignments = $builder->orderBy('started_at', 'DESC')->findAll();

        $userModel = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberProfileModel();

        foreach ($assignments as &$assignment) {
            $user = $userModel->find($assignment['user_id']);
            if ($user) {
                $assignment['user'] = $user;
                $member = $memberModel->where('user_id', $user->id)->first();
                if ($member) {
                    $assignment['member'] = $member;
                }
            }
        }

        return $assignments;
    }

    /**
     * Get current user's active assignments
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getCurrentAssignments(int $userId): array
    {
        $assignments = $this->where('user_id', $userId)
            ->where('status', 'active')
            ->findAll();

        $positionModel = new \App\Models\OrgPositionModel();
        $unitModel = new \App\Models\OrgUnitModel();

        foreach ($assignments as &$assignment) {
            $position = $positionModel->find($assignment['position_id']);
            if ($position) {
                $assignment['position'] = $position;
                $assignment['unit'] = $unitModel->find($position['unit_id']);
            }
        }

        return $assignments;
    }

    // =====================================================
    // STATISTICS METHODS
    // =====================================================

    /**
     * Get total assignments count
     * 
     * @param array $filters Optional filters
     * @return int
     */
    public function getTotalAssignments(array $filters = []): int
    {
        $builder = $this->builder();

        if (!empty($filters['position_id'])) {
            $builder->where('position_id', $filters['position_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (!empty($filters['assignment_type'])) {
            $builder->where('assignment_type', $filters['assignment_type']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get assignments grouped by status
     * 
     * @return array
     */
    public function getByStatus(): array
    {
        return $this->select('status, COUNT(*) as total')
            ->groupBy('status')
            ->findAll();
    }

    /**
     * Get assignments grouped by type
     * 
     * @return array
     */
    public function getByType(): array
    {
        return $this->select('assignment_type, COUNT(*) as total')
            ->groupBy('assignment_type')
            ->findAll();
    }

    /**
     * Get average assignment duration (for ended assignments)
     * 
     * @return float Average days
     */
    public function getAverageDuration(): float
    {
        $result = $this->select('AVG(DATEDIFF(ended_at, started_at)) as avg_days')
            ->where('status', 'ended')
            ->where('ended_at IS NOT NULL')
            ->get()
            ->getRow();

        return round($result->avg_days ?? 0, 2);
    }

    /**
     * Get turnover rate (ended vs active)
     * 
     * @return array
     */
    public function getTurnoverRate(): array
    {
        $total = $this->countAll();
        $active = $this->where('status', 'active')->countAllResults();
        $ended = $this->where('status', 'ended')->countAllResults();

        return [
            'total' => $total,
            'active' => $active,
            'ended' => $ended,
            'turnover_rate' => $total > 0 ? round(($ended / $total) * 100, 2) : 0
        ];
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    /**
     * Check if user has active assignment in position
     * 
     * @param int $userId User ID
     * @param int $positionId Position ID
     * @return bool
     */
    public function hasActiveAssignment(int $userId, int $positionId): bool
    {
        return $this->where('user_id', $userId)
            ->where('position_id', $positionId)
            ->where('status', 'active')
            ->countAllResults() > 0;
    }

    /**
     * Search assignments by keyword
     * 
     * @param string $keyword Search keyword
     * @param array $filters Optional filters
     * @return array
     */
    public function search(string $keyword, array $filters = []): array
    {
        $builder = $this->builder();

        // Join with users and positions for search
        $builder->join('users', 'users.id = org_assignments.user_id')
            ->join('org_positions', 'org_positions.id = org_assignments.position_id')
            ->select('org_assignments.*, users.email, org_positions.title as position_title')
            ->groupStart()
            ->like('users.email', $keyword)
            ->orLike('org_positions.title', $keyword)
            ->orLike('org_assignments.notes', $keyword)
            ->groupEnd();

        if (!empty($filters['status'])) {
            $builder->where('org_assignments.status', $filters['status']);
        }

        return $builder->orderBy('org_assignments.started_at', 'DESC')->findAll();
    }
}
