<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PermissionFilter
 * 
 * Filter untuk memeriksa apakah user memiliki permission yang diperlukan
 * Digunakan untuk granular access control di level route
 * 
 * Usage di Routes:
 * $routes->get('admin/members', 'Admin\MemberController::index', ['filter' => 'permission:member.view']);
 * $routes->post('admin/members/delete/(:num)', 'Admin\MemberController::delete/$1', ['filter' => 'permission:member.delete']);
 * 
 * @package App\Filters
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PermissionFilter implements FilterInterface
{
    /**
     * Check if user has required permission before processing request
     *
     * @param RequestInterface $request
     * @param array|null $arguments Permission key(s) required
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

        // Super Admin bypass all permission checks
        // Use getGroups() instead of inGroup() for more reliable check
        if ($user->inGroup('superadmin')) {
            return $request;
        }

        // === TAMBAHKAN DEBUG INI ===
        log_message('debug', 'PermissionFilter - User ID: ' . $user->id);
        log_message('debug', 'PermissionFilter - Required permissions: ' . json_encode($arguments));
        log_message('debug', 'PermissionFilter - User groups: ' . json_encode($user->getGroups()));
        log_message('debug', 'PermissionFilter - inGroup superadmin: ' . ($user->inGroup('superadmin') ? 'YES' : 'NO'));
        // === AKHIR DEBUG ===

        // No permission specified, allow access (just check login)
        if (empty($arguments)) {
            return $request;
        }

        // Get required permission(s) from arguments
        // Can be single permission or array of permissions
        $requiredPermissions = is_array($arguments) ? $arguments : [$arguments];

        // Check if user has at least one of the required permissions
        $hasPermission = false;
        foreach ($requiredPermissions as $permission) {
            if ($user->can($permission)) {
                $hasPermission = true;
                break;
            }
        }

        // User doesn't have required permission
        if (!$hasPermission) {
            // Log unauthorized access attempt
            log_message('warning', sprintf(
                'Unauthorized access attempt by User ID %d to %s. Required permissions: %s',
                $user->id,
                current_url(),
                implode(', ', $requiredPermissions)
            ));

            // Check if AJAX request
            if ($request->isAJAX()) {
                return response()->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melakukan aksi ini.',
                    'error_code' => 'PERMISSION_DENIED'
                ])->setStatusCode(403);
            }

            // Regular request - show error page or redirect
            if (count($requiredPermissions) === 1) {
                $message = "Anda tidak memiliki izin '{$requiredPermissions[0]}' untuk mengakses halaman ini.";
            } else {
                $message = 'Anda tidak memiliki izin yang diperlukan untuk mengakses halaman ini.';
            }

            // Redirect back with error message
            return redirect()->back()
                ->with('error', $message)
                ->with('required_permissions', $requiredPermissions);
        }

        // User has permission, allow request to proceed
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
}
