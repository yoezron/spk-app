<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ProvinceModel
 * 
 * Model untuk mengelola data provinsi Indonesia
 * Digunakan untuk master data wilayah dan relasi dengan anggota
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ProvinceModel extends Model
{
    protected $table            = 'provinces';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'latitude',
        'longitude'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[provinces.name,id,{id}]',
        'code' => 'permit_empty|max_length[10]|is_unique[provinces.code,id,{id}]',
        'latitude'  => 'permit_empty|decimal',
        'longitude' => 'permit_empty|decimal',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama provinsi harus diisi',
            'min_length' => 'Nama provinsi minimal 3 karakter',
            'max_length' => 'Nama provinsi maksimal 100 karakter',
            'is_unique'  => 'Nama provinsi sudah ada',
        ],
        'code' => [
            'is_unique' => 'Kode provinsi sudah digunakan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateCode'];
    protected $beforeUpdate   = ['generateCode'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get province with regencies/cities
     * 
     * @return object
     */
    public function withRegencies()
    {
        return $this->select('provinces.*')
            ->select('(SELECT COUNT(*) FROM regencies WHERE regencies.province_id = provinces.id) as regencies_count');
    }

    /**
     * Get province with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('provinces.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.province_id = provinces.id) as members_count');
    }

    /**
     * Get province with complete statistics
     * 
     * @return object
     */
    public function withStats()
    {
        return $this->select('provinces.*')
            ->select('(SELECT COUNT(*) FROM regencies WHERE regencies.province_id = provinces.id) as regencies_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.province_id = provinces.id) as members_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.province_id = provinces.id AND member_profiles.membership_status = "active") as active_members_count');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get provinces that have members
     * 
     * @return object
     */
    public function hasMembers()
    {
        return $this->select('provinces.*')
            ->join('member_profiles', 'member_profiles.province_id = provinces.id', 'inner')
            ->groupBy('provinces.id');
    }

    /**
     * Search provinces by name
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('code', $keyword);
    }

    /**
     * Order by name ascending
     * 
     * @return object
     */
    public function ordered()
    {
        return $this->orderBy('name', 'ASC');
    }

    // ========================================
    // CUSTOM METHODS
    // ========================================

    /**
     * Get all provinces ordered by name
     * 
     * @return array
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get province by name
     * 
     * @param string $name Province name
     * @return object|null
     */
    public function getByName(string $name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Get province by code
     * 
     * @param string $code Province code
     * @return object|null
     */
    public function getByCode(string $code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get provinces with statistics (members count, regencies count)
     * 
     * @return array
     */
    public function getAllWithStats(): array
    {
        return $this->withStats()
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get top provinces by members count
     * 
     * @param int $limit Number of results
     * @return array
     */
    public function getTopByMembers(int $limit = 10): array
    {
        return $this->withMembersCount()
            ->orderBy('members_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get provinces for dropdown
     * Format: ['id' => 'name']
     * 
     * @return array
     */
    public function getDropdown(): array
    {
        $provinces = $this->orderBy('name', 'ASC')->findAll();

        $dropdown = [];
        foreach ($provinces as $province) {
            $dropdown[$province->id] = $province->name;
        }

        return $dropdown;
    }

    /**
     * Get regencies by province ID
     * 
     * @param int $provinceId Province ID
     * @return array
     */
    public function getRegencies(int $provinceId): array
    {
        return $this->db->table('regencies')
            ->where('province_id', $provinceId)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get members by province ID
     * 
     * @param int $provinceId Province ID
     * @param int $limit Number of results
     * @return array
     */
    public function getMembers(int $provinceId, int $limit = 100): array
    {
        return $this->db->table('member_profiles')
            ->select('member_profiles.*, users.username, users.active')
            ->join('users', 'users.id = member_profiles.user_id', 'left')
            ->where('member_profiles.province_id', $provinceId)
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get province statistics
     * 
     * @param int $provinceId Province ID
     * @return array
     */
    public function getStats(int $provinceId): array
    {
        $province = $this->withStats()->find($provinceId);

        if (!$province) {
            return [];
        }

        return [
            'province_name'        => $province->name,
            'province_code'        => $province->code,
            'regencies_count'      => $province->regencies_count ?? 0,
            'members_count'        => $province->members_count ?? 0,
            'active_members_count' => $province->active_members_count ?? 0,
        ];
    }

    /**
     * Check if province has members
     * 
     * @param int $provinceId Province ID
     * @return bool
     */
    public function hasMembersData(int $provinceId): bool
    {
        $count = $this->db->table('member_profiles')
            ->where('province_id', $provinceId)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Bulk insert provinces
     * 
     * @param array $provinces Array of provinces data
     * @return bool
     */
    public function bulkInsert(array $provinces): bool
    {
        if (empty($provinces)) {
            return false;
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        foreach ($provinces as &$province) {
            $province['created_at'] = $now;
            $province['updated_at'] = $now;
        }

        return $this->insertBatch($provinces);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate province code if not provided
     * 
     * @param array $data
     * @return array
     */
    protected function generateCode(array $data): array
    {
        // Only generate if code is empty
        if (empty($data['data']['code']) && !empty($data['data']['name'])) {
            // Generate code from first 3 letters of name + random
            $name = strtoupper($data['data']['name']);
            $code = substr($name, 0, 3) . rand(10, 99);
            $data['data']['code'] = $code;
        }

        return $data;
    }

    // ========================================
    // EXPORT & IMPORT
    // ========================================

    /**
     * Export provinces to array for Excel/CSV
     * 
     * @return array
     */
    public function exportData(): array
    {
        $provinces = $this->withStats()->orderBy('name', 'ASC')->findAll();

        $export = [];
        foreach ($provinces as $province) {
            $export[] = [
                'ID'               => $province->id,
                'Nama Provinsi'    => $province->name,
                'Kode'             => $province->code,
                'Jumlah Kabupaten' => $province->regencies_count ?? 0,
                'Jumlah Anggota'   => $province->members_count ?? 0,
                'Anggota Aktif'    => $province->active_members_count ?? 0,
                'Latitude'         => $province->latitude,
                'Longitude'        => $province->longitude,
            ];
        }

        return $export;
    }
}
