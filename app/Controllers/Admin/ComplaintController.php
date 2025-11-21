<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ComplaintService;
use App\Services\RegionScopeService;
use App\Services\Communication\NotificationService;
use App\Models\ComplaintModel;
use App\Models\ComplaintReplyModel;
use App\Models\UserModel;
use App\Models\MemberProfileModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ComplaintController (Admin)
 * 
 * Mengelola ticketing system untuk pengaduan anggota
 * Support regional scope untuk Koordinator Wilayah
 * Assign tickets, reply, update status, export reports
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ComplaintController extends BaseController
{
    /**
     * @var ComplaintService
     */
    protected $complaintService;

    /**
     * @var RegionScopeService
     */
    protected $regionScope;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var ComplaintModel
     */
    protected $complaintModel;

    /**
     * @var ComplaintReplyModel
     */
    protected $replyModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->complaintService = new ComplaintService();
        $this->regionScope = new RegionScopeService();
        $this->notificationService = new NotificationService();
        $this->complaintModel = new ComplaintModel();
        $this->replyModel = new ComplaintReplyModel();
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
    }

    /**
     * Display list of all tickets with regional scope
     * Koordinator Wilayah only see tickets from their province
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('complaint.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat pengaduan');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get filters from request
        $filters = [
            'status' => $this->request->getGet('status'),
            'priority' => $this->request->getGet('priority'),
            'category' => $this->request->getGet('category'),
            'assigned_to' => $this->request->getGet('assigned_to'),
            'search' => $this->request->getGet('search')
        ];

        // Build query
        $builder = $this->complaintModel
            ->select('complaints.*, member_profiles.full_name as reporter_name, member_profiles.phone as reporter_phone, provinces.name as province_name, assignees.email as assigned_to_email, assignee_profiles.full_name as assigned_to_name')
            ->join('member_profiles', 'member_profiles.user_id = complaints.user_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('users as assignees', 'assignees.id = complaints.assigned_to', 'left')
            ->join('member_profiles as assignee_profiles', 'assignee_profiles.user_id = assignees.id', 'left');

        // CRITICAL: Apply regional scope for Koordinator Wilayah
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $builder->where('member_profiles.province_id', $scopeResult['data']['province_id']);
            }
        }

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('complaints.status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $builder->where('complaints.priority', $filters['priority']);
        }

        if (!empty($filters['category'])) {
            $builder->where('complaints.category', $filters['category']);
        }

        if (!empty($filters['assigned_to'])) {
            $builder->where('complaints.assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('complaints.ticket_number', $search)
                ->orLike('complaints.subject', $search)
                ->orLike('complaints.description', $search)
                ->orLike('member_profiles.full_name', $search)
                ->groupEnd();
        }

        // Get paginated results
        $tickets = $builder
            ->orderBy('complaints.priority', 'DESC')
            ->orderBy('complaints.created_at', 'DESC')
            ->paginate(20);

        // Get staff list for assignment filter
        $staffList = $this->userModel
            ->select('users.id, users.email, member_profiles.full_name')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->whereIn('auth_groups.name', ['pengurus', 'koordinator_wilayah'])
            ->findAll();

        $data = [
            'title' => 'Kelola Pengaduan',
            'tickets' => $tickets,
            'pager' => $this->complaintModel->pager,
            'filters' => $filters,
            'staff_list' => $staffList,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/complaints/index', $data);
    }

    /**
     * View ticket detail with complete history
     * Shows ticket info, replies, and status changes
     * 
     * @param int $id Ticket ID
     * @return string|ResponseInterface
     */
    public function show(int $id)
    {
        // Check permission
        if (!auth()->user()->can('complaint.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat detail pengaduan');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get ticket with complete info
        $ticket = $this->complaintModel
            ->select('complaints.*, member_profiles.full_name as reporter_name, member_profiles.email as reporter_email, member_profiles.phone as reporter_phone, provinces.name as province_name, assignees.email as assigned_to_email, assignee_profiles.full_name as assigned_to_name')
            ->join('member_profiles', 'member_profiles.user_id = complaints.user_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('users as assignees', 'assignees.id = complaints.assigned_to', 'left')
            ->join('member_profiles as assignee_profiles', 'assignee_profiles.user_id = assignees.id', 'left')
            ->find($id);

        if (!$ticket) {
            return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
        }

        // Check regional scope access
        if ($isKoordinator) {
            $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
            if ($memberProfile) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                    }
                }
            }
        }

        // Get replies
        $replies = $this->replyModel
            ->select('complaint_replies.*, users.email as replier_email, member_profiles.full_name as replier_name')
            ->join('users', 'users.id = complaint_replies.user_id')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->where('complaint_replies.complaint_id', $id)
            ->orderBy('complaint_replies.created_at', 'ASC')
            ->findAll();

        // Get staff list for assignment
        $staffList = $this->userModel
            ->select('users.id, users.email, member_profiles.full_name')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->whereIn('auth_groups.name', ['pengurus', 'koordinator_wilayah'])
            ->findAll();

        $data = [
            'title' => 'Detail Pengaduan - ' . $ticket->ticket_number,
            'ticket' => $ticket,
            'replies' => $replies,
            'staff_list' => $staffList,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/complaints/show', $data);
    }

    /**
     * Assign ticket to staff
     * Assign ticket to specific pengurus/koordinator
     * 
     * @param int $id Ticket ID
     * @return ResponseInterface
     */
    public function assign(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk assign pengaduan');
        }

        $user = auth()->user();

        // Validate input
        $assigneeId = $this->request->getPost('assignee_id');

        if (empty($assigneeId)) {
            return redirect()->back()->with('error', 'Pilih staff yang akan ditugaskan');
        }

        try {
            $ticket = $this->complaintModel->find($id);

            if (!$ticket) {
                return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
            }

            // Check regional scope access for Koordinator
            if ($user->inGroup('koordinator_wilayah')) {
                $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
                if ($memberProfile) {
                    $scopeResult = $this->regionScope->getScopeData($user->id);
                    if ($scopeResult['success']) {
                        if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                        }
                    }
                }
            }

            // Update assignment
            $this->complaintModel->update($id, [
                'assigned_to' => $assigneeId,
                'assigned_at' => date('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add system reply
            $assignee = $this->userModel->find($assigneeId);
            $this->replyModel->insert([
                'complaint_id' => $id,
                'user_id' => $user->id,
                'message' => "Pengaduan telah ditugaskan kepada {$assignee->email}",
                'is_internal' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification to assignee
            $this->notificationService->sendTicketAssignedNotification($assigneeId, $ticket->ticket_number);

            // Log activity
            log_message('info', "Ticket {$ticket->ticket_number} assigned to user {$assigneeId} by user {$user->id}");

            return redirect()->back()->with('success', 'Pengaduan berhasil ditugaskan');
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::assign: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal assign pengaduan: ' . $e->getMessage());
        }
    }

    /**
     * Add reply to ticket
     * Staff can reply to tickets
     * 
     * @param int $id Ticket ID
     * @return ResponseInterface
     */
    public function reply(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membalas pengaduan');
        }

        // Validate input
        $rules = [
            'message' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = auth()->user();

        try {
            $ticket = $this->complaintModel->find($id);

            if (!$ticket) {
                return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
            }

            // Check regional scope access
            if ($user->inGroup('koordinator_wilayah')) {
                $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
                if ($memberProfile) {
                    $scopeResult = $this->regionScope->getScopeData($user->id);
                    if ($scopeResult['success']) {
                        if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                        }
                    }
                }
            }

            $message = $this->request->getPost('message');
            $isInternal = $this->request->getPost('is_internal') ? 1 : 0;

            // Add reply
            $this->replyModel->insert([
                'complaint_id' => $id,
                'user_id' => $user->id,
                'message' => $message,
                'is_internal' => $isInternal,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update ticket last response time
            $this->complaintModel->update($id, [
                'last_response_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification to ticket owner if not internal
            if (!$isInternal) {
                $this->notificationService->sendTicketReplyNotification($ticket->user_id, $ticket->ticket_number);
            }

            // Log activity
            log_message('info', "Reply added to ticket {$ticket->ticket_number} by user {$user->id}");

            return redirect()->back()->with('success', 'Balasan berhasil ditambahkan');
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::reply: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan balasan: ' . $e->getMessage());
        }
    }

    /**
     * Update ticket status
     * Change status (open, in_progress, resolved, closed)
     * 
     * @param int $id Ticket ID
     * @return ResponseInterface
     */
    public function updateStatus(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengubah status pengaduan');
        }

        $user = auth()->user();

        // Validate input
        $newStatus = $this->request->getPost('status');
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];

        if (!in_array($newStatus, $validStatuses)) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }

        try {
            $ticket = $this->complaintModel->find($id);

            if (!$ticket) {
                return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
            }

            // Check regional scope access
            if ($user->inGroup('koordinator_wilayah')) {
                $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
                if ($memberProfile) {
                    $scopeResult = $this->regionScope->getScopeData($user->id);
                    if ($scopeResult['success']) {
                        if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                        }
                    }
                }
            }

            $oldStatus = $ticket->status;

            // Update status
            $updateData = [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($newStatus === 'resolved') {
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
                $updateData['resolved_by'] = $user->id;
            } elseif ($newStatus === 'closed') {
                $updateData['closed_at'] = date('Y-m-d H:i:s');
                $updateData['closed_by'] = $user->id;
            }

            $this->complaintModel->update($id, $updateData);

            // Add system reply
            $statusText = [
                'open' => 'Dibuka kembali',
                'in_progress' => 'Sedang diproses',
                'resolved' => 'Telah diselesaikan',
                'closed' => 'Ditutup'
            ];

            $this->replyModel->insert([
                'complaint_id' => $id,
                'user_id' => $user->id,
                'message' => "Status pengaduan diubah dari {$oldStatus} menjadi {$newStatus}",
                'is_internal' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification
            $this->notificationService->sendTicketStatusChangedNotification(
                $ticket->user_id,
                $ticket->ticket_number,
                $statusText[$newStatus]
            );

            // Log activity
            log_message('info', "Ticket {$ticket->ticket_number} status changed from {$oldStatus} to {$newStatus} by user {$user->id}");

            return redirect()->back()->with('success', 'Status pengaduan berhasil diubah');
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::updateStatus: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Close ticket
     * Mark ticket as closed with resolution notes
     * 
     * @param int $id Ticket ID
     * @return ResponseInterface
     */
    public function close(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menutup pengaduan');
        }

        $user = auth()->user();

        try {
            $ticket = $this->complaintModel->find($id);

            if (!$ticket) {
                return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
            }

            // Check regional scope access
            if ($user->inGroup('koordinator_wilayah')) {
                $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
                if ($memberProfile) {
                    $scopeResult = $this->regionScope->getScopeData($user->id);
                    if ($scopeResult['success']) {
                        if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                        }
                    }
                }
            }

            $resolutionNotes = $this->request->getPost('resolution_notes');

            // Close ticket
            $this->complaintModel->update($id, [
                'status' => 'closed',
                'closed_at' => date('Y-m-d H:i:s'),
                'closed_by' => $user->id,
                'resolution_notes' => $resolutionNotes,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add resolution reply
            if (!empty($resolutionNotes)) {
                $this->replyModel->insert([
                    'complaint_id' => $id,
                    'user_id' => $user->id,
                    'message' => "Pengaduan ditutup. Catatan penyelesaian: {$resolutionNotes}",
                    'is_internal' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Send notification
            $this->notificationService->sendTicketClosedNotification($ticket->user_id, $ticket->ticket_number);

            // Log activity
            log_message('info', "Ticket {$ticket->ticket_number} closed by user {$user->id}");

            return redirect()->back()->with('success', 'Pengaduan berhasil ditutup');
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::close: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menutup pengaduan: ' . $e->getMessage());
        }
    }

    /**
     * Resolve ticket
     * Mark ticket as resolved (convenience method for updateStatus)
     *
     * @param int $id Ticket ID
     * @return ResponseInterface
     */
    public function resolve(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menyelesaikan pengaduan');
        }

        $user = auth()->user();

        try {
            $ticket = $this->complaintModel->find($id);

            if (!$ticket) {
                return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
            }

            // Check regional scope access
            if ($user->inGroup('koordinator_wilayah')) {
                $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
                if ($memberProfile) {
                    $scopeResult = $this->regionScope->getScopeData($user->id);
                    if ($scopeResult['success']) {
                        if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                        }
                    }
                }
            }

            $resolutionNotes = $this->request->getPost('resolution_notes') ?? $this->request->getPost('notes');

            // Resolve ticket
            $updateData = [
                'status' => 'resolved',
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolved_by' => $user->id,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($resolutionNotes)) {
                $updateData['resolution_notes'] = $resolutionNotes;
            }

            $this->complaintModel->update($id, $updateData);

            // Add system reply
            $message = "Pengaduan telah diselesaikan";
            if (!empty($resolutionNotes)) {
                $message .= ". Catatan: {$resolutionNotes}";
            }

            $this->replyModel->insert([
                'complaint_id' => $id,
                'user_id' => $user->id,
                'message' => $message,
                'is_internal' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification
            $this->notificationService->sendTicketStatusChangedNotification(
                $ticket->user_id,
                $ticket->ticket_number,
                'Telah diselesaikan'
            );

            // Log activity
            log_message('info', "Ticket {$ticket->ticket_number} resolved by user {$user->id}");

            return redirect()->back()->with('success', 'Pengaduan berhasil diselesaikan');
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::resolve: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyelesaikan pengaduan: ' . $e->getMessage());
        }
    }

    /**
     * Reopen ticket
     * Reopen a resolved/closed ticket (convenience method for updateStatus)
     *
     * @param int $id Ticket ID
     * @return ResponseInterface
     */
    public function reopen(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuka kembali pengaduan');
        }

        $user = auth()->user();

        try {
            $ticket = $this->complaintModel->find($id);

            if (!$ticket) {
                return redirect()->back()->with('error', 'Pengaduan tidak ditemukan');
            }

            // Check if ticket can be reopened
            if (!in_array($ticket->status, ['resolved', 'closed'])) {
                return redirect()->back()->with('error', 'Hanya pengaduan yang sudah diselesaikan atau ditutup yang dapat dibuka kembali');
            }

            // Check regional scope access
            if ($user->inGroup('koordinator_wilayah')) {
                $memberProfile = $this->memberModel->where('user_id', $ticket->user_id)->first();
                if ($memberProfile) {
                    $scopeResult = $this->regionScope->getScopeData($user->id);
                    if ($scopeResult['success']) {
                        if ($memberProfile->province_id != $scopeResult['data']['province_id']) {
                            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengaduan ini');
                        }
                    }
                }
            }

            $reopenReason = $this->request->getPost('reason') ?? $this->request->getPost('notes');
            $oldStatus = $ticket->status;

            // Reopen ticket
            $updateData = [
                'status' => 'open',
                'updated_at' => date('Y-m-d H:i:s'),
                'reopened_at' => date('Y-m-d H:i:s'),
                'reopened_by' => $user->id
            ];

            // Clear resolution/closed data
            if ($oldStatus === 'resolved') {
                $updateData['resolved_at'] = null;
                $updateData['resolved_by'] = null;
            } elseif ($oldStatus === 'closed') {
                $updateData['closed_at'] = null;
                $updateData['closed_by'] = null;
            }

            $this->complaintModel->update($id, $updateData);

            // Add system reply
            $message = "Pengaduan dibuka kembali dari status {$oldStatus}";
            if (!empty($reopenReason)) {
                $message .= ". Alasan: {$reopenReason}";
            }

            $this->replyModel->insert([
                'complaint_id' => $id,
                'user_id' => $user->id,
                'message' => $message,
                'is_internal' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification
            $this->notificationService->sendTicketStatusChangedNotification(
                $ticket->user_id,
                $ticket->ticket_number,
                'Dibuka kembali'
            );

            // Log activity
            log_message('info', "Ticket {$ticket->ticket_number} reopened by user {$user->id}");

            return redirect()->back()->with('success', 'Pengaduan berhasil dibuka kembali');
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::reopen: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuka kembali pengaduan: ' . $e->getMessage());
        }
    }

    /**
     * Export ticket report to Excel
     * Export filtered tickets with complete information
     *
     * @return ResponseInterface
     */
    public function export(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('complaint.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengekspor data pengaduan');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get filters from request
        $filters = [
            'status' => $this->request->getGet('status'),
            'priority' => $this->request->getGet('priority'),
            'category' => $this->request->getGet('category')
        ];

        try {
            // Build query
            $builder = $this->complaintModel
                ->select('complaints.*, member_profiles.full_name as reporter_name, member_profiles.email as reporter_email, member_profiles.phone as reporter_phone, provinces.name as province_name, assignees.email as assigned_to_email, assignee_profiles.full_name as assigned_to_name')
                ->join('member_profiles', 'member_profiles.user_id = complaints.user_id')
                ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
                ->join('users as assignees', 'assignees.id = complaints.assigned_to', 'left')
                ->join('member_profiles as assignee_profiles', 'assignee_profiles.user_id = assignees.id', 'left');

            // Apply regional scope
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    $builder->where('member_profiles.province_id', $scopeResult['data']['province_id']);
                }
            }

            // Apply filters
            if (!empty($filters['status'])) {
                $builder->where('complaints.status', $filters['status']);
            }

            if (!empty($filters['priority'])) {
                $builder->where('complaints.priority', $filters['priority']);
            }

            if (!empty($filters['category'])) {
                $builder->where('complaints.category', $filters['category']);
            }

            $tickets = $builder->findAll();

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Pengaduan');

            // Set headers
            $headers = ['No', 'No. Tiket', 'Tanggal', 'Pelapor', 'Email', 'Provinsi', 'Kategori', 'Subjek', 'Prioritas', 'Status', 'Ditugaskan Ke', 'Tanggal Selesai'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header row
            $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            $sheet->getStyle('A1:L1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $sheet->getStyle('A1:L1')->getFont()->getColor()->setRGB('FFFFFF');

            // Fill data
            $row = 2;
            foreach ($tickets as $index => $ticket) {
                $sheet->fromArray([
                    $index + 1,
                    $ticket->ticket_number,
                    date('d/m/Y', strtotime($ticket->created_at)),
                    $ticket->reporter_name,
                    $ticket->reporter_email,
                    $ticket->province_name ?? '-',
                    ucfirst($ticket->category),
                    $ticket->subject,
                    ucfirst($ticket->priority),
                    ucfirst($ticket->status),
                    $ticket->assigned_to_name ?? '-',
                    $ticket->resolved_at ? date('d/m/Y', strtotime($ticket->resolved_at)) : '-'
                ], null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Generate filename
            $filename = 'laporan_pengaduan_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::export: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket statistics (AJAX endpoint)
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

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        try {
            $builder = $this->complaintModel->builder();

            // Apply regional scope
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    $builder->join('member_profiles', 'member_profiles.user_id = complaints.user_id')
                        ->where('member_profiles.province_id', $scopeResult['data']['province_id']);
                }
            }

            $stats = [
                'total' => (clone $builder)->countAllResults(),
                'open' => (clone $builder)->where('complaints.status', 'open')->countAllResults(),
                'in_progress' => (clone $builder)->where('complaints.status', 'in_progress')->countAllResults(),
                'resolved' => (clone $builder)->where('complaints.status', 'resolved')->countAllResults(),
                'closed' => (clone $builder)->where('complaints.status', 'closed')->countAllResults()
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in ComplaintController::getStats: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
