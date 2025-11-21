<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ContentService;
use App\Services\FileUploadService;
use App\Models\PostModel;
use App\Models\PageModel;
use App\Models\PostCategoryModel;
use App\Models\PostTagModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ContentController (Admin)
 * 
 * Mengelola Content Management System (CMS)
 * CRUD blog posts, static pages, categories, tags
 * Support featured images, SEO fields, publish workflow
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ContentController extends BaseController
{
    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * @var FileUploadService
     */
    protected $fileUploadService;

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
     * @var PostTagModel
     */
    protected $tagModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->contentService = new ContentService();
        $this->fileUploadService = new FileUploadService();
        $this->postModel = new PostModel();
        $this->pageModel = new PageModel();
        $this->categoryModel = new PostCategoryModel();
        $this->tagModel = new PostTagModel();
    }

    /**
     * Display list of blog posts
     * Shows all posts with filters
     * 
     * @return string|ResponseInterface
     */
    public function posts()
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengelola konten');
        }

        // Get filters from request
        $filters = [
            'status' => $this->request->getGet('status'),
            'category_id' => $this->request->getGet('category_id'),
            'search' => $this->request->getGet('search')
        ];

        // Build query
        $builder = $this->postModel
            ->select('posts.*, post_categories.name as category_name, auth_identities.secret as author_email, member_profiles.full_name as author_name')
            ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
            ->join('users', 'users.id = posts.author_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('posts.status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $builder->where('posts.category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('posts.title', $search)
                ->orLike('posts.content', $search)
                ->orLike('posts.excerpt', $search)
                ->groupEnd();
        }

        // Get paginated results
        $posts = $builder
            ->orderBy('posts.created_at', 'DESC')
            ->paginate(20);

        // Get categories for filter
        $categories = $this->categoryModel->findAll();

        $data = [
            'title' => 'Kelola Artikel',
            'posts' => $posts,
            'pager' => $this->postModel->pager,
            'filters' => $filters,
            'categories' => $categories
        ];

        return view('admin/content/posts/index', $data);
    }

    /**
     * Show create post form
     * Display form to create new blog post
     * 
     * @return string|ResponseInterface
     */
    public function createPost()
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat artikel');
        }

        // Get categories and tags
        $categories = $this->categoryModel->findAll();
        $tags = $this->tagModel->findAll();

        $data = [
            'title' => 'Buat Artikel Baru',
            'categories' => $categories,
            'tags' => $tags
        ];

        return view('admin/content/posts/create', $data);
    }

    /**
     * Store new post
     * Save new blog post with image and tags
     * 
     * @return ResponseInterface
     */
    public function storePost(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat artikel');
        }

        // Validate input
        $rules = [
            'title' => 'required|min_length[5]|max_length[255]',
            'slug' => 'required|is_unique[posts.slug]|alpha_dash',
            'excerpt' => 'permit_empty|max_length[500]',
            'content' => 'required|min_length[50]',
            'category_id' => 'required|numeric',
            'status' => 'required|in_list[draft,published]',
            'featured_image' => 'permit_empty|uploaded[featured_image]|max_size[featured_image,2048]|is_image[featured_image]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $postData = [
                'title' => $this->request->getPost('title'),
                'slug' => $this->request->getPost('slug'),
                'excerpt' => $this->request->getPost('excerpt'),
                'content' => $this->request->getPost('content'),
                'category_id' => $this->request->getPost('category_id'),
                'author_id' => auth()->id(),
                'status' => $this->request->getPost('status'),
                'meta_description' => $this->request->getPost('meta_description'),
                'meta_keywords' => $this->request->getPost('meta_keywords'),
                'is_featured' => $this->request->getPost('is_featured') ? 1 : 0
            ];

            // Handle featured image upload
            $featuredImage = $this->request->getFile('featured_image');
            if ($featuredImage && $featuredImage->isValid()) {
                $uploadResult = $this->fileUploadService->uploadFile($featuredImage, 'posts');
                if ($uploadResult['success']) {
                    $postData['featured_image'] = $uploadResult['data']['filename'];
                }
            }

            // Get tags
            $tags = $this->request->getPost('tags') ?? [];

            // Create post using service
            $result = $this->contentService->createPost($postData, $tags);

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            // Set published_at if status is published
            if ($postData['status'] === 'published') {
                $this->postModel->update($result['data']['post_id'], [
                    'published_at' => date('Y-m-d H:i:s')
                ]);
            }

            return redirect()->to('/admin/content/posts')->with('success', 'Artikel berhasil dibuat');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::storePost: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal membuat artikel: ' . $e->getMessage());
        }
    }

    /**
     * Show edit post form
     * Display form to edit existing post
     * 
     * @param int $id Post ID
     * @return string|ResponseInterface
     */
    public function editPost(int $id)
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit artikel');
        }

        // Get post
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->back()->with('error', 'Artikel tidak ditemukan');
        }

        // Get post tags
        $postTags = $this->contentService->getPostTags($id);

        // Get all categories and tags
        $categories = $this->categoryModel->findAll();
        $tags = $this->tagModel->findAll();

        $data = [
            'title' => 'Edit Artikel',
            'post' => $post,
            'post_tags' => $postTags,
            'categories' => $categories,
            'tags' => $tags
        ];

        return view('admin/content/posts/edit', $data);
    }

    /**
     * Update post
     * Update existing blog post
     * 
     * @param int $id Post ID
     * @return ResponseInterface
     */
    public function updatePost(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit artikel');
        }

        // Get existing post
        $existingPost = $this->postModel->find($id);

        if (!$existingPost) {
            return redirect()->back()->with('error', 'Artikel tidak ditemukan');
        }

        // Validate input
        $rules = [
            'title' => 'required|min_length[5]|max_length[255]',
            'slug' => "required|alpha_dash|is_unique[posts.slug,id,{$id}]",
            'excerpt' => 'permit_empty|max_length[500]',
            'content' => 'required|min_length[50]',
            'category_id' => 'required|numeric',
            'status' => 'required|in_list[draft,published]',
            'featured_image' => 'permit_empty|uploaded[featured_image]|max_size[featured_image,2048]|is_image[featured_image]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $postData = [
                'title' => $this->request->getPost('title'),
                'slug' => $this->request->getPost('slug'),
                'excerpt' => $this->request->getPost('excerpt'),
                'content' => $this->request->getPost('content'),
                'category_id' => $this->request->getPost('category_id'),
                'status' => $this->request->getPost('status'),
                'meta_description' => $this->request->getPost('meta_description'),
                'meta_keywords' => $this->request->getPost('meta_keywords'),
                'is_featured' => $this->request->getPost('is_featured') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle featured image upload
            $featuredImage = $this->request->getFile('featured_image');
            if ($featuredImage && $featuredImage->isValid()) {
                // Delete old image if exists
                if (!empty($existingPost->featured_image)) {
                    $oldImagePath = WRITEPATH . 'uploads/posts/' . $existingPost->featured_image;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $uploadResult = $this->fileUploadService->uploadFile($featuredImage, 'posts');
                if ($uploadResult['success']) {
                    $postData['featured_image'] = $uploadResult['data']['filename'];
                }
            }

            // Get tags
            $tags = $this->request->getPost('tags') ?? [];

            // Update post using service
            $result = $this->contentService->updatePost($id, $postData, $tags);

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            // Set published_at if status changed to published
            if ($existingPost->status !== 'published' && $postData['status'] === 'published') {
                $this->postModel->update($id, [
                    'published_at' => date('Y-m-d H:i:s')
                ]);
            }

            return redirect()->to('/admin/content/posts')->with('success', 'Artikel berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::updatePost: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate artikel: ' . $e->getMessage());
        }
    }

    /**
     * Delete post
     * Soft delete blog post
     * 
     * @param int $id Post ID
     * @return ResponseInterface
     */
    public function deletePost(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus artikel');
        }

        try {
            $post = $this->postModel->find($id);

            if (!$post) {
                return redirect()->back()->with('error', 'Artikel tidak ditemukan');
            }

            // Delete post (soft delete if implemented)
            $this->postModel->delete($id);

            // Delete featured image if exists
            if (!empty($post->featured_image)) {
                $imagePath = WRITEPATH . 'uploads/posts/' . $post->featured_image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Log activity
            log_message('info', "Post ID {$id} ({$post->title}) deleted by user " . auth()->id());

            return redirect()->to('/admin/content/posts')->with('success', 'Artikel berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::deletePost: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus artikel: ' . $e->getMessage());
        }
    }

    /**
     * Publish post
     * Change status to published
     * 
     * @param int $id Post ID
     * @return ResponseInterface
     */
    public function publish(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mempublikasi artikel');
        }

        try {
            $post = $this->postModel->find($id);

            if (!$post) {
                return redirect()->back()->with('error', 'Artikel tidak ditemukan');
            }

            // Update status
            $this->postModel->update($id, [
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s')
            ]);

            // Log activity
            log_message('info', "Post ID {$id} published by user " . auth()->id());

            return redirect()->back()->with('success', 'Artikel berhasil dipublikasi');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::publish: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mempublikasi artikel: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish post
     * Change status back to draft
     * 
     * @param int $id Post ID
     * @return ResponseInterface
     */
    public function unpublish(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk unpublish artikel');
        }

        try {
            $post = $this->postModel->find($id);

            if (!$post) {
                return redirect()->back()->with('error', 'Artikel tidak ditemukan');
            }

            // Update status
            $this->postModel->update($id, [
                'status' => 'draft'
            ]);

            // Log activity
            log_message('info', "Post ID {$id} unpublished by user " . auth()->id());

            return redirect()->back()->with('success', 'Artikel berhasil di-draft kembali');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::unpublish: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal unpublish artikel: ' . $e->getMessage());
        }
    }

    /**
     * Display list of static pages
     * Shows all pages (Manifesto, AD/ART, etc)
     * 
     * @return string|ResponseInterface
     */
    public function pages()
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengelola halaman');
        }

        // Get all pages
        $pages = $this->pageModel
            ->select('pages.*, auth_identities.secret as author_email, member_profiles.full_name as author_name')
            ->join('users', 'users.id = pages.author_id', 'left')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->orderBy('pages.order_number', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Kelola Halaman',
            'pages' => $pages
        ];

        return view('admin/content/pages/index', $data);
    }

    /**
     * Show edit page form
     * Display form to edit static page
     * 
     * @param string $slug Page slug
     * @return string|ResponseInterface
     */
    public function editPage(string $slug)
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit halaman');
        }

        // Get page
        $page = $this->pageModel->where('slug', $slug)->first();

        if (!$page) {
            return redirect()->back()->with('error', 'Halaman tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Halaman - ' . $page->title,
            'page' => $page
        ];

        return view('admin/content/pages/edit', $data);
    }

    /**
     * Update page
     * Update static page content
     * 
     * @param string $slug Page slug
     * @return ResponseInterface
     */
    public function updatePage(string $slug): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit halaman');
        }

        // Get existing page
        $existingPage = $this->pageModel->where('slug', $slug)->first();

        if (!$existingPage) {
            return redirect()->back()->with('error', 'Halaman tidak ditemukan');
        }

        // Validate input
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'content' => 'required|min_length[50]',
            'status' => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $pageData = [
                'title' => $this->request->getPost('title'),
                'content' => $this->request->getPost('content'),
                'status' => $this->request->getPost('status'),
                'meta_description' => $this->request->getPost('meta_description'),
                'meta_keywords' => $this->request->getPost('meta_keywords'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update page
            $this->pageModel->where('slug', $slug)->set($pageData)->update();

            // Log activity
            log_message('info', "Page '{$slug}' updated by user " . auth()->id());

            return redirect()->to('/admin/content/pages')->with('success', 'Halaman berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::updatePage: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate halaman: ' . $e->getMessage());
        }
    }

    /**
     * Manage categories
     * CRUD for post categories
     * 
     * @return string|ResponseInterface
     */
    public function categories()
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengelola kategori');
        }

        // Get all categories with post count
        $categories = $this->categoryModel
            ->select('post_categories.*, COUNT(posts.id) as post_count')
            ->join('posts', 'posts.category_id = post_categories.id', 'left')
            ->groupBy('post_categories.id')
            ->orderBy('post_categories.name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Kelola Kategori',
            'categories' => $categories
        ];

        return view('admin/content/categories/index', $data);
    }

    /**
     * Store new category
     * Create new post category
     * 
     * @return ResponseInterface
     */
    public function storeCategory(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat kategori');
        }

        // Validate input
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|is_unique[post_categories.name]',
            'slug' => 'required|alpha_dash|is_unique[post_categories.slug]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $categoryData = [
                'name' => $this->request->getPost('name'),
                'slug' => $this->request->getPost('slug'),
                'description' => $this->request->getPost('description')
            ];

            $this->categoryModel->insert($categoryData);

            return redirect()->to('/admin/content/categories')->with('success', 'Kategori berhasil dibuat');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::storeCategory: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal membuat kategori: ' . $e->getMessage());
        }
    }

    /**
     * Update category
     * Update existing category
     * 
     * @param int $id Category ID
     * @return ResponseInterface
     */
    public function updateCategory(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit kategori');
        }

        // Validate input
        $rules = [
            'name' => "required|min_length[3]|max_length[100]|is_unique[post_categories.name,id,{$id}]",
            'slug' => "required|alpha_dash|is_unique[post_categories.slug,id,{$id}]"
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $categoryData = [
                'name' => $this->request->getPost('name'),
                'slug' => $this->request->getPost('slug'),
                'description' => $this->request->getPost('description')
            ];

            $this->categoryModel->update($id, $categoryData);

            return redirect()->to('/admin/content/categories')->with('success', 'Kategori berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::updateCategory: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate kategori: ' . $e->getMessage());
        }
    }

    /**
     * Delete category
     * Delete category if no posts are using it
     * 
     * @param int $id Category ID
     * @return ResponseInterface
     */
    public function deleteCategory(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('content.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus kategori');
        }

        try {
            // Check if category is used by any posts
            $postCount = $this->postModel->where('category_id', $id)->countAllResults();

            if ($postCount > 0) {
                return redirect()->back()->with('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$postCount} artikel");
            }

            $this->categoryModel->delete($id);

            return redirect()->to('/admin/content/categories')->with('success', 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in ContentController::deleteCategory: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Generate slug from title (AJAX endpoint)
     * Auto-generate SEO-friendly slug
     * 
     * @return ResponseInterface JSON response
     */
    public function generateSlug(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $title = $this->request->getPost('title');

        if (empty($title)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Title is required'
            ])->setStatusCode(400);
        }

        // Generate slug
        $slug = url_title($title, '-', true);

        return $this->response->setJSON([
            'success' => true,
            'data' => ['slug' => $slug]
        ]);
    }
}
