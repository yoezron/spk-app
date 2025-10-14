<?php

/**
 * View: Verify Card
 * Controller: Public\VerifyCardController
 * Description: Halaman verifikasi kartu anggota SPK via QR code atau manual input
 * 
 * Features:
 * - Hero section dengan illustration
 * - Manual input form (member number/token)
 * - QR scanner (HTML5 camera API - optional)
 * - Verification result display
 * - Member info display (jika valid)
 * - Error handling & validation
 * - Responsive design
 * - Security information
 * 
 * @package App\Views\Public
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('styles') ?>
<style>
    /* Hero Section */
    .verify-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 80px 0 60px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .verify-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .verify-hero .container {
        position: relative;
        z-index: 1;
    }

    .verify-hero h1 {
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 16px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .verify-hero p {
        font-size: 18px;
        opacity: 0.95;
        margin-bottom: 0;
    }

    .verify-icon {
        font-size: 120px;
        opacity: 0.2;
        margin-bottom: 20px;
    }

    /* Main Content */
    .verify-content {
        margin-top: -40px;
        margin-bottom: 80px;
        position: relative;
        z-index: 2;
    }

    .verify-card {
        background: white;
        border-radius: 16px;
        padding: 50px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .verify-card h3 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #2d3748;
    }

    .verify-card p {
        color: #718096;
        margin-bottom: 30px;
    }

    /* Input Form */
    .verify-input-group {
        position: relative;
        margin-bottom: 24px;
    }

    .verify-input-group input {
        height: 60px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0 20px 0 60px;
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .verify-input-group input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .verify-input-group i {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 24px;
        color: #a0aec0;
    }

    .verify-btn {
        height: 60px;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .verify-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .verify-btn i {
        font-size: 24px;
    }

    /* Divider */
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 40px 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 2px solid #e2e8f0;
    }

    .divider span {
        padding: 0 20px;
        color: #a0aec0;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
    }

    /* QR Scanner */
    .qr-scanner-section {
        text-align: center;
    }

    .qr-scanner-box {
        width: 100%;
        max-width: 400px;
        height: 400px;
        margin: 0 auto 24px;
        border: 3px dashed #e2e8f0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7fafc;
        position: relative;
        overflow: hidden;
    }

    .qr-scanner-box.active {
        border-color: #667eea;
        background: white;
    }

    .qr-scanner-placeholder {
        text-align: center;
        padding: 40px;
    }

    .qr-scanner-placeholder i {
        font-size: 80px;
        color: #cbd5e0;
        margin-bottom: 20px;
    }

    .qr-scanner-placeholder p {
        color: #a0aec0;
        font-size: 16px;
        margin: 0;
    }

    #qr-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    #qr-video.active {
        display: block;
    }

    .scanner-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 250px;
        height: 250px;
        border: 3px solid #667eea;
        border-radius: 16px;
        pointer-events: none;
    }

    .scanner-overlay::before,
    .scanner-overlay::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        border: 4px solid #667eea;
    }

    .scanner-overlay::before {
        top: -4px;
        left: -4px;
        border-right: none;
        border-bottom: none;
    }

    .scanner-overlay::after {
        bottom: -4px;
        right: -4px;
        border-left: none;
        border-top: none;
    }

    .scan-btn {
        background: white;
        border: 2px solid #667eea;
        color: #667eea;
    }

    .scan-btn:hover {
        background: #667eea;
        color: white;
    }

    /* Verification Result */
    .verification-result {
        display: none;
        margin-top: 40px;
    }

    .verification-result.show {
        display: block;
        animation: fadeInUp 0.5s ease;
    }

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

    .result-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .result-card.success {
        border-top: 4px solid #48bb78;
    }

    .result-card.error {
        border-top: 4px solid #f56565;
    }

    .result-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 40px;
    }

    .result-icon.success {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .result-icon.error {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        color: white;
    }

    .result-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 12px;
        text-align: center;
    }

    .result-title.success {
        color: #48bb78;
    }

    .result-title.error {
        color: #f56565;
    }

    .result-message {
        text-align: center;
        color: #718096;
        margin-bottom: 30px;
        font-size: 16px;
    }

    /* Member Info */
    .member-info {
        background: #f7fafc;
        border-radius: 12px;
        padding: 30px;
        margin-top: 30px;
    }

    .member-info-row {
        display: flex;
        padding: 16px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .member-info-row:last-child {
        border-bottom: none;
    }

    .member-info-label {
        flex: 0 0 200px;
        font-weight: 600;
        color: #4a5568;
    }

    .member-info-value {
        flex: 1;
        color: #2d3748;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.inactive {
        background: #fed7d7;
        color: #742a2a;
    }

    /* Security Info */
    .security-info {
        background: #edf2f7;
        border-left: 4px solid #667eea;
        padding: 20px;
        border-radius: 8px;
        margin-top: 40px;
    }

    .security-info h5 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 12px;
    }

    .security-info p {
        color: #4a5568;
        font-size: 14px;
        margin: 0;
        line-height: 1.6;
    }

    /* How It Works */
    .how-it-works {
        margin-top: 60px;
    }

    .how-it-works h3 {
        text-align: center;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 40px;
        color: #2d3748;
    }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .step-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.3s ease;
    }

    .step-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .step-number {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        margin: 0 auto 20px;
    }

    .step-card h4 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #2d3748;
    }

    .step-card p {
        color: #718096;
        font-size: 14px;
        margin: 0;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 40px;
    }

    .loading-spinner.show {
        display: block;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #e2e8f0;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive */
    @media (max-width: 767px) {
        .verify-hero h1 {
            font-size: 32px;
        }

        .verify-card {
            padding: 30px 20px;
        }

        .verify-card h3 {
            font-size: 24px;
        }

        .verify-input-group input {
            height: 50px;
            font-size: 16px;
            padding-left: 50px;
        }

        .verify-btn {
            height: 50px;
            font-size: 16px;
        }

        .qr-scanner-box {
            height: 300px;
        }

        .member-info-row {
            flex-direction: column;
            gap: 8px;
        }

        .member-info-label {
            flex: none;
        }

        .steps-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="verify-hero">
    <div class="container text-center">
        <i class="bi bi-qr-code-scan verify-icon"></i>
        <h1>Verifikasi Kartu Anggota</h1>
        <p>Cek keaslian dan status keanggotaan Serikat Pekerja Kampus</p>
    </div>
</div>

<!-- Main Content -->
<div class="verify-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Verification Form -->
                <div class="verify-card">
                    <h3><i class="bi bi-shield-check"></i> Verifikasi Manual</h3>
                    <p>Masukkan nomor anggota atau kode verifikasi dari kartu anggota Anda</p>

                    <form id="verifyForm" action="<?= base_url('verify-card/verify') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="verify-input-group">
                            <i class="bi bi-person-badge"></i>
                            <input type="text"
                                class="form-control"
                                id="verification_code"
                                name="verification_code"
                                placeholder="Masukkan nomor anggota atau kode verifikasi"
                                required
                                autocomplete="off">
                        </div>

                        <button type="submit" class="btn verify-btn w-100">
                            <i class="bi bi-search"></i>
                            Verifikasi Sekarang
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="divider">
                        <span>atau</span>
                    </div>

                    <!-- QR Scanner Section -->
                    <div class="qr-scanner-section">
                        <h3><i class="bi bi-qr-code-scan"></i> Scan QR Code</h3>
                        <p>Gunakan kamera untuk memindai QR code pada kartu anggota</p>

                        <div class="qr-scanner-box" id="qrScannerBox">
                            <div class="qr-scanner-placeholder">
                                <i class="bi bi-camera"></i>
                                <p>Klik tombol di bawah untuk mengaktifkan kamera</p>
                            </div>
                            <video id="qr-video" playsinline></video>
                            <div class="scanner-overlay" style="display: none;"></div>
                        </div>

                        <button type="button" class="btn verify-btn scan-btn w-100" id="startScanBtn">
                            <i class="bi bi-camera-fill"></i>
                            Aktifkan Kamera
                        </button>

                        <button type="button" class="btn btn-secondary w-100 mt-2" id="stopScanBtn" style="display: none;">
                            <i class="bi bi-x-circle"></i>
                            Hentikan Scan
                        </button>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner"></div>
                    <p>Memverifikasi data...</p>
                </div>

                <!-- Verification Result -->
                <div class="verification-result" id="verificationResult">
                    <!-- Result will be inserted here via JavaScript -->
                </div>

                <!-- Flash Messages -->
                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= session('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <?= session('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Security Info -->
                <div class="security-info">
                    <h5>
                        <i class="bi bi-shield-lock-fill"></i>
                        Informasi Keamanan
                    </h5>
                    <p>
                        Verifikasi kartu anggota ini menggunakan sistem keamanan terenkripsi.
                        Data pribadi anggota dilindungi dan hanya informasi status keanggotaan
                        yang ditampilkan untuk keperluan verifikasi. Jika Anda menemukan
                        kartu anggota palsu atau mencurigakan, silakan laporkan ke pengurus SPK.
                    </p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="how-it-works">
            <h3>Cara Verifikasi</h3>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>Pilih Metode</h4>
                    <p>Gunakan input manual atau scan QR code pada kartu anggota</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>Masukkan Data</h4>
                    <p>Ketik nomor anggota atau scan QR code menggunakan kamera</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>Lihat Hasil</h4>
                    <p>Sistem akan menampilkan status dan informasi keanggotaan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Form submission with AJAX
    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const code = formData.get('verification_code');

        if (!code || code.trim() === '') {
            alert('Silakan masukkan nomor anggota atau kode verifikasi');
            return;
        }

        // Show loading
        document.getElementById('loadingSpinner').classList.add('show');
        document.getElementById('verificationResult').classList.remove('show');

        // Submit via AJAX
        fetch('<?= base_url('verify-card/verify') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading
                document.getElementById('loadingSpinner').classList.remove('show');

                // Show result
                displayResult(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingSpinner').classList.remove('show');

                displayResult({
                    success: false,
                    message: 'Terjadi kesalahan saat memverifikasi. Silakan coba lagi.'
                });
            });
    });

    // Display verification result
    function displayResult(data) {
        const resultDiv = document.getElementById('verificationResult');

        if (data.success) {
            // Success result
            const member = data.member;
            resultDiv.innerHTML = `
            <div class="result-card success">
                <div class="result-icon success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h3 class="result-title success">Kartu Valid!</h3>
                <p class="result-message">${data.message}</p>
                
                <div class="member-info">
                    <div class="member-info-row">
                        <div class="member-info-label">Nomor Anggota:</div>
                        <div class="member-info-value"><strong>${member.member_number}</strong></div>
                    </div>
                    <div class="member-info-row">
                        <div class="member-info-label">Nama Lengkap:</div>
                        <div class="member-info-value">${member.full_name}</div>
                    </div>
                    <div class="member-info-row">
                        <div class="member-info-label">Status:</div>
                        <div class="member-info-value">
                            <span class="status-badge active">
                                <i class="bi bi-check-circle"></i> Aktif
                            </span>
                        </div>
                    </div>
                    ${member.province_name ? `
                    <div class="member-info-row">
                        <div class="member-info-label">Wilayah:</div>
                        <div class="member-info-value">${member.province_name}</div>
                    </div>
                    ` : ''}
                    ${member.university_name ? `
                    <div class="member-info-row">
                        <div class="member-info-label">Kampus:</div>
                        <div class="member-info-value">${member.university_name}</div>
                    </div>
                    ` : ''}
                    ${member.card_expiry ? `
                    <div class="member-info-row">
                        <div class="member-info-label">Masa Berlaku:</div>
                        <div class="member-info-value">${formatDate(member.card_expiry)}</div>
                    </div>
                    ` : ''}
                    <div class="member-info-row">
                        <div class="member-info-label">Terverifikasi:</div>
                        <div class="member-info-value">${formatDate(new Date())}</div>
                    </div>
                </div>

                <button class="btn verify-btn w-100 mt-4" onclick="resetVerification()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Verifikasi Kartu Lain
                </button>
            </div>
        `;
        } else {
            // Error result
            resultDiv.innerHTML = `
            <div class="result-card error">
                <div class="result-icon error">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <h3 class="result-title error">Verifikasi Gagal</h3>
                <p class="result-message">${data.message}</p>

                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Perhatian:</strong><br>
                    Jika Anda yakin kartu ini asli, silakan hubungi pengurus SPK untuk verifikasi lebih lanjut.
                </div>

                <button class="btn verify-btn w-100 mt-3" onclick="resetVerification()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Coba Lagi
                </button>
            </div>
        `;
        }

        resultDiv.classList.add('show');

        // Scroll to result
        resultDiv.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    }

    // Reset verification
    function resetVerification() {
        document.getElementById('verificationResult').classList.remove('show');
        document.getElementById('verification_code').value = '';
        document.getElementById('verification_code').focus();

        // Scroll to top
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Format date
    function formatDate(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }

        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return date.toLocaleDateString('id-ID', options);
    }

    // QR Scanner functionality (basic HTML5 implementation)
    let stream = null;
    const video = document.getElementById('qr-video');
    const startBtn = document.getElementById('startScanBtn');
    const stopBtn = document.getElementById('stopScanBtn');
    const scannerBox = document.getElementById('qrScannerBox');

    startBtn.addEventListener('click', async function() {
        try {
            // Request camera access
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment'
                }
            });

            video.srcObject = stream;
            video.classList.add('active');
            video.play();

            scannerBox.classList.add('active');
            scannerBox.querySelector('.scanner-overlay').style.display = 'block';

            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';

            // Note: For actual QR code scanning, you would need a library like jsQR
            // This is just a placeholder for camera activation
            alert('Kamera aktif! Untuk implementasi lengkap scanning QR code, gunakan library seperti jsQR atau ZXing.');

        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera dan menggunakan HTTPS.');
        }
    });

    stopBtn.addEventListener('click', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.classList.remove('active');
            scannerBox.classList.remove('active');
            scannerBox.querySelector('.scanner-overlay').style.display = 'none';

            startBtn.style.display = 'block';
            stopBtn.style.display = 'none';
        }
    });

    // Auto-focus input on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('verification_code').focus();
    });

    // Handle URL parameters (for QR code redirect)
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token') || urlParams.get('code');

    if (token) {
        document.getElementById('verification_code').value = token;
        document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
    }
</script>
<?= $this->endSection() ?>