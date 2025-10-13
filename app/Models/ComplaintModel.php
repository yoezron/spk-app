<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ComplaintModel
 * 
 * Model untuk mengelola pengaduan/ticket dari anggota dan publik
 * Mendukung sistem ticketing dengan assignment, prioritas, dan status tracking
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ComplaintModel extends Model
{
    protected $table            = 'complaints';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id',
        'user_id',
        'sender_name',
        'sender_email',
        'sender_phone',
        'subject',
        'message',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'subject' => 'required|min_length[5]|max_length[255]',
        'message' => 'required|min_length[10]',
        'priority' => 'permit_empty|in_list[low,medium,high,urgent]',
        'status' => 'permit_empty|in_list[open,in_progress,resolved,closed]',
        'sender_email' => 'permit_empty|valid_email',
        'sender_phone' => 'permit_empty|min_length[10]|max_length[15]',
    ];

    protected $validationMessages = [
        'subject' => [
            'required'   => 'Subjek pengaduan harus diisi',
            'min_length' => 'Subjek minimal 5 karakter',
            'max_length' => 'Subjek maksimal 255 karakter',
        ],
        'message' => [
            'required'   => 'Isi pengaduan harus diisi',
            'min_length' => 'Isi pengaduan minimal 10 karakter',
        ],
        'priority' => [
            'in_list' => 'Prioritas tidak valid',
        ],
        'status' => [
            'in_list' => 'Status tidak valid',
        ],
        'sender_email' => [
            'valid_email' => 'Format email tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultStatus', 'setDefaultPriority'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get complaint with user (sender)
     * 
     * @return object
     */
    public function withUser()
    {
        return $this->select('complaints.*, users.username, users.email as user_email')
            ->join('users', 'users.id = complaints.user_id', 'left');
    }

    /**
     * Get complaint with category
     * 
     * @return object
     */
    public function withCategory()
    {
        return $this->select('complaints.*, complaint_categories.name as category_name, complaint_categories.icon as category_icon')
            ->join('complaint_categories', 'complaint_categories.id = complaints.category_id', 'left');
    }

    /**
     * Get complaint with assigned user (pengurus)
     * 
     * @return object
     */
    public function withAssignedTo()
    {
        return $this->select('complaints.*, assigned_users.username as assigned_to_name')
            ->join('users as assigned_users', 'assigned_users.id = complaints.assigned_to', 'left');
    }

    /**
     * Get complaint with responses count
     * 
     * @return object
     */
    public function withResponsesCount()
    {
        return $this->select('complaints.*')
            ->select('(SELECT COUNT(*) FROM complaint_responses WHERE complaint_responses.complaint_id = complaints.id) as responses_count');
    }

    /**
     * Get complaint with complete relations
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('complaints.*')
            ->select('users.username, users.email as user_email')
            ->select('complaint_categories.name as category_name, complaint_categories.icon as category_icon')
            ->select('assigned_users.username as assigned_to_name')
            ->select('(SELECT COUNT(*) FROM complaint_responses WHERE complaint_responses.complaint_id = complaints.id) as responses_count')
            ->join('users', 'users.id = complaints.user_id', 'left')
            ->join('complaint_categories', 'complaint_categories.id = complaints.category_id', 'left')
            ->join('users as assigned_users', 'assigned_users.id = complaints.assigned_to', 'left');
    }

    // ========================================
    // SCOPES - FILTERING BY STATUS
    // ========================================

    /**
     * Get open complaints
     * 
     * @return object
     */
    public function open()
    {
        return $this->where('status', 'open');
    }

    /**
     * Get in progress complaints
     * 
     * @return object
     */
    public function inProgress()
    {
        return $this->where('status', 'in_progress');
    }

    /**
     * Get resolved complaints
     * 
     * @return object
     */
    public function resolved()
    {
        return $this->where('status', 'resolved');
    }

    /**
     * Get closed complaints
     * 
     * @return object
     */
    public function closed()
    {
        return $this->where('status', 'closed');
    }

    /**
     * Get complaints by status
     * 
     * @param string $status Status value
     * @return object
     */
    public function byStatus(string $status)
    {
        return $this->where('status', $status);
    }

    // ========================================
    // SCOPES - FILTERING BY PRIORITY
    // ========================================

    /**
     * Get urgent complaints
     * 
     * @return object
     */
    public function urgent()
    {
        return $this->where('priority', 'urgent');
    }

    /**
     * Get high priority complaints
     * 
     * @return object
     */
    public function highPriority()
    {
        return $this->where('priority', 'high');
    }

    /**
     * Get complaints by priority
     * 
     * @param string $priority Priority value
     * @return object
     */
    public function byPriority(string $priority)
    {
        return $this->where('priority', $priority);
    }

    // ========================================
    // SCOPES - FILTERING BY ASSIGNMENT
    // ========================================

    /**
     * Get unassigned complaints
     * 
     * @return object
     */
    public function unassigned()
    {
        return $this->where('assigned_to IS NULL');
    }

    /**
     * Get assigned complaints
     * 
     * @return object
     */
    public function assigned()
    {
        return $this->where('assigned_to IS NOT NULL');
    }

    /**
     * Get complaints assigned to specific user
     * 
     * @param int $userId User ID
     * @return object
     */
    public function assignedTo(int $userId)
    {
        return $this->where('assigned_to', $userId);
    }

    // ========================================
    // SCOPES - FILTERING BY SOURCE
    // ========================================

    /**
     * Get complaints from members (logged in users)
     * 
     * @return object
     */
    public function fromMembers()
    {
        return $this->where('user_id IS NOT NULL');
    }

    /**
     * Get complaints from public (non-members)
     * 
     * @return object
     */
    public function fromPublic()
    {
        return $this->where('user_id IS NULL');
    }

    /**
     * Get complaints by category
     * 
     * @param int $categoryId Category ID
     * @return object
     */
    public function byCategory(int $categoryId)
    {
        return $this->where('category_id', $categoryId);
    }

    /**
     * Get complaints by user
     * 
     * @param int $userId User ID
     * @return object
     */
    public function byUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Search complaints by keyword
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('subject', $keyword)
            ->orLike('message', $keyword)
            ->orLike('sender_name', $keyword)
            ->orLike('sender_email', $keyword)
            ->groupEnd();
    }

    /**
     * Get recent complaints
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function recent(int $limit = 10): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get pending complaints (open or in_progress)
     * 
     * @return object
     */
    public function pending()
    {
        return $this->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Get completed complaints (resolved or closed)
     * 
     * @return object
     */
    public function completed()
    {
        return $this->whereIn('status', ['resolved', 'closed']);
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count complaints by status
     * 
     * @return array
     */
    public function countByStatus(): array
    {
        $result = $this->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $stats = [
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->status] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Count complaints by priority
     * 
     * @return array
     */
    public function countByPriority(): array
    {
        $result = $this->select('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->findAll();

        $stats = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
            'urgent' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->priority] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Count complaints by category
     * 
     * @return array
     */
    public function countByCategory(): array
    {
        return $this->select('complaint_categories.name, COUNT(complaints.id) as count')
            ->join('complaint_categories', 'complaint_categories.id = complaints.category_id', 'left')
            ->groupBy('complaints.category_id')
            ->findAll();
    }

    /**
     * Get response time statistics
     * 
     * @return object
     */
    public function getResponseTimeStats()
    {
        return $this->select('
                AVG(TIMESTAMPDIFF(HOUR, complaints.created_at, 
                    (SELECT MIN(created_at) FROM complaint_responses 
                     WHERE complaint_responses.complaint_id = complaints.id)
                )) as avg_response_time_hours,
                MIN(TIMESTAMPDIFF(HOUR, complaints.created_at, 
                    (SELECT MIN(created_at) FROM complaint_responses 
                     WHERE complaint_responses.complaint_id = complaints.id)
                )) as min_response_time_hours,
                MAX(TIMESTAMPDIFF(HOUR, complaints.created_at, 
                    (SELECT MIN(created_at) FROM complaint_responses 
                     WHERE complaint_responses.complaint_id = complaints.id)
                )) as max_response_time_hours
            ')
            ->where('status !=', 'open')
            ->first();
    }

    /**
     * Get resolution statistics
     * 
     * @return object
     */
    public function getResolutionStats()
    {
        return $this->select('
                COUNT(*) as total_resolved,
                AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as avg_resolution_days,
                MIN(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as min_resolution_days,
                MAX(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as max_resolution_days
            ')
            ->where('status', 'resolved')
            ->orWhere('status', 'closed')
            ->first();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Assign complaint to user (pengurus)
     * 
     * @param int $complaintId Complaint ID
     * @param int $userId User ID to assign
     * @return bool
     */
    public function assignComplaint(int $complaintId, int $userId): bool
    {
        $data = [
            'assigned_to' => $userId,
            'status' => 'in_progress',
        ];

        return $this->update($complaintId, $data);
    }

    /**
     * Unassign complaint
     * 
     * @param int $complaintId Complaint ID
     * @return bool
     */
    public function unassignComplaint(int $complaintId): bool
    {
        return $this->update($complaintId, ['assigned_to' => null]);
    }

    /**
     * Mark complaint as resolved
     * 
     * @param int $complaintId Complaint ID
     * @return bool
     */
    public function resolveComplaint(int $complaintId): bool
    {
        $data = [
            'status' => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($complaintId, $data);
    }

    /**
     * Close complaint
     * 
     * @param int $complaintId Complaint ID
     * @return bool
     */
    public function closeComplaint(int $complaintId): bool
    {
        $data = [
            'status' => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
        ];

        // If not yet resolved, set resolved_at as well
        $complaint = $this->find($complaintId);
        if ($complaint && !$complaint->resolved_at) {
            $data['resolved_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($complaintId, $data);
    }

    /**
     * Reopen complaint
     * 
     * @param int $complaintId Complaint ID
     * @return bool
     */
    public function reopenComplaint(int $complaintId): bool
    {
        $data = [
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ];

        return $this->update($complaintId, $data);
    }

    /**
     * Update complaint priority
     * 
     * @param int $complaintId Complaint ID
     * @param string $priority Priority level
     * @return bool
     */
    public function updatePriority(int $complaintId, string $priority): bool
    {
        $validPriorities = ['low', 'medium', 'high', 'urgent'];

        if (!in_array($priority, $validPriorities)) {
            return false;
        }

        return $this->update($complaintId, ['priority' => $priority]);
    }

    /**
     * Get complaint with responses
     * 
     * @param int $complaintId Complaint ID
     * @return object|null
     */
    public function getWithResponses(int $complaintId)
    {
        $complaint = $this->withComplete()->find($complaintId);

        if (!$complaint) {
            return null;
        }

        // Get responses
        $responseModel = new \App\Models\ComplaintResponseModel();
        $complaint->responses = $responseModel->withUser()
            ->where('complaint_id', $complaintId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $complaint;
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set default status before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultStatus(array $data): array
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'open';
        }

        return $data;
    }

    /**
     * Set default priority before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultPriority(array $data): array
    {
        if (!isset($data['data']['priority'])) {
            $data['data']['priority'] = 'medium';
        }

        return $data;
    }
}
