<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * LoginController
 * 
 * Menangani proses login dan logout user menggunakan CodeIgniter Shield
 * Termasuk validasi, redirect berdasarkan role, dan session management
 * 
 * @package App\Controllers\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
class LoginController extends BaseController
{
    /**
     * Display login form
     * Redirect to dashboard if user already logged in
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // Check if user is already logged in
        if (auth()->loggedIn()) {
            return $this->redirectBasedOnRole();
        }

        $data = [
            'title' => 'Login - Serikat Pekerja Kampus',
            'pageTitle' => 'Login ke Akun Anda'
        ];

        return view('auth/login', $data);
    }

    /**
     * Handle login attempt
     * Validates credentials and creates session
     * 
     * @return RedirectResponse
     */
    public function attempt(): RedirectResponse
    {
        // Validation rules
        $rules = [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Password harus diisi'
                ]
            ]
        ];

        // Validate input
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Get credentials
        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password')
        ];

        // Get remember me option
        $remember = (bool) $this->request->getPost('remember');

        // Attempt to login using Shield
        $result = auth()->attempt($credentials, $remember);

        if (!$result->isOK()) {
            // Login failed
            return redirect()->back()
                ->withInput()
                ->with('error', $result->reason());
        }

        // Login successful
        $user = auth()->user();

        // Check if user account is active
        if (!$user->active) {
            auth()->logout();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda belum diaktifkan. Silakan hubungi administrator.');
        }

        // Check if user has verified email
        if (!$user->email_verified_at && setting('Auth.requireEmailVerification')) {
            auth()->logout();
            return redirect()->route('verify.email')
                ->with('info', 'Silakan verifikasi email Anda terlebih dahulu.');
        }

        // Log the login activity
        $this->logLoginActivity($user);

        // Success message
        session()->setFlashdata('success', 'Selamat datang kembali, ' . ($user->username ?? 'User') . '!');

        // Redirect based on user role
        return $this->redirectBasedOnRole();
    }

    /**
     * Handle logout
     * Destroys session and redirects to login page
     * 
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        // Get user before logout for logging
        $user = auth()->user();

        if ($user) {
            $this->logLogoutActivity($user);
        }

        // Logout using Shield
        auth()->logout();

        // Clear all session data
        session()->destroy();

        // Redirect to login with message
        return redirect()->to('/login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Redirect user based on their role/group
     * 
     * @return RedirectResponse
     */
    protected function redirectBasedOnRole(): RedirectResponse
    {
        $user = auth()->user();

        // Check user groups/roles
        if ($user->inGroup('Super Admin')) {
            return redirect()->to('/super/dashboard');
        }

        if ($user->inGroup('Pengurus')) {
            return redirect()->to('/admin/dashboard');
        }

        if ($user->inGroup('Koordinator Wilayah')) {
            return redirect()->to('/admin/dashboard');
        }

        if ($user->inGroup('Anggota')) {
            return redirect()->to('/member/dashboard');
        }

        if ($user->inGroup('Calon Anggota')) {
            return redirect()->to('/member/dashboard')
                ->with('info', 'Akun Anda masih menunggu verifikasi dari pengurus.');
        }

        // Default redirect if no specific role
        return redirect()->to('/');
    }

    /**
     * Log successful login activity
     * 
     * @param object $user User entity
     * @return void
     */
    protected function logLoginActivity($user): void
    {
        try {
            $auditModel = model('AuditLogModel');

            $auditModel->insert([
                'user_id' => $user->id,
                'action' => 'login',
                'description' => 'User logged in successfully',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the login process
            log_message('error', 'Failed to log login activity: ' . $e->getMessage());
        }
    }

    /**
     * Log logout activity
     * 
     * @param object $user User entity
     * @return void
     */
    protected function logLogoutActivity($user): void
    {
        try {
            $auditModel = model('AuditLogModel');

            $auditModel->insert([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the logout process
            log_message('error', 'Failed to log logout activity: ' . $e->getMessage());
        }
    }
}
