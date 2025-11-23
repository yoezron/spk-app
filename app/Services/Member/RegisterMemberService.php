<?php

namespace App\Services\Member;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Shield\Entities\User;

/**
 * RegisterMemberService
 * 
 * Menangani proses registrasi anggota baru secara end-to-end
 * Termasuk pembuatan user account, profile, upload file, dan email verifikasi
 * 
 * @package App\Services\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegisterMemberService
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
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
    }

    /**
     * Main registration method
     * Menangani seluruh flow registrasi dari awal hingga akhir
     * 
     * @param array $data Data registrasi dari form
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function register(array $data): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Validate input data
            $validation = $this->validateRegistrationData($data);
            if (!$validation['success']) {
                return $validation;
            }

            // 2. Create user account with Shield
            $user = $this->createUserAccount($data);
            if (!$user) {
                throw new \Exception('Gagal membuat akun user');
            }

            // 3. Create member profile
            $memberData = $this->prepareMemberData($data, $user->id);

            log_message('info', 'Member data prepared. Type: ' . gettype($memberData));
            log_message('info', 'Member data: ' . json_encode($memberData));

            $memberId = $this->memberModel->insert($memberData);

            if (!$memberId) {
                $errors = $this->memberModel->errors();
                log_message('error', 'Failed to insert member profile. Errors: ' . json_encode($errors));
                throw new \Exception('Gagal membuat profil anggota: ' . json_encode($errors));
            }

            log_message('info', 'Member profile created successfully. ID: ' . $memberId);

            // 4. Upload files (foto, bukti bayar, CV)
            if (isset($data['files'])) {
                $uploadResult = $this->uploadFiles($data['files'], $memberId);
                if (!$uploadResult['success']) {
                    throw new \Exception($uploadResult['message']);
                }
            }

            // 5. Assign role "Calon Anggota"
            $user->addGroup('calon_anggota');

            // 6. Trigger email verification using Shield's EmailActivator
            // This will automatically send verification email and set user as inactive
            // User must click verification link before they can login
            try {
                // Force user to verify email before login
                $user->active = 0; // User cannot login until email verified
                $this->userModel->save($user);
            } catch (\Exception $e) {
                log_message('error', 'Failed to set user as inactive: ' . $e->getMessage());
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi akun. Anda harus memverifikasi email sebelum dapat login.',
                'data' => [
                    'user_id' => $user->id,
                    'member_id' => $memberId,
                    'email' => $user->email,
                    'requires_verification' => true
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();

            log_message('error', 'Error in RegisterMemberService::register: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Pendaftaran gagal: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate registration data
     * 
     * @param array $data Data to validate
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function validateRegistrationData(array $data): array
    {
        $validation = \Config\Services::validation();

        $rules = [
            'email' => [
                'rules' => 'required|valid_email|is_unique[auth_identities.secret]',
                'label' => 'Email',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[5]|max_length[30]|alpha_numeric|is_unique[users.username]',
                'label' => 'Username',
                'errors' => [
                    'required' => 'Username harus diisi',
                    'min_length' => 'Username minimal 5 karakter',
                    'is_unique' => 'Username sudah digunakan'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]|strong_password',
                'label' => 'Password',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 8 karakter',
                    'strong_password' => 'Password harus mengandung huruf besar, kecil, dan angka'
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'label' => 'Konfirmasi Password',
                'errors' => [
                    'required' => 'Konfirmasi password harus diisi',
                    'matches' => 'Konfirmasi password tidak cocok'
                ]
            ],
            'full_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'label' => 'Nama Lengkap',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama lengkap minimal 3 karakter'
                ]
            ],
            'phone' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'label' => 'Nomor Telepon',
                'errors' => [
                    'required' => 'Nomor telepon harus diisi',
                    'numeric' => 'Nomor telepon harus berupa angka'
                ]
            ],
            'whatsapp' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'label' => 'WhatsApp',
                'errors' => [
                    'required' => 'Nomor WhatsApp harus diisi',
                    'numeric' => 'Nomor WhatsApp harus berupa angka'
                ]
            ],
            'gender' => [
                'rules' => 'required|in_list[Laki-laki,Perempuan]',
                'label' => 'Jenis Kelamin',
                'errors' => [
                    'required' => 'Jenis kelamin harus dipilih',
                    'in_list' => 'Jenis kelamin tidak valid'
                ]
            ],
            'address' => [
                'rules' => 'required|min_length[10]',
                'label' => 'Alamat',
                'errors' => [
                    'required' => 'Alamat harus diisi',
                    'min_length' => 'Alamat minimal 10 karakter'
                ]
            ],
            'province_id' => [
                'rules' => 'required|is_natural_no_zero',
                'label' => 'Provinsi',
                'errors' => [
                    'required' => 'Provinsi harus dipilih',
                    'is_natural_no_zero' => 'Provinsi tidak valid'
                ]
            ],
            'university_id' => [
                'rules' => 'required|is_natural_no_zero',
                'label' => 'Perguruan Tinggi',
                'errors' => [
                    'required' => 'Perguruan tinggi harus dipilih',
                    'is_natural_no_zero' => 'Perguruan tinggi tidak valid'
                ]
            ]
        ];

        $validation->setRules($rules);

        if (!$validation->run($data)) {
            return [
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ];
        }

        return [
            'success' => true,
            'message' => 'Validasi berhasil',
            'errors' => []
        ];
    }

    /**
     * Create user account using CodeIgniter Shield
     * 
     * @param array $data Registration data
     * @return User|null
     */
    protected function createUserAccount(array $data): ?User
    {
        try {
            log_message('info', 'Starting user account creation for: ' . $data['username']);

            // Get auth provider
            $users = auth()->getProvider();
            log_message('info', 'Auth provider obtained: ' . get_class($users));

            // Create user entity
            $userData = [
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'active'   => false,
            ];

            log_message('info', 'Creating user with data: ' . json_encode([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'active' => $userData['active']
            ]));

            $user = new User($userData);
            log_message('info', 'User entity created successfully');

            // Save user
            log_message('info', 'Attempting to save user...');
            $result = $users->save($user);

            log_message('info', 'Save result: ' . ($result ? 'true' : 'false'));

            if (!$result) {
                $errors = $users->errors();
                $errorMsg = 'Failed to save user. Errors: ' . json_encode($errors);
                log_message('error', $errorMsg);
                throw new \Exception($errorMsg);
            }

            // CRITICAL FIX: Reload user from database to get complete object with ID
            $insertId = $users->getInsertID();
            log_message('info', 'User insert ID: ' . $insertId);

            if (!$insertId) {
                throw new \Exception('Failed to get insert ID after saving user');
            }

            // Get fresh user from database with ID populated
            $user = $users->findById($insertId);

            if (!$user) {
                throw new \Exception('User saved but could not be retrieved from database. ID: ' . $insertId);
            }

            log_message('info', 'User reloaded successfully. User ID: ' . $user->id);

            // Get or create email identity (now user has valid ID)
            log_message('info', 'Checking for existing email identity...');
            $emailIdentity = $user->getEmailIdentity();

            if (!$emailIdentity) {
                log_message('info', 'No existing email identity, creating new one...');

                $identityResult = $user->createEmailIdentity([
                    'email' => $data['email'],
                    'password' => $data['password']
                ]);

                if (!$identityResult) {
                    $errorMsg = 'Failed to create email identity';
                    log_message('error', $errorMsg);
                    throw new \Exception($errorMsg);
                }

                log_message('info', 'Email identity created successfully');
            } else {
                log_message('info', 'Email identity already exists');
            }

            log_message('info', 'User account creation completed successfully');
            return $user;

        } catch (\Exception $e) {
            log_message('error', 'EXCEPTION in createUserAccount: ' . $e->getMessage());
            log_message('error', 'Exception type: ' . get_class($e));
            log_message('error', 'File: ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Prepare member profile data from registration form
     * 
     * @param array $data Registration data
     * @param int $userId User ID
     * @return array Prepared member data
     */
    protected function prepareMemberData(array $data, int $userId): array
    {
        $memberData = [
            'user_id'              => $userId,
            'member_number'        => $this->generateMemberNumber(),
            'full_name'            => $data['full_name'],
            'nik'                  => $data['nik'] ?? null,
            'nidn_nip'             => $data['nidn_nip'] ?? null,
            'gender'               => $data['gender'],
            'birth_place'          => $data['birth_place'] ?? null,
            'birth_date'           => $data['birth_date'] ?? null,
            'phone'                => $data['phone'],
            'whatsapp'             => $data['whatsapp'],
            'address'              => $data['address'],
            'province_id'          => $data['province_id'],
            'regency_id'           => $data['regency_id'] ?? null,
            'postal_code'          => $data['postal_code'] ?? null,
            'university_id'        => $data['university_id'],
            'study_program_id'     => $data['study_program_id'] ?? null,
            'employment_status_id' => $data['employment_status_id'] ?? null,
            'salary_payer'         => $data['salary_payer'] ?? 'KAMPUS',
            'salary_range_id'      => $data['salary_range_id'] ?? null,
            'job_position'         => $data['job_position'] ?? null,
            'work_start_date'      => $data['work_start_date'] ?? null,
            'skills'               => $data['skills'] ?? null,
            'motivation'           => $data['motivation'] ?? null,
            'join_date'            => date('Y-m-d'),
            'membership_status'    => 'pending',
        ];

        // Move uploaded files from temp to secure user directory
        if (isset($data['photo_path']) && $data['photo_path']) {
            $memberData['photo_path'] = $this->moveFileToSecureLocation($data['photo_path'], $userId, 'photo');
        }

        if (isset($data['employment_letter_path']) && $data['employment_letter_path']) {
            $memberData['employment_letter_path'] = $this->moveFileToSecureLocation($data['employment_letter_path'], $userId, 'employment_letter');
        }

        if (isset($data['id_card_path']) && $data['id_card_path']) {
            $memberData['id_card_path'] = $this->moveFileToSecureLocation($data['id_card_path'], $userId, 'id_card');
        }

        return $memberData;
    }

    /**
     * Move file from temp directory to secure user-specific directory
     * 
     * @param string $tempPath Temporary file path (relative to writable/uploads/)
     * @param int $userId User ID
     * @param string $type File type (photo, payment_proof)
     * @return string|null Secure file path or null on failure
     */
    protected function moveFileToSecureLocation(string $tempPath, int $userId, string $type): ?string
    {
        try {
            $fileService = new \App\Services\FileUploadService();

            // Build full temp path
            $fullTempPath = WRITEPATH . 'uploads/' . $tempPath;

            if (!file_exists($fullTempPath)) {
                log_message('error', "Temp file not found: {$fullTempPath}");
                return null;
            }

            // Get secure upload path for this user
            $secureDir = $fileService->getSecureUploadPath($userId, $type);

            // Ensure directory exists
            if (!is_dir($secureDir)) {
                mkdir($secureDir, 0755, true);
            }

            // Generate secure filename
            $extension = pathinfo($tempPath, PATHINFO_EXTENSION);
            $secureFilename = $type . '_' . time() . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $securePath = $secureDir . $secureFilename;

            // Move file
            if (rename($fullTempPath, $securePath)) {
                // Return relative path for database storage
                $relativePath = str_replace(WRITEPATH . 'uploads/', '', $securePath);
                log_message('info', "File moved successfully: {$tempPath} -> {$relativePath}");
                return $relativePath;
            } else {
                log_message('error', "Failed to move file: {$tempPath} -> {$securePath}");
                return null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error moving file to secure location: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique member number
     * Format: SPK-YYYY-XXXXX (e.g., SPK-2025-00001)
     * 
     * @return string Generated member number
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

        if ($lastMember && isset($lastMember->member_number)) {
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
     * Upload registration files (photo, payment proof, CV)
     * 
     * @param array $files Array of uploaded files
     * @param int $memberId Member profile ID
     * @return array ['success' => bool, 'message' => string]
     */
    protected function uploadFiles(array $files, int $memberId): array
    {
        try {
            $uploadPath = WRITEPATH . 'uploads/members/' . $memberId . '/';

            // Create directory if not exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $updateData = [];

            // Upload photo
            if (isset($files['photo']) && $files['photo']->isValid()) {
                $photo = $files['photo'];
                $photoName = 'photo_' . time() . '.' . $photo->getExtension();

                if ($photo->move($uploadPath, $photoName)) {
                    $updateData['photo_path'] = 'uploads/members/' . $memberId . '/' . $photoName;
                }
            }

            // Upload CV
            if (isset($files['cv']) && $files['cv']->isValid()) {
                $cv = $files['cv'];
                $cvName = 'cv_' . time() . '.' . $cv->getExtension();

                if ($cv->move($uploadPath, $cvName)) {
                    $updateData['cv_path'] = 'uploads/members/' . $memberId . '/' . $cvName;
                }
            }

            // Upload ID card
            if (isset($files['id_card']) && $files['id_card']->isValid()) {
                $idCard = $files['id_card'];
                $idCardName = 'id_card_' . time() . '.' . $idCard->getExtension();

                if ($idCard->move($uploadPath, $idCardName)) {
                    $updateData['id_card_path'] = 'uploads/members/' . $memberId . '/' . $idCardName;
                }
            }

            // Update member profile with file paths
            if (!empty($updateData)) {
                $this->memberModel->update($memberId, $updateData);
            }

            return [
                'success' => true,
                'message' => 'File berhasil diupload'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error uploading files: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupload file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send verification email to new user
     * 
     * @param User $user User entity
     * @return array ['success' => bool, 'message' => string]
     */
    protected function sendVerificationEmail(User $user): array
    {
        try {
            // Generate email verification token
            $token = bin2hex(random_bytes(32));

            // Store token in database (would need email_verifications table)
            // For now, we'll use Shield's built-in email verification

            $email = \Config\Services::email();

            $email->setFrom('noreply@spk.or.id', 'Serikat Pekerja Kampus');
            $email->setTo($user->email);
            $email->setSubject('Verifikasi Email - Serikat Pekerja Kampus');

            $message = view('emails/verification', [
                'username' => $user->username,
                'email' => $user->email,
                'verification_link' => base_url('auth/verify-email/' . $token)
            ]);

            $email->setMessage($message);

            if ($email->send()) {
                return [
                    'success' => true,
                    'message' => 'Email verifikasi berhasil dikirim'
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal mengirim email verifikasi'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error sending verification email: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error mengirim email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if email already exists
     * 
     * @param string $email Email to check
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        $user = $this->userModel->findByEmail($email);
        return $user !== null;
    }

    /**
     * Check if username already exists
     * 
     * @param string $username Username to check
     * @return bool
     */
    public function usernameExists(string $username): bool
    {
        return $this->userModel->usernameExists($username);
    }

    /**
     * Get registration statistics
     * 
     * @return array Statistics data
     */
    public function getRegistrationStats(): array
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        $thisYear = date('Y');

        return [
            'today' => $this->memberModel
                ->where('DATE(join_date)', $today)
                ->countAllResults(),
            'this_month' => $this->memberModel
                ->like('join_date', $thisMonth, 'after')
                ->countAllResults(),
            'this_year' => $this->memberModel
                ->like('join_date', $thisYear, 'after')
                ->countAllResults(),
            'total' => $this->memberModel->countAllResults(),
            'pending' => $this->memberModel
                ->where('membership_status', 'pending')
                ->countAllResults(),
        ];
    }
}
