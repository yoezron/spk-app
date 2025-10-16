<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\MemberProfileModel;
use App\Services\FinanceService;
use App\Services\FileUploadService;
use App\Services\Communication\NotificationService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * PaymentController (Member)
 * 
 * Portal anggota untuk manajemen pembayaran iuran
 * Upload bukti pembayaran, view riwayat, dan tracking status
 * 
 * @package App\Controllers\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PaymentController extends BaseController
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
     * @var FinanceService
     */
    protected $financeService;

    /**
     * @var FileUploadService
     */
    protected $fileUploadService;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->memberModel = new MemberProfileModel();
        $this->financeService = new FinanceService();
        $this->fileUploadService = new FileUploadService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Display member's payment history
     * 
     * @return string
     */
    public function index()
    {
        $userId = auth()->id();

        // Get payment history
        $payments = $this->paymentModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        $pager = $this->paymentModel->pager;

        // Get payment summary
        $summary = $this->getPaymentSummary($userId);

        // Get member profile for display
        $member = $this->memberModel->where('user_id', $userId)->first();

        $data = [
            'title' => 'Riwayat Pembayaran Iuran',
            'payments' => $payments,
            'pager' => $pager,
            'summary' => $summary,
            'member' => $member
        ];

        return view('member/payment/index', $data);
    }

    /**
     * Show upload payment form
     * 
     * @return string
     */
    public function create()
    {
        // Check if member is active
        $member = $this->memberModel->where('user_id', auth()->id())->first();

        if (!$member) {
            return redirect()->to('/member/profile')
                ->with('error', 'Profil anggota tidak ditemukan. Silakan lengkapi profil terlebih dahulu.');
        }

        if ($member->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Hanya anggota aktif yang dapat melakukan pembayaran iuran.');
        }

        // Get current year and month for period
        $currentYear = date('Y');
        $currentMonth = date('n');

        // Check if already paid for current month
        $existingPayment = $this->paymentModel
            ->where('user_id', auth()->id())
            ->where('payment_type', 'monthly')
            ->where('period_year', $currentYear)
            ->where('period_month', $currentMonth)
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        $data = [
            'title' => 'Upload Bukti Pembayaran',
            'member' => $member,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'existingPayment' => $existingPayment
        ];

        return view('member/payment/upload', $data);
    }

    /**
     * Process payment upload
     * 
     * @return RedirectResponse
     */
    public function store()
    {
        // Validation rules
        $rules = [
            'payment_type' => 'required|in_list[monthly,annual,donation]',
            'amount' => 'required|decimal|greater_than[0]',
            'payment_date' => 'required|valid_date',
            'payment_method' => 'permit_empty|in_list[bank_transfer,cash,e-wallet,other]',
            'proof_file' => 'uploaded[proof_file]|max_size[proof_file,2048]|ext_in[proof_file,jpg,jpeg,png,pdf]',
            'bank_name' => 'permit_empty|string|max_length[100]',
            'account_name' => 'permit_empty|string|max_length[100]',
            'notes' => 'permit_empty|string|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userId = auth()->id();
        $paymentType = $this->request->getPost('payment_type');
        $amount = $this->request->getPost('amount');

        // Validate amount
        $validation = $this->financeService->validatePaymentAmount($amount, $paymentType);
        if (!$validation['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $validation['message']);
        }

        // Check if member is active
        $member = $this->memberModel->where('user_id', $userId)->first();
        if (!$member || $member->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Hanya anggota aktif yang dapat melakukan pembayaran iuran.');
        }

        try {
            // Upload proof file
            $file = $this->request->getFile('proof_file');
            $uploadResult = $this->fileUploadService->upload($file, 'payment_proofs');

            if (!$uploadResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $uploadResult['message']);
            }

            // Prepare payment data
            $paymentData = [
                'user_id' => $userId,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'payment_date' => $this->request->getPost('payment_date'),
                'payment_method' => $this->request->getPost('payment_method') ?? 'bank_transfer',
                'proof_file' => $uploadResult['data']['file_path'],
                'bank_name' => $this->request->getPost('bank_name'),
                'account_name' => $this->request->getPost('account_name'),
                'notes' => $this->request->getPost('notes'),
                'status' => 'pending'
            ];

            // Add period for monthly/annual payments
            if (in_array($paymentType, ['monthly', 'annual'])) {
                $paymentData['period_month'] = $this->request->getPost('period_month') ?? date('n');
                $paymentData['period_year'] = $this->request->getPost('period_year') ?? date('Y');

                // Check duplicate payment for the same period
                $existing = $this->paymentModel
                    ->where('user_id', $userId)
                    ->where('payment_type', $paymentType)
                    ->where('period_year', $paymentData['period_year'])
                    ->where('period_month', $paymentData['period_month'])
                    ->whereIn('status', ['pending', 'verified'])
                    ->first();

                if ($existing) {
                    // Delete uploaded file
                    $this->fileUploadService->delete($uploadResult['data']['file_path']);

                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Anda sudah memiliki pembayaran untuk periode ini yang sedang diproses atau sudah diverifikasi.');
                }
            }

            // Insert payment
            if ($this->paymentModel->insert($paymentData)) {
                // Send notification to admin
                $this->notificationService->notifyAdminNewPayment($userId, $paymentData);

                return redirect()->to('/member/payment')
                    ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi dari pengurus.');
            } else {
                // Delete uploaded file if insert fails
                $this->fileUploadService->delete($uploadResult['data']['file_path']);

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal menyimpan data pembayaran. Silakan coba lagi.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Member\PaymentController::store - Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
        }
    }

    /**
     * Show payment detail
     * 
     * @param int $id Payment ID
     * @return string|RedirectResponse
     */
    public function detail($id)
    {
        $payment = $this->paymentModel->find($id);

        if (!$payment) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        // Check ownership
        if ($payment->user_id != auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pembayaran ini.');
        }

        // Get verifier info if verified
        $verifier = null;
        if ($payment->verified_by) {
            $verifier = model('UserModel')
                ->select('users.username, member_profiles.full_name')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->find($payment->verified_by);
        }

        $data = [
            'title' => 'Detail Pembayaran',
            'payment' => $payment,
            'verifier' => $verifier
        ];

        return view('member/payment/detail', $data);
    }

    /**
     * Download payment proof
     * 
     * @param int $id Payment ID
     * @return \CodeIgniter\HTTP\ResponseInterface|RedirectResponse
     */
    public function download($id)
    {
        $payment = $this->paymentModel->find($id);

        if (!$payment) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        // Check ownership
        if ($payment->user_id != auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke file ini.');
        }

        if (!$payment->proof_file) {
            return redirect()->back()->with('error', 'File bukti pembayaran tidak ditemukan.');
        }

        // Get file path
        $filePath = WRITEPATH . 'uploads/' . $payment->proof_file;

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        // Download file
        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }

    /**
     * Delete/Cancel payment (only pending status)
     * 
     * @param int $id Payment ID
     * @return RedirectResponse
     */
    public function cancel($id)
    {
        $payment = $this->paymentModel->find($id);

        if (!$payment) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        // Check ownership
        if ($payment->user_id != auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membatalkan pembayaran ini.');
        }

        // Only pending payments can be cancelled
        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pembayaran dengan status pending yang dapat dibatalkan.');
        }

        try {
            // Delete proof file
            if ($payment->proof_file) {
                $this->fileUploadService->delete($payment->proof_file);
            }

            // Delete payment record
            if ($this->paymentModel->delete($id)) {
                return redirect()->to('/member/payment')
                    ->with('success', 'Pembayaran berhasil dibatalkan.');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal membatalkan pembayaran.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Member\PaymentController::cancel - Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membatalkan pembayaran.');
        }
    }

    /**
     * Get payment summary for member
     * 
     * @param int $userId User ID
     * @return array
     */
    protected function getPaymentSummary(int $userId): array
    {
        try {
            // Total payments
            $totalPayments = $this->paymentModel
                ->where('user_id', $userId)
                ->countAllResults();

            // Verified payments
            $verifiedPayments = $this->paymentModel
                ->where('user_id', $userId)
                ->where('status', 'verified')
                ->countAllResults();

            // Pending payments
            $pendingPayments = $this->paymentModel
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->countAllResults();

            // Total amount (verified only)
            $totalAmountResult = $this->paymentModel
                ->select('SUM(amount) as total')
                ->where('user_id', $userId)
                ->where('status', 'verified')
                ->get()
                ->getRow();

            $totalAmount = $totalAmountResult ? (float) $totalAmountResult->total : 0;

            // Last payment
            $lastPayment = $this->paymentModel
                ->where('user_id', $userId)
                ->where('status', 'verified')
                ->orderBy('payment_date', 'DESC')
                ->first();

            return [
                'total_payments' => $totalPayments,
                'verified_payments' => $verifiedPayments,
                'pending_payments' => $pendingPayments,
                'total_amount' => $totalAmount,
                'last_payment' => $lastPayment
            ];
        } catch (\Exception $e) {
            log_message('error', 'Member\PaymentController::getPaymentSummary - Error: ' . $e->getMessage());

            return [
                'total_payments' => 0,
                'verified_payments' => 0,
                'pending_payments' => 0,
                'total_amount' => 0,
                'last_payment' => null
            ];
        }
    }
}
