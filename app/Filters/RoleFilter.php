<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter
 * 
 * Filter untuk memeriksa apakah user memiliki role (group) yang diperlukan
 * Digunakan untuk role-based access control di level route
 * 
 * Usage di Routes:
 * $routes->get('admin/dashboard', 'Admin\DashboardController::index', ['filter' => 'role:pengurus,superadmin']);
 * $routes->get('super/roles', 'Super\RoleController::index', ['filter' => 'role:superadmin']);
 * $routes->get('member/profile', 'Member\ProfileController::index', ['filter' => 'role:anggota,pengurus,superadmin']);
 * 
 * Multiple roles can be specified, user needs to have at least one of them
 * 
 * @package App\Filters
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RoleFilter implements FilterInterface
{
    /**
     * Check if user has required role before processing request
     *
     * @param RequestInterface $request
     * @param array|null $arguments Role(s) required
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!auth()->loggedIn()) {
            // Redirect to login page with intended URL
            session()->set('redirect_url', current_url());
            return redirect()->to('/auth/login')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Get current user
        $user = auth()->user();

        // No role specified, just check login (allow access)
        if (empty($arguments)) {
            return $request;
        }

        // Get required role(s) from arguments
        // Can be single role or comma-separated roles
        if (is_array($arguments)) {
            $requiredRoles = $arguments;
        } else {
            // Split by comma if multiple roles provided
            $requiredRoles = array_map('trim', explode(',', $arguments));
        }

        // Check if user has at least one of the required roles
        $hasRole = false;
        $userRoles = [];

        foreach ($requiredRoles as $role) {
            if ($user->inGroup($role)) {
                $hasRole = true;
                break;
            }
        }

        // Get user's current roles for logging
        $groups = $user->getGroups();
        foreach ($groups as $group) {
            $userRoles[] = $group;
        }

        // User doesn't have required role
        if (!$hasRole) {
            // Log unauthorized access attempt
            log_message('warning', sprintf(
                'Unauthorized role access attempt by User ID %d (roles: %s) to %s. Required roles: %s',
                $user->id,
                implode(', ', $userRoles),
                current_url(),
                implode(', ', $requiredRoles)
            ));

            // Check if AJAX request
            if ($request->isAJAX()) {
                return response()->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk halaman ini.',
                    'error_code' => 'ROLE_ACCESS_DENIED'
                ])->setStatusCode(403);
            }

            // Determine redirect based on user's current role
            $redirectUrl = $this->determineRedirectUrl($userRoles);

            // Build error message
            if (count($requiredRoles) === 1) {
                $message = "Halaman ini hanya dapat diakses oleh '{$requiredRoles[0]}'.";
            } else {
                $message = 'Anda tidak memiliki role yang diperlukan untuk mengakses halaman ini.';
            }

            // Redirect to appropriate dashboard with error message
            return redirect()->to($redirectUrl)
                ->with('error', $message)
                ->with('required_roles', $requiredRoles);
        }

        // User has required role, allow request to proceed
        return $request;
    }

    /**
     * Perform any final actions after controller
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
        return $response;
    }

    /**
     * Determine appropriate redirect URL based on user's role
     * 
     * @param array $userRoles User's current roles
     * @return string Redirect URL
     */
    protected function determineRedirectUrl(array $userRoles): string
    {
        // Priority order for redirect
        if (in_array('superadmin', $userRoles)) {
            return '/super/dashboard';
        }

        if (in_array('pengurus', $userRoles)) {
            return '/admin/dashboard';
        }

        if (in_array('koordinator_wilayah', $userRoles)) {
            return '/admin/dashboard';
        }

        if (in_array('anggota', $userRoles)) {
            return '/member/dashboard';
        }

        // Default to member dashboard
        return '/member/dashboard';
    }

    /**
     * Check if user has any of the specified roles
     * Helper method for use in controllers/views
     * 
     * @param array|string $roles Role(s) to check
     * @return bool
     */
    public static function hasAnyRole($roles): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }

        $user = auth()->user();
        $rolesToCheck = is_array($roles) ? $roles : [$roles];

        foreach ($rolesToCheck as $role) {
            if ($user->inGroup($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all specified roles
     * Helper method for use in controllers/views
     * 
     * @param array $roles Roles to check
     * @return bool
     */
    public static function hasAllRoles(array $roles): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if (!$user->inGroup($role)) {
                return false;
            }
        }

        return true;
    }
}
