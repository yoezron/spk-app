<?php

/**
 * Layout: Public
 * Neptune Admin Template - Public Layout
 * 
 * Layout untuk halaman public (Landing page, Blog, About, Contact)
 * Features: Navbar, Hero section support, Footer, Responsive design
 * 
 * @package App\Views\Layouts
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc($metaDescription ?? 'Serikat Pekerja Kampus - Bersatu untuk Kesejahteraan Pekerja Pendidikan Tinggi') ?>">
    <meta name="keywords" content="<?= esc($metaKeywords ?? 'SPK, Serikat Pekerja, Kampus, Pendidikan Tinggi, Anggota') ?>">
    <meta name="author" content="SPK Development Team">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= esc($title ?? 'Serikat Pekerja Kampus') ?>">
    <meta property="og:description" content="<?= esc($metaDescription ?? 'Serikat Pekerja Kampus - Bersatu untuk Kesejahteraan') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <?php if (isset($metaImage)): ?>
        <meta property="og:image" content="<?= esc($metaImage) ?>">
    <?php endif; ?>

    <!-- Title -->
    <title><?= esc($title ?? 'Serikat Pekerja Kampus') ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

    <!-- Neptune Admin CSS -->
    <link href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/main.min.css') ?>" rel="stylesheet">

    <!-- Custom Public CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --text-dark: #2d3748;
            --text-light: #718096;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
        }

        /* Navbar Styling */
        .navbar-public {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            font-size: 32px;
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            color: var(--text-dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color);
        }

        .btn-register {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #ffffff;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
            color: #ffffff;
        }

        .btn-login {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: var(--primary-color);
            color: #ffffff;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #ffffff;
            padding: 80px 0;
            text-align: center;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        /* Content Section */
        .content-section {
            padding: 60px 0;
        }

        /* Footer Styling */
        .footer-public {
            background: #1a202c;
            color: #cbd5e0;
            padding: 60px 0 30px;
        }

        .footer-title {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .footer-link {
            color: #cbd5e0;
            text-decoration: none;
            display: block;
            padding: 8px 0;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--accent-color);
            padding-left: 5px;
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 40px;
            padding-top: 30px;
            text-align: center;
            font-size: 14px;
        }

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 16px;
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

        /* Card Styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 32px;
            }

            .hero-subtitle {
                font-size: 16px;
            }

            .content-section {
                padding: 40px 0;
            }

            .footer-public {
                padding: 40px 0 20px;
            }
        }
    </style>

    <!-- Additional CSS -->
    <?= $this->renderSection('styles') ?>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-public">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('/') ?>">
                <?php
                $logo = app_logo();
                if ($logo):
                ?>
                    <img src="<?= esc($logo) ?>" alt="<?= esc(app_name()) ?>" style="max-height: 40px; max-width: 150px; object-fit: contain;">
                <?php else: ?>
                    <i class="material-icons-outlined">group</i>
                    <span><?= esc(app_name()) ?></span>
                <?php endif; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link <?= url_is('/') ? 'active' : '' ?>" href="<?= base_url('/') ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= url_is('about') ? 'active' : '' ?>" href="<?= base_url('about') ?>">Tentang SPK</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= url_is('blog*') ? 'active' : '' ?>" href="<?= base_url('blog') ?>">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= url_is('contact') ? 'active' : '' ?>" href="<?= base_url('contact') ?>">Kontak</a>
                    </li>

                    <?php if (auth()->loggedIn()): ?>
                        <?php
                        $dashboardPath = trim(user_dashboard_path(), '/');
                        if ($dashboardPath === '') {
                            $dashboardActive = url_is('/');
                        } else {
                            $dashboardActive = url_is($dashboardPath) || url_is($dashboardPath . '/*');
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $dashboardActive ? 'active' : '' ?>" href="<?= user_dashboard_url() ?>">
                                <i class="material-icons-outlined" style="vertical-align: middle; font-size: 20px;">dashboard</i>
                                Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link btn-login" href="<?= base_url('auth/login') ?>">Login</a>
                        </li>
                        <?php if (config('Auth')->allowRegistration ?? true): ?>
                            <li class="nav-item ms-lg-2">
                                <a class="nav-link btn-register" href="<?= base_url('auth/register') ?>">Daftar Anggota</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info')): ?>
        <div class="container mt-4">
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
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <?= $this->renderSection('content') ?>

    <!-- Footer -->
    <footer class="footer-public">
        <div class="container">
            <div class="row">
                <!-- About -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <?php
                    $logo = app_logo();
                    if ($logo):
                    ?>
                        <div class="mb-3">
                            <img src="<?= esc($logo) ?>" alt="<?= esc(app_name()) ?>" style="max-height: 60px; max-width: 200px; object-fit: contain; filter: brightness(0) invert(1);">
                        </div>
                    <?php endif; ?>
                    <h5 class="footer-title"><?= esc(app_name()) ?></h5>
                    <p style="line-height: 1.8;">
                        <?= esc(app_tagline()) ?>
                    </p>
                    <div class="footer-social">
                        <a href="#" title="Facebook"><i class="material-icons">facebook</i></a>
                        <a href="#" title="Twitter"><i class="material-icons">twitter</i></a>
                        <a href="#" title="Instagram"><i class="material-icons">instagram</i></a>
                        <a href="#" title="YouTube"><i class="material-icons">youtube</i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Menu Cepat</h5>
                    <a href="<?= base_url('/') ?>" class="footer-link">Home</a>
                    <a href="<?= base_url('about') ?>" class="footer-link">Tentang Kami</a>
                    <a href="<?= base_url('manifesto') ?>" class="footer-link">Manifesto</a>
                    <a href="<?= base_url('adart') ?>" class="footer-link">AD/ART</a>
                    <a href="<?= base_url('sejarah') ?>" class="footer-link">Sejarah SPK</a>
                </div>

                <!-- Resources -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Sumber Daya</h5>
                    <a href="<?= base_url('blog') ?>" class="footer-link">Blog & Berita</a>
                    <a href="<?= base_url('struktur-organisasi') ?>" class="footer-link">Struktur Organisasi</a>
                    <a href="<?= base_url('contact') ?>" class="footer-link">Hubungi Kami</a>
                    <?php if (!auth()->loggedIn()): ?>
                        <a href="<?= base_url('auth/register') ?>" class="footer-link">Daftar Anggota</a>
                    <?php endif; ?>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Kontak</h5>
                    <?php
                    $contact_address = get_setting('App\\Config\\General', 'contact_address', 'Jl. Kampus No. 123, Jakarta, Indonesia');
                    $contact_email = get_setting('App\\Config\\General', 'contact_email', 'info@spk.or.id');
                    $contact_phone = get_setting('App\\Config\\General', 'contact_phone', '+62 21 1234 5678');
                    ?>
                    <?php if (!empty($contact_address)): ?>
                    <div class="d-flex align-items-start mb-3">
                        <i class="material-icons-outlined me-2">location_on</i>
                        <span><?= esc($contact_address) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($contact_email)): ?>
                    <div class="d-flex align-items-center mb-3">
                        <i class="material-icons-outlined me-2">email</i>
                        <a href="mailto:<?= esc($contact_email) ?>" style="color: inherit; text-decoration: none;">
                            <?= esc($contact_email) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($contact_phone)): ?>
                    <div class="d-flex align-items-center">
                        <i class="material-icons-outlined me-2">phone</i>
                        <a href="tel:<?= esc($contact_phone) ?>" style="color: inherit; text-decoration: none;">
                            <?= esc($contact_phone) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> Serikat Pekerja Kampus. All rights reserved.
                    <span class="mx-2">|</span>
                    <a href="<?= base_url('privacy') ?>" style="color: var(--accent-color);">Privacy Policy</a>
                    <span class="mx-2">|</span>
                    <a href="<?= base_url('terms') ?>" style="color: var(--accent-color);">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

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

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.hash);
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });

        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar-public').addClass('shadow-lg');
            } else {
                $('.navbar-public').removeClass('shadow-lg');
            }
        });
    </script>

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>