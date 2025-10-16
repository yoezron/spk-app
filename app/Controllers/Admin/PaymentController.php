<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\MemberProfileModel;
use App\Models\UserModel;
use App\Services\FinanceService;
use App\Services\RegionScopeService;
use App\Services\Communication\NotificationService;
use CodeIgniter\HTTP\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * PaymentController
 * 
 * Mengelola pembayaran iuran anggota SPK
 * Verifikasi bukti pembayaran, laporan keuangan, dan tracking iuran
 * Support regional scope untuk Koordinator Wilayah
 * 
 * @package App\Controllers\Admin
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
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var FinanceService
     */
    protected $financeService;

    /**
     * @var RegionScopeService
     */
    protected $regionScope;

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
        $this->userModel = new UserModel();
        $this->financeService = new FinanceService();
        $this->regionScope = new RegionScopeService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Display list of all payments with filters
     * Support regional scope for Koordinator Wilayah
     * 
     * @return string
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('payment.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Get filter parameters
        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year') ?? date('Y');
        $search = $this->request->getGet('search');

        // Build query with regional scope
        $builder = $this->paymentModel->builder();

        // Apply regional scope for Koordinator Wilayah
        $user = auth()->user();
        if ($user->inGroup('koordinator')) {
            $builder = $this->regionScope->applyScopeToPayments($builder, auth()->id());
        }

        // Apply filters
        if ($status) {
            $builder->where('payments.status', $status);
        }

        if ($type) {
            $builder->where('payments.payment_type', $type);
        }

        if ($month) {
            $builder->where('payments.period_month', $month);
        }

        if ($year) {
            $builder->where('payments.period_year', $year);
        }

        if ($search) {
            $builder->groupStart()
                ->like('users.username', $search)
                ->orLike('member_profiles.full_name', $search)
                ->orLike('payments.notes', $search)
                ->groupEnd();
        }

        // Join with users and member profiles
        $builder->select('payments.*, users.username, member_profiles.full_name, member_profiles.university_name, member_profiles.province_name')
            ->join('users', 'users.id = payments.user_id')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
            ->orderBy('payments.created_at', 'DESC');

        $payments = $builder->paginate(20);
        $pager = $this->paymentModel->pager;

        // Get statistics
        $stats = $this->financeService->getPaymentStatistics($year, $month, auth()->id());

        $data = [
            'title' => 'Manajemen Pembayaran Iuran',
            'payments' => $payments,
            'pager' => $pager,
            'stats' => $stats,
            'currentYear' => $year,
            'currentMonth' => $month,
            'filterStatus' => $status,
            'filterType' => $type,
            'search' => $search,
            'years' => range(date('Y'), date('Y') - 5) // Last 5 years
        ];

        return view('admin/payment/index', $data);
    }

    /**
     * Display pending payments for verification
     * 
     * @return string
     */
    public function pending()
    {
        // Check permission
        if (!auth()->user()->can('payment.verify')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Build query with regional scope
        $builder = $this->paymentModel->builder();

        // Apply regional scope for Koordinator Wilayah
        $user = auth()->user();
        if ($user->inGroup('koordinator')) {
            $builder = $this->regionScope->applyScopeToPayments($builder, auth()->id());
        }

        // Get only pending payments
        $builder->where('payments.status', 'pending')
            ->select('payments.*, users.username, member_profiles.full_name, member_profiles.university_name, member_profiles.phone')
            ->join('users', 'users.id = payments.user_id')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
            ->orderBy('payments.created_at', 'ASC');

        $pendingPayments = $builder->paginate(20);
        $pager = $this->paymentModel->pager;

        $data = [
            'title' => 'Verifikasi Pembayaran',
            'payments' => $pendingPayments,
            'pager' => $pager,
            'totalPending' => $this->paymentModel->where('status', 'pending')->countAllResults()
        ];

        return view('admin/payment/verify', $data);
    }

    /**
     * Show payment detail
     * 
     * @param int $id Payment ID
     * @return string|RedirectResponse
     */
    public function detail($id)
    {
        // Check permission
        if (!auth()->user()->can('payment.view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $payment = $this->paymentModel->select('payments.*, users.username, users.email, member_profiles.*')
            ->join('users', 'users.id = payments.user_id')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
            ->find($id);

        if (!$payment) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        // Check regional scope
        $user = auth()->user();
        if ($user->inGroup('koordinator')) {
            $hasAccess = $this->regionScope->canAccessPayment(auth()->id(), $id);
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pembayaran ini.');
            }
        }

        // Get verifier info if verified
        $verifier = null;
        if ($payment->verified_by) {
            $verifier = $this->userModel->select('users.username, member_profiles.full_name')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->find($payment->verified_by);
        }

        $data = [
            'title' => 'Detail Pembayaran',
            'payment' => $payment,
            'verifier' => $verifier
        ];

        return view('admin/payment/detail', $data);
    }

    /**
     * Verify payment
     * 
     * @param int $id Payment ID
     * @return RedirectResponse
     */
    public function verify($id)
    {
        // Check permission
        if (!auth()->user()->can('payment.verify')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan verifikasi.');
        }

        // Check regional scope
        $user = auth()->user();
        if ($user->inGroup('koordinator')) {
            $hasAccess = $this->regionScope->canAccessPayment(auth()->id(), $id);
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pembayaran ini.');
            }
        }

        $notes = $this->request->getPost('notes');

        $result = $this->financeService->verifyPayment($id, auth()->id(), $notes);

        if ($result['success']) {
            // Send notification to member
            $payment = $this->paymentModel->find($id);
            if ($payment) {
                $this->notificationService->sendPaymentVerified($payment->user_id, $payment);
            }

            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Reject payment
     * 
     * @param int $id Payment ID
     * @return RedirectResponse
     */
    public function reject($id)
    {
        // Check permission
        if (!auth()->user()->can('payment.verify')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan penolakan.');
        }

        // Check regional scope
        $user = auth()->user();
        if ($user->inGroup('koordinator')) {
            $hasAccess = $this->regionScope->canAccessPayment(auth()->id(), $id);
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pembayaran ini.');
            }
        }

        $reason = $this->request->getPost('reason');

        if (empty($reason)) {
            return redirect()->back()->with('error', 'Alasan penolakan harus diisi.');
        }

        $result = $this->financeService->rejectPayment($id, auth()->id(), $reason);

        if ($result['success']) {
            // Send notification to member
            $payment = $this->paymentModel->find($id);
            if ($payment) {
                $this->notificationService->sendPaymentRejected($payment->user_id, $payment, $reason);
            }

            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Display financial report
     * 
     * @return string
     */
    public function report()
    {
        // Check permission
        if (!auth()->user()->can('payment.report')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengakses laporan keuangan.');
        }

        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month');
        $groupBy = $this->request->getGet('group_by') ?? 'month'; // month, province, type

        // Get report data based on grouping
        $reportData = $this->financeService->generateReport($year, $month, $groupBy, auth()->id());

        // Get summary statistics
        $summary = $this->financeService->getSummaryStatistics($year, $month, auth()->id());

        $data = [
            'title' => 'Laporan Keuangan',
            'reportData' => $reportData,
            'summary' => $summary,
            'currentYear' => $year,
            'currentMonth' => $month,
            'groupBy' => $groupBy,
            'years' => range(date('Y'), date('Y') - 5)
        ];

        return view('admin/payment/report', $data);
    }

    /**
     * Export payments to Excel
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function export()
    {
        // Check permission
        if (!auth()->user()->can('payment.export')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk export data.');
        }

        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year') ?? date('Y');

        // Build query with filters
        $builder = $this->paymentModel->builder();

        // Apply regional scope for Koordinator Wilayah
        $user = auth()->user();
        if ($user->inGroup('koordinator')) {
            $builder = $this->regionScope->applyScopeToPayments($builder, auth()->id());
        }

        // Apply filters
        if ($status) {
            $builder->where('payments.status', $status);
        }
        if ($type) {
            $builder->where('payments.payment_type', $type);
        }
        if ($month) {
            $builder->where('payments.period_month', $month);
        }
        if ($year) {
            $builder->where('payments.period_year', $year);
        }

        // Get payments
        $payments = $builder->select('payments.*, users.username, member_profiles.full_name, member_profiles.university_name, member_profiles.province_name, member_profiles.phone')
            ->join('users', 'users.id = payments.user_id')
            ->join('member_profiles', 'member_profiles.user_id = payments.user_id', 'left')
            ->orderBy('payments.created_at', 'DESC')
            ->get()
            ->getResult();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Tanggal');
        $sheet->setCellValue('C1', 'Nama Anggota');
        $sheet->setCellValue('D1', 'Username');
        $sheet->setCellValue('E1', 'Kampus');
        $sheet->setCellValue('F1', 'Provinsi');
        $sheet->setCellValue('G1', 'Tipe Pembayaran');
        $sheet->setCellValue('H1', 'Jumlah');
        $sheet->setCellValue('I1', 'Periode');
        $sheet->setCellValue('J1', 'Status');
        $sheet->setCellValue('K1', 'Metode');
        $sheet->setCellValue('L1', 'Catatan');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        // Fill data
        $row = 2;
        foreach ($payments as $index => $payment) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($payment->payment_date)));
            $sheet->setCellValue('C' . $row, $payment->full_name);
            $sheet->setCellValue('D' . $row, $payment->username);
            $sheet->setCellValue('E' . $row, $payment->university_name);
            $sheet->setCellValue('F' . $row, $payment->province_name);
            $sheet->setCellValue('G' . $row, ucfirst($payment->payment_type));
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($payment->amount, 0, ',', '.'));

            $period = '';
            if ($payment->period_month && $payment->period_year) {
                $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                $period = $months[$payment->period_month] . ' ' . $payment->period_year;
            }
            $sheet->setCellValue('I' . $row, $period);

            $sheet->setCellValue('J' . $row, ucfirst($payment->status));
            $sheet->setCellValue('K' . $row, $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : '-');
            $sheet->setCellValue('L' . $row, $payment->notes ?? '-');

            $row++;
        }

        // Auto size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate filename
        $filename = 'Laporan_Pembayaran_' . $year;
        if ($month) {
            $filename .= '_' . str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        $filename .= '_' . date('YmdHis') . '.xlsx';

        // Output file
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Delete payment (soft delete)
     * 
     * @param int $id Payment ID
     * @return RedirectResponse
     */
    public function delete($id)
    {
        // Check permission - only superadmin can delete
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus data pembayaran.');
        }

        $payment = $this->paymentModel->find($id);

        if (!$payment) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        if ($this->paymentModel->delete($id)) {
            return redirect()->back()->with('success', 'Pembayaran berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus pembayaran.');
        }
    }
}
