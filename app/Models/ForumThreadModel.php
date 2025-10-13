<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ForumThreadModel
 * 
 * Model untuk mengelola thread/topik diskusi forum
 * Digunakan untuk fitur forum diskusi anggota SPK
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ForumThreadModel extends Model
{
    protected $table            = 'forum_threads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'content',
        'is_pinned',
        'is_locked',
        'is_solved',
        'views_count',
        'likes_count',
        'last_activity_at',
        'last_comment_user_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'category_id' => 'required|integer|is_not_unique[forum_categories.id]',
        'user_id'     => 'required|integer|is_not_unique[users.id]',
        'title'       => 'required|min_length[5]|max_length[255]',
        'content'     => 'required|min_length[10]',
        'slug'        => 'permit_empty|max_length[255]|is_unique[forum_threads.slug,id,{id}]',
        'is_pinned'   => 'permit_empty|in_list[0,1]',
        'is_locked'   => 'permit_empty|in_list[0,1]',
        'is_solved'   => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'category_id' => [
            'required'      => 'Kategori forum harus dipilih',
            'is_not_unique' => 'Kategori tidak ditemukan',
        ],
        'user_id' => [
            'required'      => 'User ID harus ada',
            'is_not_unique' => 'User tidak ditemukan',
        ],
        'title' => [
            'required'   => 'Judul thread harus diisi',
            'min_length' => 'Judul minimal 5 karakter',
            'max_length' => 'Judul maksimal 255 karakter',
        ],
        'content' => [
            'required'   => 'Konten thread harus diisi',
            'min_length' => 'Konten minimal 10 karakter',
        ],
        'slug' => [
            'is_unique' => 'Slug sudah digunakan',
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
     * Generate slug from title
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data)
    {
        if (isset($data['data']['title']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = url_title($data['data']['title'], '-', true) . '-' . time();
        }
        return $data;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get thread with author data
     * 
     * @return object
     */
    public function withAuthor()
    {
        return $this->select('forum_threads.*, users.username')
            ->select('member_profiles.full_name as author_name, member_profiles.photo_path as author_photo')
            ->join('users', 'users.id = forum_threads.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');
    }

    /**
     * Get thread with category data
     * 
     * @return object
     */
    public function withCategory()
    {
        return $this->select('forum_threads.*, forum_categories.name as category_name, forum_categories.slug as category_slug, forum_categories.icon as category_icon')
            ->join('forum_categories', 'forum_categories.id = forum_threads.category_id', 'left');
    }

    /**
     * Get thread with last commenter data
     * 
     * @return object
     */
    public function withLastCommenter()
    {
        return $this->select('forum_threads.*')
            ->select('last_user.username as last_commenter_username')
            ->select('last_member.full_name as last_commenter_name')
            ->join('users as last_user', 'last_user.id = forum_threads.last_comment_user_id', 'left')
            ->join('member_profiles as last_member', 'last_member.user_id = last_user.id', 'left');
    }

    /**
     * Get thread with comments count
     * 
     * @return object
     */
    public function withCommentsCount()
    {
        return $this->select('forum_threads.*')
            ->select('(SELECT COUNT(*) FROM forum_comments WHERE forum_comments.thread_id = forum_threads.id AND forum_comments.deleted_at IS NULL) as comments_count');
    }

    /**
     * Get thread with complete data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('forum_threads.*')
            ->select('users.username, member_profiles.full_name as author_name, member_profiles.photo_path as author_photo')
            ->select('forum_categories.name as category_name, forum_categories.slug as category_slug, forum_categories.icon as category_icon')
            ->select('(SELECT COUNT(*) FROM forum_comments WHERE forum_comments.thread_id = forum_threads.id AND forum_comments.deleted_at IS NULL) as comments_count')
            ->join('users', 'users.id = forum_threads.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('forum_categories', 'forum_categories.id = forum_threads.category_id', 'left');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get active threads (not deleted)
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->orderBy('is_pinned', 'DESC')
            ->orderBy('last_activity_at', 'DESC')
            ->findAll();
    }

    /**
     * Get pinned threads
     * 
     * @return array
     */
    public function getPinned()
    {
        return $this->where('is_pinned', 1)
            ->orderBy('last_activity_at', 'DESC')
            ->findAll();
    }

    /**
     * Get threads by category
     * 
     * @param int $categoryId
     * @return array
     */
    public function getByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
            ->orderBy('is_pinned', 'DESC')
            ->orderBy('last_activity_at', 'DESC')
            ->findAll();
    }

    /**
     * Get threads by user
     * 
     * @param int $userId
     * @return array
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get popular threads (most viewed)
     * 
     * @param int $limit
     * @return array
     */
    public function getPopular($limit = 10)
    {
        return $this->orderBy('views_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get trending threads (most active recently)
     * 
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getTrending($limit = 10, $days = 7)
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $this->where('last_activity_at >=', $since)
            ->orderBy('views_count + (likes_count * 2)', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get unsolved threads
     * 
     * @return array
     */
    public function getUnsolved()
    {
        return $this->where('is_solved', 0)
            ->where('is_locked', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Search threads
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        return $this->like('title', $keyword)
            ->orLike('content', $keyword)
            ->orderBy('last_activity_at', 'DESC')
            ->findAll();
    }

    /**
     * Get thread by slug
     * 
     * @param string $slug
     * @return object|null
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Increment views count
     * 
     * @param int $id
     * @return bool
     */
    public function incrementViews($id)
    {
        return $this->set('views_count', 'views_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Increment likes count
     * 
     * @param int $id
     * @return bool
     */
    public function incrementLikes($id)
    {
        return $this->set('likes_count', 'likes_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Decrement likes count
     * 
     * @param int $id
     * @return bool
     */
    public function decrementLikes($id)
    {
        return $this->set('likes_count', 'likes_count - 1', false)
            ->where('id', $id)
            ->where('likes_count >', 0)
            ->update();
    }

    /**
     * Update last activity
     * 
     * @param int $id
     * @param int|null $userId
     * @return bool
     */
    public function updateLastActivity($id, $userId = null)
    {
        $data = ['last_activity_at' => date('Y-m-d H:i:s')];

        if ($userId) {
            $data['last_comment_user_id'] = $userId;
        }

        return $this->update($id, $data);
    }

    /**
     * Pin thread
     * 
     * @param int $id
     * @return bool
     */
    public function pin($id)
    {
        return $this->update($id, ['is_pinned' => 1]);
    }

    /**
     * Unpin thread
     * 
     * @param int $id
     * @return bool
     */
    public function unpin($id)
    {
        return $this->update($id, ['is_pinned' => 0]);
    }

    /**
     * Lock thread
     * 
     * @param int $id
     * @return bool
     */
    public function lock($id)
    {
        return $this->update($id, ['is_locked' => 1]);
    }

    /**
     * Unlock thread
     * 
     * @param int $id
     * @return bool
     */
    public function unlock($id)
    {
        return $this->update($id, ['is_locked' => 0]);
    }

    /**
     * Mark as solved
     * 
     * @param int $id
     * @return bool
     */
    public function markSolved($id)
    {
        return $this->update($id, ['is_solved' => 1]);
    }

    /**
     * Mark as unsolved
     * 
     * @param int $id
     * @return bool
     */
    public function markUnsolved($id)
    {
        return $this->update($id, ['is_solved' => 0]);
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total threads count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get threads count by category
     * 
     * @param int $categoryId
     * @return int
     */
    public function getCountByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)->countAllResults();
    }

    /**
     * Get threads count by user
     * 
     * @param int $userId
     * @return int
     */
    public function getCountByUser($userId)
    {
        return $this->where('user_id', $userId)->countAllResults();
    }

    /**
     * Get total views
     * 
     * @return int
     */
    public function getTotalViews()
    {
        $result = $this->selectSum('views_count')->first();
        return $result ? (int) $result->views_count : 0;
    }

    /**
     * Get threads distribution by category
     * 
     * @return array
     */
    public function getCategoryDistribution()
    {
        return $this->select('forum_categories.name, COUNT(forum_threads.id) as total')
            ->join('forum_categories', 'forum_categories.id = forum_threads.category_id', 'left')
            ->groupBy('forum_threads.category_id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get most active users
     * 
     * @param int $limit
     * @return array
     */
    public function getMostActiveUsers($limit = 10)
    {
        return $this->select('users.username, member_profiles.full_name, COUNT(forum_threads.id) as threads_count')
            ->join('users', 'users.id = forum_threads.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->groupBy('forum_threads.user_id')
            ->orderBy('threads_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
