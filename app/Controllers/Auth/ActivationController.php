<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\Member\MemberActivationService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ActivationController
 * 
 * Handle member account activation from email link
 * Set password, update profile, and activate account
 * 
 * @package App\Controllers\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ActivationController extends BaseController
{
    /**
     * @var MemberActivationService
     */
    protected $activationService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activationService = new MemberActivationService();
    }

    /**
     * Show activation form
     * Verify token and display set password form
     * 
     * @param string $token Activation token
     * @return string|ResponseInterface
     */
    public function index(string $token)
    {
        try {
            // Verify token
            $verification = $this->activationService->verifyToken($token);

            if (!$verification['success']) {
                $data = [
                    'title' => 'Aktivasi Akun',
                    'error' => $verification['message'],
                ];

                // Check if token expired
                if (isset($verification['data']['expired']) && $verification['data']['expired']) {
                    $data['expired'] = true;
                    $data['userId'] = $verification['data']['user_id'] ?? null;
                }

                // Check if already activated
                if (isset($verification['data']['already_activated']) && $verification['data']['already_activated']) {
                    $data['alreadyActivated'] = true;
                    $data['activatedAt'] = $verification['data']['activated_at'] ?? null;
                }

                return view('auth/activate', $data);
            }

            // Get member data for form
            $memberData = $this->activationService->getMemberDataForActivation($token);

            if (!$memberData['success']) {
                return view('auth/activate', [
                    'title' => 'Aktivasi Akun',
                    'error' => $memberData['message'],
                ]);
            }

            $data = [
                'title' => 'Aktivasi Akun',
                'token' => $token,
                'memberData' => $memberData['data']['member'],
            ];

            return view('auth/activate', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in ActivationController::index: ' . $e->getMessage());

            return view('auth/activate', [
                'title' => 'Aktivasi Akun',
                'error' => 'Terjadi kesalahan. Silakan coba lagi atau hubungi administrator.',
            ]);
        }
    }

    /**
     * Process activation
     * Set password and activate account
     * 
     * @param string $token Activation token
     * @return ResponseInterface
     */
    public function activate(string $token)
    {
        // Validate request
        $rules = [
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 8 karakter',
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password harus diisi',
                    'matches' => 'Konfirmasi password tidak sama dengan password',
                ]
            ],
            'agree_terms' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Anda harus menyetujui syarat dan ketentuan',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $password = $this->request->getPost('password');

            // Complete activation (verify token, set password, activate)
            $result = $this->activationService->completeActivation($token, $password);

            if (!$result['success']) {
                return redirect()->back()
                    ->with('error', $result['message']);
            }

            // Success - redirect to profile update page
            return redirect()->to('auth/update-profile/' . $token)
                ->with('success', 'Akun berhasil diaktivasi! Silakan lengkapi profil Anda.');
        } catch (\Exception $e) {
            log_message('error', 'Error in ActivationController::activate: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat aktivasi. Silakan coba lagi.');
        }
    }

    /**
     * Show update profile form after activation
     * 
     * @param string $token Activation token
     * @return string|ResponseInterface
     */
    public function updateProfile(string $token)
    {
        try {
            // Get member data
            $memberData = $this->activationService->getMemberDataForActivation($token);

            if (!$memberData['success']) {
                return redirect()->to('login')
                    ->with('error', 'Data member tidak ditemukan');
            }

            $data = [
                'title' => 'Lengkapi Profil',
                'token' => $token,
                'member' => $memberData['data']['member'],
                'user' => $memberData['data']['user'],
            ];

            return view('auth/update_profile', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in ActivationController::updateProfile: ' . $e->getMessage());

            return redirect()->to('login')
                ->with('error', 'Terjadi kesalahan. Silakan login.');
        }
    }

    /**
     * Process profile update
     * 
     * @param string $token Activation token
     * @return ResponseInterface
     */
    public function processProfileUpdate(string $token)
    {
        // Validate request
        $rules = [
            'phone' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor telepon harus diisi',
                    'numeric' => 'Nomor telepon harus berupa angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit',
                ]
            ],
            'whatsapp' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor WhatsApp harus diisi',
                    'numeric' => 'Nomor WhatsApp harus berupa angka',
                    'min_length' => 'Nomor WhatsApp minimal 10 digit',
                    'max_length' => 'Nomor WhatsApp maksimal 15 digit',
                ]
            ],
            'address' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Alamat harus diisi',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            // Verify token to get user ID
            $verification = $this->activationService->verifyToken($token);

            if (!$verification['success']) {
                return redirect()->to('login')
                    ->with('error', 'Token tidak valid');
            }

            $userId = $verification['data']['user']->id;

            // Prepare profile data
            $profileData = [
                'phone' => $this->request->getPost('phone'),
                'whatsapp' => $this->request->getPost('whatsapp'),
                'address' => $this->request->getPost('address'),
                'birth_place' => $this->request->getPost('birth_place'),
                'birth_date' => $this->request->getPost('birth_date'),
            ];

            // Handle photo upload
            $photo = $this->request->getFile('photo');
            if ($photo && $photo->isValid() && !$photo->hasMoved()) {
                $newName = $photo->getRandomName();
                $photo->move(WRITEPATH . 'uploads/members', $newName);
                $profileData['photo'] = $newName;
            }

            // Activate member with updated profile
            $result = $this->activationService->activateMember($userId, $profileData);

            if (!$result['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

            // Success - redirect to login or dashboard
            return redirect()->to('login')
                ->with('success', 'Profil berhasil dilengkapi! Silakan login dengan email dan password Anda.');
        } catch (\Exception $e) {
            log_message('error', 'Error in ActivationController::processProfileUpdate: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat update profil. Silakan coba lagi.');
        }
    }

    /**
     * Resend activation email
     * 
     * @return ResponseInterface
     */
    public function resendEmail()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('login');
        }

        try {
            $email = $this->request->getPost('email');

            if (empty($email)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email harus diisi'
                ]);
            }

            // TODO: Implement resend activation email
            // Find user by email
            // Check if not activated
            // Generate new token
            // Send email

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email aktivasi telah dikirim ulang. Silakan cek inbox Anda.'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in ActivationController::resendEmail: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Check activation status
     * 
     * @param int $userId User ID
     * @return ResponseInterface
     */
    public function checkStatus(int $userId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('login');
        }

        try {
            $result = $this->activationService->canActivate($userId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error in ActivationController::checkStatus: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ]);
        }
    }
}
