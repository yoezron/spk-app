<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AuditLogModel
 * 
 * Model untuk mengelola audit trail dan activity logging
 * Mencatat semua aktivitas penting user untuk keamanan dan monitoring
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false; // Audit logs should never be deleted
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'module',
        'action',
        'description',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null; // Audit logs are never updated

    // Validation
    protected $validationRules = [
        'module' => 'required|max_length[50]',
        'action' => 'required|max_length[50]',
        'description' => 'required|max_length[500]',
        'ip_address' => 'permit_empty|max_length[45]',
        'method' => 'permit_empty|in_list[GET,POST,PUT,PATCH,DELETE]',
        'status' => 'permit_empty|in_list[success,failed,warning]',
    ];

    protected $validationMessages = [
        'module' => [
            'required' => 'Module harus diisi',
        ],
        'action' => [
            'required' => 'Action harus diisi',
        ],
        'description' => [
            'required' => 'Description harus diisi',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultStatus'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get audit log with user
     * 
     * @return object
     */
    public function withUser()
    {
        return $this->select('audit_logs.*, users.username, auth_identities.secret as user_email')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->join('auth_identities', 'auth_identities.user_id = audit_logs.user_id AND auth_identities.type = "email_password"', 'left');
    }

    /**
     * Get audit log with member profile
     * 
     * @return object
     */
    public function withMemberProfile()
    {
        return $this->select('audit_logs.*')
            ->select('users.username, users.email')
            ->select('member_profiles.full_name, member_profiles.membership_number')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = audit_logs.user_id', 'left');
    }

    /**
     * Get logs with users and apply filters
     * 
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogsWithUsers(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $builder = $this->withUser();

        // Apply filters
        if (!empty($filters['user_id'])) {
            $builder->where('audit_logs.user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $builder->where('audit_logs.action', $filters['action']);
        }

        if (!empty($filters['module'])) {
            $builder->where('audit_logs.module', $filters['module']);
        }

        if (!empty($filters['status'])) {
            $builder->where('audit_logs.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('audit_logs.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('audit_logs.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('audit_logs.description', $filters['search'])
                ->orLike('audit_logs.module', $filters['search'])
                ->orLike('audit_logs.action', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('audit_logs.created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Count filtered logs
     * 
     * @param array $filters
     * @return int
     */
    public function countFiltered(array $filters = []): int
    {
        $builder = $this->builder();

        // Apply same filters as getLogsWithUsers
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $builder->where('action', $filters['action']);
        }

        if (!empty($filters['module'])) {
            $builder->where('module', $filters['module']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('description', $filters['search'])
                ->orLike('module', $filters['search'])
                ->orLike('action', $filters['search'])
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Get distinct actions
     * 
     * @return array
     */
    public function getActions(): array
    {
        return $this->select('action')
            ->distinct()
            ->where('action IS NOT NULL')
            ->orderBy('action', 'ASC')
            ->findColumn('action');
    }

    /**
     * Get distinct modules
     * 
     * @return array
     */
    public function getModules(): array
    {
        return $this->select('module')
            ->distinct()
            ->where('module IS NOT NULL')
            ->orderBy('module', 'ASC')
            ->findColumn('module');
    }
    /**
     * Get distinct entity types
     * 
     * @return array
     */
    public function getEntityTypes(): array
    {
        return $this->select('entity_type')
            ->distinct()
            ->where('entity_type IS NOT NULL')
            ->orderBy('entity_type', 'ASC')
            ->findColumn('entity_type');
    }

    // ========================================
    // SCOPES - FILTERING BY MODULE
    // ========================================

    /**
     * Get logs by module
     * 
     * @param string $module Module name
     * @return object
     */
    public function byModule(string $module)
    {
        return $this->where('module', $module);
    }

    /**
     * Get authentication logs
     * 
     * @return object
     */
    public function authLogs()
    {
        return $this->where('module', 'authentication');
    }

    /**
     * Get member management logs
     * 
     * @return object
     */
    public function memberLogs()
    {
        return $this->whereIn('module', ['member', 'member_profile']);
    }

    /**
     * Get payment logs
     * 
     * @return object
     */
    public function paymentLogs()
    {
        return $this->where('module', 'payment');
    }

    /**
     * Get content logs
     * 
     * @return object
     */
    public function contentLogs()
    {
        return $this->whereIn('module', ['post', 'page', 'content']);
    }

    /**
     * Get system logs
     * 
     * @return object
     */
    public function systemLogs()
    {
        return $this->whereIn('module', ['system', 'settings', 'config']);
    }

    // ========================================
    // SCOPES - FILTERING BY ACTION
    // ========================================

    /**
     * Get logs by action
     * 
     * @param string $action Action name
     * @return object
     */
    public function byAction(string $action)
    {
        return $this->where('action', $action);
    }

    /**
     * Get create actions
     * 
     * @return object
     */
    public function createActions()
    {
        return $this->where('action', 'create');
    }

    /**
     * Get update actions
     * 
     * @return object
     */
    public function updateActions()
    {
        return $this->where('action', 'update');
    }

    /**
     * Get delete actions
     * 
     * @return object
     */
    public function deleteActions()
    {
        return $this->where('action', 'delete');
    }

    /**
     * Get login actions
     * 
     * @return object
     */
    public function loginActions()
    {
        return $this->where('action', 'login');
    }

    /**
     * Get failed login actions
     * 
     * @return object
     */
    public function failedLogins()
    {
        return $this->where('action', 'login_failed');
    }

    // ========================================
    // SCOPES - FILTERING BY STATUS
    // ========================================

    /**
     * Get successful actions
     * 
     * @return object
     */
    public function successful()
    {
        return $this->where('status', 'success');
    }

    /**
     * Get failed actions
     * 
     * @return object
     */
    public function failed()
    {
        return $this->where('status', 'failed');
    }

    /**
     * Get warning actions
     * 
     * @return object
     */
    public function warnings()
    {
        return $this->where('status', 'warning');
    }

    // ========================================
    // SCOPES - FILTERING BY USER
    // ========================================

    /**
     * Get logs by user
     * 
     * @param int $userId User ID
     * @return object
     */
    public function byUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Get logs by record
     * 
     * @param string $module Module name
     * @param int $recordId Record ID
     * @return object
     */
    public function byRecord(string $module, int $recordId)
    {
        return $this->where('module', $module)
            ->where('record_id', $recordId);
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get logs by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return object
     */
    public function byDateRange(string $startDate, string $endDate)
    {
        return $this->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate);
    }

    /**
     * Get logs by IP address
     * 
     * @param string $ipAddress IP address
     * @return object
     */
    public function byIpAddress(string $ipAddress)
    {
        return $this->where('ip_address', $ipAddress);
    }

    /**
     * Search logs
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('description', $keyword)
            ->orLike('module', $keyword)
            ->orLike('action', $keyword)
            ->orLike('ip_address', $keyword)
            ->groupEnd();
    }

    /**
     * Get recent logs
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function recent(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get user activity
     * 
     * @param int $userId User ID
     * @param int $limit Number of records
     * @return array
     */
    public function getUserActivity(int $userId, int $limit = 50): array
    {
        return $this->byUser($userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get record history
     * 
     * @param string $module Module name
     * @param int $recordId Record ID
     * @return array
     */
    public function getRecordHistory(string $module, int $recordId): array
    {
        return $this->byRecord($module, $recordId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get security events
     * 
     * @param int $hours Hours to look back
     * @return array
     */
    public function getSecurityEvents(int $hours = 24): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->where('created_at >=', $since)
            ->groupStart()
            ->where('action', 'login_failed')
            ->orWhere('status', 'failed')
            ->orWhere('status', 'warning')
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get suspicious activities
     * Multiple failed logins from same IP
     * 
     * @param int $threshold Number of failed attempts
     * @param int $minutes Time window in minutes
     * @return array
     */
    public function getSuspiciousActivities(int $threshold = 5, int $minutes = 30): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));

        return $this->select('ip_address, COUNT(*) as attempt_count, MAX(created_at) as last_attempt')
            ->where('action', 'login_failed')
            ->where('created_at >=', $since)
            ->groupBy('ip_address')
            ->having('attempt_count >=', $threshold)
            ->orderBy('attempt_count', 'DESC')
            ->findAll();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count logs by module
     * 
     * @return array
     */
    public function countByModule(): array
    {
        return $this->select('module, COUNT(*) as count')
            ->groupBy('module')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    /**
     * Count logs by action
     * 
     * @return array
     */
    public function countByAction(): array
    {
        return $this->select('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    /**
     * Count logs by status
     * 
     * @return array
     */
    public function countByStatus(): array
    {
        $result = $this->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $stats = [
            'success' => 0,
            'failed' => 0,
            'warning' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->status] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Get activity by date
     * 
     * @param int $days Number of days
     * @return array
     */
    public function getActivityByDate(int $days = 30): array
    {
        return $this->select('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")))
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    /**
     * Get activity by hour
     * 
     * @return array
     */
    public function getActivityByHour(): array
    {
        return $this->select('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at >=', date('Y-m-d 00:00:00'))
            ->groupBy('HOUR(created_at)')
            ->orderBy('hour', 'ASC')
            ->findAll();
    }

    /**
     * Get most active users
     * 
     * @param int $limit Number of records
     * @param int|null $days Days to look back
     * @return array
     */
    public function getMostActiveUsers(int $limit = 10, ?int $days = null): array
    {
        $builder = $this->select('users.username, users.email, COUNT(audit_logs.id) as activity_count')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->groupBy('audit_logs.user_id');

        if ($days) {
            $builder->where('audit_logs.created_at >=', date('Y-m-d', strtotime("-{$days} days")));
        }

        return $builder->orderBy('activity_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get failed login attempts by user
     * 
     * @param int $hours Hours to look back
     * @return array
     */
    public function getFailedLoginsByUser(int $hours = 24): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->select('user_id, users.username, users.email, COUNT(*) as failed_attempts, MAX(audit_logs.created_at) as last_attempt')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.action', 'login_failed')
            ->where('audit_logs.created_at >=', $since)
            ->groupBy('audit_logs.user_id')
            ->orderBy('failed_attempts', 'DESC')
            ->findAll();
    }

    /**
     * Get statistics summary
     * 
     * @param int $days Days to analyze
     * @return object
     */
    public function getStatisticsSummary(int $days = 7): object
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));

        $total = $this->where('created_at >=', $since)->countAllResults(false);
        $success = $this->successful()->countAllResults(false);
        $failed = $this->failed()->countAllResults(false);
        $warnings = $this->warnings()->countAllResults(false);
        $uniqueUsers = $this->select('DISTINCT user_id')->where('created_at >=', $since)->countAllResults(false);

        return (object)[
            'total_activities' => $total,
            'successful' => $success,
            'failed' => $failed,
            'warnings' => $warnings,
            'unique_users' => $uniqueUsers,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
            'period_days' => $days,
        ];
    }
    /**
     * Get activity statistics
     * 
     * @param int $days
     * @return array
     */
    public function getActivityStats(int $days = 30): array
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));

        // Total events
        $total = $this->where('created_at >=', $since)->countAllResults(false);

        // By status
        $byStatus = $this->select('status, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('status')
            ->findAll();

        $statusStats = [
            'success' => 0,
            'failed' => 0,
            'warning' => 0
        ];

        foreach ($byStatus as $stat) {
            $statusStats[$stat->status] = (int)$stat->count;
        }

        // By severity (jika ada kolom severity di tabel)
        $bySeverity = $this->select('severity, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('severity')
            ->findAll();

        $severityStats = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
            'critical' => 0
        ];

        foreach ($bySeverity as $stat) {
            $severityStats[$stat->severity] = (int)$stat->count;
        }

        // By action
        $byAction = $this->select('action, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('action')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->findAll();

        // By module
        $byModule = $this->select('module, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->where('module IS NOT NULL')
            ->groupBy('module')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->findAll();

        // By day (trend)
        $byDay = $this->select('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();

        // Format by_day for chart
        $dailyStats = [];
        foreach ($byDay as $day) {
            $dailyStats[] = [
                'date' => $day->date,
                'count' => (int)$day->count
            ];
        }

        return [
            'total' => $total,
            'by_status' => $statusStats,
            'by_severity' => $severityStats,
            'by_action' => $byAction,
            'by_module' => $byModule,
            'by_day' => $dailyStats,
            'period_days' => $days
        ];
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Log user action
     * 
     * @param array $data Log data
     * @return int|false Log ID or false
     */
    public function logAction(array $data)
    {
        // Add request info if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = service('request')->getIPAddress();
        }

        if (!isset($data['user_agent'])) {
            $data['user_agent'] = service('request')->getUserAgent()->getAgentString();
        }

        if (!isset($data['url'])) {
            $data['url'] = current_url();
        }

        if (!isset($data['method'])) {
            $data['method'] = service('request')->getMethod();
        }

        return $this->insert($data);
    }

    /**
     * Log authentication event
     * 
     * @param int|null $userId User ID (null for failed login)
     * @param string $action Action (login, logout, login_failed)
     * @param string $description Description
     * @param string $status Status
     * @return int|false
     */
    public function logAuth(?int $userId, string $action, string $description, string $status = 'success')
    {
        return $this->logAction([
            'user_id' => $userId,
            'module' => 'authentication',
            'action' => $action,
            'description' => $description,
            'status' => $status,
        ]);
    }

    /**
     * Log CRUD operation
     * 
     * @param int $userId User ID
     * @param string $module Module name
     * @param string $action Action (create, update, delete)
     * @param int $recordId Record ID
     * @param string $description Description
     * @param array|null $oldValues Old values (for update)
     * @param array|null $newValues New values
     * @return int|false
     */
    public function logCrud(
        int $userId,
        string $module,
        string $action,
        int $recordId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ) {
        return $this->logAction([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'status' => 'success',
        ]);
    }

    /**
     * Clean old logs
     * Delete logs older than specified days
     * 
     * @param int $days Days to keep
     * @return int Number of deleted records
     */
    public function cleanOldLogs(int $days = 90): int
    {
        $date = date('Y-m-d', strtotime("-{$days} days"));

        return $this->where('created_at <', $date)->delete();
    }

    /**
     * Export logs to array
     * 
     * @param array $filters Filters
     * @return array
     */
    public function exportLogs(array $filters = []): array
    {
        $builder = $this->withUser();

        if (isset($filters['start_date'])) {
            $builder->where('audit_logs.created_at >=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $builder->where('audit_logs.created_at <=', $filters['end_date']);
        }

        if (isset($filters['module'])) {
            $builder->where('audit_logs.module', $filters['module']);
        }

        if (isset($filters['action'])) {
            $builder->where('audit_logs.action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $builder->where('audit_logs.user_id', $filters['user_id']);
        }

        return $builder->orderBy('audit_logs.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get changes diff for update action
     * 
     * @param int $logId Log ID
     * @return array|null
     */
    public function getChangesDiff(int $logId): ?array
    {
        $log = $this->find($logId);

        if (!$log || $log->action !== 'update' || !$log->old_values || !$log->new_values) {
            return null;
        }

        $oldValues = json_decode($log->old_values, true);
        $newValues = json_decode($log->new_values, true);

        $changes = [];
        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Set default status before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultStatus(array $data): array
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'success';
        }

        return $data;
    }
}
