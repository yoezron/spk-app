<?php

/**
 * View: Admin Payment List
 * Controller: App\Controllers\Admin\PaymentController::index()
 * Description: Comprehensive payment management dengan filtering dan statistics
 *
 * Features:
 * - Payment list dengan DataTables
 * - Advanced filters (Status, Type, Month, Year, Search)
 * - Payment statistics summary cards
 * - Status badges dengan color coding
 * - Action buttons per row (View Detail, Verify, Reject)
 * - Export functionality
 * - Regional scope support untuk Koordinator
 * - Responsive design
 * - Permission-based access control
 *
 * @package App\Views\Admin\Payment
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>" rel="stylesheet">
<!-- Select2 CSS -->
<link href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/select2/css/select2-bootstrap4.min.css') ?>" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">

<style>
    /* Payment Page Wrapper */
    .payment-wrapper {
        padding: 24px;
        background: #f8f9fa;
        min-height: calc(100vh - 80px);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        padding: 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        color: white;
        box-shadow: 0 8px 24px rgba(72, 187, 120, 0.25);
    }

    .page-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title-section h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title-section p {
        font-size: 15px;
        opacity: 0.95;
        margin: 0;
    }

    .page-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .page-actions .btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Stats Summary Cards */
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-summary-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .stat-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-summary-card.verified {
        border-left-color: #48bb78;
    }

    .stat-summary-card.pending {
        border-left-color: #f6ad55;
    }

    .stat-summary-card.rejected {
        border-left-color: #f56565;
    }

    .stat-summary-card.total {
        border-left-color: #667eea;
    }

    .stat-summary-label {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .stat-summary-value {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
    }

    .stat-summary-subtitle {
        font-size: 14px;
        color: #48bb78;
        font-weight: 600;
        margin-top: 4px;
    }

    /* Filters Section */
    .filters-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .filters-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .filters-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filters-header h3 i {
        color: #48bb78;
    }

    .filters-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: #4a5568;
        margin: 0;
    }

    .filter-group .form-control,
    .filter-group .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .filter-group .form-control:focus,
    .filter-group .form-select:focus {
        border-color: #48bb78;
        box-shadow: 0 0 0 3px rgba(72, 187, 120, 0.1);
    }

    .filters-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    /* Table Section */
    .table-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    /* DataTable Custom Styles */
    #paymentsTable thead th {
        background: #f7fafc;
        color: #4a5568;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 12px;
        border-bottom: 2px solid #e2e8f0;
    }

    #paymentsTable tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        font-size: 14px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
    }

    #paymentsTable tbody tr:hover {
        background: #f7fafc;
    }

    /* Payment Info Cell */
    .payment-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .payment-member {
        font-weight: 600;
        color: #2d3748;
        font-size: 14px;
    }

    .payment-university {
        font-size: 12px;
        color: #718096;
    }

    /* Amount Display */
    .payment-amount {
        font-weight: 700;
        color: #48bb78;
        font-size: 16px;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-badge.verified {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.pending {
        background: #feebc8;
        color: #7c2d12;
    }

    .status-badge.rejected {
        background: #fed7d7;
        color: #742a2a;
    }

    .status-badge i {
        font-size: 14px;
    }

    /* Type Badges */
    .type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-badge.monthly {
        background: #e6f2ff;
        color: #1976d2;
    }

    .type-badge.annual {
        background: #fff3e0;
        color: #f57c00;
    }

    .type-badge.donation {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        font-size: 16px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-action.view {
        background: #e6f2ff;
        color: #4299e1;
    }

    .btn-action.view:hover {
        background: #4299e1;
        color: white;
    }

    .btn-action.verify {
        background: #d1fae5;
        color: #059669;
    }

    .btn-action.verify:hover {
        background: #059669;
        color: white;
    }

    .btn-action.reject {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-action.reject:hover {
        background: #dc2626;
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 700;
        color: #718096;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .payment-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
        }

        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-title-section h1 {
            font-size: 22px;
        }

        .page-actions {
            width: 100%;
        }

        .page-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .stats-summary {
            grid-template-columns: 1fr;
        }

        .filters-body {
            grid-template-columns: 1fr;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="payment-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <div class="page-header-content">
            <div class="page-title-section">
                <h1>
                    <i class="material-icons-outlined">payments</i>
                    Manajemen Pembayaran Iuran
                </h1>
                <p>Verifikasi pembayaran, laporan keuangan, dan tracking iuran anggota</p>
            </div>

            <div class="page-actions">
                <?php if (auth()->user()->can('payment.verify')): ?>
                    <a href="<?= base_url('admin/payment/pending') ?>" class="btn btn-light">
                        <i class="material-icons-outlined">pending_actions</i>
                        Verifikasi Pembayaran
                        <?php if (isset($stats['pending_count']) && $stats['pending_count'] > 0): ?>
                            <span class="badge bg-danger"><?= $stats['pending_count'] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <?php if (auth()->user()->can('payment.export')): ?>
                    <button type="button" class="btn btn-light" id="exportBtn">
                        <i class="material-icons-outlined">download</i>
                        Export Data
                    </button>
                <?php endif; ?>

                <?php if (auth()->user()->can('payment.report')): ?>
                    <a href="<?= base_url('admin/payment/report') ?>" class="btn btn-light">
                        <i class="material-icons-outlined">assessment</i>
                        Laporan Keuangan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Statistics Summary -->
    <div class="stats-summary animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="stat-summary-card total">
            <div class="stat-summary-label">Total Pembayaran</div>
            <div class="stat-summary-value"><?= number_format($pager->getTotal()) ?></div>
        </div>

        <div class="stat-summary-card verified">
            <div class="stat-summary-label">Terverifikasi</div>
            <div class="stat-summary-value"><?= number_format($stats['verified_count'] ?? 0) ?></div>
            <div class="stat-summary-subtitle">Rp <?= number_format($stats['verified_amount'] ?? 0, 0, ',', '.') ?></div>
        </div>

        <div class="stat-summary-card pending">
            <div class="stat-summary-label">Menunggu Verifikasi</div>
            <div class="stat-summary-value"><?= number_format($stats['pending_count'] ?? 0) ?></div>
            <div class="stat-summary-subtitle">Rp <?= number_format($stats['pending_amount'] ?? 0, 0, ',', '.') ?></div>
        </div>

        <div class="stat-summary-card rejected">
            <div class="stat-summary-label">Ditolak</div>
            <div class="stat-summary-value"><?= number_format($stats['rejected_count'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="filters-header">
            <h3>
                <i class="material-icons-outlined">filter_list</i>
                Filter & Pencarian
            </h3>
        </div>

        <form method="GET" action="<?= current_url() ?>" id="filtersForm">
            <div class="filters-body">

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="filterStatus">Status Pembayaran</label>
                    <select name="status" id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= ($filterStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="verified" <?= ($filterStatus ?? '') === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                        <option value="rejected" <?= ($filterStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="filter-group">
                    <label for="filterType">Tipe Pembayaran</label>
                    <select name="type" id="filterType" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="monthly" <?= ($filterType ?? '') === 'monthly' ? 'selected' : '' ?>>Iuran Bulanan</option>
                        <option value="annual" <?= ($filterType ?? '') === 'annual' ? 'selected' : '' ?>>Iuran Tahunan</option>
                        <option value="donation" <?= ($filterType ?? '') === 'donation' ? 'selected' : '' ?>>Donasi</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div class="filter-group">
                    <label for="filterYear">Tahun</label>
                    <select name="year" id="filterYear" class="form-select">
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= ($currentYear ?? date('Y')) == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Month Filter -->
                <div class="filter-group">
                    <label for="filterMonth">Bulan</label>
                    <select name="month" id="filterMonth" class="form-select">
                        <option value="">Semua Bulan</option>
                        <?php
                        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        for ($m = 1; $m <= 12; $m++):
                        ?>
                            <option value="<?= $m ?>" <?= ($currentMonth ?? '') == $m ? 'selected' : '' ?>>
                                <?= $months[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="filter-group">
                    <label for="filterSearch">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        id="filterSearch"
                        class="form-control"
                        placeholder="Nama anggota, catatan..."
                        value="<?= esc($search ?? '') ?>">
                </div>

            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-success">
                    <i class="material-icons-outlined" style="font-size: 18px;">search</i>
                    Terapkan Filter
                </button>
                <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined" style="font-size: 18px;">refresh</i>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-section animate-fade-in-up" style="animation-delay: 0.3s;">

        <div class="table-header">
            <h3 class="table-title">
                Daftar Pembayaran
                <span style="color: #718096; font-weight: 400; font-size: 14px;">
                    (<?= number_format($pager->getTotal()) ?> total)
                </span>
            </h3>
        </div>

        <!-- DataTable -->
        <?php if (!empty($payments)): ?>
            <div class="table-responsive">
                <table id="paymentsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Periode</th>
                            <th>Tanggal Bayar</th>
                            <th>Status</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <div class="payment-info">
                                        <div class="payment-member"><?= esc($payment->full_name) ?></div>
                                        <div class="payment-university"><?= esc($payment->university_name ?? '-') ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $typeClass = 'monthly';
                                    $typeText = 'Bulanan';
                                    if ($payment->payment_type === 'annual') {
                                        $typeClass = 'annual';
                                        $typeText = 'Tahunan';
                                    } elseif ($payment->payment_type === 'donation') {
                                        $typeClass = 'donation';
                                        $typeText = 'Donasi';
                                    }
                                    ?>
                                    <span class="type-badge <?= $typeClass ?>"><?= $typeText ?></span>
                                </td>
                                <td>
                                    <span class="payment-amount">Rp <?= number_format($payment->amount, 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <?php
                                    if ($payment->period_month && $payment->period_year) {
                                        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                        echo $months[$payment->period_month] . ' ' . $payment->period_year;
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?= date('d M Y', strtotime($payment->payment_date)) ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'pending';
                                    $statusIcon = 'pending';
                                    $statusText = 'Pending';

                                    if ($payment->status === 'verified') {
                                        $statusClass = 'verified';
                                        $statusIcon = 'check_circle';
                                        $statusText = 'Terverifikasi';
                                    } elseif ($payment->status === 'rejected') {
                                        $statusClass = 'rejected';
                                        $statusIcon = 'cancel';
                                        $statusText = 'Ditolak';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="material-icons-outlined"><?= $statusIcon ?></i>
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- View Button -->
                                        <?php if (auth()->user()->can('payment.view')): ?>
                                            <a
                                                href="<?= base_url('admin/payment/detail/' . $payment->id) ?>"
                                                class="btn-action view"
                                                data-bs-toggle="tooltip"
                                                title="Lihat Detail">
                                                <i class="material-icons-outlined">visibility</i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Verify Button (only for pending) -->
                                        <?php if ($payment->status === 'pending' && auth()->user()->can('payment.verify')): ?>
                                            <button
                                                type="button"
                                                class="btn-action verify"
                                                onclick="verifyPayment(<?= $payment->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Verifikasi">
                                                <i class="material-icons-outlined">check</i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action reject"
                                                onclick="rejectPayment(<?= $payment->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Tolak">
                                                <i class="material-icons-outlined">close</i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan <?= ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 ?>
                    sampai <?= min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()) ?>
                    dari <?= number_format($pager->getTotal()) ?> data
                </div>
                <?= $pager->links('default', 'default_full') ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="material-icons-outlined">payments</i>
                <h3>Tidak Ada Data Pembayaran</h3>
                <p>Belum ada pembayaran yang terdaftar atau sesuai dengan filter yang diterapkan</p>
            </div>
        <?php endif; ?>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables JS -->
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/js/select2.min.js') ?>"></script>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Initialize DataTables
        const table = $('#paymentsTable').DataTable({
            responsive: true,
            pageLength: 20,
            ordering: true,
            searching: true,
            dom: 'rtip',
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>',
                emptyTable: 'Tidak ada data yang tersedia',
                zeroRecords: 'Tidak ditemukan data yang sesuai'
            },
            columnDefs: [{
                orderable: false,
                targets: [6]
            }]
        });

        // Initialize Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Export Button
        $('#exportBtn').on('click', function() {
            const currentUrl = new URL(window.location.href);
            window.location.href = '<?= base_url('admin/payment/export') ?>?' + currentUrl.searchParams.toString();
        });

    });

    // Verify Payment
    function verifyPayment(id) {
        Swal.fire({
            title: 'Verifikasi Pembayaran?',
            text: 'Pembayaran akan diverifikasi sebagai sah',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Verifikasi!',
            cancelButtonText: 'Batal',
            input: 'textarea',
            inputPlaceholder: 'Catatan (opsional)',
            inputAttributes: {
                'aria-label': 'Catatan verifikasi'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form to verify
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('admin/payment/verify/') ?>' + id;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                if (result.value) {
                    const notesInput = document.createElement('input');
                    notesInput.type = 'hidden';
                    notesInput.name = 'notes';
                    notesInput.value = result.value;
                    form.appendChild(notesInput);
                }

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Reject Payment
    function rejectPayment(id) {
        Swal.fire({
            title: 'Tolak Pembayaran?',
            text: 'Pembayaran akan ditolak',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal',
            input: 'textarea',
            inputPlaceholder: 'Alasan penolakan (wajib)',
            inputAttributes: {
                'aria-label': 'Alasan penolakan'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan penolakan harus diisi!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form to reject
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('admin/payment/reject/') ?>' + id;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = result.value;
                form.appendChild(reasonInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>
