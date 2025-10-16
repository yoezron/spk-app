<?php

namespace App\Services\Content;

use App\Models\PageModel;
use App\Models\PostCategoryModel;
use App\Models\PostModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * ContentService (Public)
 *
 * Provides read-only helpers for public content consumption such as
 * listing published posts, retrieving pages, and building navigation
 * data that is used by the public controllers.
 */
class ContentService
{
    protected PostModel $postModel;

    protected PageModel $pageModel;

    protected PostCategoryModel $categoryModel;

    protected BaseConnection $db;

    public function __construct()
    {
        $this->postModel = new PostModel();
        $this->pageModel = new PageModel();
        $this->categoryModel = new PostCategoryModel();
        $this->db = Database::connect();
    }

    /**
     * Get list of published posts for public consumption.
     * Supports filtering, searching, and pagination.
     */
    public function getPublishedPosts(array $options = []): array
    {
        try {
            $limit = max(1, (int) ($options['limit'] ?? 12));
            $page = max(1, (int) ($options['page'] ?? 1));
            $now = date('Y-m-d H:i:s');

            $builder = $this->db->table('posts')
                ->select(
                    'posts.*, post_categories.name as category_name, post_categories.slug as category_slug, ' .
                        'users.username as author_username, member_profiles.full_name as author_name'
                )
                ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                ->join('users', 'users.id = posts.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('posts.status', 'published')
                ->where('posts.deleted_at', null)
                ->groupStart()
                ->where('posts.published_at <=', $now)
                ->orWhere('posts.published_at', null)
                ->groupEnd();

            if (!empty($options['search'])) {
                $keyword = trim($options['search']);
                $builder->groupStart()
                    ->like('posts.title', $keyword)
                    ->orLike('posts.excerpt', $keyword)
                    ->orLike('posts.content', $keyword)
                    ->groupEnd();
            }

            if (!empty($options['category'])) {
                $builder->where('post_categories.slug', $options['category']);
            }

            if (!empty($options['category_id'])) {
                $builder->where('posts.category_id', (int) $options['category_id']);
            }

            if (!empty($options['tag'])) {
                $builder->join('post_tags', 'post_tags.post_id = posts.id', 'inner')
                    ->join('tags', 'tags.id = post_tags.tag_id', 'inner')
                    ->where('tags.slug', $options['tag']);
            }

            if (!empty($options['exclude_ids']) && is_array($options['exclude_ids'])) {
                $builder->whereNotIn('posts.id', $options['exclude_ids']);
            }

            $allowedOrder = ['published_at', 'created_at', 'views_count', 'title'];
            $orderBy = $options['order_by'] ?? 'published_at';
            if (!in_array($orderBy, $allowedOrder, true)) {
                $orderBy = 'published_at';
            }

            $orderDir = strtoupper($options['order_dir'] ?? 'DESC');
            if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
                $orderDir = 'DESC';
            }

            $countBuilder = clone $builder;
            $total = $countBuilder->distinct()->countAllResults();

            $offset = ($page - 1) * $limit;

            $builder->distinct()
                ->orderBy('posts.' . $orderBy, $orderDir)
                ->limit($limit, $offset);

            $posts = $builder->get()->getResultObject();

            $pager = null;
            if (array_key_exists('page', $options)) {
                $pager = Services::pager();
                $pager->store('default', $page, $limit, $total);
            }

            return [
                'success' => true,
                'message' => 'Published posts retrieved successfully',
                'data' => $posts,
                'pager' => $pager,
                'total' => $total,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getPublishedPosts: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil artikel: ' . $e->getMessage(),
                'data' => [],
                'pager' => null,
                'total' => 0,
            ];
        }
    }

    /**
     * Retrieve featured pages for the landing page.
     */
    public function getFeaturedPages(array $options = []): array
    {
        try {
            $defaultSlugs = ['manifesto', 'sejarah-spk'];
            $slugs = $options['slugs'] ?? $defaultSlugs;

            $builder = $this->pageModel
                ->select('pages.*, users.username as author_username, member_profiles.full_name as author_name')
                ->join('users', 'users.id = pages.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('pages.status', 'published')
                ->where('pages.deleted_at', null);

            if (!empty($slugs)) {
                $builder->whereIn('pages.slug', $slugs);
            }

            $pages = $builder
                ->orderBy('pages.published_at', 'DESC')
                ->findAll();

            return [
                'success' => true,
                'message' => 'Featured pages retrieved successfully',
                'data' => $pages,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getFeaturedPages: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil halaman unggulan: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Retrieve a single published page by slug.
     */
    public function getPageBySlug(string $slug): array
    {
        try {
            $page = $this->pageModel
                ->select('pages.*, users.username as author_username, member_profiles.full_name as author_name')
                ->join('users', 'users.id = pages.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('pages.slug', $slug)
                ->where('pages.deleted_at', null)
                ->first();

            return [
                'success' => (bool) $page,
                'message' => $page ? 'Page found' : 'Halaman tidak ditemukan',
                'data' => $page,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getPageBySlug: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil halaman: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Retrieve a single post by slug.
     */
    public function getPostBySlug(string $slug): array
    {
        try {
            $post = $this->db->table('posts')
                ->select(
                    'posts.*, post_categories.name as category_name, post_categories.slug as category_slug, ' .
                        'users.username as author_username, member_profiles.full_name as author_name'
                )
                ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                ->join('users', 'users.id = posts.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('posts.slug', $slug)
                ->where('posts.deleted_at', null)
                ->get()
                ->getRow();

            if ($post) {
                $tags = $this->db->table('tags')
                    ->select('tags.name, tags.slug')
                    ->join('post_tags', 'post_tags.tag_id = tags.id', 'inner')
                    ->where('post_tags.post_id', $post->id)
                    ->orderBy('tags.name', 'ASC')
                    ->get()
                    ->getResultObject();

                if (!empty($tags)) {
                    $post->tags = array_map(static fn($tag) => $tag->name, $tags);
                } else {
                    $post->tags = [];
                }
            }

            return [
                'success' => (bool) $post,
                'message' => $post ? 'Post found' : 'Artikel tidak ditemukan',
                'data' => $post,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getPostBySlug: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil artikel: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Increment the view counter for a post.
     */
    public function incrementPostViews(int $postId): void
    {
        try {
            $this->db->table('posts')
                ->set('views_count', 'views_count + 1', false)
                ->where('id', $postId)
                ->update();
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::incrementPostViews: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve related posts based on category.
     */
    public function getRelatedPosts(int $postId, array $options = []): array
    {
        try {
            $limit = max(1, (int) ($options['limit'] ?? 4));
            $post = $this->postModel->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post tidak ditemukan',
                    'data' => [],
                ];
            }

            $builder = $this->db->table('posts')
                ->select(
                    'posts.*, post_categories.name as category_name, post_categories.slug as category_slug, ' .
                        'users.username as author_username, member_profiles.full_name as author_name'
                )
                ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                ->join('users', 'users.id = posts.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('posts.status', 'published')
                ->where('posts.deleted_at', null)
                ->where('posts.id !=', $postId)
                ->orderBy('posts.published_at', 'DESC')
                ->limit($limit);

            if (!empty($post->category_id)) {
                $builder->where('posts.category_id', $post->category_id);
            }

            $related = $builder->get()->getResultObject();

            if (empty($related)) {
                // Fallback to latest posts if no related items found.
                $related = $this->db->table('posts')
                    ->select(
                        'posts.*, post_categories.name as category_name, post_categories.slug as category_slug, ' .
                            'users.username as author_username, member_profiles.full_name as author_name'
                    )
                    ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                    ->join('users', 'users.id = posts.author_id', 'left')
                    ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                    ->where('posts.status', 'published')
                    ->where('posts.deleted_at', null)
                    ->where('posts.id !=', $postId)
                    ->orderBy('posts.published_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultObject();
            }

            return [
                'success' => true,
                'message' => 'Related posts retrieved successfully',
                'data' => $related,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getRelatedPosts: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil artikel terkait: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Retrieve the previous and next posts based on publish date.
     */
    public function getPostNavigation(int $postId): array
    {
        try {
            $post = $this->postModel->find($postId);

            if (!$post) {
                return [
                    'previous' => null,
                    'next' => null,
                ];
            }

            $referenceDate = $post->published_at ?? $post->created_at ?? date('Y-m-d H:i:s');

            $previous = $this->db->table('posts')
                ->select('id, title, slug, published_at')
                ->where('status', 'published')
                ->where('deleted_at', null)
                ->where('published_at <', $referenceDate)
                ->orderBy('published_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();

            $next = $this->db->table('posts')
                ->select('id, title, slug, published_at')
                ->where('status', 'published')
                ->where('deleted_at', null)
                ->where('published_at >', $referenceDate)
                ->orderBy('published_at', 'ASC')
                ->limit(1)
                ->get()
                ->getRow();

            return [
                'previous' => $previous,
                'next' => $next,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getPostNavigation: ' . $e->getMessage());

            return [
                'previous' => null,
                'next' => null,
            ];
        }
    }

    /**
     * Retrieve all active categories with published post counts.
     */
    public function getCategories(array $options = []): array
    {
        try {
            $builder = $this->categoryModel
                ->withPostsCount()
                ->where('post_categories.deleted_at', null)
                ->orderBy('post_categories.sort_order', 'ASC')
                ->orderBy('post_categories.name', 'ASC');

            if (isset($options['active_only']) ? $options['active_only'] : true) {
                $builder->where('post_categories.is_active', 1);
            }

            $categories = $builder->findAll();

            return [
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getCategories: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil kategori: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Retrieve a single category by slug.
     */
    public function getCategoryBySlug(string $slug): array
    {
        try {
            $category = $this->categoryModel
                ->where('slug', $slug)
                ->where('deleted_at', null)
                ->first();

            return [
                'success' => (bool) $category,
                'message' => $category ? 'Category found' : 'Kategori tidak ditemukan',
                'data' => $category,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getCategoryBySlug: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil kategori: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Retrieve popular posts ordered by view count.
     */
    public function getPopularPosts(array $options = []): array
    {
        try {
            $limit = max(1, (int) ($options['limit'] ?? 5));

            $posts = $this->db->table('posts')
                ->select(
                    'posts.*, post_categories.name as category_name, post_categories.slug as category_slug, ' .
                        'users.username as author_username, member_profiles.full_name as author_name'
                )
                ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                ->join('users', 'users.id = posts.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('posts.status', 'published')
                ->where('posts.deleted_at', null)
                ->orderBy('posts.views_count', 'DESC')
                ->orderBy('posts.published_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultObject();

            return [
                'success' => true,
                'message' => 'Popular posts retrieved successfully',
                'data' => $posts,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getPopularPosts: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil artikel populer: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Retrieve the most frequently used tags.
     */
    public function getPopularTags(array $options = []): array
    {
        try {
            $limit = max(1, (int) ($options['limit'] ?? 20));

            $tags = $this->db->table('tags')
                ->select('tags.id, tags.name, tags.slug, COUNT(DISTINCT posts.id) as usage_count')
                ->join('post_tags', 'post_tags.tag_id = tags.id', 'inner')
                ->join('posts', 'posts.id = post_tags.post_id', 'inner')
                ->where('posts.status', 'published')
                ->where('posts.deleted_at', null)
                ->groupBy('tags.id, tags.name, tags.slug')
                ->orderBy('usage_count', 'DESC')
                ->orderBy('tags.name', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultObject();

            return [
                'success' => true,
                'message' => 'Popular tags retrieved successfully',
                'data' => $tags,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::getPopularTags: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil tag populer: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Search published posts by keyword.
     */
    public function searchPosts(string $keyword, array $options = []): array
    {
        try {
            $limit = max(1, (int) ($options['limit'] ?? 10));

            $builder = $this->db->table('posts')
                ->select(
                    'posts.*, post_categories.name as category_name, post_categories.slug as category_slug, ' .
                        'users.username as author_username, member_profiles.full_name as author_name'
                )
                ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                ->join('users', 'users.id = posts.author_id', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->where('posts.status', 'published')
                ->where('posts.deleted_at', null)
                ->groupStart()
                ->like('posts.title', $keyword)
                ->orLike('posts.excerpt', $keyword)
                ->orLike('posts.content', $keyword)
                ->groupEnd()
                ->orderBy('posts.published_at', 'DESC')
                ->limit($limit);

            $posts = $builder->get()->getResultObject();

            return [
                'success' => true,
                'message' => 'Search completed',
                'data' => $posts,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in Content\\ContentService::searchPosts: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mencari artikel: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
