<?php

namespace App\Services;

use App\Models\MenuModel;
use App\Models\PermissionModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * MenuService
 * 
 * Menangani dynamic menu management dengan RBAC integration
 * Termasuk hierarchical menu structure, rendering, dan permission-based visibility
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MenuService
{
    /**
     * @var MenuModel
     */
    protected $menuModel;

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
        $this->menuModel = new MenuModel();
        $this->permissionModel = new PermissionModel();
        $this->userModel = new UserModel();
    }

    /**
     * Get menu tree structure
     * Returns hierarchical menu structure with parent-child relationships
     * 
     * @param int|null $parentId Parent menu ID (null for root menus)
     * @param bool $activeOnly Filter only active menus
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getMenuTree(?int $parentId = null, bool $activeOnly = true): array
    {
        try {
            $builder = $this->menuModel->builder();

            if ($parentId === null) {
                $builder->where('parent_id IS NULL');
            } else {
                $builder->where('parent_id', $parentId);
            }

            if ($activeOnly) {
                $builder->where('is_active', 1);
            }

            $menus = $builder->orderBy('sort_order', 'ASC')->get()->getResult();

            // Build tree structure recursively
            $tree = [];
            foreach ($menus as $menu) {
                $menuItem = clone $menu;

                // Get children recursively
                $children = $this->getMenuTree($menu->id, $activeOnly);
                if ($children['success'] && !empty($children['data'])) {
                    $menuItem->children = $children['data'];
                } else {
                    $menuItem->children = [];
                }

                $tree[] = $menuItem;
            }

            return [
                'success' => true,
                'message' => 'Menu tree berhasil diambil',
                'data' => $tree
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in MenuService::getMenuTree: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil menu tree: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get menu for specific user with permission filtering
     * Returns only menus that user has permission to access
     * 
     * @param int $userId User ID
     * @param int|null $parentId Parent menu ID (null for root)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getMenuForUser(int $userId, ?int $parentId = null): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => []
                ];
            }

            // Get all active menus at this level
            $builder = $this->menuModel->builder();

            if ($parentId === null) {
                $builder->where('parent_id IS NULL');
            } else {
                $builder->where('parent_id', $parentId);
            }

            $menus = $builder
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResult();

            $filteredMenus = [];

            foreach ($menus as $menu) {
                // Check permission if permission_key is set
                if (!empty($menu->permission_key)) {
                    // Check if user has permission
                    if (!$user->can($menu->permission_key)) {
                        continue; // Skip this menu
                    }
                }

                // Keep menu as object for consistency
                $menuItem = clone $menu;

                // Get children recursively
                $children = $this->getMenuForUser($userId, $menu->id);
                if ($children['success'] && !empty($children['data'])) {
                    $menuItem->children = $children['data'];
                } else {
                    $menuItem->children = [];
                }

                // Add menu item if it has children or no permission required or user has permission
                if (!empty($menuItem->children) || empty($menu->permission_key) || $user->can($menu->permission_key)) {
                    $filteredMenus[] = $menuItem;
                }
            }

            return [
                'success' => true,
                'message' => 'Menu untuk user berhasil diambil',
                'data' => $filteredMenus
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in MenuService::getMenuForUser: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil menu user: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Render menu as HTML
     * Generates HTML markup for menu navigation
     * 
     * @param array $menus Menu array from getMenuTree or getMenuForUser
     * @param string $cssClass CSS class for ul element
     * @param string $activeUrl Current active URL for highlighting
     * @return string HTML markup
     */
    public function renderMenu(array $menus, string $cssClass = 'nav', string $activeUrl = ''): string
    {
        if (empty($menus)) {
            return '';
        }

        $html = '<ul class="' . esc($cssClass) . '">';

        foreach ($menus as $menu) {
            $menuItem = is_array($menu) ? (object) $menu : $menu;

            // Determine URL
            $url = !empty($menuItem->url) ? $menuItem->url : '#';

            // Check if active
            $isActive = ($activeUrl === $url) || ($activeUrl && strpos($activeUrl, $url) === 0 && $url !== '#');
            $activeClass = $isActive ? ' active' : '';

            // Build link attributes
            $target = !empty($menuItem->target) ? $menuItem->target : '_self';
            $icon = !empty($menuItem->icon) ? '<i class="' . esc($menuItem->icon) . '"></i> ' : '';
            $itemClass = !empty($menuItem->css_class) ? ' ' . esc($menuItem->css_class) : '';

            // Has children?
            $hasChildren = !empty($menuItem->children);
            $childrenClass = $hasChildren ? ' has-children' : '';

            $html .= '<li class="nav-item' . $activeClass . $childrenClass . $itemClass . '">';
            $html .= '<a href="' . esc($url) . '" class="nav-link" target="' . esc($target) . '">';
            $html .= $icon . esc($menuItem->title);

            if ($hasChildren) {
                $html .= ' <i class="submenu-icon material-icons">keyboard_arrow_right</i>';
            }

            $html .= '</a>';

            // Render children
            if ($hasChildren) {
                $html .= $this->renderMenu($menuItem->children, 'sub-menu', $activeUrl);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Create new menu item
     * Adds new menu to database
     * 
     * @param array $data Menu data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createMenuItem(array $data): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validate required fields
            if (empty($data['title'])) {
                return [
                    'success' => false,
                    'message' => 'Judul menu harus diisi',
                    'data' => null
                ];
            }

            // Validate parent if provided
            if (!empty($data['parent_id'])) {
                $parent = $this->menuModel->find($data['parent_id']);
                if (!$parent) {
                    return [
                        'success' => false,
                        'message' => 'Parent menu tidak ditemukan',
                        'data' => null
                    ];
                }
            }

            // Set defaults
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_external'] = $data['is_external'] ?? 0;
            $data['target'] = $data['target'] ?? '_self';

            // Auto-generate sort_order if not provided
            if (empty($data['sort_order'])) {
                $parentId = $data['parent_id'] ?? null;
                $maxSort = $this->menuModel
                    ->selectMax('sort_order')
                    ->where('parent_id', $parentId)
                    ->first();

                $data['sort_order'] = ($maxSort->sort_order ?? 0) + 1;
            }

            // Insert menu
            $menuId = $this->menuModel->insert($data);

            if (!$menuId) {
                throw new \Exception('Gagal menyimpan menu: ' . json_encode($this->menuModel->errors()));
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Menu berhasil ditambahkan',
                'data' => [
                    'menu_id' => $menuId,
                    'title' => $data['title']
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in MenuService::createMenuItem: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambah menu: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update menu item
     * Updates existing menu data
     * 
     * @param int $menuId Menu ID
     * @param array $data Update data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateMenuItem(int $menuId, array $data): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $menu = $this->menuModel->find($menuId);

            if (!$menu) {
                return [
                    'success' => false,
                    'message' => 'Menu tidak ditemukan',
                    'data' => null
                ];
            }

            // Prevent circular parent reference
            if (!empty($data['parent_id']) && $data['parent_id'] == $menuId) {
                return [
                    'success' => false,
                    'message' => 'Menu tidak dapat menjadi parent dari dirinya sendiri',
                    'data' => null
                ];
            }

            // Validate parent if changed
            if (!empty($data['parent_id']) && $data['parent_id'] != $menu->parent_id) {
                $parent = $this->menuModel->find($data['parent_id']);
                if (!$parent) {
                    return [
                        'success' => false,
                        'message' => 'Parent menu tidak ditemukan',
                        'data' => null
                    ];
                }
            }

            // Update menu
            $updated = $this->menuModel->update($menuId, $data);

            if (!$updated) {
                throw new \Exception('Gagal update menu: ' . json_encode($this->menuModel->errors()));
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Menu berhasil diupdate',
                'data' => [
                    'menu_id' => $menuId
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in MenuService::updateMenuItem: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update menu: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete menu item
     * Removes menu and optionally its children
     * 
     * @param int $menuId Menu ID
     * @param bool $deleteChildren Delete children menus as well
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deleteMenuItem(int $menuId, bool $deleteChildren = false): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $menu = $this->menuModel->find($menuId);

            if (!$menu) {
                return [
                    'success' => false,
                    'message' => 'Menu tidak ditemukan',
                    'data' => null
                ];
            }

            // Check for children
            $children = $this->menuModel->where('parent_id', $menuId)->findAll();

            if (!empty($children)) {
                if ($deleteChildren) {
                    // Delete all children recursively
                    foreach ($children as $child) {
                        $this->deleteMenuItem($child->id, true);
                    }
                } else {
                    // Set children's parent to null
                    $this->menuModel->where('parent_id', $menuId)->set(['parent_id' => null])->update();
                }
            }

            // Delete menu
            $this->menuModel->delete($menuId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Menu berhasil dihapus',
                'data' => [
                    'menu_id' => $menuId,
                    'children_deleted' => $deleteChildren ? count($children) : 0
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in MenuService::deleteMenuItem: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal hapus menu: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Reorder menu items
     * Changes sort order of menus
     * 
     * @param array $menuOrder Array of menu_id => sort_order
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function reorderMenu(array $menuOrder): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $updated = 0;

            foreach ($menuOrder as $menuId => $sortOrder) {
                $menu = $this->menuModel->find($menuId);

                if ($menu) {
                    $this->menuModel->update($menuId, ['sort_order' => $sortOrder]);
                    $updated++;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => sprintf('Berhasil update urutan %d menu', $updated),
                'data' => [
                    'updated_count' => $updated
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in MenuService::reorderMenu: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal reorder menu: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Toggle menu active status
     * Activate or deactivate menu
     * 
     * @param int $menuId Menu ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function toggleMenuItem(int $menuId): array
    {
        try {
            $menu = $this->menuModel->find($menuId);

            if (!$menu) {
                return [
                    'success' => false,
                    'message' => 'Menu tidak ditemukan',
                    'data' => null
                ];
            }

            $newStatus = $menu->is_active ? 0 : 1;
            $this->menuModel->update($menuId, ['is_active' => $newStatus]);

            return [
                'success' => true,
                'message' => sprintf('Menu berhasil %s', $newStatus ? 'diaktifkan' : 'dinonaktifkan'),
                'data' => [
                    'menu_id' => $menuId,
                    'is_active' => $newStatus
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in MenuService::toggleMenuItem: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal toggle menu: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate if user can access menu
     * Checks permission-based access
     * 
     * @param int $userId User ID
     * @param int $menuId Menu ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function validateMenuAccess(int $userId, int $menuId): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => ['has_access' => false]
                ];
            }

            $menu = $this->menuModel->find($menuId);

            if (!$menu) {
                return [
                    'success' => false,
                    'message' => 'Menu tidak ditemukan',
                    'data' => ['has_access' => false]
                ];
            }

            // Check if menu is active
            if (!$menu->is_active) {
                return [
                    'success' => false,
                    'message' => 'Menu tidak aktif',
                    'data' => ['has_access' => false]
                ];
            }

            // Check permission if required
            if (!empty($menu->permission_key)) {
                $hasPermission = $user->can($menu->permission_key);

                return [
                    'success' => $hasPermission,
                    'message' => $hasPermission ? 'Akses granted' : 'Tidak memiliki permission',
                    'data' => [
                        'has_access' => $hasPermission,
                        'permission_key' => $menu->permission_key
                    ]
                ];
            }

            // No permission required
            return [
                'success' => true,
                'message' => 'Akses granted (no permission required)',
                'data' => ['has_access' => true]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in MenuService::validateMenuAccess: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal validasi akses menu: ' . $e->getMessage(),
                'data' => ['has_access' => false]
            ];
        }
    }
}
