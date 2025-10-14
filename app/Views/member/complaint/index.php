<?php

/**
 * View: Complaint/Ticket Index - Member Area
 * Controller: Member\ComplaintController@index
 * Description: Menampilkan daftar pengaduan/tiket yang dibuat oleh anggota
 * 
 * Features:
 * - Stats cards (total, by status)
 * - Filter by status
 * - Complaint cards dengan status badges
 * - Priority indicators
 * - Create new complaint button
 * - Pagination
 * - Empty state
 * - Responsive layout
 * 
 * @package App\Views\Member\Complaint
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
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .page-header-content {
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header p {
        opacity: 0.9;
        margin-bottom: 0;
        font-size: 15px;
    }

    /* Stats Cards */
    .stats-row {
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.active {
        border: 2px solid #667eea;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-icon.open {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .stat-icon.progress {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .stat-icon.resolved {
        background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        color: white;
    }

    .stat-icon.closed {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: white;
    }

    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
        line-height: 1;
    }

    .stat-info p {
        margin: 0;
        color: #6c757d;
        font-size: 13px;
        margin-top: 5px;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .filter-title {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 12px;
    }

    /* Complaint Card */
    .complaint-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-left: 4px solid transparent;
    }

    .complaint-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .complaint-card.priority-low::before {
        background: #28a745;
    }

    .complaint-card.priority-medium::before {
        background: #ffc107;
    }

    .complaint-card.priority-high::before {
        background: #fd7e14;
    }

    .complaint-card.priority-urgent::before {
        background: #dc3545;
    }

    .complaint-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .complaint-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .complaint-title-section {
        flex: 1;
        min-width: 0;
    }

    .complaint-ticket-number {
        font-size: 13px;
        color: #667eea;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .complaint-subject {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: block;
        text-decoration: none;
        transition: color 0.2s;
    }

    .complaint-subject:hover {
        color: #667eea;
    }

    .complaint-badges {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
        margin-left: 15px;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-badge.open {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-badge.in_progress {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.resolved {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.closed {
        background: #e2e3e5;
        color: #383d41;
    }

    .priority-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .priority-badge.low {
        background: #d4edda;
        color: #155724;
    }

    .priority-badge.medium {
        background: #fff3cd;
        color: #856404;
    }

    .priority-badge.high {
        background: #f8d7da;
        color: #721c24;
    }

    .priority-badge.urgent {
        background: #dc3545;
        color: white;
    }

    .complaint-description {
        color: #6c757d;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .complaint-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #6c757d;
        padding-top: 15px;
        border-top: 1px solid #f1f3f5;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-item i {
        color: #667eea;
        font-size: 16px;
    }

    .complaint-category {
        background: #e7f3ff;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 64px;
        color: #e3e6f0;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #6c757d;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 20px;
        font-size: 14px;
    }

    /* Info Box */
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box p {
        color: #1976d2;
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
    }

    .info-box strong {
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
        }

        .page-header-content {
            flex-direction: column;
            gap: 15px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .stat-card {
            padding: 18px;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            font-size: 20px;
        }

        .stat-info h3 {
            font-size: 24px;
        }

        .complaint-card {
            padding: 20px;
        }

        .complaint-header {
            flex-direction: column;
        }

        .complaint-badges {
            margin-left: 0;
            margin-top: 10px;
        }

        .complaint-subject {
            font-size: 16px;
        }

        .complaint-meta {
            gap: 12px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1><i class="bi bi-headset"></i> <?= esc($pageTitle) ?></h1>
            <p>Kelola dan pantau status pengaduan Anda</p>
        </div>
        <a href="<?= base_url('member/complaint/create') ?>" class="btn btn-light btn-lg">
            <i class="bi bi-plus-circle"></i> Buat Pengaduan Baru
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Info Box -->
<div class="info-box">
    <p>
        <i class="bi bi-info-circle"></i>
        <strong>Butuh bantuan?</strong> Laporkan masalah atau ajukan pertanyaan melalui sistem pengaduan. Tim kami akan merespons dalam 1-2 hari kerja.
    </p>
</div>

<!-- Stats Cards -->
<div class="stats-row">
    <div class="row g-3">
        <div class="col-md-3 col-6">
            <div class="stat-card <?= empty($currentStatus) ? 'active' : '' ?>"
                onclick="window.location.href='<?= base_url('member/complaint') ?>'">
                <div class="stat-icon total">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['total'] ?? 0) ?></h3>
                    <p>Total Pengaduan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card <?= $currentStatus === 'open' ? 'active' : '' ?>"
                onclick="window.location.href='<?= base_url('member/complaint?status=open') ?>'">
                <div class="stat-icon open">
                    <i class="bi bi-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['open'] ?? 0) ?></h3>
                    <p>Baru</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card <?= $currentStatus === 'in_progress' ? 'active' : '' ?>"
                onclick="window.location.href='<?= base_url('member/complaint?status=in_progress') ?>'">
                <div class="stat-icon progress">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['in_progress'] ?? 0) ?></h3>
                    <p>Diproses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card <?= $currentStatus === 'resolved' ? 'active' : '' ?>"
                onclick="window.location.href='<?= base_url('member/complaint?status=resolved') ?>'">
                <div class="stat-icon resolved">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['resolved'] ?? 0) ?></h3>
                    <p>Selesai</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<?php if (!empty($tickets)): ?>
    <div class="filter-section">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label class="filter-title">Filter berdasarkan Status</label>
                <select class="form-select" id="statusFilter" onchange="filterByStatus(this.value)">
                    <option value="">Semua Status</option>
                    <option value="open" <?= $currentStatus === 'open' ? 'selected' : '' ?>>Baru</option>
                    <option value="in_progress" <?= $currentStatus === 'in_progress' ? 'selected' : '' ?>>Diproses</option>
                    <option value="resolved" <?= $currentStatus === 'resolved' ? 'selected' : '' ?>>Selesai</option>
                    <option value="closed" <?= $currentStatus === 'closed' ? 'selected' : '' ?>>Ditutup</option>
                </select>
            </div>
            <div class="col-md-8 text-end mt-3 mt-md-0">
                <small class="text-muted">
                    Menampilkan <?= count($tickets) ?> dari <?= number_format($total) ?> pengaduan
                </small>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Complaints List -->
<?php if (!empty($tickets)): ?>
    <?php foreach ($tickets as $ticket): ?>
        <div class="complaint-card priority-<?= esc($ticket->priority ?? 'medium') ?>">
            <div class="complaint-header">
                <div class="complaint-title-section">
                    <div class="complaint-ticket-number">
                        <i class="bi bi-ticket-detailed"></i>
                        #<?= esc($ticket->ticket_number ?? $ticket->id) ?>
                    </div>
                    <a href="<?= base_url('member/complaint/detail/' . $ticket->id) ?>"
                        class="complaint-subject">
                        <?= esc($ticket->subject) ?>
                    </a>
                </div>

                <div class="complaint-badges">
                    <span class="status-badge <?= esc($ticket->status) ?>">
                        <?php
                        $statusLabels = [
                            'open' => 'Baru',
                            'in_progress' => 'Diproses',
                            'resolved' => 'Selesai',
                            'closed' => 'Ditutup'
                        ];
                        echo $statusLabels[$ticket->status] ?? ucfirst($ticket->status);
                        ?>
                    </span>

                    <?php if (!empty($ticket->priority) && $ticket->priority !== 'medium'): ?>
                        <span class="priority-badge <?= esc($ticket->priority) ?>">
                            <?php
                            $priorityLabels = [
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'urgent' => 'Mendesak'
                            ];
                            echo $priorityLabels[$ticket->priority] ?? ucfirst($ticket->priority);
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($ticket->description)): ?>
                <div class="complaint-description">
                    <?= esc(strip_tags($ticket->description)) ?>
                </div>
            <?php endif; ?>

            <div class="complaint-meta">
                <?php if (!empty($ticket->category)): ?>
                    <div class="meta-item">
                        <span class="complaint-category">
                            <i class="bi bi-tag"></i>
                            <?= esc($ticket->category) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="meta-item">
                    <i class="bi bi-calendar"></i>
                    <span><?= date('d M Y', strtotime($ticket->created_at)) ?></span>
                </div>

                <?php if (!empty($ticket->replies_count)): ?>
                    <div class="meta-item">
                        <i class="bi bi-chat-dots"></i>
                        <span><?= $ticket->replies_count ?> balasan</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($ticket->last_reply_at)): ?>
                    <div class="meta-item">
                        <i class="bi bi-clock-history"></i>
                        <span>Balasan terakhir: <?= time_ago($ticket->last_reply_at) ?></span>
                    </div>
                <?php endif; ?>

                <div class="meta-item ms-auto">
                    <a href="<?= base_url('member/complaint/detail/' . $ticket->id) ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if (!empty($pager)): ?>
        <div class="d-flex justify-content-center mt-4">
            <?= $pager->links('default', 'bootstrap_pagination') ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Empty State -->
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h4>
            <?php if (!empty($currentStatus)): ?>
                Tidak Ada Pengaduan dengan Status Ini
            <?php else: ?>
                Belum Ada Pengaduan
            <?php endif; ?>
        </h4>
        <p>
            <?php if (!empty($currentStatus)): ?>
                Tidak ada pengaduan dengan status yang Anda pilih.
            <?php else: ?>
                Anda belum pernah membuat pengaduan. Mulai dengan membuat pengaduan pertama Anda.
            <?php endif; ?>
        </p>
        <?php if (empty($currentStatus)): ?>
            <a href="<?= base_url('member/complaint/create') ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle"></i> Buat Pengaduan Pertama
            </a>
        <?php else: ?>
            <a href="<?= base_url('member/complaint') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Lihat Semua Pengaduan
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Filter by status function
    function filterByStatus(status) {
        if (status) {
            window.location.href = '<?= base_url('member/complaint') ?>?status=' + status;
        } else {
            window.location.href = '<?= base_url('member/complaint') ?>';
        }
    }

    $(document).ready(function() {
        // Animation on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });

        // Observe all complaint cards
        document.querySelectorAll('.complaint-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });

        // Observe stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    });

    // Helper function for time ago (if not already in helpers)
    function timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        const intervals = {
            tahun: 31536000,
            bulan: 2592000,
            minggu: 604800,
            hari: 86400,
            jam: 3600,
            menit: 60
        };

        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return `${interval} ${unit} yang lalu`;
            }
        }

        return 'Baru saja';
    }
</script>
<?= $this->endSection() ?>