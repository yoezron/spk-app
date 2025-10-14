<?php

/**
 * View: Member Card
 * Controller: Member\CardController
 * Description: Digital member card dengan QR code verification
 * 
 * Features:
 * - Digital card preview (front & back design)
 * - QR code display untuk verification
 * - Member information display
 * - Download PDF button
 * - Print card button
 * - Card validity status
 * - Beautiful gradient card design
 * - Responsive layout
 * - Card flip animation
 * 
 * @package App\Views\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 30px;
    }

    .page-header h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header p {
        opacity: 0.95;
        margin: 0;
    }

    /* Card Container */
    .card-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    .action-buttons .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-outline-primary {
        background: white;
        border: 2px solid #667eea;
        color: #667eea;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    /* Member Card */
    .member-card-wrapper {
        perspective: 1500px;
        margin-bottom: 40px;
    }

    .member-card {
        width: 100%;
        max-width: 600px;
        aspect-ratio: 1.586;
        margin: 0 auto;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.8s cubic-bezier(0.4, 0.2, 0.2, 1);
        cursor: pointer;
    }

    .member-card.flipped {
        transform: rotateY(180deg);
    }

    .card-face {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    /* Card Front */
    .card-front {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card-front::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .card-header {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .card-logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .card-logo-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .card-logo-text h3 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .card-logo-text p {
        font-size: 12px;
        margin: 0;
        opacity: 0.9;
    }

    .card-chip {
        width: 50px;
        height: 40px;
        background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        border-radius: 8px;
        position: relative;
        overflow: hidden;
    }

    .card-chip::before,
    .card-chip::after {
        content: '';
        position: absolute;
        background: rgba(0, 0, 0, 0.1);
    }

    .card-chip::before {
        width: 60%;
        height: 40%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border-radius: 4px;
    }

    .card-member-info {
        position: relative;
        z-index: 2;
    }

    .card-member-number {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 2px;
        margin-bottom: 20px;
        font-family: 'Courier New', monospace;
    }

    .card-member-name {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .card-member-details {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        opacity: 0.95;
    }

    .card-detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .card-detail-label {
        font-size: 11px;
        opacity: 0.8;
        text-transform: uppercase;
    }

    .card-detail-value {
        font-weight: 600;
    }

    /* Card Back */
    .card-back {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        padding: 40px;
        color: white;
        transform: rotateY(180deg);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .card-qr-section {
        background: white;
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 20px;
    }

    .card-qr-section img {
        width: 200px;
        height: 200px;
        display: block;
    }

    .card-qr-info {
        margin-bottom: 20px;
    }

    .card-qr-info h4 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .card-qr-info p {
        font-size: 13px;
        opacity: 0.95;
        margin: 0;
    }

    .card-validity {
        background: rgba(255, 255, 255, 0.2);
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
    }

    /* Flip Hint */
    .flip-hint {
        text-align: center;
        margin-top: 20px;
        color: #718096;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .flip-hint i {
        font-size: 18px;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateX(0);
        }

        50% {
            transform: translateX(10px);
        }
    }

    /* Card Info */
    .card-info {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .card-info h4 {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-item i {
        font-size: 24px;
        color: #667eea;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        font-size: 13px;
        color: #718096;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .info-value {
        font-size: 16px;
        color: #2d3748;
        font-weight: 600;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.pending {
        background: #feebc8;
        color: #7c2d12;
    }

    .status-badge.expired {
        background: #fed7d7;
        color: #742a2a;
    }

    /* Instructions */
    .instructions-card {
        background: #f7fafc;
        border-radius: 12px;
        padding: 30px;
        border-left: 4px solid #667eea;
    }

    .instructions-card h4 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .instructions-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .instructions-list li {
        padding: 12px 0;
        padding-left: 36px;
        position: relative;
        color: #4a5568;
        font-size: 14px;
        line-height: 1.6;
    }

    .instructions-list li::before {
        content: attr(data-number);
        position: absolute;
        left: 0;
        top: 12px;
        width: 24px;
        height: 24px;
        background: #667eea;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .page-header {
            padding: 20px;
        }

        .page-header h2 {
            font-size: 24px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
            justify-content: center;
        }

        .card-front,
        .card-back {
            padding: 30px 24px;
        }

        .card-member-number {
            font-size: 20px;
        }

        .card-member-name {
            font-size: 18px;
        }

        .card-qr-section img {
            width: 160px;
            height: 160px;
        }

        .card-info,
        .instructions-card {
            padding: 20px;
        }

        .card-info-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }

        .member-card-wrapper,
        .member-card-wrapper * {
            visibility: visible;
        }

        .member-card-wrapper {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .page-header,
        .action-buttons,
        .flip-hint,
        .card-info,
        .instructions-card {
            display: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="bi bi-credit-card-2-front"></i> Kartu Anggota Digital</h2>
    <p>Kartu identitas digital Anda sebagai anggota Serikat Pekerja Kampus</p>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<div class="card-container">
    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="<?= base_url('member/card/download') ?>" class="btn btn-primary" target="_blank">
            <i class="bi bi-download"></i>
            Download PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer"></i>
            Cetak Kartu
        </button>
        <a href="<?= base_url('member/profile') ?>" class="btn btn-outline-primary">
            <i class="bi bi-person-circle"></i>
            Lihat Profil
        </a>
    </div>

    <!-- Member Card -->
    <div class="member-card-wrapper">
        <div class="member-card" id="memberCard">
            <!-- Card Front -->
            <div class="card-face card-front">
                <div class="card-header">
                    <div class="card-logo">
                        <div class="card-logo-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="card-logo-text">
                            <h3>SPK</h3>
                            <p>Serikat Pekerja Kampus</p>
                        </div>
                    </div>
                    <div class="card-chip"></div>
                </div>

                <div class="card-member-info">
                    <div class="card-member-number">
                        <?= esc($member->member_number ?? '---- ---- ----') ?>
                    </div>

                    <div class="card-member-name">
                        <?= esc($member->full_name) ?>
                    </div>

                    <div class="card-member-details">
                        <div class="card-detail-item">
                            <span class="card-detail-label">Wilayah</span>
                            <span class="card-detail-value"><?= esc($province->name ?? '-') ?></span>
                        </div>
                        <div class="card-detail-item">
                            <span class="card-detail-label">Kampus</span>
                            <span class="card-detail-value">
                                <?= !empty($university->name) ? (strlen($university->name) > 20 ? substr($university->name, 0, 20) . '...' : $university->name) : '-' ?>
                            </span>
                        </div>
                        <div class="card-detail-item">
                            <span class="card-detail-label">Bergabung</span>
                            <span class="card-detail-value"><?= date('Y', strtotime($user->created_at)) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Back -->
            <div class="card-face card-back">
                <div class="card-qr-section">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($qrCodeData ?? base_url('verify-card?token=' . ($verificationToken ?? ''))) ?>"
                        alt="QR Code Verification">
                </div>

                <div class="card-qr-info">
                    <h4>Verifikasi Keanggotaan</h4>
                    <p>Scan QR code ini untuk memverifikasi<br>keaslian kartu anggota</p>
                </div>

                <div class="card-validity">
                    <?php if (!empty($member->card_expiry)): ?>
                        <i class="bi bi-calendar-check"></i>
                        Berlaku s/d <?= date('d M Y', strtotime($member->card_expiry)) ?>
                    <?php else: ?>
                        <i class="bi bi-infinity"></i>
                        Masa Berlaku Tidak Terbatas
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Flip Hint -->
    <div class="flip-hint">
        <i class="bi bi-arrow-left-right"></i>
        <span>Klik kartu untuk melihat QR code</span>
    </div>

    <!-- Card Information -->
    <div class="card-info">
        <h4>
            <i class="bi bi-info-circle-fill"></i>
            Informasi Kartu
        </h4>

        <div class="card-info-grid">
            <div class="info-item">
                <i class="bi bi-person-badge"></i>
                <div class="info-content">
                    <div class="info-label">Nomor Anggota</div>
                    <div class="info-value"><?= esc($member->member_number ?? '-') ?></div>
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-person-circle"></i>
                <div class="info-content">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value"><?= esc($member->full_name) ?></div>
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-shield-check"></i>
                <div class="info-content">
                    <div class="info-label">Status Keanggotaan</div>
                    <div class="info-value">
                        <span class="status-badge <?= $member->membership_status === 'active' ? 'active' : ($member->membership_status === 'calon_anggota' ? 'pending' : 'expired') ?>">
                            <i class="bi bi-<?= $member->membership_status === 'active' ? 'check-circle' : ($member->membership_status === 'calon_anggota' ? 'hourglass-split' : 'x-circle') ?>"></i>
                            <?= $member->membership_status === 'active' ? 'Aktif' : ($member->membership_status === 'calon_anggota' ? 'Calon Anggota' : 'Tidak Aktif') ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-calendar-event"></i>
                <div class="info-content">
                    <div class="info-label">Tanggal Bergabung</div>
                    <div class="info-value"><?= date('d F Y', strtotime($user->created_at)) ?></div>
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-geo-alt-fill"></i>
                <div class="info-content">
                    <div class="info-label">Wilayah</div>
                    <div class="info-value"><?= esc($province->name ?? '-') ?></div>
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-building"></i>
                <div class="info-content">
                    <div class="info-label">Kampus</div>
                    <div class="info-value"><?= esc($university->name ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="instructions-card">
        <h4>
            <i class="bi bi-lightbulb-fill"></i>
            Cara Menggunakan Kartu
        </h4>
        <ul class="instructions-list">
            <li data-number="1">
                <strong>Download PDF:</strong> Klik tombol "Download PDF" untuk mengunduh kartu dalam format PDF yang dapat dicetak.
            </li>
            <li data-number="2">
                <strong>Cetak Kartu:</strong> Cetak kartu menggunakan kertas berkualitas baik (sebaiknya PVC card atau kertas tebal).
            </li>
            <li data-number="3">
                <strong>Verifikasi QR Code:</strong> QR code di bagian belakang kartu dapat di-scan untuk memverifikasi keaslian kartu.
            </li>
            <li data-number="4">
                <strong>Gunakan Sebagai Identitas:</strong> Gunakan kartu ini sebagai identitas resmi Anda di kegiatan serikat.
            </li>
            <li data-number="5">
                <strong>Perbarui Data:</strong> Jika ada perubahan data, segera update profil Anda agar kartu tetap valid.
            </li>
        </ul>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Card flip functionality
    document.getElementById('memberCard').addEventListener('click', function() {
        this.classList.toggle('flipped');
    });

    // Animation on load
    window.addEventListener('load', function() {
        const card = document.getElementById('memberCard');
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px) rotateX(-10deg)';

        setTimeout(() => {
            card.style.transition = 'all 1s cubic-bezier(0.4, 0.2, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0) rotateX(0)';
        }, 100);
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

    document.querySelectorAll('.card-info, .instructions-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>
<?= $this->endSection() ?>