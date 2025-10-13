<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * WAGroupModel
 * 
 * Model untuk mengelola WhatsApp groups SPK
 * Mendukung grup nasional dan regional per wilayah
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class WAGroupModel extends Model
{
    protected $table            = 'wa_groups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'description',
        'scope',
        'region_id',
        'province_id',
        'invite_link',
        'group_phone',
        'admin_name',
        'admin_phone',
        'is_active',
        'member_count',
        'max_members',
        'created_by',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'scope' => 'required|in_list[national,regional,provincial]',
        'invite_link' => 'permit_empty|max_length[500]|valid_url',
        'group_phone' => 'permit_empty|max_length[20]',
        'admin_name' => 'permit_empty|max_length[100]',
        'admin_phone' => 'permit_empty|max_length[20]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'member_count' => 'permit_empty|is_natural',
        'max_members' => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama grup harus diisi',
            'min_length' => 'Nama grup minimal 3 karakter',
            'max_length' => 'Nama grup maksimal 255 karakter',
        ],
        'scope' => [
            'required' => 'Scope grup harus diisi',
            'in_list' => 'Scope tidak valid',
        ],
        'invite_link' => [
            'valid_url' => 'Format link tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultValues'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get WA group with region
     * 
     * @return object
     */
    public function withRegion()
    {
        return $this->select('wa_groups.*, regions.name as region_name, regions.code as region_code')
            ->join('regions', 'regions.id = wa_groups.region_id', 'left');
    }

    /**
     * Get WA group with province
     * 
     * @return object
     */
    public function withProvince()
    {
        return $this->select('wa_groups.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = wa_groups.province_id', 'left');
    }

    /**
     * Get WA group with creator
     * 
     * @return object
     */
    public function withCreator()
    {
        return $this->select('wa_groups.*, users.username as creator_name, users.email as creator_email')
            ->join('users', 'users.id = wa_groups.created_by', 'left');
    }

    /**
     * Get WA group with complete data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('wa_groups.*')
            ->select('regions.name as region_name, regions.code as region_code')
            ->select('provinces.name as province_name')
            ->select('users.username as creator_name')
            ->join('regions', 'regions.id = wa_groups.region_id', 'left')
            ->join('provinces', 'provinces.id = wa_groups.province_id', 'left')
            ->join('users', 'users.id = wa_groups.created_by', 'left');
    }

    // ========================================
    // SCOPES - FILTERING BY SCOPE
    // ========================================

    /**
     * Get national groups
     * 
     * @return object
     */
    public function national()
    {
        return $this->where('scope', 'national');
    }

    /**
     * Get regional groups
     * 
     * @return object
     */
    public function regional()
    {
        return $this->where('scope', 'regional');
    }

    /**
     * Get provincial groups
     * 
     * @return object
     */
    public function provincial()
    {
        return $this->where('scope', 'provincial');
    }

    /**
     * Get groups by scope
     * 
     * @param string $scope Scope value
     * @return object
     */
    public function byScope(string $scope)
    {
        return $this->where('scope', $scope);
    }

    // ========================================
    // SCOPES - FILTERING BY STATUS
    // ========================================

    /**
     * Get active groups
     * 
     * @return object
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /**
     * Get inactive groups
     * 
     * @return object
     */
    public function inactive()
    {
        return $this->where('is_active', 0);
    }

    /**
     * Get groups with invite link
     * 
     * @return object
     */
    public function withLink()
    {
        return $this->where('invite_link IS NOT NULL')
            ->where('invite_link !=', '');
    }

    /**
     * Get groups without invite link
     * 
     * @return object
     */
    public function withoutLink()
    {
        return $this->groupStart()
            ->where('invite_link IS NULL')
            ->orWhere('invite_link', '')
            ->groupEnd();
    }

    // ========================================
    // SCOPES - FILTERING BY LOCATION
    // ========================================

    /**
     * Get groups by region
     * 
     * @param int $regionId Region ID
     * @return object
     */
    public function byRegion(int $regionId)
    {
        return $this->where('region_id', $regionId);
    }

    /**
     * Get groups by province
     * 
     * @param int $provinceId Province ID
     * @return object
     */
    public function byProvince(int $provinceId)
    {
        return $this->where('province_id', $provinceId);
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get all active groups
     * 
     * @return array
     */
    public function getActive(): array
    {
        return $this->active()
            ->orderBy('scope', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get national group
     * 
     * @return object|null
     */
    public function getNationalGroup()
    {
        return $this->active()
            ->national()
            ->first();
    }

    /**
     * Get regional groups list
     * 
     * @return array
     */
    public function getRegionalGroups(): array
    {
        return $this->withRegion()
            ->active()
            ->regional()
            ->orderBy('regions.name', 'ASC')
            ->findAll();
    }

    /**
     * Get provincial groups list
     * 
     * @return array
     */
    public function getProvincialGroups(): array
    {
        return $this->withProvince()
            ->active()
            ->provincial()
            ->orderBy('provinces.name', 'ASC')
            ->findAll();
    }

    /**
     * Get group for user's region
     * 
     * @param int $regionId User's region ID
     * @return object|null
     */
    public function getForUserRegion(int $regionId)
    {
        return $this->active()
            ->regional()
            ->where('region_id', $regionId)
            ->first();
    }

    /**
     * Get group for user's province
     * 
     * @param int $provinceId User's province ID
     * @return object|null
     */
    public function getForUserProvince(int $provinceId)
    {
        return $this->active()
            ->provincial()
            ->where('province_id', $provinceId)
            ->first();
    }

    /**
     * Get all groups for member (national + regional/provincial)
     * 
     * @param int|null $regionId Member's region ID
     * @param int|null $provinceId Member's province ID
     * @return array
     */
    public function getForMember(?int $regionId = null, ?int $provinceId = null): array
    {
        $groups = [];

        // National group
        $national = $this->getNationalGroup();
        if ($national) {
            $groups[] = $national;
        }

        // Regional group
        if ($regionId) {
            $regional = $this->getForUserRegion($regionId);
            if ($regional) {
                $groups[] = $regional;
            }
        }

        // Provincial group
        if ($provinceId) {
            $provincial = $this->getForUserProvince($provinceId);
            if ($provincial) {
                $groups[] = $provincial;
            }
        }

        return $groups;
    }

    /**
     * Search groups
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('name', $keyword)
            ->orLike('description', $keyword)
            ->orLike('admin_name', $keyword)
            ->groupEnd();
    }

    /**
     * Get groups dropdown
     * 
     * @return array
     */
    public function getDropdown(): array
    {
        $groups = $this->active()->findAll();

        $dropdown = [];
        foreach ($groups as $group) {
            $dropdown[$group->id] = $group->name;
        }

        return $dropdown;
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count groups by scope
     * 
     * @return array
     */
    public function countByScope(): array
    {
        $result = $this->select('scope, COUNT(*) as count')
            ->where('is_active', 1)
            ->groupBy('scope')
            ->findAll();

        $stats = [
            'national' => 0,
            'regional' => 0,
            'provincial' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->scope] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Get total members across all groups
     * 
     * @return int
     */
    public function getTotalMembers(): int
    {
        $result = $this->selectSum('member_count')
            ->where('is_active', 1)
            ->first();

        return $result ? (int)$result->member_count : 0;
    }

    /**
     * Get average members per group
     * 
     * @return float
     */
    public function getAverageMembers(): float
    {
        $result = $this->selectAvg('member_count')
            ->where('is_active', 1)
            ->first();

        return $result ? (float)$result->member_count : 0.0;
    }

    /**
     * Get groups statistics
     * 
     * @return object
     */
    public function getStatistics()
    {
        $total = $this->active()->countAllResults(false);
        $withLink = $this->active()->withLink()->countAllResults(false);
        $withoutLink = $this->active()->withoutLink()->countAllResults(false);
        $totalMembers = $this->getTotalMembers();

        return (object)[
            'total_groups' => $total,
            'with_link' => $withLink,
            'without_link' => $withoutLink,
            'total_members' => $totalMembers,
            'avg_members' => $total > 0 ? round($totalMembers / $total, 2) : 0,
        ];
    }

    /**
     * Get groups nearing capacity
     * 
     * @param int $threshold Percentage threshold (default 90%)
     * @return array
     */
    public function getNearingCapacity(int $threshold = 90): array
    {
        return $this->select('wa_groups.*')
            ->select('(member_count * 100 / max_members) as capacity_percentage')
            ->where('is_active', 1)
            ->where('max_members >', 0)
            ->having('capacity_percentage >=', $threshold)
            ->orderBy('capacity_percentage', 'DESC')
            ->findAll();
    }

    /**
     * Get groups by region with member count
     * 
     * @return array
     */
    public function getRegionalDistribution(): array
    {
        return $this->select('regions.name as region_name, wa_groups.name as group_name, wa_groups.member_count')
            ->join('regions', 'regions.id = wa_groups.region_id', 'left')
            ->where('wa_groups.scope', 'regional')
            ->where('wa_groups.is_active', 1)
            ->orderBy('regions.name', 'ASC')
            ->findAll();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Activate group
     * 
     * @param int $groupId Group ID
     * @return bool
     */
    public function activate(int $groupId): bool
    {
        return $this->update($groupId, ['is_active' => 1]);
    }

    /**
     * Deactivate group
     * 
     * @param int $groupId Group ID
     * @return bool
     */
    public function deactivate(int $groupId): bool
    {
        return $this->update($groupId, ['is_active' => 0]);
    }

    /**
     * Update invite link
     * 
     * @param int $groupId Group ID
     * @param string $inviteLink New invite link
     * @return bool
     */
    public function updateInviteLink(int $groupId, string $inviteLink): bool
    {
        return $this->update($groupId, ['invite_link' => $inviteLink]);
    }

    /**
     * Update member count
     * 
     * @param int $groupId Group ID
     * @param int $memberCount New member count
     * @return bool
     */
    public function updateMemberCount(int $groupId, int $memberCount): bool
    {
        return $this->update($groupId, ['member_count' => $memberCount]);
    }

    /**
     * Increment member count
     * 
     * @param int $groupId Group ID
     * @return bool
     */
    public function incrementMemberCount(int $groupId): bool
    {
        return $this->set('member_count', 'member_count + 1', false)
            ->where('id', $groupId)
            ->update();
    }

    /**
     * Decrement member count
     * 
     * @param int $groupId Group ID
     * @return bool
     */
    public function decrementMemberCount(int $groupId): bool
    {
        return $this->set('member_count', 'GREATEST(member_count - 1, 0)', false)
            ->where('id', $groupId)
            ->update();
    }

    /**
     * Update admin info
     * 
     * @param int $groupId Group ID
     * @param string $adminName Admin name
     * @param string|null $adminPhone Admin phone
     * @return bool
     */
    public function updateAdmin(int $groupId, string $adminName, ?string $adminPhone = null): bool
    {
        $data = ['admin_name' => $adminName];

        if ($adminPhone) {
            $data['admin_phone'] = $adminPhone;
        }

        return $this->update($groupId, $data);
    }

    /**
     * Check if group is full
     * 
     * @param int $groupId Group ID
     * @return bool
     */
    public function isFull(int $groupId): bool
    {
        $group = $this->find($groupId);

        if (!$group || $group->max_members <= 0) {
            return false;
        }

        return $group->member_count >= $group->max_members;
    }

    /**
     * Get available capacity
     * 
     * @param int $groupId Group ID
     * @return int
     */
    public function getAvailableCapacity(int $groupId): int
    {
        $group = $this->find($groupId);

        if (!$group || $group->max_members <= 0) {
            return 0;
        }

        return max(0, $group->max_members - $group->member_count);
    }

    /**
     * Check if region has group
     * 
     * @param int $regionId Region ID
     * @return bool
     */
    public function regionHasGroup(int $regionId): bool
    {
        return $this->regional()
            ->where('region_id', $regionId)
            ->countAllResults() > 0;
    }

    /**
     * Check if province has group
     * 
     * @param int $provinceId Province ID
     * @return bool
     */
    public function provinceHasGroup(int $provinceId): bool
    {
        return $this->provincial()
            ->where('province_id', $provinceId)
            ->countAllResults() > 0;
    }

    /**
     * Create regional groups for all regions without groups
     * 
     * @param int $createdBy User ID
     * @return int Number of groups created
     */
    public function createMissingRegionalGroups(int $createdBy): int
    {
        $regions = $this->db->table('regions')
            ->whereNotIn('id', function ($builder) {
                return $builder->select('region_id')
                    ->from('wa_groups')
                    ->where('scope', 'regional')
                    ->where('region_id IS NOT NULL');
            })
            ->get()
            ->getResult();

        $count = 0;
        foreach ($regions as $region) {
            $data = [
                'name' => 'SPK ' . $region->name,
                'description' => 'Grup WhatsApp SPK Regional ' . $region->name,
                'scope' => 'regional',
                'region_id' => $region->id,
                'created_by' => $createdBy,
                'is_active' => 1,
            ];

            if ($this->insert($data)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Sync member count with actual members
     * For future use when member tracking is implemented
     * 
     * @param int $groupId Group ID
     * @return bool
     */
    public function syncMemberCount(int $groupId): bool
    {
        // This is placeholder for future member tracking feature
        // When wa_group_members table is implemented, this will count actual members

        // For now, just return true
        return true;
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set default values before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultValues(array $data): array
    {
        if (!isset($data['data']['is_active'])) {
            $data['data']['is_active'] = 1;
        }

        if (!isset($data['data']['member_count'])) {
            $data['data']['member_count'] = 0;
        }

        if (!isset($data['data']['max_members'])) {
            $data['data']['max_members'] = 512; // WhatsApp group limit
        }

        return $data;
    }
}
