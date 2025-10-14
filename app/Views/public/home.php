<?php

/**
 * View: Home (Landing Page)
 * Controller: Public\HomeController
 * Description: Landing page untuk Serikat Pekerja Kampus
 * 
 * Features:
 * - Hero section dengan gradient background
 * - Statistics overview (members, provinces, universities)
 * - About SPK section
 * - Latest blog posts
 * - Call-to-action untuk registrasi
 * - Responsive design
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
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 120px 0 80px;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        z-index: 1;
        color: white;
        text-align: center;
    }

    .hero-title {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .hero-subtitle {
        font-size: 20px;
        margin-bottom: 40px;
        opacity: 0.95;
    }

    .hero-buttons .btn {
        margin: 0 10px;
        padding: 14px 32px;
        font-size: 16px;
        font-weight: 500;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .hero-buttons .btn-primary {
        background: white;
        color: #667eea;
        border: none;
    }

    .hero-buttons .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .hero-buttons .btn-outline-light {
        border: 2px solid white;
        color: white;
    }

    .hero-buttons .btn-outline-light:hover {
        background: white;
        color: #667eea;
    }

    /* Statistics Section */
    .stats-section {
        margin-top: -60px;
        position: relative;
        z-index: 2;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .stat-icon i {
        font-size: 32px;
        color: white;
    }

    .stat-number {
        font-size: 40px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .stat-label {
        color: #718096;
        font-size: 16px;
    }

    /* About Section */
    .about-section {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .section-title {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 16px;
        text-align: center;
    }

    .section-subtitle {
        color: #718096;
        font-size: 18px;
        text-align: center;
        margin-bottom: 60px;
    }

    .feature-card {
        text-align: center;
        padding: 30px 20px;
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .feature-icon i {
        font-size: 36px;
        color: white;
    }

    .feature-title {
        font-size: 20px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 12px;
    }

    .feature-desc {
        color: #718096;
        line-height: 1.6;
    }

    /* Blog Section */
    .blog-section {
        padding: 80px 0;
    }

    .blog-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .blog-card-img {
        height: 200px;
        object-fit: cover;
    }

    .blog-card-body {
        padding: 24px;
    }

    .blog-card-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-excerpt {
        color: #718096;
        font-size: 14px;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-meta {
        font-size: 13px;
        color: #a0aec0;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .blog-card-meta i {
        font-size: 16px;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 80px 0;
        color: white;
        text-align: center;
    }

    .cta-title {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .cta-subtitle {
        font-size: 18px;
        margin-bottom: 40px;
        opacity: 0.95;
    }

    .cta-button {
        background: white;
        color: #667eea;
        padding: 16px 48px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 50px;
        border: none;
        transition: all 0.3s ease;
    }

    .cta-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        color: #667eea;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 32px;
        }

        .hero-subtitle {
            font-size: 16px;
        }

        .hero-buttons .btn {
            display: block;
            margin: 10px auto;
            width: 100%;
            max-width: 280px;
        }

        .stat-number {
            font-size: 32px;
        }

        .section-title {
            font-size: 28px;
        }

        .cta-title {
            font-size: 28px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Serikat Pekerja Kampus</h1>
            <p class="hero-subtitle">
                Bersatu untuk Kesejahteraan Pekerja Pendidikan Tinggi di Indonesia
            </p>
            <div class="hero-buttons">
                <?php if ($showRegisterCTA): ?>
                    <a href="<?= base_url('auth/register') ?>" class="btn btn-primary">
                        <i class="material-icons-outlined align-middle me-2" style="font-size: 20px;">how_to_reg</i>
                        Daftar Sekarang
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('about') ?>" class="btn btn-outline-light">
                    <i class="material-icons-outlined align-middle me-2" style="font-size: 20px;">info</i>
                    Tentang SPK
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="material-icons-outlined">group</i>
                    </div>
                    <div class="stat-number"><?= number_format($totalMembers ?? 0) ?></div>
                    <div class="stat-label">Anggota Aktif</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="material-icons-outlined">location_on</i>
                    </div>
                    <div class="stat-number"><?= $totalProvinces ?? 0 ?></div>
                    <div class="stat-label">Provinsi</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="material-icons-outlined">school</i>
                    </div>
                    <div class="stat-number"><?= number_format($totalUniversities ?? 0) ?></div>
                    <div class="stat-label">Kampus</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="material-icons-outlined">trending_up</i>
                    </div>
                    <div class="stat-number"><?= number_format($growthPercentage ?? 0, 1) ?>%</div>
                    <div class="stat-label">Pertumbuhan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <h2 class="section-title">Mengapa Bergabung dengan SPK?</h2>
        <p class="section-subtitle">
            Bersama SPK, kita memperjuangkan hak dan kesejahteraan pekerja pendidikan tinggi
        </p>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="material-icons-outlined">gavel</i>
                    </div>
                    <h3 class="feature-title">Advokasi Hukum</h3>
                    <p class="feature-desc">
                        Kami memberikan bantuan hukum dan advokasi untuk melindungi hak-hak pekerja kampus
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="material-icons-outlined">people</i>
                    </div>
                    <h3 class="feature-title">Solidaritas</h3>
                    <p class="feature-desc">
                        Membangun jaringan solidaritas antar pekerja kampus di seluruh Indonesia
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="material-icons-outlined">campaign</i>
                    </div>
                    <h3 class="feature-title">Suara Bersama</h3>
                    <p class="feature-desc">
                        Memperjuangkan kebijakan yang adil untuk peningkatan kesejahteraan bersama
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="material-icons-outlined">school</i>
                    </div>
                    <h3 class="feature-title">Pendidikan & Pelatihan</h3>
                    <p class="feature-desc">
                        Program peningkatan kapasitas dan pengembangan diri untuk anggota
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="material-icons-outlined">forum</i>
                    </div>
                    <h3 class="feature-title">Forum Diskusi</h3>
                    <p class="feature-desc">
                        Ruang berbagi pengalaman dan diskusi isu-isu ketenagakerjaan
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="material-icons-outlined">support</i>
                    </div>
                    <h3 class="feature-title">Layanan Pengaduan</h3>
                    <p class="feature-desc">
                        Sistem pengaduan untuk menampung dan menindaklanjuti masalah anggota
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<?php if (!empty($latestPosts)): ?>
    <section class="blog-section">
        <div class="container">
            <h2 class="section-title">Berita & Artikel Terbaru</h2>
            <p class="section-subtitle">
                Update terkini seputar dunia kerja pendidikan tinggi dan kegiatan SPK
            </p>

            <div class="row g-4">
                <?php foreach ($latestPosts as $post): ?>
                    <div class="col-md-4">
                        <div class="card blog-card">
                            <?php if (!empty($post->featured_image)): ?>
                                <img src="<?= base_url('uploads/posts/' . esc($post->featured_image)) ?>"
                                    class="blog-card-img"
                                    alt="<?= esc($post->title) ?>">
                            <?php else: ?>
                                <div class="blog-card-img" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                            <?php endif; ?>

                            <div class="blog-card-body">
                                <h3 class="blog-card-title">
                                    <a href="<?= base_url('blog/' . esc($post->slug)) ?>" class="text-decoration-none text-dark">
                                        <?= esc($post->title) ?>
                                    </a>
                                </h3>
                                <p class="blog-card-excerpt">
                                    <?= esc($post->excerpt ?? strip_tags(substr($post->content, 0, 150))) ?>
                                </p>
                                <div class="blog-card-meta">
                                    <span>
                                        <i class="material-icons-outlined">person</i>
                                        <?= esc($post->author_name ?? 'Admin') ?>
                                    </span>
                                    <span>
                                        <i class="material-icons-outlined">calendar_today</i>
                                        <?= date('d M Y', strtotime($post->published_at ?? $post->created_at)) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?= base_url('blog') ?>" class="btn btn-outline-primary">
                    Lihat Semua Artikel
                    <i class="material-icons-outlined align-middle ms-1" style="font-size: 18px;">arrow_forward</i>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- CTA Section -->
<?php if ($showRegisterCTA): ?>
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Siap Bergabung dengan Kami?</h2>
            <p class="cta-subtitle">
                Mari bersama-sama memperjuangkan kesejahteraan dan hak-hak pekerja kampus
            </p>
            <a href="<?= base_url('auth/register') ?>" class="btn cta-button">
                <i class="material-icons-outlined align-middle me-2" style="font-size: 22px;">how_to_reg</i>
                Daftar Sebagai Anggota
            </a>
        </div>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.stat-card, .feature-card, .blog-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
</script>
<?= $this->endSection() ?>