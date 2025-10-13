<?php

namespace App\Services;

use App\Models\PostModel;
use App\Models\PageModel;
use App\Models\PostCategoryModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * ContentService
 * 
 * Menangani CMS content management untuk posts & pages
 * Termasuk create, update, delete, publish workflow, SEO, dan statistics
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ContentService
{
    /**
     * @var PostModel
     */
    protected $postModel;

    /**
     * @var PageModel
     */
    protected $pageModel;

    /**
     * @var PostCategoryModel
     */
    protected $categoryModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->postModel = new PostModel();
        $this->pageModel = new PageModel();
        $this->categoryModel = new PostCategoryModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create new post
     * Creates blog post/article with metadata
     * 
     * @param array $data Post data
     * @param int $authorId Author user ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createPost(array $data, int $authorId): array
    {
        $this->db->transStart();

        try {
            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                return [
                    'success' => false,
                    'message' => 'Judul dan konten harus diisi',
                    'data' => null
                ];
            }

            // Validate author
            $author = $this->userModel->find($authorId);
            if (!$author) {
                return [
                    'success' => false,
                    'message' => 'Author tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate category if provided
            if (!empty($data['category_id'])) {
                $category = $this->categoryModel->find($data['category_id']);
                if (!$category) {
                    return [
                        'success' => false,
                        'message' => 'Kategori tidak ditemukan',
                        'data' => null
                    ];
                }
            }

            // Generate slug
            $slug = $this->generateSlug($data['title'], 'post');

            // Prepare post data
            $postData = [
                'category_id' => $data['category_id'] ?? null,
                'author_id' => $authorId,
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? $this->generateExcerpt($data['content']),
                'content' => $data['content'],
                'featured_image' => $data['featured_image'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'is_featured' => $data['is_featured'] ?? 0,
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'published_at' => $data['published_at'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Auto-set published_at if status is published
            if ($postData['status'] === 'published' && empty($postData['published_at'])) {
                $postData['published_at'] = date('Y-m-d H:i:s');
            }

            // Insert post
            $postId = $this->postModel->insert($postData);

            if (!$postId) {
                throw new \Exception('Gagal menyimpan post: ' . json_encode($this->postModel->errors()));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Post berhasil dibuat',
                'data' => [
                    'post_id' => $postId,
                    'title' => $data['title'],
                    'slug' => $slug,
                    'status' => $postData['status']
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ContentService::createPost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat post: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update existing post
     * Updates post data and metadata
     * 
     * @param int $postId Post ID
     * @param array $data Update data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updatePost(int $postId, array $data): array
    {
        $this->db->transStart();

        try {
            $post = $this->postModel->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post tidak ditemukan',
                    'data' => null
                ];
            }

            // Prepare update data
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update fields if provided
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
                // Regenerate slug if title changed
                if ($data['title'] !== $post->title) {
                    $updateData['slug'] = $this->generateSlug($data['title'], 'post', $postId);
                }
            }

            if (isset($data['content'])) {
                $updateData['content'] = $data['content'];
            }

            if (isset($data['excerpt'])) {
                $updateData['excerpt'] = $data['excerpt'];
            }

            if (isset($data['category_id'])) {
                $updateData['category_id'] = $data['category_id'];
            }

            if (isset($data['featured_image'])) {
                $updateData['featured_image'] = $data['featured_image'];
            }

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
                // Set published_at if changing to published
                if ($data['status'] === 'published' && $post->status !== 'published') {
                    $updateData['published_at'] = date('Y-m-d H:i:s');
                }
            }

            if (isset($data['is_featured'])) {
                $updateData['is_featured'] = $data['is_featured'];
            }

            if (isset($data['meta_title'])) {
                $updateData['meta_title'] = $data['meta_title'];
            }

            if (isset($data['meta_description'])) {
                $updateData['meta_description'] = $data['meta_description'];
            }

            if (isset($data['meta_keywords'])) {
                $updateData['meta_keywords'] = $data['meta_keywords'];
            }

            // Update post
            $updated = $this->postModel->update($postId, $updateData);

            if (!$updated) {
                throw new \Exception('Gagal update post');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Post berhasil diupdate',
                'data' => [
                    'post_id' => $postId,
                    'slug' => $updateData['slug'] ?? $post->slug
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ContentService::updatePost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update post: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete post
     * Soft deletes post
     * 
     * @param int $postId Post ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deletePost(int $postId): array
    {
        try {
            $post = $this->postModel->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post tidak ditemukan',
                    'data' => null
                ];
            }

            // Soft delete
            $this->postModel->delete($postId);

            return [
                'success' => true,
                'message' => 'Post berhasil dihapus',
                'data' => [
                    'post_id' => $postId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::deletePost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal hapus post: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Publish post
     * Changes post status to published
     * 
     * @param int $postId Post ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function publishPost(int $postId): array
    {
        try {
            $post = $this->postModel->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post tidak ditemukan',
                    'data' => null
                ];
            }

            // Update to published
            $this->postModel->update($postId, [
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Post berhasil dipublikasikan',
                'data' => [
                    'post_id' => $postId,
                    'status' => 'published',
                    'published_at' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::publishPost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal publish post: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create new page
     * Creates static page with metadata
     * 
     * @param array $data Page data
     * @param int $authorId Author user ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createPage(array $data, int $authorId): array
    {
        $this->db->transStart();

        try {
            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                return [
                    'success' => false,
                    'message' => 'Judul dan konten harus diisi',
                    'data' => null
                ];
            }

            // Generate slug
            $slug = $this->generateSlug($data['title'], 'page');

            // Prepare page data
            $pageData = [
                'author_id' => $authorId,
                'title' => $data['title'],
                'slug' => $slug,
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
                'template' => $data['template'] ?? 'default',
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'is_homepage' => $data['is_homepage'] ?? 0,
                'sort_order' => $data['sort_order'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Auto-set published_at if status is published
            if ($pageData['status'] === 'published' && empty($pageData['published_at'])) {
                $pageData['published_at'] = date('Y-m-d H:i:s');
            }

            // Insert page
            $pageId = $this->pageModel->insert($pageData);

            if (!$pageId) {
                throw new \Exception('Gagal menyimpan page: ' . json_encode($this->pageModel->errors()));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Page berhasil dibuat',
                'data' => [
                    'page_id' => $pageId,
                    'title' => $data['title'],
                    'slug' => $slug,
                    'status' => $pageData['status']
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ContentService::createPage: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat page: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update existing page
     * Updates page data and metadata
     * 
     * @param int $pageId Page ID
     * @param array $data Update data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updatePage(int $pageId, array $data): array
    {
        try {
            $page = $this->pageModel->find($pageId);

            if (!$page) {
                return [
                    'success' => false,
                    'message' => 'Page tidak ditemukan',
                    'data' => null
                ];
            }

            // Prepare update data
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update fields if provided
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
                // Regenerate slug if title changed
                if ($data['title'] !== $page->title) {
                    $updateData['slug'] = $this->generateSlug($data['title'], 'page', $pageId);
                }
            }

            if (isset($data['content'])) {
                $updateData['content'] = $data['content'];
            }

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
                // Set published_at if changing to published
                if ($data['status'] === 'published' && $page->status !== 'published') {
                    $updateData['published_at'] = date('Y-m-d H:i:s');
                }
            }

            if (isset($data['template'])) {
                $updateData['template'] = $data['template'];
            }

            if (isset($data['is_homepage'])) {
                $updateData['is_homepage'] = $data['is_homepage'];
            }

            if (isset($data['sort_order'])) {
                $updateData['sort_order'] = $data['sort_order'];
            }

            if (isset($data['meta_title'])) {
                $updateData['meta_title'] = $data['meta_title'];
            }

            if (isset($data['meta_description'])) {
                $updateData['meta_description'] = $data['meta_description'];
            }

            if (isset($data['meta_keywords'])) {
                $updateData['meta_keywords'] = $data['meta_keywords'];
            }

            // Update page
            $this->pageModel->update($pageId, $updateData);

            return [
                'success' => true,
                'message' => 'Page berhasil diupdate',
                'data' => [
                    'page_id' => $pageId,
                    'slug' => $updateData['slug'] ?? $page->slug
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::updatePage: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update page: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete page
     * Soft deletes page
     * 
     * @param int $pageId Page ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deletePage(int $pageId): array
    {
        try {
            $page = $this->pageModel->find($pageId);

            if (!$page) {
                return [
                    'success' => false,
                    'message' => 'Page tidak ditemukan',
                    'data' => null
                ];
            }

            // Soft delete
            $this->pageModel->delete($pageId);

            return [
                'success' => true,
                'message' => 'Page berhasil dihapus',
                'data' => [
                    'page_id' => $pageId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::deletePage: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal hapus page: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Set featured post
     * Marks post as featured/unfeatured
     * 
     * @param int $postId Post ID
     * @param bool $featured Featured status
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function setFeaturedPost(int $postId, bool $featured = true): array
    {
        try {
            $post = $this->postModel->find($postId);

            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post tidak ditemukan',
                    'data' => null
                ];
            }

            $this->postModel->update($postId, [
                'is_featured' => $featured ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => $featured ? 'Post berhasil di-feature' : 'Post berhasil di-unfeature',
                'data' => [
                    'post_id' => $postId,
                    'is_featured' => $featured
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::setFeaturedPost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update featured status: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get content by slug
     * Retrieves post or page by slug
     * 
     * @param string $slug Content slug
     * @param string $type Content type (post or page)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getContentBySlug(string $slug, string $type = 'post'): array
    {
        try {
            if ($type === 'post') {
                $content = $this->postModel
                    ->select('posts.*, users.username as author_name, post_categories.name as category_name')
                    ->join('users', 'users.id = posts.author_id', 'left')
                    ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                    ->where('posts.slug', $slug)
                    ->where('posts.status', 'published')
                    ->first();
            } else {
                $content = $this->pageModel
                    ->select('pages.*, users.username as author_name')
                    ->join('users', 'users.id = pages.author_id', 'left')
                    ->where('pages.slug', $slug)
                    ->where('pages.status', 'published')
                    ->first();
            }

            if (!$content) {
                return [
                    'success' => false,
                    'message' => ucfirst($type) . ' tidak ditemukan',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => ucfirst($type) . ' berhasil diambil',
                'data' => $content
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::getContentBySlug: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil content: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Search content
     * Searches posts and pages by keyword
     * 
     * @param string $keyword Search keyword
     * @param array $filters Optional filters (type, status, category_id, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function searchContent(string $keyword, array $filters = []): array
    {
        try {
            $results = [
                'posts' => [],
                'pages' => []
            ];

            // Search posts
            if (!isset($filters['type']) || $filters['type'] === 'post') {
                $postBuilder = $this->postModel
                    ->select('posts.*, users.username as author_name')
                    ->join('users', 'users.id = posts.author_id', 'left')
                    ->groupStart()
                    ->like('posts.title', $keyword)
                    ->orLike('posts.content', $keyword)
                    ->orLike('posts.excerpt', $keyword)
                    ->groupEnd();

                if (isset($filters['status'])) {
                    $postBuilder->where('posts.status', $filters['status']);
                }

                if (isset($filters['category_id'])) {
                    $postBuilder->where('posts.category_id', $filters['category_id']);
                }

                $results['posts'] = $postBuilder->findAll();
            }

            // Search pages
            if (!isset($filters['type']) || $filters['type'] === 'page') {
                $pageBuilder = $this->pageModel
                    ->select('pages.*, users.username as author_name')
                    ->join('users', 'users.id = pages.author_id', 'left')
                    ->groupStart()
                    ->like('pages.title', $keyword)
                    ->orLike('pages.content', $keyword)
                    ->groupEnd();

                if (isset($filters['status'])) {
                    $pageBuilder->where('pages.status', $filters['status']);
                }

                $results['pages'] = $pageBuilder->findAll();
            }

            $totalResults = count($results['posts']) + count($results['pages']);

            return [
                'success' => true,
                'message' => "Ditemukan {$totalResults} hasil",
                'data' => [
                    'keyword' => $keyword,
                    'results' => $results,
                    'total' => $totalResults
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::searchContent: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal search content: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get content statistics
     * Returns comprehensive content stats
     * 
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getContentStats(): array
    {
        try {
            // Post stats
            $postStats = [
                'total' => $this->postModel->countAllResults(false),
                'published' => $this->postModel->where('status', 'published')->countAllResults(false),
                'draft' => $this->postModel->where('status', 'draft')->countAllResults(false),
                'featured' => $this->postModel->where('is_featured', 1)->countAllResults(false)
            ];

            // Page stats
            $pageStats = [
                'total' => $this->pageModel->countAllResults(false),
                'published' => $this->pageModel->where('status', 'published')->countAllResults(false),
                'draft' => $this->pageModel->where('status', 'draft')->countAllResults(false)
            ];

            // Category stats
            $categoryStats = $this->categoryModel
                ->select('post_categories.*, COUNT(posts.id) as post_count')
                ->join('posts', 'posts.category_id = post_categories.id', 'left')
                ->groupBy('post_categories.id')
                ->findAll();

            return [
                'success' => true,
                'message' => 'Statistik content berhasil diambil',
                'data' => [
                    'posts' => $postStats,
                    'pages' => $pageStats,
                    'categories' => $categoryStats
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentService::getContentStats: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate unique slug
     * Creates URL-friendly slug from title
     * 
     * @param string $title Content title
     * @param string $type Content type (post or page)
     * @param int|null $excludeId Exclude this ID from uniqueness check
     * @return string Unique slug
     */
    protected function generateSlug(string $title, string $type = 'post', ?int $excludeId = null): string
    {
        $slug = url_title($title, '-', true);
        $model = $type === 'post' ? $this->postModel : $this->pageModel;

        // Check if slug exists
        $builder = $model->builder()->where('slug', $slug);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        $count = $builder->countAllResults();

        // Append number if slug exists
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        return $slug;
    }

    /**
     * Generate excerpt from content
     * Creates short excerpt from full content
     * 
     * @param string $content Full content
     * @param int $length Maximum length
     * @return string Excerpt
     */
    protected function generateExcerpt(string $content, int $length = 200): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);

        if (strlen($content) <= $length) {
            return $content;
        }

        return substr($content, 0, $length) . '...';
    }
}
