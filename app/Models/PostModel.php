<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PostModel
 * 
 * Model untuk mengelola blog posts, artikel, dan konten berita SPK
 * Mendukung kategori, featured posts, dan SEO optimization
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PostModel extends Model
{
    protected $table            = 'posts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'is_featured',
        'is_sticky',
        'published_at',
        'meta_description',
        'meta_keywords',
        'views_count',
        'comments_enabled'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title' => 'required|min_length[5]|max_length[255]',
        'slug' => 'permit_empty|max_length[255]|is_unique[posts.slug,id,{id}]',
        'excerpt' => 'permit_empty|max_length[500]',
        'content' => 'required|min_length[10]',
        'status' => 'permit_empty|in_list[draft,published,scheduled]',
        'author_id' => 'required|is_natural_no_zero',
        'is_featured' => 'permit_empty|in_list[0,1]',
        'is_sticky' => 'permit_empty|in_list[0,1]',
        'comments_enabled' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Judul post harus diisi',
            'min_length' => 'Judul minimal 5 karakter',
            'max_length' => 'Judul maksimal 255 karakter',
        ],
        'slug' => [
            'is_unique' => 'Slug sudah digunakan',
        ],
        'content' => [
            'required'   => 'Konten post harus diisi',
            'min_length' => 'Konten minimal 10 karakter',
        ],
        'status' => [
            'in_list' => 'Status tidak valid',
        ],
        'author_id' => [
            'required' => 'Author harus diisi',
            'is_natural_no_zero' => 'Author tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug', 'setDefaults'];
    protected $beforeUpdate   = ['generateSlug'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get post with author
     * 
     * @return object
     */
    public function withAuthor()
    {
        return $this->select('posts.*, users.username as author_name, users.email as author_email')
            ->join('users', 'users.id = posts.author_id', 'left');
    }

    /**
     * Get post with category
     * 
     * @return object
     */
    public function withCategory()
    {
        return $this->select('posts.*, post_categories.name as category_name, post_categories.slug as category_slug')
            ->join('post_categories', 'post_categories.id = posts.category_id', 'left');
    }

    /**
     * Get post with complete relations
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('posts.*')
            ->select('users.username as author_name, users.email as author_email')
            ->select('post_categories.name as category_name, post_categories.slug as category_slug')
            ->join('users', 'users.id = posts.author_id', 'left')
            ->join('post_categories', 'post_categories.id = posts.category_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING BY STATUS
    // ========================================

    /**
     * Get published posts only
     * 
     * @return object
     */
    public function published()
    {
        return $this->where('status', 'published')
            ->where('published_at <=', date('Y-m-d H:i:s'));
    }

    /**
     * Get draft posts
     * 
     * @return object
     */
    public function draft()
    {
        return $this->where('status', 'draft');
    }

    /**
     * Get scheduled posts
     * 
     * @return object
     */
    public function scheduled()
    {
        return $this->where('status', 'scheduled')
            ->where('published_at >', date('Y-m-d H:i:s'));
    }

    /**
     * Get posts by status
     * 
     * @param string $status Status value
     * @return object
     */
    public function byStatus(string $status)
    {
        return $this->where('status', $status);
    }

    // ========================================
    // SCOPES - FILTERING BY FEATURES
    // ========================================

    /**
     * Get featured posts
     * 
     * @return object
     */
    public function featured()
    {
        return $this->where('is_featured', 1);
    }

    /**
     * Get sticky posts
     * 
     * @return object
     */
    public function sticky()
    {
        return $this->where('is_sticky', 1);
    }

    /**
     * Get posts by category
     * 
     * @param int $categoryId Category ID
     * @return object
     */
    public function byCategory(int $categoryId)
    {
        return $this->where('category_id', $categoryId);
    }

    /**
     * Get posts by author
     * 
     * @param int $authorId Author ID
     * @return object
     */
    public function byAuthor(int $authorId)
    {
        return $this->where('author_id', $authorId);
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get post by slug
     * 
     * @param string $slug Post slug
     * @return object|null
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get published post by slug with complete data
     * 
     * @param string $slug Post slug
     * @return object|null
     */
    public function getPublishedBySlug(string $slug)
    {
        return $this->withComplete()
            ->published()
            ->where('posts.slug', $slug)
            ->first();
    }

    /**
     * Search posts by keyword
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('title', $keyword)
            ->orLike('excerpt', $keyword)
            ->orLike('content', $keyword)
            ->orLike('meta_keywords', $keyword)
            ->groupEnd();
    }

    /**
     * Get recent posts
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function recent(int $limit = 10): array
    {
        return $this->published()
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get popular posts (by views)
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function popular(int $limit = 10): array
    {
        return $this->published()
            ->orderBy('views_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get featured posts for homepage
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getFeatured(int $limit = 5): array
    {
        return $this->withComplete()
            ->published()
            ->featured()
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get sticky posts
     * 
     * @return array
     */
    public function getStickyPosts(): array
    {
        return $this->withComplete()
            ->published()
            ->sticky()
            ->orderBy('published_at', 'DESC')
            ->findAll();
    }

    /**
     * Get related posts by category
     * 
     * @param int $postId Current post ID
     * @param int $categoryId Category ID
     * @param int $limit Number of records
     * @return array
     */
    public function getRelated(int $postId, int $categoryId, int $limit = 5): array
    {
        return $this->withAuthor()
            ->published()
            ->where('posts.id !=', $postId)
            ->where('category_id', $categoryId)
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get posts for sitemap
     * 
     * @return array
     */
    public function getForSitemap(): array
    {
        return $this->select('slug, updated_at')
            ->published()
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count posts by status
     * 
     * @return array
     */
    public function countByStatus(): array
    {
        $result = $this->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $stats = [
            'draft' => 0,
            'published' => 0,
            'scheduled' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->status] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Count posts by category
     * 
     * @return array
     */
    public function countByCategory(): array
    {
        return $this->select('post_categories.name, post_categories.slug, COUNT(posts.id) as count')
            ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
            ->where('posts.status', 'published')
            ->groupBy('posts.category_id')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    /**
     * Count posts by author
     * 
     * @return array
     */
    public function countByAuthor(): array
    {
        return $this->select('users.username, COUNT(posts.id) as count')
            ->join('users', 'users.id = posts.author_id')
            ->where('posts.status', 'published')
            ->groupBy('posts.author_id')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    /**
     * Get total views count
     * 
     * @return int
     */
    public function getTotalViews(): int
    {
        $result = $this->selectSum('views_count')->first();
        return $result ? (int)$result->views_count : 0;
    }

    /**
     * Get posts statistics by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return object
     */
    public function getStatsByDateRange(string $startDate, string $endDate)
    {
        return $this->select('
                COUNT(*) as total_posts,
                SUM(views_count) as total_views,
                AVG(views_count) as avg_views
            ')
            ->where('status', 'published')
            ->where('published_at >=', $startDate)
            ->where('published_at <=', $endDate)
            ->first();
    }

    /**
     * Get posting activity by month
     * 
     * @param int $months Number of months
     * @return array
     */
    public function getActivityByMonth(int $months = 12): array
    {
        return $this->select('
                DATE_FORMAT(published_at, "%Y-%m") as month,
                COUNT(*) as count
            ')
            ->where('status', 'published')
            ->where('published_at >=', date('Y-m-d', strtotime("-{$months} months")))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Publish post
     * 
     * @param int $postId Post ID
     * @return bool
     */
    public function publishPost(int $postId): bool
    {
        $data = [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($postId, $data);
    }

    /**
     * Unpublish post (set to draft)
     * 
     * @param int $postId Post ID
     * @return bool
     */
    public function unpublishPost(int $postId): bool
    {
        return $this->update($postId, ['status' => 'draft']);
    }

    /**
     * Schedule post for future publishing
     * 
     * @param int $postId Post ID
     * @param string $publishDate Publish date
     * @return bool
     */
    public function schedulePost(int $postId, string $publishDate): bool
    {
        $data = [
            'status' => 'scheduled',
            'published_at' => $publishDate,
        ];

        return $this->update($postId, $data);
    }

    /**
     * Set post as featured
     * 
     * @param int $postId Post ID
     * @param bool $featured Featured status
     * @return bool
     */
    public function setFeatured(int $postId, bool $featured = true): bool
    {
        return $this->update($postId, ['is_featured' => $featured ? 1 : 0]);
    }

    /**
     * Set post as sticky
     * 
     * @param int $postId Post ID
     * @param bool $sticky Sticky status
     * @return bool
     */
    public function setSticky(int $postId, bool $sticky = true): bool
    {
        return $this->update($postId, ['is_sticky' => $sticky ? 1 : 0]);
    }

    /**
     * Increment views count
     * 
     * @param int $postId Post ID
     * @return bool
     */
    public function incrementViews(int $postId): bool
    {
        return $this->set('views_count', 'views_count + 1', false)
            ->where('id', $postId)
            ->update();
    }

    /**
     * Update featured image
     * 
     * @param int $postId Post ID
     * @param string $imagePath Image path
     * @return bool
     */
    public function updateFeaturedImage(int $postId, string $imagePath): bool
    {
        return $this->update($postId, ['featured_image' => $imagePath]);
    }

    /**
     * Enable/disable comments
     * 
     * @param int $postId Post ID
     * @param bool $enabled Comments enabled status
     * @return bool
     */
    public function setCommentsEnabled(int $postId, bool $enabled = true): bool
    {
        return $this->update($postId, ['comments_enabled' => $enabled ? 1 : 0]);
    }

    /**
     * Publish scheduled posts
     * Run this via cron job
     * 
     * @return int Number of posts published
     */
    public function publishScheduledPosts(): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('status', 'scheduled')
            ->where('published_at <=', $now)
            ->set('status', 'published')
            ->update();
    }

    /**
     * Get post for editing with validation
     * 
     * @param int $postId Post ID
     * @param int $userId User ID (for permission check)
     * @return object|null
     */
    public function getForEdit(int $postId, int $userId)
    {
        // Admin can edit all posts, author can edit their own
        return $this->withComplete()
            ->where('posts.id', $postId)
            ->groupStart()
            ->where('posts.author_id', $userId)
            // Add admin check via join if needed
            ->groupEnd()
            ->first();
    }

    /**
     * Duplicate post
     * 
     * @param int $postId Post ID to duplicate
     * @return int|false New post ID or false
     */
    public function duplicatePost(int $postId)
    {
        $post = $this->find($postId);

        if (!$post) {
            return false;
        }

        $newPost = [
            'category_id' => $post->category_id,
            'author_id' => $post->author_id,
            'title' => $post->title . ' (Copy)',
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'featured_image' => $post->featured_image,
            'status' => 'draft',
            'meta_description' => $post->meta_description,
            'meta_keywords' => $post->meta_keywords,
            'comments_enabled' => $post->comments_enabled,
        ];

        return $this->insert($newPost);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate slug from title
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data): array
    {
        if (isset($data['data']['title']) && empty($data['data']['slug'])) {
            $slug = url_title($data['data']['title'], '-', true);

            // Check if slug exists and make it unique
            $count = 1;
            $originalSlug = $slug;
            while ($this->where('slug', $slug)->countAllResults() > 0) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $data['data']['slug'] = $slug;
        }

        return $data;
    }

    /**
     * Set default values before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaults(array $data): array
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'draft';
        }

        if (!isset($data['data']['is_featured'])) {
            $data['data']['is_featured'] = 0;
        }

        if (!isset($data['data']['is_sticky'])) {
            $data['data']['is_sticky'] = 0;
        }

        if (!isset($data['data']['views_count'])) {
            $data['data']['views_count'] = 0;
        }

        if (!isset($data['data']['comments_enabled'])) {
            $data['data']['comments_enabled'] = 1;
        }

        // Set published_at if status is published but no date set
        if ($data['data']['status'] === 'published' && empty($data['data']['published_at'])) {
            $data['data']['published_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }
}
