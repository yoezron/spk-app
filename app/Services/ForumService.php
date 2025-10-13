<?php

namespace App\Services;

use App\Models\ForumThreadModel;
use App\Models\ForumPostModel;
use App\Models\ForumCategoryModel;
use App\Models\UserModel;
use App\Services\Communication\NotificationService;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * ForumService
 * 
 * Menangani forum thread & post management
 * Termasuk create, reply, moderation, attachments, dan notifications
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ForumService
{
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
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->threadModel = new ForumThreadModel();
        $this->postModel = new ForumPostModel();
        $this->categoryModel = new ForumCategoryModel();
        $this->userModel = new UserModel();
        $this->notificationService = new NotificationService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create new forum thread
     * Creates thread with initial post
     * 
     * @param int $userId Creator user ID
     * @param array $data Thread data (category_id, title, content)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createThread(int $userId, array $data): array
    {
        $this->db->transStart();

        try {
            // Validate user exists
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                return [
                    'success' => false,
                    'message' => 'Judul dan konten thread harus diisi',
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

            // Create thread
            $threadData = [
                'category_id' => $data['category_id'] ?? null,
                'user_id' => $userId,
                'title' => $data['title'],
                'slug' => url_title($data['title'], '-', true),
                'is_pinned' => 0,
                'is_locked' => 0,
                'views_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $threadId = $this->threadModel->insert($threadData);

            if (!$threadId) {
                throw new \Exception('Gagal membuat thread: ' . json_encode($this->threadModel->errors()));
            }

            // Create first post
            $postData = [
                'thread_id' => $threadId,
                'user_id' => $userId,
                'content' => $data['content'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $postId = $this->postModel->insert($postData);

            if (!$postId) {
                throw new \Exception('Gagal membuat post');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            // Check for mentions and notify
            $this->processMentions($data['content'], $postId, $userId);

            return [
                'success' => true,
                'message' => 'Thread berhasil dibuat',
                'data' => [
                    'thread_id' => $threadId,
                    'post_id' => $postId,
                    'title' => $data['title'],
                    'slug' => $threadData['slug']
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ForumService::createThread: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat thread: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Post reply to thread
     * Adds new post to existing thread
     * 
     * @param int $threadId Thread ID
     * @param int $userId User ID
     * @param array $data Post data (content, parent_post_id)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function postReply(int $threadId, int $userId, array $data): array
    {
        $this->db->transStart();

        try {
            // Validate thread exists
            $thread = $this->threadModel->find($threadId);

            if (!$thread) {
                return [
                    'success' => false,
                    'message' => 'Thread tidak ditemukan',
                    'data' => null
                ];
            }

            // Check if thread is locked
            if ($thread->is_locked) {
                return [
                    'success' => false,
                    'message' => 'Thread ini sudah dikunci dan tidak bisa dibalas',
                    'data' => null
                ];
            }

            // Validate user
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate content
            if (empty($data['content'])) {
                return [
                    'success' => false,
                    'message' => 'Konten reply harus diisi',
                    'data' => null
                ];
            }

            // Create post
            $postData = [
                'thread_id' => $threadId,
                'user_id' => $userId,
                'parent_post_id' => $data['parent_post_id'] ?? null,
                'content' => $data['content'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $postId = $this->postModel->insert($postData);

            if (!$postId) {
                throw new \Exception('Gagal membuat reply');
            }

            // Update thread updated_at
            $this->threadModel->update($threadId, [
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            // Notify thread owner
            if ($thread->user_id != $userId) {
                $this->notificationService->send(
                    $thread->user_id,
                    'Balasan Baru di Thread Anda',
                    "{$user->username} membalas thread '{$thread->title}'",
                    [
                        'type' => 'forum_reply',
                        'thread_id' => $threadId,
                        'post_id' => $postId
                    ]
                );
            }

            // Check for mentions
            $this->processMentions($data['content'], $postId, $userId);

            return [
                'success' => true,
                'message' => 'Reply berhasil diposting',
                'data' => [
                    'post_id' => $postId,
                    'thread_id' => $threadId
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ForumService::postReply: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal posting reply: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Edit post
     * Updates existing post content
     * 
     * @param int $postId Post ID
     * @param int $userId User ID (must be post owner or admin)
     * @param string $content New content
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function editPost(int $postId, int $userId, string $content): array
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

            $user = $this->userModel->find($userId);

            // Check ownership or admin permission
            if ($post->user_id != $userId && !$user->can('forum.moderate')) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk edit post ini',
                    'data' => null
                ];
            }

            // Update post
            $this->postModel->update($postId, [
                'content' => $content,
                'is_edited' => 1,
                'edited_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Post berhasil diupdate',
                'data' => [
                    'post_id' => $postId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumService::editPost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal edit post: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete post
     * Removes post (soft delete)
     * 
     * @param int $postId Post ID
     * @param int $userId User ID (must be post owner or moderator)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deletePost(int $postId, int $userId): array
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

            $user = $this->userModel->find($userId);

            // Check ownership or moderator permission
            if ($post->user_id != $userId && !$user->can('forum.moderate')) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk hapus post ini',
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
            log_message('error', 'Error in ForumService::deletePost: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal hapus post: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Pin thread
     * Makes thread sticky at top of list
     * 
     * @param int $threadId Thread ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function pinThread(int $threadId): array
    {
        try {
            $thread = $this->threadModel->find($threadId);

            if (!$thread) {
                return [
                    'success' => false,
                    'message' => 'Thread tidak ditemukan',
                    'data' => null
                ];
            }

            $this->threadModel->update($threadId, [
                'is_pinned' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Thread berhasil di-pin',
                'data' => [
                    'thread_id' => $threadId,
                    'is_pinned' => true
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumService::pinThread: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal pin thread: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Unpin thread
     * Removes sticky status from thread
     * 
     * @param int $threadId Thread ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function unpinThread(int $threadId): array
    {
        try {
            $thread = $this->threadModel->find($threadId);

            if (!$thread) {
                return [
                    'success' => false,
                    'message' => 'Thread tidak ditemukan',
                    'data' => null
                ];
            }

            $this->threadModel->update($threadId, [
                'is_pinned' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Thread berhasil di-unpin',
                'data' => [
                    'thread_id' => $threadId,
                    'is_pinned' => false
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumService::unpinThread: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal unpin thread: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Lock thread
     * Prevents new replies to thread
     * 
     * @param int $threadId Thread ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function lockThread(int $threadId): array
    {
        try {
            $thread = $this->threadModel->find($threadId);

            if (!$thread) {
                return [
                    'success' => false,
                    'message' => 'Thread tidak ditemukan',
                    'data' => null
                ];
            }

            $this->threadModel->update($threadId, [
                'is_locked' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Thread berhasil dikunci',
                'data' => [
                    'thread_id' => $threadId,
                    'is_locked' => true
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumService::lockThread: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal lock thread: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Unlock thread
     * Allows replies to locked thread
     * 
     * @param int $threadId Thread ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function unlockThread(int $threadId): array
    {
        try {
            $thread = $this->threadModel->find($threadId);

            if (!$thread) {
                return [
                    'success' => false,
                    'message' => 'Thread tidak ditemukan',
                    'data' => null
                ];
            }

            $this->threadModel->update($threadId, [
                'is_locked' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Thread berhasil dibuka',
                'data' => [
                    'thread_id' => $threadId,
                    'is_locked' => false
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumService::unlockThread: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal unlock thread: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Upload attachment for post
     * Handles file upload and associates with post
     * 
     * @param int $postId Post ID
     * @param object $file Uploaded file
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function uploadAttachment(int $postId, $file): array
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

            // Validate file
            if (!$file->isValid() || $file->hasMoved()) {
                return [
                    'success' => false,
                    'message' => 'File tidak valid',
                    'data' => null
                ];
            }

            // Allowed types
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
            $extension = $file->getExtension();

            if (!in_array($extension, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Tipe file tidak diizinkan',
                    'data' => null
                ];
            }

            // Max size 5MB
            if ($file->getSize() > 5242880) {
                return [
                    'success' => false,
                    'message' => 'Ukuran file maksimal 5MB',
                    'data' => null
                ];
            }

            // Generate filename
            $filename = 'forum_' . $postId . '_' . time() . '_' . $file->getRandomName();
            $uploadPath = WRITEPATH . 'uploads/forum/';

            // Ensure directory exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file
            $file->move($uploadPath, $filename);

            // Save to database
            $attachmentData = [
                'post_id' => $postId,
                'filename' => $filename,
                'original_name' => $file->getClientName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $attachmentId = $this->db->table('forum_attachments')->insert($attachmentData);

            return [
                'success' => true,
                'message' => 'File berhasil diupload',
                'data' => [
                    'attachment_id' => $this->db->insertID(),
                    'filename' => $filename,
                    'original_name' => $file->getClientName()
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ForumService::uploadAttachment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal upload attachment: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Increment thread view count
     * Updates view counter when thread is accessed
     * 
     * @param int $threadId Thread ID
     * @return void
     */
    public function incrementViewCount(int $threadId): void
    {
        try {
            $this->threadModel->where('id', $threadId)
                ->set('views_count', 'views_count + 1', false)
                ->update();
        } catch (\Exception $e) {
            log_message('error', 'Error incrementing view count: ' . $e->getMessage());
        }
    }

    /**
     * Process mentions in post content
     * Detects @username mentions and sends notifications
     * 
     * @param string $content Post content
     * @param int $postId Post ID
     * @param int $authorId Author user ID
     * @return void
     */
    protected function processMentions(string $content, int $postId, int $authorId): void
    {
        try {
            // Find all @mentions using regex
            preg_match_all('/@(\w+)/', $content, $matches);

            if (empty($matches[1])) {
                return;
            }

            $usernames = array_unique($matches[1]);

            foreach ($usernames as $username) {
                // Find user by username
                $user = $this->userModel->where('username', $username)->first();

                if ($user && $user->id != $authorId) {
                    // Send notification
                    $this->notificationService->send(
                        $user->id,
                        'Anda Disebutkan di Forum',
                        "Anda disebutkan dalam sebuah post forum",
                        [
                            'type' => 'forum_mention',
                            'post_id' => $postId,
                            'author_id' => $authorId
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error processing mentions: ' . $e->getMessage());
        }
    }
}
