<?php

/**
 * Component: Footer
 * Footer component untuk dashboard pages
 * 
 * Simple & clean footer dengan copyright info dan quick links
 * Digunakan untuk member, admin, dan super admin dashboards
 * 
 * Optional variables:
 * - $showLinks: Boolean untuk menampilkan quick links (default: true)
 * - $footerType: 'minimal', 'standard', or 'extended' (default: 'standard')
 * 
 * @package App\Views\Components
 * @author  SPK Development Team
 * @version 1.0.0
 */

// Set default values
$showLinks = $showLinks ?? true;
$footerType = $footerType ?? 'standard';
$currentYear = date('Y');
?>

<?php if ($footerType === 'minimal'): ?>
    <!-- Minimal Footer -->
    <footer class="app-footer minimal-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 text-muted">
                        &copy; <?= $currentYear ?> Serikat Pekerja Kampus. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

<?php elseif ($footerType === 'extended'): ?>
    <!-- Extended Footer -->
    <footer class="app-footer extended-footer">
        <div class="container-fluid">
            <div class="row py-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-2">
                        <strong>Serikat Pekerja Kampus</strong>
                    </p>
                    <p class="mb-0 text-muted small">
                        Sistem Informasi Anggota SPK v1.0.0<br>
                        Bersatu untuk Kesejahteraan Pekerja Pendidikan Tinggi
                    </p>
                </div>
                <div class="col-md-6">
                    <?php if ($showLinks): ?>
                        <div class="footer-links text-md-end">
                            <a href="<?= base_url('about') ?>" class="text-muted me-3">Tentang</a>
                            <a href="<?= base_url('contact') ?>" class="text-muted me-3">Kontak</a>
                            <a href="<?= base_url('privacy') ?>" class="text-muted me-3">Privacy</a>
                            <a href="<?= base_url('terms') ?>" class="text-muted">Terms</a>
                        </div>
                    <?php endif; ?>
                    <p class="mb-0 text-muted small text-md-end mt-2">
                        &copy; <?= $currentYear ?> SPK. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

<?php else: ?>
    <!-- Standard Footer (Default) -->
    <footer class="app-footer standard-footer">
        <div class="container-fluid">
            <div class="row align-items-center py-3">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 text-muted">
                        &copy; <?= $currentYear ?> <strong>Serikat Pekerja Kampus</strong>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <?php if ($showLinks): ?>
                        <div class="footer-links d-inline-flex flex-wrap justify-content-center justify-content-md-end">
                            <a href="<?= base_url('about') ?>" class="text-muted mx-2">Tentang SPK</a>
                            <span class="text-muted d-none d-md-inline">|</span>
                            <a href="<?= base_url('contact') ?>" class="text-muted mx-2">Kontak</a>
                            <span class="text-muted d-none d-md-inline">|</span>
                            <a href="<?= base_url('help') ?>" class="text-muted mx-2">Bantuan</a>
                        </div>
                    <?php else: ?>
                        <p class="mb-0 text-muted small">
                            SI Anggota SPK v1.0.0
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

<style>
    /* Footer Base Styling */
    .app-footer {
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        margin-top: auto;
        font-size: 14px;
    }

    /* Minimal Footer */
    .minimal-footer {
        padding: 15px 0;
    }

    /* Standard Footer */
    .standard-footer {
        padding: 10px 0;
    }

    .standard-footer p {
        margin-bottom: 0;
        line-height: 1.5;
    }

    /* Extended Footer */
    .extended-footer {
        padding: 20px 0;
        background: #f8f9fa;
    }

    /* Footer Links */
    .footer-links a {
        text-decoration: none;
        font-size: 13px;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: #667eea !important;
        text-decoration: none;
    }

    /* Text Styling */
    .text-muted {
        color: #6c757d !important;
    }

    .app-footer strong {
        color: #495057;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .app-footer {
            font-size: 13px;
        }

        .standard-footer .col-md-6 {
            text-align: center !important;
        }

        .footer-links {
            margin-top: 8px;
        }

        .footer-links span {
            display: none;
        }
    }

    /* Dark Mode Support (Optional) */
    @media (prefers-color-scheme: dark) {
        .app-footer {
            background: #1a202c;
            border-top-color: #2d3748;
        }

        .extended-footer {
            background: #2d3748;
        }
    }
</style>

<!-- Footer Scripts (Optional) -->
<script>
    // Sticky footer functionality
    document.addEventListener('DOMContentLoaded', function() {
        const footer = document.querySelector('.app-footer');
        const appContent = document.querySelector('.app-content');

        if (footer && appContent) {
            // Calculate if content is shorter than viewport
            function adjustFooter() {
                const contentHeight = appContent.offsetHeight;
                const windowHeight = window.innerHeight;

                if (contentHeight < windowHeight - 200) {
                    footer.style.position = 'fixed';
                    footer.style.bottom = '0';
                    footer.style.width = '100%';
                    footer.style.left = '0';
                } else {
                    footer.style.position = 'relative';
                }
            }

            // Run on load and resize
            adjustFooter();
            window.addEventListener('resize', adjustFooter);
        }
    });

    // Back to top button (appears in footer on scroll)
    let backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="material-icons">arrow_upward</i>';
    backToTopButton.className = 'btn-back-to-top';
    backToTopButton.title = 'Kembali ke atas';
    backToTopButton.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 30px;
        z-index: 99;
        border: none;
        outline: none;
        background-color: #667eea;
        color: white;
        cursor: pointer;
        padding: 12px;
        border-radius: 50%;
        font-size: 18px;
        width: 50px;
        height: 50px;
        display: none;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    `;

    document.body.appendChild(backToTopButton);

    // Show button when user scrolls down
    window.addEventListener('scroll', function() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    // Scroll to top when button is clicked
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Hover effect
    backToTopButton.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#5568d3';
        this.style.transform = 'translateY(-5px)';
    });

    backToTopButton.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '#667eea';
        this.style.transform = 'translateY(0)';
    });
</script>