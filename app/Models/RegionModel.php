<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RegionModel
 * 
 * Model untuk mengelola data wilayah SPK
 * Digunakan untuk pembagian wilayah koordinator (region-based organization)
 * Berbeda dengan provinces - ini adalah wilayah custom SPK
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegionModel extends Model
{
    protected $table            = 'regions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'description',
        'coordinator_user_id',
        'province_ids',
        'color_code',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'                => 'required|min_length[3]|max_length[100]',
        'code'                => 'permit_empty|max_length[50]|is_unique[regions.code,id,{id}]',
        'description'         => 'permit_empty|max_length[500]',
        'coordinator_user_id' => 'permit_empty|integer|is_not_unique[users.id]',
        'color_code'          => 'permit_empty|max_length[7]',
        'is_active'           => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama wilayah harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'code' => [
            'is_unique' => 'Kode wilayah sudah digunakan',
        ],
        'coordinator_user_id' => [
            'is_not_unique' => 'Koordinator tidak ditemukan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $beforeUpdate   = [];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get region with coordinator data
     * 
     * @return object
     */
    public function withCoordinator()
    {
        return $this->select('regions.*, users.username as coordinator_username')
            ->select('member_profiles.full_name as coordinator_name, member_profiles.phone as coordinator_phone')
            ->join('users', 'users.id = regions.coordinator_user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');
    }

    /**
     * Get region with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('regions.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.region_id = regions.id) as members_count');
    }

    /**
     * Get region with complete statistics
     * 
     * @return object
     */
    public function withStatistics()
    {
        return $this->select('regions.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.region_id = regions.id) as members_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.region_id = regions.id AND member_profiles.membership_status = "active") as active_members')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.region_id = regions.id AND member_profiles.membership_status = "pending") as pending_members');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active regions
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all regions ordered
     * 
     * @return array
     */
    public function getAllOrdered()
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Search region by name
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('description', $keyword)
            ->orLike('code', $keyword)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get region dropdown options
     * 
     * @return array
     */
    public function getDropdown()
    {
        $regions = $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $options = [];
        foreach ($regions as $region) {
            $options[$region->id] = $region->name;
        }

        return $options;
    }

    /**
     * Check if region exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get region by code
     * 
     * @param string $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get regions by coordinator
     * 
     * @param int $userId
     * @return array
     */
    public function getByCoordinator($userId)
    {
        return $this->where('coordinator_user_id', $userId)
            ->where('is_active', 1)
            ->findAll();
    }

    /**
     * Assign coordinator to region
     * 
     * @param int $regionId
     * @param int $userId
     * @return bool
     */
    public function assignCoordinator($regionId, $userId)
    {
        return $this->update($regionId, ['coordinator_user_id' => $userId]);
    }

    /**
     * Remove coordinator from region
     * 
     * @param int $regionId
     * @return bool
     */
    public function removeCoordinator($regionId)
    {
        return $this->update($regionId, ['coordinator_user_id' => null]);
    }

    /**
     * Get provinces in this region
     * Parse the province_ids JSON field
     * 
     * @param int $regionId
     * @return array
     */
    public function getProvinces($regionId)
    {
        $region = $this->find($regionId);

        if (!$region || !$region->province_ids) {
            return [];
        }

        $provinceIds = json_decode($region->province_ids, true);

        if (empty($provinceIds)) {
            return [];
        }

        return $this->db->table('provinces')
            ->whereIn('id', $provinceIds)
            ->get()
            ->getResult();
    }

    /**
     * Bulk insert regions
     * Used for seeding or mass import
     * 
     * @param array $data
     * @return bool
     */
    public function bulkInsert(array $data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Activate region
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate region
     * 
     * @param int $id
     * @return bool
     */
    public function deactivate($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total regions count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active regions count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get regions without coordinator
     * 
     * @return array
     */
    public function getWithoutCoordinator()
    {
        return $this->where('coordinator_user_id', null)
            ->where('is_active', 1)
            ->findAll();
    }

    /**
     * Get regions with members distribution
     * 
     * @return array
     */
    public function getMembersDistribution()
    {
        return $this->select('regions.name, COUNT(member_profiles.id) as total')
            ->join('member_profiles', 'member_profiles.region_id = regions.id', 'left')
            ->where('regions.is_active', 1)
            ->groupBy('regions.id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get region with most members
     * 
     * @return object|null
     */
    public function getLargestRegion()
    {
        return $this->select('regions.*, COUNT(member_profiles.id) as members_count')
            ->join('member_profiles', 'member_profiles.region_id = regions.id', 'left')
            ->where('regions.is_active', 1)
            ->groupBy('regions.id')
            ->orderBy('members_count', 'DESC')
            ->first();
    }
}
