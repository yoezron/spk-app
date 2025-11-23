<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Services\FileUploadService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * ProfileController (Member Area)
 * 
 * Menangani manajemen profil anggota
 * View, edit profile, update foto, change password, settings
 * 
 * @package App\Controllers\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ProfileController extends BaseController
{
    /**
     * @var FileUploadService
     */
    protected $fileUploadService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Display profile detail
     * Shows complete member information
     *
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
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
                                           regions.name as region_name,
                                           universities.name as university_name,
                                           study_programs.name as study_program_name,
                                           employment_statuses.name as employment_status_name,
                                           salary_ranges.name as salary_range_name')
                ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
                ->join('regions', 'regions.id = member_profiles.region_id', 'left')
                ->join('universities', 'universities.id = member_profiles.university_id', 'left')
                ->join('study_programs', 'study_programs.id = member_profiles.study_program_id', 'left')
                ->join('employment_statuses', 'employment_statuses.id = member_profiles.employment_status_id', 'left')
                ->join('salary_ranges', 'salary_ranges.id = member_profiles.salary_range_id', 'left')
                ->where('member_profiles.user_id', $user->id)
                ->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            $data = [
                'title' => 'Profil Saya - Serikat Pekerja Kampus',
                'pageTitle' => 'Profil Saya',
                'user' => $user,
                'member' => $member
            ];

            return view('member/profile/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading profile: ' . $e->getMessage());

            return redirect()->to('/member/dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat profil.');
        }
    }

    /**
     * Display edit profile form
     *
     * @return string|RedirectResponse
     */
    public function edit(): string|RedirectResponse
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

            // Set default values for fields that might not exist in database
            if (!isset($member->religion)) {
                $member->religion = null;
            }
            if (!isset($member->marital_status)) {
                $member->marital_status = null;
            }

            // Load master data for dropdowns
            $data = [
                'title' => 'Edit Profil - Serikat Pekerja Kampus',
                'pageTitle' => 'Edit Profil',
                'user' => $user,
                'member' => $member,

                // Master data
                'provinces' => $this->loadMasterData('ProvinceModel', 'name'),
                'employment_statuses' => $this->loadMasterData('EmploymentStatusModel', 'name'),
                'salary_ranges' => $this->loadMasterData('SalaryRangeModel', 'min_amount'),

                // Static data for salary payer (no database table)
                'salary_payers' => [
                    'KAMPUS' => 'Kampus/Perguruan Tinggi',
                    'PEMERINTAH' => 'Pemerintah',
                    'YAYASAN' => 'Yayasan',
                    'LAINNYA' => 'Lainnya'
                ]
            ];

            return view('member/profile/edit', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading edit profile: ' . $e->getMessage());

            return redirect()->to('/member/profile')
                ->with('error', 'Terjadi kesalahan saat memuat form.');
        }
    }

    /**
     * Safely load master data for dropdowns.
     *
     * @param string $modelName
     * @param string|null $orderBy
     * @param string $direction
     *
     * @return array
     */
    protected function loadMasterData(string $modelName, ?string $orderBy = null, string $direction = 'ASC'): array
    {
        try {
            $model = model($modelName);

            if (!is_object($model)) {
                log_message('error', 'Model not found when loading profile master data: ' . $modelName);
                return [];
            }

            if ($orderBy) {
                return $model->orderBy($orderBy, $direction)->findAll();
            }

            return $model->findAll();
        } catch (\Throwable $e) {
            log_message('error', sprintf('Failed loading profile master data via %s: %s', $modelName, $e->getMessage()));
            return [];
        }
    }

    /**
     * Update profile data
     * 
     * @return RedirectResponse
     */
    public function update(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        // Validation rules
        $validationRules = [
            'full_name' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[3]|max_length[255]'
            ],
            'gender' => [
                'label' => 'Jenis Kelamin',
                'rules' => 'required|in_list[Laki-laki,Perempuan]'
            ],
            'phone' => [
                'label' => 'Nomor Telepon',
                'rules' => 'permit_empty|max_length[20]'
            ],
            'whatsapp' => [
                'label' => 'Nomor WhatsApp',
                'rules' => 'permit_empty|max_length[20]'
            ],
            'address' => [
                'label' => 'Alamat',
                'rules' => 'permit_empty|max_length[500]'
            ],
            'province_id' => [
                'label' => 'Provinsi',
                'rules' => 'permit_empty|is_natural_no_zero'
            ],
            'regency_id' => [
                'label' => 'Kabupaten/Kota',
                'rules' => 'permit_empty|is_natural_no_zero'
            ],
            'employment_status_id' => [
                'label' => 'Status Kepegawaian',
                'rules' => 'permit_empty|is_natural_no_zero'
            ],
            'salary_payer' => [
                'label' => 'Pemberi Gaji',
                'rules' => 'permit_empty|in_list[KAMPUS,PEMERINTAH,YAYASAN,LAINNYA]'
            ],
            'salary_range_id' => [
                'label' => 'Range Gaji',
                'rules' => 'permit_empty|is_natural_no_zero'
            ],
            'university_id' => [
                'label' => 'Universitas',
                'rules' => 'permit_empty|is_natural_no_zero'
            ],
            'study_program_id' => [
                'label' => 'Program Studi',
                'rules' => 'permit_empty|is_natural_no_zero'
            ]
        ];

        // Validate input
        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $memberModel = model('MemberProfileModel');

            // Get member profile
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            // Prepare update data
            $updateData = [
                'full_name' => $this->request->getPost('full_name'),
                'gender' => $this->request->getPost('gender'),
                'phone' => $this->request->getPost('phone'),
                'whatsapp' => $this->request->getPost('whatsapp'),
                'address' => $this->request->getPost('address'),
                'province_id' => $this->request->getPost('province_id'),
                'regency_id' => $this->request->getPost('regency_id'),
                'postal_code' => $this->request->getPost('postal_code'),
                'nidn_nip' => $this->request->getPost('nidn_nip'),
                'employment_status_id' => $this->request->getPost('employment_status_id'),
                'salary_payer' => $this->request->getPost('salary_payer'),
                'salary_range_id' => $this->request->getPost('salary_range_id'),
                'job_position' => $this->request->getPost('job_position'),
                'university_id' => $this->request->getPost('university_id'),
                'study_program_id' => $this->request->getPost('study_program_id'),
                'skills' => $this->request->getPost('skills'),
                'motivation' => $this->request->getPost('motivation')
            ];

            // Update profile
            $memberModel->update($member->id, $updateData);

            return redirect()->to('/member/profile')
                ->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating profile: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui profil.');
        }
    }

    /**
     * Update profile photo
     * 
     * @return RedirectResponse
     */
    public function updatePhoto(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        // Validation rules for photo
        $validationRules = [
            'foto' => [
                'label' => 'Foto',
                'rules' => 'uploaded[foto]|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->to('/member/dashboard')
                    ->with('error', 'Profil tidak ditemukan.');
            }

            // Handle file upload
            $foto = $this->request->getFile('foto');

            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                // Delete old photo if exists
                if ($member->foto_path) {
                    $oldPhotoPath = WRITEPATH . 'uploads/' . $member->foto_path;
                    if (file_exists($oldPhotoPath)) {
                        @unlink($oldPhotoPath);
                    }
                }

                // Upload new photo
                $uploadResult = $this->fileUploadService->upload($foto, 'photo');

                if (!$uploadResult['success']) {
                    return redirect()->back()
                        ->with('error', $uploadResult['message']);
                }

                // Update member profile
                $memberModel->update($member->id, [
                    'foto_path' => $uploadResult['data']['relative_path'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                return redirect()->to('/member/profile')
                    ->with('success', 'Foto profil berhasil diperbarui.');
            }

            return redirect()->back()
                ->with('error', 'Gagal mengupload foto.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating photo: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui foto.');
        }
    }

    /**
     * Display change password form
     *
     * @return string|RedirectResponse
     */
    public function changePassword(): string|RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Ubah Password - Serikat Pekerja Kampus',
            'pageTitle' => 'Ubah Password'
        ];

        return view('member/profile/change_password', $data);
    }

    /**
     * Update password
     * 
     * @return RedirectResponse
     */
    public function updatePassword(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        // Validation rules
        $validationRules = [
            'current_password' => [
                'label' => 'Password Lama',
                'rules' => 'required'
            ],
            'new_password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[8]|strong_password'
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[new_password]'
            ]
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Verify current password
            if (!auth()->check(['password' => $currentPassword])) {
                return redirect()->back()
                    ->with('error', 'Password lama tidak sesuai.');
            }

            // Update password using Shield
            $user->password = $newPassword;
            $userModel = model('UserModel');
            $userModel->save($user);

            // Log activity
            $auditModel = model('AuditLogModel');
            $auditModel->insert([
                'user_id' => $user->id,
                'action' => 'password_changed',
                'description' => 'User changed their password',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to('/member/profile')
                ->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            log_message('error', 'Error changing password: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengubah password.');
        }
    }

    /**
     * Display account settings
     *
     * @return string|RedirectResponse
     */
    public function settings(): string|RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get user settings (if you have a settings table)
            // For now, use basic user preferences

            $data = [
                'title' => 'Pengaturan Akun - Serikat Pekerja Kampus',
                'pageTitle' => 'Pengaturan Akun',
                'user' => $user
            ];

            return view('member/profile/settings', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading settings: ' . $e->getMessage());

            return redirect()->to('/member/profile')
                ->with('error', 'Terjadi kesalahan saat memuat pengaturan.');
        }
    }

    /**
     * Update account settings
     * 
     * @return RedirectResponse
     */
    public function updateSettings(): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        try {
            // Get settings from POST
            $emailNotifications = $this->request->getPost('email_notifications') ? 1 : 0;
            $whatsappNotifications = $this->request->getPost('whatsapp_notifications') ? 1 : 0;
            $publicProfile = $this->request->getPost('public_profile') ? 1 : 0;

            // Update settings in member profile
            $memberModel = model('MemberProfileModel');
            $member = $memberModel->where('user_id', $user->id)->first();

            if ($member) {
                $memberModel->update($member->id, [
                    'email_notifications' => $emailNotifications,
                    'whatsapp_notifications' => $whatsappNotifications,
                    'public_profile' => $publicProfile,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return redirect()->to('/member/profile/settings')
                ->with('success', 'Pengaturan berhasil diperbarui.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating settings: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui pengaturan.');
        }
    }
}
