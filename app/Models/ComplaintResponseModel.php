<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ComplaintResponseModel
 * 
 * Model untuk mengelola tanggapan/balasan pengurus pada pengaduan
 * Mendukung sistem komunikasi antara pengadu dan pengurus
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ComplaintResponseModel extends Model
{
    protected $table            = 'complaint_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'complaint_id',
        'user_id',
        'message',
        'is_internal_note',
        'attachment_path'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'complaint_id' => 'required|is_natural_no_zero',
        'user_id' => 'required|is_natural_no_zero',
        'message' => 'required|min_length[5]',
        'is_internal_note' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'complaint_id' => [
            'required' => 'ID pengaduan harus diisi',
            'is_natural_no_zero' => 'ID pengaduan tidak valid',
        ],
        'user_id' => [
            'required' => 'ID user harus diisi',
            'is_natural_no_zero' => 'ID user tidak valid',
        ],
        'message' => [
            'required' => 'Pesan tanggapan harus diisi',
            'min_length' => 'Pesan minimal 5 karakter',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultInternalNote'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get response with complaint
     * 
     * @return object
     */
    public function withComplaint()
    {
        return $this->select('complaint_responses.*, complaints.subject as complaint_subject, complaints.status as complaint_status')
            ->join('complaints', 'complaints.id = complaint_responses.complaint_id', 'left');
    }

    /**
     * Get response with user (responder)
     * 
     * @return object
     */
    public function withUser()
    {
        return $this->select('complaint_responses.*, users.username as responder_name, users.email as responder_email')
            ->join('users', 'users.id = complaint_responses.user_id', 'left');
    }

    /**
     * Get response with complete relations
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('complaint_responses.*')
            ->select('complaints.subject as complaint_subject, complaints.status as complaint_status')
            ->select('users.username as responder_name, users.email as responder_email')
            ->join('complaints', 'complaints.id = complaint_responses.complaint_id', 'left')
            ->join('users', 'users.id = complaint_responses.user_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get responses by complaint
     * 
     * @param int $complaintId Complaint ID
     * @return object
     */
    public function byComplaint(int $complaintId)
    {
        return $this->where('complaint_id', $complaintId);
    }

    /**
     * Get responses by user (responder)
     * 
     * @param int $userId User ID
     * @return object
     */
    public function byUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Get public responses (visible to complainant)
     * 
     * @return object
     */
    public function publicResponses()
    {
        return $this->where('is_internal_note', 0);
    }

    /**
     * Get internal notes (only visible to staff)
     * 
     * @return object
     */
    public function internalNotes()
    {
        return $this->where('is_internal_note', 1);
    }

    /**
     * Get responses with attachments
     * 
     * @return object
     */
    public function withAttachments()
    {
        return $this->where('attachment_path IS NOT NULL');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get responses for a complaint ordered by date
     * 
     * @param int $complaintId Complaint ID
     * @param bool $includeInternal Include internal notes
     * @return array
     */
    public function getComplaintResponses(int $complaintId, bool $includeInternal = false): array
    {
        $builder = $this->withUser()
            ->where('complaint_id', $complaintId);

        if (!$includeInternal) {
            $builder->where('is_internal_note', 0);
        }

        return $builder->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get latest response for a complaint
     * 
     * @param int $complaintId Complaint ID
     * @return object|null
     */
    public function getLatestResponse(int $complaintId)
    {
        return $this->withUser()
            ->where('complaint_id', $complaintId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Get responses by responder with pagination
     * 
     * @param int $userId User ID
     * @param int $limit Records per page
     * @return array
     */
    public function getResponderActivity(int $userId, int $limit = 20): array
    {
        return $this->withComplaint()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit);
    }

    /**
     * Search responses by keyword
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->like('message', $keyword);
    }

    /**
     * Get recent responses
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function recent(int $limit = 10): array
    {
        return $this->withComplete()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count responses by complaint
     * 
     * @param int $complaintId Complaint ID
     * @return int
     */
    public function countByComplaint(int $complaintId): int
    {
        return $this->where('complaint_id', $complaintId)
            ->where('is_internal_note', 0)
            ->countAllResults();
    }

    /**
     * Count internal notes by complaint
     * 
     * @param int $complaintId Complaint ID
     * @return int
     */
    public function countInternalNotes(int $complaintId): int
    {
        return $this->where('complaint_id', $complaintId)
            ->where('is_internal_note', 1)
            ->countAllResults();
    }

    /**
     * Count responses by user
     * 
     * @param int $userId User ID
     * @return int
     */
    public function countByUser(int $userId): int
    {
        return $this->where('user_id', $userId)->countAllResults();
    }

    /**
     * Get response statistics by user
     * 
     * @param int $userId User ID
     * @return object
     */
    public function getUserStats(int $userId)
    {
        return $this->select('
                COUNT(*) as total_responses,
                COUNT(DISTINCT complaint_id) as complaints_handled,
                SUM(CASE WHEN is_internal_note = 1 THEN 1 ELSE 0 END) as internal_notes_count,
                SUM(CASE WHEN is_internal_note = 0 THEN 1 ELSE 0 END) as public_responses_count
            ')
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get top responders
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getTopResponders(int $limit = 10): array
    {
        return $this->select('users.username, users.email, COUNT(complaint_responses.id) as response_count')
            ->join('users', 'users.id = complaint_responses.user_id')
            ->groupBy('complaint_responses.user_id')
            ->orderBy('response_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get average response time per user
     * 
     * @return array
     */
    public function getAverageResponseTime(): array
    {
        return $this->select('
                users.username,
                AVG(TIMESTAMPDIFF(HOUR, complaints.created_at, complaint_responses.created_at)) as avg_response_hours
            ')
            ->join('users', 'users.id = complaint_responses.user_id')
            ->join('complaints', 'complaints.id = complaint_responses.complaint_id')
            ->where('complaint_responses.created_at = (
                SELECT MIN(created_at) 
                FROM complaint_responses cr 
                WHERE cr.complaint_id = complaint_responses.complaint_id
            )')
            ->groupBy('complaint_responses.user_id')
            ->orderBy('avg_response_hours', 'ASC')
            ->findAll();
    }

    /**
     * Get response activity by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array
     */
    public function getActivityByDateRange(string $startDate, string $endDate): array
    {
        return $this->select('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Add response to complaint
     * 
     * @param int $complaintId Complaint ID
     * @param int $userId User ID (responder)
     * @param string $message Response message
     * @param bool $isInternalNote Is internal note
     * @param string|null $attachmentPath Attachment file path
     * @return int|false Response ID or false on failure
     */
    public function addResponse(
        int $complaintId,
        int $userId,
        string $message,
        bool $isInternalNote = false,
        ?string $attachmentPath = null
    ) {
        $data = [
            'complaint_id' => $complaintId,
            'user_id' => $userId,
            'message' => $message,
            'is_internal_note' => $isInternalNote ? 1 : 0,
            'attachment_path' => $attachmentPath,
        ];

        return $this->insert($data);
    }

    /**
     * Update response message
     * 
     * @param int $responseId Response ID
     * @param string $message New message
     * @return bool
     */
    public function updateMessage(int $responseId, string $message): bool
    {
        return $this->update($responseId, ['message' => $message]);
    }

    /**
     * Toggle internal note status
     * 
     * @param int $responseId Response ID
     * @return bool
     */
    public function toggleInternalNote(int $responseId): bool
    {
        $response = $this->find($responseId);

        if (!$response) {
            return false;
        }

        $newStatus = $response->is_internal_note ? 0 : 1;
        return $this->update($responseId, ['is_internal_note' => $newStatus]);
    }

    /**
     * Add attachment to response
     * 
     * @param int $responseId Response ID
     * @param string $attachmentPath Attachment file path
     * @return bool
     */
    public function addAttachment(int $responseId, string $attachmentPath): bool
    {
        return $this->update($responseId, ['attachment_path' => $attachmentPath]);
    }

    /**
     * Remove attachment from response
     * 
     * @param int $responseId Response ID
     * @return bool
     */
    public function removeAttachment(int $responseId): bool
    {
        return $this->update($responseId, ['attachment_path' => null]);
    }

    /**
     * Check if user can edit response
     * 
     * @param int $responseId Response ID
     * @param int $userId User ID
     * @return bool
     */
    public function canEdit(int $responseId, int $userId): bool
    {
        $response = $this->find($responseId);

        if (!$response) {
            return false;
        }

        // User can only edit their own responses
        return $response->user_id === $userId;
    }

    /**
     * Get unread responses for complaint owner
     * 
     * @param int $complaintId Complaint ID
     * @param string $lastReadAt Last read timestamp
     * @return array
     */
    public function getUnreadResponses(int $complaintId, string $lastReadAt): array
    {
        return $this->withUser()
            ->where('complaint_id', $complaintId)
            ->where('is_internal_note', 0)
            ->where('created_at >', $lastReadAt)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get response with complaint and user details
     * 
     * @param int $responseId Response ID
     * @return object|null
     */
    public function getDetailedResponse(int $responseId)
    {
        return $this->withComplete()
            ->find($responseId);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set default internal note status
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultInternalNote(array $data): array
    {
        if (!isset($data['data']['is_internal_note'])) {
            $data['data']['is_internal_note'] = 0;
        }

        return $data;
    }
}
