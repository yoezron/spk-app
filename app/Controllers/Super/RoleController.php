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
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all roles with permission count and member count
        $roles = $this->groupModel
            ->select('auth_groups.*, 
                      COUNT(DISTINCT auth_groups_permissions.permission_id) as permission_count,
                      COUNT(DISTINCT auth_groups_users.user_id) as member_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.group = auth_groups.title', 'left')
            ->groupBy('auth_groups.id')
            ->orderBy('auth_groups.title', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Manajemen Role',
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
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $data = [
            'title' => 'Tambah Role Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('super/roles/create', $data);
    }

    /**
     * Store new role to database
     * 
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        // Validation rules
        $rules = [
            'title' => [
                'rules' => 'required|min_length[3]|max_length[100]|is_unique[auth_groups.title]|alpha_dash',
                'errors' => [
                    'required' => 'Nama role wajib diisi',
                    'min_length' => 'Nama role minimal 3 karakter',
                    'max_length' => 'Nama role maksimal 100 karakter',
                    'is_unique' => 'Nama role sudah digunakan',
                    'alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, underscore, dan dash'
                ]
            ],
            'description' => [
                'rules' => 'required|min_length[10]|max_length[255]',
                'errors' => [
                    'required' => 'Deskripsi role wajib diisi',
                    'min_length' => 'Deskripsi minimal 10 karakter',
                    'max_length' => 'Deskripsi maksimal 255 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => strtolower($this->request->getPost('title')),
            'description' => $this->request->getPost('description')
        ];

        try {
            $this->groupModel->insert($data);

            return redirect()->to('/super/roles')
                ->with('success', 'Role berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating role: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan role. Silakan coba lagi.');
        }
    }

    /**
     * Show edit role form
     * 
     * @param int $id Role ID
     * @return string|RedirectResponse
     */
    public function edit(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $role = $this->groupModel->find($id);

        if (!$role) {
            return redirect()->to('/super/roles')
                ->with('error', 'Role tidak ditemukan.');
        }

        // Prevent editing superadmin role
        if ($role->title === 'superadmin') {
            return redirect()->to('/super/roles')
                ->with('error', 'Role Super Admin tidak dapat diedit.');
        }

        $data = [
            'title' => 'Edit Role',
            'role' => $role,
            'validation' => \Config\Services::validation()
        ];

        return view('super/roles/edit', $data);
    }

    /**
     * Update role in database
     * 
     * @param int $id Role ID
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $role = $this->groupModel->find($id);

        if (!$role) {
            return redirect()->to('/super/roles')
                ->with('error', 'Role tidak ditemukan.');
        }

        // Prevent editing superadmin role
        if ($role->title === 'superadmin') {
            return redirect()->to('/super/roles')
                ->with('error', 'Role Super Admin tidak dapat diedit.');
        }

        // Validation rules
        $rules = [
            'title' => [
                'rules' => "required|min_length[3]|max_length[100]|is_unique[auth_groups.title,id,{$id}]|alpha_dash",
                'errors' => [
                    'required' => 'Nama role wajib diisi',
                    'min_length' => 'Nama role minimal 3 karakter',
                    'max_length' => 'Nama role maksimal 100 karakter',
                    'is_unique' => 'Nama role sudah digunakan',
                    'alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, underscore, dan dash'
                ]
            ],
            'description' => [
                'rules' => 'required|min_length[10]|max_length[255]',
                'errors' => [
                    'required' => 'Deskripsi role wajib diisi',
                    'min_length' => 'Deskripsi minimal 10 karakter',
                    'max_length' => 'Deskripsi maksimal 255 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => strtolower($this->request->getPost('title')),
            'description' => $this->request->getPost('description')
        ];

        try {
            $this->groupModel->update($id, $data);

            return redirect()->to('/super/roles')
                ->with('success', 'Role berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating role: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate role. Silakan coba lagi.');
        }
    }

    /**
     * Delete role from database
     * Validates that role is not assigned to any users
     * 
     * @param int $id Role ID
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $role = $this->groupModel->find($id);

        if (!$role) {
            return redirect()->to('/super/roles')
                ->with('error', 'Role tidak ditemukan.');
        }

        // Prevent deleting superadmin role
        if ($role->title === 'superadmin') {
            return redirect()->to('/super/roles')
                ->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        // Check if role is assigned to any users
        $userCount = $this->groupModel->db->table('auth_groups_users')
            ->where('group', $role->title)
            ->countAllResults();

        if ($userCount > 0) {
            return redirect()->to('/super/roles')
                ->with('error', "Role tidak dapat dihapus karena masih digunakan oleh {$userCount} user. Hapus atau pindahkan user tersebut terlebih dahulu.");
        }

        try {
            // Delete role permissions first
            $this->groupModel->db->table('auth_groups_permissions')
                ->where('group_id', $id)
                ->delete();

            // Delete role
            $this->groupModel->delete($id);

            return redirect()->to('/super/roles')
                ->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting role: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus role. Silakan coba lagi.');
        }
    }

    /**
     * Show role permissions management page
     * Display current permissions and available permissions
     * 
     * @param int $id Role ID
     * @return string|RedirectResponse
     */
    public function permissions(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $role = $this->groupModel->find($id);

        if (!$role) {
            return redirect()->to('/super/roles')
                ->with('error', 'Role tidak ditemukan.');
        }

        // Get current permissions for this role
        $currentPermissions = $this->groupModel->db->table('auth_groups_permissions')
            ->select('auth_permissions.id, auth_permissions.name, auth_permissions.description')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
            ->where('auth_groups_permissions.group_id', $id)
            ->get()
            ->getResult();

        $currentPermissionIds = array_column($currentPermissions, 'id');

        // Get all permissions grouped by module
        $allPermissions = $this->permissionModel
            ->orderBy('name', 'ASC')
            ->findAll();

        // Group permissions by module (extract module from permission name)
        $groupedPermissions = [];
        foreach ($allPermissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';

            if (!isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = [];
            }

            $permission->is_assigned = in_array($permission->id, $currentPermissionIds);
            $groupedPermissions[$module][] = $permission;
        }

        // Sort modules alphabetically
        ksort($groupedPermissions);

        $data = [
            'title' => 'Kelola Permissions - ' . ucfirst($role->title),
            'role' => $role,
            'currentPermissions' => $currentPermissions,
            'groupedPermissions' => $groupedPermissions
        ];

        return view('super/roles/permissions', $data);
    }

    /**
     * Assign or remove permissions to/from role
     * 
     * @param int $id Role ID
     * @return RedirectResponse
     */
    public function assignPermissions(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $role = $this->groupModel->find($id);

        if (!$role) {
            return redirect()->to('/super/roles')
                ->with('error', 'Role tidak ditemukan.');
        }

        // Get selected permissions from POST
        $selectedPermissions = $this->request->getPost('permissions') ?? [];

        try {
            // Start transaction
            $this->groupModel->db->transStart();

            // Delete all current permissions for this role
            $this->groupModel->db->table('auth_groups_permissions')
                ->where('group_id', $id)
                ->delete();

            // Insert selected permissions
            if (!empty($selectedPermissions)) {
                $insertData = [];
                foreach ($selectedPermissions as $permissionId) {
                    $insertData[] = [
                        'group_id' => $id,
                        'permission_id' => $permissionId
                    ];
                }

                $this->groupModel->db->table('auth_groups_permissions')
                    ->insertBatch($insertData);
            }

            // Complete transaction
            $this->groupModel->db->transComplete();

            if ($this->groupModel->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            $permissionCount = count($selectedPermissions);
            return redirect()->to("/super/roles/permissions/{$id}")
                ->with('success', "{$permissionCount} permission berhasil di-assign ke role {$role->title}.");
        } catch (\Exception $e) {
            log_message('error', 'Error assigning permissions: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal meng-assign permissions. Silakan coba lagi.');
        }
    }

    /**
     * Show members who have this role
     * 
     * @param int $id Role ID
     * @return string|RedirectResponse
     */
    public function members(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $role = $this->groupModel->find($id);

        if (!$role) {
            return redirect()->to('/super/roles')
                ->with('error', 'Role tidak ditemukan.');
        }

        // Get users with this role
        $members = $this->userModel
            ->select('users.id, users.username, users.email, users.active, 
                      member_profiles.full_name, member_profiles.no_wa,
                      auth_groups_users.created_at as assigned_at')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->where('auth_groups_users.group', $role->title)
            ->orderBy('auth_groups_users.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Anggota Role - ' . ucfirst($role->title),
            'role' => $role,
            'members' => $members,
            'member_count' => count($members)
        ];

        return view('super/roles/members', $data);
    }
}
