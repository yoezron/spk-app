<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Role Entity
 * 
 * Representasi object-oriented dari role/group
 * Menyediakan business logic methods untuk role management dan RBAC
 * 
 * @package App\Entities
 * @author  SPK Development Team
 * @version 1.0.0
 */
class Role extends Entity
{
    /**
     * Data mapping (if column names differ from property names)
     */
    protected $datamap = [];

    /**
     * Define date fields for automatic Time conversion
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Type casting for properties
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Cache for permissions to avoid multiple queries
     */
    protected $permissionsCache = null;

    /**
     * Cache for user count
     */
    protected $userCountCache = null;

    // ========================================
    // BASIC GETTERS
    // ========================================

    /**
     * Get role ID
     * 
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->attributes['id'];
    }

    /**
     * Get role title/name
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return $this->attributes['title'] ?? '';
    }

    /**
     * Get role description
     * 
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    /**
     * Get role display name (alias for getTitle)
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->getTitle();
    }

    // ========================================
    // ROLE TYPE CHECK METHODS
    // ========================================

    /**
     * Check if this is Super Admin role
     * 
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->getTitle() === 'Super Admin';
    }

    /**
     * Check if this is Pengurus role
     * 
     * @return bool
     */
    public function isPengurus(): bool
    {
        return $this->getTitle() === 'Pengurus';
    }

    /**
     * Check if this is Koordinator Wilayah role
     * 
     * @return bool
     */
    public function isKoordinatorWilayah(): bool
    {
        return $this->getTitle() === 'Koordinator Wilayah';
    }

    /**
     * Check if this is Anggota role
     * 
     * @return bool
     */
    public function isAnggota(): bool
    {
        return $this->getTitle() === 'Anggota';
    }

    /**
     * Check if this is Calon Anggota role
     * 
     * @return bool
     */
    public function isCalonAnggota(): bool
    {
        return $this->getTitle() === 'Calon Anggota';
    }

    /**
     * Check if this is a system role (protected from deletion)
     * System roles: Super Admin, Pengurus, Anggota, Calon Anggota
     * 
     * @return bool
     */
    public function isSystemRole(): bool
    {
        $systemRoles = ['Super Admin', 'Pengurus', 'Anggota', 'Calon Anggota', 'Koordinator Wilayah'];
        return in_array($this->getTitle(), $systemRoles);
    }

    /**
     * Check if this is a custom/user-created role
     * 
     * @return bool
     */
    public function isCustomRole(): bool
    {
        return !$this->isSystemRole();
    }

    /**
     * Check if role can be deleted
     * System roles and roles with users cannot be deleted
     * 
     * @return bool
     */
    public function isDeletable(): bool
    {
        if ($this->isSystemRole()) {
            return false;
        }

        if ($this->getUserCount() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if role can be edited
     * Super Admin role cannot be edited (title & permissions locked)
     * 
     * @return bool
     */
    public function isEditable(): bool
    {
        return !$this->isSuperAdmin();
    }

    // ========================================
    // PERMISSION METHODS
    // ========================================

    /**
     * Get all permissions for this role
     * Returns array of permission objects from joined data or cache
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        // Check cache first
        if ($this->permissionsCache !== null) {
            return $this->permissionsCache;
        }

        // Check if permissions already loaded from joined data
        if (isset($this->attributes['permissions']) && is_array($this->attributes['permissions'])) {
            $this->permissionsCache = $this->attributes['permissions'];
            return $this->permissionsCache;
        }

        // If permission_ids available from joined query
        if (isset($this->attributes['permission_ids'])) {
            $permissionIds = explode(',', $this->attributes['permission_ids']);
            $this->permissionsCache = array_filter($permissionIds);
            return $this->permissionsCache;
        }

        // Return empty array if no data available
        // Actual data should be loaded via RoleModel with withPermissions()
        return [];
    }

    /**
     * Get permission IDs only
     * 
     * @return array
     */
    public function getPermissionIds(): array
    {
        $permissions = $this->getPermissions();

        if (empty($permissions)) {
            return [];
        }

        // If permissions are objects, extract IDs
        if (is_object($permissions[0])) {
            return array_column($permissions, 'id');
        }

        // If already IDs
        return $permissions;
    }

    /**
     * Get permission count
     * 
     * @return int
     */
    public function getPermissionCount(): int
    {
        // Check if count available from joined data
        if (isset($this->attributes['permission_count'])) {
            return (int) $this->attributes['permission_count'];
        }

        return count($this->getPermissions());
    }

    /**
     * Check if role has specific permission
     * 
     * @param string $permission Permission key (e.g., 'member.manage')
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Super Admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->getPermissions();

        if (empty($permissions)) {
            return false;
        }

        // If permissions are objects with 'key' property
        foreach ($permissions as $perm) {
            if (is_object($perm) && isset($perm->key) && $perm->key === $permission) {
                return true;
            }
            if (is_array($perm) && isset($perm['key']) && $perm['key'] === $permission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if role has any of the given permissions
     * 
     * @param array $permissions Array of permission keys
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // Super Admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if role has all of the given permissions
     * 
     * @param array $permissions Array of permission keys
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        // Super Admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get permissions grouped by module
     * 
     * @return array ['module' => [permissions]]
     */
    public function getPermissionsByModule(): array
    {
        $permissions = $this->getPermissions();
        $grouped = [];

        foreach ($permissions as $permission) {
            // Extract module from permission key (e.g., 'member.view' -> 'member')
            $module = 'general';

            if (is_object($permission) && isset($permission->key)) {
                $parts = explode('.', $permission->key);
                $module = $parts[0] ?? 'general';
            } elseif (is_array($permission) && isset($permission['key'])) {
                $parts = explode('.', $permission['key']);
                $module = $parts[0] ?? 'general';
            }

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = $permission;
        }

        return $grouped;
    }

    // ========================================
    // USER METHODS
    // ========================================

    /**
     * Get user count for this role
     * Returns count from joined data or cache
     * 
     * @return int
     */
    public function getUserCount(): int
    {
        // Check cache first
        if ($this->userCountCache !== null) {
            return $this->userCountCache;
        }

        // Check if user_count available from joined data
        if (isset($this->attributes['user_count'])) {
            $this->userCountCache = (int) $this->attributes['user_count'];
            return $this->userCountCache;
        }

        // Return 0 if no data available
        // Actual data should be loaded via RoleModel with withUserCount()
        return 0;
    }

    /**
     * Check if role has users
     * 
     * @return bool
     */
    public function hasUsers(): bool
    {
        return $this->getUserCount() > 0;
    }

    /**
     * Check if role has no users
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->getUserCount() === 0;
    }

    // ========================================
    // DISPLAY METHODS
    // ========================================

    /**
     * Get role badge class for CSS
     * 
     * @return string
     */
    public function getBadgeClass(): string
    {
        return match ($this->getTitle()) {
            'Super Admin' => 'badge-dark',
            'Pengurus' => 'badge-primary',
            'Koordinator Wilayah' => 'badge-info',
            'Anggota' => 'badge-success',
            'Calon Anggota' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get role icon class
     * 
     * @return string
     */
    public function getIconClass(): string
    {
        return match ($this->getTitle()) {
            'Super Admin' => 'fas fa-crown',
            'Pengurus' => 'fas fa-user-shield',
            'Koordinator Wilayah' => 'fas fa-map-marked-alt',
            'Anggota' => 'fas fa-user',
            'Calon Anggota' => 'fas fa-user-clock',
            default => 'fas fa-users',
        };
    }

    /**
     * Get role level/hierarchy
     * Higher number = higher privilege
     * 
     * @return int
     */
    public function getLevel(): int
    {
        return match ($this->getTitle()) {
            'Super Admin' => 100,
            'Pengurus' => 90,
            'Koordinator Wilayah' => 70,
            'Anggota' => 50,
            'Calon Anggota' => 10,
            default => 30,
        };
    }

    /**
     * Check if this role is higher level than another role
     * 
     * @param Role $otherRole
     * @return bool
     */
    public function isHigherThan(Role $otherRole): bool
    {
        return $this->getLevel() > $otherRole->getLevel();
    }

    /**
     * Check if this role is lower level than another role
     * 
     * @param Role $otherRole
     * @return bool
     */
    public function isLowerThan(Role $otherRole): bool
    {
        return $this->getLevel() < $otherRole->getLevel();
    }

    /**
     * Check if this role is same level as another role
     * 
     * @param Role $otherRole
     * @return bool
     */
    public function isSameLevelAs(Role $otherRole): bool
    {
        return $this->getLevel() === $otherRole->getLevel();
    }

    /**
     * Get role summary for display
     * 
     * @return string
     */
    public function getSummary(): string
    {
        $summary = $this->getTitle();

        if ($this->getDescription()) {
            $summary .= ' - ' . $this->getDescription();
        }

        $userCount = $this->getUserCount();
        $permCount = $this->getPermissionCount();

        $summary .= sprintf(
            ' (%d user%s, %d permission%s)',
            $userCount,
            $userCount !== 1 ? 's' : '',
            $permCount,
            $permCount !== 1 ? 's' : ''
        );

        return $summary;
    }

    /**
     * Get role status label
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        if ($this->isSystemRole()) {
            return 'System Role';
        }

        if ($this->hasUsers()) {
            return 'Active';
        }

        return 'Inactive';
    }

    /**
     * Get role status badge class
     * 
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        if ($this->isSystemRole()) {
            return 'badge-info';
        }

        if ($this->hasUsers()) {
            return 'badge-success';
        }

        return 'badge-secondary';
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Convert role to array for JSON response
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'is_system_role' => $this->isSystemRole(),
            'is_super_admin' => $this->isSuperAdmin(),
            'permission_count' => $this->getPermissionCount(),
            'user_count' => $this->getUserCount(),
            'level' => $this->getLevel(),
            'badge_class' => $this->getBadgeClass(),
            'icon_class' => $this->getIconClass(),
            'is_deletable' => $this->isDeletable(),
            'is_editable' => $this->isEditable(),
        ];
    }

    /**
     * Magic getter for better property access
     * 
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        // Custom getters
        if ($key === 'name') {
            return $this->getName();
        }

        if ($key === 'permissions') {
            return $this->getPermissions();
        }

        if ($key === 'user_count') {
            return $this->getUserCount();
        }

        if ($key === 'permission_count') {
            return $this->getPermissionCount();
        }

        // Default Entity behavior
        return parent::__get($key);
    }

    /**
     * Magic toString method
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
