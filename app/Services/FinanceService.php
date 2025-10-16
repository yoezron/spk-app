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
     * Get payment statistics
     * 
     * @param int $year Year
     * @param int|null $month Month (optional)
     * @param int|null $userId User ID for regional scope (optional)
     * @return array
     */
    public function getPaymentStatistics(int $year, ?int $month = null, ?int $userId = null): array
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

            // Base filter
            $builder->where('YEAR(payments.payment_date)', $year);
            if ($month) {
                $builder->where('MONTH(payments.payment_date)', $month);
            }

            // Total payments
            $totalPayments = $builder->countAllResults(false);

            // Verified payments
            $verifiedPayments = (clone $builder)->where('status', 'verified')->countAllResults(false);

            // Pending payments
            $pendingPayments = (clone $builder)->where('status', 'pending')->countAllResults(false);

            // Rejected payments
            $rejectedPayments = (clone $builder)->where('status', 'rejected')->countAllResults(false);

            // Total amount (verified only)
            $totalAmountResult = (clone $builder)
                ->select('SUM(amount) as total')
                ->where('status', 'verified')
                ->get()
                ->getRow();

            $totalAmount = $totalAmountResult ? (float) $totalAmountResult->total : 0;

            // Amount by payment type (verified only)
            $amountByType = (clone $builder)
                ->select('payment_type, SUM(amount) as total')
                ->where('status', 'verified')
                ->groupBy('payment_type')
                ->get()
                ->getResult();

            $typeBreakdown = [];
            foreach ($amountByType as $type) {
                $typeBreakdown[$type->payment_type] = (float) $type->total;
            }

            return [
                'total_payments' => $totalPayments,
                'verified' => $verifiedPayments,
                'pending' => $pendingPayments,
                'rejected' => $rejectedPayments,
                'total_amount' => $totalAmount,
                'amount_by_type' => $typeBreakdown,
                'verification_rate' => $totalPayments > 0 ? round(($verifiedPayments / $totalPayments) * 100, 2) : 0
            ];
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
     * Get summary statistics
     * 
     * @param int $year Year
     * @param int|null $month Month (optional)
     * @param int|null $userId User ID for regional scope (optional)
     * @return array
     */
    public function getSummaryStatistics(int $year, ?int $month = null, ?int $userId = null): array
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

            // Filter
            $builder->where('YEAR(payments.payment_date)', $year)
                ->where('payments.status', 'verified');

            if ($month) {
                $builder->where('MONTH(payments.payment_date)', $month);
            }

            // Total verified amount
            $totalResult = (clone $builder)
                ->select('SUM(amount) as total, COUNT(*) as count')
                ->get()
                ->getRow();

            $totalAmount = $totalResult ? (float) $totalResult->total : 0;
            $totalCount = $totalResult ? (int) $totalResult->count : 0;

            // Average payment
            $averagePayment = $totalCount > 0 ? $totalAmount / $totalCount : 0;

            // Highest payment
            $highestResult = (clone $builder)
                ->select('MAX(amount) as highest')
                ->get()
                ->getRow();

            $highestPayment = $highestResult ? (float) $highestResult->highest : 0;

            // Lowest payment
            $lowestResult = (clone $builder)
                ->select('MIN(amount) as lowest')
                ->get()
                ->getRow();

            $lowestPayment = $lowestResult ? (float) $lowestResult->lowest : 0;

            // Count active paying members
            $activeMembers = (clone $builder)
                ->select('COUNT(DISTINCT user_id) as count')
                ->get()
                ->getRow();

            $activeMembersCount = $activeMembers ? (int) $activeMembers->count : 0;

            return [
                'total_amount' => $totalAmount,
                'total_count' => $totalCount,
                'average_payment' => $averagePayment,
                'highest_payment' => $highestPayment,
                'lowest_payment' => $lowestPayment,
                'active_members' => $activeMembersCount
            ];
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
}
