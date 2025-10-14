<?php

/**
 * View: Login
 * Controller: Auth\LoginController
 * Description: Halaman login untuk member SPK
 * 
 * Features:
 * - Email & password authentication
 * - Remember me functionality
 * - Links ke register & forgot password
 * - Validation error display
 * - CSRF protection
 * 
 * @package App\Views\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('auth/login') ?>" method="POST" id="loginForm">
    <?= csrf_field() ?>

    <!-- Email Field -->
    <div class="mb-3">
        <label for="email" class="form-label">
            Email <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="material-icons-outlined">email</i>
            </span>
            <input
                type="email"
                class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                id="email"
                name="email"
                placeholder="Masukkan email Anda"
                value="<?= old('email') ?>"
                required
                autofocus>
            <?php if (session('errors.email')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.email') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Password Field -->
    <div class="mb-3">
        <label for="password" class="form-label">
            Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="material-icons-outlined">lock</i>
            </span>
            <input
                type="password"
                class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                id="password"
                name="password"
                placeholder="Masukkan password Anda"
                required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="material-icons-outlined">visibility</i>
            </button>
            <?php if (session('errors.password')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.password') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                name="remember"
                id="remember"
                value="1"
                <?= old('remember') ? 'checked' : '' ?>>
            <label class="form-check-label" for="remember">
                Ingat Saya
            </label>
        </div>
        <a href="<?= base_url('auth/forgot-password') ?>" class="text-decoration-none">
            Lupa Password?
        </a>
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary btn-lg" id="loginButton">
            <i class="material-icons-outlined align-middle me-1" style="font-size: 20px;">login</i>
            Login
        </button>
    </div>

    <!-- Divider -->
    <div class="divider my-4">
        <span>atau</span>
    </div>

    <!-- Register Link -->
    <div class="text-center">
        <p class="mb-0">
            Belum punya akun?
            <a href="<?= base_url('auth/register') ?>" class="text-decoration-none fw-bold">
                Daftar Sekarang
            </a>
        </p>
    </div>
</form>
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
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const toggleIcon = togglePassword.querySelector('i');

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            if (type === 'text') {
                toggleIcon.textContent = 'visibility_off';
            } else {
                toggleIcon.textContent = 'visibility';
            }
        });

        // Form submission handling
        loginForm.addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            loginButton.disabled = true;
            loginButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

            // Validate form
            if (!loginForm.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                loginButton.disabled = false;
                loginButton.innerHTML = '<i class="material-icons-outlined align-middle me-1" style="font-size: 20px;">login</i>Login';
            }

            loginForm.classList.add('was-validated');
        });

        // Focus email field on page load
        document.getElementById('email').focus();

        // Enter key handling
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                loginForm.submit();
            }
        });
    });
</script>

<style>
    /* Input Group Styling */
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }

    .input-group .form-control {
        border-left: none;
    }

    .input-group .form-control:focus {
        border-color: #667eea;
        box-shadow: none;
    }

    .input-group .form-control:focus+.input-group-text,
    .input-group-text+.form-control:focus {
        border-color: #667eea;
    }

    /* Toggle Password Button */
    #togglePassword {
        border-left: none;
        background-color: #f8f9fa;
    }

    #togglePassword:hover {
        background-color: #e9ecef;
    }

    #togglePassword i {
        font-size: 20px;
    }

    /* Form Check */
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .form-check-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Links */
    a {
        color: #667eea;
        transition: color 0.3s ease;
    }

    a:hover {
        color: #5568d3;
    }

    /* Divider */
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

    /* Button */
    .btn-primary {
        font-weight: 500;
        padding: 12px;
    }

    .btn-primary:disabled {
        opacity: 0.7;
    }

    /* Footer Links */
    .auth-footer a {
        color: #6c757d;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
    }

    .auth-footer a:hover {
        color: #667eea;
    }

    /* Validation Styling */
    .was-validated .form-control:invalid {
        border-color: #dc3545;
        background-image: none;
    }

    .was-validated .form-control:valid {
        border-color: #28a745;
        background-image: none;
    }
</style>
<?= $this->endSection() ?>