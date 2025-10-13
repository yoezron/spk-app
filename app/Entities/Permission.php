<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Permission Entity
 * 
 * Representasi object-oriented dari permission
 * Menyediakan business logic methods untuk permission management
 * 
 * @package App\Entities
 * @author  SPK Development Team
 * @version 1.0.0
 */
class Permission extends Entity
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
     * Cache for roles
     */
    protected $rolesCache = null;

    // ========================================
    // BASIC GETTERS
    // ========================================

    /**
     * Get permission ID
     * 
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->attributes['id'];
    }

    /**
     * Get permission key/name
     * Format: module.action (e.g., 'member.view', 'member.manage')
     * 
     * @return string
     */
    public function getKey(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Get permission name (alias for getKey)
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->getKey();
    }

    /**
     * Get permission description
     * 
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    // ========================================
    // MODULE & ACTION METHODS
    // ========================================

    /**
     * Get module name from permission key
     * Extracts the module part before the dot
     * Example: 'member.view' -> 'member'
     * 
     * @return string
     */
    public function getModuleName(): string
    {
        $key = $this->getKey();
        $parts = explode('.', $key);

        return $parts[0] ?? 'general';
    }

    /**
     * Get action name from permission key
     * Extracts the action part after the dot
     * Example: 'member.view' -> 'view'
     * 
     * @return string
     */
    public function getActionName(): string
    {
        $key = $this->getKey();
        $parts = explode('.', $key);

        return $parts[1] ?? '';
    }

    /**
     * Get full module and action names
     * 
     * @return array ['module' => string, 'action' => string]
     */
    public function getParsedKey(): array
    {
        return [
            'module' => $this->getModuleName(),
            'action' => $this->getActionName(),
        ];
    }

    /**
     * Check if permission belongs to specific module
     * 
     * @param string $module Module name
     * @return bool
     */
    public function belongsToModule(string $module): bool
    {
        return $this->getModuleName() === $module;
    }

    /**
     * Check if permission is for specific action
     * 
     * @param string $action Action name (e.g., 'view', 'manage', 'delete')
     * @return bool
     */
    public function isAction(string $action): bool
    {
        return $this->getActionName() === $action;
    }

    // ========================================
    // ROLE METHODS
    // ========================================

    /**
     * Get roles that have this permission
     * Returns array of role objects from joined data or cache
     * 
     * @return array
     */
    public function getRoles(): array
    {
        // Check cache first
        if ($this->rolesCache !== null) {
            return $this->rolesCache;
        }

        // Check if roles already loaded from joined data
        if (isset($this->attributes['roles']) && is_array($this->attributes['roles'])) {
            $this->rolesCache = $this->attributes['roles'];
            return $this->rolesCache;
        }

        // If role_ids available from joined query
        if (isset($this->attributes['role_ids'])) {
            $roleIds = explode(',', $this->attributes['role_ids']);
            $this->rolesCache = array_filter($roleIds);
            return $this->rolesCache;
        }

        // If role_names available from joined query
        if (isset($this->attributes['role_names'])) {
            $roleNames = explode(',', $this->attributes['role_names']);
            $this->rolesCache = array_filter($roleNames);
            return $this->rolesCache;
        }

        // Return empty array if no data available
        return [];
    }

    /**
     * Get role IDs only
     * 
     * @return array
     */
    public function getRoleIds(): array
    {
        $roles = $this->getRoles();

        if (empty($roles)) {
            return [];
        }

        // If roles are objects, extract IDs
        if (is_object($roles[0])) {
            return array_column($roles, 'id');
        }

        // If already IDs
        return $roles;
    }

    /**
     * Get role names only
     * 
     * @return array
     */
    public function getRoleNames(): array
    {
        $roles = $this->getRoles();

        if (empty($roles)) {
            return [];
        }

        // If roles are objects, extract titles
        if (is_object($roles[0])) {
            return array_column($roles, 'title');
        }

        // If already names
        return $roles;
    }

    /**
     * Get role count
     * 
     * @return int
     */
    public function getRoleCount(): int
    {
        // Check if count available from joined data
        if (isset($this->attributes['role_count'])) {
            return (int) $this->attributes['role_count'];
        }

        return count($this->getRoles());
    }

    /**
     * Check if permission is assigned to specific role
     * 
     * @param string $role Role name (e.g., 'Super Admin', 'Pengurus')
     * @return bool
     */
    public function isAssignedToRole(string $role): bool
    {
        $roles = $this->getRoles();

        if (empty($roles)) {
            return false;
        }

        // If roles are objects with 'title' property
        foreach ($roles as $r) {
            if (is_object($r) && isset($r->title) && $r->title === $role) {
                return true;
            }
            if (is_array($r) && isset($r['title']) && $r['title'] === $role) {
                return true;
            }
            // If roles are just strings (names)
            if (is_string($r) && $r === $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if permission is assigned to any roles
     * 
     * @return bool
     */
    public function hasRoles(): bool
    {
        return $this->getRoleCount() > 0;
    }

    /**
     * Check if permission is not assigned to any role
     * 
     * @return bool
     */
    public function isUnassigned(): bool
    {
        return $this->getRoleCount() === 0;
    }

    /**
     * Check if permission is assigned to Super Admin role
     * 
     * @return bool
     */
    public function isAssignedToSuperAdmin(): bool
    {
        return $this->isAssignedToRole('Super Admin');
    }

    // ========================================
    // PERMISSION TYPE METHODS
    // ========================================

    /**
     * Check if this is a view/read permission
     * 
     * @return bool
     */
    public function isViewPermission(): bool
    {
        $action = $this->getActionName();
        return in_array($action, ['view', 'read', 'list', 'show']);
    }

    /**
     * Check if this is a create permission
     * 
     * @return bool
     */
    public function isCreatePermission(): bool
    {
        $action = $this->getActionName();
        return in_array($action, ['create', 'add', 'new']);
    }

    /**
     * Check if this is an update/edit permission
     * 
     * @return bool
     */
    public function isUpdatePermission(): bool
    {
        $action = $this->getActionName();
        return in_array($action, ['update', 'edit', 'modify']);
    }

    /**
     * Check if this is a delete permission
     * 
     * @return bool
     */
    public function isDeletePermission(): bool
    {
        $action = $this->getActionName();
        return in_array($action, ['delete', 'remove', 'destroy']);
    }

    /**
     * Check if this is a manage/admin permission (full access)
     * 
     * @return bool
     */
    public function isManagePermission(): bool
    {
        $action = $this->getActionName();
        return in_array($action, ['manage', 'admin', 'full']);
    }

    /**
     * Check if this is a critical permission (e.g., delete, manage)
     * 
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->isDeletePermission() || $this->isManagePermission();
    }

    /**
     * Get permission level/severity
     * Higher number = more critical
     * 
     * @return int
     */
    public function getLevel(): int
    {
        if ($this->isManagePermission()) {
            return 100;
        }

        if ($this->isDeletePermission()) {
            return 90;
        }

        if ($this->isUpdatePermission()) {
            return 70;
        }

        if ($this->isCreatePermission()) {
            return 50;
        }

        if ($this->isViewPermission()) {
            return 30;
        }

        return 10;
    }

    // ========================================
    // DISPLAY METHODS
    // ========================================

    /**
     * Get human-readable permission name
     * Converts key to readable format
     * Example: 'member.view' -> 'View Member'
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        $module = $this->getModuleName();
        $action = $this->getActionName();

        // Capitalize and format
        $module = ucfirst($module);
        $action = ucfirst($action);

        return $action . ' ' . $module;
    }

    /**
     * Get permission badge class for CSS
     * 
     * @return string
     */
    public function getBadgeClass(): string
    {
        if ($this->isManagePermission()) {
            return 'badge-danger';
        }

        if ($this->isDeletePermission()) {
            return 'badge-warning';
        }

        if ($this->isUpdatePermission()) {
            return 'badge-info';
        }

        if ($this->isCreatePermission()) {
            return 'badge-success';
        }

        if ($this->isViewPermission()) {
            return 'badge-primary';
        }

        return 'badge-secondary';
    }

    /**
     * Get permission icon class
     * 
     * @return string
     */
    public function getIconClass(): string
    {
        if ($this->isManagePermission()) {
            return 'fas fa-crown';
        }

        if ($this->isDeletePermission()) {
            return 'fas fa-trash-alt';
        }

        if ($this->isUpdatePermission()) {
            return 'fas fa-edit';
        }

        if ($this->isCreatePermission()) {
            return 'fas fa-plus';
        }

        if ($this->isViewPermission()) {
            return 'fas fa-eye';
        }

        return 'fas fa-key';
    }

    /**
     * Get permission summary for display
     * 
     * @return string
     */
    public function getSummary(): string
    {
        $summary = $this->getDisplayName();

        if ($this->getDescription()) {
            $summary .= ' - ' . $this->getDescription();
        }

        $roleCount = $this->getRoleCount();

        $summary .= sprintf(
            ' (Assigned to %d role%s)',
            $roleCount,
            $roleCount !== 1 ? 's' : ''
        );

        return $summary;
    }

    /**
     * Get module badge class for CSS
     * 
     * @return string
     */
    public function getModuleBadgeClass(): string
    {
        $module = $this->getModuleName();

        return match ($module) {
            'member' => 'badge-primary',
            'role', 'permission' => 'badge-dark',
            'menu' => 'badge-secondary',
            'content', 'post', 'page' => 'badge-info',
            'forum' => 'badge-purple',
            'survey' => 'badge-success',
            'ticket', 'complaint' => 'badge-warning',
            'finance', 'payment' => 'badge-danger',
            'org', 'organization' => 'badge-indigo',
            'group', 'whatsapp' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    // ========================================
    // COMPARISON METHODS
    // ========================================

    /**
     * Check if this permission is more critical than another
     * 
     * @param Permission $other
     * @return bool
     */
    public function isMoreCriticalThan(Permission $other): bool
    {
        return $this->getLevel() > $other->getLevel();
    }

    /**
     * Check if this permission is less critical than another
     * 
     * @param Permission $other
     * @return bool
     */
    public function isLessCriticalThan(Permission $other): bool
    {
        return $this->getLevel() < $other->getLevel();
    }

    /**
     * Check if permissions are in the same module
     * 
     * @param Permission $other
     * @return bool
     */
    public function isSameModuleAs(Permission $other): bool
    {
        return $this->getModuleName() === $other->getModuleName();
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Check if permission can be deleted
     * System critical permissions cannot be deleted
     * 
     * @return bool
     */
    public function isDeletable(): bool
    {
        // Define critical system permissions that cannot be deleted
        $systemPermissions = [
            'member.view',
            'member.manage',
            'role.view',
            'role.manage',
            'permission.view',
            'permission.manage',
        ];

        return !in_array($this->getKey(), $systemPermissions);
    }

    /**
     * Convert permission to array for JSON response
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'module' => $this->getModuleName(),
            'action' => $this->getActionName(),
            'display_name' => $this->getDisplayName(),
            'role_count' => $this->getRoleCount(),
            'level' => $this->getLevel(),
            'is_critical' => $this->isCritical(),
            'is_view' => $this->isViewPermission(),
            'is_create' => $this->isCreatePermission(),
            'is_update' => $this->isUpdatePermission(),
            'is_delete' => $this->isDeletePermission(),
            'is_manage' => $this->isManagePermission(),
            'badge_class' => $this->getBadgeClass(),
            'icon_class' => $this->getIconClass(),
            'is_deletable' => $this->isDeletable(),
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
        if ($key === 'key') {
            return $this->getKey();
        }

        if ($key === 'module') {
            return $this->getModuleName();
        }

        if ($key === 'action') {
            return $this->getActionName();
        }

        if ($key === 'roles') {
            return $this->getRoles();
        }

        if ($key === 'role_count') {
            return $this->getRoleCount();
        }

        if ($key === 'display_name') {
            return $this->getDisplayName();
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
        return $this->getDisplayName();
    }
}
