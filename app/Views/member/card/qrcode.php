<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $pageTitle ?></h1>
            <p class="text-muted">QR Code untuk verifikasi kartu anggota</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/card') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Kartu
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <!-- QR Code Display -->
        <div class="card text-center">
            <div class="card-body py-5">
                <h4 class="mb-4">QR Code Verifikasi Kartu</h4>

                <!-- QR Code (using qrcode.js library) -->
                <div id="qrcode" class="mb-4 d-flex justify-content-center"></div>

                <div class="alert alert-info text-start">
                    <i class="material-icons-outlined">info</i>
                    <strong>Cara Penggunaan:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Tunjukkan QR code ini untuk verifikasi identitas</li>
                        <li>QR code dapat di-scan menggunakan aplikasi scanner</li>
                        <li>Atau klik tombol "Verifikasi Online" untuk membuka link verifikasi</li>
                    </ol>
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
                <div class="mt-4 d-grid gap-2">
                    <a href="<?= base_url('verify-card?token=' . $verificationToken) ?>"
                       target="_blank"
                       class="btn btn-primary">
                        <i class="material-icons-outlined">open_in_new</i>
                        Buka Link Verifikasi
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="printQRCode()">
                        <i class="material-icons-outlined">print</i>
                        Cetak QR Code
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="downloadQRCode()">
                        <i class="material-icons-outlined">download</i>
                        Download QR Code
                    </button>
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
        <div class="alert alert-warning mt-3">
            <i class="material-icons-outlined">warning</i>
            <strong>Penting:</strong> Jangan bagikan QR code atau link verifikasi ini ke pihak yang tidak terpercaya.
            QR code ini berisi informasi unik untuk verifikasi identitas Anda.
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
            width: 256,
            height: 256,
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
    @media print {
        .page-header,
        .btn,
        .alert-warning,
        .input-group {
            display: none !important;
        }

        .card {
            border: none;
            box-shadow: none;
        }
    }
</style>
<?= $this->endSection() ?>
