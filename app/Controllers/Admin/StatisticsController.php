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
     * Get comprehensive statistics
     * 
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getComprehensiveStats(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        $builder = $this->memberModel->builder();

        // Apply regional scope
        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        // Total members
        $totalMembers = (clone $builder)
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1)
            ->countAllResults();

        // New members this month
        $newMembersThisMonth = (clone $builder)
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.created_at >=', date('Y-m-01 00:00:00'))
            ->countAllResults();

        // Pending approvals
        $pendingApprovals = (clone $builder)
            ->where('member_profiles.membership_status', 'calon_anggota')
            ->countAllResults();

        // Growth rate (compare to last month)
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

        $lastMonthMembers = (clone $builder)
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.created_at >=', $lastMonthStart . ' 00:00:00')
            ->where('users.created_at <=', $lastMonthEnd . ' 23:59:59')
            ->countAllResults();

        $growthRate = $lastMonthMembers > 0
            ? round((($newMembersThisMonth - $lastMonthMembers) / $lastMonthMembers) * 100, 2)
            : 0;

        // Engagement stats
        $activeTickets = $this->complaintModel
            ->whereIn('status', ['open', 'in_progress'])
            ->countAllResults();

        $totalThreads = $this->forumModel->where('is_deleted', 0)->countAllResults();

        $activeSurveys = $this->surveyModel
            ->where('status', 'published')
            ->where('end_date >=', date('Y-m-d'))
            ->countAllResults();

        return [
            'total_members' => $totalMembers,
            'new_members_this_month' => $newMembersThisMonth,
            'pending_approvals' => $pendingApprovals,
            'growth_rate' => $growthRate,
            'active_tickets' => $activeTickets,
            'total_threads' => $totalThreads,
            'active_surveys' => $activeSurveys
        ];
    }

    /**
     * Get top statistics (top provinces, universities, etc)
     * 
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getTopStatistics(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        $builder = $this->memberModel->builder();

        // Apply regional scope
        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        // Top provinces (skip if koordinator)
        $topProvinces = [];
        if (!$isKoordinator) {
            $topProvinces = (clone $builder)
                ->select('provinces.name, COUNT(*) as total')
                ->join('provinces', 'provinces.id = member_profiles.province_id')
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('users.active', 1)
                ->groupBy('member_profiles.province_id')
                ->orderBy('total', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
        }

        // Top universities
        $topUniversities = (clone $builder)
            ->select('universities.name, COUNT(*) as total')
            ->join('universities', 'universities.id = member_profiles.university_id')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1)
            ->groupBy('member_profiles.university_id')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Position type distribution
        $positionTypes = (clone $builder)
            ->select('position_type, COUNT(*) as total')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('users.active', 1)
            ->groupBy('position_type')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'top_provinces' => $topProvinces,
            'top_universities' => $topUniversities,
            'position_types' => $positionTypes
        ];
    }

    /**
     * Get trend data for charts
     * 
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getTrendData(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        $trendData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthStart = $month . '-01 00:00:00';
            $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));

            $builder = $this->memberModel->builder()
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('users.created_at >=', $monthStart)
                ->where('users.created_at <=', $monthEnd);

            if ($isKoordinator && $scopeData) {
                $builder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            $count = $builder->countAllResults();

            $trendData[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'count' => $count
            ];
        }

        return $trendData;
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
     * Get growth analytics
     * 
     * @param string $period Period in months
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array
     */
    protected function getGrowthAnalytics(string $period, bool $isKoordinator, ?array $scopeData): array
    {
        $months = (int) $period;
        $growthData = [];
        $cumulative = 0;

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthStart = $month . '-01 00:00:00';
            $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));

            $builder = $this->memberModel->builder()
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('users.created_at >=', $monthStart)
                ->where('users.created_at <=', $monthEnd);

            if ($isKoordinator && $scopeData) {
                $builder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            $count = $builder->countAllResults();
            $cumulative += $count;

            $growthData[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'new_members' => $count,
                'cumulative' => $cumulative
            ];
        }

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
     * Create detailed member list sheet
     */
    protected function createMemberListSheet(Spreadsheet $spreadsheet, bool $isKoordinator, ?array $scopeData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Daftar Anggota');

        $builder = $this->memberModel->builder()
            ->select('member_profiles.*, auth_identities.secret as email, provinces.name as province_name')
            ->join('users', 'users.id = member_profiles.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->where('users.active', 1);

        if ($isKoordinator && $scopeData) {
            $builder->where('member_profiles.province_id', $scopeData['province_id']);
        }

        $members = $builder->findAll();

        $headers = ['No', 'Nama', 'Email', 'Provinsi', 'No. Anggota', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($members as $index => $member) {
            $sheet->fromArray([
                $index + 1,
                $member->full_name,
                $member->email,
                $member->province_name ?? '-',
                $member->member_number ?? '-',
                ucfirst($member->membership_status)
            ], null, "A{$row}");
            $row++;
        }

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
