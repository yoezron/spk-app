<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Services\Content\ForumService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * ForumController (Member Area)
 * 
 * Menangani forum diskusi untuk anggota
 * List threads, view detail, create thread, reply, edit/delete posts
 * 
 * @package App\Controllers\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ForumController extends BaseController
{
    /**
     * @var ForumService
     */
    protected $forumService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->forumService = new ForumService();
    }

    /**
     * Display forum threads list
     * Shows all active threads with pagination and search
     * 
     * @return string
     */
    public function index(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            // Get query parameters
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = 15;
            $search = $this->request->getGet('search');
            $category = $this->request->getGet('category');

            // Build filter options
            $options = [
                'page' => $page,
                'limit' => $perPage,
                'order_by' => 'last_activity',
                'order_dir' => 'DESC'
            ];

            if ($search) {
                $options['search'] = $search;
            }

            if ($category) {
                $options['category'] = $category;
            }

            // Get threads
            $result = $this->forumService->getThreads($options);

            // Get forum categories
            $categories = $this->forumService->getCategories();

            // Get user's thread count
            $userThreadCount = $this->getUserThreadCount(auth()->id());

            $data = [
                'title' => 'Forum Diskusi - Serikat Pekerja Kampus',
                'pageTitle' => 'Forum Diskusi',

                // Threads data
                'threads' => $result['data'] ?? [],
                'pager' => $result['pager'] ?? null,
                'total' => $result['total'] ?? 0,

                // Categories
                'categories' => $categories['data'] ?? [],

                // User stats
                'userThreadCount' => $userThreadCount,

                // Filter state
                'currentSearch' => $search,
                'currentCategory' => $category
            ];

            return view('member/forum/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading forum index: ' . $e->getMessage());

            return view('member/forum/index', [
                'title' => 'Forum Diskusi',
                'pageTitle' => 'Forum',
                'threads' => [],
                'pager' => null,
                'total' => 0,
                'categories' => [],
                'userThreadCount' => 0,
                'currentSearch' => null,
                'currentCategory' => null
            ]);
        }
    }

    /**
     * Display thread detail with all posts
     * 
     * @param int $threadId Thread ID
     * @return string
     */
    public function show(int $threadId): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            // Get thread with posts
            $result = $this->forumService->getThreadWithPosts($threadId);

            if (!$result['success'] || !$result['data']) {
                return redirect()->to('/member/forum')
                    ->with('error', 'Thread tidak ditemukan.');
            }

            $thread = $result['data'];

            // Increment view count
            $this->forumService->incrementThreadViews($threadId);

            // Check if user can moderate (edit/delete any post)
            $canModerate = auth()->user()->can('forum.moderate');

            $data = [
                'title' => $thread->title . ' - Forum SPK',
                'pageTitle' => $thread->title,

                // Thread data
                'thread' => $thread,
                'posts' => $thread->posts ?? [],

                // Permissions
                'canModerate' => $canModerate,
                'currentUserId' => auth()->id()
            ];

            return view('member/forum/show', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading thread: ' . $e->getMessage());

            return redirect()->to('/member/forum')
                ->with('error', 'Terjadi kesalahan saat memuat thread.');
        }
    }

    /**
     * Display create thread form
     * 
     * @return string
     */
    public function create(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            // Get forum categories
            $categories = $this->forumService->getCategories();

            $data = [
                'title' => 'Buat Thread Baru - Forum SPK',
                'pageTitle' => 'Buat Thread Baru',
                'categories' => $categories['data'] ?? []
            ];

            return view('member/forum/create', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading create thread form: ' . $e->getMessage());

            return redirect()->to('/member/forum')
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Store new thread
     * 
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        // Validation rules
        $validationRules = [
            'title' => [
                'label' => 'Judul Thread',
                'rules' => 'required|min_length[5]|max_length[200]',
                'errors' => [
                    'required' => 'Judul thread harus diisi',
                    'min_length' => 'Judul minimal 5 karakter',
                    'max_length' => 'Judul maksimal 200 karakter'
                ]
            ],
            'content' => [
                'label' => 'Konten',
                'rules' => 'required|min_length[20]',
                'errors' => [
                    'required' => 'Konten harus diisi',
                    'min_length' => 'Konten minimal 20 karakter'
                ]
            ],
            'category' => [
                'label' => 'Kategori',
                'rules' => 'permit_empty|max_length[50]'
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $threadData = [
                'title' => $this->request->getPost('title'),
                'content' => $this->request->getPost('content'),
                'category' => $this->request->getPost('category'),
                'created_by' => auth()->id()
            ];

            $result = $this->forumService->createThread($threadData);

            if ($result['success']) {
                return redirect()->to('/member/forum/thread/' . $result['data']['thread_id'])
                    ->with('success', 'Thread berhasil dibuat.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating thread: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat thread.');
        }
    }

    /**
     * Reply to thread
     * 
     * @param int $threadId Thread ID
     * @return RedirectResponse
     */
    public function reply(int $threadId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        // Validation rules
        $validationRules = [
            'content' => [
                'label' => 'Konten',
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Konten harus diisi',
                    'min_length' => 'Konten minimal 10 karakter'
                ]
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $postData = [
                'thread_id' => $threadId,
                'content' => $this->request->getPost('content'),
                'user_id' => auth()->id()
            ];

            $result = $this->forumService->createPost($postData);

            if ($result['success']) {
                return redirect()->to('/member/forum/thread/' . $threadId . '#post-' . $result['data']['post_id'])
                    ->with('success', 'Balasan berhasil ditambahkan.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error replying to thread: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan balasan.');
        }
    }

    /**
     * Edit post form
     * 
     * @param int $postId Post ID
     * @return string|RedirectResponse
     */
    public function editPost(int $postId)
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            // Get post
            $postModel = model('ForumPostModel');
            $post = $postModel->find($postId);

            if (!$post) {
                return redirect()->to('/member/forum')
                    ->with('error', 'Post tidak ditemukan.');
            }

            // Check if user owns the post or can moderate
            if ($post->user_id != auth()->id() && !auth()->user()->can('forum.moderate')) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki izin untuk mengedit post ini.');
            }

            $data = [
                'title' => 'Edit Post - Forum SPK',
                'pageTitle' => 'Edit Post',
                'post' => $post
            ];

            return view('member/forum/edit_post', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading edit post: ' . $e->getMessage());

            return redirect()->to('/member/forum')
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Update post
     * 
     * @param int $postId Post ID
     * @return RedirectResponse
     */
    public function updatePost(int $postId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        // Validation
        $validationRules = [
            'content' => [
                'label' => 'Konten',
                'rules' => 'required|min_length[10]'
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            // Get post
            $postModel = model('ForumPostModel');
            $post = $postModel->find($postId);

            if (!$post) {
                return redirect()->to('/member/forum')
                    ->with('error', 'Post tidak ditemukan.');
            }

            // Check permission
            if ($post->user_id != auth()->id() && !auth()->user()->can('forum.moderate')) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki izin untuk mengedit post ini.');
            }

            // Update post
            $updateData = [
                'content' => $this->request->getPost('content'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->forumService->updatePost($postId, $updateData);

            if ($result['success']) {
                return redirect()->to('/member/forum/thread/' . $post->thread_id . '#post-' . $postId)
                    ->with('success', 'Post berhasil diperbarui.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating post: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui post.');
        }
    }

    /**
     * Delete post
     * 
     * @param int $postId Post ID
     * @return RedirectResponse
     */
    public function deletePost(int $postId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            // Get post
            $postModel = model('ForumPostModel');
            $post = $postModel->find($postId);

            if (!$post) {
                return redirect()->to('/member/forum')
                    ->with('error', 'Post tidak ditemukan.');
            }

            // Check permission
            if ($post->user_id != auth()->id() && !auth()->user()->can('forum.moderate')) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki izin untuk menghapus post ini.');
            }

            $threadId = $post->thread_id;

            // Delete post
            $result = $this->forumService->deletePost($postId);

            if ($result['success']) {
                return redirect()->to('/member/forum/thread/' . $threadId)
                    ->with('success', 'Post berhasil dihapus.');
            } else {
                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting post: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus post.');
        }
    }

    /**
     * Search threads (AJAX endpoint)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function search()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        $query = $this->request->getGet('q');

        if (empty($query) || strlen($query) < 3) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Query minimal 3 karakter',
                'data' => []
            ]);
        }

        try {
            $result = $this->forumService->searchThreads($query, [
                'limit' => 10
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Search completed',
                'data' => $result['data'] ?? []
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error searching threads: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari',
                'data' => []
            ]);
        }
    }

    /**
     * Get user's thread count
     * 
     * @param int $userId User ID
     * @return int
     */
    protected function getUserThreadCount(int $userId): int
    {
        try {
            $threadModel = model('ForumThreadModel');
            return $threadModel->where('created_by', $userId)->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'Error getting user thread count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Display user's threads
     * 
     * @return string
     */
    public function myThreads(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            $page = (int) ($this->request->getGet('page') ?? 1);

            $result = $this->forumService->getThreads([
                'page' => $page,
                'limit' => 15,
                'user_id' => auth()->id(),
                'order_by' => 'created_at',
                'order_dir' => 'DESC'
            ]);

            $data = [
                'title' => 'Thread Saya - Forum SPK',
                'pageTitle' => 'Thread Saya',
                'threads' => $result['data'] ?? [],
                'pager' => $result['pager'] ?? null,
                'total' => $result['total'] ?? 0
            ];

            return view('member/forum/my_threads', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading my threads: ' . $e->getMessage());

            return redirect()->to('/member/forum')
                ->with('error', 'Terjadi kesalahan.');
        }
    }
}
