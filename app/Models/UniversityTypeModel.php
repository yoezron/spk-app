<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UniversityTypeModel
 * 
 * Model untuk mengelola data jenis perguruan tinggi
 * Digunakan untuk master data tipe PT (Universitas, Institut, Politeknik, dll)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class UniversityTypeModel extends Model
{
    protected $table            = 'university_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'category',
        'description',
        'display_order',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'          => 'required|min_length[3]|max_length[100]',
        'code'          => 'permit_empty|max_length[20]|is_unique[university_types.code,id,{id}]',
        'category'      => 'permit_empty|in_list[Akademik,Vokasi,Profesi,Keagamaan,Kedinasan]',
        'description'   => 'permit_empty|max_length[500]',
        'display_order' => 'permit_empty|integer',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama jenis PT harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'code' => [
            'is_unique' => 'Kode jenis PT sudah digunakan',
        ],
        'category' => [
            'in_list' => 'Kategori tidak valid',
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
     * Get university type with universities count
     * 
     * @return object
     */
    public function withUniversitiesCount()
    {
        return $this->select('university_types.*')
            ->select('(SELECT COUNT(*) FROM universities WHERE universities.university_type_id = university_types.id) as universities_count');
    }

    /**
     * Get university type with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('university_types.*')
            ->select('(SELECT COUNT(*) FROM member_profiles mp JOIN universities u ON mp.university_id = u.id WHERE u.university_type_id = university_types.id) as members_count');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active university types
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all university types ordered
     * 
     * @return array
     */
    public function getAllOrdered()
    {
        return $this->orderBy('display_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get university types by category
     * 
     * @param string $category
     * @return array
     */
    public function getByCategory($category)
    {
        return $this->where('category', $category)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Search university type by name
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('description', $keyword)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get university type dropdown options
     * 
     * @return array
     */
    public function getDropdown()
    {
        $types = $this->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $options = [];
        foreach ($types as $type) {
            $options[$type->id] = $type->name;
        }

        return $options;
    }

    /**
     * Get university type dropdown grouped by category
     * 
     * @return array
     */
    public function getDropdownGrouped()
    {
        $types = $this->where('is_active', 1)
            ->orderBy('category', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($types as $type) {
            $category = $type->category ?: 'Lainnya';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$type->id] = $type->name;
        }

        return $grouped;
    }

    /**
     * Check if university type exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get university type by code
     * 
     * @param string $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Bulk insert university types
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
     * Activate university type
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate university type
     * 
     * @param int $id
     * @return bool
     */
    public function deactivate($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Update display order
     * 
     * @param int $id
     * @param int $order
     * @return bool
     */
    public function updateOrder($id, $order)
    {
        return $this->update($id, ['display_order' => $order]);
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total university types count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active university types count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get universities distribution by type
     * 
     * @return array
     */
    public function getUniversitiesDistribution()
    {
        return $this->select('university_types.name, university_types.category, COUNT(universities.id) as total')
            ->join('universities', 'universities.university_type_id = university_types.id', 'left')
            ->where('university_types.is_active', 1)
            ->groupBy('university_types.id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get most used university type
     * 
     * @return object|null
     */
    public function getMostUsed()
    {
        return $this->select('university_types.*, COUNT(universities.id) as universities_count')
            ->join('universities', 'universities.university_type_id = university_types.id', 'left')
            ->where('university_types.is_active', 1)
            ->groupBy('university_types.id')
            ->orderBy('universities_count', 'DESC')
            ->first();
    }

    /**
     * Get distribution by category
     * 
     * @return array
     */
    public function getCategoryDistribution()
    {
        return $this->select('category, COUNT(*) as total')
            ->where('is_active', 1)
            ->groupBy('category')
            ->orderBy('total', 'DESC')
            ->findAll();
    }
}
