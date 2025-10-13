<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MenuModel
 * 
 * Model untuk mengelola dynamic menu dengan RBAC integration
 * Mendukung hierarchical menu structure dan permission-based visibility
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent_id',
        'title',
        'url',
        'route_name',
        'icon',
        'permission_key',
        'is_active',
        'is_external',
        'target',
        'sort_order',
        'description',
        'css_class'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title' => 'required|min_length[2]|max_length[100]',
        'url' => 'permit_empty|max_length[255]',
        'route_name' => 'permit_empty|max_length[100]',
        'icon' => 'permit_empty|max_length[50]',
        'permission_key' => 'permit_empty|max_length[100]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_external' => 'permit_empty|in_list[0,1]',
        'target' => 'permit_empty|in_list[_self,_blank,_parent,_top]',
        'sort_order' => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Judul menu harus diisi',
            'min_length' => 'Judul menu minimal 2 karakter',
            'max_length' => 'Judul menu maksimal 100 karakter',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultValues'];
    protected $beforeUpdate   = [];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get menu with parent
     * 
     * @return object
     */
    public function withParent()
    {
        return $this->select('menus.*, parent_menus.title as parent_title')
            ->join('menus as parent_menus', 'parent_menus.id = menus.parent_id', 'left');
    }

    /**
     * Get menu with children count
     * 
     * @return object
     */
    public function withChildrenCount()
    {
        return $this->select('menus.*')
            ->select('(SELECT COUNT(*) FROM menus as child_menus WHERE child_menus.parent_id = menus.id AND child_menus.deleted_at IS NULL) as children_count');
    }

    /**
     * Get menu with permission details
     * 
     * @return object
     */
    public function withPermission()
    {
        return $this->select('menus.*, auth_permissions.name as permission_name, auth_permissions.description as permission_description')
            ->join('auth_permissions', 'auth_permissions.key = menus.permission_key', 'left');
    }

    /**
     * Get menu with complete data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('menus.*')
            ->select('parent_menus.title as parent_title')
            ->select('auth_permissions.name as permission_name')
            ->select('(SELECT COUNT(*) FROM menus as child_menus WHERE child_menus.parent_id = menus.id AND child_menus.deleted_at IS NULL) as children_count')
            ->join('menus as parent_menus', 'parent_menus.id = menus.parent_id', 'left')
            ->join('auth_permissions', 'auth_permissions.key = menus.permission_key', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get active menus only
     * 
     * @return object
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /**
     * Get inactive menus
     * 
     * @return object
     */
    public function inactive()
    {
        return $this->where('is_active', 0);
    }

    /**
     * Get parent menus (top level)
     * 
     * @return object
     */
    public function parentMenus()
    {
        return $this->where('parent_id IS NULL');
    }

    /**
     * Get child menus
     * 
     * @param int $parentId Parent menu ID
     * @return object
     */
    public function childMenus(int $parentId)
    {
        return $this->where('parent_id', $parentId);
    }

    /**
     * Get external menus
     * 
     * @return object
     */
    public function external()
    {
        return $this->where('is_external', 1);
    }

    /**
     * Get internal menus
     * 
     * @return object
     */
    public function internal()
    {
        return $this->where('is_external', 0);
    }

    /**
     * Order by sort order
     * 
     * @return object
     */
    public function ordered()
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get all active menus ordered
     * 
     * @return array
     */
    public function getActive(): array
    {
        return $this->active()
            ->ordered()
            ->findAll();
    }

    /**
     * Get menu structure (hierarchical)
     * 
     * @return array
     */
    public function getMenuStructure(): array
    {
        $parents = $this->active()
            ->parentMenus()
            ->ordered()
            ->findAll();

        foreach ($parents as &$parent) {
            $parent->children = $this->active()
                ->childMenus($parent->id)
                ->ordered()
                ->findAll();
        }

        return $parents;
    }

    /**
     * Build menu tree for specific user/role
     * 
     * @param array $userPermissions Array of permission keys user has
     * @return array
     */
    public function buildMenuTree(array $userPermissions = []): array
    {
        $menus = $this->active()->ordered()->findAll();

        // Filter menus based on permissions
        $filteredMenus = [];
        foreach ($menus as $menu) {
            // If no permission required, or user has the permission
            if (empty($menu->permission_key) || in_array($menu->permission_key, $userPermissions)) {
                $filteredMenus[] = $menu;
            }
        }

        // Build tree structure
        return $this->buildTree($filteredMenus);
    }

    /**
     * Build hierarchical tree from flat array
     * 
     * @param array $elements Menu items
     * @param int|null $parentId Parent ID
     * @return array
     */
    protected function buildTree(array $elements, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Get menus for specific role
     * 
     * @param string $roleTitle Role title
     * @return array
     */
    public function getForRole(string $roleTitle): array
    {
        // Get permissions for this role
        $permissions = $this->db->table('auth_groups_permissions')
            ->select('auth_permissions.key')
            ->join('auth_groups', 'auth_groups.id = auth_groups_permissions.group_id')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
            ->where('auth_groups.title', $roleTitle)
            ->get()
            ->getResultArray();

        $permissionKeys = array_column($permissions, 'key');

        return $this->buildMenuTree($permissionKeys);
    }

    /**
     * Get menus for specific user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getForUser(int $userId): array
    {
        // Get user's permissions through their roles
        $permissions = $this->db->table('auth_groups_users')
            ->select('auth_permissions.key')
            ->join('auth_groups', 'auth_groups.title = auth_groups_users.group')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
            ->where('auth_groups_users.user_id', $userId)
            ->get()
            ->getResultArray();

        $permissionKeys = array_unique(array_column($permissions, 'key'));

        return $this->buildMenuTree($permissionKeys);
    }

    /**
     * Get breadcrumb trail for menu
     * 
     * @param int $menuId Menu ID
     * @return array
     */
    public function getBreadcrumb(int $menuId): array
    {
        $breadcrumb = [];
        $menu = $this->find($menuId);

        while ($menu) {
            array_unshift($breadcrumb, [
                'id' => $menu->id,
                'title' => $menu->title,
                'url' => $menu->url,
            ]);

            $menu = $menu->parent_id ? $this->find($menu->parent_id) : null;
        }

        return $breadcrumb;
    }

    /**
     * Search menus by keyword
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('title', $keyword)
            ->orLike('description', $keyword)
            ->orLike('url', $keyword)
            ->groupEnd();
    }

    /**
     * Get menus without permission (public menus)
     * 
     * @return array
     */
    public function getPublicMenus(): array
    {
        return $this->active()
            ->where('permission_key IS NULL')
            ->ordered()
            ->findAll();
    }

    /**
     * Get menus by permission
     * 
     * @param string $permissionKey Permission key
     * @return array
     */
    public function getByPermission(string $permissionKey): array
    {
        return $this->active()
            ->where('permission_key', $permissionKey)
            ->ordered()
            ->findAll();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count active menus
     * 
     * @return int
     */
    public function countActive(): int
    {
        return $this->active()->countAllResults();
    }

    /**
     * Count parent menus
     * 
     * @return int
     */
    public function countParents(): int
    {
        return $this->active()->parentMenus()->countAllResults();
    }

    /**
     * Count child menus
     * 
     * @return int
     */
    public function countChildren(): int
    {
        return $this->active()
            ->where('parent_id IS NOT NULL')
            ->countAllResults();
    }

    /**
     * Get menu distribution by level
     * 
     * @return array
     */
    public function getDistributionByLevel(): array
    {
        $parents = $this->countParents();
        $children = $this->countChildren();

        return [
            'parent_menus' => $parents,
            'child_menus' => $children,
            'total_menus' => $parents + $children,
        ];
    }

    /**
     * Get menus without permission count
     * 
     * @return int
     */
    public function countPublicMenus(): int
    {
        return $this->active()
            ->where('permission_key IS NULL')
            ->countAllResults();
    }

    /**
     * Get menus with permission count
     * 
     * @return int
     */
    public function countProtectedMenus(): int
    {
        return $this->active()
            ->where('permission_key IS NOT NULL')
            ->countAllResults();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Activate menu
     * 
     * @param int $menuId Menu ID
     * @return bool
     */
    public function activate(int $menuId): bool
    {
        return $this->update($menuId, ['is_active' => 1]);
    }

    /**
     * Deactivate menu
     * 
     * @param int $menuId Menu ID
     * @return bool
     */
    public function deactivate(int $menuId): bool
    {
        return $this->update($menuId, ['is_active' => 0]);
    }

    /**
     * Update sort order
     * 
     * @param int $menuId Menu ID
     * @param int $sortOrder New sort order
     * @return bool
     */
    public function updateSortOrder(int $menuId, int $sortOrder): bool
    {
        return $this->update($menuId, ['sort_order' => $sortOrder]);
    }

    /**
     * Reorder menus
     * 
     * @param array $order Array of [id => sort_order]
     * @return bool
     */
    public function reorder(array $order): bool
    {
        $this->db->transStart();

        foreach ($order as $id => $sortOrder) {
            $this->update($id, ['sort_order' => $sortOrder]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Set parent menu
     * 
     * @param int $menuId Menu ID
     * @param int|null $parentId Parent menu ID
     * @return bool
     */
    public function setParent(int $menuId, ?int $parentId = null): bool
    {
        // Prevent circular reference
        if ($parentId && $this->isCircularReference($menuId, $parentId)) {
            return false;
        }

        return $this->update($menuId, ['parent_id' => $parentId]);
    }

    /**
     * Check for circular reference in parent-child relationship
     * 
     * @param int $menuId Menu ID
     * @param int $parentId Potential parent ID
     * @return bool
     */
    protected function isCircularReference(int $menuId, int $parentId): bool
    {
        if ($menuId === $parentId) {
            return true;
        }

        $parent = $this->find($parentId);
        while ($parent && $parent->parent_id) {
            if ($parent->parent_id === $menuId) {
                return true;
            }
            $parent = $this->find($parent->parent_id);
        }

        return false;
    }

    /**
     * Set permission for menu
     * 
     * @param int $menuId Menu ID
     * @param string|null $permissionKey Permission key
     * @return bool
     */
    public function setPermission(int $menuId, ?string $permissionKey = null): bool
    {
        return $this->update($menuId, ['permission_key' => $permissionKey]);
    }

    /**
     * Duplicate menu
     * 
     * @param int $menuId Menu ID to duplicate
     * @return int|false New menu ID or false
     */
    public function duplicateMenu(int $menuId)
    {
        $menu = $this->find($menuId);

        if (!$menu) {
            return false;
        }

        $newMenu = [
            'parent_id' => $menu->parent_id,
            'title' => $menu->title . ' (Copy)',
            'url' => $menu->url,
            'route_name' => $menu->route_name,
            'icon' => $menu->icon,
            'permission_key' => $menu->permission_key,
            'is_active' => 0, // Set as inactive by default
            'is_external' => $menu->is_external,
            'target' => $menu->target,
            'description' => $menu->description,
            'css_class' => $menu->css_class,
        ];

        return $this->insert($newMenu);
    }

    /**
     * Check if menu can be deleted
     * 
     * @param int $menuId Menu ID
     * @return bool
     */
    public function canDelete(int $menuId): bool
    {
        // Menu can be deleted if it has no children
        $childrenCount = $this->db->table('menus')
            ->where('parent_id', $menuId)
            ->countAllResults();

        return $childrenCount === 0;
    }

    /**
     * Delete menu with children
     * 
     * @param int $menuId Menu ID
     * @param bool $cascade Delete children too
     * @return bool
     */
    public function deleteWithChildren(int $menuId, bool $cascade = false): bool
    {
        $this->db->transStart();

        if ($cascade) {
            // Delete all children first
            $children = $this->childMenus($menuId)->findAll();
            foreach ($children as $child) {
                $this->deleteWithChildren($child->id, true);
            }
        } else {
            // Move children to parent's level
            $menu = $this->find($menuId);
            if ($menu) {
                $this->db->table('menus')
                    ->where('parent_id', $menuId)
                    ->update(['parent_id' => $menu->parent_id]);
            }
        }

        // Delete the menu
        $this->delete($menuId);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Check if user can access menu
     * 
     * @param int $menuId Menu ID
     * @param int $userId User ID
     * @return bool
     */
    public function canUserAccess(int $menuId, int $userId): bool
    {
        $menu = $this->find($menuId);

        if (!$menu || !$menu->is_active) {
            return false;
        }

        // If no permission required, menu is public
        if (empty($menu->permission_key)) {
            return true;
        }

        // Check if user has the required permission
        $hasPermission = $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.title = auth_groups_users.group')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
            ->where('auth_groups_users.user_id', $userId)
            ->where('auth_permissions.key', $menu->permission_key)
            ->countAllResults() > 0;

        return $hasPermission;
    }

    /**
     * Get menu depth/level
     * 
     * @param int $menuId Menu ID
     * @return int
     */
    public function getMenuDepth(int $menuId): int
    {
        $depth = 0;
        $menu = $this->find($menuId);

        while ($menu && $menu->parent_id) {
            $depth++;
            $menu = $this->find($menu->parent_id);
        }

        return $depth;
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set default values before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultValues(array $data): array
    {
        if (!isset($data['data']['is_active'])) {
            $data['data']['is_active'] = 1;
        }

        if (!isset($data['data']['is_external'])) {
            $data['data']['is_external'] = 0;
        }

        if (!isset($data['data']['target'])) {
            $data['data']['target'] = '_self';
        }

        if (!isset($data['data']['sort_order'])) {
            // Get max sort_order for same parent level
            $parentId = $data['data']['parent_id'] ?? null;

            $builder = $this->selectMax('sort_order');
            if ($parentId) {
                $builder->where('parent_id', $parentId);
            } else {
                $builder->where('parent_id IS NULL');
            }

            $maxOrder = $builder->first();
            $data['data']['sort_order'] = $maxOrder ? (int)$maxOrder->sort_order + 1 : 1;
        }

        if (!isset($data['data']['icon'])) {
            $data['data']['icon'] = 'ti ti-circle';
        }

        return $data;
    }
}
