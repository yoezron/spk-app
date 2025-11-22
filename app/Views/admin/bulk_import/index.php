<?php

/**
 * View: Admin Bulk Import
 * Controller: App\Controllers\Admin\BulkImportController
 * Description: Bulk import members from Excel/CSV dengan preview, validation, dan history
 * 
 * Features:
 * - Drag & Drop file upload area
 * - Download Excel template with instructions
 * - File validation (size, type, format)
 * - Recent import history table
 * - Import statistics cards
 * - Step-by-step instructions
 * - File format requirements
 * - Progress indicators
 * - Error handling & validation
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    /* Bulk Import Wrapper */
    .bulk-import-wrapper {
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

    /* Main Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    /* Upload Section */
    .upload-section {
        background: white;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #667eea;
        font-size: 28px;
    }

    /* Upload Area */
    .upload-area {
        border: 3px dashed #cbd5e0;
        border-radius: 12px;
        padding: 48px 32px;
        text-align: center;
        background: #f7fafc;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .upload-area:hover {
        border-color: #667eea;
        background: #ebf4ff;
    }

    .upload-area.dragover {
        border-color: #667eea;
        background: #ebf4ff;
        transform: scale(1.02);
    }

    .upload-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 40px;
    }

    .upload-text {
        margin-bottom: 12px;
    }

    .upload-text-primary {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .upload-text-secondary {
        font-size: 14px;
        color: #718096;
    }

    .upload-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 16px;
    }

    .upload-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
    }

    .file-input {
        display: none;
    }

    /* File Requirements */
    .file-requirements {
        background: #f7fafc;
        border-radius: 8px;
        padding: 16px;
        margin-top: 24px;
        border-left: 4px solid #4299e1;
    }

    .file-requirements h4 {
        font-size: 14px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .file-requirements h4 i {
        color: #4299e1;
        font-size: 20px;
    }

    .file-requirements ul {
        margin: 0;
        padding-left: 24px;
        list-style: none;
    }

    .file-requirements li {
        font-size: 13px;
        color: #4a5568;
        margin-bottom: 8px;
        position: relative;
    }

    .file-requirements li::before {
        content: '✓';
        position: absolute;
        left: -20px;
        color: #48bb78;
        font-weight: 700;
    }

    /* Instructions Section */
    .instructions-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .instruction-step {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .instruction-step:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .step-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-size: 15px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .step-description {
        font-size: 13px;
        color: #718096;
        margin: 0;
    }

    .download-template-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 20px;
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
    }

    .download-template-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(72, 187, 120, 0.3);
    }

    /* Statistics Cards */
    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

    .stat-card.danger {
        border-left-color: #f56565;
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

    .stat-card.danger .stat-card-icon {
        background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
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

    /* Recent Imports Table */
    .history-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .history-table thead th {
        background: #f7fafc;
        color: #4a5568;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
    }

    .history-table tbody td {
        padding: 14px 12px;
        font-size: 14px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
    }

    .history-table tbody tr:hover {
        background: #f7fafc;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-badge.success {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.warning {
        background: #feebc8;
        color: #7c2d12;
    }

    .status-badge.danger {
        background: #fed7d7;
        color: #742a2a;
    }

    .status-badge i {
        font-size: 14px;
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

    .alert-box.info {
        border-left-color: #4299e1;
        background: #ebf8ff;
    }

    .alert-box.warning {
        border-left-color: #f6ad55;
        background: #fffaf0;
    }

    .alert-box i {
        font-size: 24px;
        flex-shrink: 0;
    }

    .alert-box.info i {
        color: #2b6cb0;
    }

    .alert-box.warning i {
        color: #dd6b20;
    }

    .alert-box-content {
        flex: 1;
    }

    .alert-box-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #2d3748;
    }

    .alert-box-text {
        font-size: 13px;
        color: #4a5568;
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
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .bulk-import-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
        }

        .upload-section,
        .instructions-section,
        .history-section {
            padding: 20px;
        }

        .stats-section {
            grid-template-columns: 1fr;
        }

        .history-table {
            font-size: 12px;
        }

        .history-table thead th,
        .history-table tbody td {
            padding: 8px;
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

<div class="bulk-import-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <h1>
            <i class="material-icons-outlined">cloud_upload</i>
            Import Data Anggota
        </h1>
        <p>Upload file Excel atau CSV untuk mengimpor data anggota secara massal</p>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Info Alert -->
    <div class="alert-box info animate-fade-in-up" style="animation-delay: 0.1s;">
        <i class="material-icons-outlined">info</i>
        <div class="alert-box-content">
            <div class="alert-box-title">Panduan Import Data</div>
            <p class="alert-box-text">
                Unduh template Excel terlebih dahulu, isi data sesuai format, lalu upload file untuk preview dan validasi sebelum melakukan import.
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-section animate-fade-in-up" style="animation-delay: 0.2s;">

        <div class="stat-card primary">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">upload_file</i>
            </div>
            <div class="stat-card-label">Total Import</div>
            <div class="stat-card-value"><?= count($recent_imports ?? []) ?></div>
        </div>

        <div class="stat-card success">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">check_circle</i>
            </div>
            <div class="stat-card-label">Berhasil</div>
            <div class="stat-card-value">
                <?php
                $successCount = 0;
                foreach ($recent_imports ?? [] as $import) {
                    if ($import->status === 'success') $successCount++;
                }
                echo $successCount;
                ?>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">warning</i>
            </div>
            <div class="stat-card-label">Dengan Warning</div>
            <div class="stat-card-value">
                <?php
                $warningCount = 0;
                foreach ($recent_imports ?? [] as $import) {
                    if ($import->status === 'partial') $warningCount++;
                }
                echo $warningCount;
                ?>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-card-icon">
                <i class="material-icons-outlined">error</i>
            </div>
            <div class="stat-card-label">Gagal</div>
            <div class="stat-card-value">
                <?php
                $failedCount = 0;
                foreach ($recent_imports ?? [] as $import) {
                    if ($import->status === 'failed') $failedCount++;
                }
                echo $failedCount;
                ?>
            </div>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="content-grid animate-fade-in-up" style="animation-delay: 0.3s;">

        <!-- Upload Section -->
        <div class="upload-section">
            <h2 class="section-title">
                <i class="material-icons-outlined">cloud_upload</i>
                Upload File Import
            </h2>

            <form id="uploadForm" method="POST" action="<?= base_url('admin/bulk-import/preview') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Upload Area -->
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="material-icons-outlined">cloud_upload</i>
                    </div>

                    <div class="upload-text">
                        <div class="upload-text-primary">Drag & Drop file di sini</div>
                        <div class="upload-text-secondary">atau</div>
                    </div>

                    <button type="button" class="upload-button" onclick="document.getElementById('fileInput').click()">
                        <i class="material-icons-outlined">folder_open</i>
                        Pilih File
                    </button>

                    <input
                        type="file"
                        id="fileInput"
                        name="file"
                        class="file-input"
                        accept=".xlsx,.xls,.csv"
                        required>
                </div>

                <!-- Selected File Info -->
                <div id="fileInfo" style="display: none; margin-top: 20px;">
                    <div style="background: #f0fff4; border: 2px solid #48bb78; border-radius: 8px; padding: 16px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="material-icons-outlined" style="color: #48bb78; font-size: 32px;">description</i>
                            <div style="flex: 1;">
                                <div id="fileName" style="font-weight: 700; color: #2d3748; margin-bottom: 4px;"></div>
                                <div id="fileSize" style="font-size: 13px; color: #718096;"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                <i class="material-icons-outlined" style="font-size: 16px;">close</i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="btn btn-primary btn-lg"
                    id="submitBtn"
                    style="width: 100%; margin-top: 24px; display: none;">
                    <i class="material-icons-outlined">preview</i>
                    Preview & Validasi Data
                </button>

            </form>

            <!-- File Requirements -->
            <div class="file-requirements">
                <h4>
                    <i class="material-icons-outlined">rule</i>
                    Persyaratan File
                </h4>
                <ul>
                    <li>Format file: Excel (.xlsx, .xls) atau CSV (.csv)</li>
                    <li>Ukuran maksimal: <?= $max_file_size ?>MB</li>
                    <li>Maksimal 1000 baris data per file</li>
                    <li>File harus menggunakan template yang disediakan</li>
                    <li>Pastikan tidak ada baris kosong di tengah data</li>
                </ul>
            </div>

        </div>

        <!-- Instructions Section -->
        <div class="instructions-section">
            <h2 class="section-title">
                <i class="material-icons-outlined">list_alt</i>
                Langkah-Langkah Import
            </h2>

            <div class="instruction-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Download Template</div>
                    <p class="step-description">
                        Unduh template Excel yang sudah disediakan dengan format yang sesuai
                    </p>
                </div>
            </div>

            <div class="instruction-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Isi Data Anggota</div>
                    <p class="step-description">
                        Isi data anggota sesuai kolom yang tersedia. Perhatikan format dan validasi di sheet Instructions
                    </p>
                </div>
            </div>

            <div class="instruction-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Upload File</div>
                    <p class="step-description">
                        Upload file yang sudah diisi menggunakan form di sebelah kiri
                    </p>
                </div>
            </div>

            <div class="instruction-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Preview & Validasi</div>
                    <p class="step-description">
                        Sistem akan memvalidasi data dan menampilkan preview. Periksa error jika ada
                    </p>
                </div>
            </div>

            <div class="instruction-step">
                <div class="step-number">5</div>
                <div class="step-content">
                    <div class="step-title">Konfirmasi Import</div>
                    <p class="step-description">
                        Jika semua data valid, konfirmasi untuk memulai proses import
                    </p>
                </div>
            </div>

            <!-- Download Template Button -->
            <button
                type="button"
                class="download-template-btn"
                onclick="downloadTemplate()">
                <i class="material-icons-outlined" style="font-size: 24px;">download</i>
                Download Template Excel
            </button>

        </div>

    </div>

    <!-- Recent Import History -->
    <div class="history-section animate-fade-in-up" style="animation-delay: 0.4s;">
        <h2 class="section-title">
            <i class="material-icons-outlined">history</i>
            Riwayat Import Terbaru
        </h2>

        <?php if (!empty($recent_imports)): ?>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>File Name</th>
                            <th>Total Data</th>
                            <th>Berhasil</th>
                            <th>Gagal</th>
                            <th>Status</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_imports as $import): ?>
                            <tr>
                                <td>
                                    <?= date('d M Y H:i', strtotime($import->created_at)) ?>
                                </td>
                                <td>
                                    <strong><?= esc($import->filename ?? 'N/A') ?></strong>
                                </td>
                                <td>
                                    <strong><?= number_format($import->total_rows ?? 0) ?></strong>
                                </td>
                                <td>
                                    <span style="color: #48bb78; font-weight: 700;">
                                        <?= number_format($import->success_count ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #f56565; font-weight: 700;">
                                        <?= number_format($import->failed_count ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'success';
                                    $statusIcon = 'check_circle';
                                    $statusText = 'Sukses';

                                    if ($import->status === 'failed') {
                                        $statusClass = 'danger';
                                        $statusIcon = 'error';
                                        $statusText = 'Gagal';
                                    } elseif ($import->status === 'partial') {
                                        $statusClass = 'warning';
                                        $statusIcon = 'warning';
                                        $statusText = 'Partial';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="material-icons-outlined"><?= $statusIcon ?></i>
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <?= esc($import->email ?? 'System') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="material-icons-outlined">history</i>
                <h3>Belum Ada Riwayat Import</h3>
                <p>Riwayat import akan muncul di sini setelah Anda melakukan import pertama</p>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text">Memproses File...</div>
        <div class="loading-subtext">Mohon tunggu, sedang memvalidasi data</div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
    $(document).ready(function() {

        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');
        const fileInfo = document.getElementById('fileInfo');
        const submitBtn = document.getElementById('submitBtn');
        const maxFileSize = <?= $max_file_size ?> * 1024 * 1024; // Convert to bytes
        const allowedExtensions = <?= json_encode($allowed_extensions) ?>;

        // Drag & Drop Events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect({
                    target: {
                        files: files
                    }
                });
            }
        }

        // File Input Change
        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect(e) {
            const file = e.target.files[0];

            if (!file) {
                return;
            }

            // Validate file
            const validation = validateFile(file);

            if (!validation.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Tidak Valid',
                    text: validation.message,
                    confirmButtonColor: '#f56565'
                });

                fileInput.value = '';
                fileInfo.style.display = 'none';
                submitBtn.style.display = 'none';
                return;
            }

            // Display file info
            displayFileInfo(file);
        }

        function validateFile(file) {
            // Check extension
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(extension)) {
                return {
                    valid: false,
                    message: `Format file tidak didukung. Gunakan: ${allowedExtensions.join(', ')}`
                };
            }

            // Check size
            if (file.size > maxFileSize) {
                return {
                    valid: false,
                    message: `Ukuran file terlalu besar. Maksimal <?= $max_file_size ?>MB`
                };
            }

            return {
                valid: true
            };
        }

        function displayFileInfo(file) {
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);

            fileInfo.style.display = 'block';
            submitBtn.style.display = 'flex';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Form Submit
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const file = fileInput.files[0];

            if (!file) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Belum Dipilih',
                    text: 'Silakan pilih file terlebih dahulu',
                    confirmButtonColor: '#f56565'
                });
                return;
            }

            // Show loading
            showLoading();

            // Submit form
            uploadForm.submit();
        });

    });

    // Remove File
    function removeFile() {
        document.getElementById('fileInput').value = '';
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('submitBtn').style.display = 'none';
    }

    // Download Template
    function downloadTemplate() {
        Swal.fire({
            icon: 'info',
            title: 'Mengunduh Template...',
            text: 'Template Excel akan segera diunduh',
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true
        });

        window.location.href = '<?= base_url('admin/bulk-import/download-template') ?>';
    }

    // Show Loading
    function showLoading() {
        document.getElementById('loadingOverlay').classList.add('active');
    }

    // Hide Loading
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.remove('active');
    }

    // Hide loading on page load (in case of redirect back)
    window.addEventListener('load', function() {
        hideLoading();
    });
</script>
<?= $this->endSection() ?>