<?php

/**
 * View: Admin Dashboard
 * Controller: App\Controllers\Admin\DashboardController
 * Description: Comprehensive admin dashboard dengan statistics, charts, recent activities
 * 
 * Features:
 * - Statistics cards (members, complaints, forums, surveys)
 * - Interactive charts (growth, regional distribution, university type)
 * - Recent activities timeline
 * - Pending items quick access
 * - Quick actions menu
 * - Regional scope support untuk Koordinator Wilayah
 * - Real-time data updates (AJAX)
 * - Responsive design (mobile-first)
 * 
 * @package App\Views\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    /* Dashboard Wrapper */
    .dashboard-wrapper {
        padding: 24px;
        background: #f8f9fa;
        min-height: calc(100vh - 80px);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        color: white;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: white;
    }

    .page-header .subtitle {
        font-size: 16px;
        opacity: 0.95;
        margin: 0;
    }

    .page-header .scope-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
        backdrop-filter: blur(10px);
    }

    /* Stats Cards Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary {
        border-left-color: #667eea;
    }

    .stat-card.success {
        border-left-color: #48bb78;
    }

    .stat-card.warning {
        border-left-color: #f6ad55;
    }

    .stat-card.danger {
        border-left-color: #f56565;
    }

    .stat-card.info {
        border-left-color: #4299e1;
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    }

    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 13px;
        color: #718096;
        margin-bottom: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-description {
        font-size: 13px;
        color: #a0aec0;
        margin: 0;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        margin-top: 8px;
    }

    .stat-trend.up {
        background: #c6f6d5;
        color: #22543d;
    }

    .stat-trend.down {
        background: #fed7d7;
        color: #742a2a;
    }

    /* Charts Section */
    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .chart-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .chart-card-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .chart-card-header .chart-actions {
        display: flex;
        gap: 8px;
    }

    .chart-card-header .btn-sm {
        padding: 4px 12px;
        font-size: 12px;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    /* Activities & Pending Section */
    .content-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .content-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .content-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .content-card-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .content-card-header h3 i {
        color: #667eea;
    }

    /* Activities Timeline */
    .activities-timeline {
        position: relative;
    }

    .activity-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .activity-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 28px;
        bottom: -24px;
        width: 2px;
        background: #e2e8f0;
    }

    .activity-item:last-child::before {
        display: none;
    }

    .activity-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .activity-description {
        font-size: 13px;
        color: #718096;
        margin-bottom: 4px;
    }

    .activity-time {
        font-size: 12px;
        color: #a0aec0;
    }

    /* Pending Items */
    .pending-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .pending-item {
        padding: 16px;
        background: #f7fafc;
        border-radius: 8px;
        border-left: 4px solid #f6ad55;
        transition: all 0.3s ease;
    }

    .pending-item:hover {
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .pending-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .pending-item-title {
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
    }

    .pending-badge {
        background: #f6ad55;
        color: white;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .pending-item-description {
        font-size: 13px;
        color: #718096;
        margin-bottom: 8px;
    }

    .pending-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pending-item-time {
        font-size: 12px;
        color: #a0aec0;
    }

    .pending-item-action {
        padding: 4px 12px;
        font-size: 12px;
    }

    /* Quick Actions */
    .quick-actions {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 32px;
    }

    .quick-actions h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-actions h3 i {
        color: #667eea;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .action-button {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #f7fafc;
        border-radius: 10px;
        text-decoration: none;
        color: #2d3748;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .action-button:hover {
        background: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        color: #667eea;
        text-decoration: none;
    }

    .action-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .action-text {
        flex: 1;
    }

    .action-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .action-description {
        font-size: 12px;
        color: #718096;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Loading State */
    .loading-spinner {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.3em;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .charts-section {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .content-section {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .stat-value {
            font-size: 28px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="dashboard-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <h1>
            <i class="material-icons-outlined" style="vertical-align: middle; margin-right: 8px;">dashboard</i>
            Dashboard Admin
        </h1>
        <p class="subtitle">
            Selamat datang kembali, <?= esc($user->full_name ?? 'Admin') ?>!
            Berikut ringkasan sistem informasi SPK.
        </p>

        <?php if ($is_koordinator && $scope_data): ?>
            <div class="scope-badge">
                <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">location_on</i>
                Wilayah: <?= esc($scope_data['province_name']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Statistics Cards -->
    <div class="stats-grid animate-fade-in-up" style="animation-delay: 0.1s;">

        <!-- Total Members -->
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">people</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Anggota</div>
                <div class="stat-value"><?= number_format($stats['total_members'] ?? 0) ?></div>
                <p class="stat-description">Seluruh anggota terdaftar</p>
                <?php if (isset($stats['member_growth']) && $stats['member_growth'] > 0): ?>
                    <span class="stat-trend up">
                        <i class="material-icons-outlined" style="font-size: 16px;">trending_up</i>
                        +<?= number_format($stats['member_growth']) ?> bulan ini
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Members -->
        <div class="stat-card warning">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">pending</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Menunggu Approval</div>
                <div class="stat-value"><?= number_format($stats['pending_members'] ?? 0) ?></div>
                <p class="stat-description">Calon anggota baru</p>
                <?php if (($stats['pending_members'] ?? 0) > 0): ?>
                    <a href="<?= base_url('admin/members?status=pending') ?>" class="btn btn-sm btn-warning mt-2">
                        Review Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Members -->
        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">check_circle</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Anggota Aktif</div>
                <div class="stat-value"><?= number_format($stats['active_members'] ?? 0) ?></div>
                <p class="stat-description">Status aktif & terverifikasi</p>
                <?php
                $activePercentage = ($stats['total_members'] ?? 0) > 0
                    ? round((($stats['active_members'] ?? 0) / $stats['total_members']) * 100, 1)
                    : 0;
                ?>
                <small class="text-muted"><?= $activePercentage ?>% dari total</small>
            </div>
        </div>

        <!-- Suspended Members -->
        <div class="stat-card danger">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">block</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Anggota Suspend</div>
                <div class="stat-value"><?= number_format($stats['suspended_members'] ?? 0) ?></div>
                <p class="stat-description">Status tidak aktif</p>
            </div>
        </div>

        <!-- Complaints -->
        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">support_agent</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pengaduan Aktif</div>
                <div class="stat-value"><?= number_format($stats['open_complaints'] ?? 0) ?></div>
                <p class="stat-description">dari <?= number_format($stats['total_complaints'] ?? 0) ?> total</p>
                <?php if (($stats['open_complaints'] ?? 0) > 0): ?>
                    <a href="<?= base_url('admin/complaints') ?>" class="btn btn-sm btn-info mt-2">
                        Lihat Pengaduan
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Forum Threads -->
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">forum</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Thread Forum</div>
                <div class="stat-value"><?= number_format($stats['total_forums'] ?? 0) ?></div>
                <p class="stat-description"><?= number_format($stats['active_forums'] ?? 0) ?> aktif bulan ini</p>
            </div>
        </div>

        <!-- Surveys -->
        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">poll</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Survei Aktif</div>
                <div class="stat-value"><?= number_format($stats['active_surveys'] ?? 0) ?></div>
                <p class="stat-description">dari <?= number_format($stats['total_surveys'] ?? 0) ?> total survei</p>
            </div>
        </div>

        <!-- WA Groups (if koordinator or admin) -->
        <?php if (auth()->user()->can('wa_groups.view')): ?>
            <div class="stat-card warning">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="material-icons-outlined">groups</i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Grup WhatsApp</div>
                    <div class="stat-value"><?= number_format($stats['wa_groups'] ?? 0) ?></div>
                    <p class="stat-description">Grup komunikasi wilayah</p>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Charts Section -->
    <div class="charts-section animate-fade-in-up" style="animation-delay: 0.2s;">

        <!-- Member Growth Chart -->
        <div class="chart-card">
            <div class="chart-card-header">
                <h3>
                    <i class="material-icons-outlined">trending_up</i>
                    Pertumbuhan Anggota
                </h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshChart('memberGrowth')">
                        <i class="material-icons-outlined" style="font-size: 16px;">refresh</i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="memberGrowthChart"></canvas>
            </div>
        </div>

        <!-- Regional Distribution Chart -->
        <div class="chart-card">
            <div class="chart-card-header">
                <h3>
                    <i class="material-icons-outlined">location_on</i>
                    Distribusi Regional
                </h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshChart('regional')">
                        <i class="material-icons-outlined" style="font-size: 16px;">refresh</i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="regionalChart"></canvas>
            </div>
        </div>

        <!-- University Type Chart -->
        <div class="chart-card">
            <div class="chart-card-header">
                <h3>
                    <i class="material-icons-outlined">school</i>
                    Jenis Perguruan Tinggi
                </h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshChart('university')">
                        <i class="material-icons-outlined" style="font-size: 16px;">refresh</i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="universityChart"></canvas>
            </div>
        </div>

        <!-- Status Breakdown Chart -->
        <div class="chart-card">
            <div class="chart-card-header">
                <h3>
                    <i class="material-icons-outlined">pie_chart</i>
                    Status Keanggotaan
                </h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshChart('status')">
                        <i class="material-icons-outlined" style="font-size: 16px;">refresh</i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Content Section: Activities & Pending Items -->
    <div class="content-section animate-fade-in-up" style="animation-delay: 0.3s;">

        <!-- Recent Activities -->
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <i class="material-icons-outlined">history</i>
                    Aktivitas Terbaru
                </h3>
                <a href="<?= base_url('admin/audit-logs') ?>" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>

            <div class="activities-timeline">
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="material-icons-outlined" style="font-size: 14px;">
                                    <?= $activity['icon'] ?? 'circle' ?>
                                </i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= esc($activity['title']) ?></div>
                                <div class="activity-description"><?= esc($activity['description']) ?></div>
                                <div class="activity-time">
                                    <i class="material-icons-outlined" style="font-size: 12px; vertical-align: middle;">schedule</i>
                                    <?= esc($activity['time']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="material-icons-outlined">history</i>
                        <p>Belum ada aktivitas terbaru</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Items -->
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <i class="material-icons-outlined">pending_actions</i>
                    Memerlukan Perhatian
                </h3>
                <span class="badge badge-warning">
                    <?= count($pending_items ?? []) ?> item
                </span>
            </div>

            <div class="pending-list">
                <?php if (!empty($pending_items)): ?>
                    <?php foreach ($pending_items as $item): ?>
                        <div class="pending-item">
                            <div class="pending-item-header">
                                <div class="pending-item-title"><?= esc($item['title']) ?></div>
                                <span class="pending-badge"><?= esc($item['count']) ?></span>
                            </div>
                            <div class="pending-item-description">
                                <?= esc($item['description']) ?>
                            </div>
                            <div class="pending-item-footer">
                                <div class="pending-item-time">
                                    <i class="material-icons-outlined" style="font-size: 12px; vertical-align: middle;">schedule</i>
                                    <?= esc($item['time']) ?>
                                </div>
                                <a href="<?= esc($item['url']) ?>" class="btn btn-sm btn-primary pending-item-action">
                                    Tinjau
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="material-icons-outlined">check_circle</i>
                        <p>Tidak ada item yang memerlukan perhatian</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="quick-actions animate-fade-in-up" style="animation-delay: 0.4s;">
        <h3>
            <i class="material-icons-outlined">flash_on</i>
            Aksi Cepat
        </h3>

        <div class="actions-grid">

            <?php if (auth()->user()->can('member.create')): ?>
                <a href="<?= base_url('admin/members/create') ?>" class="action-button">
                    <div class="action-icon">
                        <i class="material-icons-outlined">person_add</i>
                    </div>
                    <div class="action-text">
                        <div class="action-title">Tambah Anggota</div>
                        <div class="action-description">Daftarkan anggota baru</div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (auth()->user()->can('member.import')): ?>
                <a href="<?= base_url('admin/bulk-import') ?>" class="action-button">
                    <div class="action-icon">
                        <i class="material-icons-outlined">upload_file</i>
                    </div>
                    <div class="action-text">
                        <div class="action-title">Import Anggota</div>
                        <div class="action-description">Upload data massal</div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (auth()->user()->can('member.view')): ?>
                <a href="<?= base_url('admin/members') ?>" class="action-button">
                    <div class="action-icon">
                        <i class="material-icons-outlined">people</i>
                    </div>
                    <div class="action-text">
                        <div class="action-title">Kelola Anggota</div>
                        <div class="action-description">Lihat & kelola anggota</div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (auth()->user()->can('survey.create')): ?>
                <a href="<?= base_url('admin/surveys/create') ?>" class="action-button">
                    <div class="action-icon">
                        <i class="material-icons-outlined">poll</i>
                    </div>
                    <div class="action-text">
                        <div class="action-title">Buat Survei</div>
                        <div class="action-description">Survei anggota baru</div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (auth()->user()->can('content.create')): ?>
                <a href="<?= base_url('admin/content/posts/create') ?>" class="action-button">
                    <div class="action-icon">
                        <i class="material-icons-outlined">article</i>
                    </div>
                    <div class="action-text">
                        <div class="action-title">Tulis Artikel</div>
                        <div class="action-description">Publikasi konten baru</div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (auth()->user()->can('statistics.view')): ?>
                <a href="<?= base_url('admin/statistics') ?>" class="action-button">
                    <div class="action-icon">
                        <i class="material-icons-outlined">analytics</i>
                    </div>
                    <div class="action-text">
                        <div class="action-title">Statistik Lengkap</div>
                        <div class="action-description">Analytics & reports</div>
                    </div>
                </a>
            <?php endif; ?>

        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js -->
<script src="<?= base_url('assets/plugins/chart.js/chart.min.js') ?>"></script>

<script>
    // Chart.js Configuration
    Chart.defaults.font.family = 'Poppins, sans-serif';
    Chart.defaults.color = '#718096';

    // Chart Data from Controller
    const chartData = <?= json_encode($chart_data ?? []) ?>;

    // Member Growth Chart (Line)
    const memberGrowthCtx = document.getElementById('memberGrowthChart');
    if (memberGrowthCtx) {
        new Chart(memberGrowthCtx, {
            type: 'line',
            data: {
                labels: chartData.member_growth?.labels || [],
                datasets: [{
                    label: 'Jumlah Anggota',
                    data: chartData.member_growth?.data || [],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 13
                        },
                        borderColor: '#667eea',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Regional Distribution Chart (Doughnut)
    const regionalCtx = document.getElementById('regionalChart');
    if (regionalCtx) {
        new Chart(regionalCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.regional_distribution?.labels || [],
                datasets: [{
                    data: chartData.regional_distribution?.data || [],
                    backgroundColor: [
                        '#667eea',
                        '#48bb78',
                        '#f6ad55',
                        '#f56565',
                        '#4299e1',
                        '#9f7aea',
                        '#ed64a6',
                        '#38b2ac'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                }
            }
        });
    }

    // University Type Chart (Bar)
    const universityCtx = document.getElementById('universityChart');
    if (universityCtx) {
        new Chart(universityCtx, {
            type: 'bar',
            data: {
                labels: chartData.university_type?.labels || [],
                datasets: [{
                    label: 'Jumlah Anggota',
                    data: chartData.university_type?.data || [],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(72, 187, 120, 0.8)',
                        'rgba(246, 173, 85, 0.8)',
                        'rgba(245, 101, 101, 0.8)'
                    ],
                    borderColor: [
                        '#667eea',
                        '#48bb78',
                        '#f6ad55',
                        '#f56565'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Status Breakdown Chart (Pie)
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: chartData.status_breakdown?.labels || [],
                datasets: [{
                    data: chartData.status_breakdown?.data || [],
                    backgroundColor: [
                        '#48bb78',
                        '#f6ad55',
                        '#f56565',
                        '#a0aec0'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                }
            }
        });
    }

    // Refresh Chart Function
    function refreshChart(type) {
        // Show loading
        const btn = event.target.closest('button');
        const icon = btn.querySelector('i');
        icon.style.animation = 'spin 1s linear infinite';

        // Simulate refresh (in production, this would be an AJAX call)
        setTimeout(() => {
            icon.style.animation = '';

            // You can add AJAX call here to fetch new data
            // Example:
            // fetch('<?= base_url('admin/dashboard/charts') ?>')
            //     .then(response => response.json())
            //     .then(data => {
            //         // Update chart with new data
            //     });
        }, 1000);
    }

    // Add spin animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Auto-refresh statistics every 5 minutes (optional)
    // setInterval(() => {
    //     location.reload();
    // }, 300000);
</script>
<?= $this->endSection() ?>