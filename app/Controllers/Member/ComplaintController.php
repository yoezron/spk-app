<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Services\Content\ComplaintService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * ComplaintController (Member Area)
 * 
 * Menangani sistem complaint/ticketing untuk anggota
 * Create ticket, view status, add replies, track resolution
 * 
 * @package App\Controllers\Member
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
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->complaintService = new ComplaintService();
    }

    /**
     * Display user's tickets list
     * Shows all tickets created by the member
     * 
     * @return string
     */
    public function index(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Get query parameters
            $page = (int) ($this->request->getGet('page') ?? 1);
            $status = $this->request->getGet('status');

            // Build options
            $options = [
                'page' => $page,
                'limit' => 15,
                'user_id' => $userId,
                'order_by' => 'created_at',
                'order_dir' => 'DESC'
            ];

            if ($status) {
                $options['status'] = $status;
            }

            // Get tickets
            $result = $this->complaintService->getTickets($options);

            // Get ticket statistics
            $stats = $this->getUserTicketStats($userId);

            $data = [
                'title' => 'Pengaduan Saya - Serikat Pekerja Kampus',
                'pageTitle' => 'Pengaduan Saya',

                // Tickets
                'tickets' => $result['data'] ?? [],
                'pager' => $result['pager'] ?? null,
                'total' => $result['total'] ?? 0,

                // Statistics
                'stats' => $stats,

                // Filter state
                'currentStatus' => $status
            ];

            return view('member/complaint/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading complaints: ' . $e->getMessage());

            return view('member/complaint/index', [
                'title' => 'Pengaduan Saya',
                'pageTitle' => 'Pengaduan',
                'tickets' => [],
                'pager' => null,
                'total' => 0,
                'stats' => [],
                'currentStatus' => null
            ]);
        }
    }

    /**
     * Display create ticket form
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
            // Get complaint categories
            $categories = $this->getComplaintCategories();

            $data = [
                'title' => 'Buat Pengaduan Baru - Serikat Pekerja Kampus',
                'pageTitle' => 'Buat Pengaduan Baru',
                'categories' => $categories
            ];

            return view('member/complaint/create', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading create complaint form: ' . $e->getMessage());

            return redirect()->to('/member/complaints')
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Store new ticket
     * 
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        // Validation rules
        $validationRules = [
            'subject' => [
                'label' => 'Subjek',
                'rules' => 'required|min_length[5]|max_length[200]',
                'errors' => [
                    'required' => 'Subjek harus diisi',
                    'min_length' => 'Subjek minimal 5 karakter',
                    'max_length' => 'Subjek maksimal 200 karakter'
                ]
            ],
            'category' => [
                'label' => 'Kategori',
                'rules' => 'required|in_list[ketenagakerjaan,gaji,kontrak,lingkungan_kerja,diskriminasi,pelecehan,lainnya]',
                'errors' => [
                    'required' => 'Kategori harus dipilih',
                    'in_list' => 'Kategori tidak valid'
                ]
            ],
            'description' => [
                'label' => 'Deskripsi',
                'rules' => 'required|min_length[20]',
                'errors' => [
                    'required' => 'Deskripsi harus diisi',
                    'min_length' => 'Deskripsi minimal 20 karakter'
                ]
            ],
            'priority' => [
                'label' => 'Prioritas',
                'rules' => 'permit_empty|in_list[low,normal,high,urgent]'
            ]
        ];

        // Validate input
        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            // Prepare ticket data
            $ticketData = [
                'user_id' => $userId,
                'ticket_number' => $this->generateTicketNumber(),
                'subject' => $this->request->getPost('subject'),
                'category' => $this->request->getPost('category'),
                'description' => $this->request->getPost('description'),
                'priority' => $this->request->getPost('priority') ?? 'normal',
                'status' => 'open'
            ];

            // Handle file attachment (optional)
            $attachment = $this->request->getFile('attachment');
            if ($attachment && $attachment->isValid() && !$attachment->hasMoved()) {
                $attachmentPath = $this->uploadAttachment($attachment);
                if ($attachmentPath) {
                    $ticketData['attachment_path'] = $attachmentPath;
                }
            }

            // Create ticket
            $result = $this->complaintService->createTicket($ticketData);

            if ($result['success']) {
                return redirect()->to('/member/complaints/' . $result['data']['ticket_id'])
                    ->with('success', 'Pengaduan berhasil dibuat. Tim kami akan segera merespon.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating complaint: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat pengaduan.');
        }
    }

    /**
     * Display ticket detail with replies
     * 
     * @param int $ticketId Ticket ID
     * @return string|RedirectResponse
     */
    public function show(int $ticketId)
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Get ticket with replies
            $result = $this->complaintService->getTicketWithReplies($ticketId);

            if (!$result['success'] || !$result['data']) {
                return redirect()->to('/member/complaints')
                    ->with('error', 'Pengaduan tidak ditemukan.');
            }

            $ticket = $result['data'];

            // Check if user owns this ticket
            if ($ticket->user_id != $userId) {
                return redirect()->to('/member/complaints')
                    ->with('error', 'Anda tidak memiliki akses ke pengaduan ini.');
            }

            $data = [
                'title' => 'Pengaduan #' . $ticket->ticket_number . ' - SPK',
                'pageTitle' => 'Detail Pengaduan',
                'ticket' => $ticket,
                'replies' => $ticket->replies ?? []
            ];

            return view('member/complaint/detail', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading complaint: ' . $e->getMessage());

            return redirect()->to('/member/complaints')
                ->with('error', 'Terjadi kesalahan saat memuat pengaduan.');
        }
    }

    /**
     * Add reply to ticket
     * 
     * @param int $ticketId Ticket ID
     * @return RedirectResponse
     */
    public function reply(int $ticketId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        // Validation
        $validationRules = [
            'message' => [
                'label' => 'Pesan',
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Pesan harus diisi',
                    'min_length' => 'Pesan minimal 10 karakter'
                ]
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            // Check ticket ownership
            $complaintModel = model('ComplaintModel');
            $ticket = $complaintModel->find($ticketId);

            if (!$ticket || $ticket->user_id != $userId) {
                return redirect()->to('/member/complaints')
                    ->with('error', 'Pengaduan tidak ditemukan.');
            }

            // Add reply
            $replyData = [
                'complaint_id' => $ticketId,
                'user_id' => $userId,
                'message' => $this->request->getPost('message'),
                'is_staff_reply' => false
            ];

            $result = $this->complaintService->addReply($replyData);

            if ($result['success']) {
                return redirect()->to('/member/complaints/' . $ticketId . '#reply-' . $result['data']['reply_id'])
                    ->with('success', 'Balasan berhasil ditambahkan.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error adding reply: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan balasan.');
        }
    }

    /**
     * Close ticket
     * Member can close their own resolved tickets
     * 
     * @param int $ticketId Ticket ID
     * @return RedirectResponse
     */
    public function close(int $ticketId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Check ticket ownership
            $complaintModel = model('ComplaintModel');
            $ticket = $complaintModel->find($ticketId);

            if (!$ticket || $ticket->user_id != $userId) {
                return redirect()->to('/member/complaints')
                    ->with('error', 'Pengaduan tidak ditemukan.');
            }

            // Check if already closed
            if ($ticket->status === 'closed') {
                return redirect()->back()
                    ->with('info', 'Pengaduan sudah ditutup.');
            }

            // Close ticket
            $result = $this->complaintService->closeTicket($ticketId, $userId);

            if ($result['success']) {
                return redirect()->to('/member/complaints/' . $ticketId)
                    ->with('success', 'Pengaduan berhasil ditutup.');
            } else {
                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error closing ticket: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menutup pengaduan.');
        }
    }

    /**
     * Reopen closed ticket
     * 
     * @param int $ticketId Ticket ID
     * @return RedirectResponse
     */
    public function reopen(int $ticketId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Check ticket ownership
            $complaintModel = model('ComplaintModel');
            $ticket = $complaintModel->find($ticketId);

            if (!$ticket || $ticket->user_id != $userId) {
                return redirect()->to('/member/complaints')
                    ->with('error', 'Pengaduan tidak ditemukan.');
            }

            // Check if not closed
            if ($ticket->status !== 'closed') {
                return redirect()->back()
                    ->with('info', 'Pengaduan belum ditutup.');
            }

            // Reopen ticket
            $result = $this->complaintService->reopenTicket($ticketId);

            if ($result['success']) {
                return redirect()->to('/member/complaints/' . $ticketId)
                    ->with('success', 'Pengaduan berhasil dibuka kembali.');
            } else {
                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error reopening ticket: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuka pengaduan.');
        }
    }

    /**
     * Get user ticket statistics
     * 
     * @param int $userId User ID
     * @return array
     */
    protected function getUserTicketStats(int $userId): array
    {
        try {
            $complaintModel = model('ComplaintModel');

            $stats = [
                'total' => $complaintModel->where('user_id', $userId)->countAllResults(),
                'open' => $complaintModel->where('user_id', $userId)->where('status', 'open')->countAllResults(),
                'in_progress' => $complaintModel->where('user_id', $userId)->where('status', 'in_progress')->countAllResults(),
                'resolved' => $complaintModel->where('user_id', $userId)->where('status', 'resolved')->countAllResults(),
                'closed' => $complaintModel->where('user_id', $userId)->where('status', 'closed')->countAllResults()
            ];

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'Error getting ticket stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get complaint categories
     * 
     * @return array
     */
    protected function getComplaintCategories(): array
    {
        return [
            'ketenagakerjaan' => 'Masalah Ketenagakerjaan',
            'gaji' => 'Gaji & Tunjangan',
            'kontrak' => 'Kontrak Kerja',
            'lingkungan_kerja' => 'Lingkungan Kerja',
            'diskriminasi' => 'Diskriminasi',
            'pelecehan' => 'Pelecehan',
            'lainnya' => 'Lainnya'
        ];
    }

    /**
     * Generate unique ticket number
     * 
     * @return string
     */
    protected function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        return $prefix . '-' . $date . '-' . $random;
    }

    /**
     * Upload ticket attachment
     * 
     * @param object $file Uploaded file
     * @return string|null File path or null on failure
     */
    protected function uploadAttachment($file): ?string
    {
        try {
            // Validate file
            if ($file->getSize() > 5242880) { // 5MB
                return null;
            }

            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            if (!in_array($file->getExtension(), $allowedTypes)) {
                return null;
            }

            // Generate unique filename
            $fileName = $file->getRandomName();

            // Move file
            $file->move(WRITEPATH . 'uploads/complaints', $fileName);

            return 'complaints/' . $fileName;
        } catch (\Exception $e) {
            log_message('error', 'Error uploading attachment: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rate ticket resolution
     * Member can rate how satisfied they are with the resolution
     * 
     * @param int $ticketId Ticket ID
     * @return RedirectResponse
     */
    public function rate(int $ticketId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        // Validation
        $validationRules = [
            'rating' => [
                'label' => 'Rating',
                'rules' => 'required|in_list[1,2,3,4,5]'
            ],
            'feedback' => [
                'label' => 'Feedback',
                'rules' => 'permit_empty|max_length[500]'
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            // Check ticket ownership
            $complaintModel = model('ComplaintModel');
            $ticket = $complaintModel->find($ticketId);

            if (!$ticket || $ticket->user_id != $userId) {
                return redirect()->to('/member/complaints')
                    ->with('error', 'Pengaduan tidak ditemukan.');
            }

            // Update rating
            $complaintModel->update($ticketId, [
                'rating' => $this->request->getPost('rating'),
                'feedback' => $this->request->getPost('feedback'),
                'rated_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to('/member/complaints/' . $ticketId)
                ->with('success', 'Terima kasih atas penilaian Anda!');
        } catch (\Exception $e) {
            log_message('error', 'Error rating ticket: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan.');
        }
    }
}
