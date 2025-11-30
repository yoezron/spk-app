<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">
                <i class="material-icons-outlined">badge</i>
                <?= $pageTitle ?>
            </h1>
            <p class="text-muted">Kartu anggota digital Serikat Pekerja Kampus</p>
        </div>
        <div class="col-auto">
            <?php if (isset($cardStatus) && $cardStatus['status'] === 'expiring'): ?>
                <a href="<?= base_url('member/card/renew') ?>" class="btn btn-warning">
                    <i class="material-icons-outlined">autorenew</i>
                    Perpanjang Kartu
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Card Status Alert -->
<?php if (isset($cardStatus)): ?>
    <?php if ($cardStatus['status'] === 'expired'): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="material-icons-outlined me-3 fs-2">error</i>
            <div>
                <h5 class="alert-heading mb-1">Kartu Kadaluarsa</h5>
                <p class="mb-0"><?= $cardStatus['message'] ?> sejak <?= $cardStatus['expiration_date'] ?>.
                    <a href="<?= base_url('member/card/renew') ?>" class="alert-link">Ajukan perpanjangan sekarang</a>
                </p>
            </div>
        </div>
    <?php elseif ($cardStatus['status'] === 'expiring'): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="material-icons-outlined me-3 fs-2">warning</i>
            <div>
                <h5 class="alert-heading mb-1">Kartu Akan Kadaluarsa</h5>
                <p class="mb-0"><?= $cardStatus['message'] ?>.
                    <a href="<?= base_url('member/card/renew') ?>" class="alert-link">Ajukan perpanjangan</a>
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="material-icons-outlined me-3 fs-2">verified</i>
            <div>
                <h5 class="alert-heading mb-1">Kartu Aktif</h5>
                <p class="mb-0">Kartu Anda aktif hingga <?= $cardStatus['expiration_date'] ?></p>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row">
    <!-- Digital Card Display -->
    <div class="col-lg-8">
        <div class="card shadow-lg border-0 mb-4">
            <div class="card-body p-0">
                <!-- Card Front -->
                <div id="cardFront" class="member-card">
                    <div class="card-background">
                        <!-- Gradient Background -->
                        <div class="card-gradient"></div>

                        <!-- Organization Logo/Header -->
                        <div class="card-header-section">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="org-logo-circle">
                                        <i class="material-icons-outlined">groups</i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="card-org-name mb-0">Serikat Pekerja Kampus</h5>
                                    <small class="card-org-subtitle">Kartu Anggota Digital</small>
                                </div>
                            </div>
                        </div>

                        <!-- Member Information -->
                        <div class="card-content-section">
                            <div class="row">
                                <!-- Photo -->
                                <div class="col-auto">
                                    <div class="member-photo-container">
                                        <?php if (!empty($member->photo_path)): ?>
                                            <img src="<?= base_url($member->photo_path) ?>"
                                                 alt="<?= esc($member->full_name) ?>"
                                                 class="member-photo">
                                        <?php else: ?>
                                            <div class="member-photo-placeholder">
                                                <i class="material-icons-outlined">person</i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="col">
                                    <div class="member-details">
                                        <h4 class="member-name"><?= esc($member->full_name ?? 'N/A') ?></h4>
                                        <p class="member-number mb-3"><?= esc($member->member_number ?? 'N/A') ?></p>

                                        <div class="member-info">
                                            <div class="info-row">
                                                <span class="info-label">
                                                    <i class="material-icons-outlined">school</i>
                                                    Universitas
                                                </span>
                                                <span class="info-value"><?= esc($member->university_name ?? 'N/A') ?></span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">
                                                    <i class="material-icons-outlined">location_on</i>
                                                    Provinsi
                                                </span>
                                                <span class="info-value"><?= esc($member->province_name ?? 'N/A') ?></span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">
                                                    <i class="material-icons-outlined">event</i>
                                                    Bergabung
                                                </span>
                                                <span class="info-value">
                                                    <?= !empty($member->join_date) ? date('d M Y', strtotime($member->join_date)) : 'N/A' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- QR Code -->
                                <div class="col-auto d-none d-md-block">
                                    <div class="qr-code-container">
                                        <div id="cardQRCode"></div>
                                        <small class="qr-label">Scan untuk verifikasi</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer-section">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="expiry-info">
                                        <small>Berlaku hingga</small>
                                        <strong><?= $cardStatus['expiration_date'] ?? 'N/A' ?></strong>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="card-status-badge status-<?= $cardStatus['status'] ?? 'unknown' ?>">
                                        <?= $cardStatus['label'] ?? 'N/A' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card-actions p-4">
                    <div class="row g-2">
                        <div class="col-md-3 col-6">
                            <button type="button" class="btn btn-primary w-100" onclick="downloadCard()">
                                <i class="material-icons-outlined">download</i>
                                <span class="d-none d-sm-inline">Download PDF</span>
                                <span class="d-inline d-sm-none">PDF</span>
                            </button>
                        </div>
                        <div class="col-md-3 col-6">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="viewQRCode()">
                                <i class="material-icons-outlined">qr_code_2</i>
                                <span class="d-none d-sm-inline">Lihat QR</span>
                                <span class="d-inline d-sm-none">QR</span>
                            </button>
                        </div>
                        <div class="col-md-3 col-6">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="printCard()">
                                <i class="material-icons-outlined">print</i>
                                <span class="d-none d-sm-inline">Cetak</span>
                                <span class="d-inline d-sm-none">Cetak</span>
                            </button>
                        </div>
                        <div class="col-md-3 col-6">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="shareCard()">
                                <i class="material-icons-outlined">share</i>
                                <span class="d-none d-sm-inline">Bagikan</span>
                                <span class="d-inline d-sm-none">Share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile QR Code (visible on small screens) -->
        <div class="card d-md-none mb-4">
            <div class="card-body text-center">
                <h6 class="card-title">QR Code Verifikasi</h6>
                <div id="mobileQRCode" class="d-flex justify-content-center my-3"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewQRCode()">
                    Lihat Detail QR Code
                </button>
            </div>
        </div>

        <!-- Card Features -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">info</i>
                    Fitur Kartu Anggota
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="feature-item">
                            <div class="feature-icon bg-primary-subtle">
                                <i class="material-icons-outlined text-primary">verified</i>
                            </div>
                            <div class="feature-content">
                                <h6>Verifikasi Digital</h6>
                                <p class="text-muted small mb-0">
                                    Kartu dilengkapi QR code untuk verifikasi identitas secara online
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-item">
                            <div class="feature-icon bg-success-subtle">
                                <i class="material-icons-outlined text-success">cloud_download</i>
                            </div>
                            <div class="feature-content">
                                <h6>Download & Cetak</h6>
                                <p class="text-muted small mb-0">
                                    Unduh kartu dalam format PDF atau cetak langsung
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-item">
                            <div class="feature-icon bg-warning-subtle">
                                <i class="material-icons-outlined text-warning">security</i>
                            </div>
                            <div class="feature-content">
                                <h6>Keamanan Terjamin</h6>
                                <p class="text-muted small mb-0">
                                    Token verifikasi unik untuk mencegah pemalsuan
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-item">
                            <div class="feature-icon bg-info-subtle">
                                <i class="material-icons-outlined text-info">update</i>
                            </div>
                            <div class="feature-content">
                                <h6>Update Otomatis</h6>
                                <p class="text-muted small mb-0">
                                    Informasi kartu selalu sinkron dengan profil Anda
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="material-icons-outlined">flash_on</i>
                    Aksi Cepat
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('member/profile') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="material-icons-outlined">person</i>
                        Update Profil
                    </a>
                    <a href="<?= base_url('verify/' . ($verificationToken ?? '')) ?>"
                       target="_blank"
                       class="btn btn-outline-primary btn-sm">
                        <i class="material-icons-outlined">open_in_new</i>
                        Buka Link Verifikasi
                    </a>
                    <a href="<?= base_url('member/card/history') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="material-icons-outlined">history</i>
                        Riwayat Kartu
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Statistics -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="material-icons-outlined">analytics</i>
                    Statistik Kartu
                </h6>
            </div>
            <div class="card-body">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="material-icons-outlined text-primary">event</i>
                    </div>
                    <div class="stat-content">
                        <small class="text-muted">Masa Aktif</small>
                        <h6 class="mb-0">
                            <?= isset($cardStatus['days_until_expiration']) && $cardStatus['days_until_expiration'] > 0
                                ? $cardStatus['days_until_expiration'] . ' hari lagi'
                                : 'Kadaluarsa' ?>
                        </h6>
                    </div>
                </div>
                <hr>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="material-icons-outlined text-success">verified_user</i>
                    </div>
                    <div class="stat-content">
                        <small class="text-muted">Status</small>
                        <h6 class="mb-0"><?= $cardStatus['label'] ?? 'N/A' ?></h6>
                    </div>
                </div>
                <hr>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="material-icons-outlined text-info">card_membership</i>
                    </div>
                    <div class="stat-content">
                        <small class="text-muted">Nomor Anggota</small>
                        <h6 class="mb-0"><?= esc($member->member_number ?? 'N/A') ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">help_outline</i>
                    Bantuan
                </h6>
                <p class="small text-muted mb-3">
                    Ada pertanyaan tentang kartu anggota?
                </p>
                <div class="d-grid gap-2">
                    <a href="<?= base_url('member/complaint/create') ?>" class="btn btn-sm btn-outline-info">
                        <i class="material-icons-outlined">support_agent</i>
                        Hubungi Pengurus
                    </a>
                    <a href="<?= base_url('member/help') ?>" class="btn btn-sm btn-outline-info">
                        <i class="material-icons-outlined">menu_book</i>
                        Panduan Penggunaan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="material-icons-outlined">qr_code_2</i>
                    QR Code Verifikasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="modalQRCode" class="d-flex justify-content-center mb-3"></div>
                <div class="alert alert-info text-start">
                    <small>
                        <strong>Cara Penggunaan:</strong><br>
                        Tunjukkan QR code ini untuk verifikasi identitas atau scan untuk membuka halaman verifikasi online
                    </small>
                </div>
                <div class="input-group input-group-sm">
                    <input type="text"
                           class="form-control"
                           id="verificationLink"
                           value="<?= base_url('verify/' . ($verificationToken ?? '')) ?>"
                           readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">
                        <i class="material-icons-outlined">content_copy</i>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadQRImage()">
                    <i class="material-icons-outlined">download</i>
                    Download QR
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- QR Code Generator Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    const qrCodeData = "<?= base_url('verify/' . ($verificationToken ?? '')) ?>";

    // Generate QR Codes
    document.addEventListener('DOMContentLoaded', function() {
        // Card QR Code (smaller)
        new QRCode(document.getElementById("cardQRCode"), {
            text: qrCodeData,
            width: 100,
            height: 100,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Mobile QR Code
        if (document.getElementById("mobileQRCode")) {
            new QRCode(document.getElementById("mobileQRCode"), {
                text: qrCodeData,
                width: 150,
                height: 150,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });

    // Download Card as PDF
    function downloadCard() {
        window.location.href = "<?= base_url('member/card/download') ?>";
    }

    // View QR Code in Modal
    function viewQRCode() {
        // Generate QR code in modal
        const modalQR = document.getElementById("modalQRCode");
        modalQR.innerHTML = ''; // Clear previous

        new QRCode(modalQR, {
            text: qrCodeData,
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }

    // Print Card
    function printCard() {
        window.print();
    }

    // Share Card
    function shareCard() {
        if (navigator.share) {
            navigator.share({
                title: 'Kartu Anggota SPK',
                text: 'Verifikasi kartu anggota saya',
                url: qrCodeData
            }).catch(err => console.log('Error sharing:', err));
        } else {
            copyLink();
            alert('Link verifikasi telah disalin! Anda dapat membagikannya melalui aplikasi lain.');
        }
    }

    // Copy Link to Clipboard
    function copyLink() {
        const input = document.getElementById('verificationLink');
        input.select();
        input.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(input.value).then(() => {
            alert('Link verifikasi berhasil disalin!');
        });
    }

    // Download QR Code as Image
    function downloadQRImage() {
        const canvas = document.querySelector('#modalQRCode canvas');
        if (canvas) {
            const url = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = 'qrcode-<?= $member->member_number ?? 'card' ?>.png';
            link.href = url;
            link.click();
        }
    }
</script>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Member Card Styling */
    .member-card {
        position: relative;
        min-height: 450px;
        overflow: hidden;
        border-radius: 15px;
    }

    .card-background {
        position: relative;
        padding: 2rem;
        height: 100%;
    }

    .card-gradient {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0.95;
    }

    .card-header-section,
    .card-content-section,
    .card-footer-section {
        position: relative;
        z-index: 1;
        color: white;
    }

    .card-header-section {
        margin-bottom: 2rem;
    }

    .org-logo-circle {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }

    .org-logo-circle i {
        font-size: 32px;
        color: white;
    }

    .card-org-name {
        font-size: 1.25rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .card-org-subtitle {
        opacity: 0.9;
        font-size: 0.875rem;
    }

    .member-photo-container {
        width: 120px;
        height: 150px;
        border-radius: 10px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.1);
        border: 3px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }

    .member-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
    }

    .member-photo-placeholder i {
        font-size: 64px;
        color: rgba(255, 255, 255, 0.5);
    }

    .member-details {
        padding-left: 1rem;
    }

    .member-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .member-number {
        font-size: 1rem;
        opacity: 0.9;
        letter-spacing: 1px;
    }

    .member-info .info-row {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .member-info .info-label {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        opacity: 0.8;
        min-width: 120px;
    }

    .member-info .info-label i {
        font-size: 16px;
    }

    .member-info .info-value {
        font-weight: 500;
    }

    .qr-code-container {
        background: white;
        padding: 0.75rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }

    .qr-label {
        display: block;
        margin-top: 0.5rem;
        color: #666;
        font-size: 0.75rem;
    }

    .card-footer-section {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .expiry-info small {
        display: block;
        opacity: 0.8;
        font-size: 0.75rem;
    }

    .expiry-info strong {
        font-size: 1rem;
    }

    .card-status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-status-badge.status-active {
        background: rgba(34, 197, 94, 0.2);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .card-status-badge.status-expiring {
        background: rgba(251, 191, 36, 0.2);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .card-status-badge.status-expired {
        background: rgba(239, 68, 68, 0.2);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    /* Card Actions */
    .card-actions {
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }

    /* Feature Items */
    .feature-item {
        display: flex;
        gap: 1rem;
        align-items: start;
    }

    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .feature-icon i {
        font-size: 24px;
    }

    .feature-content h6 {
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    /* Stat Items */
    .stat-item {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 28px;
    }

    .stat-content {
        flex-grow: 1;
    }

    /* Print Styles */
    @media print {
        .page-header,
        .card-actions,
        .col-lg-4,
        .alert,
        .btn,
        body > *:not(.row) {
            display: none !important;
        }

        .col-lg-8 {
            width: 100%;
        }

        .member-card {
            page-break-inside: avoid;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .member-card {
            min-height: 400px;
        }

        .card-background {
            padding: 1.5rem;
        }

        .member-photo-container {
            width: 90px;
            height: 110px;
        }

        .member-name {
            font-size: 1.25rem;
        }

        .member-info .info-label {
            min-width: 100px;
            font-size: 0.8rem;
        }

        .org-logo-circle {
            width: 50px;
            height: 50px;
        }

        .card-org-name {
            font-size: 1rem;
        }
    }
</style>
<?= $this->endSection() ?>
