<?php

/**
 * View: Forum Index - Member Area
 * Controller: Member\ForumController@index
 * Description: Menampilkan daftar thread forum dengan search, filter, dan pagination
 * 
 * Features:
 * - Search threads by title/content
 * - Filter by category
 * - Pinned threads section
 * - Thread cards with stats (views, replies, last activity)
 * - Create new thread button
 * - Pagination
 * - Empty state handling
 * - Responsive design
 * 
 * @package App\Views\Member\Forum
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Thread Card Styles */
    .thread-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        background: #fff;
        transition: all 0.3s ease;
        position: relative;
    }

    .thread-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #667eea;
    }

    .thread-card.pinned {
        background: linear-gradient(135deg, #fff9e6 0%, #fff 100%);
        border-left: 4px solid #f39c12;
    }

    .thread-card.locked {
        background: #f8f9fa;
        opacity: 0.9;
    }

    .thread-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 12px;
    }

    .thread-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .thread-avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 18px;
        flex-shrink: 0;
    }

    .thread-info {
        flex: 1;
        min-width: 0;
    }

    .thread-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 6px;
        display: block;
        text-decoration: none;
        transition: color 0.2s;
    }

    .thread-title:hover {
        color: #667eea;
    }

    .thread-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: #6c757d;
        flex-wrap: wrap;
    }

    .thread-content-preview {
        color: #6c757d;
        font-size: 14px;
        line-height: 1.6;
        margin: 12px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .thread-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #f1f3f5;
    }

    .thread-stats {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #6c757d;
    }

    .stat-item i {
        font-size: 16px;
    }

    .thread-badges {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .badge-pinned {
        background: #f39c12;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-locked {
        background: #95a5a6;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .category-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }

    /* Search & Filter Section */
    .forum-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .forum-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .forum-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }

    .search-filter-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding-left: 45px;
        height: 45px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 18px;
    }

    /* Stats Cards */
    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stats-icon.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stats-icon.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .stats-info h3 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
    }

    .stats-info p {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 8px;
    }

    .empty-state i {
        font-size: 64px;
        color: #e3e6f0;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #6c757d;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 20px;
    }

    /* Section Title */
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
    }

    .section-title i {
        color: #667eea;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .forum-header {
            padding: 20px;
        }

        .forum-header h1 {
            font-size: 24px;
        }

        .thread-header {
            gap: 12px;
        }

        .thread-avatar,
        .thread-avatar-placeholder {
            width: 40px;
            height: 40px;
        }

        .thread-title {
            font-size: 16px;
        }

        .thread-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .thread-stats {
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Forum Header -->
<div class="forum-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1><i class="bi bi-chat-dots"></i> Forum Diskusi</h1>
            <p>Ruang diskusi dan berbagi informasi sesama anggota SPK</p>
        </div>
        <a href="<?= base_url('member/forum/create') ?>" class="btn btn-light btn-lg">
            <i class="bi bi-plus-circle"></i> Buat Thread Baru
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-9">
        <!-- Search & Filter -->
        <div class="search-filter-box">
            <form action="<?= base_url('member/forum') ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text"
                                class="form-control"
                                name="search"
                                placeholder="Cari thread berdasarkan judul atau konten..."
                                value="<?= esc($currentSearch ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <select class="form-select" name="category" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= esc($cat->id) ?>"
                                            <?= ($currentCategory == $cat->id) ? 'selected' : '' ?>>
                                            <?= esc($cat->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Threads Section -->
        <?php if (!empty($threads)): ?>
            <?php
            // Separate pinned and regular threads
            $pinnedThreads = array_filter($threads, function ($thread) {
                return !empty($thread->is_pinned);
            });
            $regularThreads = array_filter($threads, function ($thread) {
                return empty($thread->is_pinned);
            });
            ?>

            <!-- Pinned Threads -->
            <?php if (!empty($pinnedThreads)): ?>
                <div class="section-title">
                    <i class="bi bi-pin-angle-fill"></i>
                    <span>Thread Terpin</span>
                </div>

                <?php foreach ($pinnedThreads as $thread): ?>
                    <div class="thread-card pinned">
                        <div class="thread-header">
                            <!-- User Avatar -->
                            <?php if (!empty($thread->creator->photo)): ?>
                                <img src="<?= base_url('uploads/members/' . esc($thread->creator->photo)) ?>"
                                    alt="<?= esc($thread->creator->name) ?>"
                                    class="thread-avatar">
                            <?php else: ?>
                                <div class="thread-avatar-placeholder">
                                    <?= strtoupper(substr($thread->creator->name ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Thread Info -->
                            <div class="thread-info">
                                <a href="<?= base_url('member/forum/thread/' . $thread->id) ?>" class="thread-title">
                                    <?= esc($thread->title) ?>
                                </a>
                                <div class="thread-meta">
                                    <span><strong><?= esc($thread->creator->name ?? 'Unknown') ?></strong></span>
                                    <span>•</span>
                                    <span><?= date('d M Y, H:i', strtotime($thread->created_at)) ?> WIB</span>
                                    <?php if (!empty($thread->category_name)): ?>
                                        <span class="category-badge"><?= esc($thread->category_name) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Content Preview -->
                        <?php if (!empty($thread->content)): ?>
                            <div class="thread-content-preview">
                                <?= esc(strip_tags($thread->content)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Footer -->
                        <div class="thread-footer">
                            <div class="thread-stats">
                                <div class="stat-item">
                                    <i class="bi bi-eye"></i>
                                    <span><?= number_format($thread->views_count ?? 0) ?> views</span>
                                </div>
                                <div class="stat-item">
                                    <i class="bi bi-chat-left-text"></i>
                                    <span><?= number_format($thread->replies_count ?? 0) ?> balasan</span>
                                </div>
                                <?php if (!empty($thread->last_activity)): ?>
                                    <div class="stat-item">
                                        <i class="bi bi-clock"></i>
                                        <span><?= time_ago($thread->last_activity) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="thread-badges">
                                <span class="badge-pinned">
                                    <i class="bi bi-pin-angle-fill"></i> Terpin
                                </span>
                                <?php if (!empty($thread->is_locked)): ?>
                                    <span class="badge-locked">
                                        <i class="bi bi-lock-fill"></i> Terkunci
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Regular Threads -->
            <?php if (!empty($regularThreads)): ?>
                <?php if (!empty($pinnedThreads)): ?>
                    <div class="section-title mt-4">
                        <i class="bi bi-list-ul"></i>
                        <span>Thread Lainnya</span>
                    </div>
                <?php endif; ?>

                <?php foreach ($regularThreads as $thread): ?>
                    <div class="thread-card <?= !empty($thread->is_locked) ? 'locked' : '' ?>">
                        <div class="thread-header">
                            <!-- User Avatar -->
                            <?php if (!empty($thread->creator->photo)): ?>
                                <img src="<?= base_url('uploads/members/' . esc($thread->creator->photo)) ?>"
                                    alt="<?= esc($thread->creator->name) ?>"
                                    class="thread-avatar">
                            <?php else: ?>
                                <div class="thread-avatar-placeholder">
                                    <?= strtoupper(substr($thread->creator->name ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Thread Info -->
                            <div class="thread-info">
                                <a href="<?= base_url('member/forum/thread/' . $thread->id) ?>" class="thread-title">
                                    <?= esc($thread->title) ?>
                                </a>
                                <div class="thread-meta">
                                    <span><strong><?= esc($thread->creator->name ?? 'Unknown') ?></strong></span>
                                    <span>•</span>
                                    <span><?= date('d M Y, H:i', strtotime($thread->created_at)) ?> WIB</span>
                                    <?php if (!empty($thread->category_name)): ?>
                                        <span class="category-badge"><?= esc($thread->category_name) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Content Preview -->
                        <?php if (!empty($thread->content)): ?>
                            <div class="thread-content-preview">
                                <?= esc(strip_tags($thread->content)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Footer -->
                        <div class="thread-footer">
                            <div class="thread-stats">
                                <div class="stat-item">
                                    <i class="bi bi-eye"></i>
                                    <span><?= number_format($thread->views_count ?? 0) ?> views</span>
                                </div>
                                <div class="stat-item">
                                    <i class="bi bi-chat-left-text"></i>
                                    <span><?= number_format($thread->replies_count ?? 0) ?> balasan</span>
                                </div>
                                <?php if (!empty($thread->last_activity)): ?>
                                    <div class="stat-item">
                                        <i class="bi bi-clock"></i>
                                        <span><?= time_ago($thread->last_activity) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($thread->is_locked)): ?>
                                <div class="thread-badges">
                                    <span class="badge-locked">
                                        <i class="bi bi-lock-fill"></i> Terkunci
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if (!empty($pager)): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?= $pager->links('default', 'bootstrap_pagination') ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-chat-dots"></i>
                <h4>Belum Ada Thread</h4>
                <p>
                    <?php if (!empty($currentSearch) || !empty($currentCategory)): ?>
                        Tidak ada thread yang sesuai dengan pencarian Anda.
                    <?php else: ?>
                        Jadilah yang pertama membuat thread diskusi!
                    <?php endif; ?>
                </p>
                <?php if (empty($currentSearch) && empty($currentCategory)): ?>
                    <a href="<?= base_url('member/forum/create') ?>" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> Buat Thread Pertama
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('member/forum') ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <!-- Total Threads Stats -->
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bi bi-chat-dots"></i>
            </div>
            <div class="stats-info">
                <h3><?= number_format($total ?? 0) ?></h3>
                <p>Total Thread</p>
            </div>
        </div>

        <!-- My Threads Stats -->
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="stats-info">
                <h3><?= number_format($userThreadCount ?? 0) ?></h3>
                <p>Thread Saya</p>
            </div>
        </div>

        <!-- Forum Rules Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check"></i> Aturan Forum
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0" style="font-size: 13px; line-height: 1.8;">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Gunakan bahasa yang sopan dan santun
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Hindari SARA dan konten negatif
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Hargai pendapat anggota lain
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Jaga privasi dan data pribadi
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success"></i>
                        Diskusi fokus pada isu ketenagakerjaan
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Auto-submit when category changes
        $('#categoryFilter').on('change', function() {
            $(this).closest('form').submit();
        });

        // Smooth scroll animation for thread cards
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

        // Observe all thread cards
        document.querySelectorAll('.thread-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
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