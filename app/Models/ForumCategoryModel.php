<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ForumCategoryModel
 * 
 * Model untuk mengelola kategori forum diskusi
 * Digunakan untuk mengelompokkan thread forum berdasarkan topik
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ForumCategoryModel extends Model
{
    protected $table            = 'forum_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
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
        'slug'          => 'permit_empty|max_length[100]|is_unique[forum_categories.slug,id,{id}]',
        'description'   => 'permit_empty|max_length[500]',
        'icon'          => 'permit_empty|max_length[50]',
        'color'         => 'permit_empty|max_length[7]',
        'display_order' => 'permit_empty|integer',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama kategori harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter',
        ],
        'slug' => [
            'is_unique' => 'Slug kategori sudah digunakan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate slug from name
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data)
    {
        if (isset($data['data']['name']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = url_title($data['data']['name'], '-', true);
        }
        return $data;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get category with threads count
     * 
     * @return object
     */
    public function withThreadsCount()
    {
        return $this->select('forum_categories.*')
            ->select('(SELECT COUNT(*) FROM forum_threads WHERE forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL) as threads_count');
    }

    /**
     * Get category with comments count
     * 
     * @return object
     */
    public function withCommentsCount()
    {
        return $this->select('forum_categories.*')
            ->select('(SELECT COUNT(*) FROM forum_comments fc JOIN forum_threads ft ON fc.thread_id = ft.id WHERE ft.category_id = forum_categories.id AND fc.deleted_at IS NULL AND ft.deleted_at IS NULL) as comments_count');
    }

    /**
     * Get category with latest thread
     * 
     * @return object
     */
    public function withLatestThread()
    {
        return $this->select('forum_categories.*')
            ->select('latest_thread.id as latest_thread_id, latest_thread.title as latest_thread_title, latest_thread.slug as latest_thread_slug, latest_thread.created_at as latest_thread_date')
            ->select('latest_user.username as latest_thread_author_username')
            ->select('latest_member.full_name as latest_thread_author_name')
            ->join('(SELECT category_id, id, title, slug, user_id, created_at, ROW_NUMBER() OVER (PARTITION BY category_id ORDER BY created_at DESC) as rn FROM forum_threads WHERE deleted_at IS NULL) as latest_thread', 'latest_thread.category_id = forum_categories.id AND latest_thread.rn = 1', 'left')
            ->join('users as latest_user', 'latest_user.id = latest_thread.user_id', 'left')
            ->join('member_profiles as latest_member', 'latest_member.user_id = latest_user.id', 'left');
    }

    /**
     * Get category with complete statistics
     * 
     * @return object
     */
    public function withStatistics()
    {
        return $this->select('forum_categories.*')
            ->select('(SELECT COUNT(*) FROM forum_threads WHERE forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL) as threads_count')
            ->select('(SELECT COUNT(*) FROM forum_comments fc JOIN forum_threads ft ON fc.thread_id = ft.id WHERE ft.category_id = forum_categories.id AND fc.deleted_at IS NULL AND ft.deleted_at IS NULL) as comments_count')
            ->select('(SELECT SUM(views_count) FROM forum_threads WHERE forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL) as total_views');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active categories
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
     * Get all categories ordered
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
     * Search category by name
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
     * Get category dropdown options
     * 
     * @return array
     */
    public function getDropdown()
    {
        $categories = $this->where('is_active', 1)
            ->orderBy('display_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $options = [];
        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }

        return $options;
    }

    /**
     * Check if category exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get category by slug
     * 
     * @param string $slug
     * @return object|null
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Bulk insert categories
     * Used for seeding
     * 
     * @param array $data
     * @return bool
     */
    public function bulkInsert(array $data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Activate category
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate category
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

    /**
     * Reorder categories
     * 
     * @param array $orders Array of ['id' => order]
     * @return bool
     */
    public function reorderCategories(array $orders)
    {
        $this->db->transStart();

        foreach ($orders as $id => $order) {
            $this->update($id, ['display_order' => $order]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Check if category has threads
     * 
     * @param int $id
     * @return bool
     */
    public function hasThreads($id)
    {
        $count = $this->db->table('forum_threads')
            ->where('category_id', $id)
            ->where('deleted_at', null)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Move threads to another category
     * 
     * @param int $fromCategoryId
     * @param int $toCategoryId
     * @return bool
     */
    public function moveThreads($fromCategoryId, $toCategoryId)
    {
        return $this->db->table('forum_threads')
            ->where('category_id', $fromCategoryId)
            ->update(['category_id' => $toCategoryId]);
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total categories count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get active categories count
     * 
     * @return int
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get most active category
     * Based on thread count
     * 
     * @return object|null
     */
    public function getMostActive()
    {
        return $this->select('forum_categories.*, COUNT(forum_threads.id) as threads_count')
            ->join('forum_threads', 'forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL', 'left')
            ->where('forum_categories.is_active', 1)
            ->groupBy('forum_categories.id')
            ->orderBy('threads_count', 'DESC')
            ->first();
    }

    /**
     * Get most popular category
     * Based on views count
     * 
     * @return object|null
     */
    public function getMostPopular()
    {
        return $this->select('forum_categories.*, SUM(forum_threads.views_count) as total_views')
            ->join('forum_threads', 'forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL', 'left')
            ->where('forum_categories.is_active', 1)
            ->groupBy('forum_categories.id')
            ->orderBy('total_views', 'DESC')
            ->first();
    }

    /**
     * Get categories distribution
     * 
     * @return array
     */
    public function getDistribution()
    {
        return $this->select('forum_categories.name, forum_categories.color')
            ->select('COUNT(forum_threads.id) as threads_count')
            ->select('COALESCE(SUM(forum_threads.views_count), 0) as total_views')
            ->join('forum_threads', 'forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL', 'left')
            ->where('forum_categories.is_active', 1)
            ->groupBy('forum_categories.id')
            ->orderBy('forum_categories.display_order', 'ASC')
            ->findAll();
    }

    /**
     * Get empty categories (no threads)
     * 
     * @return array
     */
    public function getEmptyCategories()
    {
        return $this->select('forum_categories.*')
            ->select('(SELECT COUNT(*) FROM forum_threads WHERE forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL) as threads_count')
            ->having('threads_count', 0)
            ->where('forum_categories.is_active', 1)
            ->findAll();
    }

    /**
     * Get category activity summary
     * 
     * @param int $categoryId
     * @return object|null
     */
    public function getActivitySummary($categoryId)
    {
        return $this->select('forum_categories.*')
            ->select('COUNT(DISTINCT forum_threads.id) as threads_count')
            ->select('COUNT(forum_comments.id) as comments_count')
            ->select('COUNT(DISTINCT forum_threads.user_id) as unique_authors')
            ->select('COALESCE(SUM(forum_threads.views_count), 0) as total_views')
            ->select('MAX(forum_threads.last_activity_at) as last_activity')
            ->join('forum_threads', 'forum_threads.category_id = forum_categories.id AND forum_threads.deleted_at IS NULL', 'left')
            ->join('forum_comments', 'forum_comments.thread_id = forum_threads.id AND forum_comments.deleted_at IS NULL', 'left')
            ->where('forum_categories.id', $categoryId)
            ->groupBy('forum_categories.id')
            ->first();
    }
}
