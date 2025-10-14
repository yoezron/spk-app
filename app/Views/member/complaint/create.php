<?php

/**
 * View: Complaint Create - Member Area
 * Controller: Member\ComplaintController@create
 * Description: Form untuk membuat pengaduan/tiket baru
 * 
 * Features:
 * - Subject input dengan character counter
 * - Category dropdown (7 categories)
 * - Priority select (4 levels)
 * - Description textarea dengan character counter
 * - Attachment upload (optional)
 * - Validation error handling
 * - Guidelines sidebar
 * - Responsive form layout
 * 
 * @package App\Views\Member\Complaint
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header p {
        opacity: 0.9;
        margin-bottom: 0;
        font-size: 15px;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 20px;
    }

    .form-card-header {
        border-bottom: 2px solid #f1f3f5;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    .form-card-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .form-card-header p {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 0;
    }

    /* Form Elements */
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .form-label .required {
        color: #e74c3c;
        margin-left: 3px;
    }

    .char-counter {
        font-size: 12px;
        color: #6c757d;
        font-weight: 400;
    }

    .char-counter.warning {
        color: #f39c12;
    }

    .char-counter.danger {
        color: #e74c3c;
    }

    .form-control,
    .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #e74c3c;
    }

    textarea.form-control {
        min-height: 200px;
        resize: vertical;
    }

    .form-text {
        font-size: 13px;
        color: #6c757d;
        margin-top: 6px;
    }

    /* File Upload */
    .file-upload-wrapper {
        border: 2px dashed #e3e6f0;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #f8f9fa;
    }

    .file-upload-wrapper:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }

    .file-upload-wrapper.dragover {
        border-color: #667eea;
        background: #e7f3ff;
    }

    .file-upload-icon {
        font-size: 48px;
        color: #667eea;
        margin-bottom: 15px;
    }

    .file-upload-text {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .file-upload-hint {
        font-size: 13px;
        color: #6c757d;
    }

    .file-preview {
        display: none;
        margin-top: 15px;
        padding: 15px;
        background: white;
        border: 2px solid #e3e6f0;
        border-radius: 8px;
    }

    .file-preview.active {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .file-icon {
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

    .file-details {
        flex: 1;
    }

    .file-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .file-size {
        font-size: 12px;
        color: #6c757d;
    }

    .file-remove {
        color: #e74c3c;
        cursor: pointer;
        font-size: 20px;
        padding: 5px;
    }

    .file-remove:hover {
        color: #c0392b;
    }

    /* Submit Section */
    .submit-section {
        border-top: 2px solid #f1f3f5;
        padding-top: 25px;
        margin-top: 25px;
    }

    .submit-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Sidebar Cards */
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 25px;
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

    .guideline-item {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
        font-size: 14px;
        line-height: 1.6;
    }

    .guideline-icon {
        color: #667eea;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .guideline-text {
        color: #6c757d;
    }

    .category-legend {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .category-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .category-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .category-name {
        font-weight: 500;
        color: #2c3e50;
    }

    /* Info Box */
    .info-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box-content {
        display: flex;
        gap: 12px;
    }

    .info-box-icon {
        font-size: 24px;
        color: #856404;
        flex-shrink: 0;
    }

    .info-box-text {
        flex: 1;
    }

    .info-box-text h5 {
        color: #856404;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .info-box-text p {
        color: #856404;
        font-size: 13px;
        margin: 0;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .form-card {
            padding: 20px;
        }

        .submit-actions {
            flex-direction: column;
        }

        .submit-actions .btn {
            width: 100%;
        }

        .sidebar-card {
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
        <li class="breadcrumb-item active" aria-current="page">Buat Pengaduan</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1><i class="bi bi-plus-circle"></i> Buat Pengaduan Baru</h1>
            <p>Sampaikan keluhan atau pertanyaan Anda kepada tim SPK</p>
        </div>
        <a href="<?= base_url('member/complaint') ?>" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Info Box -->
<div class="info-box">
    <div class="info-box-content">
        <div class="info-box-icon">
            <i class="bi bi-info-circle"></i>
        </div>
        <div class="info-box-text">
            <h5>Response Time: 1-2 Hari Kerja</h5>
            <p>
                Tim kami akan merespons pengaduan Anda dalam 1-2 hari kerja. Untuk kasus mendesak, pilih prioritas "Mendesak".
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Form -->
        <div class="form-card">
            <div class="form-card-header">
                <h3>Informasi Pengaduan</h3>
                <p>Isi formulir di bawah dengan lengkap dan jelas</p>
            </div>

            <form id="complaintForm" action="<?= base_url('member/complaint/store') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Subject -->
                <div class="mb-4">
                    <label for="subject" class="form-label">
                        <span>
                            Subjek Pengaduan <span class="required">*</span>
                        </span>
                        <span class="char-counter" id="subjectCounter">0/200</span>
                    </label>
                    <input type="text"
                        class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>"
                        id="subject"
                        name="subject"
                        placeholder="Contoh: Keterlambatan pembayaran gaji bulan Oktober 2025"
                        value="<?= old('subject') ?>"
                        maxlength="200"
                        required>
                    <?php if (isset($errors['subject'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['subject']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Tulis subjek yang jelas dan spesifik (minimal 5 karakter)
                    </div>
                </div>

                <!-- Category -->
                <div class="mb-4">
                    <label for="category" class="form-label">
                        Kategori Pengaduan <span class="required">*</span>
                    </label>
                    <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>"
                        id="category"
                        name="category"
                        required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="ketenagakerjaan" <?= old('category') === 'ketenagakerjaan' ? 'selected' : '' ?>>
                            Ketenagakerjaan
                        </option>
                        <option value="gaji" <?= old('category') === 'gaji' ? 'selected' : '' ?>>
                            Gaji & Tunjangan
                        </option>
                        <option value="kontrak" <?= old('category') === 'kontrak' ? 'selected' : '' ?>>
                            Kontrak Kerja
                        </option>
                        <option value="lingkungan_kerja" <?= old('category') === 'lingkungan_kerja' ? 'selected' : '' ?>>
                            Lingkungan Kerja
                        </option>
                        <option value="diskriminasi" <?= old('category') === 'diskriminasi' ? 'selected' : '' ?>>
                            Diskriminasi
                        </option>
                        <option value="pelecehan" <?= old('category') === 'pelecehan' ? 'selected' : '' ?>>
                            Pelecehan
                        </option>
                        <option value="lainnya" <?= old('category') === 'lainnya' ? 'selected' : '' ?>>
                            Lainnya
                        </option>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['category']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Pilih kategori yang paling sesuai dengan pengaduan Anda
                    </div>
                </div>

                <!-- Priority -->
                <div class="mb-4">
                    <label for="priority" class="form-label">
                        Prioritas
                    </label>
                    <select class="form-select <?= isset($errors['priority']) ? 'is-invalid' : '' ?>"
                        id="priority"
                        name="priority">
                        <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>
                            Rendah - Tidak mendesak
                        </option>
                        <option value="normal" <?= old('priority', 'normal') === 'normal' ? 'selected' : '' ?>>
                            Normal - Dapat menunggu
                        </option>
                        <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>
                            Tinggi - Perlu segera ditangani
                        </option>
                        <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>
                            Mendesak - Sangat penting
                        </option>
                    </select>
                    <?php if (isset($errors['priority'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['priority']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Pilih prioritas sesuai dengan tingkat urgensi masalah
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label">
                        <span>
                            Deskripsi Pengaduan <span class="required">*</span>
                        </span>
                        <span class="char-counter" id="descriptionCounter">0 karakter</span>
                    </label>
                    <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                        id="description"
                        name="description"
                        rows="10"
                        placeholder="Jelaskan masalah Anda dengan detail. Sertakan:&#10;- Kronologi kejadian&#10;- Waktu dan tempat kejadian&#10;- Pihak yang terlibat&#10;- Dampak yang dialami&#10;- Harapan penyelesaian"
                        required><?= old('description') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['description']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Minimal 20 karakter. Jelaskan masalah Anda sejelas mungkin agar kami dapat membantu dengan lebih baik.
                    </div>
                </div>

                <!-- Attachment -->
                <div class="mb-4">
                    <label class="form-label">
                        Lampiran (Opsional)
                    </label>

                    <div class="file-upload-wrapper" id="fileUploadWrapper">
                        <input type="file"
                            class="d-none"
                            id="attachment"
                            name="attachment"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">

                        <div class="file-upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="file-upload-text">
                            Klik atau seret file ke sini
                        </div>
                        <div class="file-upload-hint">
                            Format: JPG, PNG, PDF, DOC (Max: 5MB)
                        </div>
                    </div>

                    <div class="file-preview" id="filePreview">
                        <div class="file-info">
                            <div class="file-icon">
                                <i class="bi bi-file-earmark"></i>
                            </div>
                            <div class="file-details">
                                <div class="file-name" id="fileName"></div>
                                <div class="file-size" id="fileSize"></div>
                            </div>
                        </div>
                        <div class="file-remove" id="fileRemove">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>

                    <div class="form-text">
                        Unggah bukti pendukung seperti screenshot, surat, atau dokumen lainnya
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="submit-section">
                    <div class="submit-actions">
                        <a href="<?= base_url('member/complaint') ?>" class="btn btn-light btn-lg">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="submitButton">
                            <i class="bi bi-send"></i> Kirim Pengaduan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Guidelines Card -->
        <div class="sidebar-card">
            <h5>
                <i class="bi bi-lightbulb"></i> Panduan Pengaduan
            </h5>

            <div class="guideline-item">
                <div class="guideline-icon">
                    <i class="bi bi-1-circle"></i>
                </div>
                <div class="guideline-text">
                    Tulis subjek yang jelas dan spesifik
                </div>
            </div>

            <div class="guideline-item">
                <div class="guideline-icon">
                    <i class="bi bi-2-circle"></i>
                </div>
                <div class="guideline-text">
                    Pilih kategori yang sesuai dengan masalah Anda
                </div>
            </div>

            <div class="guideline-item">
                <div class="guideline-icon">
                    <i class="bi bi-3-circle"></i>
                </div>
                <div class="guideline-text">
                    Jelaskan kronologi kejadian secara detail
                </div>
            </div>

            <div class="guideline-item">
                <div class="guideline-icon">
                    <i class="bi bi-4-circle"></i>
                </div>
                <div class="guideline-text">
                    Sertakan bukti pendukung jika ada
                </div>
            </div>

            <div class="guideline-item">
                <div class="guideline-icon">
                    <i class="bi bi-5-circle"></i>
                </div>
                <div class="guideline-text">
                    Gunakan bahasa yang sopan dan profesional
                </div>
            </div>
        </div>

        <!-- Category Legend -->
        <div class="sidebar-card">
            <h5>
                <i class="bi bi-tags"></i> Kategori Pengaduan
            </h5>

            <div class="category-legend">
                <div class="category-item">
                    <div class="category-color" style="background: #3498db;"></div>
                    <span class="category-name">Ketenagakerjaan</span>
                </div>
                <div class="category-item">
                    <div class="category-color" style="background: #2ecc71;"></div>
                    <span class="category-name">Gaji & Tunjangan</span>
                </div>
                <div class="category-item">
                    <div class="category-color" style="background: #f39c12;"></div>
                    <span class="category-name">Kontrak Kerja</span>
                </div>
                <div class="category-item">
                    <div class="category-color" style="background: #9b59b6;"></div>
                    <span class="category-name">Lingkungan Kerja</span>
                </div>
                <div class="category-item">
                    <div class="category-color" style="background: #e74c3c;"></div>
                    <span class="category-name">Diskriminasi</span>
                </div>
                <div class="category-item">
                    <div class="category-color" style="background: #c0392b;"></div>
                    <span class="category-name">Pelecehan</span>
                </div>
                <div class="category-item">
                    <div class="category-color" style="background: #95a5a6;"></div>
                    <span class="category-name">Lainnya</span>
                </div>
            </div>
        </div>

        <!-- Privacy Notice -->
        <div class="sidebar-card" style="background: #e7f3ff; border: 2px solid #2196F3;">
            <h5 style="color: #1565c0;">
                <i class="bi bi-shield-check"></i> Privasi & Keamanan
            </h5>
            <p style="color: #1976d2; font-size: 13px; line-height: 1.6; margin: 0;">
                Semua pengaduan Anda dijaga kerahasiaannya. Hanya tim SPK yang berwenang yang dapat mengakses informasi pengaduan Anda.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Subject character counter
        $('#subject').on('input', function() {
            const length = $(this).val().length;
            const counter = $('#subjectCounter');
            counter.text(length + '/200');

            if (length >= 180) {
                counter.addClass('warning');
            } else {
                counter.removeClass('warning');
            }

            if (length >= 195) {
                counter.addClass('danger');
                counter.removeClass('warning');
            } else {
                counter.removeClass('danger');
            }
        });

        // Description character counter
        $('#description').on('input', function() {
            const length = $(this).val().length;
            const counter = $('#descriptionCounter');
            counter.text(length + ' karakter');

            if (length < 20 && length > 0) {
                counter.addClass('danger');
                counter.text(length + ' karakter (minimal 20)');
            } else {
                counter.removeClass('danger');
            }
        });

        // File upload functionality
        const fileInput = $('#attachment');
        const fileWrapper = $('#fileUploadWrapper');
        const filePreview = $('#filePreview');
        const fileName = $('#fileName');
        const fileSize = $('#fileSize');
        const fileRemove = $('#fileRemove');

        // Click to upload
        fileWrapper.on('click', function() {
            fileInput.click();
        });

        // Drag and drop
        fileWrapper.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });

        fileWrapper.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });

        fileWrapper.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                fileInput[0].files = files;
                handleFileSelect(files[0]);
            }
        });

        // File input change
        fileInput.on('change', function() {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });

        // Handle file selection
        function handleFileSelect(file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal 5MB',
                    confirmButtonColor: '#667eea'
                });
                fileInput.val('');
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Didukung',
                    text: 'Format yang didukung: JPG, PNG, PDF, DOC, DOCX',
                    confirmButtonColor: '#667eea'
                });
                fileInput.val('');
                return;
            }

            // Show file preview
            fileName.text(file.name);
            fileSize.text(formatFileSize(file.size));
            filePreview.addClass('active');
            fileWrapper.hide();
        }

        // Remove file
        fileRemove.on('click', function() {
            fileInput.val('');
            filePreview.removeClass('active');
            fileWrapper.show();
        });

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Form validation before submit
        $('#complaintForm').on('submit', function(e) {
            const subject = $('#subject').val().trim();
            const category = $('#category').val();
            const description = $('#description').val().trim();

            if (subject.length < 5) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Subjek Terlalu Pendek',
                    text: 'Subjek pengaduan minimal 5 karakter',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            if (!category) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Kategori Belum Dipilih',
                    text: 'Silakan pilih kategori pengaduan',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            if (description.length < 20) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Deskripsi Terlalu Pendek',
                    text: 'Deskripsi pengaduan minimal 20 karakter',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            // Show loading
            Swal.fire({
                title: 'Mengirim Pengaduan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Trigger initial counter
        $('#subject').trigger('input');
        $('#description').trigger('input');

        // Confirm before leave if there's unsaved content
        let formChanged = false;
        $('#complaintForm').on('change input', function() {
            formChanged = true;
        });

        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                const message = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                e.returnValue = message;
                return message;
            }
        });

        // Remove beforeunload when form is submitted
        $('#complaintForm').on('submit', function() {
            formChanged = false;
        });
    });
</script>
<?= $this->endSection() ?>