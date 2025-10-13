<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * VillageModel
 * 
 * Model untuk mengelola data desa/kelurahan
 * Digunakan untuk master data desa dan relasi dengan kecamatan
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class VillageModel extends Model
{
    protected $table            = 'villages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'district_id',
        'code',
        'name',
        'postal_code',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'district_id' => 'required|integer|is_not_unique[districts.id]',
        'name'        => 'required|min_length[3]|max_length[100]',
        'code'        => 'permit_empty|max_length[10]|is_unique[villages.code,id,{id}]',
        'postal_code' => 'permit_empty|max_length[10]',
        'is_active'   => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'district_id' => [
            'required'      => 'Kecamatan harus dipilih',
            'integer'       => 'ID Kecamatan tidak valid',
            'is_not_unique' => 'Kecamatan tidak ditemukan',
        ],
        'name' => [
            'required'   => 'Nama desa/kelurahan harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'code' => [
            'is_unique' => 'Kode desa/kelurahan sudah digunakan',
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
     * Get village with district data
     * 
     * @return object
     */
    public function withDistrict()
    {
        return $this->select('villages.*, districts.name as district_name')
            ->join('districts', 'districts.id = villages.district_id', 'left');
    }

    /**
     * Get village with regency data
     * 
     * @return object
     */
    public function withRegency()
    {
        return $this->select('villages.*, regencies.name as regency_name, regencies.type as regency_type')
            ->join('districts', 'districts.id = villages.district_id', 'left')
            ->join('regencies', 'regencies.id = districts.regency_id', 'left');
    }

    /**
     * Get village with province data
     * 
     * @return object
     */
    public function withProvince()
    {
        return $this->select('villages.*, provinces.name as province_name')
            ->join('districts', 'districts.id = villages.district_id', 'left')
            ->join('regencies', 'regencies.id = districts.regency_id', 'left')
            ->join('provinces', 'provinces.id = regencies.province_id', 'left');
    }

    /**
     * Get village with complete location data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('villages.*, districts.name as district_name, regencies.name as regency_name, regencies.type as regency_type, provinces.name as province_name')
            ->join('districts', 'districts.id = villages.district_id', 'left')
            ->join('regencies', 'regencies.id = districts.regency_id', 'left')
            ->join('provinces', 'provinces.id = regencies.province_id', 'left');
    }

    /**
     * Get village with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('villages.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.village_id = villages.id) as members_count');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active villages
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get villages by district ID
     * 
     * @param int $districtId
     * @return array
     */
    public function getByDistrict($districtId)
    {
        return $this->where('district_id', $districtId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get villages by regency ID
     * 
     * @param int $regencyId
     * @return array
     */
    public function getByRegency($regencyId)
    {
        return $this->select('villages.*')
            ->join('districts', 'districts.id = villages.district_id')
            ->where('districts.regency_id', $regencyId)
            ->where('villages.is_active', 1)
            ->orderBy('villages.name', 'ASC')
            ->findAll();
    }

    /**
     * Get villages by province ID
     * 
     * @param int $provinceId
     * @return array
     */
    public function getByProvince($provinceId)
    {
        return $this->select('villages.*')
            ->join('districts', 'districts.id = villages.district_id')
            ->join('regencies', 'regencies.id = districts.regency_id')
            ->where('regencies.province_id', $provinceId)
            ->where('villages.is_active', 1)
            ->orderBy('villages.name', 'ASC')
            ->findAll();
    }

    /**
     * Search villages by name
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
     * Get village dropdown options
     * 
     * @param int|null $districtId
     * @return array
     */
    public function getDropdown($districtId = null)
    {
        $builder = $this->where('is_active', 1);

        if ($districtId) {
            $builder->where('district_id', $districtId);
        }

        $villages = $builder->orderBy('name', 'ASC')->findAll();

        $options = [];
        foreach ($villages as $village) {
            $options[$village->id] = $village->name;
        }

        return $options;
    }

    /**
     * Check if village exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get village by code
     * 
     * @param string $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get villages by postal code
     * 
     * @param string $postalCode
     * @return array
     */
    public function getByPostalCode($postalCode)
    {
        return $this->where('postal_code', $postalCode)
            ->where('is_active', 1)
            ->findAll();
    }

    /**
     * Bulk insert villages
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
     * Activate village
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate village
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
     * Get total villages count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active villages count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get villages count by district
     * 
     * @param int $districtId
     * @return int
     */
    public function getCountByDistrict($districtId)
    {
        return $this->where('district_id', $districtId)->countAllResults();
    }
}
