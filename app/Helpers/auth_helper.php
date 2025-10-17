<?php

/**
 * Auth Helper
 * 
 * Helper functions untuk authentication dan authorization
 * 
 * @package App\Helpers
 * @author  SPK Development Team
 * @version 1.0.0
 */

if (!function_exists('current_user')) {
    function current_user()
    {
        if (!auth()->loggedIn()) {
            return null;
        }
        return auth()->user();
    }
}

if (!function_exists('user_id')) {
    function user_id(): ?int
    {
        $user = current_user();
        return $user ? $user->id : null;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return auth()->loggedIn();
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        if (!is_logged_in()) {
            return false;
        }
        $user = current_user();
        if ($user->inGroup('superadmin') || $user->inGroup('Super Admin')) {
            return true;
        }
        return $user->can($permission);
    }
}

if (!function_exists('has_any_permission')) {
    function has_any_permission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (has_permission($permission)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('has_all_permissions')) {
    function has_all_permissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!has_permission($permission)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('normalize_role_key')) {
    function normalize_role_key(string $roleName): string
    {
        $normalized = strtolower($roleName);
        $normalized = preg_replace('/[^a-z0-9]+/u', '', $normalized ?? '');
        return $normalized ?? '';
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool
    {
        if (!is_logged_in()) {
            return false;
        }
        $user = current_user();
        if ($user->inGroup($role)) {
            return true;
        }
        $roleVariants = array_unique([
            $role,
            str_replace(['_', '-'], ' ', $role),
            str_replace([' ', '-'], '_', $role),
            str_replace([' ', '_'], '-', $role)
        ]);
        foreach ($roleVariants as $variant) {
            if ($user->inGroup($variant)) {
                return true;
            }
        }
        $targetKey = normalize_role_key($role);
        foreach (user_roles() as $userRole) {
            if (normalize_role_key($userRole) === $targetKey) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('has_any_role')) {
    function has_any_role(array $roles): bool
    {
        foreach ($roles as $role) {
            if (has_role($role)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('is_superadmin')) {
    function is_superadmin(): bool
    {
        return has_role('superadmin') || has_role('Super Admin');
    }
}

if (!function_exists('is_pengurus')) {
    function is_pengurus(): bool
    {
        return has_role('pengurus') || has_role('Pengurus');
    }
}

if (!function_exists('is_koordinator')) {
    function is_koordinator(): bool
    {
        return has_role('koordinator_wilayah') || has_role('Koordinator Wilayah');
    }
}

if (!function_exists('is_anggota')) {
    function is_anggota(): bool
    {
        return has_role('anggota') || has_role('Anggota');
    }
}

if (!function_exists('user_dashboard_path')) {
    function user_dashboard_path(): string
    {
        if (!is_logged_in()) {
            return '/';
        }
        if (has_role('superadmin') || has_role('Super Admin')) {
            return 'super/dashboard';
        }
        if (has_role('pengurus') || has_role('Pengurus') || has_role('koordinator_wilayah') || has_role('Koordinator Wilayah')) {
            return 'admin/dashboard';
        }
        if (has_role('anggota') || has_role('Anggota')) {
            return 'member/dashboard';
        }
        if (has_role('calon_anggota') || has_role('Calon Anggota')) {
            return 'member/dashboard';
        }
        return '/';
    }
}

if (!function_exists('user_dashboard_url')) {
    function user_dashboard_url(): string
    {
        $path = user_dashboard_path();
        if ($path === '/' || $path === '') {
            return base_url();
        }
        return base_url($path);
    }
}

if (!function_exists('user_roles')) {
    function user_roles(): array
    {
        if (!is_logged_in()) {
            return [];
        }
        $user = current_user();
        $groups = $user->getGroups();
        return array_column($groups, 'group');
    }
}

if (!function_exists('user_permissions')) {
    function user_permissions(): array
    {
        if (!is_logged_in()) {
            return [];
        }
        $user = current_user();
        if ($user->inGroup('superadmin') || $user->inGroup('Super Admin')) {
            $permissionModel = new \App\Models\PermissionModel();
            return $permissionModel->findColumn('name');
        }
        return $user->getPermissions();
    }
}

if (!function_exists('can_access_admin')) {
    function can_access_admin(): bool
    {
        return has_any_role(['superadmin', 'Super Admin', 'pengurus', 'Pengurus', 'koordinator_wilayah', 'Koordinator Wilayah']);
    }
}

if (!function_exists('user_full_name')) {
    function user_full_name(): string
    {
        $user = current_user();
        if (!$user) {
            return 'Guest';
        }
        if (isset($user->member_profile) && !empty($user->member_profile->full_name)) {
            return $user->member_profile->full_name;
        }
        return $user->username ?? 'User';
    }
}

if (!function_exists('user_email')) {
    function user_email(): ?string
    {
        $user = current_user();
        return $user ? $user->email : null;
    }
}

if (!function_exists('user_avatar')) {
    function user_avatar(string $default = ''): string
    {
        $user = current_user();
        if (!$user) {
            return $default ?: base_url('assets/images/avatars/avatar.png');
        }
        if (isset($user->member_profile) && !empty($user->member_profile->photo)) {
            return base_url('uploads/photos/' . $user->member_profile->photo);
        }
        return $default ?: base_url('assets/images/avatars/avatar.png');
    }
}

if (!function_exists('require_permission')) {
    function require_permission(string $permission, string $message = '')
    {
        if (!has_permission($permission)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                $message ?: "Anda tidak memiliki izin '{$permission}' untuk mengakses resource ini."
            );
        }
    }
}

if (!function_exists('require_role')) {
    function require_role(string $role, string $message = '')
    {
        if (!has_role($role)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                $message ?: "Halaman ini hanya dapat diakses oleh '{$role}'."
            );
        }
    }
}