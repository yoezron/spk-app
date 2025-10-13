<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PaymentModel
 * 
 * Model untuk mengelola pembayaran iuran anggota SPK
 * Mendukung iuran pendaftaran, bulanan, dan verifikasi pembayaran
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'payment_type',
        'amount',
        'payment_date',
        'payment_method',
        'proof_file',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'notes',
        'period_month',
        'period_year',
        'bank_name',
        'account_number',
        'account_name'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'payment_type' => 'required|in_list[registration,monthly,annual,donation]',
        'amount' => 'required|decimal|greater_than[0]',
        'payment_date' => 'required|valid_date',
        'payment_method' => 'permit_empty|in_list[bank_transfer,cash,e-wallet,other]',
        'status' => 'permit_empty|in_list[pending,verified,rejected]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID harus diisi',
            'is_natural_no_zero' => 'User ID tidak valid',
        ],
        'payment_type' => [
            'required' => 'Tipe pembayaran harus diisi',
            'in_list' => 'Tipe pembayaran tidak valid',
        ],
        'amount' => [
            'required' => 'Jumlah pembayaran harus diisi',
            'decimal' => 'Jumlah pembayaran harus berupa angka',
            'greater_than' => 'Jumlah pembayaran harus lebih dari 0',
        ],
        'payment_date' => [
            'required' => 'Tanggal pembayaran harus diisi',
            'valid_date' => 'Format tanggal tidak valid',
        ],
        'status' => [
            'in_list' => 'Status tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultStatus'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get payment with user
     * 
     * @return object
     */
    public function withUser()
    {
        return $this->select('payments.*, users.username, users.email as user_email')
            ->join('users', 'users.id = payments.user_id', 'left');
    }

    /**
     * Get payment with member profile
     * 
     * @return object
     */
    public function withMemberProfile()
    {
        return $this->select('payments.*')
            ->select('users.username, users.email')
            ->select('member_profiles.full_name, member_profiles.membership_number, member_profiles.phone')
            ->join('users', 'users.id = payments.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left');
    }

    /**
     * Get payment with verifier
     * 
     * @return object
     */
    public function withVerifier()
    {
        return $this->select('payments.*, verifier_users.username as verifier_name')
            ->join('users as verifier_users', 'verifier_users.id = payments.verified_by', 'left');
    }

    /**
     * Get payment with complete relations
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('payments.*')
            ->select('users.username, users.email')
            ->select('member_profiles.full_name, member_profiles.membership_number, member_profiles.province_id, member_profiles.region_id')
            ->select('provinces.name as province_name')
            ->select('regions.name as region_name')
            ->select('verifier_users.username as verifier_name')
            ->join('users', 'users.id = payments.user_id', 'left')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('regions', 'regions.id = member_profiles.region_id', 'left')
            ->join('users as verifier_users', 'verifier_users.id = payments.verified_by', 'left');
    }

    // ========================================
    // SCOPES - FILTERING BY STATUS
    // ========================================

    /**
     * Get pending payments
     * 
     * @return object
     */
    public function pending()
    {
        return $this->where('status', 'pending');
    }

    /**
     * Get verified payments
     * 
     * @return object
     */
    public function verified()
    {
        return $this->where('status', 'verified');
    }

    /**
     * Get rejected payments
     * 
     * @return object
     */
    public function rejected()
    {
        return $this->where('status', 'rejected');
    }

    /**
     * Get payments by status
     * 
     * @param string $status Status value
     * @return object
     */
    public function byStatus(string $status)
    {
        return $this->where('status', $status);
    }

    // ========================================
    // SCOPES - FILTERING BY TYPE
    // ========================================

    /**
     * Get registration payments
     * 
     * @return object
     */
    public function registration()
    {
        return $this->where('payment_type', 'registration');
    }

    /**
     * Get monthly payments
     * 
     * @return object
     */
    public function monthly()
    {
        return $this->where('payment_type', 'monthly');
    }

    /**
     * Get annual payments
     * 
     * @return object
     */
    public function annual()
    {
        return $this->where('payment_type', 'annual');
    }

    /**
     * Get donation payments
     * 
     * @return object
     */
    public function donation()
    {
        return $this->where('payment_type', 'donation');
    }

    /**
     * Get payments by type
     * 
     * @param string $type Payment type
     * @return object
     */
    public function byType(string $type)
    {
        return $this->where('payment_type', $type);
    }

    // ========================================
    // SCOPES - FILTERING BY USER/REGION
    // ========================================

    /**
     * Get payments by user
     * 
     * @param int $userId User ID
     * @return object
     */
    public function byUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Get payments by region
     * 
     * @param int $regionId Region ID
     * @return object
     */
    public function byRegion(int $regionId)
    {
        return $this->join('member_profiles', 'member_profiles.user_id = payments.user_id')
            ->where('member_profiles.region_id', $regionId);
    }

    /**
     * Get payments by province
     * 
     * @param int $provinceId Province ID
     * @return object
     */
    public function byProvince(int $provinceId)
    {
        return $this->join('member_profiles', 'member_profiles.user_id = payments.user_id')
            ->where('member_profiles.province_id', $provinceId);
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get payments by period
     * 
     * @param int $month Month
     * @param int $year Year
     * @return object
     */
    public function byPeriod(int $month, int $year)
    {
        return $this->where('period_month', $month)
            ->where('period_year', $year);
    }

    /**
     * Get payments by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return object
     */
    public function byDateRange(string $startDate, string $endDate)
    {
        return $this->where('payment_date >=', $startDate)
            ->where('payment_date <=', $endDate);
    }

    /**
     * Search payments
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('account_name', $keyword)
            ->orLike('bank_name', $keyword)
            ->orLike('account_number', $keyword)
            ->orLike('notes', $keyword)
            ->groupEnd();
    }

    /**
     * Get recent payments
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function recent(int $limit = 10): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get payments awaiting verification
     * 
     * @return array
     */
    public function awaitingVerification(): array
    {
        return $this->withMemberProfile()
            ->pending()
            ->orderBy('payments.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get user's payment history
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserHistory(int $userId): array
    {
        return $this->withVerifier()
            ->byUser($userId)
            ->orderBy('payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Check if user has paid for period
     * 
     * @param int $userId User ID
     * @param int $month Month
     * @param int $year Year
     * @return bool
     */
    public function hasPaidForPeriod(int $userId, int $month, int $year): bool
    {
        return $this->byUser($userId)
            ->byPeriod($month, $year)
            ->verified()
            ->countAllResults() > 0;
    }

    /**
     * Get latest verified payment for user
     * 
     * @param int $userId User ID
     * @return object|null
     */
    public function getLatestVerified(int $userId)
    {
        return $this->byUser($userId)
            ->verified()
            ->orderBy('payment_date', 'DESC')
            ->first();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count payments by status
     * 
     * @return array
     */
    public function countByStatus(): array
    {
        $result = $this->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $stats = [
            'pending' => 0,
            'verified' => 0,
            'rejected' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->status] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Count payments by type
     * 
     * @return array
     */
    public function countByType(): array
    {
        $result = $this->select('payment_type, COUNT(*) as count')
            ->verified()
            ->groupBy('payment_type')
            ->findAll();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row->payment_type] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Calculate total payments
     * 
     * @param string|null $status Filter by status
     * @return float
     */
    public function calculateTotal(?string $status = null): float
    {
        $builder = $this->selectSum('amount');

        if ($status) {
            $builder->where('status', $status);
        }

        $result = $builder->first();
        return $result ? (float)$result->amount : 0.0;
    }

    /**
     * Calculate total by type
     * 
     * @return array
     */
    public function calculateTotalByType(): array
    {
        $result = $this->select('payment_type, SUM(amount) as total')
            ->verified()
            ->groupBy('payment_type')
            ->findAll();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row->payment_type] = (float)$row->total;
        }

        return $stats;
    }

    /**
     * Calculate total by region
     * 
     * @return array
     */
    public function calculateTotalByRegion(): array
    {
        return $this->select('regions.name as region_name, SUM(payments.amount) as total')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id')
            ->join('regions', 'regions.id = member_profiles.region_id', 'left')
            ->where('payments.status', 'verified')
            ->groupBy('member_profiles.region_id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Calculate total by province
     * 
     * @return array
     */
    public function calculateTotalByProvince(): array
    {
        return $this->select('provinces.name as province_name, SUM(payments.amount) as total')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->where('payments.status', 'verified')
            ->groupBy('member_profiles.province_id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get payment statistics by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return object
     */
    public function getStatsByDateRange(string $startDate, string $endDate)
    {
        return $this->select('
                COUNT(*) as total_payments,
                SUM(amount) as total_amount,
                AVG(amount) as avg_amount,
                MIN(amount) as min_amount,
                MAX(amount) as max_amount
            ')
            ->verified()
            ->byDateRange($startDate, $endDate)
            ->first();
    }

    /**
     * Get payment activity by month
     * 
     * @param int $months Number of months
     * @return array
     */
    public function getActivityByMonth(int $months = 12): array
    {
        return $this->select('
                DATE_FORMAT(payment_date, "%Y-%m") as month,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->verified()
            ->where('payment_date >=', date('Y-m-d', strtotime("-{$months} months")))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();
    }

    /**
     * Get top contributors
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getTopContributors(int $limit = 10): array
    {
        return $this->select('users.username, member_profiles.full_name, SUM(payments.amount) as total_contribution, COUNT(payments.id) as payment_count')
            ->join('users', 'users.id = payments.user_id')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
            ->where('payments.status', 'verified')
            ->groupBy('payments.user_id')
            ->orderBy('total_contribution', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get payment compliance rate
     * 
     * @param int $month Month
     * @param int $year Year
     * @return object
     */
    public function getComplianceRate(int $month, int $year)
    {
        $totalMembers = $this->db->table('member_profiles')
            ->where('membership_status', 'active')
            ->countAllResults();

        $paidMembers = $this->byPeriod($month, $year)
            ->verified()
            ->countAllResults();

        return (object)[
            'total_members' => $totalMembers,
            'paid_members' => $paidMembers,
            'unpaid_members' => $totalMembers - $paidMembers,
            'compliance_rate' => $totalMembers > 0 ? round(($paidMembers / $totalMembers) * 100, 2) : 0,
        ];
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Verify payment
     * 
     * @param int $paymentId Payment ID
     * @param int $verifierId User ID who verified
     * @param string|null $notes Verification notes
     * @return bool
     */
    public function verifyPayment(int $paymentId, int $verifierId, ?string $notes = null): bool
    {
        $data = [
            'status' => 'verified',
            'verified_by' => $verifierId,
            'verified_at' => date('Y-m-d H:i:s'),
        ];

        if ($notes) {
            $data['notes'] = $notes;
        }

        return $this->update($paymentId, $data);
    }

    /**
     * Reject payment
     * 
     * @param int $paymentId Payment ID
     * @param int $verifierId User ID who rejected
     * @param string $reason Rejection reason
     * @return bool
     */
    public function rejectPayment(int $paymentId, int $verifierId, string $reason): bool
    {
        $data = [
            'status' => 'rejected',
            'verified_by' => $verifierId,
            'verified_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason,
        ];

        return $this->update($paymentId, $data);
    }

    /**
     * Reset payment to pending
     * 
     * @param int $paymentId Payment ID
     * @return bool
     */
    public function resetToPending(int $paymentId): bool
    {
        $data = [
            'status' => 'pending',
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ];

        return $this->update($paymentId, $data);
    }

    /**
     * Update proof file
     * 
     * @param int $paymentId Payment ID
     * @param string $filePath File path
     * @return bool
     */
    public function updateProofFile(int $paymentId, string $filePath): bool
    {
        return $this->update($paymentId, ['proof_file' => $filePath]);
    }

    /**
     * Add payment record
     * 
     * @param array $data Payment data
     * @return int|false Payment ID or false
     */
    public function addPayment(array $data)
    {
        // Set period if monthly payment
        if ($data['payment_type'] === 'monthly') {
            if (!isset($data['period_month']) || !isset($data['period_year'])) {
                $date = strtotime($data['payment_date']);
                $data['period_month'] = date('n', $date);
                $data['period_year'] = date('Y', $date);
            }
        }

        return $this->insert($data);
    }

    /**
     * Get members who haven't paid for period
     * 
     * @param int $month Month
     * @param int $year Year
     * @param int|null $regionId Filter by region
     * @return array
     */
    public function getMembersNotPaid(int $month, int $year, ?int $regionId = null): array
    {
        $builder = $this->db->table('member_profiles')
            ->select('member_profiles.*, users.username, users.email')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('member_profiles.membership_status', 'active')
            ->whereNotIn('member_profiles.user_id', function ($builder) use ($month, $year) {
                return $builder->select('user_id')
                    ->from('payments')
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->where('status', 'verified');
            });

        if ($regionId) {
            $builder->where('member_profiles.region_id', $regionId);
        }

        return $builder->get()->getResult();
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
            $data['data']['status'] = 'pending';
        }

        if (!isset($data['data']['payment_method'])) {
            $data['data']['payment_method'] = 'bank_transfer';
        }

        return $data;
    }
}
