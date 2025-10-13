<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\Communication\EmailService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * VerifyController
 * 
 * Menangani proses verifikasi email untuk anggota baru
 * Termasuk verify token, resend email, dan redirect setelah verifikasi
 * 
 * @package App\Controllers\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
class VerifyController extends BaseController
{
    /**
     * @var EmailService
     */
    protected $emailService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Display email verification page
     * Shows message to check email
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // If user is logged in and already verified, redirect
        if (auth()->loggedIn()) {
            $user = auth()->user();
            if ($user->email_verified_at) {
                return redirect()->to('/member/dashboard')
                    ->with('info', 'Email Anda sudah terverifikasi.');
            }
        }

        $data = [
            'title' => 'Verifikasi Email - Serikat Pekerja Kampus',
            'pageTitle' => 'Verifikasi Email Anda',
            'email' => session()->get('registration_email') ?? (auth()->loggedIn() ? auth()->user()->email : null)
        ];

        return view('auth/verify_email', $data);
    }

    /**
     * Handle email verification with token
     * Verifies the token and activates user account
     * 
     * @param string $token Verification token from email link
     * @return RedirectResponse
     */
    public function verify(string $token): RedirectResponse
    {
        if (empty($token)) {
            return redirect()->to('/verify-email')
                ->with('error', 'Token verifikasi tidak valid.');
        }

        try {
            // Find user by verification token
            $userModel = model('UserModel');
            $user = $userModel->where('verification_token', $token)->first();

            if (!$user) {
                return redirect()->to('/verify-email')
                    ->with('error', 'Token verifikasi tidak ditemukan atau sudah tidak berlaku.');
            }

            // Check if already verified
            if ($user->email_verified_at) {
                return redirect()->to('/login')
                    ->with('info', 'Email Anda sudah terverifikasi sebelumnya. Silakan login.');
            }

            // Check token expiration (24 hours)
            $tokenCreatedAt = strtotime($user->created_at);
            $currentTime = time();
            $expirationTime = 24 * 60 * 60; // 24 hours in seconds

            if (($currentTime - $tokenCreatedAt) > $expirationTime) {
                return redirect()->to('/verify-email')
                    ->with('error', 'Token verifikasi sudah kadaluarsa. Silakan minta token baru.')
                    ->with('can_resend', true)
                    ->with('user_email', $user->email);
            }

            // Update user email verification
            $updateData = [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'verification_token' => null,
                'active' => 1
            ];

            $userModel->update($user->id, $updateData);

            // Log verification activity
            $this->logVerificationActivity($user->id);

            // Auto-login user after verification
            auth()->loginById($user->id);

            // Check user role for appropriate redirect
            $redirectUrl = $this->getRedirectUrl($user);

            return redirect()->to($redirectUrl)
                ->with('success', 'Email berhasil diverifikasi! Selamat datang di Serikat Pekerja Kampus.');
        } catch (\Exception $e) {
            log_message('error', 'Email verification error: ' . $e->getMessage());

            return redirect()->to('/verify-email')
                ->with('error', 'Terjadi kesalahan saat verifikasi email. Silakan coba lagi.');
        }
    }

    /**
     * Resend verification email
     * Generates new token and sends new verification email
     * 
     * @return RedirectResponse
     */
    public function resend(): RedirectResponse
    {
        // Get email from POST or session
        $email = $this->request->getPost('email')
            ?? session()->get('registration_email')
            ?? (auth()->loggedIn() ? auth()->user()->email : null);

        if (empty($email)) {
            return redirect()->back()
                ->with('error', 'Email tidak ditemukan. Silakan daftar ulang.');
        }

        try {
            // Find user by email
            $userModel = model('UserModel');
            $identityModel = model('UserIdentityModel');

            // Get user by email from identities table
            $identity = $identityModel->where('type', 'email_password')
                ->where('secret', $email)
                ->first();

            if (!$identity) {
                return redirect()->back()
                    ->with('error', 'Email tidak ditemukan dalam sistem.');
            }

            $user = $userModel->find($identity->user_id);

            if (!$user) {
                return redirect()->back()
                    ->with('error', 'User tidak ditemukan.');
            }

            // Check if already verified
            if ($user->email_verified_at) {
                return redirect()->to('/login')
                    ->with('info', 'Email Anda sudah terverifikasi. Silakan login.');
            }

            // Check rate limiting (prevent spam)
            $lastResend = session()->get('last_verification_resend');
            if ($lastResend && (time() - $lastResend) < 60) {
                return redirect()->back()
                    ->with('warning', 'Mohon tunggu 1 menit sebelum mengirim ulang email verifikasi.');
            }

            // Generate new verification token
            $newToken = bin2hex(random_bytes(32));

            // Update user with new token
            $userModel->update($user->id, [
                'verification_token' => $newToken
            ]);

            // Send verification email
            $emailResult = $this->emailService->sendVerificationEmail($user, $newToken);

            // Set session for rate limiting
            session()->set('last_verification_resend', time());
            session()->set('registration_email', $email);

            if ($emailResult['success']) {
                return redirect()->back()
                    ->with('success', 'Email verifikasi telah dikirim ulang. Silakan cek inbox atau folder spam Anda.');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal mengirim email verifikasi: ' . $emailResult['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Resend verification error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengirim ulang email. Silakan coba lagi.');
        }
    }

    /**
     * Get redirect URL based on user role
     * 
     * @param object $user User entity
     * @return string Redirect URL
     */
    protected function getRedirectUrl($user): string
    {
        // Reload user to get updated groups
        $user = auth()->user();

        if ($user->inGroup('Super Admin')) {
            return '/super/dashboard';
        }

        if ($user->inGroup('Pengurus')) {
            return '/admin/dashboard';
        }

        if ($user->inGroup('Koordinator Wilayah')) {
            return '/admin/dashboard';
        }

        if ($user->inGroup('Anggota')) {
            return '/member/dashboard';
        }

        if ($user->inGroup('Calon Anggota')) {
            return '/member/dashboard';
        }

        // Default redirect
        return '/member/dashboard';
    }

    /**
     * Log email verification activity
     * 
     * @param int $userId User ID
     * @return void
     */
    protected function logVerificationActivity(int $userId): void
    {
        try {
            $auditModel = model('AuditLogModel');

            $auditModel->insert([
                'user_id' => $userId,
                'action' => 'email_verified',
                'description' => 'User verified their email address',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the verification process
            log_message('error', 'Failed to log verification activity: ' . $e->getMessage());
        }
    }

    /**
     * Check verification status (AJAX endpoint)
     * Useful for polling verification status
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function checkStatus()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        $user = auth()->user();

        return $this->response->setJSON([
            'success' => true,
            'verified' => !is_null($user->email_verified_at),
            'email' => $user->email
        ]);
    }
}
