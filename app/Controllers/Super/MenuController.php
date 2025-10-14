<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Services\MenuService;
use CodeIgniter\Shield\Models\PermissionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * MenuController
 * 
 * Menangani dynamic menu management dengan tree structure
 * Super Admin dapat membuat, mengedit, menghapus, dan mengatur menu
 * Termasuk parent-child relationships, permissions, dan ordering
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MenuController extends BaseController
{
    /**
     * @var MenuModel
     */
    protected $menuModel;

    /**
     * @var MenuService
     */
    protected $menuService;

    /**
     * @var PermissionModel
     */
    protected $permissionModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->menuService = new MenuService();
        $this->permissionModel = new PermissionModel();
    }

    /**
     * Display menu tree structure
     * Shows hierarchical menu with parent-child relationships
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get menu tree (including inactive menus)
        $result = $this->menuService->getMenuTree(null, false);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Get total menu count
        $totalMenus = $this->menuModel->countAllResults();

        $data = [
            'title' => 'Manajemen Menu',
            'menuTree' => $result['data'],
            'totalMenus' => $totalMenus
        ];

        return view('super/menus/index', $data);
    }

    /**
     * Show create menu form
     * 
     * @return string|RedirectResponse
     */
    public function create()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all menus for parent selection (flat list)
        $allMenus = $this->menuModel
            ->orderBy('title', 'ASC')
            ->findAll();

        // Get all permissions for permission selection
        $permissions = $this->permissionModel
            ->orderBy('name', 'ASC')
            ->findAll();

        // Group permissions by module
        $groupedPermissions = $this->groupPermissionsByModule($permissions);

        $data = [
            'title' => 'Tambah Menu Baru',
            'allMenus' => $allMenus,
            'groupedPermissions' => $groupedPermissions,
            'validation' => \Config\Services::validation()
        ];

        return view('super/menus/create', $data);
    }

    /**
     * Store new menu to database
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
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Judul menu wajib diisi',
                    'min_length' => 'Judul menu minimal 3 karakter',
                    'max_length' => 'Judul menu maksimal 100 karakter'
                ]
            ],
            'url' => [
                'rules' => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => 'URL maksimal 255 karakter'
                ]
            ],
            'icon' => [
                'rules' => 'permit_empty|max_length[50]',
                'errors' => [
                    'max_length' => 'Icon class maksimal 50 karakter'
                ]
            ],
            'sort_order' => [
                'rules' => 'required|integer|greater_than_equal_to[0]',
                'errors' => [
                    'required' => 'Urutan menu wajib diisi',
                    'integer' => 'Urutan menu harus berupa angka',
                    'greater_than_equal_to' => 'Urutan menu minimal 0'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Prepare data
        $data = [
            'title' => $this->request->getPost('title'),
            'url' => $this->request->getPost('url') ?: null,
            'icon' => $this->request->getPost('icon') ?: null,
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'permission_key' => $this->request->getPost('permission_key') ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        try {
            $this->menuModel->insert($data);

            return redirect()->to('/super/menus')
                ->with('success', 'Menu berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating menu: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan menu. Silakan coba lagi.');
        }
    }

    /**
     * Show edit menu form
     * 
     * @param int $id Menu ID
     * @return string|RedirectResponse
     */
    public function edit(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to('/super/menus')
                ->with('error', 'Menu tidak ditemukan.');
        }

        // Get all menus for parent selection (exclude current menu and its children)
        $allMenus = $this->menuModel
            ->where('id !=', $id)
            ->orderBy('title', 'ASC')
            ->findAll();

        // Filter out children of current menu (prevent circular reference)
        $allMenus = array_filter($allMenus, function ($m) use ($id) {
            return $m->parent_id != $id;
        });

        // Get all permissions for permission selection
        $permissions = $this->permissionModel
            ->orderBy('name', 'ASC')
            ->findAll();

        // Group permissions by module
        $groupedPermissions = $this->groupPermissionsByModule($permissions);

        $data = [
            'title' => 'Edit Menu',
            'menu' => $menu,
            'allMenus' => $allMenus,
            'groupedPermissions' => $groupedPermissions,
            'validation' => \Config\Services::validation()
        ];

        return view('super/menus/edit', $data);
    }

    /**
     * Update menu in database
     * 
     * @param int $id Menu ID
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to('/super/menus')
                ->with('error', 'Menu tidak ditemukan.');
        }

        // Validation rules
        $rules = [
            'title' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Judul menu wajib diisi',
                    'min_length' => 'Judul menu minimal 3 karakter',
                    'max_length' => 'Judul menu maksimal 100 karakter'
                ]
            ],
            'url' => [
                'rules' => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => 'URL maksimal 255 karakter'
                ]
            ],
            'icon' => [
                'rules' => 'permit_empty|max_length[50]',
                'errors' => [
                    'max_length' => 'Icon class maksimal 50 karakter'
                ]
            ],
            'sort_order' => [
                'rules' => 'required|integer|greater_than_equal_to[0]',
                'errors' => [
                    'required' => 'Urutan menu wajib diisi',
                    'integer' => 'Urutan menu harus berupa angka',
                    'greater_than_equal_to' => 'Urutan menu minimal 0'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Check circular reference for parent_id
        $parentId = $this->request->getPost('parent_id') ?: null;
        if ($parentId && $this->hasCircularReference($id, $parentId)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Parent menu tidak valid. Circular reference terdeteksi.');
        }

        // Prepare data
        $data = [
            'title' => $this->request->getPost('title'),
            'url' => $this->request->getPost('url') ?: null,
            'icon' => $this->request->getPost('icon') ?: null,
            'parent_id' => $parentId,
            'permission_key' => $this->request->getPost('permission_key') ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        try {
            $this->menuModel->update($id, $data);

            return redirect()->to('/super/menus')
                ->with('success', 'Menu berhasil diupdate.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating menu: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate menu. Silakan coba lagi.');
        }
    }

    /**
     * Delete menu from database
     * Validates that menu has no children
     * 
     * @param int $id Menu ID
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to('/super/menus')
                ->with('error', 'Menu tidak ditemukan.');
        }

        // Check if menu has children
        $childCount = $this->menuModel
            ->where('parent_id', $id)
            ->countAllResults();

        if ($childCount > 0) {
            return redirect()->to('/super/menus')
                ->with('error', "Menu tidak dapat dihapus karena memiliki {$childCount} sub-menu. Hapus sub-menu terlebih dahulu.");
        }

        try {
            $this->menuModel->delete($id);

            return redirect()->to('/super/menus')
                ->with('success', 'Menu berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting menu: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus menu. Silakan coba lagi.');
        }
    }

    /**
     * Toggle menu active status
     * 
     * @param int $id Menu ID
     * @return RedirectResponse
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat melakukan aksi ini.');
        }

        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to('/super/menus')
                ->with('error', 'Menu tidak ditemukan.');
        }

        try {
            $newStatus = $menu->is_active ? 0 : 1;
            $this->menuModel->update($id, ['is_active' => $newStatus]);

            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->to('/super/menus')
                ->with('success', "Menu berhasil {$statusText}.");
        } catch (\Exception $e) {
            log_message('error', 'Error toggling menu status: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal mengubah status menu. Silakan coba lagi.');
        }
    }

    /**
     * Reorder menus (AJAX endpoint)
     * Handles drag & drop reordering
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function reorder()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akses ditolak.'
            ])->setStatusCode(403);
        }

        // Get new order from POST
        $menuOrder = $this->request->getJSON(true)['order'] ?? [];

        if (empty($menuOrder)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data order tidak valid.'
            ])->setStatusCode(400);
        }

        try {
            // Start transaction
            $this->menuModel->db->transStart();

            // Update each menu's sort_order
            foreach ($menuOrder as $index => $menuId) {
                $this->menuModel->update($menuId, [
                    'sort_order' => $index
                ]);
            }

            // Complete transaction
            $this->menuModel->db->transComplete();

            if ($this->menuModel->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Urutan menu berhasil diupdate.'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error reordering menus: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengupdate urutan menu.'
            ])->setStatusCode(500);
        }
    }

    /**
     * Preview menu for specific role
     * Shows how menu appears for different roles based on permissions
     * 
     * @param string|null $roleTitle Role title (optional)
     * @return string|RedirectResponse
     */
    public function preview(?string $roleTitle = null)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get all roles for dropdown
        $groupModel = new \CodeIgniter\Shield\Models\GroupModel();
        $allRoles = $groupModel->findAll();

        // Default to superadmin if no role specified
        $selectedRole = $roleTitle ?: 'superadmin';

        // Get menu for selected role using MenuService
        $menuTree = $this->menuService->getForRole($selectedRole);

        $data = [
            'title' => 'Preview Menu per Role',
            'allRoles' => $allRoles,
            'selectedRole' => $selectedRole,
            'menuTree' => $menuTree
        ];

        return view('super/menus/preview', $data);
    }

    /**
     * Check for circular reference in parent-child relationships
     * 
     * @param int $menuId Current menu ID
     * @param int $parentId Proposed parent ID
     * @return bool True if circular reference exists
     */
    protected function hasCircularReference(int $menuId, int $parentId): bool
    {
        // If parent is same as current menu
        if ($menuId === $parentId) {
            return true;
        }

        // Check if parent's ancestor is the current menu
        $currentParentId = $parentId;
        $maxDepth = 10; // Prevent infinite loop
        $depth = 0;

        while ($currentParentId && $depth < $maxDepth) {
            $parent = $this->menuModel->find($currentParentId);

            if (!$parent) {
                break;
            }

            if ($parent->id === $menuId) {
                return true; // Circular reference found
            }

            $currentParentId = $parent->parent_id;
            $depth++;
        }

        return false;
    }

    /**
     * Group permissions by module
     * Helper method for organizing permissions in forms
     * 
     * @param array $permissions Array of permission objects
     * @return array Grouped permissions
     */
    protected function groupPermissionsByModule(array $permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = $permission;
        }

        ksort($grouped);
        return $grouped;
    }
}
