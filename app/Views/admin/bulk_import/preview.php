<?php

/**
 * View: Admin Bulk Import Preview
 * Controller: App\Controllers\Admin\BulkImportController::preview()
 * Description: Preview & validate import data sebelum diproses dengan error handling
 * 
 * Features:
 * - Statistics summary cards (Total, Valid, Invalid rows)
 * - Data preview table dengan pagination
 * - Row-by-row validation status
 * - Error messages per row dengan details
 * - Color-coded status indicators
 * - Confirm/Cancel actions
 * - Export error log
 * - Fix & re-upload option
 * - Responsive design (mobile-first)
 * - Beautiful animations
 * 
 * @package App\Views\Admin\BulkImport
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
<!-- DataTables CSS -->
<link href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>" rel="stylesheet">

<style>
    /* Preview Wrapper */
    .preview-wrapper {
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

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        margin-bottom: 12px;
    }

    .back-button:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
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

    /* File Info Card */
    .file-info-card {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .file-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 28px;
    }

    .file-details {
        flex: 1;
    }

    .file-name {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .file-meta {
        font-size: 13px;
        color: #718096;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card.total {
        border-left-color: #667eea;
    }

    .stat-card.valid {
        border-left-color: #48bb78;
    }

    .stat-card.invalid {
        border-left-color: #f56565;
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .stat-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
    }

    .stat-card.total .stat-card-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.valid .stat-card-icon {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }

    .stat-card.invalid .stat-card-icon {
        background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    }

    .stat-card-label {
        font-size: 13px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .stat-card-value {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-card-percentage {
        font-size: 14px;
        font-weight: 600;
    }

    .stat-card.valid .stat-card-percentage {
        color: #48bb78;
    }

    .stat-card.invalid .stat-card-percentage {
        color: #f56565;
    }

    /* Alert Box */
    .alert-box {
        background: white;
        border-left: 4px solid;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .alert-box.success {
        border-left-color: #48bb78;
        background: #f0fff4;
    }

    .alert-box.warning {
        border-left-color: #f6ad55;
        background: #fffaf0;
    }

    .alert-box.danger {
        border-left-color: #f56565;
        background: #fff5f5;
    }

    .alert-box i {
        font-size: 24px;
        flex-shrink: 0;
    }

    .alert-box.success i {
        color: #2f855a;
    }

    .alert-box.warning i {
        color: #dd6b20;
    }

    .alert-box.danger i {
        color: #c53030;
    }

    .alert-box-content {
        flex: 1;
    }

    .alert-box-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #2d3748;
    }

    .alert-box-text {
        font-size: 14px;
        color: #4a5568;
        margin: 0;
    }

    /* Preview Table Section */
    .preview-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #667eea;
        font-size: 28px;
    }

    /* Preview Table */
    .preview-table {
        width: 100%;
        border-collapse: collapse;
    }

    .preview-table thead th {
        background: #f7fafc;
        color: #4a5568;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        padding: 12px 10px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .preview-table tbody td {
        padding: 12px 10px;
        font-size: 13px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-table tbody tr:hover {
        background: #f7fafc;
    }

    .preview-table tbody tr.error-row {
        background: #fff5f5;
    }

    .preview-table tbody tr.error-row:hover {
        background: #fed7d7;
    }

    /* Row Status */
    .row-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .row-status.valid {
        background: #c6f6d5;
        color: #22543d;
    }

    .row-status.invalid {
        background: #fed7d7;
        color: #742a2a;
    }

    .row-status i {
        font-size: 14px;
    }

    /* Error Message */
    .error-message {
        background: #fed7d7;
        border-left: 4px solid #f56565;
        padding: 8px 12px;
        border-radius: 6px;
        margin-top: 4px;
        font-size: 12px;
        color: #742a2a;
    }

    .error-message strong {
        display: block;
        margin-bottom: 2px;
        font-size: 11px;
        text-transform: uppercase;
    }

    /* Error List Section */
    .error-list-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .error-item {
        background: #fff5f5;
        border-left: 4px solid #f56565;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .error-item:last-child {
        margin-bottom: 0;
    }

    .error-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .error-row-number {
        font-size: 14px;
        font-weight: 700;
        color: #742a2a;
    }

    .error-count-badge {
        background: #f56565;
        color: white;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .error-item-data {
        background: white;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
    }

    .error-item-data-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 8px;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .error-item-data-row:last-child {
        margin-bottom: 0;
    }

    .error-item-data-label {
        font-weight: 600;
        color: #4a5568;
    }

    .error-item-data-value {
        color: #2d3748;
    }

    .error-item-messages {
        padding-left: 0;
        margin: 0;
        list-style: none;
    }

    .error-item-messages li {
        font-size: 13px;
        color: #742a2a;
        margin-bottom: 4px;
        padding-left: 20px;
        position: relative;
    }

    .error-item-messages li::before {
        content: '✗';
        position: absolute;
        left: 0;
        color: #f56565;
        font-weight: 700;
    }

    /* Action Buttons */
    .action-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .action-section .btn {
        flex: 1;
        min-width: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 24px;
        font-weight: 700;
        font-size: 15px;
        border-radius: 8px;
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
        font-size: 18px;
        font-weight: 700;
        color: #718096;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay.active {
        display: flex;
    }

    .loading-content {
        background: white;
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        text-align: center;
        max-width: 400px;
    }

    .loading-spinner {
        width: 64px;
        height: 64px;
        border: 6px solid #f3f4f6;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .loading-text {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .loading-subtext {
        font-size: 14px;
        color: #718096;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .preview-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .file-info-card {
            flex-direction: column;
            text-align: center;
        }

        .action-section {
            flex-direction: column;
        }

        .action-section .btn {
            width: 100%;
        }

        .error-item-data-row {
            grid-template-columns: 1fr;
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

<div class="preview-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <a href="<?= base_url('admin/bulk-import') ?>" class="back-button">
            <i class="material-icons-outlined">arrow_back</i>
            Kembali ke Upload
        </a>

        <div class="page-header-content">
            <div>
                <h1>
                    <i class="material-icons-outlined">preview</i>
                    Preview Data Import
                </h1>
                <p>Validasi data sebelum melakukan import ke database</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- File Info Card -->
    <div class="file-info-card animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="file-icon">
            <i class="material-icons-outlined">description</i>
        </div>
        <div class="file-details">
            <div class="file-name"><?= esc($file_info['original_name'] ?? 'unknown.xlsx') ?></div>
            <div class="file-meta">
                Uploaded: <?= date('d M Y H:i', strtotime($file_info['uploaded_at'] ?? 'now')) ?>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid animate-fade-in-up" style="animation-delay: 0.2s;">

        <div class="stat-card total">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="material-icons-outlined">dataset</i>
                </div>
            </div>
            <div class="stat-card-label">Total Baris Data</div>
            <div class="stat-card-value"><?= number_format($total_rows ?? 0) ?></div>
        </div>

        <div class="stat-card valid">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="material-icons-outlined">check_circle</i>
                </div>
            </div>
            <div class="stat-card-label">Data Valid</div>
            <div class="stat-card-value"><?= number_format($valid_rows ?? 0) ?></div>
            <?php
            $validPercentage = ($total_rows ?? 0) > 0
                ? round((($valid_rows ?? 0) / $total_rows) * 100, 1)
                : 0;
            ?>
            <div class="stat-card-percentage">
                <?= $validPercentage ?>% dari total
            </div>
        </div>

        <div class="stat-card invalid">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="material-icons-outlined">error</i>
                </div>
            </div>
            <div class="stat-card-label">Data Invalid</div>
            <div class="stat-card-value"><?= number_format($invalid_rows ?? 0) ?></div>
            <?php
            $invalidPercentage = ($total_rows ?? 0) > 0
                ? round((($invalid_rows ?? 0) / $total_rows) * 100, 1)
                : 0;
            ?>
            <div class="stat-card-percentage">
                <?= $invalidPercentage ?>% dari total
            </div>
        </div>

    </div>

    <!-- Status Alert -->
    <?php if (($invalid_rows ?? 0) === 0): ?>
        <div class="alert-box success animate-fade-in-up" style="animation-delay: 0.3s;">
            <i class="material-icons-outlined">check_circle</i>
            <div class="alert-box-content">
                <div class="alert-box-title">Semua Data Valid! ✓</div>
                <p class="alert-box-text">
                    Seluruh data telah melewati validasi. Anda dapat melanjutkan proses import.
                </p>
            </div>
        </div>
    <?php elseif (($valid_rows ?? 0) > 0 && ($invalid_rows ?? 0) > 0): ?>
        <div class="alert-box warning animate-fade-in-up" style="animation-delay: 0.3s;">
            <i class="material-icons-outlined">warning</i>
            <div class="alert-box-content">
                <div class="alert-box-title">Terdapat Data Invalid</div>
                <p class="alert-box-text">
                    Ditemukan <?= number_format($invalid_rows) ?> baris data yang tidak valid.
                    Anda dapat melanjutkan import hanya untuk data valid, atau memperbaiki data invalid terlebih dahulu.
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert-box danger animate-fade-in-up" style="animation-delay: 0.3s;">
            <i class="material-icons-outlined">error</i>
            <div class="alert-box-content">
                <div class="alert-box-title">Semua Data Invalid!</div>
                <p class="alert-box-text">
                    Tidak ada data valid yang dapat diimpor. Silakan perbaiki file dan upload ulang.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Preview Data Table -->
    <?php if (!empty($preview_data)): ?>
        <div class="preview-section animate-fade-in-up" style="animation-delay: 0.4s;">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="material-icons-outlined">table_view</i>
                    Preview Data (<?= min(20, count($preview_data)) ?> dari <?= count($preview_data) ?> baris)
                </h2>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportPreview()">
                    <i class="material-icons-outlined" style="font-size: 16px;">download</i>
                    Export Preview
                </button>
            </div>

            <div class="table-responsive">
                <table class="preview-table" id="previewTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Baris</th>
                            <th style="width: 80px;">Status</th>
                            <th>Email</th>
                            <th>Nama Lengkap</th>
                            <th>No. Telepon</th>
                            <th>Provinsi</th>
                            <th>Universitas</th>
                            <th>Posisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $displayLimit = 20;
                        $displayData = array_slice($preview_data, 0, $displayLimit);
                        foreach ($displayData as $index => $row):
                            $rowNumber = $index + 2; // +2 karena row 1 adalah header
                            $hasError = !empty($errors[$rowNumber]);
                            $rowClass = $hasError ? 'error-row' : '';
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td><strong><?= $rowNumber ?></strong></td>
                                <td>
                                    <?php if ($hasError): ?>
                                        <span class="row-status invalid">
                                            <i class="material-icons-outlined">error</i>
                                            Invalid
                                        </span>
                                    <?php else: ?>
                                        <span class="row-status valid">
                                            <i class="material-icons-outlined">check_circle</i>
                                            Valid
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($row['email'] ?? '-') ?></td>
                                <td><?= esc($row['full_name'] ?? '-') ?></td>
                                <td><?= esc($row['phone'] ?? '-') ?></td>
                                <td><?= esc($row['province_name'] ?? '-') ?></td>
                                <td><?= esc($row['university_name'] ?? '-') ?></td>
                                <td><?= esc($row['position_type'] ?? '-') ?></td>
                            </tr>
                            <?php if ($hasError): ?>
                                <tr class="error-row">
                                    <td colspan="8">
                                        <div class="error-message">
                                            <strong>Error:</strong>
                                            <?= implode('; ', $errors[$rowNumber]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (count($preview_data) > $displayLimit): ?>
                <div style="text-align: center; margin-top: 16px; color: #718096; font-size: 14px;">
                    Menampilkan <?= $displayLimit ?> baris pertama.
                    Total: <?= count($preview_data) ?> baris.
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <!-- Error List Section -->
    <?php if (!empty($errors) && count($errors) > 0): ?>
        <div class="error-list-section animate-fade-in-up" style="animation-delay: 0.5s;">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="material-icons-outlined">error_outline</i>
                    Detail Error (<?= count($errors) ?> baris)
                </h2>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportErrors()">
                    <i class="material-icons-outlined" style="font-size: 16px;">download</i>
                    Export Error Log
                </button>
            </div>

            <div style="max-height: 600px; overflow-y: auto;">
                <?php foreach ($errors as $rowNumber => $rowErrors): ?>
                    <?php
                    $rowIndex = $rowNumber - 2; // Convert back to array index
                    $rowData = $preview_data[$rowIndex] ?? [];
                    ?>
                    <div class="error-item">
                        <div class="error-item-header">
                            <span class="error-row-number">Baris <?= $rowNumber ?></span>
                            <span class="error-count-badge"><?= count($rowErrors) ?> error</span>
                        </div>

                        <div class="error-item-data">
                            <?php if (!empty($rowData['email'])): ?>
                                <div class="error-item-data-row">
                                    <div class="error-item-data-label">Email:</div>
                                    <div class="error-item-data-value"><?= esc($rowData['email']) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($rowData['full_name'])): ?>
                                <div class="error-item-data-row">
                                    <div class="error-item-data-label">Nama:</div>
                                    <div class="error-item-data-value"><?= esc($rowData['full_name']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <ul class="error-item-messages">
                            <?php foreach ($rowErrors as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-section animate-fade-in-up" style="animation-delay: 0.6s;">

        <a href="<?= base_url('admin/bulk-import') ?>" class="btn btn-outline-secondary">
            <i class="material-icons-outlined">close</i>
            Batal & Upload Ulang
        </a>

        <?php if (($valid_rows ?? 0) > 0): ?>
            <form method="POST" action="<?= base_url('admin/bulk-import/process') ?>" id="importForm" style="flex: 1;">
                <?= csrf_field() ?>

                <button type="submit" class="btn btn-success" id="proceedBtn" style="width: 100%;">
                    <i class="material-icons-outlined">check_circle</i>
                    <?php if (($invalid_rows ?? 0) > 0): ?>
                        Import <?= number_format($valid_rows) ?> Data Valid
                    <?php else: ?>
                        Lanjutkan Import (<?= number_format($valid_rows) ?> data)
                    <?php endif; ?>
                </button>
            </form>
        <?php endif; ?>

    </div>

</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text">Memproses Import...</div>
        <div class="loading-subtext">Mohon tunggu, sedang menyimpan data ke database</div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
<!-- DataTables JS -->
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Initialize DataTable for preview
        $('#previewTable').DataTable({
            responsive: true,
            pageLength: 20,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
            }
        });

        // Import Form Submit
        $('#importForm').on('submit', function(e) {
            e.preventDefault();

            const validRows = <?= $valid_rows ?? 0 ?>;
            const invalidRows = <?= $invalid_rows ?? 0 ?>;

            let message = `Anda akan mengimpor ${validRows} data valid ke database.`;

            if (invalidRows > 0) {
                message += `\n\n${invalidRows} data invalid akan diabaikan.`;
            }

            Swal.fire({
                title: 'Konfirmasi Import',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#48bb78',
                cancelButtonColor: '#cbd5e0',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();

                    // Submit form
                    this.submit();
                }
            });
        });

    });

    // Export Preview
    function exportPreview() {
        Swal.fire({
            icon: 'info',
            title: 'Export Preview',
            text: 'Fitur export preview akan segera tersedia',
            confirmButtonColor: '#667eea'
        });
    }

    // Export Errors
    function exportErrors() {
        Swal.fire({
            icon: 'info',
            title: 'Export Error Log',
            text: 'Error log akan didownload dalam format Excel',
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true
        });

        // TODO: Implement actual export functionality
        // window.location.href = '<?= base_url('admin/bulk-import/export-errors') ?>';
    }

    // Show Loading
    function showLoading() {
        $('#loadingOverlay').addClass('active');
    }

    // Hide Loading
    function hideLoading() {
        $('#loadingOverlay').removeClass('active');
    }

    // Hide loading on page load
    window.addEventListener('load', function() {
        hideLoading();
    });
</script>
<?= $this->endSection() ?>