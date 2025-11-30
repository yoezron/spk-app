<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">
                <i class="material-icons-outlined">qr_code_2</i>
                QR Code Verifikasi
            </h1>
            <p class="text-muted">QR Code untuk verifikasi identitas anggota</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/card') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Kartu
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- QR Code Display -->
        <div class="card shadow-lg border-0 text-center mb-4">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="material-icons-outlined">qr_code_scanner</i>
                    QR Code Kartu Anggota
                </h5>
            </div>
            <div class="card-body py-5">
                <!-- QR Code Container with Styling -->
                <div class="qr-display-container mb-4">
                    <div id="qrcode"></div>
                </div>

                <h5 class="mb-3">Scan untuk Verifikasi</h5>
                <p class="text-muted mb-4">
                    Gunakan aplikasi scanner QR code atau kamera smartphone untuk memverifikasi identitas Anda
                </p>

                <div class="alert alert-info text-start">
                    <div class="d-flex align-items-start">
                        <i class="material-icons-outlined me-2 text-info">info</i>
                        <div>
                            <strong>Cara Penggunaan:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Tunjukkan QR code ini kepada pihak yang memerlukan verifikasi</li>
                                <li>QR code dapat di-scan menggunakan aplikasi scanner atau kamera</li>
                                <li>Link verifikasi akan membuka halaman validasi kartu anggota</li>
                                <li>Atau gunakan tombol di bawah untuk membuka link langsung</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Member Info -->
                <div class="card bg-light mt-3">
                    <div class="card-body text-start">
                        <h6 class="card-title mb-3">Informasi Anggota</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted">Nama</small>
                                <p class="mb-0"><strong><?= esc($member->full_name ?? 'N/A') ?></strong></p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted">No. Anggota</small>
                                <p class="mb-0"><strong><?= esc($member->member_number ?? 'N/A') ?></strong></p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted">Universitas</small>
                                <p class="mb-0"><?= esc($member->university_name ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted">Provinsi</small>
                                <p class="mb-0"><?= esc($member->province_name ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="<?= base_url('verify/' . $verificationToken) ?>"
                               target="_blank"
                               class="btn btn-primary w-100">
                                <i class="material-icons-outlined">open_in_new</i>
                                Buka Link
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="printQRCode()">
                                <i class="material-icons-outlined">print</i>
                                Cetak
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="downloadQRCode()">
                                <i class="material-icons-outlined">download</i>
                                Download
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Verification Link -->
                <div class="mt-4">
                    <small class="text-muted">Link Verifikasi:</small>
                    <div class="input-group">
                        <input type="text"
                               class="form-control form-control-sm"
                               id="verificationLink"
                               value="<?= base_url('verify-card?token=' . $verificationToken) ?>"
                               readonly>
                        <button class="btn btn-sm btn-outline-secondary" type="button" onclick="copyToClipboard()">
                            <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="alert alert-warning border-warning">
            <div class="d-flex align-items-start">
                <i class="material-icons-outlined text-warning me-2 fs-3">warning</i>
                <div>
                    <h6 class="alert-heading">Perhatian Keamanan</h6>
                    <p class="mb-2">
                        <strong>Jangan bagikan</strong> QR code atau link verifikasi ini kepada pihak yang tidak terpercaya.
                    </p>
                    <p class="mb-0 small">
                        QR code ini berisi token unik untuk verifikasi identitas Anda. Hanya tunjukkan kepada pihak resmi yang memerlukan validasi kartu anggota.
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Info Card -->
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined text-info">lightbulb</i>
                    Tips Penggunaan
                </h6>
                <ul class="mb-0 small">
                    <li>QR code akan selalu aktif selama kartu anggota Anda valid</li>
                    <li>Simpan screenshot QR code untuk akses cepat saat offline</li>
                    <li>Pastikan QR code terlihat jelas saat di-scan</li>
                    <li>Hindari QR code terkena cahaya langsung yang berlebihan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- QR Code Generator Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= $qrCodeData ?>",
            width: 280,
            height: 280,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    });

    // Copy verification link to clipboard
    function copyToClipboard() {
        const input = document.getElementById('verificationLink');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(input.value).then(function() {
            alert('Link verifikasi berhasil disalin!');
        }, function(err) {
            console.error('Failed to copy: ', err);
        });
    }

    // Print QR Code
    function printQRCode() {
        window.print();
    }

    // Download QR Code as PNG
    function downloadQRCode() {
        const canvas = document.querySelector('#qrcode canvas');
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
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .qr-display-container {
        display: inline-block;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
    }

    .qr-display-container::before {
        content: '';
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        z-index: -1;
        opacity: 0.1;
    }

    #qrcode {
        display: inline-block;
        padding: 1rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    #qrcode canvas,
    #qrcode img {
        display: block;
        margin: 0 auto;
        border-radius: 10px;
    }

    @media print {
        .page-header,
        .btn,
        .alert,
        .input-group,
        .card:not(:has(#qrcode)),
        .col-auto {
            display: none !important;
        }

        .card {
            border: none;
            box-shadow: none;
        }

        .qr-display-container {
            box-shadow: none;
            background: white;
        }
    }

    @media (max-width: 768px) {
        .qr-display-container {
            padding: 1.5rem;
        }

        #qrcode {
            padding: 0.75rem;
        }
    }
</style>
<?= $this->endSection() ?>
