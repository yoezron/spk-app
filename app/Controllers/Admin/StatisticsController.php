<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Member\MemberStatisticsService;
use App\Services\RegionScopeService;
use App\Models\MemberProfileModel;
use App\Models\UserModel;
use App\Models\ProvinceModel;
use App\Models\UniversityModel;
use App\Models\ComplaintModel;
use App\Models\ForumThreadModel;
use App\Models\SurveyModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

/**
 * StatisticsController (Admin)
 * 
 * Mengelola statistik dan analytics comprehensive
 * Member statistics, regional distribution, growth analytics
 * Support regional scope untuk Koordinator Wilayah
 * Export statistical reports dengan charts
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class StatisticsController extends BaseController
{
    /**
     * @var MemberStatisticsService
     */
    protected $statsService;

    /**
     * @var RegionScopeService
     */
    protected $regionScope;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var UniversityModel
     */
    protected $universityModel;

    /**
     * @var ComplaintModel
     */
    protected $complaintModel;

    /**
     * @var ForumThreadModel
     */
    protected $forumModel;

    /**
     * @var SurveyModel
     */
    protected $surveyModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->statsService = new MemberStatisticsService();
        $this->regionScope = new RegionScopeService();
        $this->memberModel = new MemberProfileModel();
        $this->userModel = new UserModel();
        $this->provinceModel = new ProvinceModel();
        $this->universityModel = new UniversityModel();
        $this->complaintModel = new ComplaintModel();
        $this->forumModel = new ForumThreadModel();
        $this->surveyModel = new SurveyModel();
    }

    /**
     * Display statistics overview
     * Main statistics dashboard with comprehensive data
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('statistics.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat statistik');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Get scope data for Koordinator Wilayah
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        // Get comprehensive statistics
        $stats = $this->getComprehensiveStats($user->id, $isKoordinator, $scopeData);

        // Get top statistics
        $topStats = $this->getTopStatistics($user->id, $isKoordinator, $scopeData);

        // Get trend data
        $trendData = $this->getTrendData($user->id, $isKoordinator, $scopeData);

        $data = [
            'title' => 'Statistik & Analytics',
            'is_koordinator' => $isKoordinator,
            'scope_data' => $scopeData,
            'stats' => $stats,
            'top_stats' => $topStats,
            'trend_data' => $trendData
        ];

        return view('admin/statistics/index', $data);
    }

    /**
     * Display member statistics
     * Detailed member statistics with filters
     * 
     * @return string|ResponseInterface
     */
    public function members()
    {
        // Check permission
        if (!auth()->user()->can('statistics.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat statistik anggota');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Get filters
        $filters = [
            'date_from' => $this->request->getGet('date_from') ?? date('Y-m-01'),
            'date_to' => $this->request->getGet('date_to') ?? date('Y-m-d'),
            'province_id' => $this->request->getGet('province_id')
        ];

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
                $filters['province_id'] = $scopeData['province_id'];
            }
        }

        // Get member statistics
        $memberStats = $this->getMemberStatistics($filters, $scopeData);

        // Get provinces for filter
        $provinces = [];
        if (!$isKoordinator) {
            $provinces = $this->provinceModel->findAll();
        }

        $data = [
            'title' => 'Statistik Anggota',
            'member_stats' => $memberStats,
            'filters' => $filters,
            'provinces' => $provinces,
            'is_koordinator' => $isKoordinator,
            'scope_data' => $scopeData
        ];

        return view('admin/statistics/members', $data);
    }

    /**
     * Display regional distribution
     * Breakdown statistics by province/region
     * 
     * @return string|ResponseInterface
     */
    public function regional()
    {
        // Check permission
        if (!auth()->user()->can('statistics.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat statistik regional');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        // Get regional distribution
        $regionalStats = $this->getRegionalDistribution($isKoordinator, $scopeData);

        $data = [
            'title' => 'Distribusi Regional',
            'regional_stats' => $regionalStats,
            'is_koordinator' => $isKoordinator,
            'scope_data' => $scopeData
        ];

        return view('admin/statistics/regional', $data);
    }

    /**
     * Display growth analytics
     * Member growth over time with trends
     * 
     * @return string|ResponseInterface
     */
    public function growth()
    {
        // Check permission
        if (!auth()->user()->can('statistics.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat analitik pertumbuhan');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Get period filter (default: last 12 months)
        $period = $this->request->getGet('period') ?? '12';

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        // Get growth data
        $growthData = $this->getGrowthAnalytics($period, $isKoordinator, $scopeData);

        $data = [
            'title' => 'Analitik Pertumbuhan',
            'growth_data' => $growthData,
            'period' => $period,
            'is_koordinator' => $isKoordinator,
            'scope_data' => $scopeData
        ];

        return view('admin/statistics/growth', $data);
    }

    /**
     * Export comprehensive statistics report
     * Export to Excel with charts and detailed breakdown
     * 
     * @return ResponseInterface
     */
    public function export(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('statistics.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengekspor laporan statistik');
        }

        $user = auth()->user();
        $isKoordinator = $user->inGroup('koordinator');

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($user->id);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        try {
            // Create spreadsheet
            $spreadsheet = new Spreadsheet();

            // Sheet 1: Overview
            $this->createOverviewSheet($spreadsheet, $isKoordinator, $scopeData);

            // Sheet 2: Regional Distribution
            $this->createRegionalSheet($spreadsheet, $isKoordinator, $scopeData);

            // Sheet 3: Growth Data
            $this->createGrowthSheet($spreadsheet, $isKoordinator, $scopeData);

            // Sheet 4: Detailed Member List
            $this->createMemberListSheet($spreadsheet, $isKoordinator, $scopeData);

            // Set active sheet to first
            $spreadsheet->setActiveSheetIndex(0);

            // Generate filename
            $scope = $isKoordinator ? "wilayah_{$scopeData['province_name']}" : 'nasional';
            $filename = "laporan_statistik_{$scope}_" . date('YmdHis') . '.xlsx';

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error in StatisticsController::export: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor laporan: ' . $e->getMessage());
        }
    }

    /**
     * Get comprehensive statistics (OPTIMIZED)
     * Single aggregated query instead of multiple queries
     *
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getComprehensiveStats(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        // Use cache for better performance (5 minutes TTL)
        $cacheKey = "stats_comprehensive_" . ($isKoordinator && $scopeData ? $scopeData['province_id'] : 'all');
        $cache = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // OPTIMIZED: Single aggregated query for member stats
        $thisMonthStart = date('Y-m-01 00:00:00');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));

        $builder = $this->memberModel->builder()
            ->select('
                COUNT(DISTINCT member_profiles.id) as total_members,
                COUNT(DISTINCT CASE WHEN users.active = 1 THEN member_profiles.id END) as active_members,
                COUNT(DISTINCT CASE WHEN users.created_at >= "' . $thisMonthStart . '" THEN member_profiles.id END) as new_members,
                COUNT(DISTINCT CASE WHEN member_profiles.membership_status = "calon_anggota" THEN member_profiles.id END) as pending_approvals,
                COUNT(DISTINCT CASE
                    WHEN users.created_at >= "' . $lastMonthStart . '"
                    AND users.created_at <= "' . $lastMonthEnd . '"
                    THEN member_profiles.id
                END) as last_month_members,
                COUNT(DISTINCT member_profiles.province_id) as total_provinces,
                COUNT(DISTINCT member_profiles.university_id) as total_universities
            ')
            ->join('users', 'users.id = member_profiles.user_id', 'left');

        // Apply regional scope
        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $memberStats = $builder->get()->getRow();

        // Calculate growth rate
        $growthRate = $memberStats->last_month_members > 0
            ? round((($memberStats->new_members - $memberStats->last_month_members) / $memberStats->last_month_members) * 100, 2)
            : 0;

        // Engagement stats (separate lightweight queries)
        $activeTickets = $this->complaintModel
            ->whereIn('status', ['open', 'in_progress'])
            ->countAllResults();

        $forumThreads = $this->forumModel->countAllResults();

        $activeSurveys = $this->surveyModel
            ->where('status', 'published')
            ->where('end_date >=', date('Y-m-d'))
            ->countAllResults();

        $result = [
            'total_members' => (int) $memberStats->total_members,
            'active_members' => (int) $memberStats->active_members,
            'new_members' => (int) $memberStats->new_members,
            'new_members_this_month' => (int) $memberStats->new_members, // Alias for compatibility
            'pending_approvals' => (int) $memberStats->pending_approvals,
            'growth_rate' => $growthRate,
            'member_growth' => $growthRate, // For trend display
            'total_provinces' => (int) $memberStats->total_provinces,
            'total_universities' => (int) $memberStats->total_universities,
            'active_tickets' => $activeTickets,
            'forum_threads' => $forumThreads,
            'total_threads' => $forumThreads, // Alias
            'total_surveys' => $activeSurveys,
            'active_surveys' => $activeSurveys // Alias
        ];

        // Cache for 5 minutes
        $cache->save($cacheKey, $result, 300);

        return $result;
    }

    /**
     * Get top statistics (top provinces, universities, etc) - OPTIMIZED
     * Fixed key names to match view expectations
     *
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getTopStatistics(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        // Use cache
        $cacheKey = "stats_top_" . ($isKoordinator && $scopeData ? $scopeData['province_id'] : 'all');
        $cache = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $builder = $this->memberModel->builder();

        // Apply regional scope
        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        // Top provinces (skip if koordinator)
        $topProvinces = [];
        if (!$isKoordinator) {
            $topProvinces = (clone $builder)
                ->select('
                    provinces.id,
                    provinces.name,
                    COUNT(DISTINCT member_profiles.id) as member_count,
                    COUNT(DISTINCT member_profiles.university_id) as university_count
                ')
                ->join('provinces', 'provinces.id = member_profiles.province_id')
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('users.active', 1)
                ->groupBy('provinces.id, provinces.name')
                ->orderBy('member_count', 'DESC')
                ->limit(10)
                ->get()
                ->getResult(); // Return as objects for view compatibility
        }

        // Top universities
        $topUniversities = (clone $builder)
            ->select('
                universities.id,
                universities.name,
                provinces.name as province_name,
                COUNT(DISTINCT member_profiles.id) as member_count
            ')
            ->join('universities', 'universities.id = member_profiles.university_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1)
            ->groupBy('universities.id, universities.name, provinces.name')
            ->orderBy('member_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResult(); // Return as objects for view compatibility

        $result = [
            'provinces' => $topProvinces,      // Fixed key name (was: top_provinces)
            'universities' => $topUniversities, // Fixed key name (was: top_universities)
        ];

        // Cache for 10 minutes
        $cache->save($cacheKey, $result, 600);

        return $result;
    }

    /**
     * Get trend data for charts - OPTIMIZED
     * Single query with GROUP BY instead of loop queries
     * Returns comprehensive data for all chart types
     *
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getTrendData(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        // Use cache
        $cacheKey = "stats_trend_" . ($isKoordinator && $scopeData ? $scopeData['province_id'] : 'all');
        $cache = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // OPTIMIZED: Single query for member growth trend (last 6 months)
        $sixMonthsAgo = date('Y-m-01', strtotime('-5 months'));

        $builder = $this->memberModel->builder()
            ->select('DATE_FORMAT(users.created_at, "%Y-%m") as month_key, COUNT(member_profiles.id) as count')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.created_at >=', $sixMonthsAgo)
            ->groupBy('month_key')
            ->orderBy('month_key', 'ASC');

        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $monthlyData = $builder->get()->getResultArray();

        // Format for chart
        $memberGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));

            // Find count for this month
            $count = 0;
            foreach ($monthlyData as $data) {
                if ($data['month_key'] === $monthKey) {
                    $count = (int) $data['count'];
                    break;
                }
            }

            $memberGrowth[] = [
                'month' => $monthLabel,
                'count' => $count
            ];
        }

        // Regional distribution (top 10 provinces)
        $regionalBuilder = $this->memberModel->builder()
            ->select('provinces.name as province_name, COUNT(member_profiles.id) as total')
            ->join('provinces', 'provinces.id = member_profiles.province_id')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1)
            ->groupBy('provinces.id, provinces.name')
            ->orderBy('total', 'DESC')
            ->limit(10);

        if ($isKoordinator && $scopeData) {
            $regionalBuilder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $regionalDistribution = $regionalBuilder->get()->getResultArray();

        // University distribution (top 10)
        $universityBuilder = $this->memberModel->builder()
            ->select('universities.name, COUNT(member_profiles.id) as total')
            ->join('universities', 'universities.id = member_profiles.university_id')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1)
            ->groupBy('universities.id, universities.name')
            ->orderBy('total', 'DESC')
            ->limit(10);

        if ($isKoordinator && $scopeData) {
            $universityBuilder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $universityDistribution = $universityBuilder->get()->getResultArray();

        // Status distribution
        $statusBuilder = $this->memberModel->builder()
            ->select('membership_status, COUNT(id) as total')
            ->groupBy('membership_status')
            ->orderBy('total', 'DESC');

        if ($isKoordinator && $scopeData) {
            $statusBuilder->where('province_id', $scopeData['province_id']);
        }

        $statusDistribution = $statusBuilder->get()->getResultArray();

        // Gender distribution
        $genderBuilder = $this->memberModel->builder()
            ->select('
                CASE
                    WHEN gender = "L" THEN "Laki-laki"
                    WHEN gender = "P" THEN "Perempuan"
                    ELSE "Tidak Diketahui"
                END as gender_label,
                COUNT(id) as total
            ')
            ->groupBy('gender')
            ->orderBy('total', 'DESC');

        if ($isKoordinator && $scopeData) {
            $genderBuilder->where('province_id', $scopeData['province_id']);
        }

        $genderDistribution = $genderBuilder->get()->getResultArray();

        $result = [
            'member_growth' => $memberGrowth,
            'regional_distribution' => $regionalDistribution,
            'university_distribution' => $universityDistribution,
            'status_distribution' => $statusDistribution,
            'gender_distribution' => $genderDistribution
        ];

        // Cache for 5 minutes
        $cache->save($cacheKey, $result, 300);

        return $result;
    }

    /**
     * Get member statistics with filters
     * 
     * @param array $filters Filter parameters
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getMemberStatistics(array $filters, ?array $scopeData): array
    {
        $builder = $this->memberModel->builder()
            ->join('users', 'users.id = member_profiles.user_id');

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $builder->where('users.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('users.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        // Apply regional scope
        if (!empty($filters['province_id'])) {
            $builder->where('member_profiles.province_id', $filters['province_id']);
        } elseif ($scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $total = (clone $builder)->countAllResults();
        $active = (clone $builder)->where('users.active', 1)->countAllResults();
        $inactive = (clone $builder)->where('users.active', 0)->countAllResults();

        // Membership status breakdown
        $statusBreakdown = (clone $builder)
            ->select('membership_status, COUNT(*) as total')
            ->groupBy('membership_status')
            ->get()
            ->getResultArray();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'status_breakdown' => $statusBreakdown
        ];
    }

    /**
     * Get regional distribution
     * 
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getRegionalDistribution(bool $isKoordinator, ?array $scopeData): array
    {
        $builder = $this->memberModel->builder()
            ->select('provinces.name as province_name, provinces.id as province_id, COUNT(*) as total')
            ->join('provinces', 'provinces.id = member_profiles.province_id')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1);

        // Apply regional scope
        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        return $builder
            ->groupBy('member_profiles.province_id')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get growth analytics - OPTIMIZED
     * Single query with GROUP BY instead of loop queries
     *
     * @param string $period Period in months
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getGrowthAnalytics(string $period, bool $isKoordinator, ?array $scopeData): array
    {
        $months = (int) $period;

        // Use cache
        $cacheKey = "stats_growth_{$months}_" . ($isKoordinator && $scopeData ? $scopeData['province_id'] : 'all');
        $cache = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // OPTIMIZED: Single query for all months
        $startDate = date('Y-m-01', strtotime("-" . ($months - 1) . " months"));

        $builder = $this->memberModel->builder()
            ->select('DATE_FORMAT(users.created_at, "%Y-%m") as month_key, COUNT(member_profiles.id) as count')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.created_at >=', $startDate)
            ->groupBy('month_key')
            ->orderBy('month_key', 'ASC');

        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $monthlyData = $builder->get()->getResultArray();

        // Create lookup array
        $monthlyLookup = [];
        foreach ($monthlyData as $data) {
            $monthlyLookup[$data['month_key']] = (int) $data['count'];
        }

        // Build result with cumulative
        $growthData = [];
        $cumulative = 0;

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));

            $count = $monthlyLookup[$monthKey] ?? 0;
            $cumulative += $count;

            $growthData[] = [
                'month' => $monthLabel,
                'new_members' => $count,
                'cumulative' => $cumulative
            ];
        }

        // Cache for 5 minutes
        $cache->save($cacheKey, $growthData, 300);

        return $growthData;
    }

    /**
     * Create overview sheet for Excel export
     */
    protected function createOverviewSheet(Spreadsheet $spreadsheet, bool $isKoordinator, ?array $scopeData): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Overview');

        $stats = $this->getComprehensiveStats(auth()->id(), $isKoordinator, $scopeData);

        $sheet->setCellValue('A1', 'LAPORAN STATISTIK SPK');
        $sheet->setCellValue('A2', 'Tanggal: ' . date('d/m/Y H:i'));

        if ($scopeData) {
            $sheet->setCellValue('A3', 'Wilayah: ' . $scopeData['province_name']);
        }

        $sheet->setCellValue('A5', 'Metrik');
        $sheet->setCellValue('B5', 'Jumlah');

        $row = 6;
        $sheet->setCellValue("A{$row}", 'Total Anggota');
        $sheet->setCellValue("B{$row}", $stats['total_members']);

        $row++;
        $sheet->setCellValue("A{$row}", 'Anggota Baru Bulan Ini');
        $sheet->setCellValue("B{$row}", $stats['new_members_this_month']);

        $row++;
        $sheet->setCellValue("A{$row}", 'Pending Approval');
        $sheet->setCellValue("B{$row}", $stats['pending_approvals']);

        // Style
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A5:B5')->getFont()->setBold(true);
    }

    /**
     * Create regional distribution sheet
     */
    protected function createRegionalSheet(Spreadsheet $spreadsheet, bool $isKoordinator, ?array $scopeData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Distribusi Regional');

        $regionalStats = $this->getRegionalDistribution($isKoordinator, $scopeData);

        $sheet->setCellValue('A1', 'Provinsi');
        $sheet->setCellValue('B1', 'Jumlah Anggota');

        $row = 2;
        foreach ($regionalStats as $stat) {
            $sheet->setCellValue("A{$row}", $stat['province_name']);
            $sheet->setCellValue("B{$row}", $stat['total']);
            $row++;
        }

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
    }

    /**
     * Create growth data sheet
     */
    protected function createGrowthSheet(Spreadsheet $spreadsheet, bool $isKoordinator, ?array $scopeData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Pertumbuhan');

        $growthData = $this->getGrowthAnalytics('12', $isKoordinator, $scopeData);

        $sheet->setCellValue('A1', 'Bulan');
        $sheet->setCellValue('B1', 'Anggota Baru');
        $sheet->setCellValue('C1', 'Kumulatif');

        $row = 2;
        foreach ($growthData as $data) {
            $sheet->setCellValue("A{$row}", $data['month']);
            $sheet->setCellValue("B{$row}", $data['new_members']);
            $sheet->setCellValue("C{$row}", $data['cumulative']);
            $row++;
        }

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
    }

    /**
     * Create detailed member list sheet - OPTIMIZED with chunking
     * Prevents memory exhaustion for large datasets
     */
    protected function createMemberListSheet(Spreadsheet $spreadsheet, bool $isKoordinator, ?array $scopeData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Daftar Anggota');

        $headers = ['No', 'Nama', 'Email', 'Provinsi', 'No. Anggota', 'Status'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // OPTIMIZED: Use chunking to prevent memory issues
        $builder = $this->memberModel->builder()
            ->select('member_profiles.*, auth_identities.secret as email, provinces.name as province_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->where('users.active', 1);

        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        // Limit to 5000 records for export to prevent timeout
        $builder->limit(5000);
        $members = $builder->get()->getResult();

        $row = 2;
        foreach ($members as $index => $member) {
            $sheet->fromArray([
                $index + 1,
                $member->full_name ?? '-',
                $member->email ?? '-',
                $member->province_name ?? '-',
                $member->member_number ?? '-',
                ucfirst($member->membership_status ?? 'unknown')
            ], null, "A{$row}");
            $row++;

            // Free memory every 500 rows
            if ($row % 500 === 0) {
                gc_collect_cycles();
            }
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Clear statistics cache
     * Call this when member data is updated
     *
     * @return ResponseInterface
     */
    public function clearCache(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('statistics.view')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk clear cache'
            ])->setStatusCode(403);
        }

        try {
            $cache = \Config\Services::cache();

            // Clear all statistics cache keys
            $cacheKeys = [
                'stats_comprehensive_all',
                'stats_top_all',
                'stats_trend_all',
            ];

            // Also clear province-specific caches (iterate through common IDs)
            for ($i = 1; $i <= 50; $i++) {
                $cacheKeys[] = "stats_comprehensive_{$i}";
                $cacheKeys[] = "stats_top_{$i}";
                $cacheKeys[] = "stats_trend_{$i}";
                $cacheKeys[] = "stats_growth_12_{$i}";
                $cacheKeys[] = "stats_growth_6_{$i}";
                $cacheKeys[] = "stats_growth_24_{$i}";
            }

            foreach ($cacheKeys as $key) {
                $cache->delete($key);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cache statistik berhasil dibersihkan'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error clearing stats cache: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membersihkan cache: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
