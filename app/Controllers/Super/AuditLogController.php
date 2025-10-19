<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * AuditLogController
 * 
 * Mengelola audit logs sistem untuk Super Admin
 * View, filter, search, dan export audit trail
 * 
 * @package App\Controllers\Super
 * @author  SPK Development Team
 * @version 1.0.0
 */
class AuditLogController extends BaseController
{
    /**
     * @var AuditLogModel
     */
    protected $auditLogModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->auditLogModel = new AuditLogModel();
    }

    /**
     * Display audit logs with filters
     * 
     * @return string|RedirectResponse
     */
    public function index()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        // Get filters from request
        $filters = [
            'user_id' => $this->request->getGet('user_id'),
            'action' => $this->request->getGet('action'),
            'module' => $this->request->getGet('module'),
            'status' => $this->request->getGet('status'),
            'severity' => $this->request->getGet('severity'),
            'entity_type' => $this->request->getGet('entity_type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'search' => $this->request->getGet('search')
        ];

        // Pagination
        $perPage = (int) ($this->request->getGet('per_page') ?? 50);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        // Get logs with filters
        $logs = $this->auditLogModel->getLogsWithUsers($filters, $perPage, $offset);
        $total = $this->auditLogModel->countFiltered($filters);

        // Get filter options
        $actions = $this->auditLogModel->getActions();
        $modules = $this->auditLogModel->getModules();
        $entityTypes = $this->auditLogModel->getEntityTypes();

        // Get all users for filter
        $userModel = new \CodeIgniter\Shield\Models\UserModel();
        $users = $userModel->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->where('users.active', 1)
            ->orderBy('users.username', 'ASC')
            ->findAll();

        // Calculate pagination
        $totalPages = ceil($total / $perPage);

        $data = [
            'title' => 'Audit Logs',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Audit Logs']
            ],
            'logs' => $logs,
            'filters' => $filters,
            'actions' => $actions,
            'modules' => $modules,
            'entityTypes' => $entityTypes,
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'showing_from' => $offset + 1,
                'showing_to' => min($offset + $perPage, $total)
            ]
        ];

        return view('super/audit_logs/index', $data);
    }

    /**
     * View single audit log detail
     * 
     * @param int $id
     * @return string|RedirectResponse
     */
    public function view(int $id)
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $log = $this->auditLogModel->select('audit_logs.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->join('auth_identities', 'auth_identities.user_id = audit_logs.user_id AND auth_identities.type = "email_password"', 'left')
            ->find($id);

        if (!$log) {
            return redirect()->to('/super/audit-logs')
                ->with('error', 'Audit log tidak ditemukan.');
        }

        // Decode JSON fields
        if ($log->old_values) {
            $log->old_values = json_decode($log->old_values, true);
        }
        if ($log->new_values) {
            $log->new_values = json_decode($log->new_values, true);
        }
        if ($log->metadata) {
            $log->metadata = json_decode($log->metadata, true);
        }

        $data = [
            'title' => 'Audit Log Detail',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Audit Logs', 'url' => base_url('super/audit-logs')],
                ['title' => 'Detail']
            ],
            'log' => $log
        ];

        return view('super/audit_logs/view', $data);
    }

    /**
     * Get statistics dashboard
     * 
     * @return string|RedirectResponse
     */
    public function statistics()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $days = (int) ($this->request->getGet('days') ?? 30);

        // Get statistics
        $stats = $this->auditLogModel->getActivityStats($days);

        $data = [
            'title' => 'Audit Log Statistics',
            'breadcrumbs' => [
                ['title' => 'Super Admin'],
                ['title' => 'Audit Logs', 'url' => base_url('super/audit-logs')],
                ['title' => 'Statistics']
            ],
            'stats' => $stats,
            'days' => $days
        ];

        return view('super/audit_logs/statistics', $data);
    }

    /**
     * Export audit logs to Excel
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface|RedirectResponse
     */
    public function export()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            // Get filters
            $filters = [
                'user_id' => $this->request->getGet('user_id'),
                'action' => $this->request->getGet('action'),
                'module' => $this->request->getGet('module'),
                'severity' => $this->request->getGet('severity'),
                'entity_type' => $this->request->getGet('entity_type'),
                'date_from' => $this->request->getGet('date_from'),
                'date_to' => $this->request->getGet('date_to'),
                'search' => $this->request->getGet('search')
            ];

            // Get all logs (no limit for export)
            $logs = $this->auditLogModel->getLogsWithUsers($filters, 10000, 0);

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'ID',
                'Date/Time',
                'User',
                'Email',
                'Action',
                'Module',
                'Entity Type',
                'Entity Name',
                'Severity',
                'IP Address',
                'Description'
            ];

            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $headerStyle = $sheet->getStyle('A1:K1');
            $headerStyle->getFont()->setBold(true);
            $headerStyle->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

            // Add data
            $row = 2;
            foreach ($logs as $log) {
                $sheet->fromArray([
                    $log->id,
                    $log->created_at,
                    $log->username ?? 'System',
                    $log->email ?? '-',
                    $log->action,
                    $log->module ?? '-',
                    $log->entity_type ?? '-',
                    $log->entity_name ?? '-',
                    strtoupper($log->severity),
                    $log->ip_address ?? '-',
                    $log->action_description ?? '-'
                ], null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Generate filename
            $filename = 'audit_logs_' . date('YmdHis') . '.xlsx';

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error exporting audit logs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export audit logs.');
        }
    }

    /**
     * Clean old audit logs
     * 
     * @return RedirectResponse
     */
    public function clean()
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $daysToKeep = (int) ($this->request->getPost('days_to_keep') ?? 90);

        try {
            $deletedCount = $this->auditLogModel->cleanOldLogs($daysToKeep);

            return redirect()->to('/super/audit-logs')
                ->with('success', "{$deletedCount} audit log lama berhasil dihapus.");
        } catch (\Exception $e) {
            log_message('error', 'Error cleaning audit logs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membersihkan audit logs.');
        }
    }

    /**
     * Get logs by entity (AJAX)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getByEntity()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akses ditolak'
            ])->setStatusCode(403);
        }

        $entityType = $this->request->getGet('entity_type');
        $entityId = $this->request->getGet('entity_id');

        if (!$entityType || !$entityId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Entity type and ID required'
            ])->setStatusCode(400);
        }

        try {
            $logs = $this->auditLogModel->getByEntity($entityType, (int)$entityId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting logs by entity: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting logs'
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete single audit log
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        // CRITICAL: Check Super Admin permission
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            $log = $this->auditLogModel->find($id);

            if (!$log) {
                return redirect()->back()->with('error', 'Audit log tidak ditemukan.');
            }

            $this->auditLogModel->delete($id);

            return redirect()->to('/super/audit-logs')
                ->with('success', 'Audit log berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting audit log: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus audit log.');
        }
    }
}
