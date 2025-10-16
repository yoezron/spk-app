<?php

/**
 * View: Admin Static Page Edit Form
 * Controller: Admin\ContentController::editPage($slug)
 * Description: Form untuk mengedit halaman statis (Manifesto, AD/ART, Sejarah, dll)
 * 
 * Features:
 * - WYSIWYG Editor (Summernote) untuk konten
 * - Title & slug display (read-only untuk static pages)
 * - SEO meta fields
 * - Status toggle (draft/published)
 * - Preview mode
 * - Character counter
 * - Auto-save draft
 * - Full-screen editor mode
 * - Responsive design
 * 
 * @package App\Views\Admin\Content\Pages
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- Summernote CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-bs5.min.css') ?>">

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

    /* Form Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 25px;
        align-items: start;
    }

    /* Main Form Card */
    .main-form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 30px;
    }

    .form-section {
        margin-bottom: 30px;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f3f5;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        color: #667eea;
        font-size: 22px;
    }

    /* Sidebar Card */
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-bottom: 20px;
        position: sticky;
        top: 20px;
    }

    .sidebar-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-card-title i {
        color: #667eea;
    }

    /* Form Elements */
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label .required {
        color: #e74c3c;
        margin-left: 3px;
    }

    .form-label .hint {
        font-weight: 400;
        color: #6c757d;
        font-size: 13px;
        margin-left: 8px;
    }

    .form-control,
    .form-select {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 13px;
        margin-top: 6px;
    }

    .form-text {
        color: #6c757d;
        font-size: 13px;
        margin-top: 6px;
    }

    /* Read-only Field */
    .readonly-field {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 12px 15px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .readonly-field i {
        color: #667eea;
    }

    .readonly-field .value {
        flex: 1;
        color: #2c3e50;
        font-weight: 500;
    }

    /* Character Counter */
    .char-counter {
        float: right;
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }

    .char-counter.warning {
        color: #f39c12;
    }

    .char-counter.danger {
        color: #e74c3c;
    }

    /* Status Selection */
    .status-option {
        padding: 15px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-option:hover {
        border-color: #667eea;
        background: #f5f7ff;
    }

    .status-option.selected {
        border-color: #667eea;
        background: #f5f7ff;
    }

    .status-option input[type="radio"] {
        width: 20px;
        height: 20px;
    }

    .status-info h6 {
        margin: 0 0 4px 0;
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
    }

    .status-info p {
        margin: 0;
        font-size: 12px;
        color: #6c757d;
    }

    /* Page Info */
    .page-info-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f5;
    }

    .page-info-item:last-child {
        border-bottom: none;
    }

    .page-info-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
    }

    .page-info-value {
        font-size: 13px;
        color: #2c3e50;
        font-weight: 600;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 12px;
        padding-top: 25px;
        border-top: 2px solid #f1f3f5;
        margin-top: 30px;
    }

    .btn-submit {
        flex: 1;
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Warning Box */
    .warning-box {
        background: #fff3cd;
        border: 2px solid #f39c12;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .warning-box h6 {
        color: #856404;
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .warning-box p {
        color: #856404;
        font-size: 14px;
        margin: 0;
        line-height: 1.6;
    }

    /* Summernote Customization */
    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-top: 8px;
    }

    .note-editor.note-frame.is-invalid {
        border-color: #dc3545;
    }

    .note-editing-area {
        min-height: 500px;
    }

    /* Preview Mode */
    .preview-container {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-top: 20px;
    }

    .preview-container.show {
        display: block;
    }

    .preview-content {
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.8;
        font-size: 16px;
        color: #2c3e50;
    }

    .preview-content h1,
    .preview-content h2,
    .preview-content h3 {
        color: #2c3e50;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .preview-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 20px 0;
    }

    /* Auto-save Indicator */
    .autosave-indicator {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        padding: 12px 20px;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        display: none;
        align-items: center;
        gap: 10px;
        z-index: 1000;
    }

    .autosave-indicator.show {
        display: flex;
    }

    .autosave-indicator i {
        color: #27ae60;
        font-size: 18px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .form-container {
            grid-template-columns: 1fr;
        }

        .sidebar-card {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .page-header-content {
            padding: 20px;
        }

        .page-header-content h1 {
            font-size: 24px;
        }

        .main-form-card {
            padding: 20px;
        }

        .form-section-title {
            font-size: 16px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-submit {
            width: 100%;
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
                <i class="bi bi-pencil-square me-2"></i>
                Edit Halaman: <?= esc($page->title) ?>
            </h1>
            <p>Perbarui konten halaman statis dan klik simpan untuk menyimpan perubahan</p>
        </div>
        <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
            <span class="badge bg-<?= $page->status === 'published' ? 'success' : 'warning' ?> px-3 py-2">
                <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                <?= ucfirst(esc($page->status)) ?>
            </span>
            <a href="<?= base_url('admin/content/pages') ?>" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/content/pages') ?>">Halaman</a></li>
        <li class="breadcrumb-item active"><?= esc($page->title) ?></li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Warning for Important Pages -->
<?php if (in_array($page->slug, ['manifesto', 'ad-art', 'sejarah-spk', 'visi-misi'])): ?>
    <div class="warning-box">
        <h6>
            <i class="bi bi-exclamation-triangle-fill"></i>
            Halaman Penting!
        </h6>
        <p>
            Ini adalah halaman penting SPK. Pastikan perubahan yang Anda lakukan sudah benar dan sesuai
            dengan dokumen resmi organisasi. Halaman ini dapat diakses oleh anggota dan publik.
        </p>
    </div>
<?php endif; ?>

<!-- Form Container -->
<form id="pageForm" method="POST" action="<?= base_url('admin/content/pages/update/' . $page->slug) ?>">
    <?= csrf_field() ?>

    <div class="form-container">
        <!-- Main Form -->
        <div class="main-form-card">

            <!-- Page Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="bi bi-info-circle"></i>
                    Informasi Halaman
                </h3>

                <!-- Title (Read-only for important pages) -->
                <div class="mb-4">
                    <label for="title" class="form-label">
                        Judul Halaman<span class="required">*</span>
                    </label>
                    <?php if (in_array($page->slug, ['manifesto', 'ad-art', 'sejarah-spk', 'visi-misi'])): ?>
                        <div class="readonly-field">
                            <i class="bi bi-lock-fill"></i>
                            <span class="value"><?= esc($page->title) ?></span>
                        </div>
                        <input type="hidden" name="title" value="<?= esc($page->title) ?>">
                        <div class="form-text">Judul tidak dapat diubah untuk halaman penting</div>
                    <?php else: ?>
                        <input
                            type="text"
                            class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                            id="title"
                            name="title"
                            value="<?= old('title', $page->title) ?>"
                            required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Slug (Read-only) -->
                <div class="mb-4">
                    <label class="form-label">Slug (URL)</label>
                    <div class="readonly-field">
                        <i class="bi bi-link-45deg"></i>
                        <span class="value"><?= base_url($page->slug) ?></span>
                    </div>
                    <div class="form-text">URL halaman tidak dapat diubah</div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="bi bi-file-text"></i>
                    Konten Halaman
                </h3>

                <div class="mb-4">
                    <label for="content" class="form-label">
                        Isi Konten<span class="required">*</span>
                    </label>
                    <textarea
                        class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>"
                        id="content"
                        name="content"
                        required><?= old('content', $page->content) ?></textarea>
                    <?php if (isset($errors['content'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['content']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Gunakan editor untuk format teks, tambahkan gambar, list, dll.</div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="bi bi-search"></i>
                    SEO & Meta
                </h3>

                <!-- Meta Description -->
                <div class="mb-4">
                    <label for="meta_description" class="form-label">
                        Meta Description
                        <span class="char-counter" id="metaCounter">0 / 160</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="meta_description"
                        name="meta_description"
                        rows="2"
                        maxlength="160"
                        placeholder="Deskripsi singkat untuk search engine (opsional)..."><?= old('meta_description', $page->meta_description ?? '') ?></textarea>
                    <div class="form-text">Deskripsi ini akan muncul di hasil pencarian Google</div>
                </div>

                <!-- Meta Keywords -->
                <div class="mb-4">
                    <label for="meta_keywords" class="form-label">
                        Meta Keywords
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="meta_keywords"
                        name="meta_keywords"
                        value="<?= old('meta_keywords', $page->meta_keywords ?? '') ?>"
                        placeholder="keyword1, keyword2, keyword3">
                    <div class="form-text">Pisahkan dengan koma (,)</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary btn-submit" id="previewBtn">
                    <i class="bi bi-eye me-2"></i>
                    Preview
                </button>
                <button type="submit" name="status" value="draft" class="btn btn-secondary btn-submit">
                    <i class="bi bi-file-earmark me-2"></i>
                    Simpan sebagai Draft
                </button>
                <button type="submit" name="status" value="published" class="btn btn-primary btn-submit">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $page->status === 'published' ? 'Update & Publish' : 'Publish Halaman' ?>
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Status Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">
                    <i class="bi bi-gear"></i>
                    Status Publikasi
                </h4>

                <div class="status-option <?= $page->status === 'draft' ? 'selected' : '' ?>" data-status="draft">
                    <input
                        type="radio"
                        name="status_display"
                        id="status_draft"
                        value="draft"
                        <?= $page->status === 'draft' ? 'checked' : '' ?>>
                    <div class="status-info">
                        <h6>Draft</h6>
                        <p>Halaman tidak tampil di public</p>
                    </div>
                </div>

                <div class="status-option <?= $page->status === 'published' ? 'selected' : '' ?>" data-status="published">
                    <input
                        type="radio"
                        name="status_display"
                        id="status_published"
                        value="published"
                        <?= $page->status === 'published' ? 'checked' : '' ?>>
                    <div class="status-info">
                        <h6>Published</h6>
                        <p>Halaman dapat diakses public</p>
                    </div>
                </div>
            </div>

            <!-- Page Info Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">
                    <i class="bi bi-info-circle"></i>
                    Informasi
                </h4>

                <div>
                    <div class="page-info-item">
                        <span class="page-info-label">Dibuat:</span>
                        <span class="page-info-value">
                            <?= date('d M Y', strtotime($page->created_at)) ?>
                        </span>
                    </div>
                    <div class="page-info-item">
                        <span class="page-info-label">Terakhir Update:</span>
                        <span class="page-info-value">
                            <?= date('d M Y H:i', strtotime($page->updated_at)) ?>
                        </span>
                    </div>
                    <?php if (isset($page->views_count)): ?>
                        <div class="page-info-item">
                            <span class="page-info-label">Total Views:</span>
                            <span class="page-info-value">
                                <?= number_format($page->views_count) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ($page->status === 'published' && isset($page->published_at)): ?>
                        <div class="page-info-item">
                            <span class="page-info-label">Dipublish:</span>
                            <span class="page-info-value">
                                <?= date('d M Y', strtotime($page->published_at)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <?php if ($page->status === 'published'): ?>
                <div class="sidebar-card">
                    <h4 class="sidebar-card-title">
                        <i class="bi bi-lightning"></i>
                        Quick Actions
                    </h4>

                    <a href="<?= base_url($page->slug) ?>"
                        class="btn btn-outline-primary w-100 mb-2"
                        target="_blank">
                        <i class="bi bi-eye me-2"></i>
                        Lihat Halaman
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Preview Container -->
<div class="preview-container" id="previewContainer">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= esc($page->title) ?></h2>
        <button type="button" class="btn btn-secondary" id="closePreviewBtn">
            <i class="bi bi-x-lg me-2"></i>
            Tutup Preview
        </button>
    </div>
    <hr>
    <div class="preview-content" id="previewContent"></div>
</div>

<!-- Auto-save Indicator -->
<div class="autosave-indicator" id="autosaveIndicator">
    <i class="bi bi-check-circle-fill"></i>
    <span class="text">Perubahan tersimpan otomatis</span>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Summernote JS -->
<script src="<?= base_url('assets/plugins/summernote/summernote-bs5.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // ==========================================
        // SUMMERNOTE WYSIWYG EDITOR
        // ==========================================
        $('#content').summernote({
            height: 500,
            minHeight: 400,
            maxHeight: 800,
            placeholder: 'Tuliskan konten halaman di sini...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onChange: function(contents) {
                    // Auto-save to localStorage
                    autoSaveDraft();
                }
            }
        });

        // ==========================================
        // CHARACTER COUNTER
        // ==========================================
        function updateCharCounter(inputId, counterId, maxLength) {
            let input = $('#' + inputId);
            let counter = $('#' + counterId);

            input.on('input', function() {
                let length = $(this).val().length;
                counter.text(length + ' / ' + maxLength);

                counter.removeClass('warning danger');
                if (length > maxLength * 0.9) {
                    counter.addClass('danger');
                } else if (length > maxLength * 0.75) {
                    counter.addClass('warning');
                }
            });

            input.trigger('input');
        }

        updateCharCounter('meta_description', 'metaCounter', 160);

        // ==========================================
        // STATUS SELECTION
        // ==========================================
        $('.status-option').on('click', function() {
            $('.status-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // ==========================================
        // PREVIEW MODE
        // ==========================================
        $('#previewBtn').on('click', function() {
            const content = $('#content').summernote('code');
            $('#previewContent').html(content);
            $('#previewContainer').addClass('show');
            $('html, body').animate({
                scrollTop: $('#previewContainer').offset().top - 20
            }, 500);
        });

        $('#closePreviewBtn').on('click', function() {
            $('#previewContainer').removeClass('show');
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        });

        // ==========================================
        // AUTO-SAVE DRAFT
        // ==========================================
        let autoSaveTimer;

        function autoSaveDraft() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                const formData = {
                    title: $('#title').val(),
                    content: $('#content').summernote('code'),
                    meta_description: $('#meta_description').val(),
                    meta_keywords: $('#meta_keywords').val(),
                    timestamp: new Date().getTime()
                };

                localStorage.setItem('page_draft_<?= $page->slug ?>', JSON.stringify(formData));

                $('#autosaveIndicator').addClass('show');
                setTimeout(function() {
                    $('#autosaveIndicator').removeClass('show');
                }, 2000);
            }, 3000);
        }

        // Restore draft on page load
        const savedDraft = localStorage.getItem('page_draft_<?= $page->slug ?>');
        if (savedDraft) {
            const draft = JSON.parse(savedDraft);
            const now = new Date().getTime();
            const draftAge = now - draft.timestamp;
            const oneHourInMs = 60 * 60 * 1000;

            if (draftAge < oneHourInMs) {
                if (confirm('Draft tersimpan ditemukan. Apakah Anda ingin memulihkannya?')) {
                    if (!$('#title').is('[readonly]')) {
                        $('#title').val(draft.title);
                    }
                    $('#content').summernote('code', draft.content);
                    $('#meta_description').val(draft.meta_description);
                    $('#meta_keywords').val(draft.meta_keywords);

                    $('#meta_description').trigger('input');
                }
            }
        }

        // Clear draft on successful submit
        $('#pageForm').on('submit', function() {
            localStorage.removeItem('page_draft_<?= $page->slug ?>');
        });

        // ==========================================
        // FORM VALIDATION
        // ==========================================
        $('#pageForm').on('submit', function(e) {
            let isValid = true;

            const content = $('#content').summernote('code');
            if (content.replace(/<[^>]*>/g, '').trim().length < 50) {
                alert('Konten minimal 50 karakter');
                isValid = false;
            }

            return isValid;
        });

        // ==========================================
        // KEYBOARD SHORTCUTS
        // ==========================================
        $(document).on('keydown', function(e) {
            // Ctrl+S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('button[name="status"][value="draft"]').click();
            }
        });

        // Unsaved changes warning
        let formChanged = false;
        $('#pageForm :input').on('change input', function() {
            formChanged = true;
        });

        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'Anda memiliki perubahan yang belum disimpan.';
            }
        });

        $('#pageForm').on('submit', function() {
            formChanged = false;
        });

        console.log('✓ Page Edit Form initialized successfully');
    });
</script>
<?= $this->endSection() ?>