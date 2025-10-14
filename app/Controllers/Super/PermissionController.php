<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\GroupModel;
use CodeIgniter\Shield\Models\PermissionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * PermissionController
 * 
 * Menangani CRUD permissions dalam sistem RBAC
 * Super Admin dapat membuat, mengedit, menghapus permissions
 * Serta melihat roles yang memiliki permission tertentu
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PermissionController extends BaseController
{
    /**
     * @var PermissionModel
     */
    protected $permissionModel;

    /**
     * @var GroupModel
     */
    protected $groupModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->groupModel = new GroupModel();
    }

    /**
     * Display list of all permissions
     * Shows permissions grouped by module with role count
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all permissions with role count
        $permissions = $this->permissionModel
            ->select('auth_permissions.*, 
                      COUNT(DISTINCT auth_groups_permissions.group_id) as role_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.permission_id = auth_permissions.id', 'left')
            ->groupBy('auth_permissions.id')
            ->orderBy('auth_permissions.name', 'ASC')
            ->findAll();

        // Group permissions by module
        $groupedPermissions = [];
        $totalPermissions = 0;

        foreach ($permissions as $permission) {
            // Extract module from permission name (e.g., 'member.view' -> 'member')
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';

            if (!isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = [
                    'name' => ucfirst($module),
                    'permissions' => [],
                    'count' => 0
                ];
            }

            $groupedPermissions[$module]['permissions'][] = $permission;
            $groupedPermissions[$module]['count']++;
            $totalPermissions++;
        }

        // Sort modules alphabetically
        ksort($groupedPermissions);

        $data = [
            'title' => 'Manajemen Permission',
            'groupedPermissions' => $groupedPermissions,
            'totalPermissions' => $totalPermissions,
            'moduleCount' => count($groupedPermissions)
        ];

        return view('super/permissions/index', $data);
    }

    /**
     * Show create permission form
     * 
     * @return string|RedirectResponse
     */
    public function create()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get existing modules for dropdown
        $existingModules = $this->getExistingModules();

        $data = [
            'title' => 'Tambah Permission Baru',
            'existingModules' => $existingModules,
            'validation' => \Config\Services::validation()
        ];

        return view('super/permissions/create', $data);
    }

    /**
     * Store new permission to database
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
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]|is_unique[auth_permissions.name]|regex_match[/^[a-z]+\.[a-z_]+$/]',
                'errors' => [
                    'required' => 'Nama permission wajib diisi',
                    'min_length' => 'Nama permission minimal 3 karakter',
                    'max_length' => 'Nama permission maksimal 100 karakter',
                    'is_unique' => 'Nama permission sudah digunakan',
                    'regex_match' => 'Format permission harus: module.action (contoh: member.view, forum.manage)'
                ]
            ],
            'description' => [
                'rules' => 'required|min_length[10]|max_length[255]',
                'errors' => [
                    'required' => 'Deskripsi permission wajib diisi',
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
            'name' => strtolower($this->request->getPost('name')),
            'description' => $this->request->getPost('description')
        ];

        try {
            $this->permissionModel->insert($data);

            return redirect()->to('/super/permissions')
                ->with('success', 'Permission berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating permission: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan permission. Silakan coba lagi.');
        }
    }

    /**
     * Show edit permission form
     * 
     * @param int $id Permission ID
     * @return string|RedirectResponse
     */
    public function edit(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $permission = $this->permissionModel->find($id);

        if (!$permission) {
            return redirect()->to('/super/permissions')
                ->with('error', 'Permission tidak ditemukan.');
        }

        // Get existing modules for reference
        $existingModules = $this->getExistingModules();

        $data = [
            'title' => 'Edit Permission',
            'permission' => $permission,
            'existingModules' => $existingModules,
            'validation' => \Config\Services::validation()
        ];

        return view('super/permissions/edit', $data);
    }

    /**
     * Update permission in database
     * 
     * @param int $id Permission ID
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $permission = $this->permissionModel->find($id);

        if (!$permission) {
            return redirect()->to('/super/permissions')
                ->with('error', 'Permission tidak ditemukan.');
        }

        // Validation rules
        $rules = [
            'name' => [
                'rules' => "required|min_length[3]|max_length[100]|is_unique[auth_permissions.name,id,{$id}]|regex_match[/^[a-z]+\.[a-z_]+$/]",
                'errors' => [
                    'required' => 'Nama permission wajib diisi',
                    'min_length' => 'Nama permission minimal 3 karakter',
                    'max_length' => 'Nama permission maksimal 100 karakter',
                    'is_unique' => 'Nama permission sudah digunakan',
                    'regex_match' => 'Format permission harus: module.action (contoh: member.view, forum.manage)'
                ]
            ],
            'description' => [
                'rules' => 'required|min_length[10]|max_length[255]',
                'errors' => [
                    'required' => 'Deskripsi permission wajib diisi',
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
            'name' => strtolower($this->request->getPost('name')),
            'description' => $this->request->getPost('description')
        ];

        try {
            $this->permissionModel->update($id, $data);

            return redirect()->to('/super/permissions')
                ->with('success', 'Permission berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating permission: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate permission. Silakan coba lagi.');
        }
    }

    /**
     * Delete permission from database
     * Validates that permission is not assigned to any roles
     * 
     * @param int $id Permission ID
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $permission = $this->permissionModel->find($id);

        if (!$permission) {
            return redirect()->to('/super/permissions')
                ->with('error', 'Permission tidak ditemukan.');
        }

        // Check if permission is assigned to any roles
        $roleCount = $this->permissionModel->db->table('auth_groups_permissions')
            ->where('permission_id', $id)
            ->countAllResults();

        if ($roleCount > 0) {
            return redirect()->to('/super/permissions')
                ->with('error', "Permission tidak dapat dihapus karena masih digunakan oleh {$roleCount} role. Hapus permission dari role tersebut terlebih dahulu.");
        }

        try {
            $this->permissionModel->delete($id);

            return redirect()->to('/super/permissions')
                ->with('success', 'Permission berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting permission: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus permission. Silakan coba lagi.');
        }
    }

    /**
     * Show roles that have this permission
     * 
     * @param int $id Permission ID
     * @return string|RedirectResponse
     */
    public function roles(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $permission = $this->permissionModel->find($id);

        if (!$permission) {
            return redirect()->to('/super/permissions')
                ->with('error', 'Permission tidak ditemukan.');
        }

        // Get roles that have this permission
        $roles = $this->groupModel
            ->select('auth_groups.*, 
                      auth_groups_permissions.created_at as assigned_at,
                      COUNT(DISTINCT auth_groups_users.user_id) as member_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id')
            ->join('auth_groups_users', 'auth_groups_users.group = auth_groups.title', 'left')
            ->where('auth_groups_permissions.permission_id', $id)
            ->groupBy('auth_groups.id')
            ->orderBy('auth_groups.title', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Role yang Memiliki Permission - ' . $permission->name,
            'permission' => $permission,
            'roles' => $roles,
            'role_count' => count($roles)
        ];

        return view('super/permissions/roles', $data);
    }

    /**
     * Sync permissions to Shield database
     * Useful for ensuring consistency between app config and database
     * 
     * @return RedirectResponse
     */
    public function syncToShield(): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        try {
            // Get all permissions from database
            $dbPermissions = $this->permissionModel->findAll();
            $dbPermissionNames = array_column($dbPermissions, 'name');

            // Count operations
            $added = 0;
            $skipped = 0;

            // If you have a config file with predefined permissions, sync from there
            // For now, we'll just ensure database integrity

            // Example: Predefined permissions (you can move this to Config file)
            $predefinedPermissions = [
                // Member permissions
                ['name' => 'member.view', 'description' => 'Dapat melihat data anggota'],
                ['name' => 'member.create', 'description' => 'Dapat menambah anggota baru'],
                ['name' => 'member.edit', 'description' => 'Dapat mengedit data anggota'],
                ['name' => 'member.delete', 'description' => 'Dapat menghapus anggota'],
                ['name' => 'member.approve', 'description' => 'Dapat menyetujui pendaftaran anggota'],
                ['name' => 'member.export', 'description' => 'Dapat export data anggota'],

                // Forum permissions
                ['name' => 'forum.view', 'description' => 'Dapat melihat forum'],
                ['name' => 'forum.create', 'description' => 'Dapat membuat thread forum'],
                ['name' => 'forum.manage', 'description' => 'Dapat mengelola forum (pin, lock, delete)'],

                // Survey permissions
                ['name' => 'survey.view', 'description' => 'Dapat melihat survey'],
                ['name' => 'survey.participate', 'description' => 'Dapat mengisi survey'],
                ['name' => 'survey.manage', 'description' => 'Dapat mengelola survey'],

                // Complaint permissions
                ['name' => 'complaint.view', 'description' => 'Dapat melihat pengaduan'],
                ['name' => 'complaint.create', 'description' => 'Dapat membuat pengaduan'],
                ['name' => 'complaint.manage', 'description' => 'Dapat mengelola pengaduan'],

                // Content permissions
                ['name' => 'content.view', 'description' => 'Dapat melihat konten'],
                ['name' => 'content.manage', 'description' => 'Dapat mengelola konten (blog, pages)'],

                // System permissions
                ['name' => 'system.settings', 'description' => 'Dapat mengakses pengaturan sistem'],
                ['name' => 'system.users', 'description' => 'Dapat mengelola user'],
                ['name' => 'system.roles', 'description' => 'Dapat mengelola roles'],
                ['name' => 'system.permissions', 'description' => 'Dapat mengelola permissions'],
            ];

            // Add missing permissions
            foreach ($predefinedPermissions as $permission) {
                if (!in_array($permission['name'], $dbPermissionNames)) {
                    $this->permissionModel->insert($permission);
                    $added++;
                } else {
                    $skipped++;
                }
            }

            $message = "Sinkronisasi selesai. {$added} permission ditambahkan, {$skipped} sudah ada.";

            return redirect()->to('/super/permissions')
                ->with('success', $message);
        } catch (\Exception $e) {
            log_message('error', 'Error syncing permissions: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal melakukan sinkronisasi. Silakan coba lagi.');
        }
    }

    /**
     * Get existing modules from database
     * Extract unique modules from permission names
     * 
     * @return array
     */
    protected function getExistingModules(): array
    {
        $permissions = $this->permissionModel->findAll();
        $modules = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? null;

            if ($module && !in_array($module, $modules)) {
                $modules[] = $module;
            }
        }

        sort($modules);
        return $modules;
    }
}
