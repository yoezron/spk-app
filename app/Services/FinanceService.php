<?php

namespace App\Services;

use App\Models\PaymentModel;
use App\Models\MemberProfileModel;
use App\Models\UserModel;
use App\Services\RegionScopeService;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * FinanceService
 * 
 * Business logic untuk manajemen keuangan dan pembayaran iuran SPK
 * Handle verification, rejection, statistics, dan reporting
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class FinanceService
{
    /**
     * @var PaymentModel
     */
    protected $paymentModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var RegionScopeService
     */
    protected $regionScope;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->memberModel = new MemberProfileModel();
        $this->userModel = new UserModel();
        $this->regionScope = new RegionScopeService();
    }

    /**
     * Verify payment
     * 
     * @param int $paymentId Payment ID
     * @param int $verifierId Verifier User ID
     * @param string|null $notes Additional notes
     * @return array
     */
    public function verifyPayment(int $paymentId, int $verifierId, ?string $notes = null): array
    {
        try {
            $payment = $this->paymentModel->find($paymentId);

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan.'
                ];
            }

            if ($payment->status === 'verified') {
                return [
                    'success' => false,
                    'message' => 'Pembayaran sudah diverifikasi sebelumnya.'
                ];
            }

            if ($payment->status === 'rejected') {
                return [
                    'success' => false,
                    'message' => 'Pembayaran yang sudah ditolak tidak dapat diverifikasi.'
                ];
            }

            // Update payment status
            $data = [
                'status' => 'verified',
                'verified_by' => $verifierId,
                'verified_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => null
            ];

            if ($notes) {
                $data['notes'] = $notes;
            }

            $this->paymentModel->update($paymentId, $data);

            // Update member status if registration payment
            if ($payment->payment_type === 'registration') {
                $this->updateMemberStatusOnPayment($payment->user_id);
            }

            // Log activity
            $this->logActivity($verifierId, 'verify_payment', $paymentId, [
                'payment_id' => $paymentId,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'type' => $payment->payment_type
            ]);

            // Clear payment cache
            $this->clearPaymentCache();

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil diverifikasi.',
                'data' => $this->paymentModel->find($paymentId)
            ];
        } catch (DatabaseException $e) {
            log_message('error', 'FinanceService::verifyPayment - Database Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan database saat memverifikasi pembayaran.'
            ];
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::verifyPayment - Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi pembayaran.'
            ];
        }
    }

    /**
     * Reject payment
     * 
     * @param int $paymentId Payment ID
     * @param int $verifierId Verifier User ID
     * @param string $reason Rejection reason
     * @return array
     */
    public function rejectPayment(int $paymentId, int $verifierId, string $reason): array
    {
        try {
            $payment = $this->paymentModel->find($paymentId);

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan.'
                ];
            }

            if ($payment->status === 'verified') {
                return [
                    'success' => false,
                    'message' => 'Pembayaran yang sudah diverifikasi tidak dapat ditolak.'
                ];
            }

            // Update payment status
            $data = [
                'status' => 'rejected',
                'verified_by' => $verifierId,
                'verified_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ];

            $this->paymentModel->update($paymentId, $data);

            // Log activity
            $this->logActivity($verifierId, 'reject_payment', $paymentId, [
                'payment_id' => $paymentId,
                'user_id' => $payment->user_id,
                'reason' => $reason
            ]);

            // Clear payment cache
            $this->clearPaymentCache();

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil ditolak.',
                'data' => $this->paymentModel->find($paymentId)
            ];
        } catch (DatabaseException $e) {
            log_message('error', 'FinanceService::rejectPayment - Database Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan database saat menolak pembayaran.'
            ];
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::rejectPayment - Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menolak pembayaran.'
            ];
        }
    }

    /**
     * Get payment statistics - OPTIMIZED with caching
     * Single aggregated query instead of multiple clones
     *
     * @param int $year Year
     * @param int|null $month Month (optional)
     * @param int|null $userId User ID for regional scope (optional)
     * @return array
     */
    public function getPaymentStatistics(int $year, ?int $month = null, ?int $userId = null): array
    {
        // Use cache for better performance (5 minutes TTL)
        $scopeId = $userId ?? 'all';
        $cacheKey = "payment_stats_{$year}_" . ($month ?? 'all') . "_{$scopeId}";
        $cache = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // OPTIMIZED: Single aggregated query for counts and totals
            $builder = $this->paymentModel->builder();

            // Apply regional scope if needed
            if ($userId) {
                $user = $this->userModel->find($userId);
                if ($user && in_array('koordinator', $user->getGroups())) {
                    $builder = $this->regionScope->applyScopeToPayments($builder, $userId);
                }
            }

            // Base filter
            $builder->where('YEAR(payments.payment_date)', $year);
            if ($month) {
                $builder->where('MONTH(payments.payment_date)', $month);
            }

            // OPTIMIZED: Single query for all counts and basic stats
            $stats = $builder->select('
                COUNT(*) as total_payments,
                COUNT(CASE WHEN status = "verified" THEN 1 END) as verified,
                COUNT(CASE WHEN status = "pending" THEN 1 END) as pending,
                COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected,
                SUM(CASE WHEN status = "verified" THEN amount ELSE 0 END) as total_amount
            ')->get()->getRow();

            // Get amount by type (separate query, but only one)
            $amountByType = $this->paymentModel->builder()
                ->select('payment_type, SUM(amount) as total')
                ->where('YEAR(payment_date)', $year)
                ->where('status', 'verified');

            if ($month) {
                $amountByType->where('MONTH(payment_date)', $month);
            }

            if ($userId) {
                $user = $this->userModel->find($userId);
                if ($user && in_array('koordinator', $user->getGroups())) {
                    $amountByType = $this->regionScope->applyScopeToPayments($amountByType, $userId);
                }
            }

            $typeResult = $amountByType->groupBy('payment_type')->get()->getResult();

            $typeBreakdown = [];
            foreach ($typeResult as $type) {
                $typeBreakdown[$type->payment_type] = (float) $type->total;
            }

            $result = [
                'total_payments' => (int) $stats->total_payments,
                'verified' => (int) $stats->verified,
                'pending' => (int) $stats->pending,
                'rejected' => (int) $stats->rejected,
                'total_amount' => (float) $stats->total_amount,
                'amount_by_type' => $typeBreakdown,
                'verification_rate' => $stats->total_payments > 0
                    ? round(($stats->verified / $stats->total_payments) * 100, 2)
                    : 0
            ];

            // Cache for 5 minutes
            $cache->save($cacheKey, $result, 300);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::getPaymentStatistics - Error: ' . $e->getMessage());
            return [
                'total_payments' => 0,
                'verified' => 0,
                'pending' => 0,
                'rejected' => 0,
                'total_amount' => 0,
                'amount_by_type' => [],
                'verification_rate' => 0
            ];
        }
    }

    /**
     * Generate financial report
     * 
     * @param int $year Year
     * @param int|null $month Month (optional)
     * @param string $groupBy Group by field (month, province, type)
     * @param int|null $userId User ID for regional scope (optional)
     * @return array
     */
    public function generateReport(int $year, ?int $month = null, string $groupBy = 'month', ?int $userId = null): array
    {
        try {
            $builder = $this->paymentModel->builder();

            // Apply regional scope if needed
            if ($userId) {
                $user = $this->userModel->find($userId);
                if ($user && in_array('koordinator', $user->getGroups())) {
                    $builder = $this->regionScope->applyScopeToPayments($builder, $userId);
                }
            }

            // Join with member profiles for province data
            $builder->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left');

            // Base filter - only verified payments
            $builder->where('YEAR(payments.payment_date)', $year)
                ->where('payments.status', 'verified');

            if ($month) {
                $builder->where('MONTH(payments.payment_date)', $month);
            }

            // Group by logic
            switch ($groupBy) {
                case 'month':
                    $builder->select('MONTH(payments.payment_date) as month, COUNT(*) as count, SUM(payments.amount) as total')
                        ->groupBy('MONTH(payments.payment_date)')
                        ->orderBy('month', 'ASC');
                    break;

                case 'province':
                    $builder->select('member_profiles.province_name as province, COUNT(*) as count, SUM(payments.amount) as total')
                        ->groupBy('member_profiles.province_name')
                        ->orderBy('total', 'DESC');
                    break;

                case 'type':
                    $builder->select('payments.payment_type as type, COUNT(*) as count, SUM(payments.amount) as total')
                        ->groupBy('payments.payment_type')
                        ->orderBy('total', 'DESC');
                    break;

                default:
                    $builder->select('MONTH(payments.payment_date) as month, COUNT(*) as count, SUM(payments.amount) as total')
                        ->groupBy('MONTH(payments.payment_date)')
                        ->orderBy('month', 'ASC');
            }

            $result = $builder->get()->getResult();

            // Format result
            $reportData = [];
            foreach ($result as $row) {
                $reportData[] = [
                    'label' => $this->formatLabel($row, $groupBy),
                    'count' => (int) $row->count,
                    'total' => (float) $row->total
                ];
            }

            return $reportData;
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::generateReport - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get summary statistics - OPTIMIZED with caching
     * Single aggregated query instead of multiple clones
     *
     * @param int $year Year
     * @param int|null $month Month (optional)
     * @param int|null $userId User ID for regional scope (optional)
     * @return array
     */
    public function getSummaryStatistics(int $year, ?int $month = null, ?int $userId = null): array
    {
        // Use cache for better performance (10 minutes TTL)
        $scopeId = $userId ?? 'all';
        $cacheKey = "payment_summary_{$year}_" . ($month ?? 'all') . "_{$scopeId}";
        $cache = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $builder = $this->paymentModel->builder();

            // Apply regional scope if needed
            if ($userId) {
                $user = $this->userModel->find($userId);
                if ($user && in_array('koordinator', $user->getGroups())) {
                    $builder = $this->regionScope->applyScopeToPayments($builder, $userId);
                }
            }

            // Filter
            $builder->where('YEAR(payments.payment_date)', $year)
                ->where('payments.status', 'verified');

            if ($month) {
                $builder->where('MONTH(payments.payment_date)', $month);
            }

            // OPTIMIZED: Single query for all summary statistics
            $stats = $builder->select('
                SUM(amount) as total_amount,
                COUNT(*) as total_count,
                AVG(amount) as average_payment,
                MAX(amount) as highest_payment,
                MIN(amount) as lowest_payment,
                COUNT(DISTINCT user_id) as active_members
            ')->get()->getRow();

            $result = [
                'total_amount' => $stats ? (float) $stats->total_amount : 0,
                'total_count' => $stats ? (int) $stats->total_count : 0,
                'average_payment' => $stats ? (float) $stats->average_payment : 0,
                'highest_payment' => $stats ? (float) $stats->highest_payment : 0,
                'lowest_payment' => $stats ? (float) $stats->lowest_payment : 0,
                'active_members' => $stats ? (int) $stats->active_members : 0
            ];

            // Cache for 10 minutes
            $cache->save($cacheKey, $result, 600);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::getSummaryStatistics - Error: ' . $e->getMessage());
            return [
                'total_amount' => 0,
                'total_count' => 0,
                'average_payment' => 0,
                'highest_payment' => 0,
                'lowest_payment' => 0,
                'active_members' => 0
            ];
        }
    }

    /**
     * Update member status after registration payment verification
     * 
     * @param int $userId User ID
     * @return void
     */
    protected function updateMemberStatusOnPayment(int $userId): void
    {
        try {
            $member = $this->memberModel->where('user_id', $userId)->first();

            if ($member && $member->status === 'pending') {
                // Check if member is already approved but waiting for payment
                // This is handled by ApproveMemberService, so we just log here
                log_message('info', "Registration payment verified for user {$userId}");
            }
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::updateMemberStatusOnPayment - Error: ' . $e->getMessage());
        }
    }

    /**
     * Format label for report
     * 
     * @param object $row Data row
     * @param string $groupBy Group by field
     * @return string
     */
    protected function formatLabel($row, string $groupBy): string
    {
        switch ($groupBy) {
            case 'month':
                $months = [
                    '',
                    'Januari',
                    'Februari',
                    'Maret',
                    'April',
                    'Mei',
                    'Juni',
                    'Juli',
                    'Agustus',
                    'September',
                    'Oktober',
                    'November',
                    'Desember'
                ];
                return $months[$row->month] ?? 'Unknown';

            case 'province':
                return $row->province ?? 'Tidak Diketahui';

            case 'type':
                $types = [
                    'registration' => 'Pendaftaran',
                    'monthly' => 'Bulanan',
                    'annual' => 'Tahunan',
                    'donation' => 'Donasi'
                ];
                return $types[$row->type] ?? ucfirst($row->type);

            default:
                return 'Unknown';
        }
    }

    /**
     * Log activity
     * 
     * @param int $userId User ID
     * @param string $action Action name
     * @param int $targetId Target ID
     * @param array $details Additional details
     * @return void
     */
    protected function logActivity(int $userId, string $action, int $targetId, array $details = []): void
    {
        try {
            $db = \Config\Database::connect();

            $data = [
                'user_id' => $userId,
                'action' => $action,
                'table_name' => 'payments',
                'record_id' => $targetId,
                'details' => json_encode($details),
                'ip_address' => service('request')->getIPAddress(),
                'user_agent' => service('request')->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->table('audit_logs')->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::logActivity - Error: ' . $e->getMessage());
        }
    }

    /**
     * Validate payment amount
     *
     * @param float $amount Amount
     * @param string $type Payment type
     * @return array
     */
    public function validatePaymentAmount(float $amount, string $type): array
    {
        // Define minimum amounts per type
        $minimumAmounts = [
            'registration' => 50000,  // Rp 50.000
            'monthly' => 25000,       // Rp 25.000
            'annual' => 300000,       // Rp 300.000
            'donation' => 10000       // Rp 10.000
        ];

        $minimum = $minimumAmounts[$type] ?? 0;

        if ($amount < $minimum) {
            return [
                'success' => false,
                'message' => "Jumlah pembayaran minimal untuk {$type} adalah Rp " . number_format($minimum, 0, ',', '.')
            ];
        }

        return [
            'success' => true,
            'message' => 'Jumlah pembayaran valid.'
        ];
    }

    /**
     * Clear payment statistics cache
     * Call this when payments are verified/rejected or updated
     *
     * @return bool
     */
    public function clearPaymentCache(): bool
    {
        try {
            $cache = \Config\Services::cache();

            // Clear all payment cache keys
            $cacheKeys = [];

            // Years to clear (current + 5 years back)
            for ($year = date('Y'); $year >= date('Y') - 5; $year--) {
                // For each month
                for ($month = 1; $month <= 12; $month++) {
                    $cacheKeys[] = "payment_stats_{$year}_{$month}_all";
                    $cacheKeys[] = "payment_stats_{$year}_all_all";
                    $cacheKeys[] = "payment_summary_{$year}_{$month}_all";
                    $cacheKeys[] = "payment_summary_{$year}_all_all";

                    // For each possible user ID (up to 100 users)
                    for ($userId = 1; $userId <= 100; $userId++) {
                        $cacheKeys[] = "payment_stats_{$year}_{$month}_{$userId}";
                        $cacheKeys[] = "payment_summary_{$year}_{$month}_{$userId}";
                    }
                }
            }

            foreach ($cacheKeys as $key) {
                $cache->delete($key);
            }

            log_message('info', 'Payment cache cleared successfully');
            return true;
        } catch (\Exception $e) {
            log_message('error', 'FinanceService::clearPaymentCache - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk verify payments
     * Verify multiple payments at once
     *
     * @param array $paymentIds Array of payment IDs
     * @param int $verifierId User ID who verified
     * @param string|null $notes Verification notes
     * @return array
     */
    public function bulkVerifyPayments(array $paymentIds, int $verifierId, ?string $notes = null): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($paymentIds as $paymentId) {
                $result = $this->verifyPayment($paymentId, $verifierId, $notes);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Payment #{$paymentId}: {$result['message']}";
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Transaction failed'
                ];
            }

            // Clear cache after bulk operation
            $this->clearPaymentCache();

            return [
                'success' => true,
                'message' => "Berhasil verifikasi {$successCount} pembayaran" . ($errorCount > 0 ? ", {$errorCount} gagal" : ''),
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'FinanceService::bulkVerifyPayments - Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat bulk verify: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk reject payments
     * Reject multiple payments at once
     *
     * @param array $paymentIds Array of payment IDs
     * @param int $verifierId User ID who rejected
     * @param string $reason Rejection reason
     * @return array
     */
    public function bulkRejectPayments(array $paymentIds, int $verifierId, string $reason): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($paymentIds as $paymentId) {
                $result = $this->rejectPayment($paymentId, $verifierId, $reason);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Payment #{$paymentId}: {$result['message']}";
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Transaction failed'
                ];
            }

            // Clear cache after bulk operation
            $this->clearPaymentCache();

            return [
                'success' => true,
                'message' => "Berhasil reject {$successCount} pembayaran" . ($errorCount > 0 ? ", {$errorCount} gagal" : ''),
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'FinanceService::bulkRejectPayments - Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat bulk reject: ' . $e->getMessage()
            ];
        }
    }
}
