<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ForumService;
use App\Services\Communication\NotificationService;
use App\Models\ForumThreadModel;
use App\Models\ForumPostModel;
use App\Models\ForumCategoryModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ForumController (Admin)
 * 
 * Mengelola moderasi forum
 * Pin/unpin threads, lock/unlock, delete posts/threads
 * NO regional scope - forum is accessible globally
 * 
 * @package App\Controllers\Admin
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
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var ForumThreadModel
     */
    protected $threadModel;

    /**
     * @var ForumPostModel
     */
    protected $postModel;

    /**
     * @var ForumCategoryModel
     */
    protected $categoryModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->forumService = new ForumService();
        $this->notificationService = new NotificationService();
        $this->threadModel = new ForumThreadModel();
        $this->postModel = new ForumPostModel();
        $this->categoryModel = new ForumCategoryModel();
    }

    /**
     * Display all forum threads (moderation view)
     * Shows all threads with moderation actions
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk moderasi forum');
        }

        // Get filters from request
        $filters = [
            'category_id' => $this->request->getGet('category_id'),
            'status' => $this->request->getGet('status'),
            'search' => $this->request->getGet('search')
        ];

        // Build query
        $builder = $this->threadModel
            ->select('forum_threads.*, forum_categories.name as category_name, auth_identities.secret as author_email, member_profiles.full_name as author_name')
            ->join('forum_categories', 'forum_categories.id = forum_threads.category_id')
            ->join('users', 'users.id = forum_threads.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');

        // Apply filters
        if (!empty($filters['category_id'])) {
            $builder->where('forum_threads.category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'pinned':
                    $builder->where('forum_threads.is_pinned', 1);
                    break;
                case 'locked':
                    $builder->where('forum_threads.is_locked', 1);
                    break;
                case 'active':
                    $builder->where('forum_threads.is_locked', 0);
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('forum_threads.title', $search)
                ->orLike('forum_threads.content', $search)
                ->orLike('member_profiles.full_name', $search)
                ->groupEnd();
        }

        // Get paginated results
        $threads = $builder
            ->orderBy('forum_threads.is_pinned', 'DESC')
            ->orderBy('forum_threads.last_post_at', 'DESC')
            ->paginate(20);

        // Get categories for filter
        $categories = $this->categoryModel->findAll();

        $data = [
            'title' => 'Moderasi Forum',
            'threads' => $threads,
            'pager' => $this->threadModel->pager,
            'filters' => $filters,
            'categories' => $categories
        ];

        return view('admin/forum/index', $data);
    }

    /**
     * View thread detail with all posts (moderation view)
     * Shows complete thread with moderation actions
     * 
     * @param int $id Thread ID
     * @return string|ResponseInterface
     */
    public function show(int $id)
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk moderasi forum');
        }

        // Get thread with author info
        $thread = $this->threadModel
            ->select('forum_threads.*, forum_categories.name as category_name, auth_identities.secret as author_email, member_profiles.full_name as author_name')
            ->join('forum_categories', 'forum_categories.id = forum_threads.category_id')
            ->join('users', 'users.id = forum_threads.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->find($id);

        if (!$thread) {
            return redirect()->back()->with('error', 'Thread tidak ditemukan');
        }

        // Get all posts in this thread
        $posts = $this->postModel
            ->select('forum_posts.*, auth_identities.secret as author_email, member_profiles.full_name as author_name, member_profiles.foto_path')
            ->join('users', 'users.id = forum_posts.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->where('forum_posts.thread_id', $id)
            ->orderBy('forum_posts.created_at', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Detail Thread - ' . $thread->title,
            'thread' => $thread,
            'posts' => $posts
        ];

        return view('admin/forum/show', $data);
    }

    /**
     * Pin thread (highlight at top)
     * Pinned threads appear at the top of forum list
     * 
     * @param int $id Thread ID
     * @return ResponseInterface
     */
    public function pin(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk pin thread');
        }

        try {
            $thread = $this->threadModel->find($id);

            if (!$thread) {
                return redirect()->back()->with('error', 'Thread tidak ditemukan');
            }

            // Toggle pin status
            $newPinStatus = !$thread->is_pinned;

            $this->threadModel->update($id, [
                'is_pinned' => $newPinStatus,
                'pinned_at' => $newPinStatus ? date('Y-m-d H:i:s') : null,
                'pinned_by' => $newPinStatus ? auth()->id() : null
            ]);

            $message = $newPinStatus ? 'Thread berhasil di-pin' : 'Thread berhasil di-unpin';

            // Log activity
            log_message('info', "Thread ID {$id} " . ($newPinStatus ? 'pinned' : 'unpinned') . " by user " . auth()->id());

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumController::pin: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pin thread: ' . $e->getMessage());
        }
    }

    /**
     * Lock thread (prevent new posts)
     * Locked threads cannot receive new posts
     * 
     * @param int $id Thread ID
     * @return ResponseInterface
     */
    public function lock(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk lock thread');
        }

        try {
            $thread = $this->threadModel->find($id);

            if (!$thread) {
                return redirect()->back()->with('error', 'Thread tidak ditemukan');
            }

            // Toggle lock status
            $newLockStatus = !$thread->is_locked;

            // Get lock reason if provided
            $lockReason = $this->request->getPost('reason');

            $this->threadModel->update($id, [
                'is_locked' => $newLockStatus,
                'locked_at' => $newLockStatus ? date('Y-m-d H:i:s') : null,
                'locked_by' => $newLockStatus ? auth()->id() : null,
                'lock_reason' => $newLockStatus ? $lockReason : null
            ]);

            $message = $newLockStatus ? 'Thread berhasil dikunci' : 'Thread berhasil dibuka kembali';

            // Notify thread owner if locked
            if ($newLockStatus && $thread->user_id != auth()->id()) {
                $this->notificationService->sendThreadLockedNotification(
                    $thread->user_id,
                    $thread->title,
                    $lockReason ?? 'Pelanggaran aturan forum'
                );
            }

            // Log activity
            log_message('info', "Thread ID {$id} " . ($newLockStatus ? 'locked' : 'unlocked') . " by user " . auth()->id());

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumController::lock: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses lock thread: ' . $e->getMessage());
        }
    }

    /**
     * Delete specific post (moderation power)
     * Moderators can delete any post
     * 
     * @param int $id Post ID
     * @return ResponseInterface
     */
    public function deletePost(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus post');
        }

        try {
            $post = $this->postModel->find($id);

            if (!$post) {
                return redirect()->back()->with('error', 'Post tidak ditemukan');
            }

            $threadId = $post->thread_id;

            // Get deletion reason
            $deleteReason = $this->request->getPost('reason') ?? 'Konten melanggar aturan forum';

            // Soft delete post (or hard delete if preferred)
            $this->postModel->update($id, [
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->id(),
                'delete_reason' => $deleteReason
            ]);

            // Update thread post count
            $remainingPosts = $this->postModel
                ->where('thread_id', $threadId)
                ->where('is_deleted', 0)
                ->countAllResults();

            $this->threadModel->update($threadId, [
                'post_count' => $remainingPosts
            ]);

            // Notify post author
            if ($post->user_id != auth()->id()) {
                $this->notificationService->sendPostDeletedNotification(
                    $post->user_id,
                    $deleteReason
                );
            }

            // Log activity
            log_message('info', "Post ID {$id} deleted by user " . auth()->id());

            return redirect()->back()->with('success', 'Post berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumController::deletePost: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus post: ' . $e->getMessage());
        }
    }

    /**
     * Delete entire thread
     * Remove thread and all its posts
     * 
     * @param int $id Thread ID
     * @return ResponseInterface
     */
    public function deleteThread(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus thread');
        }

        try {
            $thread = $this->threadModel->find($id);

            if (!$thread) {
                return redirect()->back()->with('error', 'Thread tidak ditemukan');
            }

            // Get deletion reason
            $deleteReason = $this->request->getPost('reason') ?? 'Konten melanggar aturan forum';

            // Soft delete thread
            $this->threadModel->update($id, [
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->id(),
                'delete_reason' => $deleteReason
            ]);

            // Soft delete all posts in thread
            $this->postModel
                ->where('thread_id', $id)
                ->set([
                    'is_deleted' => 1,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'deleted_by' => auth()->id()
                ])
                ->update();

            // Notify thread owner
            if ($thread->user_id != auth()->id()) {
                $this->notificationService->sendThreadDeletedNotification(
                    $thread->user_id,
                    $thread->title,
                    $deleteReason
                );
            }

            // Log activity
            log_message('info', "Thread ID {$id} ({$thread->title}) deleted by user " . auth()->id());

            return redirect()->to('/admin/forum')->with('success', 'Thread berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumController::deleteThread: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus thread: ' . $e->getMessage());
        }
    }

    /**
     * Restore deleted thread
     * Undelete thread and its posts
     * 
     * @param int $id Thread ID
     * @return ResponseInterface
     */
    public function restoreThread(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk restore thread');
        }

        try {
            $thread = $this->threadModel
                ->where('id', $id)
                ->where('is_deleted', 1)
                ->first();

            if (!$thread) {
                return redirect()->back()->with('error', 'Thread tidak ditemukan atau belum dihapus');
            }

            // Restore thread
            $this->threadModel->update($id, [
                'is_deleted' => 0,
                'deleted_at' => null,
                'deleted_by' => null,
                'delete_reason' => null,
                'restored_at' => date('Y-m-d H:i:s'),
                'restored_by' => auth()->id()
            ]);

            // Restore all posts in thread
            $this->postModel
                ->where('thread_id', $id)
                ->where('is_deleted', 1)
                ->set([
                    'is_deleted' => 0,
                    'deleted_at' => null,
                    'deleted_by' => null
                ])
                ->update();

            // Notify thread owner
            $this->notificationService->sendThreadRestoredNotification(
                $thread->user_id,
                $thread->title
            );

            // Log activity
            log_message('info', "Thread ID {$id} restored by user " . auth()->id());

            return redirect()->back()->with('success', 'Thread berhasil dipulihkan');
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumController::restoreThread: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memulihkan thread: ' . $e->getMessage());
        }
    }

    /**
     * View deleted threads/posts (moderation archive)
     * Shows all deleted content for review
     * 
     * @return string|ResponseInterface
     */
    public function deleted()
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat konten terhapus');
        }

        // Get deleted threads
        $deletedThreads = $this->threadModel
            ->select('forum_threads.*, forum_categories.name as category_name, auth_identities.secret as author_email, member_profiles.full_name as author_name, deleter_auth.secret as deleted_by_email')
            ->join('forum_categories', 'forum_categories.id = forum_threads.category_id')
            ->join('users', 'users.id = forum_threads.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('users as deleters', 'deleters.id = forum_threads.deleted_by', 'left')
            ->join('auth_identities as deleter_auth', 'deleter_auth.user_id = deleters.id AND deleter_auth.type = "email_password"', 'left')
            ->where('forum_threads.is_deleted', 1)
            ->orderBy('forum_threads.deleted_at', 'DESC')
            ->paginate(20);

        $data = [
            'title' => 'Konten Forum Terhapus',
            'deleted_threads' => $deletedThreads,
            'pager' => $this->threadModel->pager
        ];

        return view('admin/forum/deleted', $data);
    }

    /**
     * Manage forum categories
     * CRUD for forum categories
     * 
     * @return string|ResponseInterface
     */
    public function categories()
    {
        // Check permission
        if (!auth()->user()->can('forum.moderate')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengelola kategori forum');
        }

        $categories = $this->categoryModel
            ->select('forum_categories.*, COUNT(forum_threads.id) as thread_count')
            ->join('forum_threads', 'forum_threads.category_id = forum_categories.id', 'left')
            ->groupBy('forum_categories.id')
            ->orderBy('forum_categories.display_order', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Kategori Forum',
            'categories' => $categories
        ];

        return view('admin/forum/categories', $data);
    }

    /**
     * Delete comment/post (alias for deletePost)
     * Moderators can delete any comment
     * This is an alias method to match the route naming
     *
     * @param int $id Post/Comment ID
     * @return ResponseInterface
     */
    public function deleteComment(int $id): ResponseInterface
    {
        // This is an alias for deletePost to match route expectations
        // In forum systems, comments are essentially posts/replies
        return $this->deletePost($id);
    }

    /**
     * Get forum statistics (AJAX endpoint)
     * Returns statistics for dashboard widgets
     *
     * @return ResponseInterface JSON response
     */
    public function getStats(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        try {
            $stats = [
                'total_threads' => $this->threadModel->where('is_deleted', 0)->countAllResults(),
                'total_posts' => $this->postModel->where('is_deleted', 0)->countAllResults(),
                'pinned_threads' => $this->threadModel->where('is_pinned', 1)->where('is_deleted', 0)->countAllResults(),
                'locked_threads' => $this->threadModel->where('is_locked', 1)->where('is_deleted', 0)->countAllResults(),
                'deleted_threads' => $this->threadModel->where('is_deleted', 1)->countAllResults(),
                'threads_today' => $this->threadModel
                    ->where('created_at >=', date('Y-m-d 00:00:00'))
                    ->where('is_deleted', 0)
                    ->countAllResults()
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumController::getStats: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
