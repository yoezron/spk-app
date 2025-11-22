<?php

/**
 * View: Admin Payment Detail
 * Controller: App\Controllers\Admin\PaymentController::detail()
 * Description: Detailed payment information dengan verification actions
 *
 * Features:
 * - Complete payment information display
 * - Member profile summary
 * - Payment proof image preview
 * - Verification status & history
 * - Action buttons (Verify, Reject)
 * - Verifier information (if verified/rejected)
 * - Regional scope support
 * - Responsive design
 * - Permission-based action visibility
 *
 * @package App\Views\Admin\Payment
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">
<!-- Lightbox CSS -->
<link href="<?= base_url('assets/plugins/lightbox/lightbox.min.css') ?>" rel="stylesheet">

<style>
    /* Payment Detail Wrapper */
    .payment-detail-wrapper {
        padding: 24px;
        background: #f8f9fa;
        min-height: calc(100vh - 80px);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        padding: 24px 32px;
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

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .back-button:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
    }

    .page-title-section h1 {
        font-size: 24px;
        font-weight: 700;
        margin: 8px 0 4px 0;
        color: white;
    }

    .page-title-section p {
        font-size: 14px;
        opacity: 0.95;
        margin: 0;
    }

    /* Main Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 24px;
        margin-bottom: 32px;
    }

    /* Main Content */
    .main-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Info Sections */
    .info-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .info-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .info-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-section-title i {
        color: #48bb78;
        font-size: 24px;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-label {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        color: #2d3748;
        font-weight: 500;
    }

    .info-value.empty {
        color: #a0aec0;
        font-style: italic;
    }

    .info-value a {
        color: #48bb78;
        text-decoration: none;
        font-weight: 600;
    }

    .info-value a:hover {
        text-decoration: underline;
    }

    .info-value.amount {
        font-size: 28px;
        font-weight: 700;
        color: #48bb78;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
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
        font-size: 18px;
    }

    /* Type Badge */
    .type-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
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

    /* Proof Preview Card (Sidebar) */
    .proof-preview-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 24px;
        height: fit-content;
    }

    .proof-preview-title {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .proof-preview-title i {
        color: #48bb78;
    }

    .proof-image-wrapper {
        background: #f7fafc;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        border: 2px dashed #cbd5e0;
        margin-bottom: 20px;
    }

    .proof-image {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .proof-image:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .no-proof {
        padding: 40px 20px;
        text-align: center;
        color: #a0aec0;
    }

    .no-proof i {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .no-proof p {
        font-size: 14px;
        margin: 0;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    .action-buttons .btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
    }

    /* Alert Box */
    .alert-box {
        background: white;
        border-left: 4px solid;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .alert-box.warning {
        border-left-color: #f6ad55;
        background: #fffaf0;
    }

    .alert-box.info {
        border-left-color: #4299e1;
        background: #ebf8ff;
    }

    .alert-box.success {
        border-left-color: #48bb78;
        background: #f0fff4;
    }

    .alert-box.danger {
        border-left-color: #f56565;
        background: #fff5f5;
    }

    .alert-box i {
        font-size: 24px;
        flex-shrink: 0;
    }

    .alert-box.warning i {
        color: #dd6b20;
    }

    .alert-box.info i {
        color: #2b6cb0;
    }

    .alert-box.success i {
        color: #2f855a;
    }

    .alert-box.danger i {
        color: #c53030;
    }

    .alert-box-content {
        flex: 1;
    }

    .alert-box-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #2d3748;
    }

    .alert-box-text {
        font-size: 13px;
        color: #4a5568;
        margin: 0;
    }

    /* Member Info Card */
    .member-info-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: #f7fafc;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .member-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
    }

    .member-details {
        flex: 1;
    }

    .member-name {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .member-email {
        font-size: 14px;
        color: #718096;
        margin-bottom: 2px;
    }

    .member-university {
        font-size: 13px;
        color: #a0aec0;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .proof-preview-card {
            position: relative;
            top: 0;
        }
    }

    @media (max-width: 768px) {
        .payment-detail-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 20px;
        }

        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="payment-detail-wrapper">

    <!-- Page Header -->
    <div class="page-header">
        <a href="<?= base_url('admin/payment') ?>" class="back-button">
            <i class="material-icons-outlined">arrow_back</i>
            Kembali ke Daftar
        </a>

        <div class="page-header-content">
            <div class="page-title-section">
                <h1>Detail Pembayaran</h1>
                <p>Informasi lengkap pembayaran dan verifikasi</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <?php if (isset($payment)): ?>

        <!-- Pending Payment Alert -->
        <?php if ($payment->status === 'pending'): ?>
            <div class="alert-box warning">
                <i class="material-icons-outlined">pending</i>
                <div class="alert-box-content">
                    <div class="alert-box-title">Menunggu Verifikasi</div>
                    <p class="alert-box-text">
                        Pembayaran ini menunggu verifikasi dari pengurus.
                        Silakan review bukti pembayaran dan informasi lainnya sebelum memverifikasi.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Rejected Payment Alert -->
        <?php if ($payment->status === 'rejected'): ?>
            <div class="alert-box danger">
                <i class="material-icons-outlined">cancel</i>
                <div class="alert-box-content">
                    <div class="alert-box-title">Pembayaran Ditolak</div>
                    <p class="alert-box-text">
                        <?php if (!empty($payment->rejection_reason)): ?>
                            Alasan: <?= esc($payment->rejection_reason) ?>
                        <?php else: ?>
                            Pembayaran ini telah ditolak.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Verified Payment Alert -->
        <?php if ($payment->status === 'verified'): ?>
            <div class="alert-box success">
                <i class="material-icons-outlined">check_circle</i>
                <div class="alert-box-content">
                    <div class="alert-box-title">Pembayaran Terverifikasi</div>
                    <p class="alert-box-text">
                        Pembayaran ini telah diverifikasi pada <?= date('d M Y H:i', strtotime($payment->verified_at)) ?>
                        <?php if ($verifier): ?>
                            oleh <?= esc($verifier->full_name ?? $verifier->username) ?>.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Content Grid -->
        <div class="content-grid">

            <!-- Main Content -->
            <div class="main-content">

                <!-- Member Information -->
                <div class="info-section">
                    <div class="info-section-header">
                        <h3 class="info-section-title">
                            <i class="material-icons-outlined">person</i>
                            Informasi Anggota
                        </h3>
                    </div>

                    <div class="member-info-card">
                        <img
                            src="<?= !empty($payment->photo) ? base_url('uploads/photos/' . $payment->photo) : base_url('assets/images/avatars/avatar.png') ?>"
                            alt="Avatar"
                            class="member-avatar">
                        <div class="member-details">
                            <div class="member-name"><?= esc($payment->full_name) ?></div>
                            <div class="member-email"><?= esc($payment->email) ?></div>
                            <div class="member-university"><?= esc($payment->university_name ?? '-') ?></div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Username</div>
                            <div class="info-value"><?= esc($payment->username) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">No. Telepon</div>
                            <div class="info-value <?= empty($payment->phone) ? 'empty' : '' ?>">
                                <?= !empty($payment->phone) ? esc($payment->phone) : 'Tidak tersedia' ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Provinsi</div>
                            <div class="info-value"><?= esc($payment->province_name ?? '-') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="info-section">
                    <div class="info-section-header">
                        <h3 class="info-section-title">
                            <i class="material-icons-outlined">payments</i>
                            Informasi Pembayaran
                        </h3>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Tipe Pembayaran</div>
                            <div class="info-value">
                                <?php
                                $typeClass = 'monthly';
                                $typeText = 'Iuran Bulanan';
                                if ($payment->payment_type === 'annual') {
                                    $typeClass = 'annual';
                                    $typeText = 'Iuran Tahunan';
                                } elseif ($payment->payment_type === 'donation') {
                                    $typeClass = 'donation';
                                    $typeText = 'Donasi';
                                }
                                ?>
                                <span class="type-badge <?= $typeClass ?>"><?= $typeText ?></span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Jumlah Pembayaran</div>
                            <div class="info-value amount">Rp <?= number_format($payment->amount, 0, ',', '.') ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
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
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Tanggal Pembayaran</div>
                            <div class="info-value"><?= date('d F Y', strtotime($payment->payment_date)) ?></div>
                        </div>

                        <?php if ($payment->period_month && $payment->period_year): ?>
                            <div class="info-item">
                                <div class="info-label">Periode</div>
                                <div class="info-value">
                                    <?php
                                    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    echo $months[$payment->period_month] . ' ' . $payment->period_year;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="info-item">
                            <div class="info-label">Metode Pembayaran</div>
                            <div class="info-value <?= empty($payment->payment_method) ? 'empty' : '' ?>">
                                <?php
                                if (!empty($payment->payment_method)) {
                                    echo ucfirst(str_replace('_', ' ', $payment->payment_method));
                                } else {
                                    echo 'Tidak disebutkan';
                                }
                                ?>
                            </div>
                        </div>

                        <?php if (!empty($payment->bank_name)): ?>
                            <div class="info-item">
                                <div class="info-label">Bank</div>
                                <div class="info-value"><?= esc($payment->bank_name) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($payment->account_name)): ?>
                            <div class="info-item">
                                <div class="info-label">Nama Rekening</div>
                                <div class="info-value"><?= esc($payment->account_name) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($payment->notes)): ?>
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
                            <div class="info-label" style="margin-bottom: 8px;">Catatan</div>
                            <div class="info-value" style="white-space: pre-wrap;"><?= esc($payment->notes) ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Verification Information -->
                <?php if ($payment->status !== 'pending'): ?>
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">verified</i>
                                Informasi Verifikasi
                            </h3>
                        </div>

                        <div class="info-grid">
                            <?php if ($verifier): ?>
                                <div class="info-item">
                                    <div class="info-label">Diverifikasi Oleh</div>
                                    <div class="info-value"><?= esc($verifier->full_name ?? $verifier->username) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($payment->verified_at): ?>
                                <div class="info-item">
                                    <div class="info-label">Tanggal Verifikasi</div>
                                    <div class="info-value"><?= date('d F Y H:i', strtotime($payment->verified_at)) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($payment->verification_notes)): ?>
                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <div class="info-label">Catatan Verifikasi</div>
                                    <div class="info-value"><?= esc($payment->verification_notes) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($payment->status === 'rejected' && !empty($payment->rejection_reason)): ?>
                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <div class="info-label">Alasan Penolakan</div>
                                    <div class="info-value" style="color: #dc2626;"><?= esc($payment->rejection_reason) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Proof Preview Sidebar -->
            <div class="proof-preview-card">
                <div class="proof-preview-title">
                    <i class="material-icons-outlined">image</i>
                    Bukti Pembayaran
                </div>

                <?php if (!empty($payment->proof_file)): ?>
                    <div class="proof-image-wrapper">
                        <img
                            src="<?= base_url('uploads/payment_proofs/' . $payment->proof_file) ?>"
                            alt="Bukti Pembayaran"
                            class="proof-image"
                            data-lightbox="payment-proof"
                            data-title="Bukti Pembayaran - <?= esc($payment->full_name) ?>">
                    </div>
                <?php else: ?>
                    <div class="no-proof">
                        <i class="material-icons-outlined">image_not_supported</i>
                        <p>Tidak ada bukti pembayaran</p>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <?php if ($payment->status === 'pending' && auth()->user()->can('payment.verify')): ?>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-success" onclick="verifyPayment(<?= $payment->id ?>)">
                            <i class="material-icons-outlined">check_circle</i>
                            Verifikasi Pembayaran
                        </button>

                        <button type="button" class="btn btn-danger" onclick="rejectPayment(<?= $payment->id ?>)">
                            <i class="material-icons-outlined">cancel</i>
                            Tolak Pembayaran
                        </button>
                    </div>
                <?php endif; ?>

                <div class="info-item" style="margin-top: 20px;">
                    <div class="info-label">Diupload pada</div>
                    <div class="info-value" style="font-size: 13px;"><?= date('d M Y H:i', strtotime($payment->created_at)) ?></div>
                </div>
            </div>

        </div>

    <?php else: ?>

        <!-- Payment Not Found -->
        <div class="info-section">
            <div style="text-align: center; padding: 60px 20px; color: #a0aec0;">
                <i class="material-icons-outlined" style="font-size: 64px; margin-bottom: 20px; opacity: 0.5;">receipt_long</i>
                <h3 style="font-size: 20px; font-weight: 700; color: #718096; margin-bottom: 8px;">Data Pembayaran Tidak Ditemukan</h3>
                <p style="font-size: 14px; margin: 0;">Pembayaran yang Anda cari tidak ditemukan atau telah dihapus</p>
                <a href="<?= base_url('admin/payment') ?>" class="btn btn-success mt-3">
                    Kembali ke Daftar Pembayaran
                </a>
            </div>
        </div>

    <?php endif; ?>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
<!-- Lightbox JS -->
<script src="<?= base_url('assets/plugins/lightbox/lightbox.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        // Lightbox Configuration
        lightbox.option({
            'resizeDuration': 300,
            'wrapAround': true,
            'albumLabel': 'Gambar %1 dari %2'
        });
    });

    // Verify Payment
    function verifyPayment(id) {
        Swal.fire({
            title: 'Verifikasi Pembayaran?',
            text: 'Pembayaran akan diverifikasi sebagai sah dan valid',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Verifikasi!',
            cancelButtonText: 'Batal',
            input: 'textarea',
            inputPlaceholder: 'Catatan verifikasi (opsional)',
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
            html: 'Pembayaran akan ditolak dan anggota akan dinotifikasi.<br><br><strong>Alasan penolakan wajib diisi.</strong>',
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
