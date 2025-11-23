<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * OrgUnitModel
 * 
 * Model untuk mengelola unit organisasi SPK
 * Mendukung struktur hierarkis (parent-child relationships)
 * Scope: pusat, wilayah, kampus, departemen, divisi, seksi
 * 
 * Features:
 * - Hierarchical structure (parent-child)
 * - Regional scope filtering
 * - Active period management
 * - Soft deletes
 * - Full CRUD operations
 * 
 * Relations:
 * - hasMany: positions (org_positions)
 * - belongsTo: parent (self-reference)
 * - hasMany: children (self-reference)
 * - belongsTo: region (provinces)
 * - belongsTo: university (universities)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class OrgUnitModel extends Model
{
    protected $table            = 'org_units';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // Allowed fields for mass assignment
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'parent_id',
        'level',
        'scope',
        'region_id',
        'university_id',
        'period_start',
        'period_end',
        'is_active',
        'display_order',
        'contact_email',
        'contact_phone',
        'address',
        'logo_path'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'name' => [
            'rules'  => 'required|max_length[150]',
            'errors' => [
                'required'   => 'Nama unit organisasi harus diisi',
                'max_length' => 'Nama unit maksimal 150 karakter'
            ]
        ],
        'slug' => [
            'rules'  => 'required|max_length[150]|is_unique[org_units.slug,id,{id}]',
            'errors' => [
                'required'  => 'Slug harus diisi',
                'is_unique' => 'Slug sudah digunakan'
            ]
        ],
        'scope' => [
            'rules'  => 'required|in_list[pusat,wilayah,kampus,departemen,divisi,seksi]',
            'errors' => [
                'required' => 'Scope harus dipilih',
                'in_list'  => 'Scope tidak valid'
            ]
        ],
        'level' => [
            'rules'  => 'required|integer|greater_than[0]|less_than[10]',
            'errors' => [
                'required'     => 'Level harus diisi',
                'integer'      => 'Level harus berupa angka',
                'greater_than' => 'Level minimal 1',
                'less_than'    => 'Level maksimal 9'
            ]
        ],
        'period_start' => [
            'rules'  => 'permit_empty|valid_date',
            'errors' => [
                'valid_date' => 'Format tanggal tidak valid'
            ]
        ],
        'period_end' => [
            'rules'  => 'permit_empty|valid_date',
            'errors' => [
                'valid_date' => 'Format tanggal tidak valid'
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
     * Generate slug from name if not provided
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data): array
    {
        if (isset($data['data']['name']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = url_title($data['data']['name'], '-', true);
        }
        return $data;
    }

    // =====================================================
    // QUERY SCOPES & FILTERS
    // =====================================================

    /**
     * Get only units with scope 'pusat'
     * 
     * @return $this
     */
    public function pusat()
    {
        return $this->where('scope', 'pusat');
    }

    /**
     * Get only units with scope 'wilayah'
     * 
     * @return $this
     */
    public function wilayah()
    {
        return $this->where('scope', 'wilayah');
    }

    /**
     * Get units by specific wilayah (province)
     * 
     * @param int $provinceId Province ID
     * @return $this
     */
    public function byWilayah(int $provinceId)
    {
        return $this->where('region_id', $provinceId);
    }

    /**
     * Get units by specific university
     * 
     * @param int $universityId University ID
     * @return $this
     */
    public function byUniversity(int $universityId)
    {
        return $this->where('university_id', $universityId);
    }

    /**
     * Get only active units
     * 
     * @return $this
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /**
     * Get units by scope
     * 
     * @param string $scope Scope type
     * @return $this
     */
    public function byScope(string $scope)
    {
        return $this->where('scope', $scope);
    }

    /**
     * Get units by level
     * 
     * @param int $level Level number
     * @return $this
     */
    public function byLevel(int $level)
    {
        return $this->where('level', $level);
    }

    /**
     * Get root level units (no parent)
     * 
     * @return $this
     */
    public function rootLevel()
    {
        return $this->where('parent_id IS NULL');
    }

    /**
     * Get child units of a parent
     * 
     * @param int $parentId Parent unit ID
     * @return $this
     */
    public function byParent(int $parentId)
    {
        return $this->where('parent_id', $parentId);
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

    // =====================================================
    // RELATIONSHIP METHODS
    // =====================================================

    /**
     * Get unit with its positions
     * 
     * @param int $unitId Unit ID
     * @return array|null
     */
    public function withPositions(int $unitId): ?array
    {
        $unit = $this->find($unitId);

        if (!$unit) {
            return null;
        }

        $positionModel = new \App\Models\OrgPositionModel();
        $unit['positions'] = $positionModel->where('unit_id', $unitId)
            ->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->findAll();

        return $unit;
    }

    /**
     * Get unit with its parent
     * 
     * @param int $unitId Unit ID
     * @return array|null
     */
    public function withParent(int $unitId): ?array
    {
        $unit = $this->find($unitId);

        if (!$unit || !$unit['parent_id']) {
            return $unit;
        }

        $unit['parent'] = $this->find($unit['parent_id']);

        return $unit;
    }

    /**
     * Get unit with its children
     * 
     * @param int $unitId Unit ID
     * @return array|null
     */
    public function withChildren(int $unitId): ?array
    {
        $unit = $this->find($unitId);

        if (!$unit) {
            return null;
        }

        $unit['children'] = $this->where('parent_id', $unitId)
            ->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->findAll();

        return $unit;
    }

    /**
     * Get full unit data with all relations
     * 
     * @param int $unitId Unit ID
     * @return array|null
     */
    public function getFullUnit(int $unitId): ?array
    {
        $unit = $this->find($unitId);

        if (!$unit) {
            return null;
        }

        // Get parent
        if ($unit['parent_id']) {
            $unit['parent'] = $this->find($unit['parent_id']);
        }

        // Get children
        $unit['children'] = $this->where('parent_id', $unitId)
            ->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->findAll();

        // Get positions with assignments
        $positionModel = new \App\Models\OrgPositionModel();
        $positions = $positionModel->where('unit_id', $unitId)
            ->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->findAll();

        $assignmentModel = new \App\Models\OrgAssignmentModel();
        foreach ($positions as &$position) {
            $position['assignments'] = $assignmentModel
                ->where('position_id', $position['id'])
                ->where('status', 'active')
                ->findAll();
        }

        $unit['positions'] = $positions;

        // Get region if applicable
        if ($unit['region_id']) {
            $provinceModel = new \App\Models\ProvinceModel();
            $unit['region'] = $provinceModel->find($unit['region_id']);
        }

        // Get university if applicable
        if ($unit['university_id']) {
            $universityModel = new \App\Models\UniversityModel();
            $unit['university'] = $universityModel->find($unit['university_id']);
        }

        return $unit;
    }

    // =====================================================
    // HIERARCHICAL METHODS
    // =====================================================

    /**
     * Get hierarchical structure starting from root
     * 
     * @param array $filters Optional filters (scope, region_id, etc.)
     * @return array
     */
    public function getHierarchy(array $filters = []): array
    {
        $builder = $this->builder();

        // Apply filters
        if (!empty($filters['scope'])) {
            $builder->where('scope', $filters['scope']);
        }
        if (!empty($filters['region_id'])) {
            $builder->where('region_id', $filters['region_id']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        // Get all units
        $units = $builder->where('parent_id IS NULL')
            ->orderBy('display_order', 'ASC')
            ->get()
            ->getResultArray();

        // Build hierarchy recursively
        foreach ($units as &$unit) {
            $unit['children'] = $this->getChildrenRecursive($unit['id'], $filters);
        }

        return $units;
    }

    /**
     * Get children recursively
     * 
     * @param int $parentId Parent unit ID
     * @param array $filters Optional filters
     * @return array
     */
    protected function getChildrenRecursive(int $parentId, array $filters = []): array
    {
        $builder = $this->builder();

        $builder->where('parent_id', $parentId);

        // Apply filters
        if (!empty($filters['scope'])) {
            $builder->where('scope', $filters['scope']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        $children = $builder->orderBy('display_order', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($children as &$child) {
            $child['children'] = $this->getChildrenRecursive($child['id'], $filters);
        }

        return $children;
    }

    /**
     * Get breadcrumb path to root
     * 
     * @param int $unitId Unit ID
     * @return array
     */
    public function getBreadcrumb(int $unitId): array
    {
        $breadcrumb = [];
        $currentUnit = $this->find($unitId);

        while ($currentUnit) {
            array_unshift($breadcrumb, [
                'id'   => $currentUnit['id'],
                'name' => $currentUnit['name'],
                'slug' => $currentUnit['slug']
            ]);

            if ($currentUnit['parent_id']) {
                $currentUnit = $this->find($currentUnit['parent_id']);
            } else {
                break;
            }
        }

        return $breadcrumb;
    }

    /**
     * Get all ancestor IDs
     * 
     * @param int $unitId Unit ID
     * @return array
     */
    public function getAncestorIds(int $unitId): array
    {
        $ancestors = [];
        $currentUnit = $this->find($unitId);

        while ($currentUnit && $currentUnit['parent_id']) {
            $ancestors[] = $currentUnit['parent_id'];
            $currentUnit = $this->find($currentUnit['parent_id']);
        }

        return $ancestors;
    }

    /**
     * Get all descendant IDs
     * 
     * @param int $unitId Unit ID
     * @return array
     */
    public function getDescendantIds(int $unitId): array
    {
        $descendants = [];
        $children = $this->where('parent_id', $unitId)->findAll();

        foreach ($children as $child) {
            $descendants[] = $child['id'];
            $descendants = array_merge(
                $descendants,
                $this->getDescendantIds($child['id'])
            );
        }

        return $descendants;
    }

    // =====================================================
    // STATISTICS METHODS
    // =====================================================

    /**
     * Get total units count
     * 
     * @param array $filters Optional filters
     * @return int
     */
    public function getTotalUnits(array $filters = []): int
    {
        $builder = $this->builder();

        if (!empty($filters['scope'])) {
            $builder->where('scope', $filters['scope']);
        }
        if (!empty($filters['region_id'])) {
            $builder->where('region_id', $filters['region_id']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get units grouped by scope
     * 
     * @return array
     */
    public function getByScope(): array
    {
        return $this->select('scope, COUNT(*) as total')
            ->where('is_active', 1)
            ->groupBy('scope')
            ->findAll();
    }

    /**
     * Get units grouped by level
     * 
     * @return array
     */
    public function getByLevel(): array
    {
        return $this->select('level, COUNT(*) as total')
            ->where('is_active', 1)
            ->groupBy('level')
            ->orderBy('level', 'ASC')
            ->findAll();
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    /**
     * Check if unit has children
     * 
     * @param int $unitId Unit ID
     * @return bool
     */
    public function hasChildren(int $unitId): bool
    {
        return $this->where('parent_id', $unitId)->countAllResults() > 0;
    }

    /**
     * Check if unit has positions
     * 
     * @param int $unitId Unit ID
     * @return bool
     */
    public function hasPositions(int $unitId): bool
    {
        $positionModel = new \App\Models\OrgPositionModel();
        return $positionModel->where('unit_id', $unitId)->countAllResults() > 0;
    }

    /**
     * Activate unit
     * 
     * @param int $unitId Unit ID
     * @return bool
     */
    public function activate(int $unitId): bool
    {
        return $this->update($unitId, ['is_active' => 1]);
    }

    /**
     * Deactivate unit
     * 
     * @param int $unitId Unit ID
     * @return bool
     */
    public function deactivate(int $unitId): bool
    {
        return $this->update($unitId, ['is_active' => 0]);
    }

    /**
     * Get next display order for a parent
     * 
     * @param int|null $parentId Parent unit ID (null for root)
     * @return int
     */
    public function getNextDisplayOrder(?int $parentId = null): int
    {
        $builder = $this->builder();

        if ($parentId) {
            $builder->where('parent_id', $parentId);
        } else {
            $builder->where('parent_id IS NULL');
        }

        $maxOrder = $builder->selectMax('display_order')->get()->getRow();

        return ($maxOrder->display_order ?? 0) + 1;
    }

    /**
     * Search units by keyword
     * 
     * @param string $keyword Search keyword
     * @param array $filters Optional filters
     * @return array
     */
    public function search(string $keyword, array $filters = []): array
    {
        $builder = $this->builder();

        $builder->groupStart()
            ->like('name', $keyword)
            ->orLike('description', $keyword)
            ->groupEnd();

        if (!empty($filters['scope'])) {
            $builder->where('scope', $filters['scope']);
        }
        if (!empty($filters['region_id'])) {
            $builder->where('region_id', $filters['region_id']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        return $builder->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Duplicate unit structure
     * 
     * @param int $unitId Source unit ID
     * @param array $newData New unit data
     * @return int|false New unit ID or false on failure
     */
    public function duplicate(int $unitId, array $newData)
    {
        $sourceUnit = $this->find($unitId);

        if (!$sourceUnit) {
            return false;
        }

        // Prepare new unit data
        $unitData = array_merge($sourceUnit, $newData);
        unset($unitData['id'], $unitData['created_at'], $unitData['updated_at']);

        // Insert new unit
        $newUnitId = $this->insert($unitData);

        if (!$newUnitId) {
            return false;
        }

        // Duplicate positions if requested
        if (!empty($newData['duplicate_positions'])) {
            $positionModel = new \App\Models\OrgPositionModel();
            $positions = $positionModel->where('unit_id', $unitId)->findAll();

            foreach ($positions as $position) {
                $positionData = $position;
                unset($positionData['id'], $positionData['created_at'], $positionData['updated_at']);
                $positionData['unit_id'] = $newUnitId;
                $positionModel->insert($positionData);
            }
        }

        return $newUnitId;
    }
}
