<?php

/**
 * View: Admin Survey Management
 * Controller: App\Controllers\Admin\SurveyController::index()
 * Description: Survey management interface dengan statistics, actions, dan export
 * 
 * Features:
 * - Statistics cards (Total, Active, Draft, Closed surveys)
 * - Advanced filters (Status, Search)
 * - DataTable dengan survey list
 * - Status badges (Draft, Active, Closed, Expired)
 * - Response count & percentage
 * - Deadline display dengan countdown
 * - Actions: View, Edit, Publish/Close, View Results, Export, Delete
 * - Create new survey button
 * - Responsive design (mobile-first)
 * - SweetAlert confirmations
 * - Permission-based actions
 * 
 * @package App\Views\Admin\Survey
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">

<style>
    /* Survey Management Wrapper */
    .survey-wrapper {
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

    .page-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
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

    .page-actions .btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
    #surveyTable thead th {
        background: #f7fafc;
        color: #4a5568;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        padding: 14px 12px;
        border-bottom: 2px solid #e2e8f0;
    }

    #surveyTable tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        font-size: 14px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
    }

    #surveyTable tbody tr {
        transition: all 0.2s ease;
    }

    #surveyTable tbody tr:hover {
        background: #f7fafc;
    }

    /* Survey Info */
    .survey-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .survey-title {
        font-weight: 600;
        color: #2d3748;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .survey-title a {
        color: #667eea;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .survey-title a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .survey-description {
        font-size: 13px;
        color: #718096;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-badge.draft {
        background: #e2e8f0;
        color: #4a5568;
    }

    .status-badge.active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.closed {
        background: #fed7d7;
        color: #742a2a;
    }

    .status-badge.expired {
        background: #feebc8;
        color: #7c2d12;
    }

    .status-badge i {
        font-size: 14px;
    }

    /* Response Stats */
    .response-stats {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .response-count {
        font-size: 20px;
        font-weight: 700;
        color: #667eea;
    }

    .response-percentage {
        font-size: 12px;
        color: #a0aec0;
    }

    .response-bar {
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 4px;
    }

    .response-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.3s ease;
    }

    /* Deadline Display */
    .deadline-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .deadline-date {
        font-size: 13px;
        font-weight: 600;
        color: #2d3748;
    }

    .deadline-countdown {
        font-size: 12px;
        color: #718096;
    }

    .deadline-countdown.urgent {
        color: #f56565;
        font-weight: 700;
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

    .btn-action.edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-action.edit:hover {
        background: #d97706;
        color: white;
    }

    .btn-action.publish {
        background: #d1fae5;
        color: #059669;
    }

    .btn-action.publish:hover {
        background: #059669;
        color: white;
    }

    .btn-action.results {
        background: #e0e7ff;
        color: #5b21b6;
    }

    .btn-action.results:hover {
        background: #5b21b6;
        color: white;
    }

    .btn-action.export {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-action.export:hover {
        background: #1e40af;
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
        margin: 0 0 20px 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .survey-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
        }

        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-actions {
            width: 100%;
        }

        .page-actions .btn {
            width: 100%;
            justify-content: center;
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

<div class="survey-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <div class="page-header-content">
            <div>
                <h1>
                    <i class="material-icons-outlined">poll</i>
                    Kelola Survey
                </h1>
                <p>Buat dan kelola survey untuk anggota SPK</p>
            </div>

            <?php if (auth()->user()->can('survey.create')): ?>
                <div class="page-actions">
                    <a href="<?= base_url('admin/survey/create') ?>" class="btn btn-light">
                        <i class="material-icons-outlined">add_circle</i>
                        Buat Survey Baru
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Statistics Cards -->
    <div class="stats-grid animate-fade-in-up" style="animation-delay: 0.1s;">

        <div class="stat-card primary">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">poll</i>
            </div>
            <div class="stat-card-label">Total Survey</div>
            <div class="stat-card-value"><?= number_format($pager->getTotal()) ?></div>
        </div>

        <div class="stat-card success">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">play_circle</i>
            </div>
            <div class="stat-card-label">Survey Aktif</div>
            <div class="stat-card-value">
                <?php
                $activeCount = 0;
                foreach ($surveys as $survey) {
                    if ($survey->status === 'active') $activeCount++;
                }
                echo number_format($activeCount);
                ?>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">edit_note</i>
            </div>
            <div class="stat-card-label">Draft</div>
            <div class="stat-card-value">
                <?php
                $draftCount = 0;
                foreach ($surveys as $survey) {
                    if ($survey->status === 'draft') $draftCount++;
                }
                echo number_format($draftCount);
                ?>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">check_circle</i>
            </div>
            <div class="stat-card-label">Closed</div>
            <div class="stat-card-value">
                <?php
                $closedCount = 0;
                foreach ($surveys as $survey) {
                    if ($survey->status === 'closed') $closedCount++;
                }
                echo number_format($closedCount);
                ?>
            </div>
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

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="filterStatus">Status Survey</label>
                    <select name="status" id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
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
                        placeholder="Cari judul atau deskripsi..."
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
                Daftar Survey
                <?php if (!empty($surveys)): ?>
                    <span style="color: #718096; font-weight: 400; font-size: 14px;">
                        (<?= count($surveys) ?> survey)
                    </span>
                <?php endif; ?>
            </h3>
        </div>

        <?php if (!empty($surveys)): ?>
            <div class="table-responsive">
                <table id="surveyTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Survey</th>
                            <th>Status</th>
                            <th style="text-align: center;">Responses</th>
                            <th>Deadline</th>
                            <th>Dibuat</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($surveys as $survey): ?>
                            <tr>
                                <td>
                                    <div class="survey-info">
                                        <div class="survey-title">
                                            <a href="<?= base_url('admin/survey/edit/' . $survey->id) ?>">
                                                <?= esc($survey->title) ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($survey->description)): ?>
                                            <div class="survey-description">
                                                <?= esc($survey->description) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'draft';
                                    $statusIcon = 'edit_note';
                                    $statusText = 'Draft';

                                    if ($survey->status === 'active') {
                                        $statusClass = 'active';
                                        $statusIcon = 'play_circle';
                                        $statusText = 'Aktif';
                                    } elseif ($survey->status === 'closed') {
                                        $statusClass = 'closed';
                                        $statusIcon = 'cancel';
                                        $statusText = 'Closed';
                                    }

                                    // Check if expired
                                    if ($survey->status === 'active' && !empty($survey->end_date)) {
                                        $endDate = strtotime($survey->end_date);
                                        if ($endDate < time()) {
                                            $statusClass = 'expired';
                                            $statusIcon = 'schedule';
                                            $statusText = 'Expired';
                                        }
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="material-icons-outlined"><?= $statusIcon ?></i>
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="response-stats">
                                        <div class="response-count"><?= number_format($survey->response_count ?? 0) ?></div>
                                        <?php
                                        // Calculate percentage (assume target is all active members)
                                        $targetCount = 100; // You can get this from stats
                                        $responsePercentage = $targetCount > 0
                                            ? min(100, round((($survey->response_count ?? 0) / $targetCount) * 100))
                                            : 0;
                                        ?>
                                        <div class="response-percentage"><?= $responsePercentage ?>% partisipasi</div>
                                        <div class="response-bar">
                                            <div class="response-bar-fill" style="width: <?= $responsePercentage ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($survey->end_date)): ?>
                                        <div class="deadline-info">
                                            <div class="deadline-date">
                                                <?= date('d M Y', strtotime($survey->end_date)) ?>
                                            </div>
                                            <?php
                                            $endDate = strtotime($survey->end_date);
                                            $now = time();
                                            $diff = $endDate - $now;

                                            if ($diff > 0) {
                                                $days = floor($diff / 86400);
                                                if ($days > 0) {
                                                    $urgentClass = $days <= 3 ? 'urgent' : '';
                                                    echo '<div class="deadline-countdown ' . $urgentClass . '">' . $days . ' hari lagi</div>';
                                                } else {
                                                    $hours = floor($diff / 3600);
                                                    echo '<div class="deadline-countdown urgent">' . $hours . ' jam lagi</div>';
                                                }
                                            } else {
                                                echo '<div class="deadline-countdown urgent">Sudah berakhir</div>';
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #a0aec0; font-size: 13px;">Tidak ada batas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #4a5568;">
                                        <?= date('d M Y', strtotime($survey->created_at)) ?>
                                    </div>
                                    <div style="font-size: 12px; color: #a0aec0;">
                                        <?= esc($survey->created_by_name ?? 'Admin') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- View Button -->
                                        <a
                                            href="<?= base_url('admin/survey/preview/' . $survey->id) ?>"
                                            class="btn-action view"
                                            data-bs-toggle="tooltip"
                                            title="Preview Survey">
                                            <i class="material-icons-outlined">visibility</i>
                                        </a>

                                        <!-- Edit Button -->
                                        <?php if ($survey->status === 'draft' || $survey->status === 'active'): ?>
                                            <a
                                                href="<?= base_url('admin/survey/edit/' . $survey->id) ?>"
                                                class="btn-action edit"
                                                data-bs-toggle="tooltip"
                                                title="Edit Survey">
                                                <i class="material-icons-outlined">edit</i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Publish/Close Button -->
                                        <?php if ($survey->status === 'draft'): ?>
                                            <button
                                                type="button"
                                                class="btn-action publish"
                                                onclick="publishSurvey(<?= $survey->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Publish Survey">
                                                <i class="material-icons-outlined">publish</i>
                                            </button>
                                        <?php elseif ($survey->status === 'active'): ?>
                                            <button
                                                type="button"
                                                class="btn-action delete"
                                                onclick="closeSurvey(<?= $survey->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Close Survey">
                                                <i class="material-icons-outlined">cancel</i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- View Results Button -->
                                        <?php if (($survey->response_count ?? 0) > 0): ?>
                                            <a
                                                href="<?= base_url('admin/survey/results/' . $survey->id) ?>"
                                                class="btn-action results"
                                                data-bs-toggle="tooltip"
                                                title="Lihat Hasil">
                                                <i class="material-icons-outlined">analytics</i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Export Button -->
                                        <?php if (($survey->response_count ?? 0) > 0): ?>
                                            <button
                                                type="button"
                                                class="btn-action export"
                                                onclick="exportSurvey(<?= $survey->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Export Data">
                                                <i class="material-icons-outlined">download</i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Delete Button -->
                                        <?php if ($survey->status === 'draft' || $survey->status === 'closed'): ?>
                                            <button
                                                type="button"
                                                class="btn-action delete"
                                                onclick="deleteSurvey(<?= $survey->id ?>, '<?= esc($survey->title) ?>')"
                                                data-bs-toggle="tooltip"
                                                title="Hapus Survey">
                                                <i class="material-icons-outlined">delete</i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan <?= count($surveys) ?> dari <?= $pager->getTotal() ?> survey
                </div>
                <?= $pager->links('default', 'custom_pagination') ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="material-icons-outlined">poll</i>
                <h3>Belum Ada Survey</h3>
                <p>Belum ada survey yang dibuat atau sesuai dengan filter yang diterapkan</p>
                <?php if (auth()->user()->can('survey.create')): ?>
                    <a href="<?= base_url('admin/survey/create') ?>" class="btn btn-primary">
                        <i class="material-icons-outlined">add_circle</i>
                        Buat Survey Pertama
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables JS -->
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Initialize DataTables
        $('#surveyTable').DataTable({
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
                targets: [5]
            }]
        });

        // Initialize Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

    });

    // Publish Survey
    function publishSurvey(surveyId) {
        Swal.fire({
            title: 'Publish Survey?',
            html: 'Survey akan dipublikasikan dan dapat diakses oleh anggota.<br><br>Pastikan semua pertanyaan sudah lengkap.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Publish!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/survey/publish/') ?>${surveyId}`;
            }
        });
    }

    // Close Survey
    function closeSurvey(surveyId) {
        Swal.fire({
            title: 'Close Survey?',
            html: 'Survey akan ditutup dan anggota tidak dapat mengisi lagi.<br><br>Anda masih dapat melihat hasil survey.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Close!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/survey/close/') ?>${surveyId}`;
            }
        });
    }

    // Export Survey
    function exportSurvey(surveyId) {
        Swal.fire({
            title: 'Export Data Survey',
            text: 'Pilih format export:',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Excel (.xlsx)',
            denyButtonText: 'CSV (.csv)',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#48bb78',
            denyButtonColor: '#4299e1'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/survey/export/') ?>${surveyId}?format=excel`;
            } else if (result.isDenied) {
                window.location.href = `<?= base_url('admin/survey/export/') ?>${surveyId}?format=csv`;
            }
        });
    }

    // Delete Survey
    function deleteSurvey(surveyId, surveyTitle) {
        Swal.fire({
            title: 'Hapus Survey?',
            html: `
            <strong>PERINGATAN!</strong><br>
            Survey "<em>${surveyTitle}</em>" akan dihapus beserta semua responses.<br><br>
            Tindakan ini TIDAK DAPAT dibatalkan!
        `,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            input: 'checkbox',
            inputPlaceholder: 'Saya memahami konsekuensinya',
            inputValidator: (result) => {
                return !result && 'Anda harus mencentang checkbox untuk melanjutkan'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/survey/delete/') ?>${surveyId}`;
            }
        });
    }
</script>
<?= $this->endSection() ?>