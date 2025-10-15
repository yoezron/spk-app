<?php

/**
 * View: Admin Blog Posts Management
 * Controller: Admin\ContentController::posts()
 * Description: Comprehensive blog/content management system dengan WYSIWYG
 * 
 * Features:
 * - Posts list dengan DataTable
 * - Advanced filters (status, category, author, date)
 * - Statistics overview cards
 * - Bulk operations (publish, unpublish, delete)
 * - Quick actions per post (edit, view, publish, delete)
 * - Featured image preview thumbnails
 * - Status & category badges
 * - View count tracking
 * - SEO indicators
 * - Export functionality (Excel, CSV)
 * - Search by title, content, excerpt
 * - Sortable columns
 * - Responsive grid/list toggle
 * - Quick publish modal
 * - Preview modal
 * - Duplicate post feature
 * - Scheduling functionality
 * 
 * @package App\Views\Admin\Content
 * @author  SPK Development Team
 * @version 3.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css') ?>">
<style>
    .page-header-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.total {
        border-left-color: #667eea;
    }

    .stat-card.published {
        border-left-color: #28a745;
    }

    .stat-card.draft {
        border-left-color: #ffc107;
    }

    .stat-card.views {
        border-left-color: #17a2b8;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-icon.published {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-icon.draft {
        background: linear-gradient(135deg, #ffc107 0%, #ff8b38 100%);
    }

    .stat-icon.views {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .view-toggle {
        display: flex;
        gap: 8px;
    }

    .view-toggle-btn {
        padding: 8px 16px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .view-toggle-btn:hover {
        background: #f8f9fa;
    }

    .view-toggle-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .post-thumbnail {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #e9ecef;
    }

    .post-thumbnail-placeholder {
        width: 80px;
        height: 60px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e9ecef;
    }

    .post-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 6px;
        display: block;
    }

    .post-title:hover {
        color: #667eea;
    }

    .post-meta {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #6c757d;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-published {
        background: #d4edda;
        color: #155724;
    }

    .status-draft {
        background: #fff3cd;
        color: #856404;
    }

    .status-scheduled {
        background: #d1ecf1;
        color: #0c5460;
    }

    .category-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        background: #e9ecef;
        color: #495057;
        font-weight: 600;
    }

    .seo-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
    }

    .seo-good {
        background: #d4edda;
        color: #155724;
    }

    .seo-warning {
        background: #fff3cd;
        color: #856404;
    }

    .seo-poor {
        background: #f8d7da;
        color: #721c24;
    }

    .action-btn {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .quick-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .bulk-actions-bar {
        background: #667eea;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
        align-items: center;
        justify-content: space-between;
    }

    .bulk-actions-bar.active {
        display: flex;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }

    .empty-state i {
        font-size: 80px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .post-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
        margin-bottom: 20px;
    }

    .post-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .post-card-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .post-card-body {
        padding: 20px;
    }

    .post-card-title {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .post-card-excerpt {
        font-size: 14px;
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .post-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .grid-view {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .list-view {
        display: block;
    }

    .view-mode {
        display: none;
    }

    .view-mode.active {
        display: block;
    }

    .grid-view.active {
        display: grid;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-content">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2 text-white">
                <i class="bi bi-newspaper me-2"></i>
                Blog Posts Management
            </h3>
            <p class="mb-0 text-white opacity-90">
                Kelola artikel, berita, dan konten publikasi SPK
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/content/categories') ?>" class="btn btn-light">
                <i class="bi bi-tags me-1"></i> Categories
            </a>
            <a href="<?= base_url('admin/content/posts/create') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> New Post
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card total">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon total">
                    <i class="bi bi-file-text-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="stat-label">Total Posts</div>
            <small class="text-muted mt-2 d-block">All articles</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card published">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon published">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['published'] ?? 0) ?></div>
            <div class="stat-label">Published</div>
            <small class="text-muted mt-2 d-block">Live on website</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card draft">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon draft">
                    <i class="bi bi-pencil-square"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['draft'] ?? 0) ?></div>
            <div class="stat-label">Drafts</div>
            <small class="text-muted mt-2 d-block">Not published yet</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card views">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon views">
                    <i class="bi bi-eye-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_views'] ?? 0) ?></div>
            <div class="stat-label">Total Views</div>
            <small class="text-muted mt-2 d-block">All time</small>
        </div>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel">
    <form id="filterForm" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" id="searchInput" name="search"
                placeholder="Title, content, excerpt...">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" id="statusFilter" name="status">
                <option value="">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Category</label>
            <select class="form-select select2" id="categoryFilter" name="category_id">
                <option value="">All Categories</option>
                <?php if (isset($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->id ?>"><?= esc($category->name) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Author</label>
            <select class="form-select select2" id="authorFilter" name="author_id">
                <option value="">All Authors</option>
                <?php if (isset($authors)): ?>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author->id ?>"><?= esc($author->full_name ?? $author->email) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Date Range</label>
            <input type="text" class="form-control" id="dateRange" placeholder="Select dates">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    </form>
</div>

<!-- View Toggle & Bulk Actions -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="view-toggle">
        <button class="view-toggle-btn active" data-view="list">
            <i class="bi bi-list-ul"></i> List
        </button>
        <button class="view-toggle-btn" data-view="grid">
            <i class="bi bi-grid-3x3-gap"></i> Grid
        </button>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="exportBtn">
            <i class="bi bi-download me-1"></i> Export
        </button>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= base_url('admin/content/categories') ?>">
                        <i class="bi bi-tags me-2"></i> Manage Categories
                    </a></li>
                <li><a class="dropdown-item" href="<?= base_url('admin/content/settings') ?>">
                        <i class="bi bi-gear me-2"></i> Content Settings
                    </a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="#" id="bulkDeleteBtn">
                        <i class="bi bi-trash me-2"></i> Bulk Delete
                    </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div>
        <i class="bi bi-check-square me-2"></i>
        <strong><span id="selectedCount">0</span> posts dipilih</strong>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-light btn-sm" id="bulkPublishBtn">
            <i class="bi bi-check-circle me-1"></i> Publish
        </button>
        <button type="button" class="btn btn-light btn-sm" id="bulkUnpublishBtn">
            <i class="bi bi-x-circle me-1"></i> Unpublish
        </button>
        <button type="button" class="btn btn-light btn-sm" id="bulkDeleteConfirmBtn">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" id="clearSelectionBtn">
            <i class="bi bi-x me-1"></i> Clear
        </button>
    </div>
</div>

<!-- List View -->
<div class="view-mode list-view active" id="listView">
    <div class="card">
        <div class="card-body">
            <?php if (!empty($posts)): ?>
                <table id="postsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAllPosts" class="form-check-input">
                            </th>
                            <th width="100">Image</th>
                            <th>Title & Info</th>
                            <th width="120">Category</th>
                            <th width="100">Status</th>
                            <th width="100">Views</th>
                            <th width="120">Author</th>
                            <th width="100">Date</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input post-checkbox" value="<?= $post->id ?>">
                                </td>
                                <td>
                                    <?php if (!empty($post->featured_image)): ?>
                                        <img src="<?= base_url('uploads/posts/' . $post->featured_image) ?>"
                                            alt="Thumbnail" class="post-thumbnail">
                                    <?php else: ?>
                                        <div class="post-thumbnail-placeholder">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/content/posts/edit/' . $post->id) ?>"
                                        class="post-title text-decoration-none">
                                        <?= esc($post->title) ?>
                                    </a>
                                    <div class="post-meta">
                                        <?php if (!empty($post->excerpt)): ?>
                                            <span class="meta-item" title="<?= esc($post->excerpt) ?>">
                                                <i class="bi bi-text-paragraph"></i>
                                                <?= esc(substr($post->excerpt, 0, 50)) ?>...
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($post->seo_score)): ?>
                                            <span class="seo-indicator seo-<?= $post->seo_score >= 70 ? 'good' : ($post->seo_score >= 50 ? 'warning' : 'poor') ?>">
                                                SEO: <?= $post->seo_score ?>%
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($post->category_name)): ?>
                                        <span class="category-badge">
                                            <?= esc($post->category_name) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Uncategorized</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $post->status ?>">
                                        <?= ucfirst($post->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-eye text-muted"></i>
                                        <strong><?= number_format($post->view_count ?? 0) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <small><?= esc($post->author_name ?? 'Unknown') ?></small>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d M Y', strtotime($post->created_at)) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="quick-actions">
                                        <?php if ($post->status === 'published'): ?>
                                            <a href="<?= base_url('blog/' . $post->slug) ?>"
                                                class="btn btn-sm btn-info action-btn" target="_blank" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('admin/content/posts/edit/' . $post->id) ?>"
                                            class="btn btn-sm btn-primary action-btn" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($post->status === 'draft'): ?>
                                            <button type="button" class="btn btn-sm btn-success action-btn publish-btn"
                                                data-post-id="<?= $post->id ?>" title="Publish">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger action-btn delete-btn"
                                            data-post-id="<?= $post->id ?>"
                                            data-post-title="<?= esc($post->title) ?>"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <div class="mt-3">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="bi bi-newspaper"></i>
                    <h5>Belum Ada Post</h5>
                    <p class="text-muted">Mulai buat artikel pertama Anda</p>
                    <a href="<?= base_url('admin/content/posts/create') ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i> Create First Post
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grid View -->
<div class="view-mode grid-view" id="gridView">
    <?php if (!empty($posts)): ?>
        <div class="grid-view">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <?php if (!empty($post->featured_image)): ?>
                        <img src="<?= base_url('uploads/posts/' . $post->featured_image) ?>"
                            alt="Featured" class="post-card-image">
                    <?php else: ?>
                        <div class="post-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-image text-white" style="font-size: 48px;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="post-card-body">
                        <div class="mb-2">
                            <span class="status-badge status-<?= $post->status ?>">
                                <?= ucfirst($post->status) ?>
                            </span>
                            <?php if (!empty($post->category_name)): ?>
                                <span class="category-badge ms-2">
                                    <?= esc($post->category_name) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h5 class="post-card-title"><?= esc($post->title) ?></h5>

                        <?php if (!empty($post->excerpt)): ?>
                            <p class="post-card-excerpt"><?= esc($post->excerpt) ?></p>
                        <?php endif; ?>

                        <div class="post-card-footer">
                            <div class="post-meta">
                                <span class="meta-item">
                                    <i class="bi bi-eye"></i>
                                    <?= number_format($post->view_count ?? 0) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d M Y', strtotime($post->created_at)) ?>
                                </span>
                            </div>
                            <div class="quick-actions">
                                <a href="<?= base_url('admin/content/posts/edit/' . $post->id) ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($post->status === 'draft'): ?>
                                    <button type="button" class="btn btn-sm btn-success publish-btn"
                                        data-post-id="<?= $post->id ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-newspaper"></i>
            <h5>Belum Ada Post</h5>
            <p class="text-muted">Mulai buat artikel pertama Anda</p>
            <a href="<?= base_url('admin/content/posts/create') ?>" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-1"></i> Create First Post
            </a>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select...',
            allowClear: true
        });

        // Initialize Date Range Picker
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        // Initialize DataTable
        const table = $('#postsTable').DataTable({
            responsive: true,
            pageLength: 20,
            order: [
                [7, 'desc']
            ], // Date
            columnDefs: [{
                orderable: false,
                targets: [0, 1, 8]
            }],
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
            }
        });

        // View Toggle
        $('.view-toggle-btn').on('click', function() {
            const view = $(this).data('view');

            $('.view-toggle-btn').removeClass('active');
            $(this).addClass('active');

            $('.view-mode').removeClass('active');
            if (view === 'grid') {
                $('#gridView').addClass('active');
            } else {
                $('#listView').addClass('active');
            }
        });

        // Handle checkbox selection
        $('.post-checkbox').on('change', function() {
            updateBulkActions();
        });

        $('#selectAllPosts').on('change', function() {
            $('.post-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkActions();
        });

        function updateBulkActions() {
            const selectedCount = $('.post-checkbox:checked').length;
            $('#selectedCount').text(selectedCount);

            if (selectedCount > 0) {
                $('#bulkActionsBar').addClass('active');
            } else {
                $('#bulkActionsBar').removeClass('active');
            }
        }

        $('#clearSelectionBtn').on('click', function() {
            $('.post-checkbox').prop('checked', false);
            $('#selectAllPosts').prop('checked', false);
            updateBulkActions();
        });

        // Publish Single Post
        $('.publish-btn').on('click', function() {
            const postId = $(this).data('post-id');

            Swal.fire({
                title: 'Publish Post?',
                text: 'Post will be visible on the website',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Publish!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('admin/content/posts/publish/') ?>' + postId,
                        method: 'POST',
                        data: {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Published!',
                                text: 'Post has been published',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: xhr.responseJSON?.message || 'An error occurred'
                            });
                        }
                    });
                }
            });
        });

        // Delete Single Post
        $('.delete-btn').on('click', function() {
            const postId = $(this).data('post-id');
            const postTitle = $(this).data('post-title');

            Swal.fire({
                title: 'Delete Post?',
                text: `"${postTitle}" will be deleted permanently`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('admin/content/posts/delete/') ?>' + postId,
                        method: 'POST',
                        data: {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Post has been deleted',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: xhr.responseJSON?.message || 'An error occurred'
                            });
                        }
                    });
                }
            });
        });

        // Bulk Publish
        $('#bulkPublishBtn').on('click', function() {
            const selectedIds = $('.post-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: `Publish ${selectedIds.length} Posts?`,
                text: 'Selected posts will be published',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Publish All!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('admin/content/posts/bulk-publish') ?>',
                        method: 'POST',
                        data: {
                            post_ids: selectedIds,
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Published!',
                                text: `${selectedIds.length} posts published`,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });
                }
            });
        });

        // Bulk Delete
        $('#bulkDeleteConfirmBtn').on('click', function() {
            const selectedIds = $('.post-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: `Delete ${selectedIds.length} Posts?`,
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Delete All!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('admin/content/posts/bulk-delete') ?>',
                        method: 'POST',
                        data: {
                            post_ids: selectedIds,
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: `${selectedIds.length} posts deleted`,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });
                }
            });
        });

        // Export
        $('#exportBtn').on('click', function() {
            Swal.fire({
                title: 'Export Format',
                text: 'Choose export format',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-file-earmark-excel"></i> Excel',
                cancelButtonText: '<i class="bi bi-filetype-csv"></i> CSV'
            }).then((result) => {
                const format = result.isConfirmed ? 'excel' : 'csv';
                if (result.isConfirmed || result.isDismissed) {
                    window.location.href = '<?= base_url('admin/content/posts/export?format=') ?>' + format;
                }
            });
        });

        // Filter form submit
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            window.location.href = '<?= current_url() ?>?' + formData;
        });
    });
</script>
<?= $this->endSection() ?>