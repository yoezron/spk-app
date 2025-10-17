<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserModel;

/**
 * LoginController
 * 
 * Handles user authentication (login/logout)
 * Redirects users based on their roles
 * Logs login activities for security monitoring
 * 
 * @package App\Controllers\Auth
 * @author  SPK Development Team
 * @version 1.1.0 - FIXED: Role redirect logic
 */
class LoginController extends BaseController
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display login form
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // If already logged in, redirect to appropriate dashboard
        if (auth()->loggedIn()) {
            return $this->redirectBasedOnRole();
        }

        $data = [
            'title' => 'Login - SPK',
        ];

        return view('auth/login', $data);
    }

    /**
     * Handle login attempt
     * 
     * @return RedirectResponse
     */
    public function attempt(): RedirectResponse
    {
        // Validation rules
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Get credentials
        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        // Remember me option
        $remember = (bool) $this->request->getPost('remember');

        // Attempt login using Shield
        $loginAttempt = auth()->attempt($credentials, $remember);

        if (!$loginAttempt->isOK()) {
            return redirect()->back()
                ->withInput()
                ->with('error', $loginAttempt->reason());
        }

        // Get authenticated user
        $user = auth()->user();

        // Check if user is active
        if (isset($user->status) && $user->status !== 'active') {
            auth()->logout();
            return redirect()->back()
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
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
     * 🔧 FIXED: Role names matching database (lowercase, no spaces)
     * 
     * @return RedirectResponse
     */
    protected function redirectBasedOnRole(): RedirectResponse
    {
        $user = auth()->user();

        // Priority order: Check roles from highest to lowest

        // 1. Super Admin - Full system access
        if ($user->inGroup('superadmin')) {
            return redirect()->to('/super/dashboard');
        }

        // 2. Pengurus (Admin) - Administrative access
        if ($user->inGroup('pengurus')) {
            return redirect()->to('/admin/dashboard');
        }

        // 3. Koordinator Wilayah - Regional admin access
        if ($user->inGroup('koordinator')) {
            return redirect()->to('/admin/dashboard');
        }

        // 4. Anggota - Member portal
        if ($user->inGroup('anggota')) {
            return redirect()->to('/member/dashboard');
        }

        // 5. Calon Anggota - Limited member access
        if ($user->inGroup('calon_anggota')) {
            return redirect()->to('/member/dashboard')
                ->with('info', 'Akun Anda masih menunggu verifikasi dari pengurus.');
        }

        // Default: Redirect to member dashboard
        // This handles any edge cases or custom roles
        return redirect()->to('/member/dashboard');
    }

    /**
     * Log user login activity
     * 
     * @param object $user
     * @return void
     */
    protected function logLoginActivity($user): void
    {
        try {
            $loginLogModel = model('App\Models\LoginLogModel');

            $loginLogModel->insert([
                'user_id' => $user->id,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'login_at' => date('Y-m-d H:i:s'),
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log login activity: ' . $e->getMessage());
        }
    }

    /**
     * Log user logout activity
     * 
     * @param object $user
     * @return void
     */
    protected function logLogoutActivity($user): void
    {
        try {
            $loginLogModel = model('App\Models\LoginLogModel');

            $loginLogModel->insert([
                'user_id' => $user->id,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'logout_at' => date('Y-m-d H:i:s'),
                'status' => 'logout',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log logout activity: ' . $e->getMessage());
        }
    }
}
