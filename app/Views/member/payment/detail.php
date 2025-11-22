<?php

/**
 * View: Member Payment Detail
 * Controller: Member\PaymentController::detail()
 * Description: Detail pembayaran untuk portal anggota
 *
 * Features:
 * - Payment header dengan status badge
 * - Payment information display
 * - Proof image preview
 * - Verification status & info
 * - Download proof button
 * - Cancel button (for pending payments)
 * - Responsive design
 * - Member-friendly interface
 *
 * @package App\Views\Member\Payment
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<!-- Lightbox CSS -->
<link href="<?= base_url('assets/plugins/lightbox/lightbox.min.css') ?>" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">

<style>
    /* Payment Header Card */
    .payment-header-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .payment-header-top {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        padding: 25px 30px;
        color: white;
    }

    .payment-id {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .payment-title {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 15px;
    }

    .payment-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        opacity: 0.9;
        font-size: 14px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .payment-info-bar {
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        border-bottom: 2px solid #f1f3f5;
    }

    .payment-badges {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .payment-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Status Badge */
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge.verified {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.rejected {
        background: #f8d7da;
        color: #721c24;
    }

    /* Type Badge */
    .type-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .type-badge.monthly {
        background: #cce5ff;
        color: #004085;
    }

    .type-badge.annual {
        background: #fff3e0;
        color: #f57c00;
    }

    .type-badge.donation {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    /* Info Card */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .info-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .info-card-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }

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
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 500;
    }

    .info-value.amount {
        font-size: 32px;
        font-weight: 700;
        color: #48bb78;
    }

    .info-value.empty {
        color: #adb5bd;
        font-style: italic;
    }

    /* Proof Preview */
    .proof-preview-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .proof-preview-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .proof-preview-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .proof-preview-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }

    .proof-image-wrapper {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 2px dashed #dee2e6;
    }

    .proof-image {
        max-width: 100%;
        max-height: 500px;
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
        padding: 60px 20px;
        text-align: center;
        color: #adb5bd;
    }

    .no-proof i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .no-proof p {
        font-size: 14px;
        margin: 0;
    }

    /* Alert Box */
    .alert-box {
        background: white;
        border-left: 4px solid;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .alert-box.info {
        border-left-color: #4299e1;
        background: #ebf8ff;
    }

    .alert-box.success {
        border-left-color: #48bb78;
        background: #f0fff4;
    }

    .alert-box.warning {
        border-left-color: #f6ad55;
        background: #fffaf0;
    }

    .alert-box.danger {
        border-left-color: #f56565;
        background: #fff5f5;
    }

    .alert-box i {
        font-size: 24px;
        flex-shrink: 0;
    }

    .alert-box.info i {
        color: #2b6cb0;
    }

    .alert-box.success i {
        color: #2f855a;
    }

    .alert-box.warning i {
        color: #dd6b20;
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
        color: #2c3e50;
    }

    .alert-box-text {
        font-size: 13px;
        color: #495057;
        margin: 0;
    }

    /* Back Button */
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        color: #2c3e50;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .back-button:hover {
        background: #f8f9fa;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        color: #2c3e50;
        text-decoration: none;
        transform: translateX(-4px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .payment-header-top {
            padding: 20px;
        }

        .payment-title {
            font-size: 22px;
        }

        .payment-info-bar {
            padding: 16px 20px;
        }

        .payment-badges {
            width: 100%;
        }

        .payment-actions {
            width: 100%;
        }

        .payment-actions .btn {
            flex: 1;
        }

        .info-card {
            padding: 20px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-4">

    <!-- Back Button -->
    <a href="<?= base_url('member/payment') ?>" class="back-button">
        <i class="material-icons-outlined">arrow_back</i>
        Kembali ke Riwayat Pembayaran
    </a>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <?php if (isset($payment)): ?>

        <!-- Status Alerts -->
        <?php if ($payment->status === 'pending'): ?>
            <div class="alert-box info">
                <i class="material-icons-outlined">info</i>
                <div class="alert-box-content">
                    <div class="alert-box-title">Menunggu Verifikasi</div>
                    <p class="alert-box-text">
                        Pembayaran Anda sedang dalam proses verifikasi oleh pengurus.
                        Anda akan mendapat notifikasi setelah pembayaran diverifikasi.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($payment->status === 'verified'): ?>
            <div class="alert-box success">
                <i class="material-icons-outlined">check_circle</i>
                <div class="alert-box-content">
                    <div class="alert-box-title">Pembayaran Terverifikasi</div>
                    <p class="alert-box-text">
                        Pembayaran Anda telah diverifikasi pada <?= date('d M Y H:i', strtotime($payment->verified_at)) ?>.
                        Terima kasih atas pembayaran iuran Anda!
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($payment->status === 'rejected'): ?>
            <div class="alert-box danger">
                <i class="material-icons-outlined">cancel</i>
                <div class="alert-box-content">
                    <div class="alert-box-title">Pembayaran Ditolak</div>
                    <p class="alert-box-text">
                        <?php if (!empty($payment->rejection_reason)): ?>
                            Alasan: <?= esc($payment->rejection_reason) ?><br><br>
                        <?php endif; ?>
                        Silakan upload bukti pembayaran yang baru atau hubungi pengurus untuk informasi lebih lanjut.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Header -->
        <div class="payment-header-card">
            <div class="payment-header-top">
                <div class="payment-id">
                    <i class="material-icons-outlined">receipt_long</i>
                    ID Pembayaran: #<?= str_pad($payment->id, 6, '0', STR_PAD_LEFT) ?>
                </div>

                <div class="payment-title">
                    <?php
                    $typeText = 'Iuran Bulanan';
                    if ($payment->payment_type === 'annual') {
                        $typeText = 'Iuran Tahunan';
                    } elseif ($payment->payment_type === 'donation') {
                        $typeText = 'Donasi';
                    }
                    echo $typeText;
                    ?>
                </div>

                <div class="payment-meta">
                    <div class="meta-item">
                        <i class="material-icons-outlined">calendar_today</i>
                        Diupload: <?= date('d M Y', strtotime($payment->created_at)) ?>
                    </div>
                    <?php if ($payment->period_month && $payment->period_year): ?>
                        <div class="meta-item">
                            <i class="material-icons-outlined">event</i>
                            Periode:
                            <?php
                            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            echo $months[$payment->period_month] . ' ' . $payment->period_year;
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="payment-info-bar">
                <div class="payment-badges">
                    <?php
                    $statusClass = 'pending';
                    $statusText = 'Pending';
                    if ($payment->status === 'verified') {
                        $statusClass = 'verified';
                        $statusText = 'Terverifikasi';
                    } elseif ($payment->status === 'rejected') {
                        $statusClass = 'rejected';
                        $statusText = 'Ditolak';
                    }

                    $typeClass = 'monthly';
                    if ($payment->payment_type === 'annual') {
                        $typeClass = 'annual';
                    } elseif ($payment->payment_type === 'donation') {
                        $typeClass = 'donation';
                    }
                    ?>
                    <span class="status-badge <?= $statusClass ?>">
                        <i class="material-icons-outlined">
                            <?= $payment->status === 'verified' ? 'check_circle' : ($payment->status === 'rejected' ? 'cancel' : 'pending') ?>
                        </i>
                        <?= $statusText ?>
                    </span>
                    <span class="type-badge <?= $typeClass ?>"><?= $typeText ?></span>
                </div>

                <div class="payment-actions">
                    <?php if ($payment->status === 'pending'): ?>
                        <button type="button" class="btn btn-sm btn-danger" onclick="cancelPayment(<?= $payment->id ?>)">
                            <i class="material-icons-outlined" style="font-size: 16px;">close</i>
                            Batalkan
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-icon">
                    <i class="material-icons-outlined">payments</i>
                </div>
                <div class="info-card-title">Informasi Pembayaran</div>
            </div>

            <div class="info-grid">
                <div class="info-item" style="grid-column: 1 / -1;">
                    <div class="info-label">Jumlah Pembayaran</div>
                    <div class="info-value amount">Rp <?= number_format($payment->amount, 0, ',', '.') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Tanggal Pembayaran</div>
                    <div class="info-value"><?= date('d F Y', strtotime($payment->payment_date)) ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Metode Pembayaran</div>
                    <div class="info-value <?= empty($payment->payment_method) ? 'empty' : '' ?>">
                        <?= !empty($payment->payment_method) ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : 'Tidak disebutkan' ?>
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

                <?php if (!empty($payment->notes)): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Catatan</div>
                        <div class="info-value" style="white-space: pre-wrap;"><?= esc($payment->notes) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Verification Information (if verified or rejected) -->
        <?php if ($payment->status !== 'pending'): ?>
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon">
                        <i class="material-icons-outlined">verified</i>
                    </div>
                    <div class="info-card-title">Informasi Verifikasi</div>
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
                            <div class="info-value" style="color: #dc2626; font-weight: 600;"><?= esc($payment->rejection_reason) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Proof Preview -->
        <div class="proof-preview-section">
            <div class="proof-preview-header">
                <div class="proof-preview-icon">
                    <i class="material-icons-outlined">image</i>
                </div>
                <div class="proof-preview-title">Bukti Pembayaran</div>
            </div>

            <?php if (!empty($payment->proof_file)): ?>
                <div class="proof-image-wrapper">
                    <img
                        src="<?= base_url('uploads/payment_proofs/' . $payment->proof_file) ?>"
                        alt="Bukti Pembayaran"
                        class="proof-image"
                        data-lightbox="payment-proof"
                        data-title="Bukti Pembayaran - ID #<?= str_pad($payment->id, 6, '0', STR_PAD_LEFT) ?>">
                </div>

                <div class="text-center mt-3">
                    <a
                        href="<?= base_url('member/payment/download/' . $payment->id) ?>"
                        class="btn btn-outline-success">
                        <i class="material-icons-outlined" style="font-size: 18px;">download</i>
                        Download Bukti Pembayaran
                    </a>
                </div>
            <?php else: ?>
                <div class="no-proof">
                    <i class="material-icons-outlined">image_not_supported</i>
                    <p>Tidak ada bukti pembayaran</p>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- Payment Not Found -->
        <div class="info-card">
            <div style="text-align: center; padding: 60px 20px; color: #adb5bd;">
                <i class="material-icons-outlined" style="font-size: 64px; margin-bottom: 20px; opacity: 0.5;">receipt_long</i>
                <h3 style="font-size: 20px; font-weight: 700; color: #6c757d; margin-bottom: 8px;">Data Pembayaran Tidak Ditemukan</h3>
                <p style="font-size: 14px; margin: 0;">Pembayaran yang Anda cari tidak ditemukan</p>
                <a href="<?= base_url('member/payment') ?>" class="btn btn-success mt-3">
                    Kembali ke Riwayat Pembayaran
                </a>
            </div>
        </div>

    <?php endif; ?>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Lightbox JS -->
<script src="<?= base_url('assets/plugins/lightbox/lightbox.min.js') ?>"></script>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        // Lightbox Configuration
        lightbox.option({
            'resizeDuration': 300,
            'wrapAround': true,
            'albumLabel': 'Gambar %1 dari %2'
        });
    });

    // Cancel Payment
    function cancelPayment(id) {
        Swal.fire({
            title: 'Batalkan Pembayaran?',
            html: 'Pembayaran akan dibatalkan dan data akan dihapus.<br><br><strong>Tindakan ini tidak dapat dibatalkan!</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to cancel endpoint
                window.location.href = '<?= base_url('member/payment/cancel/') ?>' + id;
            }
        });
    }
</script>
<?= $this->endSection() ?>
