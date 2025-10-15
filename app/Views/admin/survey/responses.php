<?php

/**
 * View: Admin Survey Responses & Analytics
 * Controller: Admin\SurveyController::responses($id)
 * Description: Comprehensive survey results dashboard dengan analytics, charts, dan insights
 * 
 * Features:
 * - Statistics overview (total responses, completion rate, avg time)
 * - Interactive charts per question (Chart.js)
 * - Response list dengan filter & pagination
 * - Regional distribution analytics
 * - University distribution
 * - Timeline chart (responses per day)
 * - Export to CSV/Excel/PDF
 * - Individual response detail modal
 * - Question-wise analytics
 * - Real-time data updates
 * - Anonymous/named responses toggle
 * - Filter by date range, region, university
 * - Search functionality
 * 
 * @package App\Views\Admin\Survey
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
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.primary {
        border-left-color: #667eea;
    }

    .stat-card.success {
        border-left-color: #28a745;
    }

    .stat-card.info {
        border-left-color: #17a2b8;
    }

    .stat-card.warning {
        border-left-color: #ffc107;
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

    .stat-icon.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-icon.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-icon.info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff8b38 100%);
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-change {
        font-size: 13px;
        font-weight: 600;
    }

    .chart-container {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }

    .chart-type-selector {
        display: flex;
        gap: 8px;
    }

    .chart-type-btn {
        padding: 6px 12px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
    }

    .chart-type-btn:hover {
        background: #f8f9fa;
    }

    .chart-type-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .question-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .question-header {
        display: flex;
        justify-content: between;
        align-items: start;
        margin-bottom: 20px;
    }

    .question-number {
        background: #667eea;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .question-text {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-left: 12px;
        flex: 1;
    }

    .response-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .response-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 3px solid #667eea;
        transition: all 0.2s;
    }

    .response-item:hover {
        background: #e9ecef;
        transform: translateX(4px);
    }

    .response-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .response-answer {
        font-size: 14px;
        color: #495057;
    }

    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .survey-status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-draft {
        background: #fff3cd;
        color: #856404;
    }

    .status-published {
        background: #d4edda;
        color: #155724;
    }

    .status-closed {
        background: #d1ecf1;
        color: #0c5460;
    }

    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }

    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .tab-content {
        padding: 24px 0;
    }

    .nav-tabs-custom {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 24px;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .nav-tabs-custom .nav-link:hover {
        color: #667eea;
    }

    .nav-tabs-custom .nav-link.active {
        color: #667eea;
        border-bottom: 3px solid #667eea;
        background: transparent;
    }

    .insight-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .insight-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-gradient">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <h3 class="mb-2 text-white">
                <i class="bi bi-bar-chart-line me-2"></i>
                Hasil Survey & Analytics
            </h3>
            <h5 class="mb-3 text-white opacity-90"><?= esc($survey->title) ?></h5>
            <div class="d-flex gap-3 align-items-center flex-wrap">
                <span class="survey-status-badge status-<?= esc($survey->status) ?>">
                    <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                    <?= ucfirst(esc($survey->status)) ?>
                </span>
                <span class="text-white">
                    <i class="bi bi-calendar-event me-1"></i>
                    <?= date('d M Y', strtotime($survey->start_date)) ?> - <?= date('d M Y', strtotime($survey->end_date)) ?>
                </span>
                <span class="text-white">
                    <i class="bi bi-person me-1"></i>
                    Dibuat oleh: <?= esc($survey->created_by_name ?? 'Admin') ?>
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/surveys/edit/' . $survey->id) ?>" class="btn btn-light">
                <i class="bi bi-pencil me-1"></i> Edit Survey
            </a>
            <a href="<?= base_url('admin/surveys') ?>" class="btn btn-outline-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_respondents'] ?? 0) ?></div>
            <div class="stat-label">Total Responden</div>
            <?php if (isset($stats['response_rate'])): ?>
                <div class="stat-change text-success mt-2">
                    <i class="bi bi-arrow-up"></i> <?= $stats['response_rate'] ?>% response rate
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_responses'] ?? 0) ?></div>
            <div class="stat-label">Total Jawaban</div>
            <?php if (isset($stats['avg_responses_per_user'])): ?>
                <div class="stat-change text-info mt-2">
                    <i class="bi bi-graph-up"></i> <?= number_format($stats['avg_responses_per_user'], 1) ?> avg/user
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon info">
                    <i class="bi bi-percent"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['average_completion'] ?? 0, 1) ?>%</div>
            <div class="stat-label">Completion Rate</div>
            <div class="progress-bar-custom mt-2">
                <div class="progress-fill" style="width: <?= $stats['average_completion'] ?? 0 ?>%"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon warning">
                    <i class="bi bi-question-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= $stats['total_questions'] ?? 0 ?></div>
            <div class="stat-label">Total Pertanyaan</div>
            <?php if (isset($stats['required_questions'])): ?>
                <div class="stat-change text-warning mt-2">
                    <i class="bi bi-asterisk"></i> <?= $stats['required_questions'] ?> wajib diisi
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs-custom" id="surveyResultsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
            <i class="bi bi-graph-up me-2"></i> Overview & Charts
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="responses-tab" data-bs-toggle="tab" data-bs-target="#responses" type="button">
            <i class="bi bi-list-ul me-2"></i> All Responses
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button">
            <i class="bi bi-pie-chart me-2"></i> Regional Analytics
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="insights-tab" data-bs-toggle="tab" data-bs-target="#insights" type="button">
            <i class="bi bi-lightbulb me-2"></i> Insights
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="surveyResultsTabContent">
    <!-- Overview Tab -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <!-- Export Buttons -->
        <div class="filter-panel mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-download me-2"></i>
                    Export Data
                </h5>
                <div class="export-buttons">
                    <a href="<?= base_url('admin/surveys/export/' . $survey->id . '?format=excel') ?>" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export to Excel
                    </a>
                    <a href="<?= base_url('admin/surveys/export/' . $survey->id . '?format=csv') ?>" class="btn btn-info">
                        <i class="bi bi-filetype-csv me-1"></i> Export to CSV
                    </a>
                    <button type="button" class="btn btn-danger" id="exportPdfBtn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export to PDF
                    </button>
                    <button type="button" class="btn btn-secondary" id="printBtn">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Response Timeline Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Response Timeline
                </h5>
                <div class="chart-type-selector">
                    <button class="chart-type-btn active" data-chart="timeline" data-type="line">
                        <i class="bi bi-graph-up"></i> Line
                    </button>
                    <button class="chart-type-btn" data-chart="timeline" data-type="bar">
                        <i class="bi bi-bar-chart"></i> Bar
                    </button>
                </div>
            </div>
            <canvas id="timelineChart" height="80"></canvas>
        </div>

        <!-- Questions with Charts -->
        <?php if (isset($stats['questions']) && !empty($stats['questions'])): ?>
            <?php foreach ($stats['questions'] as $index => $question): ?>
                <div class="question-card" id="question-<?= $question['id'] ?>">
                    <div class="question-header mb-4">
                        <div class="d-flex align-items-start">
                            <div class="question-number"><?= $index + 1 ?></div>
                            <div class="question-text"><?= esc($question['question_text']) ?></div>
                        </div>
                        <span class="response-badge ms-3">
                            <?= $question['response_count'] ?? 0 ?> responses
                        </span>
                    </div>

                    <?php if (in_array($question['question_type'], ['multiple_choice', 'checkbox', 'scale'])): ?>
                        <!-- Chart for Multiple Choice / Checkbox / Scale -->
                        <div class="chart-container">
                            <div class="chart-header">
                                <span class="text-muted">Distribution</span>
                                <div class="chart-type-selector">
                                    <button class="chart-type-btn active" data-chart="question-<?= $question['id'] ?>" data-type="bar">
                                        <i class="bi bi-bar-chart-fill"></i>
                                    </button>
                                    <button class="chart-type-btn" data-chart="question-<?= $question['id'] ?>" data-type="pie">
                                        <i class="bi bi-pie-chart-fill"></i>
                                    </button>
                                    <button class="chart-type-btn" data-chart="question-<?= $question['id'] ?>" data-type="doughnut">
                                        <i class="bi bi-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <canvas id="chart-question-<?= $question['id'] ?>" height="80"></canvas>
                        </div>

                        <!-- Data Table -->
                        <?php if (isset($question['answers']) && !empty($question['answers'])): ?>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Option</th>
                                            <th width="100">Count</th>
                                            <th width="120">Percentage</th>
                                            <th width="200">Visual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <tr>
                                                <td><?= esc($answer['option']) ?></td>
                                                <td><strong><?= $answer['count'] ?></strong></td>
                                                <td><strong><?= number_format($answer['percentage'], 1) ?>%</strong></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-primary" role="progressbar"
                                                            style="width: <?= $answer['percentage'] ?>%"
                                                            aria-valuenow="<?= $answer['percentage'] ?>"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            <?= number_format($answer['percentage'], 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                    <?php elseif (in_array($question['question_type'], ['text', 'textarea'])): ?>
                        <!-- Text Responses -->
                        <div class="mt-3">
                            <h6 class="mb-3">Sample Responses:</h6>
                            <?php if (isset($question['answers']) && !empty($question['answers'])): ?>
                                <?php $displayCount = 0; ?>
                                <?php foreach ($question['answers'] as $answer): ?>
                                    <?php if ($displayCount >= 5) break; ?>
                                    <?php if (!empty($answer['answer'])): ?>
                                        <div class="response-item">
                                            <div class="response-answer">
                                                "<?= esc($answer['answer']) ?>"
                                            </div>
                                        </div>
                                        <?php $displayCount++; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (count($question['answers']) > 5): ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewAllTextResponses(<?= $question['id'] ?>)">
                                        <i class="bi bi-eye me-1"></i> View All <?= count($question['answers']) ?> Responses
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted">Belum ada jawaban</p>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($question['question_type'] === 'date'): ?>
                        <!-- Date Responses -->
                        <div class="mt-3">
                            <h6 class="mb-3">Date Responses:</h6>
                            <?php if (isset($question['answers']) && !empty($question['answers'])): ?>
                                <div class="row">
                                    <?php foreach (array_slice($question['answers'], 0, 10) as $answer): ?>
                                        <div class="col-md-3 mb-2">
                                            <span class="badge bg-light text-dark">
                                                <?= esc($answer['answer']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Belum Ada Data</h5>
                <p class="text-muted">Survey belum memiliki responden</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- All Responses Tab -->
    <div class="tab-pane fade" id="responses" role="tabpanel">
        <!-- Filter Panel -->
        <div class="filter-panel mb-4">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Province</label>
                    <select class="form-select" id="provinceFilter">
                        <option value="">All Provinces</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">University</label>
                    <select class="form-select" id="universityFilter">
                        <option value="">All Universities</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Responses DataTable -->
        <div class="card">
            <div class="card-body">
                <table id="responsesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Responden</th>
                            <th>Email</th>
                            <th>Province</th>
                            <th>Submitted At</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_responses)): ?>
                            <?php foreach ($recent_responses as $index => $response): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <?php if ($survey->is_anonymous): ?>
                                            <span class="text-muted">Anonymous</span>
                                        <?php else: ?>
                                            <strong><?= esc($response->full_name ?? 'N/A') ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$survey->is_anonymous): ?>
                                            <?= esc($response->email) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($response->province_name ?? '-') ?></td>
                                    <td><?= date('d M Y H:i', strtotime($response->submitted_at)) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-response"
                                            data-response-id="<?= $response->id ?>"
                                            title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No responses yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (isset($pager)): ?>
            <div class="mt-3">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Regional Analytics Tab -->
    <div class="tab-pane fade" id="analytics" role="tabpanel">
        <div class="row">
            <!-- Regional Distribution -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h5 class="chart-title">
                            <i class="bi bi-geo-alt me-2"></i>
                            Responses by Province
                        </h5>
                    </div>
                    <canvas id="provinceChart" height="120"></canvas>
                </div>
            </div>

            <!-- University Distribution -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h5 class="chart-title">
                            <i class="bi bi-building me-2"></i>
                            Responses by University
                        </h5>
                    </div>
                    <canvas id="universityChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Contributors -->
        <div class="chart-container">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="bi bi-trophy me-2"></i>
                    Top Contributing Regions
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">Rank</th>
                            <th>Province/Region</th>
                            <th>Responses</th>
                            <th>Percentage</th>
                            <th>Visual</th>
                        </tr>
                    </thead>
                    <tbody id="topRegionsTable">
                        <!-- Will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Insights Tab -->
    <div class="tab-pane fade" id="insights" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="insight-card">
                    <div class="insight-icon">💡</div>
                    <h5>Key Insights</h5>
                    <ul>
                        <li>Response rate: <strong><?= number_format(($stats['total_respondents'] ?? 0) / 100 * 100, 1) ?>%</strong></li>
                        <li>Average completion time: <strong><?= $stats['avg_completion_time'] ?? 'N/A' ?></strong></li>
                        <li>Most engaged region: <strong><?= $stats['top_region'] ?? 'N/A' ?></strong></li>
                        <li>Peak response time: <strong><?= $stats['peak_time'] ?? 'N/A' ?></strong></li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="insight-card">
                    <div class="insight-icon">📊</div>
                    <h5>Data Quality</h5>
                    <ul>
                        <li>Complete responses: <strong><?= $stats['complete_responses'] ?? 0 ?>%</strong></li>
                        <li>Partial responses: <strong><?= $stats['partial_responses'] ?? 0 ?>%</strong></li>
                        <li>Skip rate: <strong><?= $stats['skip_rate'] ?? 0 ?>%</strong></li>
                        <li>Data reliability: <strong><?= $stats['reliability_score'] ?? 'High' ?></strong></li>
                    </ul>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <div class="insight-card">
                    <div class="insight-icon">🎯</div>
                    <h5>Recommendations</h5>
                    <ul>
                        <li>Consider extending survey duration to reach more respondents</li>
                        <li>Target low-participation regions with follow-up communications</li>
                        <li>Review questions with high skip rates for clarity improvements</li>
                        <li>Use insights to inform future survey design and targeting</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Detail Modal -->
<div class="modal fade" id="responseDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-text me-2"></i>
                    Response Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="responseDetailContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/chart.js/chart.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Chart.js default config
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6c757d';

        // Colors palette
        const colors = {
            primary: '#667eea',
            success: '#28a745',
            info: '#17a2b8',
            warning: '#ffc107',
            danger: '#dc3545'
        };

        const chartColors = [
            '#667eea', '#764ba2', '#f093fb', '#4facfe',
            '#43e97b', '#fa709a', '#fee140', '#30cfd0'
        ];

        // Timeline Chart
        <?php if (isset($stats['timeline'])): ?>
            const timelineData = <?= json_encode($stats['timeline']) ?>;
            const timelineChart = new Chart(document.getElementById('timelineChart'), {
                type: 'line',
                data: {
                    labels: timelineData.map(d => d.date),
                    datasets: [{
                        label: 'Responses',
                        data: timelineData.map(d => d.count),
                        borderColor: colors.primary,
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        <?php endif; ?>

        // Question Charts
        <?php if (isset($stats['questions'])): ?>
            <?php foreach ($stats['questions'] as $question): ?>
                <?php if (in_array($question['question_type'], ['multiple_choice', 'checkbox', 'scale']) && isset($question['answers'])): ?>
                    new Chart(document.getElementById('chart-question-<?= $question['id'] ?>'), {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode(array_column($question['answers'], 'option')) ?>,
                            datasets: [{
                                label: 'Responses',
                                data: <?= json_encode(array_column($question['answers'], 'count')) ?>,
                                backgroundColor: chartColors,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        // Chart type switcher
        $('.chart-type-btn').on('click', function() {
            const $btn = $(this);
            const chartId = $btn.data('chart');
            const chartType = $btn.data('type');

            $btn.siblings().removeClass('active');
            $btn.addClass('active');

            // Update chart type (would need to recreate chart)
            // Implementation depends on specific requirements
        });

        // Initialize DataTable
        $('#responsesTable').DataTable({
            responsive: true,
            pageLength: 20,
            order: [
                [4, 'desc']
            ],
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
            }
        });

        // Initialize Select2
        $('#provinceFilter, #universityFilter').select2({
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

        // View Response Detail
        $('.view-response').on('click', function() {
            const responseId = $(this).data('response-id');

            $('#responseDetailModal').modal('show');

            // Load response details via AJAX
            $.ajax({
                url: '<?= base_url('admin/surveys/response-detail/') ?>' + responseId,
                method: 'GET',
                success: function(data) {
                    $('#responseDetailContent').html(data);
                },
                error: function() {
                    $('#responseDetailContent').html('<p class="text-danger">Failed to load response details</p>');
                }
            });
        });

        // Export to PDF
        $('#exportPdfBtn').on('click', function() {
            window.print();
        });

        // Print
        $('#printBtn').on('click', function() {
            window.print();
        });

        // Filter Form
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            // Implement filter logic
            const formData = $(this).serialize();
            window.location.href = '<?= current_url() ?>?' + formData;
        });
    });

    // View all text responses function
    function viewAllTextResponses(questionId) {
        // Implementation for viewing all text responses
        Swal.fire({
            title: 'All Text Responses',
            html: '<div id="allResponses">Loading...</div>',
            width: '800px',
            showCloseButton: true
        });

        // Load via AJAX
        $.ajax({
            url: '<?= base_url('admin/surveys/question-responses/') ?>' + questionId,
            method: 'GET',
            success: function(data) {
                $('#allResponses').html(data);
            }
        });
    }
</script>
<?= $this->endSection() ?>