<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\Member\MemberStatisticsService;
use App\Services\ContentService; // ✅ CORRECT
use CodeIgniter\HTTP\RedirectResponse;

/**
 * HomeController
 * 
 * Menangani halaman public (landing page, about, contact)
 * Display statistics, latest posts, dan handle contact form
 * 
 * @package App\Controllers\Public
 * @author  SPK Development Team
 * @version 1.0.0
 */
class HomeController extends BaseController
{
    /**
     * @var MemberStatisticsService
     */
    protected $statisticsService;

    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->statisticsService = new MemberStatisticsService();
        $this->contentService = new ContentService();
    }

    /**
     * Display landing page
     * Shows statistics overview and latest blog posts
     * 
     * @return string
     */
    public function index(): string
    {
        try {
            // Get public statistics
            $stats = $this->statisticsService->getPublicStatistics();

            // Get latest blog posts (3 posts)
            $latestPosts = $this->contentService->getPublishedPosts([
                'limit' => 3,
                'order_by' => 'published_at',
                'order_dir' => 'DESC'
            ]);

            // Get featured content (Manifesto, Sejarah)
            $featuredPages = $this->contentService->getFeaturedPages();

            $data = [
                'title' => 'Serikat Pekerja Kampus - Bersatu untuk Kesejahteraan',
                'pageTitle' => 'Selamat Datang di SPK',
                'metaDescription' => 'Serikat Pekerja Kampus - Organisasi perjuangan untuk kesejahteraan pekerja pendidikan tinggi di Indonesia',

                // Statistics
                'totalMembers' => $stats['total_active_members'] ?? 0,
                'totalProvinces' => $stats['total_provinces'] ?? 0,
                'totalUniversities' => $stats['total_universities'] ?? 0,
                'growthPercentage' => $stats['growth_percentage'] ?? 0,

                // Content
                'latestPosts' => $latestPosts['data'] ?? [],
                'featuredPages' => $featuredPages['data'] ?? [],

                // CTA
                'showRegisterCTA' => config('Auth')->allowRegistration ?? true
            ];

            return view('public/home', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading home page: ' . $e->getMessage());

            // Return fallback view with minimal data
            return view('public/home', [
                'title' => 'Serikat Pekerja Kampus',
                'pageTitle' => 'Selamat Datang',
                'totalMembers' => 0,
                'totalProvinces' => 0,
                'totalUniversities' => 0,
                'growthPercentage' => 0,
                'latestPosts' => [],
                'featuredPages' => [],
                'showRegisterCTA' => true
            ]);
        }
    }

    /**
     * Display about page
     * Shows organization information, vision, mission, history
     * 
     * @return string
     */
    public function about(): string
    {
        try {
            // Get about page content
            $aboutContent = $this->contentService->getPageBySlug('tentang-spk');

            // Get organization structure/leadership
            $leadership = $this->getLeadershipInfo();

            $data = [
                'title' => 'Tentang Kami - Serikat Pekerja Kampus',
                'pageTitle' => 'Tentang Serikat Pekerja Kampus',
                'metaDescription' => 'Mengenal lebih dekat Serikat Pekerja Kampus, sejarah, visi, misi, dan struktur organisasi',
                'content' => $aboutContent['data'] ?? null,
                'leadership' => $leadership,

                // Quick stats
                'stats' => $this->statisticsService->getPublicStatistics()
            ];

            return view('public/about', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading about page: ' . $e->getMessage());

            return view('public/about', [
                'title' => 'Tentang Kami - SPK',
                'pageTitle' => 'Tentang Kami',
                'content' => null,
                'leadership' => [],
                'stats' => []
            ]);
        }
    }

    /**
     * Display contact page
     * Shows contact form and organization contact information
     * 
     * @return string
     */
    public function contact(): string
    {
        $data = [
            'title' => 'Hubungi Kami - Serikat Pekerja Kampus',
            'pageTitle' => 'Hubungi Kami',
            'metaDescription' => 'Hubungi Serikat Pekerja Kampus untuk informasi, konsultasi, atau bergabung sebagai anggota',

            // Contact information
            'contactInfo' => [
                'email' => 'info@spk.or.id',
                'phone' => '+62 21 1234 5678',
                'whatsapp' => '+62 812 3456 7890',
                'address' => 'Jl. Contoh No. 123, Jakarta Pusat, DKI Jakarta',
                'officeHours' => 'Senin - Jumat: 09.00 - 17.00 WIB'
            ],

            // Social media
            'socialMedia' => [
                'facebook' => 'https://facebook.com/spk',
                'twitter' => 'https://twitter.com/spk',
                'instagram' => 'https://instagram.com/spk',
                'youtube' => 'https://youtube.com/@spk'
            ]
        ];

        return view('public/contact', $data);
    }

    /**
     * Handle contact form submission
     * Creates a public ticket/inquiry
     * 
     * @return RedirectResponse
     */
    public function submitContact(): RedirectResponse
    {
        // Validation rules
        $validationRules = [
            'name' => [
                'label' => 'Nama',
                'rules' => 'required|min_length[3]|max_length[150]',
                'errors' => [
                    'required' => 'Nama harus diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'max_length' => 'Nama maksimal 150 karakter'
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid'
                ]
            ],
            'phone' => [
                'label' => 'Nomor Telepon',
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor telepon hanya boleh berisi angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit'
                ]
            ],
            'subject' => [
                'label' => 'Subjek',
                'rules' => 'required|min_length[5]|max_length[200]',
                'errors' => [
                    'required' => 'Subjek harus diisi',
                    'min_length' => 'Subjek minimal 5 karakter',
                    'max_length' => 'Subjek maksimal 200 karakter'
                ]
            ],
            'message' => [
                'label' => 'Pesan',
                'rules' => 'required|min_length[20]|max_length[2000]',
                'errors' => [
                    'required' => 'Pesan harus diisi',
                    'min_length' => 'Pesan minimal 20 karakter',
                    'max_length' => 'Pesan maksimal 2000 karakter'
                ]
            ],
            'category' => [
                'label' => 'Kategori',
                'rules' => 'required|in_list[informasi,konsultasi,pengaduan,keanggotaan,lainnya]',
                'errors' => [
                    'required' => 'Kategori harus dipilih',
                    'in_list' => 'Kategori tidak valid'
                ]
            ]
        ];

        // Validate input
        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            // Save contact inquiry to database (as a public ticket)
            $complaintModel = model('ComplaintModel');

            $ticketData = [
                'user_id' => null, // Public inquiry (no user)
                'ticket_number' => $this->generateTicketNumber(),
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'subject' => $this->request->getPost('subject'),
                'description' => $this->request->getPost('message'),
                'category' => $this->request->getPost('category'),
                'status' => 'open',
                'priority' => 'normal',
                'source' => 'contact_form',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $ticketId = $complaintModel->insert($ticketData);

            if (!$ticketId) {
                throw new \Exception('Gagal menyimpan pesan');
            }

            // Send notification email to admin
            $this->sendContactNotificationToAdmin($ticketData);

            // Send auto-reply to user
            $this->sendAutoReplyToUser($ticketData);

            return redirect()->back()
                ->with('success', 'Terima kasih! Pesan Anda telah dikirim. Kami akan merespon melalui email dalam 1-2 hari kerja.');
        } catch (\Exception $e) {
            log_message('error', 'Error submitting contact form: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi atau hubungi kami melalui email/WhatsApp.');
        }
    }

    /**
     * Display organization manifesto
     * 
     * @return string
     */
    public function manifesto(): string
    {
        try {
            $manifestoContent = $this->contentService->getPageBySlug('manifesto');

            $data = [
                'title' => 'Manifesto - Serikat Pekerja Kampus',
                'pageTitle' => 'Manifesto SPK',
                'content' => $manifestoContent['data'] ?? null
            ];

            return view('public/page', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading manifesto: ' . $e->getMessage());
            return view('errors/html/error_404');
        }
    }

    /**
     * Display organization AD/ART
     * 
     * @return string
     */
    public function adart(): string
    {
        try {
            $adartContent = $this->contentService->getPageBySlug('ad-art');

            $data = [
                'title' => 'AD/ART - Serikat Pekerja Kampus',
                'pageTitle' => 'Anggaran Dasar & Anggaran Rumah Tangga',
                'content' => $adartContent['data'] ?? null
            ];

            return view('public/page', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading AD/ART: ' . $e->getMessage());
            return view('errors/html/error_404');
        }
    }

    /**
     * Generate unique ticket number for contact inquiries
     * 
     * @return string
     */
    protected function generateTicketNumber(): string
    {
        $prefix = 'PUB';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        return $prefix . '-' . $date . '-' . $random;
    }

    /**
     * Get leadership information
     * 
     * @return array
     */
    protected function getLeadershipInfo(): array
    {
        try {
            // Get users with Pengurus role
            $userModel = model('UserModel');
            $leadership = $userModel->select('users.*, member_profiles.full_name, member_profiles.position')
                ->join('member_profiles', 'member_profiles.user_id = users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->join('auth_groups', 'auth_groups.id = auth_groups_users.group')
                ->where('auth_groups.group', 'Pengurus')
                ->where('member_profiles.position IS NOT NULL')
                ->orderBy('member_profiles.position_order', 'ASC')
                ->findAll(10);

            return $leadership ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error getting leadership info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send notification email to admin about new contact inquiry
     * 
     * @param array $ticketData Ticket data
     * @return void
     */
    protected function sendContactNotificationToAdmin(array $ticketData): void
    {
        try {
            $emailService = service('EmailService');

            $adminEmail = env('ADMIN_EMAIL', 'admin@spk.or.id');

            $emailData = [
                'to' => $adminEmail,
                'subject' => 'Pesan Baru dari Contact Form - ' . $ticketData['ticket_number'],
                'message' => view('emails/contact_notification_admin', ['ticket' => $ticketData])
            ];

            $emailService->send($emailData);
        } catch (\Exception $e) {
            log_message('error', 'Error sending admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send auto-reply email to user
     * 
     * @param array $ticketData Ticket data
     * @return void
     */
    protected function sendAutoReplyToUser(array $ticketData): void
    {
        try {
            $emailService = service('EmailService');

            $emailData = [
                'to' => $ticketData['email'],
                'subject' => 'Terima kasih atas pesan Anda - ' . $ticketData['ticket_number'],
                'message' => view('emails/contact_auto_reply', ['ticket' => $ticketData])
            ];

            $emailService->send($emailData);
        } catch (\Exception $e) {
            log_message('error', 'Error sending auto-reply: ' . $e->getMessage());
        }
    }
}
