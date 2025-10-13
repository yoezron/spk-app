<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DistrictModel
 * 
 * Model untuk mengelola data kecamatan
 * Digunakan untuk master data kecamatan dan relasi dengan kabupaten/kota
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class DistrictModel extends Model
{
    protected $table            = 'districts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'regency_id',
        'code',
        'name',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'regency_id' => 'required|integer|is_not_unique[regencies.id]',
        'name'       => 'required|min_length[3]|max_length[100]',
        'code'       => 'permit_empty|max_length[10]|is_unique[districts.code,id,{id}]',
        'is_active'  => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'regency_id' => [
            'required'      => 'Kabupaten/Kota harus dipilih',
            'integer'       => 'ID Kabupaten/Kota tidak valid',
            'is_not_unique' => 'Kabupaten/Kota tidak ditemukan',
        ],
        'name' => [
            'required'   => 'Nama kecamatan harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'code' => [
            'is_unique' => 'Kode kecamatan sudah digunakan',
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
     * Get district with regency data
     * 
     * @return object
     */
    public function withRegency()
    {
        return $this->select('districts.*, regencies.name as regency_name, regencies.type as regency_type')
            ->join('regencies', 'regencies.id = districts.regency_id', 'left');
    }

    /**
     * Get district with province data
     * 
     * @return object
     */
    public function withProvince()
    {
        return $this->select('districts.*, provinces.name as province_name')
            ->join('regencies', 'regencies.id = districts.regency_id', 'left')
            ->join('provinces', 'provinces.id = regencies.province_id', 'left');
    }

    /**
     * Get district with complete location data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('districts.*, regencies.name as regency_name, regencies.type as regency_type, provinces.name as province_name')
            ->join('regencies', 'regencies.id = districts.regency_id', 'left')
            ->join('provinces', 'provinces.id = regencies.province_id', 'left');
    }

    /**
     * Get district with villages count
     * 
     * @return object
     */
    public function withVillagesCount()
    {
        return $this->select('districts.*')
            ->select('(SELECT COUNT(*) FROM villages WHERE villages.district_id = districts.id) as villages_count');
    }

    /**
     * Get district with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('districts.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.district_id = districts.id) as members_count');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active districts
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get districts by regency ID
     * 
     * @param int $regencyId
     * @return array
     */
    public function getByRegency($regencyId)
    {
        return $this->where('regency_id', $regencyId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get districts by province ID
     * 
     * @param int $provinceId
     * @return array
     */
    public function getByProvince($provinceId)
    {
        return $this->select('districts.*')
            ->join('regencies', 'regencies.id = districts.regency_id')
            ->where('regencies.province_id', $provinceId)
            ->where('districts.is_active', 1)
            ->orderBy('districts.name', 'ASC')
            ->findAll();
    }

    /**
     * Search districts by name
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        return $this->like('name', $keyword)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get district dropdown options
     * 
     * @param int|null $regencyId
     * @return array
     */
    public function getDropdown($regencyId = null)
    {
        $builder = $this->where('is_active', 1);

        if ($regencyId) {
            $builder->where('regency_id', $regencyId);
        }

        $districts = $builder->orderBy('name', 'ASC')->findAll();

        $options = [];
        foreach ($districts as $district) {
            $options[$district->id] = $district->name;
        }

        return $options;
    }

    /**
     * Check if district exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get district by code
     * 
     * @param string $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Bulk insert districts
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
     * Activate district
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate district
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
     * Get total districts count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active districts count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get districts count by regency
     * 
     * @param int $regencyId
     * @return int
     */
    public function getCountByRegency($regencyId)
    {
        return $this->where('regency_id', $regencyId)->countAllResults();
    }
}
