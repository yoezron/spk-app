<?php

namespace App\Services;

use App\Models\ComplaintModel;
use App\Models\ComplaintCategoryModel;
use App\Models\ComplaintResponseModel;
use App\Models\UserModel;
use App\Services\Communication\NotificationService;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * ComplaintService
 * 
 * Menangani complaint/ticket system management
 * Termasuk submit, assign, respond, status management, dan statistics
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ComplaintService
{
    /**
     * @var ComplaintModel
     */
    protected $complaintModel;

    /**
     * @var ComplaintCategoryModel
     */
    protected $categoryModel;

    /**
     * @var ComplaintResponseModel
     */
    protected $responseModel;

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
        $this->complaintModel = new ComplaintModel();
        $this->categoryModel = new ComplaintCategoryModel();
        $this->responseModel = new ComplaintResponseModel();
        $this->userModel = new UserModel();
        $this->notificationService = new NotificationService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Submit new complaint
     * Creates complaint from member or public
     * 
     * @param array $data Complaint data
     * @param int|null $userId User ID if logged in (null for public)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function submitComplaint(array $data, ?int $userId = null): array
    {
        $this->db->transStart();

        try {
            // Validate required fields
            if (empty($data['subject']) || empty($data['message'])) {
                return [
                    'success' => false,
                    'message' => 'Subjek dan isi pengaduan harus diisi',
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

            // For public complaints, validate contact info
            if (!$userId) {
                if (empty($data['sender_name']) || empty($data['sender_email'])) {
                    return [
                        'success' => false,
                        'message' => 'Nama dan email harus diisi untuk pengaduan publik',
                        'data' => null
                    ];
                }
            }

            // Generate ticket number
            $ticketNumber = $this->generateTicketNumber();

            // Prepare complaint data
            $complaintData = [
                'ticket_number' => $ticketNumber,
                'category_id' => $data['category_id'] ?? null,
                'user_id' => $userId,
                'sender_name' => $data['sender_name'] ?? null,
                'sender_email' => $data['sender_email'] ?? null,
                'sender_phone' => $data['sender_phone'] ?? null,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert complaint
            $complaintId = $this->complaintModel->insert($complaintData);

            if (!$complaintId) {
                throw new \Exception('Gagal menyimpan pengaduan: ' . json_encode($this->complaintModel->errors()));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            // Notify admin/pengurus about new complaint
            $this->notifyNewComplaint($complaintId, $ticketNumber);

            return [
                'success' => true,
                'message' => 'Pengaduan berhasil dikirim',
                'data' => [
                    'complaint_id' => $complaintId,
                    'ticket_number' => $ticketNumber,
                    'subject' => $data['subject'],
                    'status' => 'open'
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ComplaintService::submitComplaint: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim pengaduan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Assign complaint to pengurus
     * Assigns complaint handler
     * 
     * @param int $complaintId Complaint ID
     * @param int $assignedTo User ID of pengurus
     * @param int $assignedBy User ID of assigner
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function assignComplaint(int $complaintId, int $assignedTo, int $assignedBy): array
    {
        $this->db->transStart();

        try {
            $complaint = $this->complaintModel->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate assignee exists
            $assignee = $this->userModel->find($assignedTo);

            if (!$assignee) {
                return [
                    'success' => false,
                    'message' => 'User yang ditugaskan tidak ditemukan',
                    'data' => null
                ];
            }

            // Update complaint
            $this->complaintModel->update($complaintId, [
                'assigned_to' => $assignedTo,
                'status' => 'in_progress',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add system response note
            $this->responseModel->insert([
                'complaint_id' => $complaintId,
                'user_id' => $assignedBy,
                'message' => sprintf('Pengaduan ditugaskan kepada %s', $assignee->username),
                'is_internal_note' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            // Notify assignee
            $this->notificationService->send(
                $assignedTo,
                'Pengaduan Baru Ditugaskan',
                "Anda ditugaskan untuk menangani pengaduan: {$complaint->subject}",
                [
                    'type' => 'complaint_assigned',
                    'complaint_id' => $complaintId,
                    'ticket_number' => $complaint->ticket_number
                ]
            );

            return [
                'success' => true,
                'message' => 'Pengaduan berhasil ditugaskan',
                'data' => [
                    'complaint_id' => $complaintId,
                    'assigned_to' => $assignedTo,
                    'assigned_to_name' => $assignee->username
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ComplaintService::assignComplaint: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal assign pengaduan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Add response to complaint
     * Adds reply from pengurus or follow-up from complainant
     * 
     * @param int $complaintId Complaint ID
     * @param int $userId User ID
     * @param string $message Response message
     * @param bool $isInternalNote Is internal note (not visible to complainant)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function addResponse(int $complaintId, int $userId, string $message, bool $isInternalNote = false): array
    {
        $this->db->transStart();

        try {
            $complaint = $this->complaintModel->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Check if complaint is closed
            if ($complaint->status === 'closed') {
                return [
                    'success' => false,
                    'message' => 'Pengaduan sudah ditutup',
                    'data' => null
                ];
            }

            // Validate message
            if (empty($message)) {
                return [
                    'success' => false,
                    'message' => 'Pesan tanggapan harus diisi',
                    'data' => null
                ];
            }

            // Add response
            $responseData = [
                'complaint_id' => $complaintId,
                'user_id' => $userId,
                'message' => $message,
                'is_internal_note' => $isInternalNote ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $responseId = $this->responseModel->insert($responseData);

            if (!$responseId) {
                throw new \Exception('Gagal menyimpan tanggapan');
            }

            // Update complaint timestamp
            $this->complaintModel->update($complaintId, [
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            // Notify complainant if not internal note
            if (!$isInternalNote && $complaint->user_id) {
                $this->notificationService->send(
                    $complaint->user_id,
                    'Tanggapan Baru pada Pengaduan Anda',
                    "Ada tanggapan baru untuk pengaduan '{$complaint->subject}'",
                    [
                        'type' => 'complaint_response',
                        'complaint_id' => $complaintId,
                        'ticket_number' => $complaint->ticket_number
                    ]
                );
            }

            return [
                'success' => true,
                'message' => 'Tanggapan berhasil ditambahkan',
                'data' => [
                    'response_id' => $responseId,
                    'complaint_id' => $complaintId,
                    'is_internal_note' => $isInternalNote
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ComplaintService::addResponse: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambah tanggapan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update complaint status
     * Changes complaint status (open, in_progress, resolved, closed)
     * 
     * @param int $complaintId Complaint ID
     * @param string $status New status
     * @param int $userId User ID making change
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateStatus(int $complaintId, string $status, int $userId): array
    {
        try {
            $complaint = $this->complaintModel->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate status
            $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Status tidak valid',
                    'data' => null
                ];
            }

            // Update status
            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($status === 'resolved') {
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'closed') {
                $updateData['closed_at'] = date('Y-m-d H:i:s');
            }

            $this->complaintModel->update($complaintId, $updateData);

            // Add system note
            $statusLabels = [
                'open' => 'Dibuka',
                'in_progress' => 'Sedang Diproses',
                'resolved' => 'Diselesaikan',
                'closed' => 'Ditutup'
            ];

            $this->responseModel->insert([
                'complaint_id' => $complaintId,
                'user_id' => $userId,
                'message' => sprintf('Status diubah menjadi: %s', $statusLabels[$status]),
                'is_internal_note' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Status berhasil diupdate',
                'data' => [
                    'complaint_id' => $complaintId,
                    'status' => $status
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintService::updateStatus: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update status: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update complaint priority
     * Changes priority level (low, medium, high, urgent)
     * 
     * @param int $complaintId Complaint ID
     * @param string $priority New priority
     * @param int $userId User ID making change
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updatePriority(int $complaintId, string $priority, int $userId): array
    {
        try {
            $complaint = $this->complaintModel->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate priority
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($priority, $validPriorities)) {
                return [
                    'success' => false,
                    'message' => 'Prioritas tidak valid',
                    'data' => null
                ];
            }

            // Update priority
            $this->complaintModel->update($complaintId, [
                'priority' => $priority,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add system note
            $priorityLabels = [
                'low' => 'Rendah',
                'medium' => 'Sedang',
                'high' => 'Tinggi',
                'urgent' => 'Mendesak'
            ];

            $this->responseModel->insert([
                'complaint_id' => $complaintId,
                'user_id' => $userId,
                'message' => sprintf('Prioritas diubah menjadi: %s', $priorityLabels[$priority]),
                'is_internal_note' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Prioritas berhasil diupdate',
                'data' => [
                    'complaint_id' => $complaintId,
                    'priority' => $priority
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintService::updatePriority: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update prioritas: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Resolve complaint
     * Marks complaint as resolved
     * 
     * @param int $complaintId Complaint ID
     * @param int $userId User ID resolving complaint
     * @param string $resolutionNote Resolution note
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function resolveComplaint(int $complaintId, int $userId, string $resolutionNote = ''): array
    {
        $this->db->transStart();

        try {
            $complaint = $this->complaintModel->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Update to resolved
            $this->complaintModel->update($complaintId, [
                'status' => 'resolved',
                'resolved_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add resolution note
            if ($resolutionNote) {
                $this->responseModel->insert([
                    'complaint_id' => $complaintId,
                    'user_id' => $userId,
                    'message' => $resolutionNote,
                    'is_internal_note' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            // Notify complainant
            if ($complaint->user_id) {
                $this->notificationService->send(
                    $complaint->user_id,
                    'Pengaduan Anda Telah Diselesaikan',
                    "Pengaduan '{$complaint->subject}' telah diselesaikan",
                    [
                        'type' => 'complaint_resolved',
                        'complaint_id' => $complaintId,
                        'ticket_number' => $complaint->ticket_number
                    ]
                );
            }

            return [
                'success' => true,
                'message' => 'Pengaduan berhasil diselesaikan',
                'data' => [
                    'complaint_id' => $complaintId,
                    'status' => 'resolved',
                    'resolved_at' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in ComplaintService::resolveComplaint: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyelesaikan pengaduan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Close complaint
     * Marks complaint as closed (final state)
     * 
     * @param int $complaintId Complaint ID
     * @param int $userId User ID closing complaint
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function closeComplaint(int $complaintId, int $userId): array
    {
        try {
            $complaint = $this->complaintModel->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Update to closed
            $this->complaintModel->update($complaintId, [
                'status' => 'closed',
                'closed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add system note
            $this->responseModel->insert([
                'complaint_id' => $complaintId,
                'user_id' => $userId,
                'message' => 'Pengaduan ditutup',
                'is_internal_note' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Pengaduan berhasil ditutup',
                'data' => [
                    'complaint_id' => $complaintId,
                    'status' => 'closed',
                    'closed_at' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintService::closeComplaint: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menutup pengaduan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get complaint statistics
     * Returns comprehensive complaint stats
     * 
     * @param array $filters Optional filters (date_from, date_to, category_id, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getComplaintStats(array $filters = []): array
    {
        try {
            $builder = $this->complaintModel->builder();

            // Apply filters
            if (isset($filters['date_from'])) {
                $builder->where('complaints.created_at >=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $builder->where('complaints.created_at <=', $filters['date_to']);
            }

            if (isset($filters['category_id'])) {
                $builder->where('complaints.category_id', $filters['category_id']);
            }

            // Get total complaints
            $total = $builder->countAllResults(false);

            // Get by status
            $byStatus = [
                'open' => $builder->where('status', 'open')->countAllResults(false),
                'in_progress' => $builder->where('status', 'in_progress')->countAllResults(false),
                'resolved' => $builder->where('status', 'resolved')->countAllResults(false),
                'closed' => $builder->where('status', 'closed')->countAllResults(false)
            ];

            // Reset builder for priority stats
            $builder = $this->complaintModel->builder();
            if (isset($filters['date_from'])) {
                $builder->where('created_at >=', $filters['date_from']);
            }
            if (isset($filters['date_to'])) {
                $builder->where('created_at <=', $filters['date_to']);
            }

            // Get by priority
            $byPriority = [
                'low' => $builder->where('priority', 'low')->countAllResults(false),
                'medium' => $builder->where('priority', 'medium')->countAllResults(false),
                'high' => $builder->where('priority', 'high')->countAllResults(false),
                'urgent' => $builder->where('priority', 'urgent')->countAllResults(false)
            ];

            // Calculate resolution rate
            $resolvedCount = $byStatus['resolved'] + $byStatus['closed'];
            $resolutionRate = $total > 0 ? round(($resolvedCount / $total) * 100, 2) : 0;

            return [
                'success' => true,
                'message' => 'Statistik pengaduan berhasil diambil',
                'data' => [
                    'total_complaints' => $total,
                    'by_status' => $byStatus,
                    'by_priority' => $byPriority,
                    'resolution_rate' => $resolutionRate,
                    'filters_applied' => $filters
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintService::getComplaintStats: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get complaint history with responses
     * Returns complete complaint thread
     * 
     * @param int $complaintId Complaint ID
     * @param bool $includeInternalNotes Include internal notes
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getComplaintHistory(int $complaintId, bool $includeInternalNotes = false): array
    {
        try {
            $complaint = $this->complaintModel
                ->select('complaints.*, complaint_categories.name as category_name')
                ->join('complaint_categories', 'complaint_categories.id = complaints.category_id', 'left')
                ->find($complaintId);

            if (!$complaint) {
                return [
                    'success' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ];
            }

            // Get responses
            $responsesBuilder = $this->responseModel
                ->select('complaint_responses.*, users.username as responder_name')
                ->join('users', 'users.id = complaint_responses.user_id', 'left')
                ->where('complaint_responses.complaint_id', $complaintId);

            if (!$includeInternalNotes) {
                $responsesBuilder->where('complaint_responses.is_internal_note', 0);
            }

            $responses = $responsesBuilder
                ->orderBy('complaint_responses.created_at', 'ASC')
                ->findAll();

            return [
                'success' => true,
                'message' => 'History pengaduan berhasil diambil',
                'data' => [
                    'complaint' => $complaint,
                    'responses' => $responses,
                    'response_count' => count($responses)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintService::getComplaintHistory: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil history: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate unique ticket number
     * Creates formatted ticket number
     * 
     * @return string Ticket number (e.g., TKT-2025-0001)
     */
    protected function generateTicketNumber(): string
    {
        $year = date('Y');
        $count = $this->complaintModel
            ->like('ticket_number', "TKT-{$year}-", 'after')
            ->countAllResults();

        $number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "TKT-{$year}-{$number}";
    }

    /**
     * Notify admin about new complaint
     * Sends notification to pengurus
     * 
     * @param int $complaintId Complaint ID
     * @param string $ticketNumber Ticket number
     * @return void
     */
    protected function notifyNewComplaint(int $complaintId, string $ticketNumber): void
    {
        try {
            // Send to all Pengurus
            $this->notificationService->sendToRole(
                'Pengurus',
                'Pengaduan Baru',
                "Pengaduan baru masuk dengan nomor tiket: {$ticketNumber}",
                [
                    'type' => 'new_complaint',
                    'complaint_id' => $complaintId,
                    'ticket_number' => $ticketNumber
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error notifying new complaint: ' . $e->getMessage());
        }
    }
}
