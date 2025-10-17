<?php

/**
 * Auth Helper Functions
 * 
 * Helper functions untuk authentication dan authorization
 * Mempermudah checking user permissions, roles, dan redirects
 * 
 * 🔧 v2.0.0 - FIXED: Dashboard URL helper with proper role names
 * 
 * @package App\Helpers
 * @author  SPK Development Team
 */

if (!function_exists('current_user')) {
    /**
     * Get current authenticated user
     * 
     * @return object|null
     */
    function current_user()
    {
        return auth()->user();
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

if (!function_exists('user_id')) {
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    function user_id(): ?int
    {
        $user = current_user();
        return $user ? $user->id : null;
    }
}

if (!function_exists('user_email')) {
    /**
     * Get current user email
     * 
     * @return string|null
     */
    function user_email(): ?string
    {
        $user = current_user();
        return $user ? $user->email : null;
    }
}

if (!function_exists('user_name')) {
    /**
     * Get current user display name
     * 
     * @return string
     */
    function user_name(): string
    {
        $user = current_user();

        if (!$user) {
            return 'Guest';
        }

        // Try to get name from profile
        if (isset($user->name) && !empty($user->name)) {
            return $user->name;
        }

        // Fallback to username or email
        return $user->username ?? $user->email ?? 'User';
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if current user has specific permission
     * 
     * @param string $permission Permission key (e.g., 'member.view')
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        $user = current_user();

        if (!$user) {
            return false;
        }

        // Super Admin has all permissions
        if ($user->inGroup('superadmin')) {
            return true;
        }

        return $user->can($permission);
    }
}

if (!function_exists('in_group')) {
    /**
     * Check if current user is in specific group/role
     * 
     * @param string $group Group name (e.g., 'superadmin', 'pengurus')
     * @return bool
     */
    function in_group(string $group): bool
    {
        $user = current_user();

        if (!$user) {
            return false;
        }

        return $user->inGroup($group);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if current user is Super Admin
     * 
     * @return bool
     */
    function is_super_admin(): bool
    {
        return in_group('superadmin');
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if current user is Admin (Pengurus or Super Admin)
     * 
     * @return bool
     */
    function is_admin(): bool
    {
        return in_group('superadmin') || in_group('pengurus') || in_group('koordinator');
    }
}

if (!function_exists('is_member')) {
    /**
     * Check if current user is Member (Anggota)
     * 
     * @return bool
     */
    function is_member(): bool
    {
        return in_group('anggota') || in_group('calon_anggota');
    }
}

if (!function_exists('user_role')) {
    /**
     * Get user's primary role name
     * 
     * @return string
     */
    function user_role(): string
    {
        $user = current_user();

        if (!$user) {
            return 'guest';
        }

        // Priority order
        if ($user->inGroup('superadmin')) return 'Super Admin';
        if ($user->inGroup('pengurus')) return 'Pengurus';
        if ($user->inGroup('koordinator')) return 'Koordinator Wilayah';
        if ($user->inGroup('anggota')) return 'Anggota';
        if ($user->inGroup('calon_anggota')) return 'Calon Anggota';

        return 'member';
    }
}

if (!function_exists('user_dashboard_url')) {
    /**
     * Get user's dashboard URL based on role
     * 
     * 🔧 FIXED: Returns correct dashboard URL for each role
     * 
     * @return string
     */
    function user_dashboard_url(): string
    {
        if (!is_logged_in()) {
            return base_url('/');
        }

        $user = current_user();

        // Return dashboard URL based on role (FIXED: Proper role names)
        if ($user->inGroup('superadmin')) {
            return base_url('/super/dashboard');
        }

        if ($user->inGroup('pengurus') || $user->inGroup('koordinator')) {
            return base_url('/admin/dashboard');
        }

        // Anggota or Calon Anggota
        return base_url('/member/dashboard');
    }
}

if (!function_exists('user_dashboard_path')) {
    /**
     * Get user's dashboard path (without base_url)
     * 
     * @return string
     */
    function user_dashboard_path(): string
    {
        if (!is_logged_in()) {
            return '/';
        }

        $user = current_user();

        // Return dashboard path based on role
        if ($user->inGroup('superadmin')) {
            return '/super/dashboard';
        }

        if ($user->inGroup('pengurus') || $user->inGroup('koordinator')) {
            return '/admin/dashboard';
        }

        // Anggota or Calon Anggota
        return '/member/dashboard';
    }
}

if (!function_exists('can_access')) {
    /**
     * Check if user can access specific module
     * 
     * @param string $module Module name (e.g., 'member', 'survey')
     * @return bool
     */
    function can_access(string $module): bool
    {
        $user = current_user();

        if (!$user) {
            return false;
        }

        // Super Admin can access everything
        if ($user->inGroup('superadmin')) {
            return true;
        }

        // Check if user has at least one permission for the module
        $permissions = [
            "{$module}.view",
            "{$module}.manage",
            "{$module}.create",
        ];

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('redirect_to_dashboard')) {
    /**
     * Redirect to appropriate dashboard based on user role
     * 
     * 🔧 FIXED: Redirects to correct dashboard
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    function redirect_to_dashboard()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        return redirect()->to(user_dashboard_url());
    }
}

if (!function_exists('require_auth')) {
    /**
     * Require authentication, redirect to login if not logged in
     * 
     * @param string|null $redirectUrl Optional redirect URL after login
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    function require_auth(?string $redirectUrl = null)
    {
        if (!is_logged_in()) {
            if ($redirectUrl) {
                session()->set('redirect_url', $redirectUrl);
            }
            return redirect()->to('/login');
        }

        return null;
    }
}

if (!function_exists('require_role')) {
    /**
     * Require specific role, redirect with error if user doesn't have it
     * 
     * @param string|array $roles Required role(s)
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    function require_role($roles)
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $user = current_user();
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if ($user->inGroup($role)) {
                return null;
            }
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}

if (!function_exists('user_avatar')) {
    /**
     * Get user avatar URL
     * 
     * @param int|null $userId User ID (default: current user)
     * @return string
     */
    function user_avatar(?int $userId = null): string
    {
        $user = $userId ? model('UserModel')->find($userId) : current_user();

        if ($user && isset($user->avatar) && !empty($user->avatar)) {
            return base_url('uploads/avatars/' . $user->avatar);
        }

        // Default avatar
        return base_url('assets/img/avatar-default.png');
    }
}

if (!function_exists('format_user_role')) {
    /**
     * Format role name for display
     * 
     * @param string $role Role key
     * @return string
     */
    function format_user_role(string $role): string
    {
        $roles = [
            'superadmin' => 'Super Admin',
            'pengurus' => 'Pengurus',
            'koordinator' => 'Koordinator Wilayah',
            'anggota' => 'Anggota',
            'calon_anggota' => 'Calon Anggota',
        ];

        return $roles[$role] ?? ucfirst($role);
    }
}
