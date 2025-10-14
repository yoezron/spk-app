<?php

/**
 * View: Member Dashboard
 * Controller: Member\DashboardController
 * Description: Dashboard anggota dengan statistics, notifications, dan quick actions
 * 
 * Features:
 * - Welcome header dengan member info
 * - Statistics cards (total anggota, pending, survey aktif, dll)
 * - Quick action buttons
 * - Recent activities timeline
 * - Notifications panel
 * - Important announcements
 * - Responsive grid layout
 * - Icons & animations
 * 
 * @package App\Views\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .welcome-section .container-fluid {
        position: relative;
        z-index: 1;
    }

    .welcome-section h2 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .welcome-section p {
        font-size: 16px;
        opacity: 0.95;
        margin-bottom: 0;
    }

    .member-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 16px;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.primary::before {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.success::before {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }

    .stat-card.warning::before {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    }

    .stat-card.info::before {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        color: white;
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
    }

    .stat-label {
        font-size: 14px;
        color: #718096;
        margin-bottom: 8px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .stat-change {
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.positive {
        color: #48bb78;
    }

    .stat-change.negative {
        color: #f56565;
    }

    /* Quick Actions */
    .quick-actions {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .quick-actions h4 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #2d3748;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 24px;
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        color: #2d3748;
    }

    .action-btn:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .action-btn i {
        font-size: 36px;
    }

    .action-btn span {
        font-size: 14px;
        font-weight: 600;
        text-align: center;
    }

    /* Recent Activities */
    .activity-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .activity-card h4 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #2d3748;
    }

    .activity-timeline {
        position: relative;
        padding-left: 40px;
    }

    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .activity-item {
        position: relative;
        padding-bottom: 24px;
    }

    .activity-item:last-child {
        padding-bottom: 0;
    }

    .activity-icon {
        position: absolute;
        left: -28px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #667eea;
    }

    .activity-content {
        background: #f7fafc;
        padding: 16px;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    .activity-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .activity-description {
        font-size: 14px;
        color: #718096;
        margin-bottom: 8px;
    }

    .activity-time {
        font-size: 12px;
        color: #a0aec0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Notifications Panel */
    .notifications-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .notifications-card h4 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #2d3748;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .notification-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        background: #f7fafc;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .notification-item:hover {
        background: #edf2f7;
        transform: translateX(4px);
    }

    .notification-item.unread {
        background: #e6f2ff;
        border-left: 3px solid #4299e1;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .notification-icon.info {
        background: #bee3f8;
        color: #2c5282;
    }

    .notification-icon.success {
        background: #c6f6d5;
        color: #22543d;
    }

    .notification-icon.warning {
        background: #feebc8;
        color: #7c2d12;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .notification-text {
        font-size: 14px;
        color: #718096;
        margin-bottom: 4px;
    }

    .notification-time {
        font-size: 12px;
        color: #a0aec0;
    }

    .view-all-link {
        text-align: center;
        padding-top: 12px;
    }

    .view-all-link a {
        color: #667eea;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .view-all-link a:hover {
        text-decoration: underline;
    }

    /* Announcements */
    .announcement-card {
        background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        border: 2px solid #f59e0b;
    }

    .announcement-card h4 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 700;
        color: #78350f;
        margin-bottom: 12px;
    }

    .announcement-card p {
        color: #92400e;
        margin: 0;
        line-height: 1.6;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .empty-state p {
        font-size: 16px;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .action-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 767px) {
        .welcome-section {
            padding: 30px 20px;
        }

        .welcome-section h2 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .action-grid {
            grid-template-columns: 1fr;
        }

        .stat-value {
            font-size: 28px;
        }

        .activity-timeline {
            padding-left: 30px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Welcome Section -->
<div class="welcome-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="bi bi-hand-wave"></i>
                    Selamat Datang, <?= esc($member->full_name ?? $user->username) ?>!
                </h2>
                <p>Ini adalah dashboard Anda di Serikat Pekerja Kampus. Kelola profil, akses informasi, dan berpartisipasi dalam kegiatan serikat.</p>

                <?php if (!empty($member->member_number)): ?>
                    <div class="member-info-badge">
                        <i class="bi bi-person-badge"></i>
                        <span>Nomor Anggota: <strong><?= esc($member->member_number) ?></strong></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="text-white">
                    <i class="bi bi-calendar3" style="font-size: 24px;"></i>
                    <div class="mt-2">
                        <strong><?= date('d F Y') ?></strong><br>
                        <span style="opacity: 0.9;"><?= date('l, H:i') ?> WIB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Important Announcement -->
<?php if (!empty($announcement)): ?>
    <div class="announcement-card">
        <h4>
            <i class="bi bi-megaphone-fill"></i>
            Pengumuman Penting
        </h4>
        <p><?= esc($announcement) ?></p>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-label">Total Anggota</div>
        <div class="stat-value"><?= number_format($statistics['total_members'] ?? 0) ?></div>
        <?php if (!empty($statistics['new_members_this_month'])): ?>
            <div class="stat-change positive">
                <i class="bi bi-arrow-up"></i>
                +<?= $statistics['new_members_this_month'] ?> bulan ini
            </div>
        <?php endif; ?>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-label">Calon Anggota</div>
        <div class="stat-value"><?= number_format($statistics['pending_members'] ?? 0) ?></div>
        <div class="stat-change">Menunggu verifikasi</div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="bi bi-clipboard-check"></i>
        </div>
        <div class="stat-label">Survey Aktif</div>
        <div class="stat-value"><?= number_format($statistics['active_surveys'] ?? 0) ?></div>
        <div class="stat-change">Dapat diikuti</div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">
            <i class="bi bi-chat-left-dots-fill"></i>
        </div>
        <div class="stat-label">Forum Diskusi</div>
        <div class="stat-value"><?= number_format($statistics['total_threads'] ?? 0) ?></div>
        <div class="stat-change">Total thread</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <h4><i class="bi bi-lightning-charge-fill"></i> Aksi Cepat</h4>
    <div class="action-grid">
        <a href="<?= base_url('member/profile') ?>" class="action-btn">
            <i class="bi bi-person-circle"></i>
            <span>Lihat Profil</span>
        </a>
        <a href="<?= base_url('member/card') ?>" class="action-btn">
            <i class="bi bi-credit-card-2-front"></i>
            <span>Kartu Anggota</span>
        </a>
        <a href="<?= base_url('member/forum') ?>" class="action-btn">
            <i class="bi bi-chat-square-text"></i>
            <span>Forum Diskusi</span>
        </a>
        <a href="<?= base_url('member/survey') ?>" class="action-btn">
            <i class="bi bi-clipboard-data"></i>
            <span>Ikuti Survey</span>
        </a>
        <a href="<?= base_url('member/complaint') ?>" class="action-btn">
            <i class="bi bi-exclamation-circle"></i>
            <span>Buat Pengaduan</span>
        </a>
        <a href="<?= base_url('member/profile/edit') ?>" class="action-btn">
            <i class="bi bi-pencil-square"></i>
            <span>Edit Profil</span>
        </a>
    </div>
</div>

<div class="row">
    <!-- Recent Activities -->
    <div class="col-lg-8">
        <div class="activity-card">
            <h4><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h4>

            <?php if (!empty($recentActivities) && is_array($recentActivities)): ?>
                <div class="activity-timeline">
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="bi bi-<?= esc($activity['icon'] ?? 'circle-fill') ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= esc($activity['title']) ?></div>
                                <div class="activity-description"><?= esc($activity['description']) ?></div>
                                <div class="activity-time">
                                    <i class="bi bi-clock"></i>
                                    <?= date('d M Y, H:i', strtotime($activity['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada aktivitas terbaru</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notifications -->
    <div class="col-lg-4">
        <div class="notifications-card">
            <h4>
                <span><i class="bi bi-bell-fill"></i> Notifikasi</span>
                <?php if (!empty($unreadCount)): ?>
                    <span class="badge bg-danger rounded-pill"><?= $unreadCount ?></span>
                <?php endif; ?>
            </h4>

            <?php if (!empty($notifications) && is_array($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>"
                        onclick="window.location.href='<?= base_url('member/notifications/' . $notification['id']) ?>'">
                        <div class="notification-icon <?= esc($notification['type'] ?? 'info') ?>">
                            <i class="bi bi-<?= esc($notification['icon'] ?? 'bell') ?>"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?= esc($notification['title']) ?></div>
                            <div class="notification-text"><?= esc($notification['message']) ?></div>
                            <div class="notification-time">
                                <i class="bi bi-clock"></i>
                                <?= date('d M Y, H:i', strtotime($notification['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="view-all-link">
                    <a href="<?= base_url('member/notifications') ?>">
                        Lihat Semua Notifikasi
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <p>Tidak ada notifikasi baru</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-refresh statistics every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);

    // Notification click handler
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.addEventListener('click', function() {
            // Mark as read via AJAX if needed
            const notificationId = this.dataset.notificationId;
            if (notificationId) {
                fetch('<?= base_url('member/notifications/mark-read/') ?>' + notificationId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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

    document.querySelectorAll('.stat-card, .activity-item, .notification-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>
<?= $this->endSection() ?>