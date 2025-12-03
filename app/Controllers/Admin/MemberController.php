<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Member\ApproveMemberService;
use App\Services\RegionScopeService;
use App\Services\Member\MemberStatisticsService;
use App\Services\Communication\NotificationService;
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
        $isKoordinator = $user->inGroup('koordinator');

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
            ->select('member_profiles.*, auth_identities.secret as email, users.active, users.created_at as registered_at, provinces.name as province_name, universities.name as university_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
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
                ->orLike('auth_identities.secret', $search)
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
        $isKoordinator = $user->inGroup('koordinator');

        // Build query
        $builder = $this->memberModel
            ->select('member_profiles.*, auth_identities.secret as email, users.created_at as registered_at, provinces.name as province_name, universities.name as university_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->where('member_profiles.membership_status', 'pending');

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
                ->orLike('auth_identities.secret', $search)
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
        $isKoordinator = $user->inGroup('koordinator');

        // Check regional scope access
        if ($isKoordinator && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Get member with relations
        $member = $this->memberModel
            ->select('member_profiles.*, auth_identities.secret as email, users.active, users.created_at as registered_at, provinces.name as province_name, regencies.name as regency_name, universities.name as university_name, study_programs.name as study_program_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
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

        return view('admin/members/detail', $data);
    }

    /**
     * Edit member profile form
     * Display form to edit member information
     * 
     * @param int $id Member profile ID
     * @return string|ResponseInterface
     */
    public function edit(int $id)
    {
        // Check permission
        if (!auth()->user()->can('member.edit')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Check regional scope access
        if ($isKoordinator && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Get member with relations
        $member = $this->memberModel
            ->select('member_profiles.*, auth_identities.secret as email, users.active, users.created_at as registered_at')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->find($id);

        if (!$member) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan');
        }

        // Get master data for dropdowns
        $provinces = $this->provinceModel->findAll();
        $universities = $this->universityModel->findAll();

        // Get user roles for role management (Super Admin only)
        $roles = [];
        if ($user->inGroup('superadmin')) {
            $roleModel = model('RoleModel');
            $roles = $roleModel->findAll();

            // Debug: Log role data
            log_message('debug', 'Roles loaded: ' . count($roles));
            if (!empty($roles)) {
                log_message('debug', 'First role: ' . json_encode($roles[0]));
            }
        } else {
            // Debug: Log user groups
            log_message('debug', 'User groups: ' . json_encode($user->getGroups()));
        }

        $data = [
            'title' => 'Edit Anggota',
            'member' => $member,
            'provinces' => $provinces,
            'universities' => $universities,
            'roles' => $roles,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/members/edit', $data);
    }

    /**
     * Update member profile
     * Process member information update
     *
     * @param int $id Member profile ID
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.edit')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Check regional scope access
        if ($isKoordinator && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        try {
            // Get member with email from auth_identities
            $member = $this->memberModel
                ->select('member_profiles.*, auth_identities.secret as email')
                ->join('auth_identities', 'auth_identities.user_id = member_profiles.user_id AND auth_identities.type = "email_password"', 'left')
                ->find($id);

            if (!$member) {
                return redirect()->back()->with('error', 'Anggota tidak ditemukan');
            }

            // Validation rules
            $rules = [
                'full_name' => 'required|min_length[3]|max_length[255]',
                'phone' => 'permit_empty|max_length[20]',
                'whatsapp' => 'permit_empty|max_length[20]',
                'address' => 'permit_empty',
                'province_id' => 'permit_empty|integer',
                'regency_id' => 'permit_empty|integer',
                'university_id' => 'permit_empty|integer',
                'study_program_id' => 'permit_empty|integer',
                'nidn_nip' => 'permit_empty|max_length[50]',
                'gender' => 'permit_empty|in_list[L,P]',
                'birth_place' => 'permit_empty|max_length[100]',
                'birth_date' => 'permit_empty|valid_date',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Get validated data
            $updateData = [
                'full_name' => $this->request->getPost('full_name'),
                'phone' => $this->request->getPost('phone'),
                'whatsapp' => $this->request->getPost('whatsapp'),
                'address' => $this->request->getPost('address'),
                'province_id' => $this->request->getPost('province_id') ?: null,
                'regency_id' => $this->request->getPost('regency_id') ?: null,
                'university_id' => $this->request->getPost('university_id') ?: null,
                'study_program_id' => $this->request->getPost('study_program_id') ?: null,
                'nidn_nip' => $this->request->getPost('nidn_nip'),
                'gender' => $this->request->getPost('gender'),
                'birth_place' => $this->request->getPost('birth_place'),
                'birth_date' => $this->request->getPost('birth_date'),
            ];

            // Update member profile
            $this->memberModel->update($id, $updateData);

            // Update email if changed (Super Admin only)
            if ($user->inGroup('superadmin')) {
                $newEmail = $this->request->getPost('email');
                if ($newEmail && $newEmail !== $member->email) {
                    // Validate email uniqueness in auth_identities table
                    $emailRules = ['email' => 'required|valid_email|is_unique[auth_identities.secret,user_id,' . $member->user_id . ']'];

                    if ($this->validate($emailRules)) {
                        // Update email in auth_identities table
                        $db = \Config\Database::connect();
                        $db->table('auth_identities')
                            ->where('user_id', $member->user_id)
                            ->where('type', 'email_password')
                            ->update(['secret' => $newEmail]);
                    } else {
                        return redirect()->back()->withInput()->with('warning', 'Profil diupdate, tetapi email gagal diubah (sudah digunakan)');
                    }
                }

                // Update role if changed
                $newRole = $this->request->getPost('role');
                if ($newRole) {
                    $userEntity = $this->userModel->find($member->user_id);
                    $currentRoles = $userEntity->getGroups();

                    // Remove all current roles
                    foreach ($currentRoles as $role) {
                        $userEntity->removeGroup($role);
                    }

                    // Add new role
                    $userEntity->addGroup($newRole);
                }
            }

            // Log activity
            log_message('info', "Member {$member->full_name} (ID: {$id}) updated by user {$user->id}");

            return redirect()->to('/admin/members/show/' . $id)->with('success', 'Data anggota berhasil diperbarui');
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data anggota: ' . $e->getMessage());
        }
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
        if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Approve member using service
        $member = $this->memberModel->find($id);
        $result = $this->approveService->approve($member->user_id, $user->id);

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
        if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        // Get rejection reason from request
        $reason = $this->request->getPost('reason') ?? 'Tidak memenuhi persyaratan';

        // Reject member using service
        $member = $this->memberModel->find($id);
        $result = $this->approveService->reject($member->user_id, $user->id, $reason);

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
        if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
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
        if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
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
            if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
                $failCount++;
                continue;
            }

            $member = $this->memberModel->find($id);
            if ($member) {
                $result = $this->approveService->approve($member->user_id, $user->id);
            } else {
                $failCount++;
                continue;
            }

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
     * Bulk reject members
     * Reject multiple pending members at once
     *
     * @return ResponseInterface
     */
    public function bulkReject(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.approve')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menolak anggota');
        }

        $memberIds = $this->request->getPost('member_ids');

        if (empty($memberIds) || !is_array($memberIds)) {
            return redirect()->back()->with('error', 'Pilih minimal satu anggota untuk ditolak');
        }

        // Get rejection reason (optional)
        $reason = $this->request->getPost('reason') ?? 'Tidak memenuhi persyaratan';

        $user = auth()->user();
        $successCount = 0;
        $failCount = 0;

        foreach ($memberIds as $id) {
            // Check regional scope access
            if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
                $failCount++;
                continue;
            }

            $member = $this->memberModel->find($id);
            if ($member) {
                $result = $this->approveService->reject($member->user_id, $user->id, $reason);
            } else {
                $failCount++;
                continue;
            }

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = "{$successCount} anggota berhasil ditolak";
        if ($failCount > 0) {
            $message .= ", {$failCount} gagal";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk delete members
     * Delete multiple members at once (soft delete)
     *
     * @return ResponseInterface
     */
    public function bulkDelete(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.delete')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus anggota');
        }

        $memberIds = $this->request->getPost('member_ids');

        if (empty($memberIds) || !is_array($memberIds)) {
            return redirect()->back()->with('error', 'Pilih minimal satu anggota untuk dihapus');
        }

        $user = auth()->user();
        $successCount = 0;
        $failCount = 0;

        foreach ($memberIds as $id) {
            // Check regional scope access
            if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
                $failCount++;
                continue;
            }

            $member = $this->memberModel->find($id);
            if ($member) {
                try {
                    // Soft delete: Deactivate user account
                    $this->userModel->update($member->user_id, ['active' => 0]);

                    // Update member status to deleted
                    $this->memberModel->update($id, [
                        'membership_status' => 'deleted',
                        'deleted_at' => date('Y-m-d H:i:s'),
                        'deleted_by' => $user->id
                    ]);

                    // Log activity
                    log_message('info', "Member {$member->full_name} (ID: {$id}) deleted by user {$user->id}");

                    $successCount++;
                } catch (\Exception $e) {
                    log_message('error', "Failed to delete member ID {$id}: " . $e->getMessage());
                    $failCount++;
                }
            } else {
                $failCount++;
            }
        }

        $message = "{$successCount} anggota berhasil dihapus";
        if ($failCount > 0) {
            $message .= ", {$failCount} gagal";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete individual member
     * Soft delete member by ID
     *
     * @param int $id Member profile ID
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.delete')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus anggota');
        }

        $user = auth()->user();

        // Check regional scope access
        if ($user->inGroup('koordinator') && !$this->regionScope->canAccessMember($user->id, $id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke anggota ini');
        }

        try {
            $member = $this->memberModel->find($id);

            if (!$member) {
                return redirect()->back()->with('error', 'Anggota tidak ditemukan');
            }

            // Soft delete: Deactivate user account
            $this->userModel->update($member->user_id, ['active' => 0]);

            // Update member status to deleted
            $this->memberModel->update($id, [
                'membership_status' => 'deleted',
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $user->id
            ]);

            // Log activity
            log_message('info', "Member {$member->full_name} (ID: {$id}) deleted by user {$user->id}");

            return redirect()->to('/admin/members')->with('success', 'Anggota berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::delete: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }

    /**
     * Search members (AJAX endpoint)
     * Used for autocomplete and dynamic search
     *
     * @return ResponseInterface JSON response
     */
    public function search(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // Check permission
        if (!auth()->user()->can('member.view')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied'
            ])->setStatusCode(403);
        }

        $searchTerm = $this->request->getGet('q') ?? $this->request->getGet('search');

        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term must be at least 2 characters'
            ])->setStatusCode(400);
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        try {
            // Build query
            $builder = $this->memberModel
                ->select('member_profiles.id, member_profiles.full_name, member_profiles.member_number, member_profiles.phone, auth_identities.secret as email, member_profiles.membership_status, provinces.name as province_name, universities.name as university_name')
                ->join('users', 'users.id = member_profiles.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
                ->join('universities', 'universities.id = member_profiles.university_id', 'left')
                ->where('users.active', 1);

            // Apply regional scope for Koordinator Wilayah
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    $builder->where('member_profiles.province_id', $scopeResult['data']['province_id']);
                }
            }

            // Apply search filter
            $builder->groupStart()
                ->like('member_profiles.full_name', $searchTerm)
                ->orLike('auth_identities.secret', $searchTerm)
                ->orLike('member_profiles.member_number', $searchTerm)
                ->orLike('member_profiles.phone', $searchTerm)
                ->groupEnd();

            // Limit results for performance
            $members = $builder->limit(20)->findAll();

            // Format results for Select2 or autocomplete
            $results = [];
            foreach ($members as $member) {
                $results[] = [
                    'id' => $member->id,
                    'text' => $member->full_name . ' (' . ($member->email ?? '-') . ')',
                    'full_name' => $member->full_name,
                    'email' => $member->email,
                    'member_number' => $member->member_number,
                    'phone' => $member->phone,
                    'membership_status' => $member->membership_status,
                    'province_name' => $member->province_name,
                    'university_name' => $member->university_name
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::search: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get member statistics (AJAX endpoint)
     * Returns member counts by status for dashboard
     *
     * @return ResponseInterface JSON response
     */
    public function getStatistics(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // Check permission
        if (!auth()->user()->can('member.view')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied'
            ])->setStatusCode(403);
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        try {
            // Get regional scope if needed
            $provinceId = null;
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    $provinceId = $scopeResult['data']['province_id'];
                }
            }

            // Get statistics using service
            $stats = $this->statsService->getMemberStatistics($provinceId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in MemberController::getStatistics: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
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
        $isKoordinator = $user->inGroup('koordinator');

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
                ->select('member_profiles.*, auth_identities.secret as email, users.active, users.created_at as registered_at, provinces.name as province_name, universities.name as university_name')
                ->join('users', 'users.id = member_profiles.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
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
                    ->orLike('auth_identities.secret', $search)
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
        if ($user->inGroup('koordinator')) {
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
