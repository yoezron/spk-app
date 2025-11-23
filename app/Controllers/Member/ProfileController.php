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
                                           prodi.name as prodi_name,
                                           status_kepegawaian.name as status_kepegawaian_name,
                                           pemberi_gaji.name as pemberi_gaji_name,
                                           range_gaji.name as range_gaji_name,
                                           jenis_pt.name as jenis_pt_name')
                ->join('provinces', 'provinces.id = member_profiles.wilayah_id', 'left')
                ->join('regions', 'regions.id = member_profiles.region_id', 'left')
                ->join('universities', 'universities.id = member_profiles.kampus_id', 'left')
                ->join('prodi', 'prodi.id = member_profiles.prodi_id', 'left')
                ->join('status_kepegawaian', 'status_kepegawaian.id = member_profiles.status_kepegawaian_id', 'left')
                ->join('pemberi_gaji', 'pemberi_gaji.id = member_profiles.pemberi_gaji_id', 'left')
                ->join('range_gaji', 'range_gaji.id = member_profiles.range_gaji_id', 'left')
                ->join('jenis_pt', 'jenis_pt.id = member_profiles.jenis_pt_id', 'left')
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

            // Load master data for dropdowns
            $data = [
                'title' => 'Edit Profil - Serikat Pekerja Kampus',
                'pageTitle' => 'Edit Profil',
                'user' => $user,
                'member' => $member,

                // Master data
                'provinsi' => $this->loadMasterData('ProvinceModel', 'name'),
                'jenis_pt' => $this->loadMasterData('JenisPtModel', 'name'),
                'status_kepegawaian' => $this->loadMasterData('StatusKepegawaianModel', 'name'),
                'pemberi_gaji' => $this->loadMasterData('PemberiGajiModel', 'name'),
                'range_gaji' => $this->loadMasterData('RangeGajiModel', 'min_salary')
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
                'rules' => 'required|min_length[3]|max_length[150]'
            ],
            'jenis_kelamin' => [
                'label' => 'Jenis Kelamin',
                'rules' => 'required|in_list[L,P]'
            ],
            'no_wa' => [
                'label' => 'Nomor WhatsApp',
                'rules' => 'required|numeric|min_length[10]|max_length[15]'
            ],
            'alamat' => [
                'label' => 'Alamat',
                'rules' => 'required|min_length[10]'
            ],
            'wilayah_id' => [
                'label' => 'Provinsi',
                'rules' => 'required|is_natural_no_zero'
            ],
            'kabupaten' => [
                'label' => 'Kabupaten/Kota',
                'rules' => 'required|min_length[3]'
            ],
            'kecamatan' => [
                'label' => 'Kecamatan',
                'rules' => 'required|min_length[3]'
            ],
            'status_kepegawaian_id' => [
                'label' => 'Status Kepegawaian',
                'rules' => 'required|is_natural_no_zero'
            ],
            'pemberi_gaji_id' => [
                'label' => 'Pemberi Gaji',
                'rules' => 'required|is_natural_no_zero'
            ],
            'range_gaji_id' => [
                'label' => 'Range Gaji',
                'rules' => 'required|is_natural_no_zero'
            ],
            'gaji_pokok' => [
                'label' => 'Gaji Pokok',
                'rules' => 'required|numeric|greater_than[0]'
            ],
            'jenis_pt_id' => [
                'label' => 'Jenis PT',
                'rules' => 'required|is_natural_no_zero'
            ],
            'kampus_id' => [
                'label' => 'Kampus',
                'rules' => 'required|is_natural_no_zero'
            ],
            'prodi_id' => [
                'label' => 'Program Studi',
                'rules' => 'required|is_natural_no_zero'
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
                'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
                'no_wa' => $this->request->getPost('no_wa'),
                'alamat' => $this->request->getPost('alamat'),
                'wilayah_id' => $this->request->getPost('wilayah_id'),
                'kabupaten' => $this->request->getPost('kabupaten'),
                'kecamatan' => $this->request->getPost('kecamatan'),
                'desa' => $this->request->getPost('desa'),
                'nidn_nip' => $this->request->getPost('nidn_nip'),
                'status_kepegawaian_id' => $this->request->getPost('status_kepegawaian_id'),
                'pemberi_gaji_id' => $this->request->getPost('pemberi_gaji_id'),
                'range_gaji_id' => $this->request->getPost('range_gaji_id'),
                'gaji_pokok' => $this->request->getPost('gaji_pokok'),
                'jenis_pt_id' => $this->request->getPost('jenis_pt_id'),
                'kampus_id' => $this->request->getPost('kampus_id'),
                'prodi_id' => $this->request->getPost('prodi_id'),
                'expertise' => $this->request->getPost('expertise'),
                'motivasi' => $this->request->getPost('motivasi'),
                'social_media' => $this->request->getPost('social_media'),
                'updated_at' => date('Y-m-d H:i:s')
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
