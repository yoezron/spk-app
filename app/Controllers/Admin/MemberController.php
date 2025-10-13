<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ApproveMemberService;
use App\Services\RegionScopeService;
use App\Services\MemberStatisticsService;
use App\Services\NotificationService;
use App\Models\MemberProfileModel;
use App\Models\UserModel;
use App\Models\ProvinceModel;
use App\Models\UniversityModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * MemberController
 * 
 * Mengelola anggota SPK (approve, reject, suspend, activate)
 * Support regional scope untuk Koordinator Wilayah
 * Export member data dan bulk operations
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MemberController extends BaseController
{
    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var ApproveMemberService
     */
    protected $approveService;

    /**
     * @var RegionScopeService
     */
    protected $regionScope;

    /**
     * @var MemberStatisticsService
     */
    protected $statsService;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var UniversityModel
     */
    protected $universityModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->memberModel = new MemberProfileModel();
        $this->userModel = new UserModel();
        $this->approveService = new ApproveMemberService();
        $this->regionScope = new RegionScopeService();
        $this->statsService = new MemberStatisticsService();
        $this->notificationService = new NotificationService();
        $this->provinceModel = new ProvinceModel();
        $this->universityModel = new UniversityModel();
    }

    /**
     * Display list of all members with regional scope
     * Koordinator Wilayah only see members in their province
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('member.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat daftar anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get filters from request
        $filters = [
            'status' => $this->request->getGet('status'),
            'province_id' => $this->request->getGet('province_id'),
            'university_id' => $this->request->getGet('university_id'),
            'membership_status' => $this->request->getGet('membership_status'),
            'search' => $this->request->getGet('search')
        ];

        // Build query with regional scope
        $builder = $this->memberModel
            ->select('member_profiles.*, users.email, users.active, users.created_at as registered_at, provinces.name as province_name, universities.name as university_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left');

        // CRITICAL: Apply regional scope for Koordinator Wilayah
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $builder->where('member_profiles.province_id', $scopeResult['data']['province_id']);
            }
        }

        // Apply filters
        if ($filters['status'] === 'active') {
            $builder->where('users.active', 1);
        } elseif ($filters['status'] === 'inactive') {
            $builder->where('users.active', 0);
        }

        if (!empty($filters['province_id'])) {
            $builder->where('member_profiles.province_id', $filters['province_id']);
        }

        if (!empty($filters['university_id'])) {
            $builder->where('member_profiles.university_id', $filters['university_id']);
        }

        if (!empty($filters['membership_status'])) {
            $builder->where('member_profiles.membership_status', $filters['membership_status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('member_profiles.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('member_profiles.member_number', $search)
                ->orLike('member_profiles.phone', $search)
                ->groupEnd();
        }

        // Get paginated results
        $members = $builder
            ->orderBy('users.created_at', 'DESC')
            ->paginate(20);

        // Get filter options
        $provinces = $this->provinceModel->findAll();
        $universities = $this->universityModel->findAll();

        $data = [
            'title' => 'Daftar Anggota',
            'members' => $members,
            'pager' => $this->memberModel->pager,
            'filters' => $filters,
            'provinces' => $provinces,
            'universities' => $universities,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/members/index', $data);
    }

    /**
     * Display list of pending members (Calon Anggota)
     * Members waiting for approval
     * 
     * @return string|ResponseInterface
     */
    public function pending()
    {
        // Check permission
        if (!auth()->user()->can('member.approve')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat calon anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Build query
        $builder = $this->memberModel
            ->select('member_profiles.*, users.email, users.created_at as registered_at, provinces.name as province_name, universities.name as university_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->where('member_profiles.membership_status', 'calon_anggota');

        // Apply regional scope for Koordinator Wilayah
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $builder->where('member_profiles.province_id', $scopeResult['data']['province_id']);
            }
        }

        // Get search filter
        $search = $this->request->getGet('search');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('member_profiles.full_name', $search)
                ->orLike('users.email', $search)
                ->orLike('member_profiles.phone', $search)
                ->groupEnd();
        }

        $pendingMembers = $builder
            ->orderBy('users.created_at', 'ASC')
            ->paginate(20);

        $data = [
            'title' => 'Calon Anggota (Pending)',
            'members' => $pendingMembers,
            'pager' => $this->memberModel->pager,
            'search' => $search,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/members/pending', $data);
    }

    /**
     * View member detail
     * Show complete member information
     * 
     * @param int $id Member profile ID
     * @return string|ResponseInterface
     */
    public function show(int $id)
    {
        // Check permission
        if (!auth()->user()->can('member.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat detail anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Check regional scope access
        if ($isKoordinator && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Get member with relations
        $member = $this->memberModel
            ->select('member_profiles.*, users.email, users.active, users.created_at as registered_at, provinces.name as province_name, regencies.name as regency_name, universities.name as university_name, study_programs.name as study_program_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('regencies', 'regencies.id = member_profiles.regency_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->join('study_programs', 'study_programs.id = member_profiles.study_program_id', 'left')
            ->find($id);

        if (!$member) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Anggota',
            'member' => $member,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/members/show', $data);
    }

    /**
     * Approve member (change status from Calon Anggota to Anggota)
     * Send approval notification
     * 
     * @param int $id Member profile ID
     * @return ResponseInterface
     */
    public function approve(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.approve')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menyetujui anggota');
        }

        $user = auth()->user();

        // Check regional scope access
        if ($user->inGroup('koordinator_wilayah') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Approve member using service
        $result = $this->approveService->approveCandidate($id, $user->id);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Reject member application
     * Send rejection notification with reason
     * 
     * @param int $id Member profile ID
     * @return ResponseInterface
     */
    public function reject(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.approve')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menolak anggota');
        }

        $user = auth()->user();

        // Check regional scope access
        if ($user->inGroup('koordinator_wilayah') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Get rejection reason from request
        $reason = $this->request->getPost('reason') ?? 'Tidak memenuhi persyaratan';

        // Reject member using service
        $result = $this->approveService->rejectCandidate($id, $user->id, $reason);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Suspend member account
     * Deactivate user and set membership status to suspended
     * 
     * @param int $id Member profile ID
     * @return ResponseInterface
     */
    public function suspend(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menonaktifkan anggota');
        }

        $user = auth()->user();

        // Check regional scope access
        if ($user->inGroup('koordinator_wilayah') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        try {
            $member = $this->memberModel->find($id);

            if (!$member) {
                return redirect()->back()->with('error', 'Anggota tidak ditemukan');
            }

            // Get suspension reason
            $reason = $this->request->getPost('reason') ?? 'Pelanggaran aturan organisasi';

            // Update member status
            $this->memberModel->update($id, [
                'membership_status' => 'suspended',
                'suspension_reason' => $reason,
                'suspended_at' => date('Y-m-d H:i:s'),
                'suspended_by' => $user->id
            ]);

            // Deactivate user account
            $this->userModel->update($member->user_id, ['active' => 0]);

            // Send notification
            $this->notificationService->sendSuspensionNotification($member->user_id, $reason);

            // Log activity
            log_message('info', "Member {$member->full_name} (ID: {$id}) suspended by user {$user->id}");

            return redirect()->back()->with('success', 'Anggota berhasil dinonaktifkan');
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::suspend: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menonaktifkan anggota: ' . $e->getMessage());
        }
    }

    /**
     * Activate suspended member
     * Reactivate user account and restore membership status
     * 
     * @param int $id Member profile ID
     * @return ResponseInterface
     */
    public function activate(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengaktifkan kembali anggota');
        }

        $user = auth()->user();

        // Check regional scope access
        if ($user->inGroup('koordinator_wilayah') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        try {
            $member = $this->memberModel->find($id);

            if (!$member) {
                return redirect()->back()->with('error', 'Anggota tidak ditemukan');
            }

            // Update member status
            $this->memberModel->update($id, [
                'membership_status' => 'anggota',
                'suspension_reason' => null,
                'suspended_at' => null,
                'suspended_by' => null,
                'reactivated_at' => date('Y-m-d H:i:s'),
                'reactivated_by' => $user->id
            ]);

            // Activate user account
            $this->userModel->update($member->user_id, ['active' => 1]);

            // Send notification
            $this->notificationService->sendReactivationNotification($member->user_id);

            // Log activity
            log_message('info', "Member {$member->full_name} (ID: {$id}) reactivated by user {$user->id}");

            return redirect()->back()->with('success', 'Anggota berhasil diaktifkan kembali');
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::activate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengaktifkan kembali anggota: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve members
     * Approve multiple members at once
     * 
     * @return ResponseInterface
     */
    public function bulkApprove(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.approve')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menyetujui anggota');
        }

        $memberIds = $this->request->getPost('member_ids');

        if (empty($memberIds) || !is_array($memberIds)) {
            return redirect()->back()->with('error', 'Pilih minimal satu anggota untuk disetujui');
        }

        $user = auth()->user();
        $successCount = 0;
        $failCount = 0;

        foreach ($memberIds as $id) {
            // Check regional scope access
            if ($user->inGroup('koordinator_wilayah') && !$this->regionScope->canAccessMember($user->id, $id)) {
                $failCount++;
                continue;
            }

            $result = $this->approveService->approveCandidate($id, $user->id);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = "{$successCount} anggota berhasil disetujui";
        if ($failCount > 0) {
            $message .= ", {$failCount} gagal";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export member data to Excel/CSV
     * Export filtered member list based on current filters
     * 
     * @return ResponseInterface
     */
    public function export(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.export')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengekspor data anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get filters from request
        $filters = [
            'status' => $this->request->getGet('status'),
            'province_id' => $this->request->getGet('province_id'),
            'membership_status' => $this->request->getGet('membership_status'),
            'search' => $this->request->getGet('search')
        ];

        try {
            // Build query with same filters as index
            $builder = $this->memberModel
                ->select('member_profiles.*, users.email, users.active, users.created_at as registered_at, provinces.name as province_name, universities.name as university_name')
                ->join('users', 'users.id = member_profiles.user_id')
                ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
                ->join('universities', 'universities.id = member_profiles.university_id', 'left');

            // Apply regional scope
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    $builder->where('member_profiles.province_id', $scopeResult['data']['province_id']);
                }
            }

            // Apply filters
            if ($filters['status'] === 'active') {
                $builder->where('users.active', 1);
            } elseif ($filters['status'] === 'inactive') {
                $builder->where('users.active', 0);
            }

            if (!empty($filters['province_id'])) {
                $builder->where('member_profiles.province_id', $filters['province_id']);
            }

            if (!empty($filters['membership_status'])) {
                $builder->where('member_profiles.membership_status', $filters['membership_status']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('member_profiles.full_name', $search)
                    ->orLike('users.email', $search)
                    ->orLike('member_profiles.member_number', $search)
                    ->groupEnd();
            }

            $members = $builder->findAll();

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = ['No', 'No. Anggota', 'Nama Lengkap', 'Email', 'Telepon', 'Provinsi', 'Universitas', 'Status', 'Tgl Daftar'];
            $sheet->fromArray($headers, null, 'A1');

            // Style headers
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);

            // Fill data
            $row = 2;
            foreach ($members as $index => $member) {
                $sheet->fromArray([
                    $index + 1,
                    $member->member_number ?? '-',
                    $member->full_name,
                    $member->email,
                    $member->phone,
                    $member->province_name ?? '-',
                    $member->university_name ?? '-',
                    ucfirst($member->membership_status),
                    date('d/m/Y', strtotime($member->registered_at))
                ], null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Generate file
            $filename = 'data_anggota_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::export: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Get members by region (AJAX endpoint)
     * Used for dynamic filtering
     * 
     * @return ResponseInterface JSON response
     */
    public function getByRegion(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $provinceId = $this->request->getGet('province_id');

        if (empty($provinceId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Province ID is required'
            ])->setStatusCode(400);
        }

        $user = auth()->user();

        // Check if koordinator can access this province
        if ($user->inGroup('koordinator_wilayah')) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                if ($scopeResult['data']['province_id'] != $provinceId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Access denied to this province'
                    ])->setStatusCode(403);
                }
            }
        }

        $result = $this->regionScope->getMembersByRegion($provinceId);

        return $this->response->setJSON($result);
    }
}
