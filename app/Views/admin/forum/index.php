<?php

/**
 * View: Admin Forum Management
 * Controller: App\Controllers\Admin\ForumController::index()
 * Description: Forum moderation interface - Pin, Lock, Delete threads
 * 
 * Features:
 * - Statistics cards (Total, Active, Locked, Pinned threads)
 * - Advanced filters (Category, Status, Search)
 * - DataTable with moderation actions
 * - Pin/Unpin threads (highlight at top)
 * - Lock/Unlock threads (prevent replies)
 * - Delete threads with reason
 * - Status badges (Pinned, Locked)
 * - View thread details
 * - Recent activity tracking
 * - Responsive design (mobile-first)
 * - SweetAlert confirmations
 * - Permission-based actions
 * 
 * @package App\Views\Admin\Forum
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>" rel="stylesheet">
<!-- Select2 CSS -->
<link href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/select2/css/select2-bootstrap4.min.css') ?>" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">

<style>
    /* Forum Management Wrapper */
    .forum-wrapper {
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
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header p {
        font-size: 15px;
        opacity: 0.95;
        margin: 0;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
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

    .stat-card.info {
        border-left-color: #4299e1;
    }

    .stat-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 12px;
    }

    .stat-card.primary .stat-card-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.success .stat-card-icon {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }

    .stat-card.warning .stat-card-icon {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    }

    .stat-card.info .stat-card-icon {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }

    .stat-card-label {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .stat-card-value {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
    }

    /* Filters Section */
    .filters-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .filters-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .filters-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filters-header h3 i {
        color: #667eea;
    }

    .filters-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: #4a5568;
        margin: 0;
    }

    .filter-group .form-control,
    .filter-group .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .filter-group .form-control:focus,
    .filter-group .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filters-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    /* Table Section */
    .table-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    /* DataTable Custom */
    #forumTable thead th {
        background: #f7fafc;
        color: #4a5568;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        padding: 14px 12px;
        border-bottom: 2px solid #e2e8f0;
    }

    #forumTable tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        font-size: 14px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
    }

    #forumTable tbody tr {
        transition: all 0.2s ease;
    }

    #forumTable tbody tr:hover {
        background: #f7fafc;
    }

    /* Thread Info */
    .thread-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .thread-title {
        font-weight: 600;
        color: #2d3748;
        font-size: 15px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .thread-title a {
        color: #667eea;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .thread-title a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .thread-meta {
        font-size: 12px;
        color: #a0aec0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .thread-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Thread Badges */
    .thread-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .thread-badge.pinned {
        background: #fef3c7;
        color: #78350f;
    }

    .thread-badge.locked {
        background: #fed7d7;
        color: #742a2a;
    }

    /* Category Badge */
    .category-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: #e6f2ff;
        color: #2b6cb0;
    }

    /* Author Info */
    .author-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .author-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }

    .author-name {
        font-size: 13px;
        font-weight: 600;
        color: #2d3748;
    }

    /* Stats Display */
    .thread-stats {
        display: flex;
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }

    .stat-number {
        font-size: 18px;
        font-weight: 700;
        color: #667eea;
    }

    .stat-label {
        font-size: 11px;
        color: #a0aec0;
        text-transform: uppercase;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        font-size: 16px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-action.view {
        background: #e6f2ff;
        color: #4299e1;
    }

    .btn-action.view:hover {
        background: #4299e1;
        color: white;
    }

    .btn-action.pin {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-action.pin:hover {
        background: #d97706;
        color: white;
    }

    .btn-action.lock {
        background: #fed7d7;
        color: #dc2626;
    }

    .btn-action.lock:hover {
        background: #dc2626;
        color: white;
    }

    .btn-action.delete {
        background: #fce7f3;
        color: #be185d;
    }

    .btn-action.delete:hover {
        background: #be185d;
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 700;
        color: #718096;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .forum-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filters-body {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
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

<div class="forum-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <h1>
            <i class="material-icons-outlined">forum</i>
            Moderasi Forum
        </h1>
        <p>Kelola dan moderasi diskusi forum anggota - Pin, Lock, atau Hapus thread</p>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Statistics Cards -->
    <div class="stats-grid animate-fade-in-up" style="animation-delay: 0.1s;">

        <div class="stat-card primary">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">forum</i>
            </div>
            <div class="stat-card-label">Total Thread</div>
            <div class="stat-card-value"><?= number_format($stats['total_threads'] ?? 0) ?></div>
        </div>

        <div class="stat-card success">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">check_circle</i>
            </div>
            <div class="stat-card-label">Thread Aktif</div>
            <div class="stat-card-value"><?= number_format($stats['active_threads'] ?? 0) ?></div>
        </div>

        <div class="stat-card warning">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">push_pin</i>
            </div>
            <div class="stat-card-label">Pinned</div>
            <div class="stat-card-value"><?= number_format($stats['pinned_threads'] ?? 0) ?></div>
        </div>

        <div class="stat-card info">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">lock</i>
            </div>
            <div class="stat-card-label">Locked</div>
            <div class="stat-card-value"><?= number_format($stats['locked_threads'] ?? 0) ?></div>
        </div>

    </div>

    <!-- Filters Section -->
    <div class="filters-section animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="filters-header">
            <h3>
                <i class="material-icons-outlined">filter_list</i>
                Filter & Pencarian
            </h3>
        </div>

        <form method="GET" action="<?= current_url() ?>" id="filtersForm">
            <div class="filters-body">

                <!-- Category Filter -->
                <div class="filter-group">
                    <label for="filterCategory">Kategori</label>
                    <select name="category_id" id="filterCategory" class="form-select select2">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories ?? [] as $category): ?>
                            <option value="<?= $category->id ?>" <?= ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' ?>>
                                <?= esc($category->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="filterStatus">Status</label>
                    <select name="status" id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="pinned" <?= ($filters['status'] ?? '') === 'pinned' ? 'selected' : '' ?>>Pinned</option>
                        <option value="locked" <?= ($filters['status'] ?? '') === 'locked' ? 'selected' : '' ?>>Locked</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="filter-group">
                    <label for="filterSearch">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        id="filterSearch"
                        class="form-control"
                        placeholder="Cari thread..."
                        value="<?= esc($filters['search'] ?? '') ?>">
                </div>

            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons-outlined" style="font-size: 18px;">search</i>
                    Terapkan Filter
                </button>
                <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined" style="font-size: 18px;">refresh</i>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-section animate-fade-in-up" style="animation-delay: 0.3s;">

        <div class="table-header">
            <h3 class="table-title">
                Daftar Thread Forum
                <?php if (!empty($threads)): ?>
                    <span style="color: #718096; font-weight: 400; font-size: 14px;">
                        (<?= count($threads) ?> thread)
                    </span>
                <?php endif; ?>
            </h3>
        </div>

        <?php if (!empty($threads)): ?>
            <div class="table-responsive">
                <table id="forumTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Thread</th>
                            <th>Author</th>
                            <th>Kategori</th>
                            <th style="text-align: center;">Replies</th>
                            <th style="text-align: center;">Views</th>
                            <th>Last Activity</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($threads as $thread): ?>
                            <tr>
                                <td>
                                    <div class="thread-info">
                                        <div class="thread-title">
                                            <?php if ($thread->is_pinned): ?>
                                                <span class="thread-badge pinned">
                                                    <i class="material-icons-outlined" style="font-size: 12px;">push_pin</i>
                                                    PINNED
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($thread->is_locked): ?>
                                                <span class="thread-badge locked">
                                                    <i class="material-icons-outlined" style="font-size: 12px;">lock</i>
                                                    LOCKED
                                                </span>
                                            <?php endif; ?>

                                            <a href="<?= base_url('admin/forum/thread/' . $thread->id) ?>">
                                                <?= esc($thread->title) ?>
                                            </a>
                                        </div>
                                        <div class="thread-meta">
                                            <span class="thread-meta-item">
                                                <i class="material-icons-outlined" style="font-size: 14px;">schedule</i>
                                                <?= date('d M Y', strtotime($thread->created_at)) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="author-info">
                                        <?php if (!empty($thread->author_photo)): ?>
                                            <img
                                                src="<?= base_url('uploads/photos/' . $thread->author_photo) ?>"
                                                alt="Avatar"
                                                class="author-avatar">
                                        <?php else: ?>
                                            <img
                                                src="<?= base_url('assets/images/avatars/avatar.png') ?>"
                                                alt="Avatar"
                                                class="author-avatar">
                                        <?php endif; ?>
                                        <span class="author-name"><?= esc($thread->author_name ?? 'Unknown') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge">
                                        <?= esc($thread->category_name ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="thread-stats">
                                        <span class="stat-number"><?= number_format($thread->reply_count ?? 0) ?></span>
                                        <span class="stat-label">Replies</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="thread-stats">
                                        <span class="stat-number"><?= number_format($thread->view_count ?? 0) ?></span>
                                        <span class="stat-label">Views</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #4a5568;">
                                        <?php
                                        $lastActivity = strtotime($thread->last_activity_at ?? $thread->updated_at);
                                        $now = time();
                                        $diff = $now - $lastActivity;

                                        if ($diff < 3600) {
                                            echo floor($diff / 60) . ' menit lalu';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . ' jam lalu';
                                        } elseif ($diff < 604800) {
                                            echo floor($diff / 86400) . ' hari lalu';
                                        } else {
                                            echo date('d M Y', $lastActivity);
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- View Button -->
                                        <a
                                            href="<?= base_url('admin/forum/thread/' . $thread->id) ?>"
                                            class="btn-action view"
                                            data-bs-toggle="tooltip"
                                            title="Lihat Thread">
                                            <i class="material-icons-outlined">visibility</i>
                                        </a>

                                        <!-- Pin/Unpin Button -->
                                        <button
                                            type="button"
                                            class="btn-action pin"
                                            onclick="togglePin(<?= $thread->id ?>, <?= $thread->is_pinned ? 'true' : 'false' ?>)"
                                            data-bs-toggle="tooltip"
                                            title="<?= $thread->is_pinned ? 'Unpin' : 'Pin' ?> Thread">
                                            <i class="material-icons-outlined"><?= $thread->is_pinned ? 'push_pin' : 'push_pin' ?></i>
                                        </button>

                                        <!-- Lock/Unlock Button -->
                                        <button
                                            type="button"
                                            class="btn-action lock"
                                            onclick="toggleLock(<?= $thread->id ?>, <?= $thread->is_locked ? 'true' : 'false' ?>)"
                                            data-bs-toggle="tooltip"
                                            title="<?= $thread->is_locked ? 'Unlock' : 'Lock' ?> Thread">
                                            <i class="material-icons-outlined"><?= $thread->is_locked ? 'lock_open' : 'lock' ?></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            type="button"
                                            class="btn-action delete"
                                            onclick="deleteThread(<?= $thread->id ?>, '<?= esc($thread->title) ?>')"
                                            data-bs-toggle="tooltip"
                                            title="Hapus Thread">
                                            <i class="material-icons-outlined">delete</i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($pager)): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Menampilkan <?= count($threads) ?> dari <?= $pager->getTotal() ?> thread
                    </div>
                    <?= $pager->links('default', 'default_full') ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="material-icons-outlined">forum</i>
                <h3>Tidak Ada Thread Forum</h3>
                <p>Belum ada thread yang dibuat atau sesuai dengan filter yang diterapkan</p>
            </div>
        <?php endif; ?>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables JS -->
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/js/select2.min.js') ?>"></script>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih...',
            allowClear: true
        });

        // Initialize DataTables
        $('#forumTable').DataTable({
            responsive: true,
            pageLength: 20,
            ordering: true,
            searching: false,
            paging: false,
            info: false,
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
            },
            columnDefs: [{
                orderable: false,
                targets: [6]
            }]
        });

        // Initialize Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

    });

    // Toggle Pin
    function togglePin(threadId, isPinned) {
        const action = isPinned ? 'Unpin' : 'Pin';
        const icon = isPinned ? 'push_pin' : 'push_pin';

        Swal.fire({
            title: `${action} Thread?`,
            html: `Thread akan ${isPinned ? 'dihapus dari' : 'ditampilkan di'} bagian atas forum`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f6ad55',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: `Ya, ${action}!`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/forum/pin/') ?>${threadId}`;
            }
        });
    }

    // Toggle Lock
    function toggleLock(threadId, isLocked) {
        const action = isLocked ? 'Unlock' : 'Lock';

        Swal.fire({
            title: `${action} Thread?`,
            html: `
            Thread akan ${isLocked ? 'dibuka kembali' : 'dikunci'}.
            ${!isLocked ? 'Anggota tidak akan bisa membalas thread ini.' : ''}
        `,
            icon: 'warning',
            input: isLocked ? null : 'textarea',
            inputLabel: isLocked ? null : 'Alasan Lock (opsional)',
            inputPlaceholder: isLocked ? null : 'Misal: Pelanggaran aturan forum...',
            showCancelButton: true,
            confirmButtonColor: isLocked ? '#48bb78' : '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: `Ya, ${action}!`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value || '';

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?= base_url('admin/forum/lock/') ?>${threadId}`;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                if (reason) {
                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'reason';
                    reasonInput.value = reason;
                    form.appendChild(reasonInput);
                }

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Delete Thread
    function deleteThread(threadId, threadTitle) {
        Swal.fire({
            title: 'Hapus Thread?',
            html: `
            <strong>PERINGATAN!</strong><br>
            Thread "<em>${threadTitle}</em>" akan dihapus beserta semua replies.<br><br>
            Tindakan ini TIDAK DAPAT dibatalkan!
        `,
            icon: 'error',
            input: 'textarea',
            inputLabel: 'Alasan Penghapusan',
            inputPlaceholder: 'Misal: Konten melanggar aturan forum...',
            inputAttributes: {
                required: true
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan penghapusan harus diisi!'
                }
            },
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?= base_url('admin/forum/delete/') ?>${threadId}`;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = result.value;
                form.appendChild(reasonInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>