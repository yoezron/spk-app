<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PostCategoryModel
 * 
 * Model untuk mengelola kategori blog posts dan artikel
 * Digunakan untuk mengklasifikasikan konten berita dan artikel SPK
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PostCategoryModel extends Model
{
    protected $table            = 'post_categories';
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
        'sort_order',
        'parent_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[post_categories.name,id,{id}]',
        'slug' => 'permit_empty|max_length[100]|is_unique[post_categories.slug,id,{id}]',
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
     * Get category with posts count
     * 
     * @return object
     */
    public function withPostsCount()
    {
        return $this->select('post_categories.*')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id AND posts.status = "published" AND posts.deleted_at IS NULL) as posts_count');
    }

    /**
     * Get category with all posts count (including drafts)
     * 
     * @return object
     */
    public function withAllPostsCount()
    {
        return $this->select('post_categories.*')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id AND posts.deleted_at IS NULL) as total_posts_count');
    }

    /**
     * Get category with parent
     * 
     * @return object
     */
    public function withParent()
    {
        return $this->select('post_categories.*, parent_categories.name as parent_name, parent_categories.slug as parent_slug')
            ->join('post_categories as parent_categories', 'parent_categories.id = post_categories.parent_id', 'left');
    }

    /**
     * Get category with children count
     * 
     * @return object
     */
    public function withChildrenCount()
    {
        return $this->select('post_categories.*')
            ->select('(SELECT COUNT(*) FROM post_categories as child_categories WHERE child_categories.parent_id = post_categories.id AND child_categories.deleted_at IS NULL) as children_count');
    }

    /**
     * Get category with complete statistics
     * 
     * @return object
     */
    public function withStats()
    {
        return $this->select('post_categories.*')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id AND posts.status = "published" AND posts.deleted_at IS NULL) as published_posts')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id AND posts.status = "draft" AND posts.deleted_at IS NULL) as draft_posts')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id AND posts.deleted_at IS NULL) as total_posts')
            ->select('(SELECT SUM(views_count) FROM posts WHERE posts.category_id = post_categories.id AND posts.deleted_at IS NULL) as total_views');
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
     * Get parent categories (top level)
     * 
     * @return object
     */
    public function parentCategories()
    {
        return $this->where('parent_id IS NULL');
    }

    /**
     * Get child categories
     * 
     * @param int $parentId Parent category ID
     * @return object
     */
    public function childCategories(int $parentId)
    {
        return $this->where('parent_id', $parentId);
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
     * Get hierarchical dropdown (with indentation for children)
     * 
     * @return array
     */
    public function getHierarchicalDropdown(): array
    {
        $dropdown = [];
        $parents = $this->active()
            ->parentCategories()
            ->ordered()
            ->findAll();

        foreach ($parents as $parent) {
            $dropdown[$parent->id] = $parent->name;

            $children = $this->active()
                ->childCategories($parent->id)
                ->ordered()
                ->findAll();

            foreach ($children as $child) {
                $dropdown[$child->id] = '— ' . $child->name;
            }
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
     * Get categories with posts
     * 
     * @return array
     */
    public function getCategoriesWithPosts(): array
    {
        return $this->withPostsCount()
            ->active()
            ->having('posts_count >', 0)
            ->ordered()
            ->findAll();
    }

    /**
     * Get popular categories (with most posts)
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getPopular(int $limit = 5): array
    {
        return $this->select('post_categories.*')
            ->select('COUNT(posts.id) as posts_count')
            ->join('posts', 'posts.category_id = post_categories.id AND posts.status = "published"', 'left')
            ->where('post_categories.is_active', 1)
            ->groupBy('post_categories.id')
            ->orderBy('posts_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get category structure (hierarchical)
     * 
     * @return array
     */
    public function getCategoryStructure(): array
    {
        $parents = $this->active()
            ->parentCategories()
            ->ordered()
            ->findAll();

        foreach ($parents as &$parent) {
            $parent->children = $this->active()
                ->childCategories($parent->id)
                ->ordered()
                ->findAll();
        }

        return $parents;
    }

    /**
     * Get categories for navigation menu
     * 
     * @return array
     */
    public function getForNavigation(): array
    {
        return $this->withPostsCount()
            ->active()
            ->parentCategories()
            ->having('posts_count >', 0)
            ->ordered()
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
        return $this->select('post_categories.name, post_categories.slug, post_categories.icon, post_categories.color')
            ->select('COUNT(posts.id) as count')
            ->select('ROUND(COUNT(posts.id) * 100.0 / (SELECT COUNT(*) FROM posts WHERE status = "published" AND deleted_at IS NULL), 2) as percentage')
            ->join('posts', 'posts.category_id = post_categories.id AND posts.status = "published"', 'left')
            ->where('post_categories.is_active', 1)
            ->groupBy('post_categories.id')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    /**
     * Get categories with most views
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getMostViewed(int $limit = 5): array
    {
        return $this->select('post_categories.name, post_categories.slug')
            ->select('SUM(posts.views_count) as total_views')
            ->join('posts', 'posts.category_id = post_categories.id AND posts.status = "published"', 'left')
            ->where('post_categories.is_active', 1)
            ->groupBy('post_categories.id')
            ->having('total_views >', 0)
            ->orderBy('total_views', 'DESC')
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
        return $this->select('post_categories.name, post_categories.slug')
            ->select('COUNT(posts.id) as total_posts')
            ->select('SUM(posts.views_count) as total_views')
            ->join('posts', 'posts.category_id = post_categories.id', 'left')
            ->where('posts.status', 'published')
            ->where('posts.published_at >=', $startDate)
            ->where('posts.published_at <=', $endDate)
            ->groupBy('post_categories.id')
            ->orderBy('total_posts', 'DESC')
            ->findAll();
    }

    /**
     * Get empty categories (no posts)
     * 
     * @return array
     */
    public function getEmptyCategories(): array
    {
        return $this->select('post_categories.*')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id) as posts_count')
            ->having('posts_count', 0)
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
     * Set parent category
     * 
     * @param int $categoryId Category ID
     * @param int|null $parentId Parent category ID
     * @return bool
     */
    public function setParent(int $categoryId, ?int $parentId = null): bool
    {
        // Prevent circular reference
        if ($parentId && $this->isCircularReference($categoryId, $parentId)) {
            return false;
        }

        return $this->update($categoryId, ['parent_id' => $parentId]);
    }

    /**
     * Check for circular reference in parent-child relationship
     * 
     * @param int $categoryId Category ID
     * @param int $parentId Potential parent ID
     * @return bool
     */
    protected function isCircularReference(int $categoryId, int $parentId): bool
    {
        if ($categoryId === $parentId) {
            return true;
        }

        $parent = $this->find($parentId);
        while ($parent && $parent->parent_id) {
            if ($parent->parent_id === $categoryId) {
                return true;
            }
            $parent = $this->find($parent->parent_id);
        }

        return false;
    }

    /**
     * Check if category can be deleted
     * 
     * @param int $categoryId Category ID
     * @return bool
     */
    public function canDelete(int $categoryId): bool
    {
        // Category can be deleted if it has no posts and no children
        $postCount = $this->db->table('posts')
            ->where('category_id', $categoryId)
            ->countAllResults();

        $childrenCount = $this->db->table('post_categories')
            ->where('parent_id', $categoryId)
            ->countAllResults();

        return $postCount === 0 && $childrenCount === 0;
    }

    /**
     * Get deletable categories
     * 
     * @return array
     */
    public function getDeletableCategories(): array
    {
        return $this->select('post_categories.*')
            ->select('(SELECT COUNT(*) FROM posts WHERE posts.category_id = post_categories.id) as posts_count')
            ->select('(SELECT COUNT(*) FROM post_categories as children WHERE children.parent_id = post_categories.id) as children_count')
            ->having('posts_count', 0)
            ->having('children_count', 0)
            ->findAll();
    }

    /**
     * Merge categories
     * Move all posts from source to target category
     * 
     * @param int $sourceId Source category ID
     * @param int $targetId Target category ID
     * @return bool
     */
    public function mergeCategories(int $sourceId, int $targetId): bool
    {
        $this->db->transStart();

        // Move all posts from source to target
        $this->db->table('posts')
            ->where('category_id', $sourceId)
            ->update(['category_id' => $targetId]);

        // Move children categories to target
        $this->db->table('post_categories')
            ->where('parent_id', $sourceId)
            ->update(['parent_id' => $targetId]);

        // Delete source category
        $this->delete($sourceId);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Move posts to another category
     * 
     * @param int $fromCategoryId Source category ID
     * @param int $toCategoryId Target category ID
     * @return int Number of posts moved
     */
    public function movePosts(int $fromCategoryId, int $toCategoryId): int
    {
        return $this->db->table('posts')
            ->where('category_id', $fromCategoryId)
            ->update(['category_id' => $toCategoryId]);
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
            $data['data']['icon'] = 'ti ti-folder';
        }

        return $data;
    }
}
