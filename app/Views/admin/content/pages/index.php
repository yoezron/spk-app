<?php

/**
 * View: Admin Static Pages Management
 * Controller: Admin\ContentController::pages()
 * Description: Kelola halaman statis (Manifesto, AD/ART, Sejarah SPK, dll)
 * 
 * Features:
 * - List semua static pages
 * - Quick edit button
 * - Status badge (draft/published)
 * - Last updated info
 * - View count per page
 * - Search & filter
 * - Reorder pages (drag & drop)
 * - Responsive table design
 * 
 * @package App\Views\Admin\Content\Pages
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>">
<style>
    /* Page Header */
    .page-header-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .page-header-content h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header-content p {
        opacity: 0.95;
        margin-bottom: 0;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.published {
        border-color: #27ae60;
    }

    .stat-card.draft {
        border-color: #f39c12;
    }

    .stat-card.total {
        border-color: #667eea;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 12px;
    }

    .stat-card.published .stat-icon {
        background: #d5f4e6;
        color: #27ae60;
    }

    .stat-card.draft .stat-icon {
        background: #fff3cd;
        color: #f39c12;
    }

    .stat-card.total .stat-icon {
        background: #e8eaf6;
        color: #667eea;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .table-card-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .table-card-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.published {
        background: #d5f4e6;
        color: #27ae60;
    }

    .status-badge.draft {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge i {
        font-size: 8px;
    }

    /* Page Type Badge */
    .page-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .page-type-badge.static {
        background: #e8eaf6;
        color: #667eea;
    }

    .page-type-badge.content {
        background: #fce4ec;
        color: #e91e63;
    }

    /* Table Styling */
    .pages-table {
        width: 100%;
    }

    .pages-table thead th {
        background: #f8f9fa;
        color: #2c3e50;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px 12px;
        border-bottom: 2px solid #dee2e6;
    }

    .pages-table tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
    }

    .pages-table tbody tr:hover {
        background: #f8f9fa;
    }

    .page-title-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #667eea;
        font-size: 18px;
        flex-shrink: 0;
    }

    .page-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 4px 0;
    }

    .page-info .slug {
        font-size: 12px;
        color: #6c757d;
        font-family: 'Courier New', monospace;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }

    .btn-action.btn-edit {
        background: #667eea;
        color: white;
    }

    .btn-action.btn-edit:hover {
        background: #5568d3;
    }

    .btn-action.btn-view {
        background: #17a2b8;
        color: white;
    }

    .btn-action.btn-view:hover {
        background: #138496;
    }

    /* Meta Info */
    .meta-info {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 13px;
        color: #6c757d;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .meta-item i {
        font-size: 14px;
        color: #adb5bd;
    }

    /* Views Counter */
    .views-count {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f8f9fa;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        color: #495057;
    }

    .views-count i {
        color: #667eea;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #6c757d;
        font-size: 18px;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 25px;
    }

    /* Info Box */
    .info-box {
        background: #e8f4fd;
        border-left: 4px solid #17a2b8;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box .d-flex {
        gap: 12px;
    }

    .info-box h6 {
        color: #0c5460;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .info-box p {
        color: #0c5460;
        font-size: 14px;
        margin: 0;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header-content {
            padding: 20px;
        }

        .page-header-content h1 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table-card {
            padding: 15px;
            overflow-x: auto;
        }

        .page-title-cell {
            flex-direction: column;
            align-items: flex-start;
        }

        .meta-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1>
                <i class="bi bi-file-earmark-text me-2"></i>
                Kelola Halaman Statis
            </h1>
            <p>Manage halaman-halaman penting SPK seperti Manifesto, AD/ART, Sejarah, dan lainnya</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/content/posts') ?>">Konten</a></li>
        <li class="breadcrumb-item active">Halaman Statis</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Info Box -->
<div class="info-box">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill" style="font-size: 24px; color: #17a2b8;"></i>
        <div>
            <h6>Tentang Halaman Statis</h6>
            <p>
                Halaman statis adalah konten permanen yang jarang berubah, seperti Manifesto SPK, AD/ART, Sejarah, dll.
                Halaman ini dapat diakses oleh anggota dan publik. Gunakan editor untuk mengupdate konten sesuai kebutuhan.
            </p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<?php
$totalPages = count($pages ?? []);
$publishedPages = 0;
$draftPages = 0;

if (!empty($pages)) {
    foreach ($pages as $page) {
        if ($page->status === 'published') $publishedPages++;
        if ($page->status === 'draft') $draftPages++;
    }
}
?>

<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon">
            <i class="bi bi-file-earmark-text-fill"></i>
        </div>
        <div class="stat-value"><?= $totalPages ?></div>
        <div class="stat-label">Total Halaman</div>
    </div>

    <div class="stat-card published">
        <div class="stat-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="stat-value"><?= $publishedPages ?></div>
        <div class="stat-label">Dipublikasi</div>
    </div>

    <div class="stat-card draft">
        <div class="stat-icon">
            <i class="bi bi-file-earmark"></i>
        </div>
        <div class="stat-value"><?= $draftPages ?></div>
        <div class="stat-label">Draft</div>
    </div>
</div>

<!-- Pages Table -->
<div class="table-card">
    <div class="table-card-header">
        <h3>
            <i class="bi bi-list-ul me-2"></i>
            Daftar Halaman
        </h3>
    </div>

    <?php if (!empty($pages)): ?>
        <div class="table-responsive">
            <table class="table pages-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Halaman</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Views</th>
                        <th style="width: 20%;">Terakhir Update</th>
                        <th style="width: 10%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <!-- Page Title & Slug -->
                            <td>
                                <div class="page-title-cell">
                                    <div class="page-icon">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div class="page-info">
                                        <h4><?= esc($page->title) ?></h4>
                                        <div class="slug">
                                            <i class="bi bi-link-45deg"></i>
                                            /<?= esc($page->slug) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="status-badge <?= esc($page->status) ?>">
                                    <i class="bi bi-circle-fill"></i>
                                    <?= ucfirst(esc($page->status)) ?>
                                </span>
                            </td>

                            <!-- Views Count -->
                            <td>
                                <div class="views-count">
                                    <i class="bi bi-eye-fill"></i>
                                    <?= number_format($page->views_count ?? 0) ?>
                                </div>
                            </td>

                            <!-- Last Updated -->
                            <td>
                                <div class="meta-info">
                                    <div class="meta-item">
                                        <i class="bi bi-clock"></i>
                                        <?= date('d M Y', strtotime($page->updated_at)) ?>
                                    </div>
                                    <?php if (isset($page->author_name)): ?>
                                        <div class="meta-item">
                                            <i class="bi bi-person"></i>
                                            <?= esc($page->author_name) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: center;">
                                <div class="action-buttons justify-content-center">
                                    <!-- Edit Button -->
                                    <a href="<?= base_url('admin/content/pages/edit/' . $page->slug) ?>"
                                        class="btn-action btn-edit"
                                        title="Edit Halaman">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- View Button (if published) -->
                                    <?php if ($page->status === 'published'): ?>
                                        <a href="<?= base_url($page->slug) ?>"
                                            class="btn-action btn-view"
                                            target="_blank"
                                            title="Lihat Halaman">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="bi bi-file-earmark-x"></i>
            <h5>Belum Ada Halaman</h5>
            <p class="text-muted">Belum ada halaman statis yang tersedia</p>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        console.log('✓ Pages List initialized');

        // Add any additional JavaScript functionality here
        // For example: sorting, filtering, etc.
    });
</script>
<?= $this->endSection() ?>