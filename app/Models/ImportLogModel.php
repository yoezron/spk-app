<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ImportLogModel
 * 
 * Model untuk tabel import_logs
 * Mencatat history import bulk anggota
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ImportLogModel extends Model
{
    protected $table            = 'import_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'imported_by',
        'filename',
        'total_rows',
        'success_count',
        'failed_count',
        'duplicate_count',
        'error_details',
        'status',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'imported_by' => 'required|integer',
        'filename'    => 'required|string|max_length[255]',
        'status'      => 'required|in_list[processing,completed,failed]',
    ];

    protected $validationMessages = [
        'imported_by' => [
            'required' => 'User ID yang mengimport harus diisi',
            'integer'  => 'User ID harus berupa angka',
        ],
        'filename' => [
            'required'   => 'Nama file harus diisi',
            'max_length' => 'Nama file maksimal 255 karakter',
        ],
        'status' => [
            'required' => 'Status harus diisi',
            'in_list'  => 'Status harus processing, completed, atau failed',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get import logs with user info
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getLogsWithUser(array $filters = []): array
    {
        $builder = $this->select('import_logs.*, users.username, users.email')
            ->join('users', 'users.id = import_logs.imported_by', 'left')
            ->orderBy('import_logs.created_at', 'DESC');

        // Apply filters
        if (isset($filters['status'])) {
            $builder->where('import_logs.status', $filters['status']);
        }

        if (isset($filters['imported_by'])) {
            $builder->where('import_logs.imported_by', $filters['imported_by']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('import_logs.created_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('import_logs.created_at <=', $filters['date_to']);
        }

        return $builder->findAll();
    }

    /**
     * Get import statistics
     * 
     * @param int|null $userId Optional filter by user
     * @return array
     */
    public function getStatistics(?int $userId = null): array
    {
        $builder = $this->select('
            COUNT(*) as total_imports,
            SUM(total_rows) as total_rows_processed,
            SUM(success_count) as total_success,
            SUM(failed_count) as total_failed,
            SUM(duplicate_count) as total_duplicates
        ');

        if ($userId) {
            $builder->where('imported_by', $userId);
        }

        $result = $builder->first();

        return [
            'total_imports' => (int) ($result->total_imports ?? 0),
            'total_rows_processed' => (int) ($result->total_rows_processed ?? 0),
            'total_success' => (int) ($result->total_success ?? 0),
            'total_failed' => (int) ($result->total_failed ?? 0),
            'total_duplicates' => (int) ($result->total_duplicates ?? 0),
            'success_rate' => $result->total_rows_processed > 0
                ? round(($result->total_success / $result->total_rows_processed) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get recent imports
     * 
     * @param int $limit Number of records
     * @param int|null $userId Optional filter by user
     * @return array
     */
    public function getRecentImports(int $limit = 10, ?int $userId = null): array
    {
        $builder = $this->select('import_logs.*, users.username')
            ->join('users', 'users.id = import_logs.imported_by', 'left')
            ->orderBy('import_logs.created_at', 'DESC')
            ->limit($limit);

        if ($userId) {
            $builder->where('import_logs.imported_by', $userId);
        }

        return $builder->findAll();
    }

    /**
     * Get error details from import log
     * 
     * @param int $importLogId Import log ID
     * @return array
     */
    public function getErrorDetails(int $importLogId): array
    {
        $log = $this->find($importLogId);

        if (!$log || empty($log->error_details)) {
            return [];
        }

        $errors = json_decode($log->error_details, true);

        return is_array($errors) ? $errors : [];
    }

    /**
     * Mark import as completed
     * 
     * @param int $importLogId Import log ID
     * @param array $results Import results
     * @return bool
     */
    public function markAsCompleted(int $importLogId, array $results): bool
    {
        return $this->update($importLogId, [
            'status' => 'completed',
            'total_rows' => $results['total'] ?? 0,
            'success_count' => $results['success'] ?? 0,
            'failed_count' => $results['failed'] ?? 0,
            'duplicate_count' => $results['duplicates'] ?? 0,
            'error_details' => json_encode($results['errors'] ?? []),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark import as failed
     * 
     * @param int $importLogId Import log ID
     * @param string $errorMessage Error message
     * @return bool
     */
    public function markAsFailed(int $importLogId, string $errorMessage): bool
    {
        return $this->update($importLogId, [
            'status' => 'failed',
            'error_details' => json_encode(['error' => $errorMessage]),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete old import logs
     * 
     * @param int $daysOld Number of days to keep
     * @return int Number of deleted records
     */
    public function deleteOldLogs(int $daysOld = 90): int
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));

        return $this->where('created_at <', $date)
            ->where('status', 'completed')
            ->delete();
    }
}
