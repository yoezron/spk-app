<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Member\MemberStatisticsService;
use App\Services\RegionScopeService;
use App\Services\Communication\NotificationService;
use App\Models\MemberProfileModel;
use App\Models\ComplaintModel;
use App\Models\ForumThreadModel;
use App\Models\SurveyModel;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * DashboardController
 * 
 * Mengelola dashboard admin dengan comprehensive statistics
 * Menampilkan overview system, recent activities, dan quick actions
 * Support regional scope untuk Koordinator Wilayah
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class DashboardController extends BaseController
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
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

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
     * @var AuditLogModel
     */
    protected $auditModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->statsService = new MemberStatisticsService();
        $this->regionScope = new RegionScopeService();
        $this->notificationService = new NotificationService();
        $this->memberModel = new MemberProfileModel();
        $this->complaintModel = new ComplaintModel();
        $this->forumModel = new ForumThreadModel();
        $this->surveyModel = new SurveyModel();
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Display admin dashboard
     * Shows comprehensive statistics and recent activities
     * Apply regional scope for Koordinator Wilayah
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('admin.dashboard')) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $user = auth()->user();
        $userId = $user->id;

        // Get user role for scope determination
        $isKoordinator = $user->inGroup('koordinator');
        $isSuperAdmin = $user->inGroup('superadmin');

        // Get scope data for Koordinator Wilayah
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($userId);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        // Get statistics based on scope
        $stats = $this->getStatistics($userId, $isKoordinator, $scopeData);

        // Get recent activities
        $recentActivities = $this->fetchRecentActivities($userId, $isKoordinator, $scopeData);

        // Get pending items
        $pendingItems = $this->getPendingItems($userId, $isKoordinator, $scopeData);

        // Get chart data
        $chartData = $this->getChartData($userId, $isKoordinator, $scopeData);

        $data = [
            'title' => 'Dashboard Admin',
            'user' => $user,
            'is_koordinator' => $isKoordinator,
            'is_superadmin' => $isSuperAdmin,
            'scope_data' => $scopeData,
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'pending_items' => $pendingItems,
            'chart_data' => $chartData
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Get quick statistics (AJAX endpoint)
     * Real-time statistics for dashboard widgets
     * 
     * @return ResponseInterface JSON response
     */
    public function getQuickStats(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $user = auth()->user();
        $userId = $user->id;
        $isKoordinator = $user->inGroup('koordinator');

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($userId);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        $stats = $this->getStatistics($userId, $isKoordinator, $scopeData);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get recent activities (AJAX endpoint)
     * Returns recent member registrations and activities
     * 
     * @return ResponseInterface JSON response
     */
    public function getRecentActivities(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $user = auth()->user();
        $userId = $user->id;
        $isKoordinator = $user->inGroup('koordinator');

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($userId);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        $activities = $this->fetchRecentActivities($userId, $isKoordinator, $scopeData);

        return $this->response->setJSON([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get chart data (AJAX endpoint)
     * Returns data for member growth and regional distribution charts
     * 
     * @return ResponseInterface JSON response
     */
    public function getCharts(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        $user = auth()->user();
        $userId = $user->id;
        $isKoordinator = $user->inGroup('koordinator');

        // Get scope data
        $scopeData = null;
        if ($isKoordinator) {
            $scopeResult = $this->regionScope->getScopeData($userId);
            if ($scopeResult['success']) {
                $scopeData = $scopeResult['data'];
            }
        }

        $chartData = $this->getChartData($userId, $isKoordinator, $scopeData);

        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData
        ]);
    }

    /**
     * Get comprehensive statistics based on user scope
     * 
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array Statistics data
     */
    protected function getStatistics(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        try {
            $builder = $this->memberModel->builder();

            // Apply regional scope for Koordinator Wilayah
            if ($isKoordinator && $scopeData) {
                $builder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            // Total members
            $totalMembers = (clone $builder)
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('users.active', 1)
                ->countAllResults();

            // Pending members (Calon Anggota)
            $pendingMembers = (clone $builder)
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('member_profiles.membership_status', 'calon_anggota')
                ->countAllResults();

            // New members this month
            $newMembersThisMonth = (clone $builder)
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('users.created_at >=', date('Y-m-01 00:00:00'))
                ->countAllResults();

            // Active tickets
            $ticketBuilder = $this->complaintModel->builder();
            if ($isKoordinator && $scopeData) {
                $ticketBuilder->join('member_profiles', 'member_profiles.user_id = complaints.user_id')
                    ->where('member_profiles.province_id', $scopeData['province_id']);
            }
            $activeTickets = $ticketBuilder
                ->whereIn('complaints.status', ['open', 'in_progress'])
                ->countAllResults();

            // Forum threads
            $totalThreads = $this->forumModel->countAll();

            // Active surveys
            $activeSurveys = $this->surveyModel
                ->where('status', 'published')
                ->where('end_date >=', date('Y-m-d'))
                ->countAllResults();

            return [
                'total_members' => $totalMembers,
                'pending_members' => $pendingMembers,
                'new_members_this_month' => $newMembersThisMonth,
                'active_tickets' => $activeTickets,
                'total_threads' => $totalThreads,
                'active_surveys' => $activeSurveys
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::getStatistics: ' . $e->getMessage());

            return [
                'total_members' => 0,
                'pending_members' => 0,
                'new_members_this_month' => 0,
                'active_tickets' => 0,
                'total_threads' => 0,
                'active_surveys' => 0
            ];
        }
    }

    /**
     * Fetch recent activities based on user scope
     *
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array Recent activities
     */
    protected function fetchRecentActivities(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        try {
            $builder = $this->memberModel->builder()
                ->select('member_profiles.*, auth_identities.secret as email, users.created_at')
                ->join('users', 'users.id = member_profiles.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->orderBy('users.created_at', 'DESC')
                ->limit(10);

            // Apply regional scope for Koordinator Wilayah
            if ($isKoordinator && $scopeData) {
                $builder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            $recentMembers = $builder->get()->getResultArray();

            // Get recent audit logs
            $auditBuilder = $this->auditModel->builder()
                ->select('audit_logs.*, auth_identities.secret as email')
                ->join('users', 'users.id = audit_logs.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->orderBy('audit_logs.created_at', 'DESC')
                ->limit(10);

            // Filter audit logs by scope if koordinator
            if ($isKoordinator && $scopeData) {
                $auditBuilder->where('audit_logs.province_id', $scopeData['province_id']);
            }

            $recentAudits = $auditBuilder->get()->getResultArray();

            return [
                'recent_members' => $recentMembers,
                'recent_audits' => $recentAudits
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::getRecentActivities: ' . $e->getMessage());

            return [
                'recent_members' => [],
                'recent_audits' => []
            ];
        }
    }

    /**
     * Get pending items that need attention
     * 
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array Pending items
     */
    protected function getPendingItems(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        try {
            // Pending member approvals
            $pendingBuilder = $this->memberModel->builder()
                ->select('member_profiles.*, auth_identities.secret as email, users.created_at')
                ->join('users', 'users.id = member_profiles.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->where('member_profiles.membership_status', 'calon_anggota')
                ->orderBy('users.created_at', 'ASC')
                ->limit(5);

            if ($isKoordinator && $scopeData) {
                $pendingBuilder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            $pendingApprovals = $pendingBuilder->get()->getResultArray();

            // Open tickets
            $ticketBuilder = $this->complaintModel->builder()
                ->select('complaints.*, member_profiles.full_name, auth_identities.secret as email')
                ->join('member_profiles', 'member_profiles.user_id = complaints.user_id')
                ->join('users', 'users.id = complaints.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->where('complaints.status', 'open')
                ->orderBy('complaints.created_at', 'ASC')
                ->limit(5);

            if ($isKoordinator && $scopeData) {
                $ticketBuilder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            $openTickets = $ticketBuilder->get()->getResultArray();

            return [
                'pending_approvals' => $pendingApprovals,
                'open_tickets' => $openTickets
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::getPendingItems: ' . $e->getMessage());

            return [
                'pending_approvals' => [],
                'open_tickets' => []
            ];
        }
    }

    /**
     * Get chart data for visualizations
     * 
     * @param int $userId User ID
     * @param bool $isKoordinator Is user Koordinator Wilayah
     * @param array|null $scopeData Scope data for Koordinator
     * @return array Chart data
     */
    protected function getChartData(int $userId, bool $isKoordinator, ?array $scopeData): array
    {
        try {
            // Member growth (last 6 months)
            $growthData = [];
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

                $growthData[] = [
                    'month' => date('M Y', strtotime($monthStart)),
                    'count' => $count
                ];
            }

            // Regional distribution (if not koordinator)
            $regionalData = [];
            if (!$isKoordinator) {
                $regionalData = $this->memberModel->builder()
                    ->select('provinces.name as province_name, COUNT(*) as total')
                    ->join('provinces', 'provinces.id = member_profiles.province_id')
                    ->join('users', 'users.id = member_profiles.user_id')
                    ->where('users.active', 1)
                    ->groupBy('member_profiles.province_id')
                    ->orderBy('total', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();
            }

            // Membership status distribution
            $statusBuilder = $this->memberModel->builder()
                ->select('membership_status, COUNT(*) as total')
                ->join('users', 'users.id = member_profiles.user_id');

            if ($isKoordinator && $scopeData) {
                $statusBuilder->where('member_profiles.province_id', $scopeData['province_id']);
            }

            $statusData = $statusBuilder
                ->groupBy('membership_status')
                ->get()
                ->getResultArray();

            return [
                'member_growth' => $growthData,
                'regional_distribution' => $regionalData,
                'status_distribution' => $statusData
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::getChartData: ' . $e->getMessage());

            return [
                'member_growth' => [],
                'regional_distribution' => [],
                'status_distribution' => []
            ];
        }
    }
}
