<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\OrgUnitModel;
use App\Models\OrgPositionModel;
use App\Models\OrgAssignmentModel;
use App\Models\MemberProfileModel;
use App\Models\UserModel;
use App\Models\ProvinceModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * OrgStructureController (Public)
 * 
 * Controller untuk menampilkan struktur organisasi SPK kepada publik
 * Menyediakan view hierarki organisasi dengan informasi kontak
 * 
 * Features:
 * - Hierarchical organization display
 * - Filter by region/wilayah
 * - Show leadership & positions
 * - Display member photos & contact
 * - Unit detail with job descriptions
 * - Responsive & mobile-friendly
 * 
 * @package App\Controllers\Public
 * @author  SPK Development Team
 * @version 1.0.0
 */
class OrgStructureController extends BaseController
{
    /**
     * @var OrgUnitModel
     */
    protected $unitModel;

    /**
     * @var OrgPositionModel
     */
    protected $positionModel;

    /**
     * @var OrgAssignmentModel
     */
    protected $assignmentModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->unitModel = new OrgUnitModel();
        $this->positionModel = new OrgPositionModel();
        $this->assignmentModel = new OrgAssignmentModel();
        $this->memberModel = new MemberProfileModel();
    }

    // =====================================================
    // PUBLIC PAGES
    // =====================================================

    /**
     * Display public organizational structure
     * Hierarchical view dengan filter wilayah
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        try {
            // Get filters from query string
            $scope = $this->request->getGet('scope') ?? 'pusat';
            $regionId = $this->request->getGet('region_id');

            // Build filters
            $filters = [
                'scope' => $scope,
                'is_active' => 1
            ];

            if ($regionId) {
                $filters['region_id'] = $regionId;
            }

            // Get hierarchy
            $hierarchy = $this->unitModel->getHierarchy($filters);

            // Enrich hierarchy with positions and members
            $hierarchy = $this->enrichHierarchyWithMembers($hierarchy);

            // Get provinces for filter
            $provinceModel = new ProvinceModel();
            $provinces = $provinceModel->orderBy('name', 'ASC')->findAll();

            // Get statistics
            $stats = [
                'total_units' => $this->unitModel->where('is_active', 1)->countAllResults(),
                'total_positions' => $this->positionModel->where('is_active', 1)->countAllResults(),
                'total_members' => $this->assignmentModel->where('status', 'active')->countAllResults()
            ];

            $data = [
                'title' => 'Struktur Organisasi - Serikat Pekerja Kampus',
                'hierarchy' => $hierarchy,
                'provinces' => $provinces,
                'stats' => $stats,
                'current_scope' => $scope,
                'current_region' => $regionId
            ];

            return view('public/org_structure/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in Public OrgStructureController::index: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }

    /**
     * Show unit detail with positions and members
     * 
     * @param string $slug Unit slug
     * @return string|ResponseInterface
     */
    public function detail(string $slug)
    {
        try {
            // Find unit by slug
            $unit = $this->unitModel->where('slug', $slug)
                ->where('is_active', 1)
                ->first();

            if (!$unit) {
                return view('errors/html/error_404');
            }

            // Get full unit data
            $unit = $this->unitModel->getFullUnit($unit['id']);

            // Get positions with assignments and member details
            $positions = $this->positionModel->getUnitPositionsWithAssignments($unit['id'], true);

            // Enrich positions with full member data
            foreach ($positions as &$position) {
                foreach ($position['assignments'] as &$assignment) {
                    if (isset($assignment['member'])) {
                        // Add photo URL
                        if (!empty($assignment['member']['foto_path'])) {
                            $assignment['member']['photo_url'] = base_url('uploads/members/' . $assignment['member']['foto_path']);
                        } else {
                            $assignment['member']['photo_url'] = base_url('assets/images/default-avatar.png');
                        }
                    }
                }
            }

            // Get breadcrumb
            $breadcrumb = $this->unitModel->getBreadcrumb($unit['id']);

            // Get region info if applicable
            if ($unit['region_id']) {
                $provinceModel = new ProvinceModel();
                $unit['region'] = $provinceModel->find($unit['region_id']);
            }

            $data = [
                'title' => $unit['name'] . ' - Struktur Organisasi SPK',
                'unit' => $unit,
                'positions' => $positions,
                'breadcrumb' => $breadcrumb
            ];

            return view('public/org_structure/detail', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in detail: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }

    /**
     * Show position detail (for SEO or direct links)
     * 
     * @param string $unitSlug Unit slug
     * @param string $positionSlug Position slug
     * @return string|ResponseInterface
     */
    public function positionDetail(string $unitSlug, string $positionSlug)
    {
        try {
            // Find unit
            $unit = $this->unitModel->where('slug', $unitSlug)
                ->where('is_active', 1)
                ->first();

            if (!$unit) {
                return view('errors/html/error_404');
            }

            // Find position
            $position = $this->positionModel->where('slug', $positionSlug)
                ->where('unit_id', $unit['id'])
                ->where('is_active', 1)
                ->first();

            if (!$position) {
                return view('errors/html/error_404');
            }

            // Get full position with assignments
            $position = $this->positionModel->withAssignments($position['id'], true);

            // Enrich with member details
            foreach ($position['assignments'] as &$assignment) {
                if (isset($assignment['member'])) {
                    // Add photo URL
                    if (!empty($assignment['member']['foto_path'])) {
                        $assignment['member']['photo_url'] = base_url('uploads/members/' . $assignment['member']['foto_path']);
                    } else {
                        $assignment['member']['photo_url'] = base_url('assets/images/default-avatar.png');
                    }
                }
            }

            // Get unit info
            $position['unit'] = $unit;

            // Parse responsibilities and authorities
            $position['responsibilities_array'] = $this->positionModel->getResponsibilities($position['id']);
            $position['authorities_array'] = $this->positionModel->getAuthorities($position['id']);

            $data = [
                'title' => $position['title'] . ' - ' . $unit['name'],
                'position' => $position,
                'unit' => $unit
            ];

            return view('public/org_structure/position_detail', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in positionDetail: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }

    /**
     * Show organizational chart (visual hierarchy)
     * 
     * @return string|ResponseInterface
     */
    public function chart()
    {
        try {
            // Get pusat level units
            $hierarchy = $this->unitModel->getHierarchy([
                'scope' => 'pusat',
                'is_active' => 1
            ]);

            // Enrich with members for chart display
            $hierarchy = $this->enrichHierarchyWithMembers($hierarchy);

            $data = [
                'title' => 'Bagan Organisasi - Serikat Pekerja Kampus',
                'hierarchy' => $hierarchy
            ];

            return view('public/org_structure/chart', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in chart: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }

    /**
     * Show leadership team (executive positions)
     * 
     * @return string|ResponseInterface
     */
    public function leadership()
    {
        try {
            // Get executive positions
            $positions = $this->positionModel
                ->where('position_type', 'executive')
                ->where('is_leadership', 1)
                ->where('is_active', 1)
                ->orderBy('display_order', 'ASC')
                ->findAll();

            // Get assignments with member details
            $userModel = new UserModel();
            foreach ($positions as &$position) {
                $assignments = $this->assignmentModel
                    ->where('position_id', $position['id'])
                    ->where('status', 'active')
                    ->findAll();

                foreach ($assignments as &$assignment) {
                    $user = $userModel->find($assignment['user_id']);
                    if ($user) {
                        $assignment['user'] = $user;
                        $member = $this->memberModel->where('user_id', $user->id)->first();
                        if ($member) {
                            $assignment['member'] = $member;

                            // Add photo URL
                            if (!empty($member['foto_path'])) {
                                $assignment['member']['photo_url'] = base_url('uploads/members/' . $member['foto_path']);
                            } else {
                                $assignment['member']['photo_url'] = base_url('assets/images/default-avatar.png');
                            }
                        }
                    }
                }

                $position['assignments'] = $assignments;

                // Get unit
                $position['unit'] = $this->unitModel->find($position['unit_id']);
            }

            $data = [
                'title' => 'Pimpinan SPK - Struktur Organisasi',
                'positions' => $positions
            ];

            return view('public/org_structure/leadership', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in leadership: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }

    /**
     * Show regional structure (by province)
     * 
     * @param int $provinceId Province ID
     * @return string|ResponseInterface
     */
    public function regional(int $provinceId)
    {
        try {
            // Get province
            $provinceModel = new ProvinceModel();
            $province = $provinceModel->find($provinceId);

            if (!$province) {
                return view('errors/html/error_404');
            }

            // Get regional hierarchy
            $hierarchy = $this->unitModel->getHierarchy([
                'scope' => 'wilayah',
                'region_id' => $provinceId,
                'is_active' => 1
            ]);

            // Enrich with members
            $hierarchy = $this->enrichHierarchyWithMembers($hierarchy);

            $data = [
                'title' => 'Struktur Organisasi - ' . $province['name'],
                'province' => $province,
                'hierarchy' => $hierarchy
            ];

            return view('public/org_structure/regional', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in regional: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Enrich hierarchy with positions and member details
     * 
     * @param array $hierarchy Hierarchy array
     * @return array Enriched hierarchy
     */
    protected function enrichHierarchyWithMembers(array $hierarchy): array
    {
        $userModel = new UserModel();

        foreach ($hierarchy as &$unit) {
            // Get positions for this unit
            $positions = $this->positionModel
                ->where('unit_id', $unit['id'])
                ->where('is_active', 1)
                ->orderBy('display_order', 'ASC')
                ->findAll();

            foreach ($positions as &$position) {
                // Get active assignments
                $assignments = $this->assignmentModel
                    ->where('position_id', $position['id'])
                    ->where('status', 'active')
                    ->findAll();

                foreach ($assignments as &$assignment) {
                    $user = $userModel->find($assignment['user_id']);
                    if ($user) {
                        $assignment['user'] = $user;

                        $member = $this->memberModel->where('user_id', $user->id)->first();
                        if ($member) {
                            $assignment['member'] = $member;

                            // Add photo URL
                            if (!empty($member['foto_path'])) {
                                $assignment['member']['photo_url'] = base_url('uploads/members/' . $member['foto_path']);
                            } else {
                                $assignment['member']['photo_url'] = base_url('assets/images/default-avatar.png');
                            }

                            // Sanitize contact info for public display
                            if (!empty($member['phone_wa'])) {
                                $assignment['member']['phone_display'] = $this->maskPhone($member['phone_wa']);
                            }
                            if (!empty($user->email)) {
                                $assignment['member']['email_display'] = $this->maskEmail($user->email);
                            }
                        }
                    }
                }

                $position['assignments'] = $assignments;
            }

            $unit['positions'] = $positions;

            // Recursively enrich children
            if (!empty($unit['children'])) {
                $unit['children'] = $this->enrichHierarchyWithMembers($unit['children']);
            }
        }

        return $hierarchy;
    }

    /**
     * Mask phone number for privacy (show only partial)
     * Example: 081234567890 -> 0812****7890
     * 
     * @param string $phone Phone number
     * @return string Masked phone
     */
    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 8) {
            return $phone;
        }

        $prefix = substr($phone, 0, 4);
        $suffix = substr($phone, -4);
        $masked = str_repeat('*', $length - 8);

        return $prefix . $masked . $suffix;
    }

    /**
     * Mask email for privacy
     * Example: john.doe@example.com -> jo******@example.com
     * 
     * @param string $email Email address
     * @return string Masked email
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        if (strlen($username) <= 2) {
            return $email;
        }

        $prefix = substr($username, 0, 2);
        $masked = str_repeat('*', strlen($username) - 2);

        return $prefix . $masked . '@' . $domain;
    }

    // =====================================================
    // AJAX ENDPOINTS
    // =====================================================

    /**
     * Get unit data for modal/popup (AJAX)
     * 
     * @param int $unitId Unit ID
     * @return ResponseInterface
     */
    public function getUnitData(int $unitId)
    {
        try {
            $unit = $this->unitModel->getFullUnit($unitId);

            if (!$unit) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ]);
            }

            // Get positions with assignments
            $positions = $this->positionModel->getUnitPositionsWithAssignments($unitId, true);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'unit' => $unit,
                    'positions' => $positions
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Search organizational structure (AJAX)
     * 
     * @return ResponseInterface
     */
    public function search()
    {
        try {
            $keyword = $this->request->getGet('q');

            if (empty($keyword)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Keyword tidak boleh kosong'
                ]);
            }

            // Search units
            $units = $this->unitModel->search($keyword, ['is_active' => 1]);

            // Search positions
            $positions = $this->positionModel->search($keyword, ['is_active' => 1]);

            // Search members
            $members = $this->memberModel
                ->like('full_name', $keyword)
                ->where('membership_status', 'anggota')
                ->limit(10)
                ->findAll();

            // Get their assignments
            foreach ($members as &$member) {
                $assignments = $this->assignmentModel
                    ->where('user_id', $member['user_id'])
                    ->where('status', 'active')
                    ->findAll();

                foreach ($assignments as &$assignment) {
                    $assignment['position'] = $this->positionModel->withUnit($assignment['position_id']);
                }

                $member['assignments'] = $assignments;
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'units' => $units,
                    'positions' => $positions,
                    'members' => $members
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get hierarchy data for chart visualization (AJAX)
     * 
     * @return ResponseInterface
     */
    public function getChartData()
    {
        try {
            $scope = $this->request->getGet('scope') ?? 'pusat';
            $regionId = $this->request->getGet('region_id');

            $filters = [
                'scope' => $scope,
                'is_active' => 1
            ];

            if ($regionId) {
                $filters['region_id'] = $regionId;
            }

            $hierarchy = $this->unitModel->getHierarchy($filters);
            $hierarchy = $this->enrichHierarchyWithMembers($hierarchy);

            // Transform to chart-friendly format (for org chart libraries)
            $chartData = $this->transformToChartData($hierarchy);

            return $this->response->setJSON([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Transform hierarchy to chart data format
     * 
     * @param array $hierarchy Hierarchy array
     * @return array Chart data
     */
    protected function transformToChartData(array $hierarchy): array
    {
        $chartData = [];

        foreach ($hierarchy as $unit) {
            $node = [
                'id' => $unit['id'],
                'name' => $unit['name'],
                'title' => $unit['name'],
                'positions' => $unit['positions'] ?? [],
                'children' => []
            ];

            if (!empty($unit['children'])) {
                $node['children'] = $this->transformToChartData($unit['children']);
            }

            $chartData[] = $node;
        }

        return $chartData;
    }
}
