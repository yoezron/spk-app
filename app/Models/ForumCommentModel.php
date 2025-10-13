<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ForumCommentModel
 * 
 * Model untuk mengelola komentar/balasan di thread forum
 * Digunakan untuk fitur komentar dan diskusi nested di forum SPK
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ForumCommentModel extends Model
{
    protected $table            = 'forum_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'thread_id',
        'user_id',
        'parent_id',
        'content',
        'is_answer',
        'likes_count',
        'is_edited',
        'edited_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'thread_id' => 'required|integer|is_not_unique[forum_threads.id]',
        'user_id'   => 'required|integer|is_not_unique[users.id]',
        'parent_id' => 'permit_empty|integer|is_not_unique[forum_comments.id]',
        'content'   => 'required|min_length[3]',
        'is_answer' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'thread_id' => [
            'required'      => 'Thread ID harus ada',
            'is_not_unique' => 'Thread tidak ditemukan',
        ],
        'user_id' => [
            'required'      => 'User ID harus ada',
            'is_not_unique' => 'User tidak ditemukan',
        ],
        'parent_id' => [
            'is_not_unique' => 'Parent comment tidak ditemukan',
        ],
        'content' => [
            'required'   => 'Konten komentar harus diisi',
            'min_length' => 'Konten minimal 3 karakter',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $beforeUpdate   = ['markAsEdited'];

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Mark comment as edited
     * 
     * @param array $data
     * @return array
     */
    protected function markAsEdited(array $data)
    {
        if (isset($data['data']['content'])) {
            $data['data']['is_edited'] = 1;
            $data['data']['edited_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get comment with author data
     * 
     * @return object
     */
    public function withAuthor()
    {
        return $this->select('forum_comments.*, users.username')
            ->select('member_profiles.full_name as author_name, member_profiles.photo_path as author_photo')
            ->join('users', 'users.id = forum_comments.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left');
    }

    /**
     * Get comment with thread data
     * 
     * @return object
     */
    public function withThread()
    {
        return $this->select('forum_comments.*, forum_threads.title as thread_title, forum_threads.slug as thread_slug')
            ->join('forum_threads', 'forum_threads.id = forum_comments.thread_id', 'left');
    }

    /**
     * Get comment with parent comment data
     * 
     * @return object
     */
    public function withParent()
    {
        return $this->select('forum_comments.*')
            ->select('parent.content as parent_content')
            ->select('parent_user.username as parent_author_username')
            ->select('parent_member.full_name as parent_author_name')
            ->join('forum_comments as parent', 'parent.id = forum_comments.parent_id', 'left')
            ->join('users as parent_user', 'parent_user.id = parent.user_id', 'left')
            ->join('member_profiles as parent_member', 'parent_member.user_id = parent_user.id', 'left');
    }

    /**
     * Get comment with replies count
     * 
     * @return object
     */
    public function withRepliesCount()
    {
        return $this->select('forum_comments.*')
            ->select('(SELECT COUNT(*) FROM forum_comments as replies WHERE replies.parent_id = forum_comments.id AND replies.deleted_at IS NULL) as replies_count');
    }

    /**
     * Get comment with complete data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('forum_comments.*')
            ->select('users.username, member_profiles.full_name as author_name, member_profiles.photo_path as author_photo')
            ->select('forum_threads.title as thread_title, forum_threads.slug as thread_slug')
            ->select('(SELECT COUNT(*) FROM forum_comments as replies WHERE replies.parent_id = forum_comments.id AND replies.deleted_at IS NULL) as replies_count')
            ->join('users', 'users.id = forum_comments.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('forum_threads', 'forum_threads.id = forum_comments.thread_id', 'left');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get comments by thread
     * 
     * @param int $threadId
     * @param bool $onlyParent Get only parent comments (not replies)
     * @return array
     */
    public function getByThread($threadId, $onlyParent = false)
    {
        $builder = $this->where('thread_id', $threadId);

        if ($onlyParent) {
            $builder->where('parent_id', null);
        }

        return $builder->orderBy('created_at', 'ASC')->findAll();
    }

    /**
     * Get replies for a comment
     * 
     * @param int $parentId
     * @return array
     */
    public function getReplies($parentId)
    {
        return $this->where('parent_id', $parentId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get comments by user
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
     * Get recent comments
     * 
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get popular comments (most liked)
     * 
     * @param int $limit
     * @return array
     */
    public function getPopular($limit = 10)
    {
        return $this->orderBy('likes_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get marked answers
     * 
     * @param int|null $threadId
     * @return array
     */
    public function getAnswers($threadId = null)
    {
        $builder = $this->where('is_answer', 1);

        if ($threadId) {
            $builder->where('thread_id', $threadId);
        }

        return $builder->findAll();
    }

    /**
     * Check if user has commented on thread
     * 
     * @param int $threadId
     * @param int $userId
     * @return bool
     */
    public function hasUserCommented($threadId, $userId)
    {
        return $this->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
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
     * Mark comment as answer
     * 
     * @param int $id
     * @return bool
     */
    public function markAsAnswer($id)
    {
        // First, unmark other answers in the same thread
        $comment = $this->find($id);
        if ($comment) {
            $this->where('thread_id', $comment->thread_id)
                ->set('is_answer', 0)
                ->update();
        }

        // Mark this comment as answer
        return $this->update($id, ['is_answer' => 1]);
    }

    /**
     * Unmark comment as answer
     * 
     * @param int $id
     * @return bool
     */
    public function unmarkAsAnswer($id)
    {
        return $this->update($id, ['is_answer' => 0]);
    }

    /**
     * Get comment with all nested replies
     * Recursive function to get nested structure
     * 
     * @param int $threadId
     * @return array
     */
    public function getThreadedComments($threadId)
    {
        // Get parent comments
        $parents = $this->where('thread_id', $threadId)
            ->where('parent_id', null)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Get replies for each parent
        foreach ($parents as $parent) {
            $parent->replies = $this->getNestedReplies($parent->id);
        }

        return $parents;
    }

    /**
     * Get nested replies recursively
     * 
     * @param int $parentId
     * @return array
     */
    private function getNestedReplies($parentId)
    {
        $replies = $this->where('parent_id', $parentId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        foreach ($replies as $reply) {
            $reply->replies = $this->getNestedReplies($reply->id);
        }

        return $replies;
    }

    /**
     * Delete comment and its replies
     * 
     * @param int $id
     * @param bool $purge Permanent delete
     * @return bool
     */
    public function deleteWithReplies($id, $purge = false)
    {
        // Get all child comments
        $replies = $this->getReplies($id);

        // Delete each reply recursively
        foreach ($replies as $reply) {
            $this->deleteWithReplies($reply->id, $purge);
        }

        // Delete the parent comment
        return $purge ? $this->delete($id, true) : $this->delete($id);
    }

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get total comments count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->countAllResults();
    }

    /**
     * Get comments count by thread
     * 
     * @param int $threadId
     * @return int
     */
    public function getCountByThread($threadId)
    {
        return $this->where('thread_id', $threadId)->countAllResults();
    }

    /**
     * Get comments count by user
     * 
     * @param int $userId
     * @return int
     */
    public function getCountByUser($userId)
    {
        return $this->where('user_id', $userId)->countAllResults();
    }

    /**
     * Get most active commenters
     * 
     * @param int $limit
     * @return array
     */
    public function getMostActiveCommenters($limit = 10)
    {
        return $this->select('users.username, member_profiles.full_name, COUNT(forum_comments.id) as comments_count')
            ->join('users', 'users.id = forum_comments.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->groupBy('forum_comments.user_id')
            ->orderBy('comments_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get average comments per thread
     * 
     * @return float
     */
    public function getAveragePerThread()
    {
        $totalComments = $this->countAllResults(false);
        $totalThreads = $this->db->table('forum_threads')->countAllResults();

        return $totalThreads > 0 ? round($totalComments / $totalThreads, 2) : 0;
    }

    /**
     * Get comments activity by date
     * 
     * @param int $days
     * @return array
     */
    public function getActivityByDate($days = 30)
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));

        return $this->select('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
