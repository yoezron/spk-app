<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - SPK</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verify-container {
            max-width: 600px;
            width: 100%;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            border: none;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 40px;
            color: white;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .card-body {
            padding: 40px 30px;
        }

        .email-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .email-info strong {
            color: #667eea;
            font-size: 1.1em;
        }

        .instruction-list {
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }

        .instruction-list li {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: start;
        }

        .instruction-list li:last-child {
            border-bottom: none;
        }

        .instruction-list .number {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .instruction-list .text {
            flex: 1;
            padding-top: 5px;
        }

        .btn-resend {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-resend:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-resend:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .countdown {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 10px;
        }

        .help-section {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 5px;
            margin-top: 25px;
        }

        .help-section .help-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 10px;
        }

        .help-section ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .help-section li {
            color: #856404;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="verify-container">
        <div class="card">
            <div class="card-header">
                <div class="success-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h3 class="mb-2">Pendaftaran Berhasil!</h3>
                <p class="mb-0">Verifikasi email Anda untuk melanjutkan</p>
            </div>

            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="text-center mb-4">
                    <p class="lead mb-3">
                        <i class="fas fa-paper-plane text-primary"></i>
                        Email verifikasi telah dikirim ke:
                    </p>
                    <div class="email-info">
                        <strong><i class="fas fa-envelope"></i> <?= esc(session()->get('email') ?? 'email@anda.com') ?></strong>
                    </div>
                </div>

                <h5 class="mb-3"><i class="fas fa-list-ol"></i> Langkah Selanjutnya:</h5>
                <ul class="instruction-list">
                    <li>
                        <div class="number">1</div>
                        <div class="text">
                            <strong>Buka email Anda</strong><br>
                            <small class="text-muted">Cek inbox atau folder spam/junk</small>
                        </div>
                    </li>
                    <li>
                        <div class="number">2</div>
                        <div class="text">
                            <strong>Klik link verifikasi</strong><br>
                            <small class="text-muted">Link berlaku selama 60 menit</small>
                        </div>
                    </li>
                    <li>
                        <div class="number">3</div>
                        <div class="text">
                            <strong>Tunggu persetujuan pengurus</strong><br>
                            <small class="text-muted">Kami akan meninjau data dan bukti pembayaran Anda</small>
                        </div>
                    </li>
                    <li>
                        <div class="number">4</div>
                        <div class="text">
                            <strong>Login ke akun Anda</strong><br>
                            <small class="text-muted">Setelah disetujui, Anda bisa mengakses portal anggota</small>
                        </div>
                    </li>
                </ul>

                <div class="text-center mt-4">
                    <p class="mb-3">Tidak menerima email?</p>
                    <button type="button" class="btn btn-resend" id="resendBtn" onclick="resendVerification()">
                        <i class="fas fa-redo-alt"></i> Kirim Ulang Email Verifikasi
                    </button>
                    <div class="countdown" id="countdown" style="display: none;">
                        Kirim ulang tersedia dalam <strong><span id="timer">60</span> detik</strong>
                    </div>
                </div>

                <div class="help-section">
                    <div class="help-title">
                        <i class="fas fa-question-circle"></i> Tidak menemukan email?
                    </div>
                    <ul>
                        <li>Periksa folder <strong>Spam</strong> atau <strong>Junk</strong></li>
                        <li>Pastikan email <strong><?= esc(session()->get('email') ?? 'Anda') ?></strong> benar</li>
                        <li>Tunggu beberapa menit, terkadang email tertunda</li>
                        <li>Hubungi kami jika masalah berlanjut: <strong>admin@spk.org</strong></li>
                    </ul>
                </div>

                <hr class="my-4">

                <div class="text-center">
                    <p class="text-muted mb-3">Sudah verifikasi email?</p>
                    <a href="<?= base_url('auth/login') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt"></i> Login ke Akun
                    </a>
                    <br><br>
                    <a href="<?= base_url('/') ?>" class="text-muted small">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-white small">
                <i class="fas fa-lock"></i> Data Anda aman dan terlindungi
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let countdownTimer = null;
        let timeLeft = 0;

        /**
         * Resend verification email
         */
        function resendVerification() {
            const email = '<?= esc(session()->get('email') ?? '') ?>';
            const btn = document.getElementById('resendBtn');
            const countdown = document.getElementById('countdown');

            if (!email) {
                alert('Email tidak ditemukan. Silakan daftar ulang.');
                return;
            }

            // Disable button
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

            // Send AJAX request
            fetch('<?= base_url('auth/resend-verification') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showAlert('success', data.message || 'Email verifikasi berhasil dikirim ulang!');

                        // Start countdown
                        startCountdown(60);
                    } else {
                        // Show error message
                        showAlert('error', data.message || 'Gagal mengirim email. Silakan coba lagi.');

                        // Re-enable button
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-redo-alt"></i> Kirim Ulang Email Verifikasi';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');

                    // Re-enable button
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo-alt"></i> Kirim Ulang Email Verifikasi';
                });
        }

        /**
         * Start countdown timer
         */
        function startCountdown(seconds) {
            const btn = document.getElementById('resendBtn');
            const countdown = document.getElementById('countdown');
            const timer = document.getElementById('timer');

            timeLeft = seconds;
            countdown.style.display = 'block';
            btn.style.display = 'none';

            countdownTimer = setInterval(() => {
                timeLeft--;
                timer.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    countdown.style.display = 'none';
                    btn.style.display = 'inline-block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo-alt"></i> Kirim Ulang Email Verifikasi';
                }
            }, 1000);
        }

        /**
         * Show alert message
         */
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon}"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            const cardBody = document.querySelector('.card-body');
            cardBody.insertAdjacentHTML('afterbegin', alertHtml);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = cardBody.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }

        /**
         * Auto-start countdown if coming from resend
         */
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('resent') === '1') {
                startCountdown(60);
            }
        });
    </script>
</body>

</html>