<?php

namespace App\Services\Member;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use App\Services\Communication\EmailService;
use CodeIgniter\I18n\Time;

/**
 * MemberActivationService
 * 
 * Menangani aktivasi akun member yang diimport
 * Verifikasi token, set password, update profil, dan aktivasi akun
 * 
 * @package App\Services\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MemberActivationService
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var EmailService
     */
    protected $emailService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
        $this->emailService = new EmailService();
    }

    /**
     * Verify activation token
     * Check if token is valid and not expired
     * 
     * @param string $token Activation token
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function verifyToken(string $token): array
    {
        try {
            if (empty($token)) {
                return [
                    'success' => false,
                    'message' => 'Token aktivasi tidak valid',
                    'data' => null
                ];
            }

            // Find user by token
            $user = $this->userModel
                ->where('activation_token', $token)
                ->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Token aktivasi tidak ditemukan atau tidak valid',
                    'data' => null
                ];
            }

            // Check if already activated
            if (!empty($user->activated_at)) {
                return [
                    'success' => false,
                    'message' => 'Akun sudah diaktivasi sebelumnya',
                    'data' => [
                        'already_activated' => true,
                        'activated_at' => $user->activated_at
                    ]
                ];
            }

            // Check if token expired
            if (
                empty($user->activation_token_expires_at) ||
                strtotime($user->activation_token_expires_at) < time()
            ) {
                return [
                    'success' => false,
                    'message' => 'Token aktivasi sudah kadaluarsa. Silakan minta pengiriman ulang email aktivasi.',
                    'data' => [
                        'expired' => true,
                        'user_id' => $user->id
                    ]
                ];
            }

            // Token valid
            return [
                'success' => true,
                'message' => 'Token valid',
                'data' => [
                    'user' => $user,
                    'expires_at' => $user->activation_token_expires_at
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in verifyToken: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error verifikasi token: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Set new password for user
     * Hash and update password, clear activation token
     * 
     * @param int $userId User ID
     * @param string $password New password
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function setPassword(int $userId, string $password): array
    {
        try {
            // Validate password
            if (strlen($password) < 8) {
                return [
                    'success' => false,
                    'message' => 'Password minimal 8 karakter',
                    'data' => null
                ];
            }

            // Get user
            $user = $this->userModel->find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            // Update password using Shield
            $users = auth()->getProvider();
            $userEntity = $users->findById($userId);

            if (!$userEntity) {
                return [
                    'success' => false,
                    'message' => 'User entity tidak ditemukan',
                    'data' => null
                ];
            }

            // Update password
            $userEntity->password = $password;
            $users->save($userEntity);

            return [
                'success' => true,
                'message' => 'Password berhasil diset',
                'data' => ['user_id' => $userId]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in setPassword: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error set password: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Activate member account
     * Update profile, set status active, clear token, send welcome email
     * 
     * @param int $userId User ID
     * @param array $profileData Updated profile data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function activateMember(int $userId, array $profileData = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get user
            $user = $this->userModel->find($userId);
            if (!$user) {
                throw new \Exception('User tidak ditemukan');
            }

            // Get member profile
            $member = $this->memberModel->where('user_id', $userId)->first();
            if (!$member) {
                throw new \Exception('Member profile tidak ditemukan');
            }

            // Generate member number if empty
            if (empty($member->member_number)) {
                $memberNumber = $this->generateMemberNumber();
                $profileData['member_number'] = $memberNumber;
            }

            // Update member profile with new data
            if (!empty($profileData)) {
                $this->memberModel->update($member->id, $profileData);
            }

            // Update membership status to active
            $this->memberModel->update($member->id, [
                'membership_status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Activate user and clear token
            $this->userModel->update($userId, [
                'active' => 1,
                'activated_at' => date('Y-m-d H:i:s'),
                'activation_token' => null,
                'activation_token_expires_at' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Reload member data
            $member = $this->memberModel->where('user_id', $userId)->first();

            // Send welcome email
            $this->sendWelcomeEmail($user, $member);

            return [
                'success' => true,
                'message' => 'Akun berhasil diaktivasi',
                'data' => [
                    'user_id' => $userId,
                    'member_number' => $member->member_number,
                    'activated_at' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in activateMember: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error aktivasi member: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Complete activation flow
     * Set password and activate member in one transaction
     * 
     * @param string $token Activation token
     * @param string $password New password
     * @param array $profileData Updated profile data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function completeActivation(string $token, string $password, array $profileData = []): array
    {
        try {
            // Verify token first
            $verification = $this->verifyToken($token);
            if (!$verification['success']) {
                return $verification;
            }

            $user = $verification['data']['user'];

            // Set password
            $passwordResult = $this->setPassword($user->id, $password);
            if (!$passwordResult['success']) {
                return $passwordResult;
            }

            // Activate member
            $activationResult = $this->activateMember($user->id, $profileData);
            if (!$activationResult['success']) {
                return $activationResult;
            }

            return [
                'success' => true,
                'message' => 'Aktivasi berhasil! Akun Anda sudah aktif.',
                'data' => $activationResult['data']
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in completeActivation: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error aktivasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send welcome email after activation
     * 
     * @param object $user User object
     * @param object $member Member profile object
     * @return void
     */
    protected function sendWelcomeEmail(object $user, object $member): void
    {
        try {
            $data = [
                'subject' => 'Selamat! Akun SPK Anda Sudah Aktif',
                'name' => $member->full_name ?? $user->username,
                'member_number' => $member->member_number,
                'login_url' => base_url('login'),
                'profile_url' => base_url('member/profile'),
                'card_url' => base_url('member/card'),
            ];

            $this->emailService->sendTemplate(
                $user->email,
                'emails/welcome_activated',
                $data
            );
        } catch (\Exception $e) {
            log_message('error', 'Error sending welcome email: ' . $e->getMessage());
            // Don't throw exception, just log it
        }
    }

    /**
     * Generate member number
     * Format: SPK-YYYY-XXXXX
     * 
     * @return string
     */
    protected function generateMemberNumber(): string
    {
        $year = date('Y');
        $prefix = 'SPK-' . $year . '-';

        // Get last member number for current year
        $lastMember = $this->memberModel
            ->like('member_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastMember && !empty($lastMember->member_number)) {
            // Extract number part and increment
            $lastNumber = (int) substr($lastMember->member_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // First member of the year
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get member data for activation form
     * 
     * @param string $token Activation token
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getMemberDataForActivation(string $token): array
    {
        try {
            // Verify token
            $verification = $this->verifyToken($token);
            if (!$verification['success']) {
                return $verification;
            }

            $user = $verification['data']['user'];

            // Get member profile
            $member = $this->memberModel
                ->select('member_profiles.*, master_provinces.name as province_name, 
                         master_universities.name as university_name')
                ->join('master_provinces', 'master_provinces.id = member_profiles.province_id', 'left')
                ->join('master_universities', 'master_universities.id = member_profiles.university_id', 'left')
                ->where('member_profiles.user_id', $user->id)
                ->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Member profile tidak ditemukan',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'Data member berhasil diambil',
                'data' => [
                    'user' => $user,
                    'member' => $member,
                    'token' => $token
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in getMemberDataForActivation: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error mengambil data member: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if user can be activated
     * 
     * @param int $userId User ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function canActivate(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ];
            }

            if (!empty($user->activated_at)) {
                return [
                    'success' => false,
                    'message' => 'User sudah diaktivasi',
                    'data' => ['already_activated' => true]
                ];
            }

            $member = $this->memberModel->where('user_id', $userId)->first();
            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Member profile tidak ditemukan',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'User dapat diaktivasi',
                'data' => [
                    'user' => $user,
                    'member' => $member
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in canActivate: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
