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

            // Format activities for the view
            $activities = [];

            // Add recent member registrations
            foreach ($recentMembers as $member) {
                $activities[] = [
                    'title' => 'Pendaftaran Anggota Baru',
                    'description' => $member['full_name'] . ' mendaftar sebagai anggota baru',
                    'time' => $this->timeAgo($member['created_at']),
                    'icon' => 'person_add',
                    'timestamp' => strtotime($member['created_at'])
                ];
            }

            // Add recent audit logs
            foreach ($recentAudits as $audit) {
                $activities[] = [
                    'title' => $audit['action'] ?? 'Aktivitas',
                    'description' => $audit['description'] ?? ($audit['email'] ?? 'User') . ' melakukan aktivitas',
                    'time' => $this->timeAgo($audit['created_at']),
                    'icon' => $this->getAuditIcon($audit['action'] ?? ''),
                    'timestamp' => strtotime($audit['created_at'])
                ];
            }

            // Sort by timestamp (most recent first)
            usort($activities, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });

            // Remove timestamp from output and limit to 10 most recent
            $activities = array_slice($activities, 0, 10);
            foreach ($activities as &$activity) {
                unset($activity['timestamp']);
            }

            return $activities;
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::fetchRecentActivities: ' . $e->getMessage());

            return [];
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

            // Format pending items for the view
            $items = [];

            // Add pending member approvals if any
            $approvalCount = count($pendingApprovals);
            if ($approvalCount > 0) {
                $oldestApproval = $pendingApprovals[0] ?? null;
                $items[] = [
                    'title' => 'Persetujuan Anggota Pending',
                    'count' => $approvalCount,
                    'description' => $approvalCount . ' anggota menunggu persetujuan',
                    'time' => $oldestApproval ? 'Tertua: ' . $this->timeAgo($oldestApproval['created_at']) : '',
                    'url' => base_url('admin/members/pending')
                ];
            }

            // Add open tickets if any
            $ticketCount = count($openTickets);
            if ($ticketCount > 0) {
                $oldestTicket = $openTickets[0] ?? null;
                $items[] = [
                    'title' => 'Tiket Terbuka',
                    'count' => $ticketCount,
                    'description' => $ticketCount . ' tiket menunggu respon',
                    'time' => $oldestTicket ? 'Tertua: ' . $this->timeAgo($oldestTicket['created_at']) : '',
                    'url' => base_url('admin/complaints')
                ];
            }

            return $items;
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::getPendingItems: ' . $e->getMessage());

            return [];
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

    /**
     * Convert timestamp to human-readable "time ago" format
     *
     * @param string $datetime DateTime string
     * @return string Human readable time
     */
    protected function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return $diff . ' detik yang lalu';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' hari yang lalu';
        } else {
            return date('d M Y H:i', $timestamp);
        }
    }

    /**
     * Get Material Icon name based on audit action type
     *
     * @param string $action Action type
     * @return string Material Icon name
     */
    protected function getAuditIcon(string $action): string
    {
        $iconMap = [
            'login' => 'login',
            'logout' => 'logout',
            'create' => 'add_circle',
            'update' => 'edit',
            'delete' => 'delete',
            'approve' => 'check_circle',
            'reject' => 'cancel',
            'verify' => 'verified',
            'export' => 'download',
            'import' => 'upload'
        ];

        $actionLower = strtolower($action);
        foreach ($iconMap as $key => $icon) {
            if (strpos($actionLower, $key) !== false) {
                return $icon;
            }
        }

        return 'info';
    }
}
