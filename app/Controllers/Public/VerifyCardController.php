<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * VerifyCardController
 * 
 * Menangani verifikasi kartu anggota SPK secara public
 * Verify menggunakan QR code atau member number
 * 
 * @package App\Controllers\Public
 * @author  SPK Development Team
 * @version 1.0.0
 */
class VerifyCardController extends BaseController
{
    /**
     * Display card verification form
     * Public page untuk verify kartu anggota
     * 
     * @return string
     */
    public function index(): string
    {
        $data = [
            'title' => 'Verifikasi Kartu Anggota - Serikat Pekerja Kampus',
            'pageTitle' => 'Verifikasi Kartu Anggota',
            'metaDescription' => 'Verifikasi keaslian kartu anggota Serikat Pekerja Kampus secara online'
        ];

        return view('public/verify_card', $data);
    }

    /**
     * Verify member card
     * Accepts member number or QR code token
     * 
     * @return string|RedirectResponse
     */
    public function verify()
    {
        // Get verification input (member number or token)
        $input = $this->request->getPost('verification_code')
            ?? $this->request->getGet('code')
            ?? $this->request->getGet('token');

        if (empty($input)) {
            return redirect()->to('/verify-card')
                ->with('error', 'Silakan masukkan nomor anggota atau scan QR code.');
        }

        try {
            // Sanitize input
            $input = trim($input);

            // Try to find member by member number or verification token
            $member = $this->findMemberByCode($input);

            if (!$member) {
                return $this->showVerificationResult(false, 'Kartu anggota tidak ditemukan atau tidak valid.');
            }

            // Check if member is active
            if ($member->membership_status !== 'active') {
                return $this->showVerificationResult(false, 'Kartu anggota tidak aktif.', $member);
            }

            // Check if user account is active
            if (!$member->user_active) {
                return $this->showVerificationResult(false, 'Akun anggota tidak aktif.', $member);
            }

            // Check card expiration (if applicable)
            if ($this->isCardExpired($member)) {
                return $this->showVerificationResult(false, 'Kartu anggota sudah kadaluarsa.', $member);
            }

            // Log verification attempt
            $this->logVerification($member->id, true);

            // Card is valid
            return $this->showVerificationResult(true, 'Kartu anggota valid dan aktif.', $member);
        } catch (\Exception $e) {
            log_message('error', 'Error verifying card: ' . $e->getMessage());

            return redirect()->to('/verify-card')
                ->with('error', 'Terjadi kesalahan saat memverifikasi kartu. Silakan coba lagi.');
        }
    }

    /**
     * Quick verify endpoint (AJAX)
     * Returns JSON response for quick validation
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function quickVerify()
    {
        $code = $this->request->getPost('code') ?? $this->request->getGet('code');

        if (empty($code)) {
            return $this->response->setJSON([
                'success' => false,
                'valid' => false,
                'message' => 'Kode verifikasi tidak boleh kosong'
            ]);
        }

        try {
            $member = $this->findMemberByCode($code);

            if (!$member) {
                return $this->response->setJSON([
                    'success' => true,
                    'valid' => false,
                    'message' => 'Kartu tidak ditemukan'
                ]);
            }

            $isValid = ($member->membership_status === 'active'
                && $member->user_active
                && !$this->isCardExpired($member));

            // Log verification
            $this->logVerification($member->id, $isValid);

            if ($isValid) {
                return $this->response->setJSON([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Kartu valid',
                    'data' => [
                        'member_number' => $member->member_number,
                        'full_name' => $member->full_name,
                        'university' => $member->university_name ?? 'N/A',
                        'province' => $member->province_name ?? 'N/A',
                        'join_date' => date('d/m/Y', strtotime($member->join_date)),
                        'verified_at' => date('d/m/Y H:i', strtotime($member->verified_at))
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => true,
                    'valid' => false,
                    'message' => 'Kartu tidak aktif atau kadaluarsa'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in quick verify: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'valid' => false,
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Show verification result page
     * 
     * @param bool $isValid Whether card is valid
     * @param string $message Verification message
     * @param object|null $member Member data (optional)
     * @return string
     */
    protected function showVerificationResult(bool $isValid, string $message, $member = null): string
    {
        $data = [
            'title' => 'Hasil Verifikasi - Serikat Pekerja Kampus',
            'pageTitle' => 'Hasil Verifikasi Kartu',
            'isValid' => $isValid,
            'message' => $message,
            'member' => $member ? $this->prepareMemberData($member) : null
        ];

        return view('public/verify_result', $data);
    }

    /**
     * Find member by member number or verification token
     * 
     * @param string $code Member number or token
     * @return object|null Member data
     */
    protected function findMemberByCode(string $code): ?object
    {
        try {
            $memberModel = model('MemberProfileModel');

            // Try to find by member number first
            $member = $memberModel->select('member_profiles.*, users.active as user_active, 
                                           provinces.name as province_name,
                                           universities.name as university_name')
                ->join('users', 'users.id = member_profiles.user_id')
                ->join('provinces', 'provinces.id = member_profiles.wilayah_id', 'left')
                ->join('universities', 'universities.id = member_profiles.kampus_id', 'left')
                ->where('member_profiles.member_number', $code)
                ->first();

            // If not found, try to find by verification token (QR code)
            if (!$member) {
                $member = $memberModel->select('member_profiles.*, users.active as user_active,
                                               provinces.name as province_name,
                                               universities.name as university_name')
                    ->join('users', 'users.id = member_profiles.user_id')
                    ->join('provinces', 'provinces.id = member_profiles.wilayah_id', 'left')
                    ->join('universities', 'universities.id = member_profiles.kampus_id', 'left')
                    ->where('member_profiles.card_verification_token', $code)
                    ->first();
            }

            return $member;
        } catch (\Exception $e) {
            log_message('error', 'Error finding member by code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if card is expired
     * Cards are valid for 3 years from join date
     * 
     * @param object $member Member data
     * @return bool
     */
    protected function isCardExpired($member): bool
    {
        if (empty($member->join_date)) {
            return false; // No join date, consider not expired
        }

        // Card validity period (3 years)
        $validityYears = 3;
        $expirationDate = date('Y-m-d', strtotime($member->join_date . ' + ' . $validityYears . ' years'));
        $currentDate = date('Y-m-d');

        return $currentDate > $expirationDate;
    }

    /**
     * Prepare member data for public display
     * Remove sensitive information
     * 
     * @param object $member Member data
     * @return array
     */
    protected function prepareMemberData($member): array
    {
        $joinDate = $member->join_date ? date('d F Y', strtotime($member->join_date)) : 'N/A';
        $verifiedDate = $member->verified_at ? date('d F Y', strtotime($member->verified_at)) : 'N/A';

        // Calculate expiration date
        $expirationDate = 'N/A';
        if ($member->join_date) {
            $expirationDate = date('d F Y', strtotime($member->join_date . ' + 3 years'));
        }

        return [
            'member_number' => $member->member_number,
            'full_name' => $member->full_name,
            'university' => $member->university_name ?? 'N/A',
            'province' => $member->province_name ?? 'N/A',
            'join_date' => $joinDate,
            'verified_date' => $verifiedDate,
            'expiration_date' => $expirationDate,
            'status' => $this->getStatusLabel($member->membership_status),
            'is_expired' => $this->isCardExpired($member),
            'foto_url' => $this->getMemberPhotoUrl($member)
        ];
    }

    /**
     * Get status label in Indonesian
     * 
     * @param string $status Status code
     * @return string
     */
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'suspended' => 'Disuspen',
            'pending' => 'Menunggu Verifikasi'
        ];

        return $labels[$status] ?? 'Tidak Diketahui';
    }

    /**
     * Get member photo URL (public path)
     * 
     * @param object $member Member data
     * @return string|null
     */
    protected function getMemberPhotoUrl($member): ?string
    {
        if (empty($member->foto_path)) {
            return null;
        }

        // Check if file exists in writable/uploads
        $fullPath = WRITEPATH . 'uploads/' . $member->foto_path;

        if (!file_exists($fullPath)) {
            return null;
        }

        // Return public URL (you may need to configure this based on your setup)
        // For now, return null as photos are in writable directory (not public)
        // You may need to implement a photo proxy controller
        return base_url('member/photo/' . $member->id);
    }

    /**
     * Log verification attempt
     * 
     * @param int $memberId Member ID
     * @param bool $isValid Whether verification was successful
     * @return void
     */
    protected function logVerification(int $memberId, bool $isValid): void
    {
        try {
            $auditModel = model('AuditLogModel');

            $auditModel->insert([
                'user_id' => $memberId,
                'action' => 'card_verification',
                'description' => $isValid ? 'Card verified successfully' : 'Card verification failed',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the verification
            log_message('error', 'Failed to log verification: ' . $e->getMessage());
        }
    }

    /**
     * Get verification statistics (for admin)
     * Returns total verifications, success rate, etc.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function statistics()
    {
        // Check if user is admin
        if (!auth()->loggedIn() || !auth()->user()->can('member.manage')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        try {
            $auditModel = model('AuditLogModel');

            // Get verification stats from last 30 days
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

            $totalVerifications = $auditModel->where('action', 'card_verification')
                ->where('created_at >=', $thirtyDaysAgo)
                ->countAllResults();

            $successfulVerifications = $auditModel->where('action', 'card_verification')
                ->where('description', 'Card verified successfully')
                ->where('created_at >=', $thirtyDaysAgo)
                ->countAllResults();

            $successRate = $totalVerifications > 0
                ? round(($successfulVerifications / $totalVerifications) * 100, 2)
                : 0;

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'total_verifications' => $totalVerifications,
                    'successful_verifications' => $successfulVerifications,
                    'failed_verifications' => $totalVerifications - $successfulVerifications,
                    'success_rate' => $successRate,
                    'period' => 'Last 30 days'
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting verification statistics: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting statistics'
            ]);
        }
    }
}
