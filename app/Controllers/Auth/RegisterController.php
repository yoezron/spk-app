<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\Member\RegisterMemberService;
use App\Models\ProvinceModel;
use App\Models\EmploymentStatusModel;
use App\Models\SalaryRangeModel;
use App\Models\UniversityTypeModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Register Controller
 * 
 * Handles new member registration
 * - Display registration form with master data
 * - Process registration with file uploads
 * - Send verification email
 * 
 * @package App\Controllers\Auth
 */
class RegisterController extends BaseController
{
    protected $registerService;
    protected $provinceModel;
    protected $employmentStatusModel;
    protected $salaryRangeModel;
    protected $universityTypeModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        helper(['form', 'url']);

        // Initialize services and models
        $this->registerService = new RegisterMemberService();
        $this->provinceModel = new ProvinceModel();
        $this->employmentStatusModel = new EmploymentStatusModel();
        $this->salaryRangeModel = new SalaryRangeModel();
        $this->universityTypeModel = new UniversityTypeModel();
    }

    /**
     * Display registration form
     * 
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
    {
        // Redirect if already logged in
        if (auth()->loggedIn()) {
            return redirect()->to('/member/dashboard')->with('info', 'Anda sudah login.');
        }

        try {
            // Load master data for dropdowns
            $data = [
                'title' => 'Daftar Anggota Baru - SPK',
                'provinces' => $this->provinceModel
                    ->orderBy('name', 'ASC')
                    ->findAll(),
                'employmentStatuses' => $this->employmentStatusModel
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll(),
                'salaryRanges' => $this->salaryRangeModel
                    ->where('is_active', 1)
                    ->orderBy('min_salary', 'ASC')
                    ->findAll(),
                'universityTypes' => $this->universityTypeModel
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll(),
            ];

            // Add payer data if model exists
            if (class_exists('\App\Models\PayerModel')) {
                $payerModel = new \App\Models\PayerModel();
                $data['payers'] = $payerModel
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
            }

            return view('auth/register', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading registration form: ' . $e->getMessage());

            return view('auth/register', [
                'title' => 'Daftar Anggota Baru - SPK',
                'error' => 'Terjadi kesalahan saat memuat halaman. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Process registration
     * 
     * @return RedirectResponse
     */
    public function register()
    {
        // CSRF protection already handled by CSRF Filter in Config/Filters.php

        try {
            // Get form data
            $data = $this->request->getPost();

            // 1. Validate registration data (including file validation)
            $validationResult = $this->validateRegistration($data);

            if (!$validationResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $validationResult['errors']);
            }

            // 2. Handle file uploads (validation already passed)
            $uploadResult = $this->handleFileUploads();

            if (!$uploadResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $uploadResult['errors']);
            }

            // 3. Add file paths to data
            $data['photo_path'] = $uploadResult['photo_path'];
            $data['payment_proof_path'] = $uploadResult['payment_proof_path'];

            // 4. Process registration through service
            $result = $this->registerService->register($data);

            if ($result['success']) {
                // Clear form input
                session()->remove('_ci_old_input');

                return redirect()->to('/auth/verify-email')
                    ->with('success', $result['message'])
                    ->with('email', $data['email']);
            } else {
                // Delete uploaded files if registration fails
                $this->deleteUploadedFiles($uploadResult);

                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Clean up uploaded files
            if (isset($uploadResult)) {
                $this->deleteUploadedFiles($uploadResult);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi.');
        }
    }

    /**
     * Handle file uploads (photo and payment proof)
     * Validation is already done in validateRegistration()
     * This method only handles the upload logic
     * 
     * @return array
     */
    protected function handleFileUploads(): array
    {
        $errors = [];
        $photoPath = null;
        $paymentProofPath = null;

        try {
            // Handle photo upload
            $photo = $this->request->getFile('photo');
            if ($photo && $photo->isValid() && !$photo->hasMoved()) {
                // Generate temporary secure path
                $tempDir = WRITEPATH . 'uploads/temp/photos/';
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                // Generate unique filename with hash for security
                $photoNewName = 'photo_' . time() . '_' . bin2hex(random_bytes(16)) . '.' . $photo->getExtension();

                if ($photo->move($tempDir, $photoNewName)) {
                    $photoPath = 'temp/photos/' . $photoNewName;
                    log_message('info', 'Photo uploaded successfully: ' . $photoPath);
                } else {
                    $errors[] = 'Gagal mengupload foto';
                    log_message('error', 'Failed to move photo file');
                }
            }

            // Handle payment proof upload
            $paymentProof = $this->request->getFile('payment_proof');
            if ($paymentProof && $paymentProof->isValid() && !$paymentProof->hasMoved()) {
                // Generate temporary secure path
                $tempDir = WRITEPATH . 'uploads/temp/payments/';
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                // Generate unique filename with hash for security
                $proofNewName = 'payment_' . time() . '_' . bin2hex(random_bytes(16)) . '.' . $paymentProof->getExtension();

                if ($paymentProof->move($tempDir, $proofNewName)) {
                    $paymentProofPath = 'temp/payments/' . $proofNewName;
                    log_message('info', 'Payment proof uploaded successfully: ' . $paymentProofPath);
                } else {
                    $errors[] = 'Gagal mengupload bukti pembayaran';
                    log_message('error', 'Failed to move payment proof file');
                }
            }

            return [
                'success' => empty($errors),
                'errors' => $errors,
                'photo_path' => $photoPath,
                'payment_proof_path' => $paymentProofPath
            ];
        } catch (\Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['Terjadi kesalahan saat mengupload file: ' . $e->getMessage()],
                'photo_path' => null,
                'payment_proof_path' => null
            ];
        }
    }

    /**
     * Validate registration data
     * 
     * @param array $data
     * @return array
     */
    protected function validateRegistration(array $data): array
    {
        $rules = [
            // Account Information
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[auth_identities.secret]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[5]|max_length[30]|alpha_numeric|is_unique[users.username]',
                'errors' => [
                    'required' => 'Username harus diisi',
                    'min_length' => 'Username minimal 5 karakter',
                    'max_length' => 'Username maksimal 30 karakter',
                    'alpha_numeric' => 'Username hanya boleh huruf dan angka',
                    'is_unique' => 'Username sudah digunakan'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|max_length[255]|strong_password',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 8 karakter',
                    'max_length' => 'Password maksimal 255 karakter',
                    'strong_password' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus'
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

            // Personal Information
            'full_name' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[3]|max_length[150]',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama lengkap minimal 3 karakter',
                    'max_length' => 'Nama lengkap maksimal 150 karakter'
                ]
            ],
            'gender' => [
                'label' => 'Jenis Kelamin',
                'rules' => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin harus dipilih',
                    'in_list' => 'Jenis kelamin tidak valid'
                ]
            ],

            // Contact Information
            'phone' => [
                'label' => 'No. Telepon',
                'rules' => 'required|valid_phone',
                'errors' => [
                    'required' => 'No. telepon harus diisi',
                    'valid_phone' => 'Format nomor telepon tidak valid (gunakan: 08xxxxxxxxxx)'
                ]
            ],
            'whatsapp' => [
                'label' => 'No. WhatsApp',
                'rules' => 'required|valid_phone',
                'errors' => [
                    'required' => 'No. WhatsApp harus diisi',
                    'valid_phone' => 'Format nomor WhatsApp tidak valid (gunakan: 08xxxxxxxxxx)'
                ]
            ],
            'address' => [
                'label' => 'Alamat',
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Alamat harus diisi',
                    'min_length' => 'Alamat minimal 10 karakter'
                ]
            ],

            // Employment Information
            'employment_status_id' => [
                'label' => 'Status Kepegawaian',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Status kepegawaian harus dipilih',
                    'is_natural_no_zero' => 'Status kepegawaian tidak valid'
                ]
            ],
            'salary_range_id' => [
                'label' => 'Range Gaji',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Range gaji harus dipilih',
                    'is_natural_no_zero' => 'Range gaji tidak valid'
                ]
            ],

            // Institution Information
            'province_id' => [
                'label' => 'Provinsi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Provinsi harus dipilih',
                    'is_natural_no_zero' => 'Provinsi tidak valid'
                ]
            ],
            'university_type_id' => [
                'label' => 'Jenis Perguruan Tinggi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Jenis perguruan tinggi harus dipilih',
                    'is_natural_no_zero' => 'Jenis perguruan tinggi tidak valid'
                ]
            ],
            'university_id' => [
                'label' => 'Perguruan Tinggi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Perguruan tinggi harus dipilih',
                    'is_natural_no_zero' => 'Perguruan tinggi tidak valid'
                ]
            ],
            'study_program_id' => [
                'label' => 'Program Studi',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Program studi harus dipilih',
                    'is_natural_no_zero' => 'Program studi tidak valid'
                ]
            ],

            // Additional Information
            'motivation' => [
                'label' => 'Motivasi',
                'rules' => 'required|min_length[50]|max_length[1000]',
                'errors' => [
                    'required' => 'Motivasi harus diisi',
                    'min_length' => 'Motivasi minimal 50 karakter',
                    'max_length' => 'Motivasi maksimal 1000 karakter'
                ]
            ],

            // File Uploads
            'photo' => [
                'label' => 'Foto',
                'rules' => 'uploaded[photo]|max_file_size[photo,2048]|valid_image_mime[photo,image/jpeg,image/jpg,image/png]|min_image_dimensions[photo,300,400]|max_image_dimensions[photo,4000,4000]',
                'errors' => [
                    'uploaded' => 'Foto harus diupload',
                    'max_file_size' => 'Ukuran foto maksimal 2MB',
                    'valid_image_mime' => 'Format foto harus JPG, JPEG, atau PNG',
                    'min_image_dimensions' => 'Resolusi foto minimal 300x400px',
                    'max_image_dimensions' => 'Resolusi foto maksimal 4000x4000px'
                ]
            ],
            'payment_proof' => [
                'label' => 'Bukti Pembayaran',
                'rules' => 'uploaded[payment_proof]|max_file_size[payment_proof,5120]|ext_in[payment_proof,jpg,jpeg,png,pdf]',
                'errors' => [
                    'uploaded' => 'Bukti pembayaran harus diupload',
                    'max_file_size' => 'Ukuran bukti pembayaran maksimal 5MB',
                    'ext_in' => 'Format bukti pembayaran harus JPG, PNG, atau PDF'
                ]
            ],

            // Terms
            'terms' => [
                'label' => 'Syarat & Ketentuan',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Anda harus menyetujui syarat & ketentuan'
                ]
            ]
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($data)) {
            return [
                'success' => false,
                'errors' => $validation->getErrors()
            ];
        }

        return [
            'success' => true,
            'errors' => []
        ];
    }

    /**
     * Delete uploaded files
     * 
     * @param array $uploadResult
     * @return void
     */
    protected function deleteUploadedFiles(array $uploadResult): void
    {
        try {
            if (!empty($uploadResult['photo_path'])) {
                $photoFullPath = WRITEPATH . 'uploads/' . $uploadResult['photo_path'];
                if (file_exists($photoFullPath)) {
                    unlink($photoFullPath);
                }
            }

            if (!empty($uploadResult['payment_proof_path'])) {
                $paymentFullPath = WRITEPATH . 'uploads/' . $uploadResult['payment_proof_path'];
                if (file_exists($paymentFullPath)) {
                    unlink($paymentFullPath);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting uploaded files: ' . $e->getMessage());
        }
    }

    /**
     * Display verify email page after registration
     */
    public function verifyEmailPage()
    {
        // Check if email is set in session
        if (!session()->has('email')) {
            return redirect()->to('/auth/register');
        }

        return view('auth/verify_email');
    }
}
