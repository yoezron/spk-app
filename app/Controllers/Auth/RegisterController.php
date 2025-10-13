<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\Member\RegisterMemberService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * RegisterController
 * 
 * Menangani proses pendaftaran anggota baru
 * Termasuk form registrasi, validasi, file upload, dan integrasi dengan RegisterMemberService
 * 
 * @package App\Controllers\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegisterController extends BaseController
{
    /**
     * @var RegisterMemberService
     */
    protected $registerService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->registerService = new RegisterMemberService();
    }

    /**
     * Display registration form
     * Load all master data for dropdowns
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // Check if registration is allowed
        if (!config('Auth')->allowRegistration) {
            return redirect()->to('/')
                ->with('error', 'Pendaftaran saat ini ditutup.');
        }

        // Check if user is already logged in
        if (auth()->loggedIn()) {
            return redirect()->to('/member/dashboard');
        }

        // Load master data for form dropdowns
        $data = [
            'title' => 'Pendaftaran Anggota - Serikat Pekerja Kampus',
            'pageTitle' => 'Daftar Sebagai Anggota Baru',

            // Master Data
            'provinsi' => model('ProvinceModel')->orderBy('name', 'ASC')->findAll(),
            'jenis_pt' => model('JenisPtModel')->orderBy('name', 'ASC')->findAll(),
            'status_kepegawaian' => model('StatusKepegawaianModel')->orderBy('name', 'ASC')->findAll(),
            'pemberi_gaji' => model('PemberiGajiModel')->orderBy('name', 'ASC')->findAll(),
            'range_gaji' => model('RangeGajiModel')->orderBy('min_salary', 'ASC')->findAll(),
        ];

        return view('auth/register', $data);
    }

    /**
     * Handle registration form submission
     * Process file uploads and call RegisterMemberService
     * 
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        // Check if registration is allowed
        if (!config('Auth')->allowRegistration) {
            return redirect()->to('/')
                ->with('error', 'Pendaftaran saat ini ditutup.');
        }

        // Validation rules
        $validationRules = [
            // Personal Info
            'full_name' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[3]|max_length[150]',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama lengkap minimal 3 karakter',
                    'max_length' => 'Nama lengkap maksimal 150 karakter'
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[auth_identities.secret]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|strong_password',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 8 karakter',
                    'strong_password' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol'
                ]
            ],
            'password_confirm' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password harus diisi',
                    'matches' => 'Konfirmasi password tidak sesuai'
                ]
            ],
            'jenis_kelamin' => [
                'label' => 'Jenis Kelamin',
                'rules' => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin harus dipilih',
                    'in_list' => 'Jenis kelamin tidak valid'
                ]
            ],
            'no_wa' => [
                'label' => 'Nomor WhatsApp',
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor WhatsApp harus diisi',
                    'numeric' => 'Nomor WhatsApp hanya boleh berisi angka',
                    'min_length' => 'Nomor WhatsApp minimal 10 digit',
                    'max_length' => 'Nomor WhatsApp maksimal 15 digit'
                ]
            ],
            'alamat' => [
                'label' => 'Alamat',
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Alamat harus diisi',
                    'min_length' => 'Alamat minimal 10 karakter'
                ]
            ],

            // Location
            'wilayah_id' => [
                'label' => 'Provinsi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Provinsi harus dipilih',
                    'is_natural_no_zero' => 'Provinsi tidak valid'
                ]
            ],
            'kabupaten' => [
                'label' => 'Kabupaten/Kota',
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Kabupaten/Kota harus diisi'
                ]
            ],
            'kecamatan' => [
                'label' => 'Kecamatan',
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Kecamatan harus diisi'
                ]
            ],

            // Employment
            'status_kepegawaian_id' => [
                'label' => 'Status Kepegawaian',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Status kepegawaian harus dipilih'
                ]
            ],
            'pemberi_gaji_id' => [
                'label' => 'Pemberi Gaji',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Pemberi gaji harus dipilih'
                ]
            ],
            'range_gaji_id' => [
                'label' => 'Range Gaji',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Range gaji harus dipilih'
                ]
            ],
            'gaji_pokok' => [
                'label' => 'Gaji Pokok',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Gaji pokok harus diisi',
                    'numeric' => 'Gaji pokok harus berupa angka',
                    'greater_than' => 'Gaji pokok harus lebih dari 0'
                ]
            ],

            // University
            'jenis_pt_id' => [
                'label' => 'Jenis Perguruan Tinggi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Jenis PT harus dipilih'
                ]
            ],
            'kampus_id' => [
                'label' => 'Kampus',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Kampus harus dipilih'
                ]
            ],
            'prodi_id' => [
                'label' => 'Program Studi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Program studi harus dipilih'
                ]
            ],

            // Files
            'foto' => [
                'label' => 'Foto',
                'rules' => 'uploaded[foto]|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Foto harus diupload',
                    'max_size' => 'Ukuran foto maksimal 2MB',
                    'is_image' => 'File harus berupa gambar',
                    'mime_in' => 'Format foto harus JPG, JPEG, atau PNG'
                ]
            ],
            'bukti_bayar_pertama' => [
                'label' => 'Bukti Pembayaran',
                'rules' => 'uploaded[bukti_bayar_pertama]|max_size[bukti_bayar_pertama,2048]|is_image[bukti_bayar_pertama]',
                'errors' => [
                    'uploaded' => 'Bukti pembayaran harus diupload',
                    'max_size' => 'Ukuran bukti pembayaran maksimal 2MB',
                    'is_image' => 'File harus berupa gambar'
                ]
            ],

            // Agreement
            'agree_terms' => [
                'label' => 'Persetujuan',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Anda harus menyetujui syarat dan ketentuan'
                ]
            ]
        ];

        // Validate input
        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Handle file uploads
        $foto = $this->request->getFile('foto');
        $buktiBayar = $this->request->getFile('bukti_bayar_pertama');

        $uploadedFiles = [];

        try {
            // Upload foto
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $fotoName = $foto->getRandomName();
                $foto->move(WRITEPATH . 'uploads/photos', $fotoName);
                $uploadedFiles['foto_path'] = 'photos/' . $fotoName;
            }

            // Upload bukti bayar
            if ($buktiBayar && $buktiBayar->isValid() && !$buktiBayar->hasMoved()) {
                $buktiName = $buktiBayar->getRandomName();
                $buktiBayar->move(WRITEPATH . 'uploads/bukti_bayar', $buktiName);
                $uploadedFiles['bukti_bayar_path'] = 'bukti_bayar/' . $buktiName;
            }

            // Prepare data for registration
            $registrationData = array_merge(
                $this->request->getPost(),
                $uploadedFiles
            );

            // Call RegisterMemberService
            $result = $this->registerService->register($registrationData);

            if ($result['success']) {
                return redirect()->to('/verify-email')
                    ->with('success', $result['message']);
            } else {
                // Delete uploaded files if registration failed
                $this->cleanupUploadedFiles($uploadedFiles);

                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            // Delete uploaded files on error
            $this->cleanupUploadedFiles($uploadedFiles);

            log_message('error', 'Registration error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        }
    }

    /**
     * Clean up uploaded files on error
     * 
     * @param array $files Array of uploaded file paths
     * @return void
     */
    protected function cleanupUploadedFiles(array $files): void
    {
        foreach ($files as $filePath) {
            $fullPath = WRITEPATH . 'uploads/' . $filePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * AJAX endpoint to get kabupaten/kota by province ID
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getKabupaten()
    {
        $provinceId = $this->request->getGet('province_id');

        if (!$provinceId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Province ID required'
            ]);
        }

        $regionModel = model('RegionModel');
        $regions = $regionModel->where('province_id', $provinceId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $regions
        ]);
    }

    /**
     * AJAX endpoint to get kampus by province ID
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getKampus()
    {
        $provinceId = $this->request->getGet('province_id');

        if (!$provinceId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Province ID required'
            ]);
        }

        $kampusModel = model('UniversityModel');
        $kampus = $kampusModel->where('province_id', $provinceId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $kampus
        ]);
    }

    /**
     * AJAX endpoint to get prodi by kampus ID
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getProdi()
    {
        $kampusId = $this->request->getGet('kampus_id');

        if (!$kampusId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kampus ID required'
            ]);
        }

        $prodiModel = model('ProdiModel');
        $prodi = $prodiModel->where('kampus_id', $kampusId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $prodi
        ]);
    }
}
