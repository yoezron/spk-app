<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * User Management Controller
 * Handles CRUD operations for users by Super Admin
 */
class UserController extends BaseController
{
    protected $userModel;
    protected $groupModel;
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = model('UserModel');
        $this->groupModel = model('RoleModel');
    }

    /**
     * Display list of all users
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get filter parameters
        $role = $this->request->getGet('role');
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        // Build query
        $builder = $this->db->table('users')
            ->select('users.*, auth_groups_users.group, member_profiles.full_name, member_profiles.phone')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('member_profiles', 'users.id = member_profiles.user_id', 'left')
            ->orderBy('users.created_at', 'DESC');

        // Apply filters
        if ($role) {
            $builder->where('auth_groups_users.group', $role);
        }

        if ($status !== null && $status !== '') {
            $builder->where('users.active', (int)$status);
        }

        if ($search) {
            $builder->groupStart()
                ->like('users.username', $search)
                ->orLike('member_profiles.full_name', $search)
                ->orLike('users.id', $search)
                ->groupEnd();
        }

        $users = $builder->get()->getResult();

        // Get roles for filter
        $roles = $this->db->table('auth_groups')
            ->orderBy('title', 'ASC')
            ->get()
            ->getResult();

        // Get statistics
        $stats = [
            'total' => $this->db->table('users')->countAll(),
            'active' => $this->db->table('users')->where('active', 1)->countAllResults(),
            'inactive' => $this->db->table('users')->where('active', 0)->countAllResults(),
            'superadmin' => $this->db->table('auth_groups_users')->where('group', 'superadmin')->countAllResults(),
            'pengurus' => $this->db->table('auth_groups_users')->where('group', 'pengurus')->countAllResults(),
            'anggota' => $this->db->table('auth_groups_users')->where('group', 'anggota')->countAllResults(),
            'calon_anggota' => $this->db->table('auth_groups_users')->where('group', 'calon_anggota')->countAllResults(),
        ];

        $data = [
            'title' => 'User Management',
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
            'filters' => [
                'role' => $role,
                'status' => $status,
                'search' => $search
            ]
        ];

        return view('super/users/index', $data);
    }

    /**
     * Show user detail
     * 
     * @param int $id User ID
     * @return string|RedirectResponse
     */
    public function show(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Get user with profile
        // IMPORTANT: Select users.id explicitly to prevent it being overwritten by member_profiles.id
        // NOTE: Email is stored in auth_identities table in Shield, not in users table
        $user = $this->db->table('users')
            ->select('users.id, users.username, users.active, users.created_at, users.updated_at, users.deleted_at')
            ->select('auth_groups_users.group')
            ->select('auth_identities.secret as email')
            ->select('member_profiles.full_name, member_profiles.nik, member_profiles.gender, member_profiles.birth_place, member_profiles.birth_date')
            ->select('member_profiles.whatsapp, member_profiles.phone, member_profiles.photo_path, member_profiles.member_number, member_profiles.membership_status')
            ->select('provinces.name as province_name')
            ->select('universities.name as university_name')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_identities', 'users.id = auth_identities.user_id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'users.id = member_profiles.user_id', 'left')
            ->join('provinces', 'member_profiles.province_id = provinces.id', 'left')
            ->join('universities', 'member_profiles.university_id = universities.id', 'left')
            ->where('users.id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return redirect()->to('/super/users')
                ->with('error', 'User tidak ditemukan.');
        }

        // Get user permissions based on their role
        $permissions = [];
        if ($user->group) {
            // Get group ID first
            $groupId = $this->db->table('auth_groups')
                ->select('id')
                ->where('title', $user->group)
                ->get()
                ->getRow()
                ->id ?? null;

            if ($groupId) {
                $permissions = $this->db->table('auth_permissions')
                    ->select('auth_permissions.id, auth_permissions.name, auth_permissions.description')
                    ->join('auth_groups_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
                    ->where('auth_groups_permissions.group_id', $groupId)
                    ->orderBy('auth_permissions.name', 'ASC')
                    ->get()
                    ->getResult();
            }
        }

        // Get recent activity logs
        $activities = $this->db->table('audit_logs')
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResult();

        $data = [
            'title' => 'User Detail',
            'user' => $user,
            'permissions' => $permissions,
            'activities' => $activities
        ];

        return view('super/users/show', $data);
    }

    /**
     * Show edit user form
     *
     * @param int $id User ID
     * @return string|RedirectResponse
     */
    public function edit(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Get user with complete data
        $user = $this->db->table('users')
            ->select('users.id, users.username, users.active, users.created_at, users.updated_at')
            ->select('auth_groups_users.group')
            ->select('auth_identities.secret as email')
            ->select('member_profiles.full_name, member_profiles.nik, member_profiles.gender')
            ->select('member_profiles.birth_place, member_profiles.birth_date')
            ->select('member_profiles.phone, member_profiles.whatsapp')
            ->select('member_profiles.province_id, member_profiles.university_id')
            ->select('member_profiles.photo_path, member_profiles.member_number, member_profiles.membership_status')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_identities', 'users.id = auth_identities.user_id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'users.id = member_profiles.user_id', 'left')
            ->where('users.id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return redirect()->to('/super/users')
                ->with('error', 'User tidak ditemukan.');
        }

        // Get all roles
        $roles = $this->db->table('auth_groups')
            ->orderBy('title', 'ASC')
            ->get()
            ->getResult();

        // Get provinces for dropdown
        $provinces = $this->db->table('provinces')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();

        // Get universities for dropdown
        $universities = $this->db->table('universities')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'userGroup' => $user->group ?? null,
            'roles' => $roles,
            'provinces' => $provinces,
            'universities' => $universities,
            'validation' => \Config\Services::validation()
        ];

        return view('super/users/edit', $data);
    }

    /**
     * Update user
     *
     * @param int $id User ID
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Get current user email for unique validation
        $currentEmail = $this->db->table('auth_identities')
            ->select('secret as email')
            ->where('user_id', $id)
            ->where('type', 'email_password')
            ->get()
            ->getRow();

        // Validation rules
        $rules = [
            'username' => "required|min_length[3]|max_length[30]|alpha_dash|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[auth_identities.secret,user_id,{$id}]",
            'role' => 'required|in_list[superadmin,pengurus,koordinator,anggota,calon_anggota]',
            'full_name' => 'permit_empty|max_length[255]',
            'nik' => 'permit_empty|exact_length[16]|numeric',
            'gender' => 'permit_empty|in_list[L,P]',
            'birth_place' => 'permit_empty|max_length[100]',
            'birth_date' => 'permit_empty|valid_date',
            'phone' => 'permit_empty|max_length[20]',
            'whatsapp' => 'permit_empty|max_length[20]',
            'province_id' => 'permit_empty|integer',
            'university_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/super/users')
                ->with('error', 'User tidak ditemukan.');
        }

        // Prevent changing own role
        if ($id == auth()->id() && $this->request->getPost('role') !== $this->db->table('auth_groups_users')->where('user_id', $id)->get()->getRow()->group) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda tidak dapat mengubah role Anda sendiri.');
        }

        try {
            $this->db->transStart();

            // Capture old values for audit log
            $oldData = $this->db->table('users')
                ->select('users.username, auth_identities.secret as email, auth_groups_users.group as role')
                ->select('member_profiles.full_name, member_profiles.nik, member_profiles.gender')
                ->select('member_profiles.phone, member_profiles.whatsapp, member_profiles.province_id, member_profiles.university_id')
                ->join('auth_identities', 'users.id = auth_identities.user_id AND auth_identities.type = "email_password"', 'left')
                ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
                ->join('member_profiles', 'users.id = member_profiles.user_id', 'left')
                ->where('users.id', $id)
                ->get()
                ->getRow();

            // Update username
            $this->userModel->update($id, [
                'username' => $this->request->getPost('username')
            ]);

            // Update email in auth_identities
            $newEmail = $this->request->getPost('email');
            if ($currentEmail && $currentEmail->email !== $newEmail) {
                $this->db->table('auth_identities')
                    ->where('user_id', $id)
                    ->where('type', 'email_password')
                    ->update(['secret' => $newEmail]);
            }

            // Update role
            $newRole = $this->request->getPost('role');
            $currentRole = $this->db->table('auth_groups_users')
                ->where('user_id', $id)
                ->get()
                ->getRow();

            if (!$currentRole || $currentRole->group !== $newRole) {
                // Remove old role
                $this->db->table('auth_groups_users')
                    ->where('user_id', $id)
                    ->delete();

                // Add new role
                $this->db->table('auth_groups_users')->insert([
                    'user_id' => $id,
                    'group' => $newRole
                ]);
            }

            // Update or insert member profile
            $memberProfile = $this->db->table('member_profiles')
                ->where('user_id', $id)
                ->get()
                ->getRow();

            $profileData = [
                'full_name' => $this->request->getPost('full_name'),
                'nik' => $this->request->getPost('nik'),
                'gender' => $this->request->getPost('gender'),
                'birth_place' => $this->request->getPost('birth_place'),
                'birth_date' => $this->request->getPost('birth_date'),
                'phone' => $this->request->getPost('phone'),
                'whatsapp' => $this->request->getPost('whatsapp'),
                'province_id' => $this->request->getPost('province_id') ?: null,
                'university_id' => $this->request->getPost('university_id') ?: null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($memberProfile) {
                // Update existing profile
                $this->db->table('member_profiles')
                    ->where('user_id', $id)
                    ->update($profileData);
            } else {
                // Create new profile
                $profileData['user_id'] = $id;
                $profileData['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('member_profiles')->insert($profileData);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Log activity (simple log without audit trail for now)
            log_message('info', "User {$id} updated by Super Admin " . auth()->id());

            // TODO: Fix audit logging
            // Currently disabled due to SQL syntax error
            // Will be fixed in next update

            return redirect()->to('/super/users/' . $id)
                ->with('success', 'User berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating user: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status
     * 
     * @param int $id User ID
     * @return RedirectResponse
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Prevent deactivating own account
        if ($id == auth()->id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/super/users')
                ->with('error', 'User tidak ditemukan.');
        }

        try {
            $newStatus = $user->active ? 0 : 1;

            $this->userModel->update($id, [
                'active' => $newStatus
            ]);

            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

            // Log activity
            log_message('info', "User {$id} {$statusText} by Super Admin " . auth()->id());

            return redirect()->back()
                ->with('success', "User berhasil {$statusText}.");
        } catch (\Exception $e) {
            log_message('error', 'Error toggling user status: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal mengubah status user.');
        }
    }

    /**
     * Force reset user password
     * Send password reset email to user
     * 
     * @param int $id User ID
     * @return RedirectResponse
     */
    public function forceResetPassword(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/super/users')
                ->with('error', 'User tidak ditemukan.');
        }

        try {
            // Get user email from auth_identities
            $emailIdentity = $this->db->table('auth_identities')
                ->select('secret as email')
                ->where('user_id', $id)
                ->where('type', 'email_password')
                ->get()
                ->getRow();

            if (!$emailIdentity || !$emailIdentity->email) {
                return redirect()->back()
                    ->with('error', 'Email user tidak ditemukan.');
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Delete old tokens for this email
            $this->db->table('auth_identity_passwords')
                ->where('identity_id', function($builder) use ($id) {
                    return $builder->select('id')
                        ->from('auth_identities')
                        ->where('user_id', $id)
                        ->where('type', 'email_password');
                })
                ->delete();

            // Save token using Shield's auth_identity_passwords table
            $identityId = $this->db->table('auth_identities')
                ->select('id')
                ->where('user_id', $id)
                ->where('type', 'email_password')
                ->get()
                ->getRow()
                ->id ?? null;

            if ($identityId) {
                $this->db->table('auth_identity_passwords')->insert([
                    'identity_id' => $identityId,
                    'hash_type' => 'reset',
                    'hash' => hash('sha256', $token),
                    'expires_at' => $expires,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Generate reset link
            $resetLink = base_url("auth/reset-password?token={$token}");

            // TODO: Send email with reset link
            // For now, just show the link in the success message (for testing)
            // In production, implement email service:
            // $this->emailService->sendPasswordResetEmail($emailIdentity->email, $resetLink);

            log_message('info', "Password reset forced for user {$id} (email: {$emailIdentity->email}) by Super Admin " . auth()->id());

            return redirect()->back()
                ->with('success', "Link reset password telah digenerate untuk {$emailIdentity->email}. (Email service belum dikonfigurasi)");
        } catch (\Exception $e) {
            log_message('error', 'Error forcing password reset: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal mengirim link reset password: ' . $e->getMessage());
        }
    }

    /**
     * Get permissions by role (AJAX endpoint)
     *
     * @param string $role Role name
     * @return ResponseInterface
     */
    public function getPermissionsByRole(string $role = null): ResponseInterface
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akses ditolak.'
            ])->setStatusCode(403);
        }

        if (!$role) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Role tidak valid.'
            ])->setStatusCode(400);
        }

        try {
            // Get group ID first
            $group = $this->db->table('auth_groups')
                ->select('id')
                ->where('title', $role)
                ->get()
                ->getRow();

            if (!$group) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Role tidak ditemukan.'
                ])->setStatusCode(404);
            }

            // Get permissions for this role
            $permissions = $this->db->table('auth_permissions')
                ->select('auth_permissions.id, auth_permissions.name, auth_permissions.description')
                ->join('auth_groups_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
                ->where('auth_groups_permissions.group_id', $group->id)
                ->orderBy('auth_permissions.name', 'ASC')
                ->get()
                ->getResult();

            return $this->response->setJSON([
                'success' => true,
                'role' => $role,
                'permissions' => $permissions,
                'count' => count($permissions)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete user account
     * 
     * @param int $id User ID
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Prevent deleting own account
        if ($id == auth()->id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/super/users')
                ->with('error', 'User tidak ditemukan.');
        }

        try {
            $this->db->transStart();

            // Delete related data
            $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
            $this->db->table('member_profiles')->where('user_id', $id)->delete();

            // Delete user
            $this->userModel->delete($id);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            log_message('info', "User {$id} deleted by Super Admin " . auth()->id());

            return redirect()->to('/super/users')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting user: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}
