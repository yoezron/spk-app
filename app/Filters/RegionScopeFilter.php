<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RegionScopeService;

/**
 * RegionScopeFilter
 * 
 * Filter untuk membatasi akses data berdasarkan wilayah (province)
 * Khusus untuk Koordinator Wilayah yang hanya boleh mengakses data di wilayahnya
 * Super Admin dan Pengurus memiliki akses ke semua wilayah
 * 
 * Usage di Routes:
 * $routes->get('admin/members', 'Admin\MemberController::index', ['filter' => 'region']);
 * $routes->get('admin/wa-groups', 'Admin\WAGroupController::index', ['filter' => 'region']);
 * 
 * Usage di Controller:
 * // Get scoped query builder
 * $builder = service('regionScope')->applyScope($this->memberModel->builder());
 * 
 * @package App\Filters
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegionScopeFilter implements FilterInterface
{
    /**
     * @var RegionScopeService
     */
    protected $regionScopeService;

    /**
     * Constructor - Initialize RegionScopeService
     */
    public function __construct()
    {
        $this->regionScopeService = new RegionScopeService();
    }

    /**
     * Apply regional scope restrictions before processing request
     * Store scope information in session for use in controllers
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!auth()->loggedIn()) {
            session()->set('redirect_url', current_url());
            return redirect()->to('/auth/login')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Get current user
        $user = auth()->user();

        // Determine user's regional scope
        $scopeInfo = $this->determineScopeInfo($user);

        // Store scope info in session for controller use
        session()->set('region_scope', $scopeInfo);

        // Log regional access for audit
        if ($scopeInfo['is_restricted']) {
            log_message('info', sprintf(
                'Regional scope applied for User ID %d (%s) - Province IDs: %s',
                $user->id,
                $scopeInfo['role'],
                implode(', ', $scopeInfo['province_ids'])
            ));
        }

        // Allow request to proceed (scope will be applied in controller)
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
        // Clear scope info from session after request
        session()->remove('region_scope');

        return $response;
    }

    /**
     * Determine regional scope information for user
     * 
     * @param object $user User object
     * @return array Scope information
     */
    protected function determineScopeInfo($user): array
    {
        // Super Admin and Pengurus have access to all regions
        if ($user->inGroup('superadmin') || $user->inGroup('pengurus')) {
            return [
                'is_restricted' => false,
                'role' => $user->inGroup('superadmin') ? 'superadmin' : 'pengurus',
                'province_ids' => [],
                'has_all_access' => true
            ];
        }

        // Koordinator Wilayah - restricted to assigned provinces
        if ($user->inGroup('koordinator')) {
            // Get assigned provinces from user's member profile
            $provinceIds = $this->regionScopeService->getUserProvinceIds($user->id);

            return [
                'is_restricted' => true,
                'role' => 'koordinator',
                'province_ids' => $provinceIds,
                'has_all_access' => false
            ];
        }

        // Regular Anggota - only their own data
        return [
            'is_restricted' => true,
            'role' => 'anggota',
            'province_ids' => [],
            'has_all_access' => false,
            'user_id' => $user->id
        ];
    }

    /**
     * Check if user can access specific province data
     * 
     * @param int $provinceId Province ID to check
     * @return bool
     */
    public function canAccessProvince(int $provinceId): bool
    {
        $scopeInfo = session()->get('region_scope');

        // No scope info, deny access
        if (!$scopeInfo) {
            return false;
        }

        // Has all access (Super Admin / Pengurus)
        if ($scopeInfo['has_all_access']) {
            return true;
        }

        // Check if province is in allowed list
        return in_array($provinceId, $scopeInfo['province_ids']);
    }

    /**
     * Get current user's accessible province IDs
     * 
     * @return array Array of province IDs
     */
    public function getAccessibleProvinceIds(): array
    {
        $scopeInfo = session()->get('region_scope');

        if (!$scopeInfo) {
            return [];
        }

        // Return all if has full access
        if ($scopeInfo['has_all_access']) {
            // Return all province IDs from database
            $provinceModel = new \App\Models\ProvinceModel();
            return $provinceModel->select('id')->findColumn('id');
        }

        return $scopeInfo['province_ids'];
    }
}
