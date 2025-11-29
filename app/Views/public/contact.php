<?php

/**
 * View: Public Contact Form
 * Controller: Public\HomeController::contact()
 * Description: Halaman kontak publik dengan form untuk submit inquiry/pengaduan
 * 
 * Features:
 * - Contact form untuk publik (non-member)
 * - Multiple categories (pertanyaan, pengaduan, kerjasama, dll)
 * - reCAPTCHA v2 protection
 * - Client-side validation
 * - Success/error message handling
 * - Contact information display
 * - Social media links
 * - Google Maps integration
 * - WhatsApp direct contact
 * - Office hours display
 * - FAQ section
 * - Responsive design
 * 
 * @package App\Views\Public
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hubungi Serikat Pekerja Kampus (SPK) Indonesia untuk pertanyaan, pengaduan, atau informasi lebih lanjut tentang organisasi kami.">
    <meta name="keywords" content="kontak spk, hubungi spk, pengaduan pekerja kampus, serikat pekerja">
    <title>Hubungi Kami - Serikat Pekerja Kampus Indonesia</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #2c3e50;
        }

        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: #667eea !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            max-height: 40px;
            max-width: 150px;
            object-fit: contain;
        }

        .nav-link {
            color: #2c3e50 !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #667eea !important;
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,128C1248,117,1344,107,1392,101.3L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            opacity: 0.3;
        }

        .page-header h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 18px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        /* Main Content */
        .contact-section {
            padding: 60px 0;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }

        .section-subtitle {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 50px;
            text-align: center;
        }

        /* Contact Cards */
        .contact-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .contact-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid transparent;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .contact-card.email {
            border-top-color: #667eea;
        }

        .contact-card.phone {
            border-top-color: #27ae60;
        }

        .contact-card.whatsapp {
            border-top-color: #25D366;
        }

        .contact-card.address {
            border-top-color: #f39c12;
        }

        .contact-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
        }

        .contact-card.email .contact-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .contact-card.phone .contact-icon {
            background: linear-gradient(135deg, #27ae60, #1e8449);
        }

        .contact-card.whatsapp .contact-icon {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .contact-card.address .contact-icon {
            background: linear-gradient(135deg, #f39c12, #d68910);
        }

        .contact-card h4 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .contact-card p {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .contact-card a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .contact-card a:hover {
            color: #5568d3;
        }

        /* Contact Form */
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label .required {
            color: #e74c3c;
            margin-left: 3px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 6px;
        }

        .form-text {
            color: #6c757d;
            font-size: 13px;
            margin-top: 6px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .alert-success {
            background: #d5f4e6;
            color: #0d5826;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* Map */
        .map-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
        }

        .map-container h4 {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .map-frame {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            border: none;
        }

        /* FAQ Section */
        .faq-section {
            margin-top: 60px;
        }

        .faq-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .faq-question i {
            color: #667eea;
            font-size: 20px;
        }

        .faq-answer {
            color: #6c757d;
            line-height: 1.6;
            margin: 0;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0 20px 0;
            margin-top: 80px;
        }

        .footer h5 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 36px;
            }

            .form-card {
                padding: 25px;
            }

            .section-title {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <?php
                $logo = app_logo();
                if ($logo):
                ?>
                    <img src="<?= esc($logo) ?>" alt="<?= esc(app_name()) ?>" style="max-height: 40px; object-fit: contain;">
                <?php else: ?>
                    <i class="bi bi-building"></i>
                    <?= esc(app_name()) ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('about') ?>">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('blog') ?>">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('contact') ?>">Kontak</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-register" href="<?= base_url('auth/register') ?>">Daftar Anggota</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>
                <i class="bi bi-envelope-fill me-3"></i>
                Hubungi Kami
            </h1>
            <p>Punya pertanyaan atau ingin bergabung? Kami siap membantu Anda</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="contact-section">
        <div class="container">

            <!-- Section Title -->
            <h2 class="section-title">Ada yang Bisa Kami Bantu?</h2>
            <p class="section-subtitle">
                Tim SPK siap menjawab pertanyaan Anda dan memberikan informasi yang Anda butuhkan
            </p>

            <!-- Contact Cards -->
            <div class="contact-cards">
                <div class="contact-card email">
                    <div class="contact-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <h4>Email</h4>
                    <p>Kirim email ke kami</p>
                    <a href="mailto:info@spk.or.id">info@spk.or.id</a>
                </div>

                <div class="contact-card phone">
                    <div class="contact-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <h4>Telepon</h4>
                    <p>Senin - Jumat, 09:00 - 17:00</p>
                    <a href="tel:+622123456789">+62 21 2345 6789</a>
                </div>

                <div class="contact-card whatsapp">
                    <div class="contact-icon">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h4>WhatsApp</h4>
                    <p>Chat langsung dengan kami</p>
                    <a href="https://wa.me/6281234567890" target="_blank">+62 812 3456 7890</a>
                </div>

                <div class="contact-card address">
                    <div class="contact-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h4>Alamat</h4>
                    <p>Kunjungi kantor kami</p>
                    <a href="#map">Lihat di Peta</a>
                </div>
            </div>

            <div class="row mt-5">
                <!-- Contact Form -->
                <div class="col-lg-8 mb-4">
                    <div class="form-card">
                        <h3 style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 25px;">
                            <i class="bi bi-send me-2"></i>
                            Kirim Pesan
                        </h3>

                        <!-- Success/Error Alert -->
                        <?php if (session()->has('success')): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?= session('success') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= base_url('kontak/submit') ?>" id="contactForm">
                            <?= csrf_field() ?>

                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        Nama Lengkap<span class="required">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                                        id="name"
                                        name="name"
                                        value="<?= old('name') ?>"
                                        placeholder="Masukkan nama lengkap Anda"
                                        required>
                                    <?php if (session('errors.name')): ?>
                                        <div class="invalid-feedback"><?= session('errors.name') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        Email<span class="required">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                                        id="email"
                                        name="email"
                                        value="<?= old('email') ?>"
                                        placeholder="nama@email.com"
                                        required>
                                    <?php if (session('errors.email')): ?>
                                        <div class="invalid-feedback"><?= session('errors.email') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Phone -->
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        Nomor Telepon
                                    </label>
                                    <input
                                        type="tel"
                                        class="form-control <?= session('errors.phone') ? 'is-invalid' : '' ?>"
                                        id="phone"
                                        name="phone"
                                        value="<?= old('phone') ?>"
                                        placeholder="081234567890">
                                    <?php if (session('errors.phone')): ?>
                                        <div class="invalid-feedback"><?= session('errors.phone') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">
                                        Kategori<span class="required">*</span>
                                    </label>
                                    <select
                                        class="form-select <?= session('errors.category') ? 'is-invalid' : '' ?>"
                                        id="category"
                                        name="category"
                                        required>
                                        <option value="">Pilih kategori</option>
                                        <option value="pertanyaan" <?= old('category') === 'pertanyaan' ? 'selected' : '' ?>>
                                            Pertanyaan Umum
                                        </option>
                                        <option value="keanggotaan" <?= old('category') === 'keanggotaan' ? 'selected' : '' ?>>
                                            Informasi Keanggotaan
                                        </option>
                                        <option value="pengaduan" <?= old('category') === 'pengaduan' ? 'selected' : '' ?>>
                                            Pengaduan
                                        </option>
                                        <option value="kerjasama" <?= old('category') === 'kerjasama' ? 'selected' : '' ?>>
                                            Kerjasama
                                        </option>
                                        <option value="lainnya" <?= old('category') === 'lainnya' ? 'selected' : '' ?>>
                                            Lainnya
                                        </option>
                                    </select>
                                    <?php if (session('errors.category')): ?>
                                        <div class="invalid-feedback"><?= session('errors.category') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    Subjek<span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control <?= session('errors.subject') ? 'is-invalid' : '' ?>"
                                    id="subject"
                                    name="subject"
                                    value="<?= old('subject') ?>"
                                    placeholder="Judul pesan Anda"
                                    required>
                                <?php if (session('errors.subject')): ?>
                                    <div class="invalid-feedback"><?= session('errors.subject') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    Pesan<span class="required">*</span>
                                </label>
                                <textarea
                                    class="form-control <?= session('errors.message') ? 'is-invalid' : '' ?>"
                                    id="message"
                                    name="message"
                                    rows="6"
                                    placeholder="Tuliskan pesan Anda di sini..."
                                    required><?= old('message') ?></textarea>
                                <div class="form-text">Minimal 20 karakter</div>
                                <?php if (session('errors.message')): ?>
                                    <div class="invalid-feedback"><?= session('errors.message') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- reCAPTCHA -->
                            <div class="mb-4">
                                <div class="g-recaptcha" data-sitekey="YOUR_RECAPTCHA_SITE_KEY"></div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-submit">
                                <i class="bi bi-send-fill me-2"></i>
                                Kirim Pesan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="col-lg-4 mb-4">
                    <div class="form-card">
                        <h4 style="font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 20px;">
                            <i class="bi bi-info-circle me-2"></i>
                            Informasi Kontak
                        </h4>

                        <div style="margin-bottom: 20px;">
                            <h6 style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                                <i class="bi bi-clock me-2" style="color: #667eea;"></i>
                                Jam Operasional
                            </h6>
                            <p style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                                Senin - Jumat: 09:00 - 17:00 WIB
                            </p>
                            <p style="color: #6c757d; font-size: 14px; margin: 0;">
                                Sabtu - Minggu: Tutup
                            </p>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <h6 style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                                <i class="bi bi-geo-alt-fill me-2" style="color: #667eea;"></i>
                                Alamat Kantor
                            </h6>
                            <p style="color: #6c757d; font-size: 14px; margin: 0;">
                                Jl. Pekerja Kampus No. 123<br>
                                Jakarta Pusat 10110<br>
                                DKI Jakarta, Indonesia
                            </p>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <h6 style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                                <i class="bi bi-share me-2" style="color: #667eea;"></i>
                                Media Sosial
                            </h6>
                            <div class="social-links">
                                <a href="#" class="social-link" title="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="#" class="social-link" title="Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <a href="#" class="social-link" title="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <a href="#" class="social-link" title="LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                                <a href="#" class="social-link" title="YouTube">
                                    <i class="bi bi-youtube"></i>
                                </a>
                            </div>
                        </div>

                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                            <p style="font-size: 13px; color: #6c757d; margin: 0;">
                                <i class="bi bi-lightning-fill" style="color: #f39c12;"></i>
                                <strong>Respon Cepat:</strong> Kami akan merespon dalam 1-2 hari kerja
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="map-container" id="map">
                <h4>
                    <i class="bi bi-map me-2"></i>
                    Lokasi Kantor Kami
                </h4>
                <iframe
                    class="map-frame"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.666890489155!2d106.82493931476894!3d-6.175392195528034!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5d2e764b12d%3A0x3d2ad6e1e0e9bcc8!2sMonas!5e0!3m2!1sen!2sid!4v1234567890123!5m2!1sen!2sid"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>
            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
                <p class="section-subtitle">Temukan jawaban untuk pertanyaan umum</p>

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="faq-item">
                            <div class="faq-question">
                                <i class="bi bi-question-circle-fill"></i>
                                Bagaimana cara bergabung dengan SPK?
                            </div>
                            <p class="faq-answer">
                                Anda dapat mendaftar melalui halaman registrasi online kami atau menghubungi kantor terdekat untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="faq-item">
                            <div class="faq-question">
                                <i class="bi bi-question-circle-fill"></i>
                                Siapa saja yang bisa menjadi anggota?
                            </div>
                            <p class="faq-answer">
                                Semua pekerja kampus di Indonesia, termasuk dosen, tenaga kependidikan, dan staf administrasi dapat bergabung dengan SPK.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="faq-item">
                            <div class="faq-question">
                                <i class="bi bi-question-circle-fill"></i>
                                Berapa lama proses persetujuan keanggotaan?
                            </div>
                            <p class="faq-answer">
                                Proses verifikasi dan persetujuan biasanya memakan waktu 3-7 hari kerja setelah dokumen lengkap diterima.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="faq-item">
                            <div class="faq-question">
                                <i class="bi bi-question-circle-fill"></i>
                                Bagaimana cara mengajukan pengaduan?
                            </div>
                            <p class="faq-answer">
                                Anggota dapat mengajukan pengaduan melalui portal member atau menghubungi kami langsung melalui email/telepon untuk penanganan yang lebih cepat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <?php
                    $logo = app_logo();
                    if ($logo):
                    ?>
                        <div class="mb-3">
                            <img src="<?= esc($logo) ?>" alt="<?= esc(app_name()) ?>" style="max-height: 50px; object-fit: contain; filter: brightness(0) invert(1);">
                        </div>
                    <?php endif; ?>
                    <h5><?= esc(app_name()) ?></h5>
                    <p style="color: #bdc3c7; line-height: 1.6;">
                        <?= esc(app_tagline()) ?>
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="<?= base_url() ?>">Home</a></li>
                        <li><a href="<?= base_url('about') ?>">Tentang Kami</a></li>
                        <li><a href="<?= base_url('blog') ?>">Berita</a></li>
                        <li><a href="<?= base_url('contact') ?>">Kontak</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Anggota</h5>
                    <ul>
                        <li><a href="<?= base_url('auth/register') ?>">Daftar</a></li>
                        <li><a href="<?= base_url('auth/login') ?>">Login</a></li>
                        <?php if (auth()->loggedIn()): ?>
                            <li><a href="<?= user_dashboard_url() ?>">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-lg-4 mb-4">
                    <h5>Kontak</h5>
                    <ul>
                        <li>
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            Jl. Pekerja Kampus No. 123, Jakarta
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill me-2"></i>
                            info@spk.or.id
                        </li>
                        <li>
                            <i class="bi bi-telephone-fill me-2"></i>
                            +62 21 2345 6789
                        </li>
                        <li>
                            <i class="bi bi-whatsapp me-2"></i>
                            +62 812 3456 7890
                        </li>
                    </ul>
                </div>
            </div>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0 20px 0;">

            <div class="text-center" style="color: #bdc3c7;">
                <p>&copy; <?= date('Y') ?> <?= esc(app_name()) ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Form Validation -->
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            let isValid = true;

            // Validate name
            const name = document.getElementById('name').value.trim();
            if (name.length < 3) {
                alert('Nama harus minimal 3 karakter');
                isValid = false;
            }

            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Format email tidak valid');
                isValid = false;
            }

            // Validate message
            const message = document.getElementById('message').value.trim();
            if (message.length < 20) {
                alert('Pesan harus minimal 20 karakter');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Character counter for message
        document.getElementById('message').addEventListener('input', function() {
            const length = this.value.length;
            const formText = this.nextElementSibling;
            formText.textContent = `Minimal 20 karakter (${length} karakter)`;

            if (length >= 20) {
                formText.style.color = '#27ae60';
            } else {
                formText.style.color = '#6c757d';
            }
        });
    </script>

</body>

</html>