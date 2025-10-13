<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ComplaintCategoryModel
 * 
 * Model untuk mengelola kategori pengaduan/ticket
 * Digunakan untuk mengklasifikasikan jenis pengaduan anggota dan publik
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ComplaintCategoryModel extends Model
{
    protected $table            = 'complaint_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[complaint_categories.name,id,{id}]',
        'slug' => 'permit_empty|max_length[100]|is_unique[complaint_categories.slug,id,{id}]',
        'description' => 'permit_empty|max_length[500]',
        'icon' => 'permit_empty|max_length[50]',
        'color' => 'permit_empty|max_length[20]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama kategori harus diisi',
            'min_length' => 'Nama kategori minimal 3 karakter',
            'max_length' => 'Nama kategori maksimal 100 karakter',
            'is_unique'  => 'Nama kategori sudah ada',
        ],
        'slug' => [
            'is_unique' => 'Slug kategori sudah digunakan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug', 'setDefaultValues'];
    protected $beforeUpdate   = ['generateSlug'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get category with complaints count
     * 
     * @return object
     */
    public function withComplaintsCount()
    {
        return $this->select('complaint_categories.*')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.deleted_at IS NULL) as complaints_count');
    }

    /**
     * Get category with open complaints count
     * 
     * @return object
     */
    public function withOpenComplaintsCount()
    {
        return $this->select('complaint_categories.*')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.status = "open" AND complaints.deleted_at IS NULL) as open_complaints_count');
    }

    /**
     * Get category with pending complaints count
     * 
     * @return object
     */
    public function withPendingComplaintsCount()
    {
        return $this->select('complaint_categories.*')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.status IN ("open", "in_progress") AND complaints.deleted_at IS NULL) as pending_complaints_count');
    }

    /**
     * Get category with complete statistics
     * 
     * @return object
     */
    public function withStats()
    {
        return $this->select('complaint_categories.*')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.deleted_at IS NULL) as total_complaints')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.status = "open" AND complaints.deleted_at IS NULL) as open_complaints')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.status = "in_progress" AND complaints.deleted_at IS NULL) as in_progress_complaints')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.status = "resolved" AND complaints.deleted_at IS NULL) as resolved_complaints')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id AND complaints.status = "closed" AND complaints.deleted_at IS NULL) as closed_complaints');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get active categories only
     * 
     * @return object
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /**
     * Get inactive categories
     * 
     * @return object
     */
    public function inactive()
    {
        return $this->where('is_active', 0);
    }

    /**
     * Order by sort order
     * 
     * @return object
     */
    public function ordered()
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get all active categories ordered
     * 
     * @return array
     */
    public function getActive(): array
    {
        return $this->active()
            ->ordered()
            ->findAll();
    }

    /**
     * Get categories for dropdown
     * 
     * @return array Array with id as key and name as value
     */
    public function getDropdown(): array
    {
        $categories = $this->active()
            ->ordered()
            ->findAll();

        $dropdown = [];
        foreach ($categories as $category) {
            $dropdown[$category->id] = $category->name;
        }

        return $dropdown;
    }

    /**
     * Get category by slug
     * 
     * @param string $slug Category slug
     * @return object|null
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Search categories by keyword
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('name', $keyword)
            ->orLike('description', $keyword)
            ->groupEnd();
    }

    /**
     * Get categories with complaints
     * 
     * @return array
     */
    public function getCategoriesWithComplaints(): array
    {
        return $this->withComplaintsCount()
            ->active()
            ->ordered()
            ->findAll();
    }

    /**
     * Get popular categories (with most complaints)
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getPopular(int $limit = 5): array
    {
        return $this->select('complaint_categories.*')
            ->select('COUNT(complaints.id) as complaints_count')
            ->join('complaints', 'complaints.category_id = complaint_categories.id', 'left')
            ->where('complaint_categories.is_active', 1)
            ->groupBy('complaint_categories.id')
            ->orderBy('complaints_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get total active categories
     * 
     * @return int
     */
    public function countActive(): int
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get category distribution
     * 
     * @return array
     */
    public function getCategoryDistribution(): array
    {
        return $this->select('complaint_categories.name, complaint_categories.icon, complaint_categories.color')
            ->select('COUNT(complaints.id) as count')
            ->select('ROUND(COUNT(complaints.id) * 100.0 / (SELECT COUNT(*) FROM complaints WHERE deleted_at IS NULL), 2) as percentage')
            ->join('complaints', 'complaints.category_id = complaint_categories.id', 'left')
            ->where('complaint_categories.is_active', 1)
            ->groupBy('complaint_categories.id')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    /**
     * Get category with highest resolution rate
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getBestResolutionRate(int $limit = 5): array
    {
        return $this->select('complaint_categories.name')
            ->select('COUNT(complaints.id) as total_complaints')
            ->select('SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved_complaints')
            ->select('ROUND(SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) * 100.0 / COUNT(complaints.id), 2) as resolution_rate')
            ->join('complaints', 'complaints.category_id = complaint_categories.id', 'left')
            ->where('complaint_categories.is_active', 1)
            ->groupBy('complaint_categories.id')
            ->having('total_complaints >', 0)
            ->orderBy('resolution_rate', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get category statistics by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array
     */
    public function getStatsByDateRange(string $startDate, string $endDate): array
    {
        return $this->select('complaint_categories.name')
            ->select('COUNT(complaints.id) as total')
            ->select('SUM(CASE WHEN complaints.status = "open" THEN 1 ELSE 0 END) as open')
            ->select('SUM(CASE WHEN complaints.status = "in_progress" THEN 1 ELSE 0 END) as in_progress')
            ->select('SUM(CASE WHEN complaints.status = "resolved" THEN 1 ELSE 0 END) as resolved')
            ->select('SUM(CASE WHEN complaints.status = "closed" THEN 1 ELSE 0 END) as closed')
            ->join('complaints', 'complaints.category_id = complaint_categories.id', 'left')
            ->where('complaints.created_at >=', $startDate)
            ->where('complaints.created_at <=', $endDate)
            ->groupBy('complaint_categories.id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Activate category
     * 
     * @param int $categoryId Category ID
     * @return bool
     */
    public function activate(int $categoryId): bool
    {
        return $this->update($categoryId, ['is_active' => 1]);
    }

    /**
     * Deactivate category
     * 
     * @param int $categoryId Category ID
     * @return bool
     */
    public function deactivate(int $categoryId): bool
    {
        return $this->update($categoryId, ['is_active' => 0]);
    }

    /**
     * Update sort order
     * 
     * @param int $categoryId Category ID
     * @param int $sortOrder New sort order
     * @return bool
     */
    public function updateSortOrder(int $categoryId, int $sortOrder): bool
    {
        return $this->update($categoryId, ['sort_order' => $sortOrder]);
    }

    /**
     * Reorder categories
     * 
     * @param array $order Array of [id => sort_order]
     * @return bool
     */
    public function reorder(array $order): bool
    {
        $this->db->transStart();

        foreach ($order as $id => $sortOrder) {
            $this->update($id, ['sort_order' => $sortOrder]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Check if category can be deleted
     * 
     * @param int $categoryId Category ID
     * @return bool
     */
    public function canDelete(int $categoryId): bool
    {
        // Category can be deleted if it has no complaints
        $complaintCount = $this->db->table('complaints')
            ->where('category_id', $categoryId)
            ->countAllResults();

        return $complaintCount === 0;
    }

    /**
     * Get categories that can be deleted
     * 
     * @return array
     */
    public function getDeletableCategories(): array
    {
        return $this->select('complaint_categories.*')
            ->select('(SELECT COUNT(*) FROM complaints WHERE complaints.category_id = complaint_categories.id) as complaints_count')
            ->having('complaints_count', 0)
            ->findAll();
    }

    /**
     * Merge categories
     * Move all complaints from source to target category
     * 
     * @param int $sourceId Source category ID
     * @param int $targetId Target category ID
     * @return bool
     */
    public function mergeCategories(int $sourceId, int $targetId): bool
    {
        $this->db->transStart();

        // Move all complaints from source to target
        $this->db->table('complaints')
            ->where('category_id', $sourceId)
            ->update(['category_id' => $targetId]);

        // Delete source category
        $this->delete($sourceId);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate slug from name
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

        if (!isset($data['data']['sort_order'])) {
            // Get max sort_order and increment
            $maxOrder = $this->selectMax('sort_order')->first();
            $data['data']['sort_order'] = $maxOrder ? (int)$maxOrder->sort_order + 1 : 1;
        }

        if (!isset($data['data']['color'])) {
            $data['data']['color'] = 'primary';
        }

        if (!isset($data['data']['icon'])) {
            $data['data']['icon'] = 'ti ti-inbox';
        }

        return $data;
    }
}
