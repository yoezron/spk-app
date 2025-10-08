<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoleModel
 * 
 * Model untuk mengelola roles/groups dinamis dari database
 * Mendukung RBAC (Role-Based Access Control) yang dapat dikelola via UI
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RoleModel extends Model
{
    protected $table            = 'auth_groups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'title'       => 'required|min_length[3]|max_length[100]|is_unique[auth_groups.title,id,{id}]',
        'description' => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Nama role harus diisi',
            'min_length' => 'Nama role minimal 3 karakter',
            'max_length' => 'Nama role maksimal 100 karakter',
            'is_unique'  => 'Nama role sudah digunakan',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = ['checkRoleUsage'];
    protected $afterDelete    = [];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get role with its permissions
     * 
     * @return object
     */
    public function withPermissions()
    {
        return $this->select('auth_groups.*, GROUP_CONCAT(auth_permissions.name) as permissions')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id', 'left')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id', 'left')
            ->groupBy('auth_groups.id');
    }

    /**
     * Get role with permission count
     * 
     * @return object
     */
    public function withPermissionCount()
    {
        return $this->select('auth_groups.*, COUNT(auth_groups_permissions.permission_id) as permission_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id', 'left')
            ->groupBy('auth_groups.id');
    }

    /**
     * Get role with user count
     * 
     * @return object
     */
    public function withUserCount()
    {
        return $this->select('auth_groups.*, COUNT(auth_groups_users.user_id) as user_count')
            ->join('auth_groups_users', 'auth_groups_users.group = auth_groups.title', 'left')
            ->groupBy('auth_groups.id');
    }

    /**
     * Get role with complete data (permissions + users count)
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('auth_groups.*')
            ->select('COUNT(DISTINCT auth_groups_permissions.permission_id) as permission_count')
            ->select('COUNT(DISTINCT auth_groups_users.user_id) as user_count')
            ->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups.id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.group = auth_groups.title', 'left')
            ->groupBy('auth_groups.id');
    }

    // ========================================
    // CUSTOM METHODS
    // ========================================

    /**
     * Get role by title/name
     * 
     * @param string $title Role title
     * @return object|null
     */
    public function findByTitle(string $title)
    {
        return $this->where('title', $title)->first();
    }

    /**
     * Get all roles with their permissions
     * 
     * @return array
     */
    public function getAllWithPermissions(): array
    {
        return $this->withPermissions()->findAll();
    }

    /**
     * Check if role title exists
     * 
     * @param string $title Role title
     * @param int|null $excludeId Exclude this ID (for updates)
     * @return bool
     */
    public function titleExists(string $title, ?int $excludeId = null): bool
    {
        $builder = $this->where('title', $title);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get role permissions
     * 
     * @param int $roleId Role ID
     * @return array Array of permission IDs
     */
    public function getPermissions(int $roleId): array
    {
        $results = $this->db->table('auth_groups_permissions')
            ->where('group_id', $roleId)
            ->get()
            ->getResultArray();

        return array_column($results, 'permission_id');
    }

    /**
     * Get role permissions (detailed)
     * 
     * @param int $roleId Role ID
     * @return array Array of permission objects
     */
    public function getPermissionsDetailed(int $roleId): array
    {
        return $this->db->table('auth_groups_permissions')
            ->select('auth_permissions.*')
            ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
            ->where('auth_groups_permissions.group_id', $roleId)
            ->get()
            ->getResultArray();
    }

    /**
     * Assign permissions to role
     * 
     * @param int $roleId Role ID
     * @param array $permissionIds Array of permission IDs
     * @return bool
     */
    public function assignPermissions(int $roleId, array $permissionIds): bool
    {
        // Start transaction
        $this->db->transStart();

        // Remove existing permissions
        $this->db->table('auth_groups_permissions')
            ->where('group_id', $roleId)
            ->delete();

        // Insert new permissions
        if (!empty($permissionIds)) {
            $data = [];
            foreach ($permissionIds as $permissionId) {
                $data[] = [
                    'group_id'      => $roleId,
                    'permission_id' => $permissionId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ];
            }

            $this->db->table('auth_groups_permissions')->insertBatch($data);
        }

        // Complete transaction
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Add single permission to role
     * 
     * @param int $roleId Role ID
     * @param int $permissionId Permission ID
     * @return bool
     */
    public function addPermission(int $roleId, int $permissionId): bool
    {
        // Check if already exists
        $exists = $this->db->table('auth_groups_permissions')
            ->where('group_id', $roleId)
            ->where('permission_id', $permissionId)
            ->countAllResults() > 0;

        if ($exists) {
            return true; // Already assigned
        }

        return $this->db->table('auth_groups_permissions')->insert([
            'group_id'      => $roleId,
            'permission_id' => $permissionId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Remove single permission from role
     * 
     * @param int $roleId Role ID
     * @param int $permissionId Permission ID
     * @return bool
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        return $this->db->table('auth_groups_permissions')
            ->where('group_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    /**
     * Get users count by role
     * 
     * @param int $roleId Role ID
     * @return int
     */
    public function getUserCount(int $roleId): int
    {
        $role = $this->find($roleId);

        if (!$role) {
            return 0;
        }

        return $this->db->table('auth_groups_users')
            ->where('group', $role->title)
            ->countAllResults();
    }

    /**
     * Get users by role
     * 
     * @param int $roleId Role ID
     * @param int|null $limit Limit results
     * @return array
     */
    public function getUsers(int $roleId, ?int $limit = null): array
    {
        $role = $this->find($roleId);

        if (!$role) {
            return [];
        }

        $builder = $this->db->table('users')
            ->select('users.*, member_profiles.full_name, member_profiles.member_number')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->where('auth_groups_users.group', $role->title);

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Check if role can be deleted
     * 
     * @param int $roleId Role ID
     * @return bool
     */
    public function canDelete(int $roleId): bool
    {
        // Cannot delete if role has users
        if ($this->getUserCount($roleId) > 0) {
            return false;
        }

        // Cannot delete system roles (optional - add logic here)
        $role = $this->find($roleId);
        $protectedRoles = ['Super Admin', 'Pengurus', 'Anggota'];

        if ($role && in_array($role->title, $protectedRoles)) {
            return false;
        }

        return true;
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get role statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $roles = $this->withComplete()->findAll();

        $stats = [
            'total_roles' => count($roles),
            'total_users' => 0,
            'roles'       => [],
        ];

        foreach ($roles as $role) {
            $stats['total_users'] += $role->user_count ?? 0;
            $stats['roles'][] = [
                'id'               => $role->id,
                'title'            => $role->title,
                'user_count'       => $role->user_count ?? 0,
                'permission_count' => $role->permission_count ?? 0,
            ];
        }

        return $stats;
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Before delete callback - check if role can be deleted
     * 
     * @param array $data
     * @return array
     */
    protected function checkRoleUsage(array $data): array
    {
        if (isset($data['id'])) {
            $roleId = is_array($data['id']) ? $data['id'][0] : $data['id'];

            if (!$this->canDelete($roleId)) {
                throw new \RuntimeException('Role tidak dapat dihapus karena masih digunakan oleh user atau merupakan role sistem.');
            }
        }

        return $data;
    }
}
