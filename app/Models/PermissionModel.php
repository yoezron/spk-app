<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PermissionModel
 * 
 * Model untuk mengelola permissions secara dinamis dari database
 * Mendukung RBAC dengan permissions granular per modul
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PermissionModel extends Model
{
    protected $table            = 'auth_permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'        => 'required|min_length[3]|max_length[100]|is_unique[auth_permissions.name,id,{id}]|regex_match[/^[a-z_\.]+$/]',
        'description' => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'    => 'Nama permission harus diisi',
            'min_length'  => 'Nama permission minimal 3 karakter',
            'max_length'  => 'Nama permission maksimal 100 karakter',
            'is_unique'   => 'Permission sudah ada',
            'regex_match' => 'Format permission harus lowercase dengan underscore atau titik (e.g., member.view)',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeDelete   = ['checkPermissionUsage'];

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get permissions by module
     * 
     * @param string $module Module name (e.g., 'member', 'forum', 'survey')
     * @return object
     */
    public function byModule(string $module)
    {
        return $this->like('name', $module . '.', 'after');
    }

    /**
     * Get permissions grouped by module
     * 
     * @return array
     */
    public function groupedByModule(): array
    {
        $permissions = $this->orderBy('name', 'ASC')->findAll();
        $grouped = [];

        foreach ($permissions as $permission) {
            // Extract module from permission name (e.g., 'member.view' -> 'member')
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = $permission;
        }

        // Sort modules alphabetically
        ksort($grouped);

        return $grouped;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get permission with roles count
     * 
     * @return object
     */
    public function withRoleCount()
    {
        return $this->select('auth_permissions.*, COUNT(auth_groups_permissions.group_id) as role_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.permission_id = auth_permissions.id', 'left')
            ->groupBy('auth_permissions.id');
    }

    /**
     * Get permission with roles (which roles have this permission)
     * 
     * @return object
     */
    public function withRoles()
    {
        return $this->select('auth_permissions.*, GROUP_CONCAT(auth_groups.title) as roles')
            ->join('auth_groups_permissions', 'auth_groups_permissions.permission_id = auth_permissions.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_permissions.group_id', 'left')
            ->groupBy('auth_permissions.id');
    }

    // ========================================
    // CUSTOM METHODS
    // ========================================

    /**
     * Get permission by name
     * 
     * @param string $name Permission name
     * @return object|null
     */
    public function findByName(string $name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Get all permissions as key-value array
     * 
     * @return array [id => name]
     */
    public function getAllAsOptions(): array
    {
        $permissions = $this->orderBy('name', 'ASC')->findAll();
        $options = [];

        foreach ($permissions as $permission) {
            $options[$permission->id] = $permission->name;
        }

        return $options;
    }

    /**
     * Get permissions grouped by module as options
     * 
     * @return array
     */
    public function getGroupedOptions(): array
    {
        $grouped = $this->groupedByModule();
        $options = [];

        foreach ($grouped as $module => $permissions) {
            $options[$module] = [];
            foreach ($permissions as $permission) {
                $options[$module][$permission->id] = $permission->name;
            }
        }

        return $options;
    }

    /**
     * Check if permission name exists
     * 
     * @param string $name Permission name
     * @param int|null $excludeId Exclude this ID (for updates)
     * @return bool
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $builder = $this->where('name', $name);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get roles that have this permission
     * 
     * @param int $permissionId Permission ID
     * @return array
     */
    public function getRoles(int $permissionId): array
    {
        return $this->db->table('auth_groups_permissions')
            ->select('auth_groups.*')
            ->join('auth_groups', 'auth_groups.id = auth_groups_permissions.group_id')
            ->where('auth_groups_permissions.permission_id', $permissionId)
            ->get()
            ->getResultArray();
    }

    /**
     * Get role count for permission
     * 
     * @param int $permissionId Permission ID
     * @return int
     */
    public function getRoleCount(int $permissionId): int
    {
        return $this->db->table('auth_groups_permissions')
            ->where('permission_id', $permissionId)
            ->countAllResults();
    }

    /**
     * Check if user has permission
     * 
     * @param int $userId User ID
     * @param string $permissionName Permission name
     * @return bool
     */
    public function userHasPermission(int $userId, string $permissionName): bool
    {
        // Get user's role
        $userRole = $this->db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        if (!$userRole) {
            return false;
        }

        // Get role ID
        $role = $this->db->table('auth_groups')
            ->where('title', $userRole->group)
            ->get()
            ->getRow();

        if (!$role) {
            return false;
        }

        // Check if role has this permission
        $permission = $this->findByName($permissionName);

        if (!$permission) {
            return false;
        }

        $hasPermission = $this->db->table('auth_groups_permissions')
            ->where('group_id', $role->id)
            ->where('permission_id', $permission->id)
            ->countAllResults() > 0;

        return $hasPermission;
    }

    /**
     * Get user's all permissions
     * 
     * @param int $userId User ID
     * @return array Array of permission names
     */
    public function getUserPermissions(int $userId): array
    {
        // Get user's role
        $userRole = $this->db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        if (!$userRole) {
            return [];
        }

        // Get role ID
        $role = $this->db->table('auth_groups')
            ->where('title', $userRole->group)
            ->get()
            ->getRow();

        if (!$role) {
            return [];
        }

        // Get all permissions for this role
        $permissions = $this->db->table('auth_groups_permissions')
            ->select('auth_permissions.name')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
            ->where('auth_groups_permissions.group_id', $role->id)
            ->get()
            ->getResultArray();

        return array_column($permissions, 'name');
    }

    /**
     * Check if permission can be deleted
     * 
     * @param int $permissionId Permission ID
     * @return bool
     */
    public function canDelete(int $permissionId): bool
    {
        // Cannot delete if permission is assigned to any role
        if ($this->getRoleCount($permissionId) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Bulk create permissions
     * 
     * @param array $permissions Array of permissions [['name' => '...', 'description' => '...'], ...]
     * @return bool
     */
    public function bulkCreate(array $permissions): bool
    {
        if (empty($permissions)) {
            return false;
        }

        // Add timestamps
        foreach ($permissions as &$permission) {
            $permission['created_at'] = date('Y-m-d H:i:s');
            $permission['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->insertBatch($permissions) > 0;
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get permission statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $grouped = $this->groupedByModule();

        $stats = [
            'total_permissions' => $this->countAllResults(false),
            'total_modules'     => count($grouped),
            'modules'           => [],
        ];

        foreach ($grouped as $module => $permissions) {
            $stats['modules'][$module] = count($permissions);
        }

        return $stats;
    }

    /**
     * Get module list
     * 
     * @return array
     */
    public function getModuleList(): array
    {
        $grouped = $this->groupedByModule();
        return array_keys($grouped);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Before delete callback - check if permission can be deleted
     * 
     * @param array $data
     * @return array
     */
    protected function checkPermissionUsage(array $data): array
    {
        if (isset($data['id'])) {
            $permissionId = is_array($data['id']) ? $data['id'][0] : $data['id'];

            if (!$this->canDelete($permissionId)) {
                throw new \RuntimeException('Permission tidak dapat dihapus karena masih digunakan oleh role.');
            }
        }

        return $data;
    }
}
