<?php

/**
 * View: Email Verification
 * Controller: Auth\VerifyController
 * Description: Halaman verifikasi email setelah registrasi
 * 
 * Features:
 * - Success message registrasi berhasil
 * - Instruksi untuk check email
 * - Resend verification email dengan countdown timer
 * - Display email yang perlu diverifikasi
 * - Link ke login jika sudah verifikasi
 * 
 * @package App\Views\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('styles') ?>
<style>
    /* Icon container */
    .verification-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        50% {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
        }
    }

    .verification-icon i {
        font-size: 48px;
        color: white;
    }

    /* Email display */
    .email-display {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
        margin: 20px 0;
        word-break: break-all;
    }

    .email-display i {
        color: #667eea;
        margin-right: 10px;
        vertical-align: middle;
    }

    .email-display strong {
        color: #2d3748;
        font-size: 16px;
    }

    /* Steps list */
    .verification-steps {
        text-align: left;
        margin: 30px 0;
    }

    .verification-steps li {
        margin-bottom: 15px;
        padding-left: 30px;
        position: relative;
        color: #4a5568;
    }

    .verification-steps li::before {
        content: '✓';
        position: absolute;
        left: 0;
        width: 24px;
        height: 24px;
        background: #667eea;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }

    /* Resend button */
    .resend-button {
        margin-top: 20px;
    }

    .resend-timer {
        color: #6c757d;
        font-size: 14px;
        margin-top: 10px;
    }

    .resend-timer.active {
        color: #667eea;
        font-weight: 500;
    }

    /* Help box */
    .help-box {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin-top: 30px;
    }

    .help-box i {
        color: #856404;
        margin-right: 8px;
        vertical-align: middle;
    }

    .help-box p {
        margin: 0;
        color: #856404;
        font-size: 14px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="text-center">
    <!-- Success Icon -->
    <div class="verification-icon">
        <i class="material-icons-outlined">mark_email_read</i>
    </div>

    <!-- Title -->
    <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">
        Verifikasi Email Anda
    </h4>

    <!-- Success Message -->
    <p class="text-muted mb-4">
        Terima kasih telah mendaftar! Kami telah mengirimkan email verifikasi ke:
    </p>

    <!-- Email Display -->
    <div class="email-display">
        <i class="material-icons-outlined">email</i>
        <strong><?= esc($email ?? 'email@example.com') ?></strong>
    </div>

    <!-- Instructions -->
    <div class="verification-steps">
        <ol style="padding-left: 0; list-style: none;">
            <li>
                <strong>Buka email Anda</strong><br>
                <small class="text-muted">Periksa folder Inbox atau Spam/Junk</small>
            </li>
            <li>
                <strong>Klik link verifikasi</strong><br>
                <small class="text-muted">Link aktif selama 24 jam</small>
            </li>
            <li>
                <strong>Selesai!</strong><br>
                <small class="text-muted">Akun Anda akan aktif dan siap digunakan</small>
            </li>
        </ol>
    </div>

    <!-- Resend Verification -->
    <div class="resend-button">
        <p class="text-muted mb-2">Tidak menerima email?</p>
        <button
            type="button"
            class="btn btn-outline-primary"
            id="resendButton"
            onclick="resendVerification()">
            <i class="material-icons-outlined align-middle me-1" style="font-size: 18px;">refresh</i>
            Kirim Ulang Email
        </button>
        <div class="resend-timer" id="resendTimer"></div>
    </div>

    <!-- Divider -->
    <div class="divider my-4">
        <span>atau</span>
    </div>

    <!-- Already Verified -->
    <div class="text-center">
        <p class="mb-0">
            Sudah verifikasi email?
            <a href="<?= base_url('auth/login') ?>" class="text-decoration-none fw-bold">
                Login Sekarang
            </a>
        </p>
    </div>

    <!-- Help Box -->
    <div class="help-box">
        <i class="material-icons-outlined">info</i>
        <p>
            <strong>Butuh bantuan?</strong><br>
            Jika Anda mengalami masalah, silakan hubungi kami di
            <a href="mailto:support@spk.or.id">support@spk.or.id</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<div class="auth-footer">
    <p class="mb-0">
        <a href="<?= base_url('/') ?>" class="me-3">
            <i class="material-icons-outlined align-middle" style="font-size: 16px;">home</i>
            Kembali ke Beranda
        </a>
        <a href="<?= base_url('help') ?>">
            <i class="material-icons-outlined align-middle" style="font-size: 16px;">help</i>
            Bantuan
        </a>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Resend verification email functionality
    let resendCooldown = 0;
    let resendInterval = null;

    function resendVerification() {
        const resendButton = document.getElementById('resendButton');
        const resendTimer = document.getElementById('resendTimer');

        // Disable button
        resendButton.disabled = true;
        resendButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

        // AJAX request to resend verification email
        fetch('<?= base_url('auth/verify/resend') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email: '<?= esc($email ?? '') ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', 'Email verifikasi telah dikirim ulang! Silakan periksa inbox Anda.');

                    // Start countdown timer (60 seconds)
                    startResendTimer(60);
                } else {
                    // Show error message
                    showAlert('error', data.message || 'Gagal mengirim email. Silakan coba lagi.');

                    // Re-enable button
                    resendButton.disabled = false;
                    resendButton.innerHTML = '<i class="material-icons-outlined align-middle me-1" style="font-size: 18px;">refresh</i>Kirim Ulang Email';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');

                // Re-enable button
                resendButton.disabled = false;
                resendButton.innerHTML = '<i class="material-icons-outlined align-middle me-1" style="font-size: 18px;">refresh</i>Kirim Ulang Email';
            });
    }

    function startResendTimer(seconds) {
        const resendButton = document.getElementById('resendButton');
        const resendTimer = document.getElementById('resendTimer');

        resendCooldown = seconds;
        resendTimer.classList.add('active');

        // Clear existing interval
        if (resendInterval) {
            clearInterval(resendInterval);
        }

        // Update timer display
        updateTimerDisplay();

        // Start countdown
        resendInterval = setInterval(function() {
            resendCooldown--;

            if (resendCooldown <= 0) {
                clearInterval(resendInterval);
                resendButton.disabled = false;
                resendButton.innerHTML = '<i class="material-icons-outlined align-middle me-1" style="font-size: 18px;">refresh</i>Kirim Ulang Email';
                resendTimer.textContent = '';
                resendTimer.classList.remove('active');
            } else {
                updateTimerDisplay();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const resendTimer = document.getElementById('resendTimer');
        resendTimer.textContent = `Anda dapat mengirim ulang dalam ${resendCooldown} detik`;
    }

    function showAlert(type, message) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="material-icons-outlined align-middle me-2">${type === 'success' ? 'check_circle' : 'error'}</i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at the top of auth-body
        const authBody = document.querySelector('.auth-body');
        authBody.insertBefore(alertDiv, authBody.firstChild);

        // Auto-remove after 5 seconds
        setTimeout(function() {
            alertDiv.remove();
        }, 5000);
    }

    // Check if there's a resend cooldown from session
    document.addEventListener('DOMContentLoaded', function() {
        const sessionCooldown = <?= session('resend_cooldown') ?? 0 ?>;
        if (sessionCooldown > 0) {
            startResendTimer(sessionCooldown);
        }

        // Auto-check verification status every 30 seconds
        setInterval(function() {
            fetch('<?= base_url('auth/verify/check-status') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.verified) {
                        // Redirect to dashboard if verified
                        window.location.href = '<?= base_url('member/dashboard') ?>';
                    }
                })
                .catch(error => {
                    console.error('Error checking verification status:', error);
                });
        }, 30000); // Check every 30 seconds
    });
</script>

<style>
    /* Divider styling */
    .divider {
        text-align: center;
        margin: 24px 0;
        position: relative;
    }

    .divider::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 100%;
        height: 1px;
        background: #dee2e6;
    }

    .divider span {
        background: #ffffff;
        padding: 0 12px;
        position: relative;
        color: #6c757d;
        font-size: 14px;
    }

    /* Alert animation */
    .alert {
        animation: slideDown 0.3s ease-in-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<?= $this->endSection() ?>