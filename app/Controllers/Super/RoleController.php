<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\GroupModel;
use CodeIgniter\Shield\Models\PermissionModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * RoleController
 * 
 * Menangani CRUD roles (groups) dalam sistem RBAC
 * Super Admin dapat membuat, mengedit, menghapus roles
 * Serta mengatur permissions untuk setiap role
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RoleController extends BaseController
{
    /**
     * @var GroupModel
     */
    protected $groupModel;

    /**
     * @var PermissionModel
     */
    protected $permissionModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->groupModel = new GroupModel();
        $this->permissionModel = new PermissionModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display list of all roles
     * Shows role name, description, permission count, and member count
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // Simplified permission check for superadmin
        $user = auth()->user();
        $groups = $user->getGroups();

        // Superadmin bypass all checks
        if (!in_array('superadmin', $groups)) {
            // For non-superadmin, check permission properly
            if (!$user->can('role.view')) {
                return redirect()->to(base_url('/'))
                    ->with('error', 'Akses ditolak.');
            }
        }

        $db = \Config\Database::connect();

        // Get all roles with statistics
        $builder = $db->table('auth_groups')
            ->select('auth_groups.*, 
                      COUNT(DISTINCT auth_groups_permissions.permission_id) as permission_count,
                      COUNT(DISTINCT auth_groups_users.user_id) as member_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.group = auth_groups.title', 'left')
            ->groupBy('auth_groups.id')
            ->orderBy('auth_groups.title', 'ASC');

        $roles = $builder->get()->getResult();

        $data = [
            'title' => 'Manajemen Role',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Roles']
            ],
            'roles' => $roles
        ];

        return view('super/roles/index', $data);
    }

    /**
     * Show create role form
     * 
     * @return string|RedirectResponse
     */
    public function create()
    {
        // Get all permissions grouped by module
        $permissions = $this->getPermissionsGrouped();

        $data = [
            'title' => 'Tambah Role Baru',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Roles', 'url' => base_url('super/roles')],
                ['title' => 'Tambah Baru']
            ],
            'permissions' => $permissions
        ];

        return view('super/roles/form', $data);
    }

    /**
     * Store new role
     * 
     * @return RedirectResponse
     */
    public function store()
    {
        // Validation rules
        $rules = [
            'title' => 'required|min_length[3]|max_length[100]|is_unique[auth_groups.title]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insert role
            $roleData = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description')
            ];

            $db->table('auth_groups')->insert($roleData);
            $roleId = $db->insertID();

            // Assign permissions to role
            $permissions = $this->request->getPost('permissions') ?? [];
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $db->table('auth_groups_permissions')->insert([
                        'group_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Failed to create role');
            }

            // Log activity
            $this->logActivity('CREATE_ROLE', 'Created new role: ' . $roleData['title']);

            return redirect()->to(base_url('super/roles'))
                ->with('success', 'Role berhasil dibuat!');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Role Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat role: ' . $e->getMessage());
        }
    }

    /**
     * Show edit role form
     * 
     * @param int $id
     * @return string|RedirectResponse
     */
    public function edit($id)
    {
        $db = \Config\Database::connect();

        // Get role
        $role = $db->table('auth_groups')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$role) {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role tidak ditemukan.');
        }

        // Prevent editing superadmin role
        if ($role->title === 'superadmin') {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role Super Admin tidak dapat diubah.');
        }

        // Get role permissions
        $rolePermissions = $db->table('auth_groups_permissions')
            ->select('permission_id')
            ->where('group_id', $id)
            ->get()
            ->getResultArray();

        $rolePermissionIds = array_column($rolePermissions, 'permission_id');

        // Get all permissions grouped
        $permissions = $this->getPermissionsGrouped();

        // Get member count
        $memberCount = $db->table('auth_groups_users')
            ->where('group', $role->title)
            ->countAllResults();

        $data = [
            'title' => 'Edit Role',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Roles', 'url' => base_url('super/roles')],
                ['title' => 'Edit']
            ],
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissionIds' => $rolePermissionIds,
            'memberCount' => $memberCount
        ];

        return view('super/roles/form', $data);
    }

    /**
     * Update existing role
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function update($id)
    {
        $db = \Config\Database::connect();

        // Get role
        $role = $db->table('auth_groups')->where('id', $id)->get()->getRow();

        if (!$role) {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role tidak ditemukan.');
        }

        // Prevent editing superadmin role
        if ($role->title === 'superadmin') {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role Super Admin tidak dapat diubah.');
        }

        // Validation rules
        $rules = [
            'title' => "required|min_length[3]|max_length[100]|is_unique[auth_groups.title,id,{$id}]",
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db->transStart();

        try {
            // Update role
            $roleData = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description')
            ];

            $db->table('auth_groups')
                ->where('id', $id)
                ->update($roleData);

            // Update permissions
            // First, delete existing permissions
            $db->table('auth_groups_permissions')
                ->where('group_id', $id)
                ->delete();

            // Then, insert new permissions
            $permissions = $this->request->getPost('permissions') ?? [];
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $db->table('auth_groups_permissions')->insert([
                        'group_id' => $id,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Failed to update role');
            }

            // Log activity
            $this->logActivity('UPDATE_ROLE', 'Updated role: ' . $roleData['title']);

            return redirect()->to(base_url('super/roles'))
                ->with('success', 'Role berhasil diupdate!');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Role Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate role: ' . $e->getMessage());
        }
    }

    /**
     * Delete role
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $db = \Config\Database::connect();

        // Get role
        $role = $db->table('auth_groups')->where('id', $id)->get()->getRow();

        if (!$role) {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role tidak ditemukan.');
        }

        // Prevent deleting superadmin role
        if ($role->title === 'superadmin') {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        // Check if role is in use
        $memberCount = $db->table('auth_groups_users')
            ->where('group', $role->title)
            ->countAllResults();

        if ($memberCount > 0) {
            return redirect()->to(base_url('super/roles'))
                ->with('error', "Role tidak dapat dihapus karena masih digunakan oleh {$memberCount} user.");
        }

        $db->transStart();

        try {
            // Delete role permissions
            $db->table('auth_groups_permissions')
                ->where('group_id', $id)
                ->delete();

            // Delete role
            $db->table('auth_groups')
                ->where('id', $id)
                ->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Failed to delete role');
            }

            // Log activity
            $this->logActivity('DELETE_ROLE', 'Deleted role: ' . $role->title);

            return redirect()->to(base_url('super/roles'))
                ->with('success', 'Role berhasil dihapus!');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Role Deletion Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }

    /**
     * Show role members
     * 
     * @param int $id
     * @return string|RedirectResponse
     */
    public function members($id)
    {
        $db = \Config\Database::connect();

        // Get role
        $role = $db->table('auth_groups')->where('id', $id)->get()->getRow();

        if (!$role) {
            return redirect()->to(base_url('super/roles'))
                ->with('error', 'Role tidak ditemukan.');
        }

        // Get members
        $members = $db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email, users.active, users.created_at')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
            ->where('auth_groups_users.group', $role->title)
            ->where('auth_identities.type', 'email_password')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title' => 'Members - ' . $role->title,
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Roles', 'url' => base_url('super/roles')],
                ['title' => $role->title]
            ],
            'role' => $role,
            'members' => $members
        ];

        return view('super/roles/members', $data);
    }

    /**
     * Get permissions grouped by module
     * 
     * @return array
     */
    private function getPermissionsGrouped(): array
    {
        $db = \Config\Database::connect();

        $permissions = $db->table('auth_permissions')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();

        $grouped = [];

        foreach ($permissions as $permission) {
            // Extract module from permission name (e.g., 'members.view' -> 'members')
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'general';

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = $permission;
        }

        return $grouped;
    }

    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @return void
     */
    private function logActivity(string $action, string $description): void
    {
        $db = \Config\Database::connect();

        // Check if audit_logs table exists
        if (!$db->tableExists('audit_logs')) {
            return;
        }

        try {
            $db->table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Get role matrix (AJAX)
     * Returns role-permission matrix for visualization
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function matrix()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        $groups = auth()->user()->getGroups();
        if (!in_array('superadmin', $groups)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $db = \Config\Database::connect();

        // Get all roles
        $roles = $db->table('auth_groups')
            ->orderBy('title', 'ASC')
            ->get()
            ->getResult();

        // Get all permissions
        $permissions = $db->table('auth_permissions')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();

        // Get role-permission relationships
        $relationships = $db->table('auth_groups_permissions')
            ->get()
            ->getResult();

        // Build matrix
        $matrix = [];
        foreach ($relationships as $rel) {
            $key = $rel->group_id . '-' . $rel->permission_id;
            $matrix[$key] = true;
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'roles' => $roles,
                'permissions' => $permissions,
                'matrix' => $matrix
            ]
        ]);
    }
}
