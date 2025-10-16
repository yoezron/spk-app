<?php

/**
 * Auth Helper
 * 
 * Helper functions untuk authentication dan authorization
 * Mempermudah checking permissions dan roles di views dan controllers
 * 
 * Load helper di controller: helper('auth');
 * Load helper di view: sudah auto-loaded via Autoload.php
 * 
 * @package App\Helpers
 * @author  SPK Development Team
 * @version 1.0.0
 */

if (!function_exists('current_user')) {
    /**
     * Get current logged in user
     * 
     * @return object|null User object or null if not logged in
     */
    function current_user()
    {
        if (!auth()->loggedIn()) {
            return null;
        }

        return auth()->user();
    }
}

if (!function_exists('user_id')) {
    /**
     * Get current user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    function user_id(): ?int
    {
        $user = current_user();
        return $user ? $user->id : null;
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    function is_logged_in(): bool
    {
        return auth()->loggedIn();
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if current user has specific permission
     * 
     * @param string $permission Permission key
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        if (!is_logged_in()) {
            return false;
        }

        $user = current_user();

        // Super Admin has all permissions
        if ($user->inGroup('superadmin')) {
            return true;
        }

        return $user->can($permission);
    }
}

if (!function_exists('has_any_permission')) {
    /**
     * Check if user has any of the specified permissions
     * 
     * @param array $permissions Array of permission keys
     * @return bool
     */
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
    /**
     * Check if user has all specified permissions
     * 
     * @param array $permissions Array of permission keys
     * @return bool
     */
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
    /**
     * Normalize role names into a comparable key.
     *
     * Menghapus karakter non-alfanumerik dan menurunkan huruf untuk mempermudah perbandingan
     * antar variasi penulisan nama role (mis. "Super Admin", "superadmin", atau "super_admin").
     *
     * @param string $roleName
     * @return string
     */
    function normalize_role_key(string $roleName): string
    {
        $normalized = strtolower($roleName);
        $normalized = preg_replace('/[^a-z0-9]+/u', '', $normalized ?? '');

        return $normalized ?? '';
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if current user has specific role
     * 
     * @param string $role Role name
     * @return bool
     */
    function has_role(string $role): bool
    {
        if (!is_logged_in()) {
            return false;
        }

        
@@ -95,64 +95,112 @@ if (!function_exists('has_any_permission')) {
        }
        }


        return false;
        return false;
    }
    }
}
}


if (!function_exists('has_all_permissions')) {
if (!function_exists('has_all_permissions')) {
    /**
    /**
     * Check if user has all specified permissions
     * Check if user has all specified permissions
     * 
     * 
     * @param array $permissions Array of permission keys
     * @param array $permissions Array of permission keys
     * @return bool
     * @return bool
     */
     */
    function has_all_permissions(array $permissions): bool
    function has_all_permissions(array $permissions): bool
    {
    {
        foreach ($permissions as $permission) {
        foreach ($permissions as $permission) {
            if (!has_permission($permission)) {
            if (!has_permission($permission)) {
                return false;
                return false;
            }
            }
        }
        }


        return true;
        return true;
    }
    }
}
}


if (!function_exists('normalize_role_key')) {
    /**
     * Normalize role names into a comparable key.
     *
     * Menghapus karakter non-alfanumerik dan menurunkan huruf untuk mempermudah perbandingan
     * antar variasi penulisan nama role (mis. "Super Admin", "superadmin", atau "super_admin").
     *
     * @param string $roleName
     * @return string
     */
    function normalize_role_key(string $roleName): string
    {
        $normalized = strtolower($roleName);
        $normalized = preg_replace('/[^a-z0-9]+/u', '', $normalized ?? '');

        return $normalized ?? '';
    }
}

if (!function_exists('has_role')) {
if (!function_exists('has_role')) {
    /**
    /**
     * Check if current user has specific role
     * Check if current user has specific role
     * 
     *
     * @param string $role Role name
     * @param string $role Role name
     * @return bool
     * @return bool
     */
     */
    function has_role(string $role): bool
    function has_role(string $role): bool
    {
    {
        if (!is_logged_in()) {
        if (!is_logged_in()) {
            return false;
            return false;
        }
        }


        return current_user()->inGroup($role);
        $user = current_user();

        // Cek langsung berdasarkan nama yang diberikan
        if ($user->inGroup($role)) {
            return true;
        }

        // Coba beberapa variasi umum (spasi/underscore/hyphen)
        $roleVariants = array_unique([
            $role,
            str_replace(['_', '-'], ' ', $role),
            str_replace([' ', '-'], '_', $role),
            str_replace([' ', '_'], '-', $role),
        ]);

        foreach ($roleVariants as $variant) {
            if ($user->inGroup($variant)) {
                return true;
            }
        }

        // Bandingkan menggunakan bentuk ter-normalisasi
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
    /**
     * Check if user has any of the specified roles
     * 
     * @param array $roles Array of role names
     * @return bool
     */
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
    /**
     * Check if current user is Super Admin
     * 
     * @return bool
     */
    function is_superadmin(): bool
    {
        return has_role('superadmin');
    }
}

if (!function_exists('is_pengurus')) {
    /**
     * Check if current user is Pengurus
     * 
     * @return bool
     */
    function is_pengurus(): bool
    {
        return has_role('pengurus');
    }
}

if (!function_exists('is_koordinator')) {
    /**
     * Check if current user is Koordinator Wilayah
     * 
     * @return bool
     */
    function is_koordinator(): bool
    {
        return has_role('koordinator_wilayah');
    }
}

if (!function_exists('is_anggota')) {
    /**
     * Check if current user is Anggota
     * 
     * @return bool
     */
    function is_anggota(): bool
    {
        return has_role('anggota');
    }
}

if (!function_exists('user_dashboard_path')) {
    /**
     * Get default dashboard path for the current user based on their role
     *
     * @return string
     */
    function user_dashboard_path(): string
    {
        if (!is_logged_in()) {
            return '/';
        }

        // Prioritize Super Admin
        if (has_role('superadmin') || has_role('Super Admin')) {
            return 'super/dashboard';
        }

        // Pengurus level (including koordinator)
        if (
            has_role('pengurus') ||
            has_role('Pengurus') ||
            has_role('koordinator_wilayah') ||
            has_role('Koordinator Wilayah')
        ) {
            return 'admin/dashboard';
        }

        // Anggota aktif
        if (has_role('anggota') || has_role('Anggota')) {
            return 'member/dashboard';
        }

        // Calon anggota diarahkan ke dashboard member juga
        if (has_role('calon_anggota') || has_role('Calon Anggota')) {
            return 'member/dashboard';
        }

        return '/';
    }
}

if (!function_exists('user_dashboard_url')) {
    /**
     * Get default dashboard URL (absolute) for the current user
     *
     * @return string
     */
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
    /**
     * Get current user's roles
     * 
     * @return array Array of role names
     */
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
    /**
     * Get current user's permissions
     * 
     * @return array Array of permission keys
     */
    function user_permissions(): array
    {
        if (!is_logged_in()) {
            return [];
        }

        $user = current_user();

        // Super Admin has all permissions
        if ($user->inGroup('superadmin')) {
            $permissionModel = new \App\Models\PermissionModel();
            return $permissionModel->findColumn('name');
        }

        return $user->getPermissions();
    }
}

if (!function_exists('can_access_admin')) {
    /**
     * Check if user can access admin panel
     * 
     * @return bool
     */
    function can_access_admin(): bool
    {
        return has_any_role(['superadmin', 'pengurus', 'koordinator_wilayah']);
    }
}

if (!function_exists('user_full_name')) {
    /**
     * Get current user's full name
     * 
     * @return string
     */
    function user_full_name(): string
    {
        $user = current_user();

        if (!$user) {
            return 'Guest';
        }

        // Try to get from member profile
        if (isset($user->member_profile) && !empty($user->member_profile->full_name)) {
            return $user->member_profile->full_name;
        }

        // Fallback to username
        return $user->username ?? 'User';
    }
}

if (!function_exists('user_email')) {
    /**
     * Get current user's email
     * 
     * @return string|null
     */
    function user_email(): ?string
    {
        $user = current_user();
        return $user ? $user->email : null;
    }
}

if (!function_exists('user_avatar')) {
    /**
     * Get current user's avatar URL
     * 
     * @param string $default Default avatar URL
     * @return string
     */
    function user_avatar(string $default = ''): string
    {
        $user = current_user();

        if (!$user) {
            return $default ?: base_url('assets/images/avatars/avatar.png');
        }

        // Try to get from member profile
        if (isset($user->member_profile) && !empty($user->member_profile->photo)) {
            return base_url('uploads/photos/' . $user->member_profile->photo);
        }

        // Return default avatar
        return $default ?: base_url('assets/images/avatars/avatar.png');
    }
}

if (!function_exists('require_permission')) {
    /**
     * Require permission or throw exception
     * Use in controllers for strict permission checking
     * 
     * @param string $permission Permission key
     * @param string $message Custom error message
     * @throws \CodeIgniter\Exceptions\PageNotFoundException
     * @return void
     */
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
    /**
     * Require role or throw exception
     * Use in controllers for strict role checking
     * 
     * @param string $role Role name
     * @param string $message Custom error message
     * @throws \CodeIgniter\Exceptions\PageNotFoundException
     * @return void
     */
    function require_role(string $role, string $message = '')
    {
        if (!has_role($role)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                $message ?: "Halaman ini hanya dapat diakses oleh '{$role}'."
            );
        }
    }
}
