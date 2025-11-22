<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Services\Member\CardGeneratorService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * CardController (Member Area)
 * 
 * Menangani kartu anggota digital
 * View card, download PDF, display QR code, check expiration
 * 
 * @package App\Controllers\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class CardController extends BaseController
{
    /**
     * @var CardGeneratorService
     */
    protected $cardService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->cardService = new CardGeneratorService();
    }

    /**
     * Display digital member card
     * Shows card preview with QR code
     * 
     * @return string
     */
    public function index(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get member profile with relations
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->select('member_profiles.*, 
                                           provinces.name as province_name,
                                           universities.name as university_name,
                                           users.active as user_active')
                ->join('provinces', 'provinces.id = member_profiles.wilayah_id', 'left')
                ->join('universities', 'universities.id = member_profiles.kampus_id', 'left')
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('member_profiles.user_id', $user->id)
                ->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            // Check if member is verified/active
            if (!$user->inGroup('Anggota')) {
                return view('member/card/pending', [
                    'title' => 'Kartu Anggota - Menunggu Verifikasi',
                    'pageTitle' => 'Kartu Anggota',
                    'member' => $member
                ]);
            }

            // Generate QR code data (verification URL)
            $verificationToken = $member->card_verification_token ?? $this->generateVerificationToken($member->id);
            $qrCodeData = base_url('verify-card?token=' . $verificationToken);

            // Check card expiration
            $cardStatus = $this->getCardStatus($member);

            $data = [
                'title' => 'Kartu Anggota Digital - Serikat Pekerja Kampus',
                'pageTitle' => 'Kartu Anggota Digital',
                'member' => $member,
                'qrCodeData' => $qrCodeData,
                'verificationToken' => $verificationToken,
                'cardStatus' => $cardStatus,
                'canDownload' => $cardStatus['status'] !== 'expired'
            ];

            return view('member/card/qrcode', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading member card: ' . $e->getMessage());

            return redirect()->to('/member/dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat kartu anggota.');
        }
    }

    /**
     * Download member card as PDF
     * Generates PDF with QR code
     * 
     * @return mixed PDF download or redirect
     */
    public function download()
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Check if member is active
            if (!$user->inGroup('Anggota')) {
                return redirect()->to('/member/card')
                    ->with('error', 'Kartu anggota hanya tersedia untuk anggota terverifikasi.');
            }

            // Get member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            // Check if card is expired
            $cardStatus = $this->getCardStatus($member);
            if ($cardStatus['status'] === 'expired') {
                return redirect()->to('/member/card')
                    ->with('error', 'Kartu anggota Anda sudah kadaluarsa. Silakan hubungi pengurus untuk perpanjangan.');
            }

            // Generate PDF card
            $result = $this->cardService->generateCard($member->id);

            if (!$result['success']) {
                return redirect()->to('/member/card')
                    ->with('error', $result['message']);
            }

            // Log download activity
            $this->logCardDownload($user->id);

            // Get PDF path
            $pdfPath = $result['data']['file_path'];
            $fileName = 'Kartu_Anggota_' . $member->member_number . '.pdf';

            // Force download
            return $this->response->download($pdfPath, null)
                ->setFileName($fileName);
        } catch (\Exception $e) {
            log_message('error', 'Error downloading member card: ' . $e->getMessage());

            return redirect()->to('/member/card')
                ->with('error', 'Terjadi kesalahan saat mengunduh kartu anggota.');
        }
    }

    /**
     * Display QR code only (for printing/sharing)
     * 
     * @return string
     */
    public function qrcode(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            // Generate verification token if not exists
            $verificationToken = $member->card_verification_token ?? $this->generateVerificationToken($member->id);
            $qrCodeData = base_url('verify-card?token=' . $verificationToken);

            $data = [
                'title' => 'QR Code Kartu Anggota',
                'pageTitle' => 'QR Code Verifikasi',
                'member' => $member,
                'qrCodeData' => $qrCodeData,
                'verificationToken' => $verificationToken
            ];

            return view('member/card/qrcode', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading QR code: ' . $e->getMessage());

            return redirect()->to('/member/card')
                ->with('error', 'Terjadi kesalahan saat memuat QR code.');
        }
    }

    /**
     * Request card renewal
     * For expired or expiring cards
     * 
     * @return string|RedirectResponse
     */
    public function renew()
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            $cardStatus = $this->getCardStatus($member);

            $data = [
                'title' => 'Perpanjang Kartu Anggota',
                'pageTitle' => 'Perpanjang Kartu Anggota',
                'member' => $member,
                'cardStatus' => $cardStatus
            ];

            return view('member/card/renew', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading card renewal: ' . $e->getMessage());

            return redirect()->to('/member/card')
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Submit card renewal request
     * 
     * @return RedirectResponse
     */
    public function submitRenewal(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            // Create renewal request (as a ticket/complaint)
            $complaintModel = model('ComplaintModel');

            $ticketData = [
                'user_id' => $user->id,
                'ticket_number' => $this->generateTicketNumber(),
                'subject' => 'Perpanjangan Kartu Anggota - ' . $member->member_number,
                'description' => 'Permintaan perpanjangan kartu anggota yang akan/sudah kadaluarsa.',
                'category' => 'perpanjangan_kartu',
                'status' => 'open',
                'priority' => 'normal',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $complaintModel->insert($ticketData);

            // Send notification to admin
            $this->sendRenewalNotificationToAdmin($member, $ticketData);

            return redirect()->to('/member/card')
                ->with('success', 'Permintaan perpanjangan kartu telah dikirim. Pengurus akan menghubungi Anda segera.');
        } catch (\Exception $e) {
            log_message('error', 'Error submitting card renewal: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengirim permintaan.');
        }
    }

    /**
     * Get card status (active, expiring, expired)
     * 
     * @param object $member Member entity
     * @return array
     */
    protected function getCardStatus($member): array
    {
        if (empty($member->join_date)) {
            return [
                'status' => 'unknown',
                'label' => 'Tidak Diketahui',
                'class' => 'secondary',
                'message' => 'Tanggal bergabung tidak tersedia'
            ];
        }

        // Card validity: 3 years from join date
        $joinDate = strtotime($member->join_date);
        $expirationDate = strtotime($member->join_date . ' + 3 years');
        $today = time();

        $daysUntilExpiration = floor(($expirationDate - $today) / (60 * 60 * 24));

        if ($daysUntilExpiration < 0) {
            // Expired
            return [
                'status' => 'expired',
                'label' => 'Kadaluarsa',
                'class' => 'danger',
                'message' => 'Kartu Anda sudah kadaluarsa',
                'expiration_date' => date('d F Y', $expirationDate),
                'days_until_expiration' => $daysUntilExpiration
            ];
        } elseif ($daysUntilExpiration <= 30) {
            // Expiring soon (within 30 days)
            return [
                'status' => 'expiring',
                'label' => 'Akan Kadaluarsa',
                'class' => 'warning',
                'message' => 'Kartu Anda akan kadaluarsa dalam ' . $daysUntilExpiration . ' hari',
                'expiration_date' => date('d F Y', $expirationDate),
                'days_until_expiration' => $daysUntilExpiration
            ];
        } else {
            // Active
            return [
                'status' => 'active',
                'label' => 'Aktif',
                'class' => 'success',
                'message' => 'Kartu Anda aktif',
                'expiration_date' => date('d F Y', $expirationDate),
                'days_until_expiration' => $daysUntilExpiration
            ];
        }
    }

    /**
     * Generate verification token for card
     * 
     * @param int $memberId Member ID
     * @return string
     */
    protected function generateVerificationToken(int $memberId): string
    {
        try {
            // Generate unique token
            $token = bin2hex(random_bytes(16));

            // Save to database
            $memberModel = model('MemberProfileModel');
            $memberModel->update($memberId, [
                'card_verification_token' => $token
            ]);

            return $token;
        } catch (\Exception $e) {
            log_message('error', 'Error generating verification token: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate ticket number for renewal request
     * 
     * @return string
     */
    protected function generateTicketNumber(): string
    {
        $prefix = 'RNW';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        return $prefix . '-' . $date . '-' . $random;
    }

    /**
     * Log card download activity
     * 
     * @param int $userId User ID
     * @return void
     */
    protected function logCardDownload(int $userId): void
    {
        try {
            $auditModel = model('AuditLogModel');

            $auditModel->insert([
                'user_id' => $userId,
                'action' => 'card_downloaded',
                'description' => 'Member downloaded their card',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log card download: ' . $e->getMessage());
        }
    }

    /**
     * Send renewal notification to admin
     * 
     * @param object $member Member entity
     * @param array $ticketData Ticket data
     * @return void
     */
    protected function sendRenewalNotificationToAdmin($member, array $ticketData): void
    {
        try {
            $emailService = service('EmailService');

            $adminEmail = env('ADMIN_EMAIL', 'admin@spk.or.id');

            $emailData = [
                'to' => $adminEmail,
                'subject' => 'Permintaan Perpanjangan Kartu - ' . $member->member_number,
                'message' => view('emails/card_renewal_notification', [
                    'member' => $member,
                    'ticket' => $ticketData
                ])
            ];

            $emailService->send($emailData);
        } catch (\Exception $e) {
            log_message('error', 'Error sending renewal notification: ' . $e->getMessage());
        }
    }

    /**
     * Display card history/logs
     * Shows when card was issued, downloaded, etc.
     * 
     * @return string
     */
    public function history(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get card-related audit logs
            $auditModel = model('AuditLogModel');
            $logs = $auditModel->where('user_id', $user->id)
                ->whereIn('action', ['card_generated', 'card_downloaded', 'card_verified'])
                ->orderBy('created_at', 'DESC')
                ->findAll(50);

            $data = [
                'title' => 'Riwayat Kartu Anggota',
                'pageTitle' => 'Riwayat Kartu',
                'logs' => $logs
            ];

            return view('member/card/history', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading card history: ' . $e->getMessage());

            return redirect()->to('/member/card')
                ->with('error', 'Terjadi kesalahan saat memuat riwayat.');
        }
    }
}
