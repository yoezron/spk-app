<?php

/**
 * View: Complaint Detail - Member Area
 * Controller: Member\ComplaintController@show
 * Description: Menampilkan detail pengaduan dengan timeline replies
 * 
 * Features:
 * - Ticket header dengan status & priority badges
 * - Original message display
 * - Replies timeline dengan staff badge
 * - Reply form (if not closed)
 * - Attachment display
 * - Status history
 * - Responsive timeline layout
 * 
 * @package App\Views\Member\Complaint
 * @author  SPK Development Team
 * @version 1.0.0 - FINAL FILE OF MEMBER VIEWS SECTION!
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Ticket Header */
    .ticket-header-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .ticket-header-top {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 25px 30px;
        color: white;
    }

    .ticket-number {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ticket-subject {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 15px;
    }

    .ticket-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        opacity: 0.9;
        font-size: 14px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ticket-info-bar {
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #f1f3f5;
    }

    .ticket-badges {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
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
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .priority-badge.low {
        background: #d4edda;
        color: #155724;
    }

    .priority-badge.normal {
        background: #cce5ff;
        color: #004085;
    }

    .priority-badge.high {
        background: #fff3cd;
        color: #856404;
    }

    .priority-badge.urgent {
        background: #f8d7da;
        color: #721c24;
    }

    /* Original Message Card */
    .message-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .message-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .message-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 20px;
        flex-shrink: 0;
    }

    .message-info {
        flex: 1;
    }

    .message-author {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .message-date {
        font-size: 13px;
        color: #6c757d;
    }

    .message-content {
        color: #2c3e50;
        line-height: 1.8;
        font-size: 15px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .category-badge {
        background: #e7f3ff;
        color: #1976d2;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        display: inline-block;
        margin-top: 15px;
    }

    /* Attachment */
    .attachment-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #f1f3f5;
    }

    .attachment-label {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 12px;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px solid #e3e6f0;
    }

    .attachment-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .attachment-info {
        flex: 1;
    }

    .attachment-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }

    /* Timeline */
    .timeline-section {
        margin-top: 30px;
    }

    .timeline-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
    }

    .timeline-header i {
        color: #667eea;
    }

    .timeline-item {
        position: relative;
        padding-left: 35px;
        padding-bottom: 30px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
    }

    .timeline-marker.staff {
        border-color: #f39c12;
    }

    .reply-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .reply-card.staff-reply {
        background: linear-gradient(135deg, #fff9e6 0%, #fff 100%);
        border-left: 4px solid #f39c12;
    }

    .reply-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 12px;
    }

    .reply-author {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .reply-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }

    .reply-avatar.staff {
        background: linear-gradient(135deg, #f39c12 0%, #f5af19 100%);
    }

    .reply-author-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 15px;
    }

    .staff-badge {
        background: #f39c12;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 8px;
    }

    .reply-date {
        font-size: 12px;
        color: #6c757d;
    }

    .reply-content {
        color: #2c3e50;
        line-height: 1.6;
        font-size: 14px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* Reply Form */
    .reply-form-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-top: 30px;
    }

    .reply-form-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .reply-form-header h4 {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .reply-form-header i {
        color: #667eea;
        font-size: 20px;
    }

    /* Closed Notice */
    .closed-notice {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        margin-top: 30px;
    }

    .closed-notice i {
        font-size: 48px;
        color: #ffc107;
        margin-bottom: 15px;
    }

    .closed-notice h4 {
        color: #856404;
        margin-bottom: 10px;
    }

    .closed-notice p {
        color: #856404;
        margin-bottom: 0;
    }

    /* Empty Replies */
    .empty-replies {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .empty-replies i {
        font-size: 48px;
        color: #e3e6f0;
        margin-bottom: 15px;
    }

    .empty-replies p {
        color: #6c757d;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ticket-header-top {
            padding: 20px;
        }

        .ticket-subject {
            font-size: 22px;
        }

        .ticket-info-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 20px;
        }

        .ticket-badges {
            width: 100%;
            flex-wrap: wrap;
        }

        .message-card {
            padding: 20px;
        }

        .message-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .timeline-item {
            padding-left: 30px;
        }

        .reply-card {
            padding: 15px;
        }

        .reply-form-card {
            padding: 20px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('member/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('member/complaint') ?>">Pengaduan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Ticket Header Card -->
<div class="ticket-header-card">
    <div class="ticket-header-top">
        <div class="ticket-number">
            <i class="bi bi-ticket-detailed"></i>
            <span>Ticket #<?= esc($ticket->ticket_number ?? $ticket->id) ?></span>
        </div>
        <h1 class="ticket-subject"><?= esc($ticket->subject) ?></h1>
        <div class="ticket-meta">
            <div class="meta-item">
                <i class="bi bi-calendar"></i>
                <span>Dibuat: <?= date('d M Y, H:i', strtotime($ticket->created_at)) ?> WIB</span>
            </div>
            <?php if (!empty($ticket->updated_at) && $ticket->updated_at != $ticket->created_at): ?>
                <div class="meta-item">
                    <i class="bi bi-clock-history"></i>
                    <span>Update terakhir: <?= time_ago($ticket->updated_at) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ticket-info-bar">
        <div class="ticket-badges">
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

            <span class="priority-badge <?= esc($ticket->priority ?? 'normal') ?>">
                <?php
                $priorityLabels = [
                    'low' => 'Rendah',
                    'normal' => 'Normal',
                    'high' => 'Tinggi',
                    'urgent' => 'Mendesak'
                ];
                echo $priorityLabels[$ticket->priority ?? 'normal'] ?? ucfirst($ticket->priority);
                ?>
            </span>
        </div>

        <?php if (!empty($ticket->assigned_to_name)): ?>
            <div style="font-size: 13px; color: #6c757d;">
                <i class="bi bi-person-check"></i>
                Ditangani oleh: <strong><?= esc($ticket->assigned_to_name) ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Original Message Card -->
<div class="message-card">
    <div class="message-header">
        <div class="message-avatar">
            <?= strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) ?>
        </div>
        <div class="message-info">
            <div class="message-author">Anda</div>
            <div class="message-date">
                <?= date('d M Y, H:i', strtotime($ticket->created_at)) ?> WIB
            </div>
        </div>
    </div>

    <div class="message-content">
        <?= esc($ticket->description) ?>
    </div>

    <?php if (!empty($ticket->category)): ?>
        <span class="category-badge">
            <i class="bi bi-tag"></i>
            <?php
            $categoryLabels = [
                'ketenagakerjaan' => 'Ketenagakerjaan',
                'gaji' => 'Gaji & Tunjangan',
                'kontrak' => 'Kontrak Kerja',
                'lingkungan_kerja' => 'Lingkungan Kerja',
                'diskriminasi' => 'Diskriminasi',
                'pelecehan' => 'Pelecehan',
                'lainnya' => 'Lainnya'
            ];
            echo $categoryLabels[$ticket->category] ?? ucfirst($ticket->category);
            ?>
        </span>
    <?php endif; ?>

    <?php if (!empty($ticket->attachment_path)): ?>
        <div class="attachment-section">
            <div class="attachment-label">
                <i class="bi bi-paperclip"></i> Lampiran
            </div>
            <div class="attachment-item">
                <div class="attachment-icon">
                    <i class="bi bi-file-earmark"></i>
                </div>
                <div class="attachment-info">
                    <div class="attachment-name">
                        <?= basename($ticket->attachment_path) ?>
                    </div>
                </div>
                <a href="<?= base_url('uploads/complaints/' . esc($ticket->attachment_path)) ?>"
                    class="btn btn-sm btn-primary"
                    download
                    target="_blank">
                    <i class="bi bi-download"></i> Unduh
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Replies Timeline -->
<?php if (!empty($replies)): ?>
    <div class="timeline-section">
        <div class="timeline-header">
            <i class="bi bi-chat-dots"></i>
            <span><?= count($replies) ?> Balasan</span>
        </div>

        <?php foreach ($replies as $reply): ?>
            <div class="timeline-item" id="reply-<?= $reply->id ?>">
                <div class="timeline-marker <?= !empty($reply->is_staff_reply) ? 'staff' : '' ?>"></div>

                <div class="reply-card <?= !empty($reply->is_staff_reply) ? 'staff-reply' : '' ?>">
                    <div class="reply-header">
                        <div class="reply-author">
                            <div class="reply-avatar <?= !empty($reply->is_staff_reply) ? 'staff' : '' ?>">
                                <?= strtoupper(substr($reply->user->name ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <span class="reply-author-name">
                                    <?= esc($reply->user->name ?? 'Unknown') ?>
                                    <?php if (!empty($reply->is_staff_reply)): ?>
                                        <span class="staff-badge">Tim SPK</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="reply-date">
                            <?= date('d M Y, H:i', strtotime($reply->created_at)) ?>
                        </div>
                    </div>

                    <div class="reply-content">
                        <?= esc($reply->message) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-replies">
        <i class="bi bi-chat-left-dots"></i>
        <p>Belum ada balasan untuk pengaduan ini</p>
    </div>
<?php endif; ?>

<!-- Reply Form or Closed Notice -->
<?php if ($ticket->status !== 'closed'): ?>
    <div class="reply-form-card">
        <div class="reply-form-header">
            <i class="bi bi-reply"></i>
            <h4>Tambah Balasan</h4>
        </div>

        <form id="replyForm" action="<?= base_url('member/complaint/reply/' . $ticket->id) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <textarea class="form-control"
                    id="message"
                    name="message"
                    rows="5"
                    placeholder="Tulis balasan atau informasi tambahan..."
                    required></textarea>
                <div class="form-text">
                    Minimal 10 karakter
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    Balasan Anda akan dikirim ke tim SPK
                </small>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Kirim Balasan
                </button>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="closed-notice">
        <i class="bi bi-lock"></i>
        <h4>Pengaduan Telah Ditutup</h4>
        <p>
            Pengaduan ini telah diselesaikan dan ditutup.
            <?php if (!empty($ticket->resolved_at)): ?>
                Diselesaikan pada <?= date('d M Y, H:i', strtotime($ticket->resolved_at)) ?> WIB.
            <?php endif; ?>
        </p>
        <?php if (!empty($ticket->resolution_notes)): ?>
            <div style="margin-top: 15px; padding: 15px; background: rgba(255, 255, 255, 0.5); border-radius: 8px;">
                <strong>Catatan Penyelesaian:</strong><br>
                <?= esc($ticket->resolution_notes) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Form validation before submit
        $('#replyForm').on('submit', function(e) {
            const message = $('#message').val().trim();

            if (message.length < 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Balasan Terlalu Pendek',
                    text: 'Balasan minimal 10 karakter',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            // Show loading
            Swal.fire({
                title: 'Mengirim Balasan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Smooth scroll to hash on page load
        if (window.location.hash) {
            setTimeout(function() {
                const target = $(window.location.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 500);
                    target.find('.reply-card').css('background-color', '#fff9e6');
                    setTimeout(() => {
                        target.find('.reply-card').css('background-color', '');
                    }, 2000);
                }
            }, 100);
        }

        // Animation on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateX(0)';
                }
            });
        }, {
            threshold: 0.1
        });

        // Observe timeline items
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(item);
        });
    });

    // Helper function for time ago
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