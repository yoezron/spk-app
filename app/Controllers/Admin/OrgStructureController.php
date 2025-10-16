<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\OrgStructureService;
use App\Models\OrgUnitModel;
use App\Models\OrgPositionModel;
use App\Models\OrgAssignmentModel;
use App\Models\UserModel;
use App\Models\MemberProfileModel;
use App\Models\ProvinceModel;
use App\Models\UniversityModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * OrgStructureController
 * 
 * Controller untuk mengelola struktur organisasi SPK
 * Menyediakan interface admin untuk manage units, positions, dan assignments
 * 
 * Features:
 * - CRUD organizational units
 * - CRUD positions/jabatan
 * - Assign/unassign members to positions
 * - View hierarchical structure
 * - Statistics & reporting
 * - AJAX operations
 * 
 * Permissions:
 * - org_structure.view: View structure
 * - org_structure.manage: Create/edit/delete units & positions
 * - org_structure.assign: Assign members to positions
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class OrgStructureController extends BaseController
{
    /**
     * @var OrgStructureService
     */
    protected $structureService;

    /**
     * @var OrgUnitModel
     */
    protected $unitModel;

    /**
     * @var OrgPositionModel
     */
    protected $positionModel;

    /**
     * @var OrgAssignmentModel
     */
    protected $assignmentModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->structureService = new OrgStructureService();
        $this->unitModel = new OrgUnitModel();
        $this->positionModel = new OrgPositionModel();
        $this->assignmentModel = new OrgAssignmentModel();
    }

    // =====================================================
    // MAIN PAGES
    // =====================================================

    /**
     * Display organizational structure (main page)
     * Tree view dengan units, positions, dan assigned members
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('org_structure.view')) {
            return redirect()->to('/admin/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        try {
            // Get filters from query string
            $filters = [
                'scope' => $this->request->getGet('scope'),
                'region_id' => $this->request->getGet('region_id'),
                'is_active' => $this->request->getGet('is_active') ?? 1
            ];

            // Remove empty filters
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // Get hierarchy
            $result = $this->structureService->getHierarchy($filters);

            // Get statistics
            $statsResult = $this->structureService->getStatistics();

            // Get provinces for filter
            $provinceModel = new ProvinceModel();
            $provinces = $provinceModel->orderBy('name', 'ASC')->findAll();

            $data = [
                'title' => 'Struktur Organisasi',
                'hierarchy' => $result['success'] ? $result['data'] : [],
                'statistics' => $statsResult['success'] ? $statsResult['data'] : [],
                'provinces' => $provinces,
                'filters' => $filters,
                'can_manage' => auth()->user()->can('org_structure.manage'),
                'can_assign' => auth()->user()->can('org_structure.assign')
            ];

            return view('admin/org_structure/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in OrgStructureController::index: ' . $e->getMessage());
            return redirect()->to('/admin/dashboard')->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Show unit detail with positions and assignments
     * 
     * @param int $unitId Unit ID
     * @return string|ResponseInterface
     */
    public function showUnit(int $unitId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.view')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $result = $this->structureService->getUnitDetail($unitId);

            if (!$result['success']) {
                return redirect()->to('/admin/org-structure')->with('error', $result['message']);
            }

            $data = [
                'title' => 'Detail Unit - ' . $result['data']['name'],
                'unit' => $result['data'],
                'can_manage' => auth()->user()->can('org_structure.manage'),
                'can_assign' => auth()->user()->can('org_structure.assign')
            ];

            return view('admin/org_structure/unit_detail', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in showUnit: ' . $e->getMessage());
            return redirect()->to('/admin/org-structure')->with('error', 'Terjadi kesalahan');
        }
    }

    // =====================================================
    // UNIT MANAGEMENT
    // =====================================================

    /**
     * Show form to create new unit
     * 
     * @return string|ResponseInterface
     */
    public function createUnit()
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            // Get parent options
            $units = $this->unitModel->active()->orderBy('name', 'ASC')->findAll();

            // Get provinces and universities for filters
            $provinceModel = new ProvinceModel();
            $universityModel = new UniversityModel();

            $data = [
                'title' => 'Tambah Unit Organisasi',
                'units' => $units,
                'provinces' => $provinceModel->orderBy('name', 'ASC')->findAll(),
                'universities' => $universityModel->orderBy('name', 'ASC')->findAll(),
                'parent_id' => $this->request->getGet('parent_id')
            ];

            return view('admin/org_structure/unit_form', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in createUnit: ' . $e->getMessage());
            return redirect()->to('/admin/org-structure')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Store new unit
     * 
     * @return ResponseInterface
     */
    public function storeUnit()
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $data = $this->request->getPost();

            // Validate
            $rules = [
                'name' => 'required|max_length[150]',
                'scope' => 'required|in_list[pusat,wilayah,kampus,departemen,divisi,seksi]',
                'level' => 'required|integer',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Create unit
            $result = $this->structureService->createUnit($data);

            if (!$result['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message'])
                    ->with('errors', $result['errors'] ?? []);
            }

            return redirect()->to('/admin/org-structure')
                ->with('success', 'Unit organisasi berhasil ditambahkan');
        } catch (\Exception $e) {
            log_message('error', 'Error in storeUnit: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Show form to edit unit
     * 
     * @param int $unitId Unit ID
     * @return string|ResponseInterface
     */
    public function editUnit(int $unitId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $unit = $this->unitModel->find($unitId);

            if (!$unit) {
                return redirect()->to('/admin/org-structure')->with('error', 'Unit tidak ditemukan');
            }

            // Get parent options (exclude self and descendants)
            $units = $this->unitModel->active()->findAll();
            $descendants = $this->unitModel->getDescendantIds($unitId);
            $descendants[] = $unitId; // Include self

            $units = array_filter($units, fn($u) => !in_array($u['id'], $descendants));

            // Get provinces and universities
            $provinceModel = new ProvinceModel();
            $universityModel = new UniversityModel();

            $data = [
                'title' => 'Edit Unit Organisasi',
                'unit' => $unit,
                'units' => $units,
                'provinces' => $provinceModel->orderBy('name', 'ASC')->findAll(),
                'universities' => $universityModel->orderBy('name', 'ASC')->findAll()
            ];

            return view('admin/org_structure/unit_form', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in editUnit: ' . $e->getMessage());
            return redirect()->to('/admin/org-structure')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Update unit
     * 
     * @param int $unitId Unit ID
     * @return ResponseInterface
     */
    public function updateUnit(int $unitId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $data = $this->request->getPost();

            // Validate
            $rules = [
                'name' => 'required|max_length[150]',
                'scope' => 'required|in_list[pusat,wilayah,kampus,departemen,divisi,seksi]',
                'level' => 'required|integer',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Update unit
            $result = $this->structureService->updateUnit($unitId, $data);

            if (!$result['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message'])
                    ->with('errors', $result['errors'] ?? []);
            }

            return redirect()->to('/admin/org-structure')
                ->with('success', 'Unit organisasi berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in updateUnit: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Delete unit
     * 
     * @param int $unitId Unit ID
     * @return ResponseInterface
     */
    public function deleteUnit(int $unitId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ]);
        }

        try {
            $result = $this->structureService->deleteUnit($unitId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error in deleteUnit: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // =====================================================
    // POSITION MANAGEMENT
    // =====================================================

    /**
     * Show form to create new position
     * 
     * @return string|ResponseInterface
     */
    public function createPosition()
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $unitId = $this->request->getGet('unit_id');

            // Get units for selection
            $units = $this->unitModel->active()->orderBy('name', 'ASC')->findAll();

            // Get positions for reports_to
            $positions = $this->positionModel->active()->orderBy('title', 'ASC')->findAll();

            $data = [
                'title' => 'Tambah Posisi/Jabatan',
                'units' => $units,
                'positions' => $positions,
                'unit_id' => $unitId
            ];

            return view('admin/org_structure/position_form', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in createPosition: ' . $e->getMessage());
            return redirect()->to('/admin/org-structure')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Store new position
     * 
     * @return ResponseInterface
     */
    public function storePosition()
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $data = $this->request->getPost();

            // Validate
            $rules = [
                'unit_id' => 'required|integer',
                'title' => 'required|max_length[150]',
                'position_type' => 'required|in_list[executive,structural,functional,coordinator,staff]',
                'position_level' => 'required|in_list[top,middle,lower]',
                'max_holders' => 'permit_empty|integer|greater_than[0]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Create position
            $result = $this->structureService->createPosition($data);

            if (!$result['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message'])
                    ->with('errors', $result['errors'] ?? []);
            }

            return redirect()->to('/admin/org-structure/unit/' . $data['unit_id'])
                ->with('success', 'Posisi berhasil ditambahkan');
        } catch (\Exception $e) {
            log_message('error', 'Error in storePosition: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Show form to edit position
     * 
     * @param int $positionId Position ID
     * @return string|ResponseInterface
     */
    public function editPosition(int $positionId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $position = $this->positionModel->find($positionId);

            if (!$position) {
                return redirect()->to('/admin/org-structure')->with('error', 'Posisi tidak ditemukan');
            }

            // Get units
            $units = $this->unitModel->active()->orderBy('name', 'ASC')->findAll();

            // Get positions for reports_to (exclude self)
            $positions = $this->positionModel->active()
                ->where('id !=', $positionId)
                ->orderBy('title', 'ASC')
                ->findAll();

            $data = [
                'title' => 'Edit Posisi/Jabatan',
                'position' => $position,
                'units' => $units,
                'positions' => $positions
            ];

            return view('admin/org_structure/position_form', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in editPosition: ' . $e->getMessage());
            return redirect()->to('/admin/org-structure')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Update position
     * 
     * @param int $positionId Position ID
     * @return ResponseInterface
     */
    public function updatePosition(int $positionId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $data = $this->request->getPost();

            // Validate
            $rules = [
                'unit_id' => 'required|integer',
                'title' => 'required|max_length[150]',
                'position_type' => 'required|in_list[executive,structural,functional,coordinator,staff]',
                'position_level' => 'required|in_list[top,middle,lower]',
                'max_holders' => 'permit_empty|integer|greater_than[0]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Update position
            $result = $this->structureService->updatePosition($positionId, $data);

            if (!$result['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message'])
                    ->with('errors', $result['errors'] ?? []);
            }

            return redirect()->to('/admin/org-structure/unit/' . $data['unit_id'])
                ->with('success', 'Posisi berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in updatePosition: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Delete position
     * 
     * @param int $positionId Position ID
     * @return ResponseInterface
     */
    public function deletePosition(int $positionId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.manage')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ]);
        }

        try {
            $result = $this->structureService->deletePosition($positionId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error in deletePosition: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // =====================================================
    // ASSIGNMENT MANAGEMENT
    // =====================================================

    /**
     * Show form to assign member to position
     * 
     * @param int $positionId Position ID
     * @return string|ResponseInterface
     */
    public function assignMemberForm(int $positionId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.assign')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $position = $this->positionModel->withUnit($positionId);

            if (!$position) {
                return redirect()->to('/admin/org-structure')->with('error', 'Posisi tidak ditemukan');
            }

            // Get available members (active members not in this position)
            $memberModel = new MemberProfileModel();
            $userModel = new UserModel();

            $members = $memberModel->where('membership_status', 'anggota')
                ->orderBy('full_name', 'ASC')
                ->findAll();

            // Filter out members already assigned to this position
            $assignedUserIds = $this->assignmentModel
                ->where('position_id', $positionId)
                ->where('status', 'active')
                ->findColumn('user_id');

            $members = array_filter($members, fn($m) => !in_array($m['user_id'], $assignedUserIds));

            $data = [
                'title' => 'Assign Anggota - ' . $position['title'],
                'position' => $position,
                'members' => $members
            ];

            return view('admin/org_structure/assign_member', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in assignMemberForm: ' . $e->getMessage());
            return redirect()->to('/admin/org-structure')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Assign member to position
     * 
     * @param int $positionId Position ID
     * @return ResponseInterface
     */
    public function assignMember(int $positionId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.assign')) {
            return redirect()->to('/admin/org-structure')->with('error', 'Tidak memiliki akses');
        }

        try {
            $userId = $this->request->getPost('user_id');
            $assignmentData = [
                'started_at' => $this->request->getPost('started_at') ?? date('Y-m-d'),
                'ended_at' => $this->request->getPost('ended_at'),
                'assignment_type' => $this->request->getPost('assignment_type') ?? 'permanent',
                'appointment_letter_number' => $this->request->getPost('appointment_letter_number'),
                'appointment_letter_date' => $this->request->getPost('appointment_letter_date'),
                'notes' => $this->request->getPost('notes')
            ];

            // Validate
            $rules = [
                'user_id' => 'required|integer',
                'started_at' => 'required|valid_date'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Perform assignment
            $result = $this->structureService->assignMember($positionId, $userId, $assignmentData);

            if (!$result['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

            $position = $this->positionModel->find($positionId);
            return redirect()->to('/admin/org-structure/unit/' . $position['unit_id'])
                ->with('success', 'Anggota berhasil ditugaskan');
        } catch (\Exception $e) {
            log_message('error', 'Error in assignMember: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * End assignment
     * 
     * @param int $assignmentId Assignment ID
     * @return ResponseInterface
     */
    public function endAssignment(int $assignmentId)
    {
        // Check permission
        if (!auth()->user()->can('org_structure.assign')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ]);
        }

        try {
            $reason = $this->request->getPost('reason') ?? '';
            $endDate = $this->request->getPost('end_date');

            $result = $this->structureService->endAssignment($assignmentId, $reason, $endDate);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error in endAssignment: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // =====================================================
    // AJAX ENDPOINTS
    // =====================================================

    /**
     * Get unit data (AJAX)
     * 
     * @param int $unitId Unit ID
     * @return ResponseInterface
     */
    public function getUnitData(int $unitId)
    {
        try {
            $result = $this->structureService->getUnitDetail($unitId);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get position data (AJAX)
     * 
     * @param int $positionId Position ID
     * @return ResponseInterface
     */
    public function getPositionData(int $positionId)
    {
        try {
            $result = $this->structureService->getPositionDetail($positionId);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get statistics (AJAX)
     * 
     * @return ResponseInterface
     */
    public function getStatistics()
    {
        try {
            $result = $this->structureService->getStatistics();
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Search members for assignment (AJAX)
     * 
     * @return ResponseInterface
     */
    public function searchMembers()
    {
        try {
            $keyword = $this->request->getGet('q');
            $positionId = $this->request->getGet('position_id');

            $memberModel = new MemberProfileModel();
            $members = $memberModel
                ->where('membership_status', 'anggota')
                ->groupStart()
                ->like('full_name', $keyword)
                ->orLike('member_number', $keyword)
                ->groupEnd()
                ->limit(10)
                ->findAll();

            // Filter out already assigned members
            if ($positionId) {
                $assignedUserIds = $this->assignmentModel
                    ->where('position_id', $positionId)
                    ->where('status', 'active')
                    ->findColumn('user_id');

                $members = array_filter($members, fn($m) => !in_array($m['user_id'], $assignedUserIds));
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => array_values($members)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
