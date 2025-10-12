<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RegencyModel
 * 
 * Model untuk mengelola data kabupaten/kota
 * Digunakan untuk master data wilayah tingkat 2 dan relasi dengan anggota
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegencyModel extends Model
{
    protected $table            = 'regencies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'province_id',
        'name',
        'type',
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
        'province_id' => 'required|integer|is_not_unique[provinces.id]',
        'name'        => 'required|min_length[3]|max_length[100]',
        'type'        => 'required|in_list[Kabupaten,Kota]',
        'code'        => 'permit_empty|max_length[10]|is_unique[regencies.code,id,{id}]',
        'latitude'    => 'permit_empty|decimal',
        'longitude'   => 'permit_empty|decimal',
    ];

    protected $validationMessages = [
        'province_id' => [
            'required'      => 'Provinsi harus dipilih',
            'integer'       => 'ID Provinsi tidak valid',
            'is_not_unique' => 'Provinsi tidak ditemukan',
        ],
        'name' => [
            'required'   => 'Nama kabupaten/kota harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'type' => [
            'required' => 'Tipe harus dipilih',
            'in_list'  => 'Tipe harus Kabupaten atau Kota',
        ],
        'code' => [
            'is_unique' => 'Kode kabupaten/kota sudah digunakan',
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
     * Get regency with province data
     * 
     * @return object
     */
    public function withProvince()
    {
        return $this->select('regencies.*, provinces.name as province_name, provinces.code as province_code')
            ->join('provinces', 'provinces.id = regencies.province_id', 'left');
    }

    /**
     * Get regency with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('regencies.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.regency_id = regencies.id) as members_count');
    }

    /**
     * Get regency with complete statistics
     * 
     * @return object
     */
    public function withStats()
    {
        return $this->select('regencies.*')
            ->select('provinces.name as province_name')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.regency_id = regencies.id) as members_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.regency_id = regencies.id AND member_profiles.membership_status = "active") as active_members_count')
            ->join('provinces', 'provinces.id = regencies.province_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get regencies by province
     * 
     * @param int $provinceId Province ID
     * @return object
     */
    public function byProvince(int $provinceId)
    {
        return $this->where('province_id', $provinceId);
    }

    /**
     * Get by type (Kabupaten or Kota)
     * 
     * @param string $type Type: 'Kabupaten' or 'Kota'
     * @return object
     */
    public function byType(string $type)
    {
        return $this->where('type', $type);
    }

    /**
     * Get only Kabupaten
     * 
     * @return object
     */
    public function kabupaten()
    {
        return $this->where('type', 'Kabupaten');
    }

    /**
     * Get only Kota
     * 
     * @return object
     */
    public function kota()
    {
        return $this->where('type', 'Kota');
    }

    /**
     * Get regencies that have members
     * 
     * @return object
     */
    public function hasMembers()
    {
        return $this->select('regencies.*')
            ->join('member_profiles', 'member_profiles.regency_id = regencies.id', 'inner')
            ->groupBy('regencies.id');
    }

    /**
     * Search regencies by name
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('name', $keyword)
            ->orLike('code', $keyword)
            ->groupEnd();
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
     * Get all regencies ordered by name
     * 
     * @return array
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get regencies by province ID
     * 
     * @param int $provinceId Province ID
     * @return array
     */
    public function getByProvinceId(int $provinceId): array
    {
        return $this->where('province_id', $provinceId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get regency by name and province
     * 
     * @param string $name Regency name
     * @param int $provinceId Province ID
     * @return object|null
     */
    public function getByNameAndProvince(string $name, int $provinceId)
    {
        return $this->where('name', $name)
            ->where('province_id', $provinceId)
            ->first();
    }

    /**
     * Get regency by code
     * 
     * @param string $code Regency code
     * @return object|null
     */
    public function getByCode(string $code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get regencies with statistics
     * 
     * @param int|null $provinceId Filter by province (optional)
     * @return array
     */
    public function getAllWithStats(?int $provinceId = null): array
    {
        $builder = $this->withStats();

        if ($provinceId) {
            $builder->where('regencies.province_id', $provinceId);
        }

        return $builder->orderBy('regencies.name', 'ASC')->findAll();
    }

    /**
     * Get top regencies by members count
     * 
     * @param int $limit Number of results
     * @param int|null $provinceId Filter by province (optional)
     * @return array
     */
    public function getTopByMembers(int $limit = 10, ?int $provinceId = null): array
    {
        $builder = $this->withMembersCount()->withProvince();

        if ($provinceId) {
            $builder->where('regencies.province_id', $provinceId);
        }

        return $builder->orderBy('members_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get regencies for dropdown
     * Format: ['id' => 'name (Type)']
     * 
     * @param int|null $provinceId Filter by province (optional)
     * @return array
     */
    public function getDropdown(?int $provinceId = null): array
    {
        $builder = $this->orderBy('name', 'ASC');

        if ($provinceId) {
            $builder->where('province_id', $provinceId);
        }

        $regencies = $builder->findAll();

        $dropdown = [];
        foreach ($regencies as $regency) {
            $dropdown[$regency->id] = $regency->name . ' (' . $regency->type . ')';
        }

        return $dropdown;
    }

    /**
     * Get regencies grouped by province
     * Format: ['province_name' => [regencies]]
     * 
     * @return array
     */
    public function getGroupedByProvince(): array
    {
        $regencies = $this->withProvince()
            ->orderBy('provinces.name', 'ASC')
            ->orderBy('regencies.name', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($regencies as $regency) {
            $provinceName = $regency->province_name ?? 'Unknown';

            if (!isset($grouped[$provinceName])) {
                $grouped[$provinceName] = [];
            }

            $grouped[$provinceName][] = $regency;
        }

        return $grouped;
    }

    /**
     * Get members by regency ID
     * 
     * @param int $regencyId Regency ID
     * @param int $limit Number of results
     * @return array
     */
    public function getMembers(int $regencyId, int $limit = 100): array
    {
        return $this->db->table('member_profiles')
            ->select('member_profiles.*, users.username, users.active')
            ->join('users', 'users.id = member_profiles.user_id', 'left')
            ->where('member_profiles.regency_id', $regencyId)
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get regency statistics
     * 
     * @param int $regencyId Regency ID
     * @return array
     */
    public function getStats(int $regencyId): array
    {
        $regency = $this->withStats()->find($regencyId);

        if (!$regency) {
            return [];
        }

        return [
            'regency_name'         => $regency->name,
            'regency_type'         => $regency->type,
            'regency_code'         => $regency->code,
            'province_name'        => $regency->province_name,
            'members_count'        => $regency->members_count ?? 0,
            'active_members_count' => $regency->active_members_count ?? 0,
        ];
    }

    /**
     * Check if regency has members
     * 
     * @param int $regencyId Regency ID
     * @return bool
     */
    public function hasMembersData(int $regencyId): bool
    {
        $count = $this->db->table('member_profiles')
            ->where('regency_id', $regencyId)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Get count by type
     * 
     * @param int|null $provinceId Filter by province (optional)
     * @return array ['Kabupaten' => count, 'Kota' => count]
     */
    public function getCountByType(?int $provinceId = null): array
    {
        $builder = $this->select('type, COUNT(*) as count')
            ->groupBy('type');

        if ($provinceId) {
            $builder->where('province_id', $provinceId);
        }

        $results = $builder->findAll();

        $counts = ['Kabupaten' => 0, 'Kota' => 0];
        foreach ($results as $result) {
            $counts[$result->type] = (int)$result->count;
        }

        return $counts;
    }

    /**
     * Bulk insert regencies
     * 
     * @param array $regencies Array of regencies data
     * @return bool
     */
    public function bulkInsert(array $regencies): bool
    {
        if (empty($regencies)) {
            return false;
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        foreach ($regencies as &$regency) {
            $regency['created_at'] = $now;
            $regency['updated_at'] = $now;
        }

        return $this->insertBatch($regencies);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate regency code if not provided
     * 
     * @param array $data
     * @return array
     */
    protected function generateCode(array $data): array
    {
        // Only generate if code is empty
        if (empty($data['data']['code']) && !empty($data['data']['name']) && !empty($data['data']['province_id'])) {
            // Get province code
            $province = $this->db->table('provinces')
                ->select('code')
                ->where('id', $data['data']['province_id'])
                ->get()
                ->getRow();

            if ($province) {
                // Generate code: PROVINCE_CODE + first 3 letters + random
                $name = strtoupper($data['data']['name']);
                $code = $province->code . '-' . substr($name, 0, 3) . rand(10, 99);
                $data['data']['code'] = $code;
            }
        }

        return $data;
    }

    // ========================================
    // EXPORT & IMPORT
    // ========================================

    /**
     * Export regencies to array for Excel/CSV
     * 
     * @param int|null $provinceId Filter by province (optional)
     * @return array
     */
    public function exportData(?int $provinceId = null): array
    {
        $regencies = $this->getAllWithStats($provinceId);

        $export = [];
        foreach ($regencies as $regency) {
            $export[] = [
                'ID'             => $regency->id,
                'Provinsi'       => $regency->province_name,
                'Nama'           => $regency->name,
                'Tipe'           => $regency->type,
                'Kode'           => $regency->code,
                'Jumlah Anggota' => $regency->members_count ?? 0,
                'Anggota Aktif'  => $regency->active_members_count ?? 0,
                'Latitude'       => $regency->latitude,
                'Longitude'      => $regency->longitude,
            ];
        }

        return $export;
    }
}
