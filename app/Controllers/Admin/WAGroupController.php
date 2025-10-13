<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\WhatsAppService;
use App\Services\RegionScopeService;
use App\Services\NotificationService;
use App\Models\WAGroupModel;
use App\Models\WAGroupMemberModel;
use App\Models\ProvinceModel;
use App\Models\MemberProfileModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * WAGroupController (Admin)
 * 
 * Mengelola grup WhatsApp per provinsi/wilayah
 * Support regional scope untuk Koordinator Wilayah
 * Track member participation, manage invite links
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class WAGroupController extends BaseController
{
    /**
     * @var WhatsAppService
     */
    protected $waService;

    /**
     * @var RegionScopeService
     */
    protected $regionScope;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var WAGroupModel
     */
    protected $groupModel;

    /**
     * @var WAGroupMemberModel
     */
    protected $groupMemberModel;

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->waService = new WhatsAppService();
        $this->regionScope = new RegionScopeService();
        $this->notificationService = new NotificationService();
        $this->groupModel = new WAGroupModel();
        $this->groupMemberModel = new WAGroupMemberModel();
        $this->provinceModel = new ProvinceModel();
        $this->memberModel = new MemberProfileModel();
    }

    /**
     * Display list of WhatsApp groups per province
     * Koordinator Wilayah only see groups in their province
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengelola grup WhatsApp');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get filters from request
        $filters = [
            'province_id' => $this->request->getGet('province_id'),
            'status' => $this->request->getGet('status'),
            'search' => $this->request->getGet('search')
        ];

        // Build query
        $builder = $this->groupModel
            ->select('wa_groups.*, provinces.name as province_name, COUNT(DISTINCT wa_group_members.member_id) as member_count')
            ->join('provinces', 'provinces.id = wa_groups.province_id')
            ->join('wa_group_members', 'wa_group_members.group_id = wa_groups.id', 'left')
            ->groupBy('wa_groups.id');

        // CRITICAL: Apply regional scope for Koordinator Wilayah
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $builder->where('wa_groups.province_id', $scopeResult['data']['province_id']);
                $filters['province_id'] = $scopeResult['data']['province_id'];
            }
        }

        // Apply filters
        if (!empty($filters['province_id']) && !$isKoordinator) {
            $builder->where('wa_groups.province_id', $filters['province_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('wa_groups.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('wa_groups.name', $search)
                ->orLike('wa_groups.description', $search)
                ->groupEnd();
        }

        // Get paginated results
        $groups = $builder
            ->orderBy('provinces.name', 'ASC')
            ->orderBy('wa_groups.created_at', 'DESC')
            ->paginate(20);

        // Get provinces for filter (only if not koordinator)
        $provinces = [];
        if (!$isKoordinator) {
            $provinces = $this->provinceModel->findAll();
        }

        $data = [
            'title' => 'Kelola Grup WhatsApp',
            'groups' => $groups,
            'pager' => $this->groupModel->pager,
            'filters' => $filters,
            'provinces' => $provinces,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/wagroups/index', $data);
    }

    /**
     * Show create group form
     * Display form to create new WhatsApp group
     * 
     * @return string|ResponseInterface
     */
    public function create()
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat grup WhatsApp');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get provinces
        $provinces = [];
        $selectedProvince = null;

        if ($isKoordinator) {
            // Koordinator can only create group for their province
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $selectedProvince = $scopeResult['data']['province_id'];
                $provinces = [$this->provinceModel->find($selectedProvince)];
            }
        } else {
            $provinces = $this->provinceModel->findAll();
        }

        $data = [
            'title' => 'Buat Grup WhatsApp',
            'provinces' => $provinces,
            'selected_province' => $selectedProvince,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/wagroups/create', $data);
    }

    /**
     * Store new WhatsApp group
     * Save new group with invite link
     * 
     * @return ResponseInterface
     */
    public function store(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat grup WhatsApp');
        }

        // Validate input
        $rules = [
            'province_id' => 'required|numeric',
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[500]',
            'invite_link' => 'required|valid_url',
            'status' => 'required|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        try {
            $provinceId = $this->request->getPost('province_id');

            // Check regional scope for Koordinator
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    if ($provinceId != $scopeResult['data']['province_id']) {
                        return redirect()->back()->withInput()->with('error', 'Anda hanya dapat membuat grup untuk provinsi Anda');
                    }
                }
            }

            // Check if group already exists for this province
            $existingGroup = $this->groupModel
                ->where('province_id', $provinceId)
                ->where('status', 'active')
                ->first();

            if ($existingGroup) {
                return redirect()->back()->withInput()->with('error', 'Grup WhatsApp aktif untuk provinsi ini sudah ada');
            }

            $groupData = [
                'province_id' => $provinceId,
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'invite_link' => $this->request->getPost('invite_link'),
                'status' => $this->request->getPost('status'),
                'created_by' => $user->id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->groupModel->insert($groupData);

            // Send notification to members in this province
            if ($groupData['status'] === 'active') {
                $this->notificationService->sendWAGroupCreatedNotification($provinceId, $groupData['invite_link']);
            }

            // Log activity
            log_message('info', "WA Group created for province {$provinceId} by user {$user->id}");

            return redirect()->to('/admin/wagroups')->with('success', 'Grup WhatsApp berhasil dibuat');
        } catch (\Exception $e) {
            log_message('error', 'Error in WAGroupController::store: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal membuat grup WhatsApp: ' . $e->getMessage());
        }
    }

    /**
     * Show edit group form
     * Display form to edit existing group
     * 
     * @param int $id Group ID
     * @return string|ResponseInterface
     */
    public function edit(int $id)
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit grup WhatsApp');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get group
        $group = $this->groupModel
            ->select('wa_groups.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = wa_groups.province_id')
            ->find($id);

        if (!$group) {
            return redirect()->back()->with('error', 'Grup WhatsApp tidak ditemukan');
        }

        // Check regional scope access
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                if ($group->province_id != $scopeResult['data']['province_id']) {
                    return redirect()->back()->with('error', 'Anda tidak memiliki akses ke grup ini');
                }
            }
        }

        $data = [
            'title' => 'Edit Grup WhatsApp',
            'group' => $group,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/wagroups/edit', $data);
    }

    /**
     * Update WhatsApp group
     * Update group information and invite link
     * 
     * @param int $id Group ID
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit grup WhatsApp');
        }

        // Get existing group
        $existingGroup = $this->groupModel->find($id);

        if (!$existingGroup) {
            return redirect()->back()->with('error', 'Grup WhatsApp tidak ditemukan');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Check regional scope access
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                if ($existingGroup->province_id != $scopeResult['data']['province_id']) {
                    return redirect()->back()->with('error', 'Anda tidak memiliki akses ke grup ini');
                }
            }
        }

        // Validate input
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[500]',
            'invite_link' => 'required|valid_url',
            'status' => 'required|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $groupData = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'invite_link' => $this->request->getPost('invite_link'),
                'status' => $this->request->getPost('status'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->groupModel->update($id, $groupData);

            // Send notification if invite link changed and status is active
            if ($existingGroup->invite_link !== $groupData['invite_link'] && $groupData['status'] === 'active') {
                $this->notificationService->sendWAGroupUpdatedNotification(
                    $existingGroup->province_id,
                    $groupData['invite_link']
                );
            }

            // Log activity
            log_message('info', "WA Group ID {$id} updated by user {$user->id}");

            return redirect()->to('/admin/wagroups')->with('success', 'Grup WhatsApp berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in WAGroupController::update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate grup WhatsApp: ' . $e->getMessage());
        }
    }

    /**
     * Delete WhatsApp group
     * Remove group and all member associations
     * 
     * @param int $id Group ID
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus grup WhatsApp');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        try {
            $group = $this->groupModel->find($id);

            if (!$group) {
                return redirect()->back()->with('error', 'Grup WhatsApp tidak ditemukan');
            }

            // Check regional scope access
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    if ($group->province_id != $scopeResult['data']['province_id']) {
                        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke grup ini');
                    }
                }
            }

            // Delete all group member associations
            $this->groupMemberModel->where('group_id', $id)->delete();

            // Delete group
            $this->groupModel->delete($id);

            // Log activity
            log_message('info', "WA Group ID {$id} deleted by user {$user->id}");

            return redirect()->to('/admin/wagroups')->with('success', 'Grup WhatsApp berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in WAGroupController::delete: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus grup WhatsApp: ' . $e->getMessage());
        }
    }

    /**
     * View group members
     * Display list of members in specific group
     * 
     * @param int $id Group ID
     * @return string|ResponseInterface
     */
    public function members(int $id)
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat anggota grup');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        // Get group
        $group = $this->groupModel
            ->select('wa_groups.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = wa_groups.province_id')
            ->find($id);

        if (!$group) {
            return redirect()->back()->with('error', 'Grup WhatsApp tidak ditemukan');
        }

        // Check regional scope access
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                if ($group->province_id != $scopeResult['data']['province_id']) {
                    return redirect()->back()->with('error', 'Anda tidak memiliki akses ke grup ini');
                }
            }
        }

        // Get group members
        $members = $this->groupMemberModel
            ->select('wa_group_members.*, member_profiles.full_name, member_profiles.phone, users.email')
            ->join('member_profiles', 'member_profiles.id = wa_group_members.member_id')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('wa_group_members.group_id', $id)
            ->orderBy('wa_group_members.joined_at', 'DESC')
            ->paginate(20);

        // Get members in province who haven't joined
        $notJoinedMembers = $this->memberModel
            ->select('member_profiles.*, users.email')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('member_profiles.province_id', $group->province_id)
            ->where('users.active', 1)
            ->whereNotIn('member_profiles.id', function ($builder) use ($id) {
                return $builder->select('member_id')
                    ->from('wa_group_members')
                    ->where('group_id', $id);
            })
            ->limit(10)
            ->findAll();

        $data = [
            'title' => 'Anggota Grup - ' . $group->name,
            'group' => $group,
            'members' => $members,
            'not_joined_members' => $notJoinedMembers,
            'pager' => $this->groupMemberModel->pager,
            'is_koordinator' => $isKoordinator
        ];

        return view('admin/wagroups/members', $data);
    }

    /**
     * Confirm member joined group
     * Mark member as confirmed in group
     * 
     * @param int $groupId Group ID
     * @param int $memberId Member profile ID
     * @return ResponseInterface
     */
    public function confirmJoin(int $groupId, int $memberId): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengkonfirmasi anggota grup');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        try {
            $group = $this->groupModel->find($groupId);

            if (!$group) {
                return redirect()->back()->with('error', 'Grup WhatsApp tidak ditemukan');
            }

            // Check regional scope access
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    if ($group->province_id != $scopeResult['data']['province_id']) {
                        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke grup ini');
                    }
                }
            }

            // Check if member already in group
            $existingMember = $this->groupMemberModel
                ->where('group_id', $groupId)
                ->where('member_id', $memberId)
                ->first();

            if ($existingMember) {
                return redirect()->back()->with('info', 'Anggota sudah terdaftar di grup');
            }

            // Add member to group
            $this->groupMemberModel->insert([
                'group_id' => $groupId,
                'member_id' => $memberId,
                'joined_at' => date('Y-m-d H:i:s'),
                'confirmed_by' => $user->id
            ]);

            // Update member count in group
            $memberCount = $this->groupMemberModel->where('group_id', $groupId)->countAllResults();
            $this->groupModel->update($groupId, ['member_count' => $memberCount]);

            // Log activity
            log_message('info', "Member {$memberId} confirmed joined WA Group {$groupId} by user {$user->id}");

            return redirect()->back()->with('success', 'Anggota berhasil dikonfirmasi bergabung ke grup');
        } catch (\Exception $e) {
            log_message('error', 'Error in WAGroupController::confirmJoin: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengkonfirmasi anggota: ' . $e->getMessage());
        }
    }

    /**
     * Remove member from group
     * Remove member association from group
     * 
     * @param int $groupId Group ID
     * @param int $memberId Member profile ID
     * @return ResponseInterface
     */
    public function removeMember(int $groupId, int $memberId): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('wagroup.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus anggota dari grup');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        try {
            $group = $this->groupModel->find($groupId);

            if (!$group) {
                return redirect()->back()->with('error', 'Grup WhatsApp tidak ditemukan');
            }

            // Check regional scope access
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    if ($group->province_id != $scopeResult['data']['province_id']) {
                        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke grup ini');
                    }
                }
            }

            // Remove member from group
            $this->groupMemberModel
                ->where('group_id', $groupId)
                ->where('member_id', $memberId)
                ->delete();

            // Update member count
            $memberCount = $this->groupMemberModel->where('group_id', $groupId)->countAllResults();
            $this->groupModel->update($groupId, ['member_count' => $memberCount]);

            // Log activity
            log_message('info', "Member {$memberId} removed from WA Group {$groupId} by user {$user->id}");

            return redirect()->back()->with('success', 'Anggota berhasil dihapus dari grup');
        } catch (\Exception $e) {
            log_message('error', 'Error in WAGroupController::removeMember: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus anggota dari grup: ' . $e->getMessage());
        }
    }

    /**
     * Get group statistics (AJAX endpoint)
     * Returns statistics for dashboard widgets
     * 
     * @return ResponseInterface JSON response
     */
    public function getStats(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator_wilayah');

        try {
            $builder = $this->groupModel->builder();

            // Apply regional scope
            if ($isKoordinator) {
                $scopeResult = $this->regionScope->getScopeData($user->id);
                if ($scopeResult['success']) {
                    $builder->where('province_id', $scopeResult['data']['province_id']);
                }
            }

            $stats = [
                'total_groups' => (clone $builder)->countAllResults(),
                'active_groups' => (clone $builder)->where('status', 'active')->countAllResults(),
                'inactive_groups' => (clone $builder)->where('status', 'inactive')->countAllResults(),
                'total_members' => $this->groupMemberModel->countAllResults()
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in WAGroupController::getStats: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
