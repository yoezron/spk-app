<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SalaryRangeModel
 * 
 * Model untuk mengelola data range gaji
 * Digunakan untuk master data rentang gaji anggota
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SalaryRangeModel extends Model
{
    protected $table            = 'salary_ranges';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'min_salary',
        'max_salary',
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
        'min_salary'    => 'permit_empty|integer',
        'max_salary'    => 'permit_empty|integer|greater_than_equal_to[min_salary]',
        'description'   => 'permit_empty|max_length[500]',
        'display_order' => 'permit_empty|integer',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama range gaji harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'max_salary' => [
            'greater_than_equal_to' => 'Gaji maksimal harus lebih besar atau sama dengan gaji minimal',
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
     * Get salary range with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('salary_ranges.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.salary_range_id = salary_ranges.id) as members_count');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active salary ranges
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->orderBy('min_salary', 'ASC')
            ->findAll();
    }

    /**
     * Get all salary ranges ordered
     * 
     * @return array
     */
    public function getAllOrdered()
    {
        return $this->orderBy('display_order', 'ASC')
            ->orderBy('min_salary', 'ASC')
            ->findAll();
    }

    /**
     * Search salary range by name
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('description', $keyword)
            ->where('is_active', 1)
            ->orderBy('min_salary', 'ASC')
            ->findAll();
    }

    /**
     * Get salary range dropdown options
     * 
     * @return array
     */
    public function getDropdown()
    {
        $ranges = $this->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->orderBy('min_salary', 'ASC')
            ->findAll();

        $options = [];
        foreach ($ranges as $range) {
            $options[$range->id] = $range->name;
        }

        return $options;
    }

    /**
     * Check if salary range exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get salary range by exact match
     * 
     * @param int $minSalary
     * @param int $maxSalary
     * @return object|null
     */
    public function getByRange($minSalary, $maxSalary)
    {
        return $this->where('min_salary', $minSalary)
            ->where('max_salary', $maxSalary)
            ->first();
    }

    /**
     * Find salary range for a specific salary amount
     * 
     * @param int $salary
     * @return object|null
     */
    public function findForSalary($salary)
    {
        return $this->where('min_salary <=', $salary)
            ->where('max_salary >=', $salary)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Bulk insert salary ranges
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
     * Activate salary range
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate salary range
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
     * Get total salary ranges count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active salary ranges count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get members distribution by salary range
     * 
     * @return array
     */
    public function getMembersDistribution()
    {
        return $this->select('salary_ranges.name, salary_ranges.min_salary, salary_ranges.max_salary, COUNT(member_profiles.id) as total')
            ->join('member_profiles', 'member_profiles.salary_range_id = salary_ranges.id', 'left')
            ->where('salary_ranges.is_active', 1)
            ->groupBy('salary_ranges.id')
            ->orderBy('salary_ranges.min_salary', 'ASC')
            ->findAll();
    }

    /**
     * Get most used salary range
     * 
     * @return object|null
     */
    public function getMostUsed()
    {
        return $this->select('salary_ranges.*, COUNT(member_profiles.id) as members_count')
            ->join('member_profiles', 'member_profiles.salary_range_id = salary_ranges.id', 'left')
            ->where('salary_ranges.is_active', 1)
            ->groupBy('salary_ranges.id')
            ->orderBy('members_count', 'DESC')
            ->first();
    }

    /**
     * Get average salary range
     * Calculate from member's basic_salary field
     * 
     * @return float
     */
    public function getAverageMemberSalary()
    {
        $result = $this->db->table('member_profiles')
            ->selectAvg('basic_salary', 'avg_salary')
            ->where('basic_salary >', 0)
            ->get()
            ->getRow();

        return $result ? (float) $result->avg_salary : 0;
    }

    /**
     * Format salary range for display
     * 
     * @param object $range
     * @return string
     */
    public function formatRange($range)
    {
        if ($range->min_salary && $range->max_salary) {
            return 'Rp ' . number_format($range->min_salary, 0, ',', '.') . ' - Rp ' . number_format($range->max_salary, 0, ',', '.');
        } elseif ($range->min_salary) {
            return '> Rp ' . number_format($range->min_salary, 0, ',', '.');
        } elseif ($range->max_salary) {
            return '< Rp ' . number_format($range->max_salary, 0, ',', '.');
        }

        return $range->name;
    }
}
