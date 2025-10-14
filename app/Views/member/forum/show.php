<?php

/**
 * View: Forum Thread Detail - Member Area
 * Controller: Member\ForumController@show
 * Description: Menampilkan detail thread dengan semua replies dan form untuk reply
 * 
 * Features:
 * - Thread header dengan info lengkap
 * - Original post content
 * - Replies/posts list dengan pagination
 * - Reply form dengan WYSIWYG editor
 * - Edit/Delete buttons untuk post milik sendiri
 * - Pin/Lock badges
 * - Scroll to specific post (#post-123)
 * - Share thread functionality
 * - Responsive design
 * 
 * @package App\Views\Member\Forum
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<!-- Summernote CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-bs5.min.css') ?>">

<style>
    /* Thread Header */
    .thread-header-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .thread-header-top {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 25px 30px;
        color: white;
    }

    .thread-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .thread-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        opacity: 0.95;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .meta-item i {
        font-size: 16px;
    }

    .thread-badges {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }

    .badge-pinned {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-locked {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-solved {
        background: rgba(46, 213, 115, 0.25);
        backdrop-filter: blur(10px);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .thread-stats {
        background: white;
        padding: 20px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #f1f3f5;
    }

    .stats-left {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6c757d;
        font-size: 14px;
    }

    .stat-item i {
        font-size: 18px;
        color: #667eea;
    }

    .stat-item strong {
        color: #2c3e50;
        font-weight: 600;
    }

    .thread-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Post Card */
    .post-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        padding: 25px;
        position: relative;
    }

    .post-card.original-post {
        border-left: 4px solid #667eea;
    }

    .post-header {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .post-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .post-avatar-placeholder {
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

    .post-info {
        flex: 1;
        min-width: 0;
    }

    .post-author {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .post-author .badge {
        font-size: 11px;
        font-weight: 500;
        margin-left: 8px;
    }

    .post-date {
        font-size: 13px;
        color: #6c757d;
    }

    .post-actions-top {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .post-content {
        color: #2c3e50;
        line-height: 1.8;
        font-size: 15px;
        margin-bottom: 15px;
    }

    .post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 15px 0;
    }

    .post-content p:last-child {
        margin-bottom: 0;
    }

    .post-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid #f1f3f5;
        margin-top: 15px;
    }

    .post-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Reply Form */
    .reply-form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-top: 30px;
        margin-bottom: 30px;
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

    .locked-notice {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin-top: 30px;
    }

    .locked-notice i {
        font-size: 48px;
        color: #ffc107;
        margin-bottom: 15px;
    }

    .locked-notice h4 {
        color: #856404;
        margin-bottom: 10px;
    }

    .locked-notice p {
        color: #856404;
        margin-bottom: 0;
    }

    /* Section Divider */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 15px;
        margin: 30px 0 25px;
    }

    .section-divider h3 {
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-divider i {
        color: #667eea;
    }

    .section-divider hr {
        flex: 1;
        border: none;
        border-top: 2px solid #e3e6f0;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 20px 0;
    }

    .empty-state i {
        font-size: 48px;
        color: #e3e6f0;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 0;
    }

    /* Sidebar */
    .sidebar-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 20px;
        margin-bottom: 20px;
    }

    .sidebar-card h5 {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f3f5;
    }

    .category-badge-large {
        background: #e3f2fd;
        color: #1976d2;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        display: inline-block;
        margin-bottom: 10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .thread-header-top {
            padding: 20px;
        }

        .thread-title {
            font-size: 22px;
        }

        .thread-stats {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 20px;
        }

        .stats-left {
            width: 100%;
            justify-content: space-between;
        }

        .thread-actions {
            width: 100%;
        }

        .thread-actions .btn {
            flex: 1;
        }

        .post-card {
            padding: 20px;
        }

        .post-header {
            gap: 12px;
        }

        .post-avatar,
        .post-avatar-placeholder {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }

        .post-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
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
        <li class="breadcrumb-item"><a href="<?= base_url('member/forum') ?>">Forum</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thread Detail</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<div class="row">
    <div class="col-lg-9">
        <!-- Thread Header Card -->
        <div class="thread-header-card">
            <div class="thread-header-top">
                <h1 class="thread-title"><?= esc($thread->title) ?></h1>

                <div class="thread-meta">
                    <div class="meta-item">
                        <i class="bi bi-person-circle"></i>
                        <strong><?= esc($thread->creator->name ?? 'Unknown') ?></strong>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-clock"></i>
                        <span><?= date('d M Y, H:i', strtotime($thread->created_at)) ?> WIB</span>
                    </div>
                    <?php if (!empty($thread->category_name)): ?>
                        <div class="meta-item">
                            <i class="bi bi-tag"></i>
                            <span><?= esc($thread->category_name) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($thread->is_pinned) || !empty($thread->is_locked) || !empty($thread->is_solved)): ?>
                    <div class="thread-badges">
                        <?php if (!empty($thread->is_pinned)): ?>
                            <span class="badge-pinned">
                                <i class="bi bi-pin-angle-fill"></i> Terpin
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($thread->is_locked)): ?>
                            <span class="badge-locked">
                                <i class="bi bi-lock-fill"></i> Terkunci
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($thread->is_solved)): ?>
                            <span class="badge-solved">
                                <i class="bi bi-check-circle-fill"></i> Terjawab
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="thread-stats">
                <div class="stats-left">
                    <div class="stat-item">
                        <i class="bi bi-eye"></i>
                        <strong><?= number_format($thread->views_count ?? 0) ?></strong> views
                    </div>
                    <div class="stat-item">
                        <i class="bi bi-chat-left-text"></i>
                        <strong><?= number_format(count($posts ?? [])) ?></strong> balasan
                    </div>
                    <?php if (!empty($thread->last_activity)): ?>
                        <div class="stat-item">
                            <i class="bi bi-clock-history"></i>
                            Aktivitas terakhir: <?= time_ago($thread->last_activity) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="thread-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="shareButton">
                        <i class="bi bi-share"></i> Share
                    </button>
                    <a href="<?= base_url('member/forum') ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Original Post -->
        <div class="post-card original-post" id="post-<?= $thread->id ?>">
            <div class="post-header">
                <?php if (!empty($thread->creator->photo)): ?>
                    <img src="<?= base_url('uploads/members/' . esc($thread->creator->photo)) ?>"
                        alt="<?= esc($thread->creator->name) ?>"
                        class="post-avatar">
                <?php else: ?>
                    <div class="post-avatar-placeholder">
                        <?= strtoupper(substr($thread->creator->name ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <div class="post-info">
                    <div class="post-author">
                        <?= esc($thread->creator->name ?? 'Unknown') ?>
                        <span class="badge bg-primary">Thread Starter</span>
                    </div>
                    <div class="post-date">
                        <?= date('d M Y, H:i', strtotime($thread->created_at)) ?> WIB
                    </div>
                </div>

                <?php if ($currentUserId == $thread->user_id || $canModerate): ?>
                    <div class="post-actions-top">
                        <?php if ($currentUserId == $thread->user_id): ?>
                            <a href="<?= base_url('member/forum/edit/' . $thread->id) ?>"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($canModerate): ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger delete-thread-btn"
                                data-id="<?= $thread->id ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="post-content">
                <?= $thread->content ?>
            </div>
        </div>

        <!-- Replies Section -->
        <?php if (!empty($posts)): ?>
            <div class="section-divider">
                <h3>
                    <i class="bi bi-chat-dots"></i>
                    <span><?= count($posts) ?> Balasan</span>
                </h3>
                <hr>
            </div>

            <?php foreach ($posts as $post): ?>
                <div class="post-card" id="post-<?= $post->id ?>">
                    <div class="post-header">
                        <?php if (!empty($post->user->photo)): ?>
                            <img src="<?= base_url('uploads/members/' . esc($post->user->photo)) ?>"
                                alt="<?= esc($post->user->name) ?>"
                                class="post-avatar">
                        <?php else: ?>
                            <div class="post-avatar-placeholder">
                                <?= strtoupper(substr($post->user->name ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-info">
                            <div class="post-author">
                                <?= esc($post->user->name ?? 'Unknown') ?>
                                <?php if ($post->user_id == $thread->user_id): ?>
                                    <span class="badge bg-info">Thread Starter</span>
                                <?php endif; ?>
                            </div>
                            <div class="post-date">
                                <?= date('d M Y, H:i', strtotime($post->created_at)) ?> WIB
                            </div>
                        </div>

                        <?php if ($currentUserId == $post->user_id || $canModerate): ?>
                            <div class="post-actions-top">
                                <?php if ($currentUserId == $post->user_id): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary edit-post-btn"
                                        data-id="<?= $post->id ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php endif; ?>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-post-btn"
                                    data-id="<?= $post->id ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="post-content">
                        <?= $post->content ?>
                    </div>

                    <div class="post-footer">
                        <div class="post-actions">
                            <a href="#replyForm" class="btn btn-sm btn-light">
                                <i class="bi bi-reply"></i> Balas
                            </a>
                        </div>
                        <small class="text-muted">
                            #<?= $post->id ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-chat-dots"></i>
                <p>Belum ada balasan untuk thread ini. Jadilah yang pertama memberi tanggapan!</p>
            </div>
        <?php endif; ?>

        <!-- Reply Form -->
        <?php if (empty($thread->is_locked)): ?>
            <div class="reply-form-card" id="replyForm">
                <div class="reply-form-header">
                    <i class="bi bi-reply"></i>
                    <h4>Balas Thread</h4>
                </div>

                <form id="replyThreadForm" action="<?= base_url('member/forum/reply/' . $thread->id) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <textarea class="form-control"
                            id="replyContent"
                            name="content"
                            rows="8"></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Gunakan bahasa yang sopan dan konstruktif
                        </small>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Kirim Balasan
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="locked-notice">
                <i class="bi bi-lock"></i>
                <h4>Thread Terkunci</h4>
                <p>Thread ini telah ditutup oleh moderator. Anda tidak dapat membalas thread ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <!-- Category Info -->
        <?php if (!empty($thread->category_name)): ?>
            <div class="sidebar-card">
                <h5>Kategori</h5>
                <span class="category-badge-large">
                    <i class="bi bi-tag"></i>
                    <?= esc($thread->category_name) ?>
                </span>
                <p class="text-muted mb-0 mt-2" style="font-size: 13px;">
                    <a href="<?= base_url('member/forum?category=' . $thread->category_id) ?>" class="text-decoration-none">
                        Lihat thread lainnya dalam kategori ini
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </p>
            </div>
        <?php endif; ?>

        <!-- Thread Creator Info -->
        <div class="sidebar-card">
            <h5>Pembuat Thread</h5>
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if (!empty($thread->creator->photo)): ?>
                    <img src="<?= base_url('uploads/members/' . esc($thread->creator->photo)) ?>"
                        alt="<?= esc($thread->creator->name) ?>"
                        class="rounded-circle"
                        style="width: 50px; height: 50px; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 20px;">
                        <?= strtoupper(substr($thread->creator->name ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= esc($thread->creator->name ?? 'Unknown') ?>
                    </div>
                    <div style="font-size: 12px; color: #6c757d;">
                        Member sejak <?= date('Y', strtotime($thread->creator->created_at ?? 'now')) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <?php if ($canModerate): ?>
            <div class="sidebar-card">
                <h5>Moderasi</h5>
                <div class="d-grid gap-2">
                    <button type="button"
                        class="btn btn-sm btn-outline-warning toggle-pin-btn"
                        data-id="<?= $thread->id ?>"
                        data-pinned="<?= !empty($thread->is_pinned) ? '1' : '0' ?>">
                        <i class="bi bi-pin-angle"></i>
                        <?= !empty($thread->is_pinned) ? 'Unpin' : 'Pin' ?> Thread
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary toggle-lock-btn"
                        data-id="<?= $thread->id ?>"
                        data-locked="<?= !empty($thread->is_locked) ? '1' : '0' ?>">
                        <i class="bi bi-lock"></i>
                        <?= !empty($thread->is_locked) ? 'Unlock' : 'Lock' ?> Thread
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger delete-thread-btn"
                        data-id="<?= $thread->id ?>">
                        <i class="bi bi-trash"></i>
                        Hapus Thread
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Forum Guidelines -->
        <div class="sidebar-card">
            <h5>Panduan Forum</h5>
            <ul class="list-unstyled mb-0" style="font-size: 13px; line-height: 1.8;">
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success"></i>
                    Gunakan bahasa yang sopan
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success"></i>
                    Hindari konten SARA
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success"></i>
                    No spam atau iklan
                </li>
                <li>
                    <i class="bi bi-check-circle text-success"></i>
                    Hormati pendapat orang lain
                </li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Summernote JS -->
<script src="<?= base_url('assets/plugins/summernote/summernote-bs5.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        // Initialize Summernote for reply
        <?php if (empty($thread->is_locked)): ?>
            $('#replyContent').summernote({
                height: 200,
                placeholder: 'Tulis balasan Anda di sini...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'help']]
                ]
            });
        <?php endif; ?>

        // Share Button
        $('#shareButton').on('click', function() {
            const url = window.location.href;

            if (navigator.share) {
                navigator.share({
                    title: '<?= addslashes($thread->title) ?>',
                    text: 'Lihat diskusi ini di Forum SPK',
                    url: url
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(url).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Link Disalin!',
                        text: 'Link thread telah disalin ke clipboard',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });

        // Delete Post
        $(document).on('click', '.delete-post-btn', function() {
            const postId = $(this).data('id');

            Swal.fire({
                title: 'Hapus Balasan?',
                text: 'Balasan yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= base_url('member/forum/delete-post/') ?>${postId}`;
                }
            });
        });

        // Delete Thread
        $(document).on('click', '.delete-thread-btn', function() {
            const threadId = $(this).data('id');

            Swal.fire({
                title: 'Hapus Thread?',
                text: 'Thread dan semua balasan akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= base_url('admin/forum/delete/') ?>${threadId}`;
                }
            });
        });

        // Toggle Pin (Moderator)
        $(document).on('click', '.toggle-pin-btn', function() {
            const threadId = $(this).data('id');
            const isPinned = $(this).data('pinned') === '1';
            const action = isPinned ? 'unpin' : 'pin';

            Swal.fire({
                title: isPinned ? 'Unpin Thread?' : 'Pin Thread?',
                text: isPinned ? 'Thread akan ditampilkan normal' : 'Thread akan ditampilkan di atas',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= base_url('admin/forum/') ?>${action}/${threadId}`;
                }
            });
        });

        // Toggle Lock (Moderator)
        $(document).on('click', '.toggle-lock-btn', function() {
            const threadId = $(this).data('id');
            const isLocked = $(this).data('locked') === '1';
            const action = isLocked ? 'unlock' : 'lock';

            Swal.fire({
                title: isLocked ? 'Unlock Thread?' : 'Lock Thread?',
                text: isLocked ? 'Thread akan dibuka untuk balasan' : 'Thread akan ditutup dari balasan baru',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= base_url('admin/forum/') ?>${action}/${threadId}`;
                }
            });
        });

        // Form validation before submit
        $('#replyThreadForm').on('submit', function(e) {
            const content = $('#replyContent').summernote('code').trim();
            const contentText = $('<div>').html(content).text().trim();

            if (contentText.length < 10) {
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
                    target.css('background-color', '#fff9e6');
                    setTimeout(() => {
                        target.css('background-color', '');
                    }, 2000);
                }
            }, 100);
        }

        // Scroll to reply form when clicking reply button
        $(document).on('click', 'a[href="#replyForm"]', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('#replyForm').offset().top - 100
            }, 500);
            $('#replyContent').summernote('focus');
        });
    });
</script>
<?= $this->endSection() ?>