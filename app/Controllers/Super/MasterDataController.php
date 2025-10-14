<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\ProvinceModel;
use App\Models\RegencyModel;
use App\Models\UniversityModel;
use App\Models\StudyProgramModel;
use App\Models\MemberProfileModel;
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
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->provinceModel = new ProvinceModel();
        $this->regencyModel = new RegencyModel();
        $this->universityModel = new UniversityModel();
        $this->studyProgramModel = new StudyProgramModel();
        $this->memberModel = new MemberProfileModel();
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
                'code' => $this->request->getPost('code') ?: null
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
                'code' => $this->request->getPost('code') ?: null
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
    // UNIVERSITIES MANAGEMENT
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
                'rules' => 'required|in_list[Universitas,Institut,Sekolah Tinggi,Politeknik,Akademi]',
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
                'address' => $this->request->getPost('address') ?: null
            ];

            $this->universityModel->insert($data);

            return redirect()->to('/super/master/universities')
                ->with('success', 'Perguruan Tinggi berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating university: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan perguruan tinggi.');
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
            'type' => 'required|in_list[Universitas,Institut,Sekolah Tinggi,Politeknik,Akademi]',
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
            return redirect()->back()->with('error', 'Gagal mengupdate perguruan tinggi.');
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
}
