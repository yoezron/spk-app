<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * EmploymentStatusModel
 * 
 * Model untuk mengelola data status kepegawaian
 * Digunakan untuk master data status kepegawaian anggota (ASN, Non-ASN, Kontrak, dll)
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class EmploymentStatusModel extends Model
{
    protected $table            = 'employment_statuses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'description',
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
        'code'          => 'permit_empty|max_length[50]|is_unique[employment_statuses.code,id,{id}]',
        'description'   => 'permit_empty|max_length[500]',
        'display_order' => 'permit_empty|integer',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama status kepegawaian harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'code' => [
            'is_unique' => 'Kode status kepegawaian sudah digunakan',
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
     * Get employment status with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('employment_statuses.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.employment_status_id = employment_statuses.id) as members_count');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active employment statuses
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
     * Get all employment statuses ordered
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
     * Search employment status by name
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
     * Get employment status dropdown options
     * 
     * @return array
     */
    public function getDropdown()
    {
        $statuses = $this->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $options = [];
        foreach ($statuses as $status) {
            $options[$status->id] = $status->name;
        }

        return $options;
    }

    /**
     * Check if employment status exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get employment status by code
     * 
     * @param string $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Bulk insert employment statuses
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
     * Activate employment status
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate employment status
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
     * Get total employment statuses count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active employment statuses count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get members distribution by employment status
     * 
     * @return array
     */
    public function getMembersDistribution()
    {
        return $this->select('employment_statuses.name, COUNT(member_profiles.id) as total')
            ->join('member_profiles', 'member_profiles.employment_status_id = employment_statuses.id', 'left')
            ->where('employment_statuses.is_active', 1)
            ->groupBy('employment_statuses.id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get most used employment status
     * 
     * @return object|null
     */
    public function getMostUsed()
    {
        return $this->select('employment_statuses.*, COUNT(member_profiles.id) as members_count')
            ->join('member_profiles', 'member_profiles.employment_status_id = employment_statuses.id', 'left')
            ->where('employment_statuses.is_active', 1)
            ->groupBy('employment_statuses.id')
            ->orderBy('members_count', 'DESC')
            ->first();
    }
}
