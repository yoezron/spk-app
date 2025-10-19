<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\ProvinceModel;
use App\Models\RegencyModel;
use App\Models\UniversityModel;
use App\Models\StudyProgramModel;
use App\Models\MemberProfileModel;
use App\Models\EmploymentStatusModel;
use App\Models\SalaryRangeModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * MasterDataController
 * 
 * Menangani CRUD master data sistem
 * Termasuk provinces, regencies, universities, dan study programs
 * Super Admin dapat mengelola data referensi utama sistem
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MasterDataController extends BaseController
{

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var RegencyModel
     */
    protected $regencyModel;

    /**
     * @var UniversityModel
     */
    protected $universityModel;

    /**
     * @var StudyProgramModel
     */
    protected $studyProgramModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var EmploymentStatusModel
     */
    protected $employmentStatusModel;

    /**
     * @var SalaryRangeModel
     */
    protected $salaryRangeModel;

    protected $provinceImportService;
    protected $regencyImportService;
    protected $universityImportService;
    protected $studyProgramImportService;



    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->provinceModel = new ProvinceModel();
        $this->regencyModel = new RegencyModel();
        $this->universityModel = new UniversityModel();
        $this->studyProgramModel = new StudyProgramModel();
        $this->memberModel = new MemberProfileModel();
        $this->employmentStatusModel = new EmploymentStatusModel();
        $this->salaryRangeModel = new SalaryRangeModel();
        $this->provinceImportService = new \App\Services\ProvinceImportService();
        $this->regencyImportService = new \App\Services\RegencyImportService();
        $this->universityImportService = new \App\Services\UniversityImportService();
        $this->studyProgramImportService = new \App\Services\StudyProgramImportService();
    }

    // ========================================
    // PROVINCES MANAGEMENT
    // ========================================

    /**
     * Display list of provinces
     * 
     * @return string|RedirectResponse
     */
    public function provinces()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all provinces with regency count and member count
        $provinces = $this->provinceModel
            ->select('provinces.*, 
                      COUNT(DISTINCT regencies.id) as regency_count,
                      COUNT(DISTINCT member_profiles.id) as member_count')
            ->join('regencies', 'regencies.province_id = provinces.id', 'left')
            ->join('member_profiles', 'member_profiles.province_id = provinces.id', 'left')
            ->groupBy('provinces.id')
            ->orderBy('provinces.name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Master Data - Provinsi',
            'provinces' => $provinces,
            'total' => count($provinces),
            'validation' => \Config\Services::validation()
        ];

        return view('super/master/provinces', $data);
    }

    /**
     * Store new province
     * 
     * @return RedirectResponse
     */
    public function storeProvince(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]|is_unique[provinces.name]',
                'errors' => [
                    'required' => 'Nama provinsi wajib diisi',
                    'min_length' => 'Nama provinsi minimal 3 karakter',
                    'is_unique' => 'Nama provinsi sudah ada'
                ]
            ],
            'code' => [
                'rules' => 'permit_empty|max_length[10]|is_unique[provinces.code]',
                'errors' => [
                    'is_unique' => 'Kode provinsi sudah digunakan'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code') ?: null
            ];

            $this->provinceModel->insert($data);

            return redirect()->to('/super/master/provinces')
                ->with('success', 'Provinsi berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating province: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan provinsi.');
        }
    }

    /**
     * Update province
     * 
     * @param int $id Province ID
     * @return RedirectResponse
     */
    public function updateProvince(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $province = $this->provinceModel->find($id);
        if (!$province) {
            return redirect()->back()->with('error', 'Provinsi tidak ditemukan.');
        }

        $rules = [
            'name' => [
                'rules' => "required|min_length[3]|max_length[100]|is_unique[provinces.name,id,{$id}]",
                'errors' => [
                    'required' => 'Nama provinsi wajib diisi',
                    'is_unique' => 'Nama provinsi sudah ada'
                ]
            ],
            'code' => [
                'rules' => "permit_empty|max_length[10]|is_unique[provinces.code,id,{$id}]",
                'errors' => [
                    'is_unique' => 'Kode provinsi sudah digunakan'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code') ?: null
            ];

            $this->provinceModel->update($id, $data);

            return redirect()->to('/super/master/provinces')
                ->with('success', 'Provinsi berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating province: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate provinsi.');
        }
    }

    /**
     * Delete province
     * Validates that province is not used by members or regencies
     * 
     * @param int $id Province ID
     * @return RedirectResponse
     */
    public function deleteProvince(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $province = $this->provinceModel->find($id);
        if (!$province) {
            return redirect()->back()->with('error', 'Provinsi tidak ditemukan.');
        }

        // Check if used by members
        $memberCount = $this->memberModel->where('province_id', $id)->countAllResults();
        if ($memberCount > 0) {
            return redirect()->back()
                ->with('error', "Provinsi tidak dapat dihapus karena digunakan oleh {$memberCount} anggota.");
        }

        // Check if has regencies
        $regencyCount = $this->regencyModel->where('province_id', $id)->countAllResults();
        if ($regencyCount > 0) {
            return redirect()->back()
                ->with('error', "Provinsi tidak dapat dihapus karena memiliki {$regencyCount} kabupaten/kota.");
        }

        try {
            $this->provinceModel->delete($id);
            return redirect()->to('/super/master/provinces')
                ->with('success', 'Provinsi berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting province: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus provinsi.');
        }
    }

    // ========================================
    // PROVINCE IMPORT METHODS
    // ========================================

    /**
     * Download template Excel untuk import provinces
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function downloadProvinceTemplate()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $fileName = 'template_import_provinsi_' . date('YmdHis') . '.xlsx';
            $filePath = WRITEPATH . 'uploads/temp/' . $fileName;

            // Ensure directory exists
            if (!is_dir(WRITEPATH . 'uploads/temp/')) {
                mkdir(WRITEPATH . 'uploads/temp/', 0755, true);
            }

            // Generate template
            $result = $this->provinceImportService->generateTemplate($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            // Download file
            return $this->response->download($filePath, null)
                ->setFileName($fileName);
        } catch (\Exception $e) {
            log_message('error', 'Error downloading province template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal download template.');
        }
    }

    /**
     * Upload and import provinces from Excel
     * 
     * @return RedirectResponse
     */
    public function importProvinces(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $file = $this->request->getFile('file');

        // Validate file
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ada file yang diupload.');
        }

        // Check extension
        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getClientExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan Excel (.xlsx atau .xls)');
        }

        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->getSize() > $maxSize) {
            return redirect()->back()->with('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
        }

        try {
            // Move file to temp directory
            $fileName = $file->getRandomName();
            $tempPath = WRITEPATH . 'uploads/temp/';

            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $file->move($tempPath, $fileName);
            $filePath = $tempPath . $fileName;

            // Import
            $result = $this->provinceImportService->import($filePath);

            // Delete temp file
            @unlink($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            $data = $result['data'];

            // Check if there are errors
            if (!empty($data['errors'])) {
                // Store errors in session for display
                session()->set('import_errors', $data['errors']);
            }

            return redirect()->to('/super/master/provinces')
                ->with('success', $result['message'])
                ->with('import_stats', [
                    'total' => $data['total'],
                    'success' => $data['success'],
                    'failed' => $data['failed'],
                    'duplicates' => $data['duplicates']
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Error importing provinces: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    // ========================================
    // REGENCIES MANAGEMENT
    // ========================================

    /**
     * Display list of regencies
     * 
     * @return string|RedirectResponse
     */
    public function regencies()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get filter
        $provinceId = $this->request->getGet('province_id');

        // Query with province data
        $builder = $this->regencyModel
            ->select('regencies.*, 
                      provinces.name as province_name,
                      COUNT(DISTINCT member_profiles.id) as member_count')
            ->join('provinces', 'provinces.id = regencies.province_id')
            ->join('member_profiles', 'member_profiles.regency_id = regencies.id', 'left')
            ->groupBy('regencies.id');

        if ($provinceId) {
            $builder->where('regencies.province_id', $provinceId);
        }

        $regencies = $builder->orderBy('provinces.name', 'ASC')
            ->orderBy('regencies.name', 'ASC')
            ->findAll();

        // Get all provinces for filter
        $provinces = $this->provinceModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Master Data - Kabupaten/Kota',
            'regencies' => $regencies,
            'provinces' => $provinces,
            'selectedProvinceId' => $provinceId,
            'total' => count($regencies),
            'validation' => \Config\Services::validation()
        ];

        return view('super/master/regencies', $data);
    }

    /**
     * Store new regency
     * 
     * @return RedirectResponse
     */
    public function storeRegency(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'province_id' => 'required|integer|is_not_unique[provinces.id]',
            'name' => 'required|min_length[3]|max_length[100]',
            'code' => 'permit_empty|max_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'province_id' => $this->request->getPost('province_id'),
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code') ?: null,
                'type' => 'Kabupaten'
            ];

            $this->regencyModel->insert($data);

            return redirect()->to('/super/master/regencies')
                ->with('success', 'Kabupaten/Kota berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating regency: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan kabupaten/kota.');
        }
    }

    /**
     * Update regency
     * 
     * @param int $id Regency ID
     * @return RedirectResponse
     */
    public function updateRegency(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $regency = $this->regencyModel->find($id);
        if (!$regency) {
            return redirect()->back()->with('error', 'Kabupaten/Kota tidak ditemukan.');
        }

        $rules = [
            'province_id' => 'required|integer|is_not_unique[provinces.id]',
            'name' => 'required|min_length[3]|max_length[100]',
            'code' => 'permit_empty|max_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'province_id' => $this->request->getPost('province_id'),
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code') ?: null,
                'type' => 'Kabupaten'
            ];

            $this->regencyModel->update($id, $data);

            return redirect()->to('/super/master/regencies')
                ->with('success', 'Kabupaten/Kota berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating regency: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate kabupaten/kota.');
        }
    }

    /**
     * Delete regency
     * 
     * @param int $id Regency ID
     * @return RedirectResponse
     */
    public function deleteRegency(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $regency = $this->regencyModel->find($id);
        if (!$regency) {
            return redirect()->back()->with('error', 'Kabupaten/Kota tidak ditemukan.');
        }

        // Check if used by members
        $memberCount = $this->memberModel->where('regency_id', $id)->countAllResults();
        if ($memberCount > 0) {
            return redirect()->back()
                ->with('error', "Kabupaten/Kota tidak dapat dihapus karena digunakan oleh {$memberCount} anggota.");
        }

        try {
            $this->regencyModel->delete($id);
            return redirect()->to('/super/master/regencies')
                ->with('success', 'Kabupaten/Kota berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting regency: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kabupaten/kota.');
        }
    }

    // ========================================
    // REGENCY IMPORT METHODS
    // ========================================

    /**
     * Download template Excel untuk import regencies
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function downloadRegencyTemplate()
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $fileName = 'template_import_kabkota_' . date('YmdHis') . '.xlsx';
            $filePath = WRITEPATH . 'uploads/temp/' . $fileName;

            if (!is_dir(WRITEPATH . 'uploads/temp/')) {
                mkdir(WRITEPATH . 'uploads/temp/', 0755, true);
            }

            $result = $this->regencyImportService->generateTemplate($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return $this->response->download($filePath, null)
                ->setFileName($fileName);
        } catch (\Exception $e) {
            log_message('error', 'Error downloading regency template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal download template.');
        }
    }

    /**
     * Upload and import regencies from Excel
     * 
     * @return RedirectResponse
     */
    public function importRegencies(): RedirectResponse
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ada file yang diupload.');
        }

        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getClientExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan Excel (.xlsx atau .xls)');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->getSize() > $maxSize) {
            return redirect()->back()->with('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
        }

        try {
            $fileName = $file->getRandomName();
            $tempPath = WRITEPATH . 'uploads/temp/';

            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $file->move($tempPath, $fileName);
            $filePath = $tempPath . $fileName;

            $result = $this->regencyImportService->import($filePath);

            @unlink($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            $data = $result['data'];

            if (!empty($data['errors'])) {
                session()->set('import_errors', $data['errors']);
            }

            return redirect()->to('/super/master/regencies')
                ->with('success', $result['message'])
                ->with('import_stats', [
                    'total' => $data['total'],
                    'success' => $data['success'],
                    'failed' => $data['failed'],
                    'duplicates' => $data['duplicates']
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Error importing regencies: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    // ========================================
    // UNIVERSITIES MANAGEMENT - FIXED
    // ========================================

    /**
     * Display list of universities
     * 
     * @return string|RedirectResponse
     */
    public function universities()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get filter
        $search = $this->request->getGet('search');
        $type = $this->request->getGet('type');

        $builder = $this->universityModel
            ->select('universities.*, 
                  COUNT(DISTINCT member_profiles.id) as member_count')
            ->join('member_profiles', 'member_profiles.university_id = universities.id', 'left')
            ->groupBy('universities.id');

        if ($search) {
            $builder->like('universities.name', $search);
        }

        if ($type) {
            $builder->where('universities.type', $type);
        }

        $universities = $builder->orderBy('universities.name', 'ASC')->findAll();

        $data = [
            'title' => 'Master Data - Perguruan Tinggi',
            'universities' => $universities,
            'search' => $search,
            'selectedType' => $type,
            'total' => count($universities),
            'validation' => \Config\Services::validation()
        ];

        return view('super/master/universities', $data);
    }

    /**
     * Store new university
     * 
     * @return RedirectResponse
     */
    public function storeUniversity(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama universitas wajib diisi',
                    'min_length' => 'Nama minimal 3 karakter'
                ]
            ],
            'type' => [
                'rules' => 'required|in_list[Negeri,Swasta,Kedinasan]',
                'errors' => [
                    'required' => 'Jenis PT wajib dipilih',
                    'in_list' => 'Jenis PT tidak valid'
                ]
            ],
            'code' => 'permit_empty|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'type' => $this->request->getPost('type'),
                'code' => $this->request->getPost('code') ?: null,
                'address' => $this->request->getPost('address') ?: null,
                'is_active' => 1
            ];

            $this->universityModel->insert($data);

            return redirect()->to('/super/master/universities')
                ->with('success', 'Perguruan Tinggi berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating university: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan perguruan tinggi: ' . $e->getMessage());
        }
    }

    /**
     * Update university
     * 
     * @param int $id University ID
     * @return RedirectResponse
     */
    public function updateUniversity(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $university = $this->universityModel->find($id);
        if (!$university) {
            return redirect()->back()->with('error', 'Perguruan Tinggi tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'type' => 'required|in_list[Negeri,Swasta,Kedinasan]',
            'code' => 'permit_empty|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'type' => $this->request->getPost('type'),
                'code' => $this->request->getPost('code') ?: null,
                'address' => $this->request->getPost('address') ?: null
            ];

            $this->universityModel->update($id, $data);

            return redirect()->to('/super/master/universities')
                ->with('success', 'Perguruan Tinggi berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating university: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate perguruan tinggi: ' . $e->getMessage());
        }
    }

    /**
     * Delete university
     * 
     * @param int $id University ID
     * @return RedirectResponse
     */
    public function deleteUniversity(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $university = $this->universityModel->find($id);
        if (!$university) {
            return redirect()->back()->with('error', 'Perguruan Tinggi tidak ditemukan.');
        }

        // Check if used by members
        $memberCount = $this->memberModel->where('university_id', $id)->countAllResults();
        if ($memberCount > 0) {
            return redirect()->back()
                ->with('error', "Perguruan Tinggi tidak dapat dihapus karena digunakan oleh {$memberCount} anggota.");
        }

        // Check if has study programs
        $programCount = $this->studyProgramModel->where('university_id', $id)->countAllResults();
        if ($programCount > 0) {
            return redirect()->back()
                ->with('error', "Perguruan Tinggi tidak dapat dihapus karena memiliki {$programCount} program studi.");
        }

        try {
            $this->universityModel->delete($id);
            return redirect()->to('/super/master/universities')
                ->with('success', 'Perguruan Tinggi berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting university: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus perguruan tinggi.');
        }
    }

    // ========================================
    // UNIVERSITY IMPORT METHODS
    // ========================================

    /**
     * Download template Excel untuk import universities
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function downloadUniversityTemplate()
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $fileName = 'template_import_universitas_' . date('YmdHis') . '.xlsx';
            $filePath = WRITEPATH . 'uploads/temp/' . $fileName;

            if (!is_dir(WRITEPATH . 'uploads/temp/')) {
                mkdir(WRITEPATH . 'uploads/temp/', 0755, true);
            }

            $result = $this->universityImportService->generateTemplate($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return $this->response->download($filePath, null)
                ->setFileName($fileName);
        } catch (\Exception $e) {
            log_message('error', 'Error downloading university template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal download template.');
        }
    }

    /**
     * Upload and import universities from Excel
     * 
     * @return RedirectResponse
     */
    public function importUniversities(): RedirectResponse
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ada file yang diupload.');
        }

        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getClientExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan Excel (.xlsx atau .xls)');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->getSize() > $maxSize) {
            return redirect()->back()->with('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
        }

        try {
            $fileName = $file->getRandomName();
            $tempPath = WRITEPATH . 'uploads/temp/';

            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $file->move($tempPath, $fileName);
            $filePath = $tempPath . $fileName;

            $result = $this->universityImportService->import($filePath);

            @unlink($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            $data = $result['data'];

            if (!empty($data['errors'])) {
                session()->set('import_errors', $data['errors']);
            }

            return redirect()->to('/super/master/universities')
                ->with('success', $result['message'])
                ->with('import_stats', [
                    'total' => $data['total'],
                    'success' => $data['success'],
                    'failed' => $data['failed'],
                    'duplicates' => $data['duplicates']
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Error importing universities: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }


    // ========================================
    // STUDY PROGRAMS MANAGEMENT
    // ========================================

    /**
     * Display list of study programs
     * 
     * @return string|RedirectResponse
     */
    public function studyPrograms()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get filter
        $universityId = $this->request->getGet('university_id');
        $search = $this->request->getGet('search');

        $builder = $this->studyProgramModel
            ->select('study_programs.*, 
                      universities.name as university_name,
                      COUNT(DISTINCT member_profiles.id) as member_count')
            ->join('universities', 'universities.id = study_programs.university_id')
            ->join('member_profiles', 'member_profiles.study_program_id = study_programs.id', 'left')
            ->groupBy('study_programs.id');

        if ($universityId) {
            $builder->where('study_programs.university_id', $universityId);
        }

        if ($search) {
            $builder->like('study_programs.name', $search);
        }

        $programs = $builder->orderBy('universities.name', 'ASC')
            ->orderBy('study_programs.name', 'ASC')
            ->findAll();

        // Get all universities for filter
        $universities = $this->universityModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Master Data - Program Studi',
            'programs' => $programs,
            'universities' => $universities,
            'selectedUniversityId' => $universityId,
            'search' => $search,
            'total' => count($programs),
            'validation' => \Config\Services::validation()
        ];

        return view('super/master/study_programs', $data);
    }

    /**
     * Store new study program
     * 
     * @return RedirectResponse
     */
    public function storeStudyProgram(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'university_id' => 'required|integer|is_not_unique[universities.id]',
            'name' => 'required|min_length[3]|max_length[255]',
            'level' => 'permit_empty|in_list[D3,D4,S1,S2,S3]',
            'code' => 'permit_empty|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'university_id' => $this->request->getPost('university_id'),
                'name' => $this->request->getPost('name'),
                'level' => $this->request->getPost('level') ?: null,
                'code' => $this->request->getPost('code') ?: null
            ];

            $this->studyProgramModel->insert($data);

            return redirect()->to('/super/master/study-programs')
                ->with('success', 'Program Studi berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating study program: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan program studi.');
        }
    }

    /**
     * Update study program
     * 
     * @param int $id Study Program ID
     * @return RedirectResponse
     */
    public function updateStudyProgram(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $program = $this->studyProgramModel->find($id);
        if (!$program) {
            return redirect()->back()->with('error', 'Program Studi tidak ditemukan.');
        }

        $rules = [
            'university_id' => 'required|integer|is_not_unique[universities.id]',
            'name' => 'required|min_length[3]|max_length[255]',
            'level' => 'permit_empty|in_list[D3,D4,S1,S2,S3]',
            'code' => 'permit_empty|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'university_id' => $this->request->getPost('university_id'),
                'name' => $this->request->getPost('name'),
                'level' => $this->request->getPost('level') ?: null,
                'code' => $this->request->getPost('code') ?: null
            ];

            $this->studyProgramModel->update($id, $data);

            return redirect()->to('/super/master/study-programs')
                ->with('success', 'Program Studi berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating study program: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate program studi.');
        }
    }

    /**
     * Delete study program
     * 
     * @param int $id Study Program ID
     * @return RedirectResponse
     */
    public function deleteStudyProgram(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $program = $this->studyProgramModel->find($id);
        if (!$program) {
            return redirect()->back()->with('error', 'Program Studi tidak ditemukan.');
        }

        // Check if used by members
        $memberCount = $this->memberModel->where('study_program_id', $id)->countAllResults();
        if ($memberCount > 0) {
            return redirect()->back()
                ->with('error', "Program Studi tidak dapat dihapus karena digunakan oleh {$memberCount} anggota.");
        }

        try {
            $this->studyProgramModel->delete($id);
            return redirect()->to('/super/master/study-programs')
                ->with('success', 'Program Studi berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting study program: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus program studi.');
        }
    }

    /**
     * Download template Excel untuk import study programs
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function downloadStudyProgramTemplate()
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $fileName = 'template_import_prodi_' . date('YmdHis') . '.xlsx';
            $filePath = WRITEPATH . 'uploads/temp/' . $fileName;

            if (!is_dir(WRITEPATH . 'uploads/temp/')) {
                mkdir(WRITEPATH . 'uploads/temp/', 0755, true);
            }

            $result = $this->studyProgramImportService->generateTemplate($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return $this->response->download($filePath, null)
                ->setFileName($fileName);
        } catch (\Exception $e) {
            log_message('error', 'Error downloading study program template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal download template.');
        }
    }

    /**
     * Upload and import study programs from Excel
     * 
     * @return RedirectResponse
     */
    public function importStudyPrograms(): RedirectResponse
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ada file yang diupload.');
        }

        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getClientExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan Excel (.xlsx atau .xls)');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->getSize() > $maxSize) {
            return redirect()->back()->with('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
        }

        try {
            $fileName = $file->getRandomName();
            $tempPath = WRITEPATH . 'uploads/temp/';

            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $file->move($tempPath, $fileName);
            $filePath = $tempPath . $fileName;

            $result = $this->studyProgramImportService->import($filePath);

            @unlink($filePath);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            $data = $result['data'];

            if (!empty($data['errors'])) {
                session()->set('import_errors', $data['errors']);
            }

            return redirect()->to('/super/master/study-programs')
                ->with('success', $result['message'])
                ->with('import_stats', [
                    'total' => $data['total'],
                    'success' => $data['success'],
                    'failed' => $data['failed'],
                    'duplicates' => $data['duplicates']
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Error importing study programs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    // ========================================
    // BULK OPERATIONS
    // ========================================

    /**
     * Export master data to Excel
     * 
     * @param string $type Data type: provinces, regencies, universities, study_programs
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function export(string $type)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            switch ($type) {
                case 'provinces':
                    $data = $this->provinceModel->orderBy('name')->findAll();
                    $sheet->fromArray(['ID', 'Nama Provinsi', 'Kode'], null, 'A1');
                    $row = 2;
                    foreach ($data as $item) {
                        $sheet->fromArray([$item->id, $item->name, $item->code], null, "A{$row}");
                        $row++;
                    }
                    $filename = 'master_provinsi_' . date('YmdHis') . '.xlsx';
                    break;

                case 'regencies':
                    $data = $this->regencyModel
                        ->select('regencies.*, provinces.name as province_name')
                        ->join('provinces', 'provinces.id = regencies.province_id')
                        ->orderBy('provinces.name')
                        ->orderBy('regencies.name')
                        ->findAll();
                    $sheet->fromArray(['ID', 'Nama Kabupaten/Kota', 'Provinsi', 'Kode'], null, 'A1');
                    $row = 2;
                    foreach ($data as $item) {
                        $sheet->fromArray([$item->id, $item->name, $item->province_name, $item->code], null, "A{$row}");
                        $row++;
                    }
                    $filename = 'master_kabkota_' . date('YmdHis') . '.xlsx';
                    break;

                case 'universities':
                    $data = $this->universityModel->orderBy('name')->findAll();
                    $sheet->fromArray(['ID', 'Nama Universitas', 'Jenis', 'Kode', 'Alamat'], null, 'A1');
                    $row = 2;
                    foreach ($data as $item) {
                        $sheet->fromArray([$item->id, $item->name, $item->type, $item->code, $item->address], null, "A{$row}");
                        $row++;
                    }
                    $filename = 'master_universitas_' . date('YmdHis') . '.xlsx';
                    break;

                case 'study_programs':
                    $data = $this->studyProgramModel
                        ->select('study_programs.*, universities.name as university_name')
                        ->join('universities', 'universities.id = study_programs.university_id')
                        ->orderBy('universities.name')
                        ->orderBy('study_programs.name')
                        ->findAll();
                    $sheet->fromArray(['ID', 'Nama Program Studi', 'Universitas', 'Jenjang', 'Kode'], null, 'A1');
                    $row = 2;
                    foreach ($data as $item) {
                        $sheet->fromArray([$item->id, $item->name, $item->university_name, $item->level, $item->code], null, "A{$row}");
                        $row++;
                    }
                    $filename = 'master_prodi_' . date('YmdHis') . '.xlsx';
                    break;

                default:
                    return redirect()->back()->with('error', 'Tipe export tidak valid.');
            }

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment;filename=\"{$filename}\"");
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error exporting master data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data.');
        }
    }

    // ========================================
    // EMPLOYMENT STATUS MANAGEMENT
    // ========================================

    /**
     * Display list of employment statuses
     * 
     * @return string|RedirectResponse
     */
    public function employmentStatus()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all employment statuses with member count
        $statuses = $this->employmentStatusModel
            ->select('employment_statuses.*, 
                  COUNT(DISTINCT member_profiles.id) as member_count')
            ->join('member_profiles', 'member_profiles.employment_status_id = employment_statuses.id', 'left')
            ->groupBy('employment_statuses.id')
            ->findAll();

        $data = [
            'title' => 'Master Data - Status Kepegawaian',
            'statuses' => $statuses,
            'total' => count($statuses),
            'validation' => \Config\Services::validation()
        ];

        return view('super/master/employment_status', $data);
    }

    /**
     * Store new employment status
     * 
     * @return RedirectResponse
     */
    public function storeEmploymentStatus(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]|is_unique[employment_statuses.name]',
                'errors' => [
                    'required' => 'Nama status kepegawaian wajib diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'is_unique' => 'Nama status kepegawaian sudah ada'
                ]
            ],
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description') ?: null,
                'is_active' => $this->request->getPost('is_active') ?? 1
            ];

            $this->employmentStatusModel->insert($data);

            return redirect()->to('/super/master/employment-status')
                ->with('success', 'Status Kepegawaian berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating employment status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan status kepegawaian.');
        }
    }

    /**
     * Update employment status
     * 
     * @param int $id Employment Status ID
     * @return RedirectResponse
     */
    public function updateEmploymentStatus(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $status = $this->employmentStatusModel->find($id);
        if (!$status) {
            return redirect()->back()->with('error', 'Status Kepegawaian tidak ditemukan.');
        }

        $rules = [
            'name' => [
                'rules' => "required|min_length[3]|max_length[100]|is_unique[employment_statuses.name,id,{$id}]",
                'errors' => [
                    'required' => 'Nama status kepegawaian wajib diisi',
                    'is_unique' => 'Nama status kepegawaian sudah ada'
                ]
            ],

            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description') ?: null,
                'is_active' => $this->request->getPost('is_active') ?? 1
            ];

            $this->employmentStatusModel->update($id, $data);

            return redirect()->to('/super/master/employment-status')
                ->with('success', 'Status Kepegawaian berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating employment status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate status kepegawaian.');
        }
    }

    /**
     * Delete employment status
     * 
     * @param int $id Employment Status ID
     * @return RedirectResponse
     */
    public function deleteEmploymentStatus(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $status = $this->employmentStatusModel->find($id);
        if (!$status) {
            return redirect()->back()->with('error', 'Status Kepegawaian tidak ditemukan.');
        }

        // Check if used by members
        $memberCount = $this->memberModel->where('employment_status_id', $id)->countAllResults();
        if ($memberCount > 0) {
            return redirect()->back()
                ->with('error', "Status Kepegawaian tidak dapat dihapus karena digunakan oleh {$memberCount} anggota.");
        }

        try {
            $this->employmentStatusModel->delete($id);
            return redirect()->to('/super/master/employment-status')
                ->with('success', 'Status Kepegawaian berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting employment status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus status kepegawaian.');
        }
    }

    // ========================================
    // SALARY RANGES MANAGEMENT
    // ========================================

    /**
     * Display list of salary ranges
     * 
     * @return string|RedirectResponse
     */
    public function salaryRanges()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all salary ranges with member count
        $ranges = $this->salaryRangeModel
            ->select('salary_ranges.*, 
                  COUNT(DISTINCT member_profiles.id) as member_count')
            ->join('member_profiles', 'member_profiles.salary_range_id = salary_ranges.id', 'left')
            ->groupBy('salary_ranges.id')
            ->findAll();

        $data = [
            'title' => 'Master Data - Range Gaji',
            'ranges' => $ranges,
            'total' => count($ranges),
            'validation' => \Config\Services::validation()
        ];

        return view('super/master/salary_ranges', $data);
    }

    /**
     * Store new salary range
     * 
     * @return RedirectResponse
     */
    public function storeSalaryRange(): RedirectResponse
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Nama range gaji wajib diisi',
                    'min_length' => 'Nama minimal 3 karakter'
                ]
            ],
            'min_amount' => 'permit_empty|decimal',
            'max_amount' => 'permit_empty|decimal',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // CUSTOM VALIDATION: Compare min vs max
        $minAmount = $this->request->getPost('min_amount');
        $maxAmount = $this->request->getPost('max_amount');

        if ($minAmount && $maxAmount && (float)$maxAmount < (float)$minAmount) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gaji maksimal harus lebih besar atau sama dengan gaji minimal.');
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'min_amount' => $minAmount ?: null,
                'max_amount' => $maxAmount ?: null,
                'is_active' => $this->request->getPost('is_active') ?? 1
            ];

            $this->salaryRangeModel->insert($data);

            return redirect()->to('/super/master/salary-ranges')
                ->with('success', 'Range Gaji berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating salary range: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan range gaji: ' . $e->getMessage());
        }
    }

    /**
     * Update salary range
     * 
     * @param int $id Salary Range ID
     * @return RedirectResponse
     */
    public function updateSalaryRange(int $id): RedirectResponse
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $range = $this->salaryRangeModel->find($id);
        if (!$range) {
            return redirect()->back()->with('error', 'Range Gaji tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'min_amount' => 'permit_empty|decimal',
            'max_amount' => 'permit_empty|decimal',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // CUSTOM VALIDATION: Compare min vs max
        $minAmount = $this->request->getPost('min_amount');
        $maxAmount = $this->request->getPost('max_amount');

        if ($minAmount && $maxAmount && (float)$maxAmount < (float)$minAmount) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gaji maksimal harus lebih besar atau sama dengan gaji minimal.');
        }

        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'min_amount' => $minAmount ?: null,
                'max_amount' => $maxAmount ?: null,
                'is_active' => $this->request->getPost('is_active') ?? 1
            ];

            $this->salaryRangeModel->update($id, $data);

            return redirect()->to('/super/master/salary-ranges')
                ->with('success', 'Range Gaji berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating salary range: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate range gaji: ' . $e->getMessage());
        }
    }

    /**
     * Delete salary range
     * 
     * @param int $id Salary Range ID
     * @return RedirectResponse
     */
    public function deleteSalaryRange(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $range = $this->salaryRangeModel->find($id);
        if (!$range) {
            return redirect()->back()->with('error', 'Range Gaji tidak ditemukan.');
        }

        // Check if used by members
        $memberCount = $this->memberModel->where('salary_range_id', $id)->countAllResults();
        if ($memberCount > 0) {
            return redirect()->back()
                ->with('error', "Range Gaji tidak dapat dihapus karena digunakan oleh {$memberCount} anggota.");
        }

        try {
            $this->salaryRangeModel->delete($id);
            return redirect()->to('/super/master/salary-ranges')
                ->with('success', 'Range Gaji berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting salary range: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus range gaji.');
        }
    }
}
