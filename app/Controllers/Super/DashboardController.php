<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * DashboardController - Super Admin
 * 
 * Mengelola dashboard Super Admin dengan comprehensive system statistics
 * Menampilkan overview lengkap sistem, user management, dan system health
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class DashboardController extends BaseController
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display Super Admin dashboard
     * Shows comprehensive system statistics, charts, and recent activities
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $user = auth()->user();

        try {
            // Get comprehensive statistics
            $stats = $this->getSystemStatistics();

            // Get chart data for visualization
            $chartData = $this->getChartData();

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Get system health info
            $systemHealth = $this->getSystemHealth();

            // Get quick actions
            $quickActions = $this->getQuickActions();

            $data = [
                'title' => 'Dashboard Super Admin',
                'breadcrumbs' => [
                    ['title' => 'Super Admin'],
                    ['title' => 'Dashboard']
                ],

                // Current user
                'user' => $user,

                // Statistics
                'stats' => $stats,

                // Chart data
                'chartData' => $chartData,

                // Recent activities
                'recentActivities' => $recentActivities,

                // System health
                'systemHealth' => $systemHealth,

                // Quick actions
                'quickActions' => $quickActions,
            ];

            return view('super/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat dashboard.');
        }
    }

    /**
     * Get comprehensive system statistics
     * 
     * @return array
     */
    private function getSystemStatistics(): array
    {
        $db = \Config\Database::connect();

        // Total Users
        $totalUsers = $db->table('users')
            ->where('active', 1)
            ->countAllResults();

        // Users by Role
        $usersByRole = $db->table('auth_groups_users')
            ->select('auth_groups_users.group, COUNT(*) as total')
            ->join('users', 'users.id = auth_groups_users.user_id')
            ->where('users.active', 1)
            ->groupBy('auth_groups_users.group')
            ->get()
            ->getResultArray();

        $roleStats = [];
        foreach ($usersByRole as $role) {
            $roleStats[$role['group']] = (int)$role['total'];
        }

        // Total Members (non Super Admin)
        $totalMembers = $db->table('users')
            ->select('users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->where('users.active', 1)
            ->where('auth_groups_users.group !=', 'superadmin')
            ->orWhere('auth_groups_users.group', null)
            ->countAllResults();

        // Pending Members (assuming status field)
        $pendingMembers = $db->table('users')
            ->where('active', 0)
            ->where('deleted_at', null)
            ->countAllResults();

        // Active Members (last 30 days activity)
        $activeMembers = $db->table('users')
            ->where('active', 1)
            ->where('last_active >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->countAllResults();

        // New members this month
        $newMembersThisMonth = $db->table('users')
            ->where('created_at >=', date('Y-m-01 00:00:00'))
            ->countAllResults();

        // Total Roles
        $totalRoles = $db->table('auth_groups')
            ->countAllResults();

        // Total Permissions
        $totalPermissions = $db->table('auth_permissions')
            ->countAllResults();

        // Check if member_profiles table exists
        $memberStats = [
            'total_provinces' => 0,
            'total_universities' => 0,
            'total_study_programs' => 0,
        ];

        if ($db->tableExists('member_profiles')) {
            // Members by Province (if table exists)
            $memberStats['total_provinces'] = $db->table('member_profiles')
                ->select('province_id')
                ->distinct()
                ->where('province_id IS NOT NULL')
                ->countAllResults();
        }

        // Check master data tables
        if ($db->tableExists('provinces')) {
            $memberStats['total_provinces'] = $db->table('provinces')->countAllResults();
        }

        if ($db->tableExists('universities')) {
            $memberStats['total_universities'] = $db->table('universities')->countAllResults();
        }

        if ($db->tableExists('study_programs')) {
            $memberStats['total_study_programs'] = $db->table('study_programs')->countAllResults();
        }

        // Check complaints, forums, surveys if tables exist
        $moduleStats = [
            'total_complaints' => 0,
            'open_complaints' => 0,
            'total_forums' => 0,
            'total_surveys' => 0,
            'active_surveys' => 0,
        ];

        if ($db->tableExists('complaints')) {
            $moduleStats['total_complaints'] = $db->table('complaints')->countAllResults();
            $moduleStats['open_complaints'] = $db->table('complaints')
                ->where('status', 'open')
                ->countAllResults();
        }

        if ($db->tableExists('forum_threads')) {
            $moduleStats['total_forums'] = $db->table('forum_threads')->countAllResults();
        }

        if ($db->tableExists('surveys')) {
            $moduleStats['total_surveys'] = $db->table('surveys')->countAllResults();
            $moduleStats['active_surveys'] = $db->table('surveys')
                ->where('status', 'active')
                ->where('end_date >=', date('Y-m-d'))
                ->countAllResults();
        }

        // Audit logs count (last 7 days)
        $recentAuditLogs = 0;
        if ($db->tableExists('audit_logs')) {
            $recentAuditLogs = $db->table('audit_logs')
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults();
        }

        return [
            // User Statistics
            'total_users' => $totalUsers,
            'total_members' => $totalMembers,
            'pending_members' => $pendingMembers,
            'active_members' => $activeMembers,
            'new_members_this_month' => $newMembersThisMonth,

            // Role Statistics
            'total_roles' => $totalRoles,
            'total_permissions' => $totalPermissions,
            'users_by_role' => $roleStats,

            // Member Profile Statistics
            'total_provinces' => $memberStats['total_provinces'],
            'total_universities' => $memberStats['total_universities'],
            'total_study_programs' => $memberStats['total_study_programs'],

            // Module Statistics
            'total_complaints' => $moduleStats['total_complaints'],
            'open_complaints' => $moduleStats['open_complaints'],
            'total_forums' => $moduleStats['total_forums'],
            'total_surveys' => $moduleStats['total_surveys'],
            'active_surveys' => $moduleStats['active_surveys'],

            // System Statistics
            'recent_audit_logs' => $recentAuditLogs,
        ];
    }

    /**
     * Get chart data for dashboard visualization
     * 
     * @return array
     */
    private function getChartData(): array
    {
        $db = \Config\Database::connect();

        // User growth last 12 months
        $userGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $count = $db->table('users')
                ->where('DATE_FORMAT(created_at, "%Y-%m")', $month)
                ->countAllResults();

            $userGrowth[] = [
                'month' => date('M Y', strtotime("-$i months")),
                'count' => $count
            ];
        }

        // Users by role (for pie chart)
        $usersByRole = $db->table('auth_groups_users')
            ->select('auth_groups_users.group as role, COUNT(*) as count')
            ->join('users', 'users.id = auth_groups_users.user_id')
            ->where('users.active', 1)
            ->groupBy('auth_groups_users.group')
            ->get()
            ->getResultArray();

        // Members by province (top 10)
        $membersByProvince = [];
        if ($db->tableExists('member_profiles') && $db->tableExists('provinces')) {
            $membersByProvince = $db->table('member_profiles')
                ->select('provinces.name as province, COUNT(*) as count')
                ->join('provinces', 'provinces.id = member_profiles.province_id')
                ->groupBy('member_profiles.province_id')
                ->orderBy('count', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
        }

        // Activity trend (last 30 days)
        $activityTrend = [];
        if ($db->tableExists('audit_logs')) {
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $count = $db->table('audit_logs')
                    ->where('DATE(created_at)', $date)
                    ->countAllResults();

                $activityTrend[] = [
                    'date' => date('d M', strtotime($date)),
                    'count' => $count
                ];
            }
        }

        return [
            'user_growth' => $userGrowth,
            'users_by_role' => $usersByRole,
            'members_by_province' => $membersByProvince,
            'activity_trend' => $activityTrend,
        ];
    }

    /**
     * Get recent system activities
     * 
     * @return array
     */
    private function getRecentActivities(): array
    {
        $db = \Config\Database::connect();
        $activities = [];

        if ($db->tableExists('audit_logs')) {
            $logs = $db->table('audit_logs')
                ->select('audit_logs.*, users.username')
                ->join('users', 'users.id = audit_logs.user_id', 'left')
                ->orderBy('audit_logs.created_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            foreach ($logs as $log) {
                $activities[] = [
                    'user' => $log['username'] ?? 'System',
                    'action' => $log['action'] ?? 'Unknown',
                    'description' => $log['description'] ?? '',
                    'ip_address' => $log['ip_address'] ?? '',
                    'created_at' => $log['created_at'] ?? '',
                ];
            }
        } else {
            // Fallback: Get recent users
            $recentUsers = $db->table('users')
                ->select('username, created_at')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            foreach ($recentUsers as $user) {
                $activities[] = [
                    'user' => $user['username'],
                    'action' => 'USER_REGISTERED',
                    'description' => 'New user registered',
                    'ip_address' => '-',
                    'created_at' => $user['created_at'],
                ];
            }
        }

        return $activities;
    }

    /**
     * Get system health information
     * 
     * @return array
     */
    private function getSystemHealth(): array
    {
        $db = \Config\Database::connect();

        // Database status
        $dbStatus = 'healthy';
        try {
            $db->query('SELECT 1');
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        // Check disk space (if available)
        $diskSpace = [
            'total' => 0,
            'free' => 0,
            'used' => 0,
            'percentage' => 0
        ];

        if (function_exists('disk_total_space') && function_exists('disk_free_space')) {
            $total = disk_total_space(ROOTPATH);
            $free = disk_free_space(ROOTPATH);
            $used = $total - $free;
            $percentage = ($used / $total) * 100;

            $diskSpace = [
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'used' => $this->formatBytes($used),
                'percentage' => round($percentage, 2)
            ];
        }

        // PHP version
        $phpVersion = phpversion();

        // CI version
        $ciVersion = \CodeIgniter\CodeIgniter::CI_VERSION;

        // Environment
        $environment = ENVIRONMENT;

        return [
            'database' => $dbStatus,
            'disk_space' => $diskSpace,
            'php_version' => $phpVersion,
            'ci_version' => $ciVersion,
            'environment' => $environment,
        ];
    }

    /**
     * Get quick actions for Super Admin
     * 
     * @return array
     */
    private function getQuickActions(): array
    {
        return [
            [
                'title' => 'Manage Roles',
                'icon' => 'fas fa-users-cog',
                'url' => base_url('super/roles'),
                'color' => 'primary',
                'description' => 'Create and manage user roles'
            ],
            [
                'title' => 'Manage Permissions',
                'icon' => 'fas fa-key',
                'url' => base_url('super/permissions'),
                'color' => 'success',
                'description' => 'Configure system permissions'
            ],
            [
                'title' => 'Menu Management',
                'icon' => 'fas fa-bars',
                'url' => base_url('super/menus'),
                'color' => 'info',
                'description' => 'Customize navigation menus'
            ],
            [
                'title' => 'Master Data',
                'icon' => 'fas fa-database',
                'url' => base_url('super/master-data/provinces'),
                'color' => 'warning',
                'description' => 'Manage system master data'
            ],
            [
                'title' => 'System Settings',
                'icon' => 'fas fa-cog',
                'url' => base_url('super/settings'),
                'color' => 'secondary',
                'description' => 'Configure system settings'
            ],
            [
                'title' => 'Audit Logs',
                'icon' => 'fas fa-history',
                'url' => base_url('super/audit-logs'),
                'color' => 'danger',
                'description' => 'View system activity logs'
            ],
        ];
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Refresh dashboard statistics (AJAX)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function refresh()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        try {
            $stats = $this->getSystemStatistics();
            $systemHealth = $this->getSystemHealth();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'system_health' => $systemHealth,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to refresh dashboard'
            ]);
        }
    }
}
