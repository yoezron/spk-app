<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * OrgPositionModel
 * 
 * Model untuk mengelola jabatan/posisi dalam struktur organisasi SPK
 * Setiap posisi terikat pada satu unit organisasi
 * Mendukung job description, reporting line, dan multiple position holders
 * 
 * Features:
 * - Position management per unit
 * - Job description & responsibilities
 * - Reporting line (atasan langsung)
 * - Position types (executive, structural, functional, coordinator, staff)
 * - Position levels (top, middle, lower)
 * - Multiple holders support
 * - Active period management
 * - Soft deletes
 * 
 * Relations:
 * - belongsTo: unit (org_units)
 * - hasMany: assignments (org_assignments)
 * - belongsTo: reports_to (self-reference)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class OrgPositionModel extends Model
{
    protected $table            = 'org_positions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // Allowed fields for mass assignment
    protected $allowedFields = [
        'unit_id',
        'title',
        'slug',
        'short_title',
        'position_type',
        'position_level',
        'job_description',
        'responsibilities',
        'authorities',
        'requirements',
        'reports_to',
        'display_order',
        'is_leadership',
        'max_holders',
        'is_active',
        'period_start',
        'period_end',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'unit_id' => [
            'rules'  => 'required|integer|is_not_unique[org_units.id]',
            'errors' => [
                'required'      => 'Unit organisasi harus dipilih',
                'integer'       => 'Unit ID tidak valid',
                'is_not_unique' => 'Unit organisasi tidak ditemukan'
            ]
        ],
        'title' => [
            'rules'  => 'required|max_length[150]',
            'errors' => [
                'required'   => 'Nama jabatan harus diisi',
                'max_length' => 'Nama jabatan maksimal 150 karakter'
            ]
        ],
        'slug' => [
            'rules'  => 'required|max_length[150]|is_unique[org_positions.slug,id,{id}]',
            'errors' => [
                'required'  => 'Slug harus diisi',
                'is_unique' => 'Slug sudah digunakan'
            ]
        ],
        'position_type' => [
            'rules'  => 'required|in_list[executive,structural,functional,coordinator,staff]',
            'errors' => [
                'required' => 'Tipe jabatan harus dipilih',
                'in_list'  => 'Tipe jabatan tidak valid'
            ]
        ],
        'position_level' => [
            'rules'  => 'required|in_list[top,middle,lower]',
            'errors' => [
                'required' => 'Level jabatan harus dipilih',
                'in_list'  => 'Level jabatan tidak valid'
            ]
        ],
        'max_holders' => [
            'rules'  => 'permit_empty|integer|greater_than[0]',
            'errors' => [
                'integer'      => 'Jumlah maksimal holder harus berupa angka',
                'greater_than' => 'Jumlah maksimal holder minimal 1'
            ]
        ]
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['generateSlug'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generate slug from title if not provided
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data): array
    {
        if (isset($data['data']['title']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = url_title($data['data']['title'], '-', true);
        }
        return $data;
    }

    // =====================================================
    // QUERY SCOPES & FILTERS
    // =====================================================

    /**
     * Get positions by unit
     * 
     * @param int $unitId Unit ID
     * @return $this
     */
    public function byUnit(int $unitId)
    {
        return $this->where('unit_id', $unitId);
    }

    /**
     * Get only active positions
     * 
     * @return $this
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /**
     * Get leadership positions only
     * 
     * @return $this
     */
    public function leadership()
    {
        return $this->where('is_leadership', 1);
    }

    /**
     * Get positions by type
     * 
     * @param string $type Position type
     * @return $this
     */
    public function byType(string $type)
    {
        return $this->where('position_type', $type);
    }

    /**
     * Get positions by level
     * 
     * @param string $level Position level
     * @return $this
     */
    public function byLevel(string $level)
    {
        return $this->where('position_level', $level);
    }

    /**
     * Get executive positions (top management)
     * 
     * @return $this
     */
    public function executive()
    {
        return $this->where('position_type', 'executive');
    }

    /**
     * Get structural positions
     * 
     * @return $this
     */
    public function structural()
    {
        return $this->where('position_type', 'structural');
    }

    /**
     * Order by display order
     * 
     * @param string $direction ASC or DESC
     * @return $this
     */
    public function ordered(string $direction = 'ASC')
    {
        return $this->orderBy('display_order', $direction);
    }

    /**
     * Get positions that have vacant slots
     * 
     * @return array
     */
    public function available(): array
    {
        $positions = $this->where('is_active', 1)->findAll();
        $assignmentModel = new \App\Models\OrgAssignmentModel();

        $available = [];
        foreach ($positions as $position) {
            $currentHolders = $assignmentModel
                ->where('position_id', $position['id'])
                ->where('status', 'active')
                ->countAllResults();

            if ($currentHolders < $position['max_holders']) {
                $position['current_holders'] = $currentHolders;
                $position['available_slots'] = $position['max_holders'] - $currentHolders;
                $available[] = $position;
            }
        }

        return $available;
    }

    // =====================================================
    // RELATIONSHIP METHODS
    // =====================================================

    /**
     * Get position with its unit
     * 
     * @param int $positionId Position ID
     * @return array|null
     */
    public function withUnit(int $positionId): ?array
    {
        $position = $this->find($positionId);

        if (!$position) {
            return null;
        }

        $unitModel = new \App\Models\OrgUnitModel();
        $position['unit'] = $unitModel->find($position['unit_id']);

        return $position;
    }

    /**
     * Get position with its assignments
     * 
     * @param int $positionId Position ID
     * @param bool $activeOnly Get only active assignments
     * @return array|null
     */
    public function withAssignments(int $positionId, bool $activeOnly = true): ?array
    {
        $position = $this->find($positionId);

        if (!$position) {
            return null;
        }

        $assignmentModel = new \App\Models\OrgAssignmentModel();
        $builder = $assignmentModel->where('position_id', $positionId);

        if ($activeOnly) {
            $builder->where('status', 'active');
        }

        $assignments = $builder->findAll();

        // Get user details for each assignment
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

        $position['assignments'] = $assignments;
        $position['current_holders'] = count($assignments);
        $position['available_slots'] = $position['max_holders'] - count($assignments);

        return $position;
    }

    /**
     * Get position with superior (reports_to)
     * 
     * @param int $positionId Position ID
     * @return array|null
     */
    public function withSuperior(int $positionId): ?array
    {
        $position = $this->find($positionId);

        if (!$position || !$position['reports_to']) {
            return $position;
        }

        $position['superior'] = $this->find($position['reports_to']);

        return $position;
    }

    /**
     * Get position with subordinates
     * 
     * @param int $positionId Position ID
     * @return array|null
     */
    public function withSubordinates(int $positionId): ?array
    {
        $position = $this->find($positionId);

        if (!$position) {
            return null;
        }

        $position['subordinates'] = $this->where('reports_to', $positionId)
            ->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->findAll();

        return $position;
    }

    /**
     * Get full position data with all relations
     * 
     * @param int $positionId Position ID
     * @return array|null
     */
    public function getFullPosition(int $positionId): ?array
    {
        $position = $this->find($positionId);

        if (!$position) {
            return null;
        }

        // Get unit
        $unitModel = new \App\Models\OrgUnitModel();
        $position['unit'] = $unitModel->find($position['unit_id']);

        // Get superior
        if ($position['reports_to']) {
            $position['superior'] = $this->find($position['reports_to']);
        }

        // Get subordinates
        $position['subordinates'] = $this->where('reports_to', $positionId)
            ->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->findAll();

        // Get assignments with user details
        $assignmentModel = new \App\Models\OrgAssignmentModel();
        $assignments = $assignmentModel->where('position_id', $positionId)
            ->where('status', 'active')
            ->findAll();

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

        $position['assignments'] = $assignments;
        $position['current_holders'] = count($assignments);
        $position['available_slots'] = $position['max_holders'] - count($assignments);

        return $position;
    }

    /**
     * Get positions by unit with assignments
     * 
     * @param int $unitId Unit ID
     * @param bool $activeOnly Get only active positions
     * @return array
     */
    public function getUnitPositionsWithAssignments(int $unitId, bool $activeOnly = true): array
    {
        $builder = $this->where('unit_id', $unitId);

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        $positions = $builder->orderBy('display_order', 'ASC')->findAll();

        $assignmentModel = new \App\Models\OrgAssignmentModel();
        $userModel = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberProfileModel();

        foreach ($positions as &$position) {
            $assignments = $assignmentModel
                ->where('position_id', $position['id'])
                ->where('status', 'active')
                ->findAll();

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

            $position['assignments'] = $assignments;
            $position['current_holders'] = count($assignments);
            $position['available_slots'] = $position['max_holders'] - count($assignments);
        }

        return $positions;
    }

    // =====================================================
    // JOB DESCRIPTION METHODS
    // =====================================================

    /**
     * Update job description
     * 
     * @param int $positionId Position ID
     * @param string $description Job description
     * @return bool
     */
    public function updateJobDescription(int $positionId, string $description): bool
    {
        return $this->update($positionId, ['job_description' => $description]);
    }

    /**
     * Update responsibilities (JSON or array)
     * 
     * @param int $positionId Position ID
     * @param array|string $responsibilities Responsibilities data
     * @return bool
     */
    public function updateResponsibilities(int $positionId, $responsibilities): bool
    {
        if (is_array($responsibilities)) {
            $responsibilities = json_encode($responsibilities);
        }
        return $this->update($positionId, ['responsibilities' => $responsibilities]);
    }

    /**
     * Update authorities (JSON or array)
     * 
     * @param int $positionId Position ID
     * @param array|string $authorities Authorities data
     * @return bool
     */
    public function updateAuthorities(int $positionId, $authorities): bool
    {
        if (is_array($authorities)) {
            $authorities = json_encode($authorities);
        }
        return $this->update($positionId, ['authorities' => $authorities]);
    }

    /**
     * Get parsed responsibilities as array
     * 
     * @param int $positionId Position ID
     * @return array
     */
    public function getResponsibilities(int $positionId): array
    {
        $position = $this->find($positionId);

        if (!$position || empty($position['responsibilities'])) {
            return [];
        }

        // Try to decode JSON
        $decoded = json_decode($position['responsibilities'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // If not JSON, split by newline
        return array_filter(explode("\n", $position['responsibilities']));
    }

    /**
     * Get parsed authorities as array
     * 
     * @param int $positionId Position ID
     * @return array
     */
    public function getAuthorities(int $positionId): array
    {
        $position = $this->find($positionId);

        if (!$position || empty($position['authorities'])) {
            return [];
        }

        // Try to decode JSON
        $decoded = json_decode($position['authorities'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // If not JSON, split by newline
        return array_filter(explode("\n", $position['authorities']));
    }

    // =====================================================
    // STATISTICS METHODS
    // =====================================================

    /**
     * Get total positions count
     * 
     * @param array $filters Optional filters
     * @return int
     */
    public function getTotalPositions(array $filters = []): int
    {
        $builder = $this->builder();

        if (!empty($filters['unit_id'])) {
            $builder->where('unit_id', $filters['unit_id']);
        }
        if (!empty($filters['position_type'])) {
            $builder->where('position_type', $filters['position_type']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get positions grouped by type
     * 
     * @param int|null $unitId Optional unit filter
     * @return array
     */
    public function getByType(?int $unitId = null): array
    {
        $builder = $this->select('position_type, COUNT(*) as total')
            ->where('is_active', 1)
            ->groupBy('position_type');

        if ($unitId) {
            $builder->where('unit_id', $unitId);
        }

        return $builder->findAll();
    }

    /**
     * Get positions grouped by level
     * 
     * @param int|null $unitId Optional unit filter
     * @return array
     */
    public function getByLevel(?int $unitId = null): array
    {
        $builder = $this->select('position_level, COUNT(*) as total')
            ->where('is_active', 1)
            ->groupBy('position_level');

        if ($unitId) {
            $builder->where('unit_id', $unitId);
        }

        return $builder->findAll();
    }

    /**
     * Get vacancy statistics
     * 
     * @param int|null $unitId Optional unit filter
     * @return array
     */
    public function getVacancyStats(?int $unitId = null): array
    {
        $builder = $this->where('is_active', 1);

        if ($unitId) {
            $builder->where('unit_id', $unitId);
        }

        $positions = $builder->findAll();
        $assignmentModel = new \App\Models\OrgAssignmentModel();

        $stats = [
            'total_positions' => count($positions),
            'total_slots' => 0,
            'filled_slots' => 0,
            'vacant_slots' => 0,
            'fully_filled' => 0,
            'partially_filled' => 0,
            'fully_vacant' => 0
        ];

        foreach ($positions as $position) {
            $currentHolders = $assignmentModel
                ->where('position_id', $position['id'])
                ->where('status', 'active')
                ->countAllResults();

            $stats['total_slots'] += $position['max_holders'];
            $stats['filled_slots'] += $currentHolders;

            if ($currentHolders >= $position['max_holders']) {
                $stats['fully_filled']++;
            } elseif ($currentHolders > 0) {
                $stats['partially_filled']++;
            } else {
                $stats['fully_vacant']++;
            }
        }

        $stats['vacant_slots'] = $stats['total_slots'] - $stats['filled_slots'];
        $stats['fill_rate'] = $stats['total_slots'] > 0
            ? round(($stats['filled_slots'] / $stats['total_slots']) * 100, 2)
            : 0;

        return $stats;
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    /**
     * Check if position has active assignments
     * 
     * @param int $positionId Position ID
     * @return bool
     */
    public function hasActiveAssignments(int $positionId): bool
    {
        $assignmentModel = new \App\Models\OrgAssignmentModel();
        return $assignmentModel
            ->where('position_id', $positionId)
            ->where('status', 'active')
            ->countAllResults() > 0;
    }

    /**
     * Check if position is vacant (has available slots)
     * 
     * @param int $positionId Position ID
     * @return bool
     */
    public function isVacant(int $positionId): bool
    {
        $position = $this->find($positionId);

        if (!$position) {
            return false;
        }

        $assignmentModel = new \App\Models\OrgAssignmentModel();
        $currentHolders = $assignmentModel
            ->where('position_id', $positionId)
            ->where('status', 'active')
            ->countAllResults();

        return $currentHolders < $position['max_holders'];
    }

    /**
     * Activate position
     * 
     * @param int $positionId Position ID
     * @return bool
     */
    public function activate(int $positionId): bool
    {
        return $this->update($positionId, ['is_active' => 1]);
    }

    /**
     * Deactivate position
     * 
     * @param int $positionId Position ID
     * @return bool
     */
    public function deactivate(int $positionId): bool
    {
        return $this->update($positionId, ['is_active' => 0]);
    }

    /**
     * Get next display order for a unit
     * 
     * @param int $unitId Unit ID
     * @return int
     */
    public function getNextDisplayOrder(int $unitId): int
    {
        $maxOrder = $this->selectMax('display_order')
            ->where('unit_id', $unitId)
            ->get()
            ->getRow();

        return ($maxOrder->display_order ?? 0) + 1;
    }

    /**
     * Search positions by keyword
     * 
     * @param string $keyword Search keyword
     * @param array $filters Optional filters
     * @return array
     */
    public function search(string $keyword, array $filters = []): array
    {
        $builder = $this->builder();

        $builder->groupStart()
            ->like('title', $keyword)
            ->orLike('short_title', $keyword)
            ->orLike('job_description', $keyword)
            ->groupEnd();

        if (!empty($filters['unit_id'])) {
            $builder->where('unit_id', $filters['unit_id']);
        }
        if (!empty($filters['position_type'])) {
            $builder->where('position_type', $filters['position_type']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        return $builder->orderBy('title', 'ASC')->findAll();
    }

    /**
     * Get reporting chain (organizational hierarchy from this position up)
     * 
     * @param int $positionId Position ID
     * @return array
     */
    public function getReportingChain(int $positionId): array
    {
        $chain = [];
        $currentPosition = $this->find($positionId);

        while ($currentPosition) {
            $chain[] = [
                'id'    => $currentPosition['id'],
                'title' => $currentPosition['title'],
                'type'  => $currentPosition['position_type'],
                'level' => $currentPosition['position_level']
            ];

            if ($currentPosition['reports_to']) {
                $currentPosition = $this->find($currentPosition['reports_to']);
            } else {
                break;
            }
        }

        return $chain;
    }

    /**
     * Duplicate position
     * 
     * @param int $positionId Source position ID
     * @param array $newData New position data
     * @return int|false New position ID or false on failure
     */
    public function duplicate(int $positionId, array $newData)
    {
        $sourcePosition = $this->find($positionId);

        if (!$sourcePosition) {
            return false;
        }

        // Prepare new position data
        $positionData = array_merge($sourcePosition, $newData);
        unset($positionData['id'], $positionData['created_at'], $positionData['updated_at']);

        // Insert new position
        return $this->insert($positionData);
    }
}
