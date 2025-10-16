<?php

/**
 * Layout: Auth
 * Neptune Admin Template - Authentication Layout
 * * Layout untuk halaman authentication (Login, Register, Email Verification)
 * Design: Clean card-based layout dengan background gradient
 * Features: Flash messages, CSRF protection, responsive design
 * * @package App\Views\Layouts
 * @author  SPK Development Team
 * @version 1.1.0 (Fixed vertical alignment for long content)
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Anggota - Serikat Pekerja Kampus">
    <meta name="keywords" content="SPK, Serikat Pekerja, Anggota">
    <meta name="author" content="SPK Development Team">

    <!-- Title -->
    <title><?= esc($title ?? 'Authentication - SI Anggota SPK') ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

    <!-- Neptune Admin CSS -->
    <link href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/main.min.css') ?>" rel="stylesheet">

    <!-- Custom Auth CSS -->
    <style>
        /*
         * KUNCI PERBAIKAN ADA DI SINI
         */
        body.auth-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            /* SEBELUMNYA: align-items: center; (Ini penyebab masalah) */
            /* SESUDAH:  align-items: flex-start; (Memulai konten dari atas) */
            align-items: flex-start;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            /* Menambahkan padding vertikal agar tidak menempel di atas */
            padding: 50px 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-in-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }

        .auth-logo {
            width: 80px;
            height: 80px;
            background: #ffffff;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .auth-logo i {
            font-size: 40px;
            color: #667eea;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .auth-body {
            padding: 40px 30px;
        }

        .auth-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }

        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Form Styling */
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 12px 16px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }

        .btn-outline-secondary {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 500;
        }

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 24px;
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

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
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

        /* Responsive */
        @media (max-width: 576px) {
            body.auth-page {
                padding: 20px 15px;
            }

            .auth-header {
                padding: 30px 20px;
            }

            .auth-body {
                padding: 30px 20px;
            }

            .auth-title {
                font-size: 20px;
            }

            .auth-logo {
                width: 60px;
                height: 60px;
            }

            .auth-logo i {
                font-size: 30px;
            }
        }
    </style>

    <!-- Additional CSS -->
    <?= $this->renderSection('styles') ?>
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Auth Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="material-icons-outlined">group</i>
                </div>
                <h1 class="auth-title"><?= esc($pageTitle ?? 'Formulir Pendaftaran') ?></h1>
                <p class="auth-subtitle">Sistem Informasi Anggota SPK</p>
            </div>

            <!-- Auth Body -->
            <div class="auth-body">
                <!-- Flash Messages -->
                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="material-icons-outlined me-2">check_circle</i>
                        <div><?= session('success') ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="material-icons-outlined me-2">error</i>
                        <div><?= session('error') ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('warning')): ?>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="material-icons-outlined me-2">warning</i>
                        <div><?= session('warning') ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('info')): ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="material-icons-outlined me-2">info</i>
                        <div><?= session('info') ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('errors') && is_array(session('errors'))): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="material-icons-outlined me-2">error</i>
                        <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Main Content -->
                <?= $this->renderSection('content') ?>
            </div>

            <!-- Auth Footer -->
            <?= $this->renderSection('footer') ?>
        </div>

        <!-- Copyright -->
        <div class="text-center mt-4">
            <p class="text-white mb-0" style="font-size: 14px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                &copy; <?= date('Y') ?> Serikat Pekerja Kampus. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Javascripts -->
    <script src="<?= base_url('assets/plugins/jquery/jquery-3.5.1.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.min.js') ?>"></script>

    <!-- Custom Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        });

        // Form validation enhancement
        $('form').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...');
        });
    </script>

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>