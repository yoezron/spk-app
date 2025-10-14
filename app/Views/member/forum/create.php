<?php

/**
 * View: Forum Create Thread - Member Area
 * Controller: Member\ForumController@create
 * Description: Form untuk membuat thread forum baru dengan WYSIWYG editor
 * 
 * Features:
 * - Title input dengan character counter
 * - Category selection dropdown
 * - Rich text editor (Summernote) untuk content
 * - Preview mode untuk melihat hasil sebelum publish
 * - Validation error handling
 * - Responsive form layout
 * - Auto-save draft (optional - bisa dikembangkan nanti)
 * 
 * @package App\Views\Member\Forum
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<!-- Summernote CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-bs5.min.css') ?>">

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

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-text {
        font-size: 13px;
        color: #6c757d;
        margin-top: 6px;
    }

    /* Preview Box */
    .preview-box {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 25px;
        margin-top: 20px;
        display: none;
    }

    .preview-box.active {
        display: block;
    }

    .preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #dee2e6;
    }

    .preview-header h4 {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .preview-title {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .preview-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 20px;
    }

    .preview-category {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    .preview-content {
        color: #2c3e50;
        line-height: 1.8;
        font-size: 15px;
    }

    .preview-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 15px 0;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-top: 25px;
        border-top: 2px solid #f1f3f5;
        margin-top: 25px;
    }

    .btn-preview {
        background: #6c757d;
        color: white;
        border: none;
    }

    .btn-preview:hover {
        background: #5a6268;
        color: white;
    }

    .btn-preview.active {
        background: #667eea;
    }

    .btn-preview.active:hover {
        background: #5568d3;
    }

    /* Guidelines Card */
    .guidelines-card {
        background: #fff9e6;
        border: 2px solid #f39c12;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .guidelines-card h4 {
        color: #f39c12;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .guidelines-card ul {
        margin-bottom: 0;
        padding-left: 20px;
    }

    .guidelines-card li {
        color: #856404;
        font-size: 14px;
        line-height: 1.8;
        margin-bottom: 6px;
    }

    /* Summernote Customization */
    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    .note-editor.note-frame.is-invalid {
        border-color: #dc3545;
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

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1><i class="bi bi-pencil-square"></i> <?= esc($pageTitle) ?></h1>
            <p>Bagikan informasi, ajukan pertanyaan, atau mulai diskusi dengan anggota lain</p>
        </div>
        <a href="<?= base_url('member/forum') ?>" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Form -->
        <div class="form-card">
            <div class="form-card-header">
                <h3>Informasi Thread</h3>
                <p>Isi formulir di bawah untuk membuat thread diskusi baru</p>
            </div>

            <form id="createThreadForm" action="<?= base_url('member/forum/store') ?>" method="POST">
                <?= csrf_field() ?>

                <!-- Title Input -->
                <div class="mb-4">
                    <label for="title" class="form-label">
                        <span>
                            Judul Thread <span class="required">*</span>
                        </span>
                        <span class="char-counter" id="titleCounter">0/200</span>
                    </label>
                    <input type="text"
                        class="form-control form-control-lg <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                        id="title"
                        name="title"
                        placeholder="Masukkan judul thread yang jelas dan deskriptif..."
                        value="<?= old('title') ?>"
                        maxlength="200"
                        required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['title']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Gunakan judul yang jelas dan mudah dipahami agar thread mudah ditemukan
                    </div>
                </div>

                <!-- Category Select -->
                <div class="mb-4">
                    <label for="category" class="form-label">
                        Kategori
                    </label>
                    <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>"
                        id="category"
                        name="category">
                        <option value="">Pilih Kategori (Opsional)</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat->id) ?>"
                                    <?= (old('category') == $cat->id) ? 'selected' : '' ?>>
                                    <?= esc($cat->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['category']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Pilih kategori yang sesuai untuk memudahkan anggota lain menemukan diskusi
                    </div>
                </div>

                <!-- Content Editor -->
                <div class="mb-4">
                    <label for="content" class="form-label">
                        <span>
                            Konten Thread <span class="required">*</span>
                        </span>
                        <span class="char-counter" id="contentCounter">0 karakter</span>
                    </label>
                    <textarea class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>"
                        id="content"
                        name="content"
                        rows="10"><?= old('content') ?></textarea>
                    <?php if (isset($errors['content'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= esc($errors['content']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Minimal 20 karakter. Jelaskan pertanyaan atau topik diskusi Anda dengan detail
                    </div>
                </div>

                <!-- Preview Box -->
                <div class="preview-box" id="previewBox">
                    <div class="preview-header">
                        <h4><i class="bi bi-eye"></i> Preview Thread</h4>
                        <button type="button" class="btn btn-sm btn-secondary" id="closePreview">
                            <i class="bi bi-x-lg"></i> Tutup Preview
                        </button>
                    </div>

                    <div class="preview-title" id="previewTitle">
                        Judul Thread Akan Muncul Di Sini
                    </div>

                    <div class="preview-meta">
                        <span><i class="bi bi-person-circle"></i> <strong><?= esc(auth()->user()->username ?? 'Anda') ?></strong></span>
                        <span>•</span>
                        <span><i class="bi bi-clock"></i> Baru saja</span>
                        <span class="preview-category" id="previewCategory" style="display: none;">
                            Kategori
                        </span>
                    </div>

                    <div class="preview-content" id="previewContent">
                        <p class="text-muted">Konten thread akan muncul di sini...</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn btn-preview" id="togglePreview">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="bi bi-send"></i> Posting Thread
                    </button>
                    <a href="<?= base_url('member/forum') ?>" class="btn btn-light">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Guidelines Card -->
        <div class="guidelines-card">
            <h4>
                <i class="bi bi-lightbulb"></i> Panduan Membuat Thread
            </h4>
            <ul>
                <li>Gunakan judul yang jelas dan spesifik</li>
                <li>Pilih kategori yang sesuai</li>
                <li>Jelaskan masalah atau topik dengan detail</li>
                <li>Gunakan bahasa yang sopan dan profesional</li>
                <li>Hindari posting yang bersifat SARA</li>
                <li>Jangan posting informasi pribadi atau rahasia</li>
                <li>Gunakan fitur preview sebelum posting</li>
            </ul>
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
                        Hormati sesama anggota
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        No spam atau iklan
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Stay on topic
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Tidak ada konten ilegal
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success"></i>
                        Laporan pelanggaran akan ditindak
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Summernote JS -->
<script src="<?= base_url('assets/plugins/summernote/summernote-bs5.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        // Initialize Summernote
        $('#content').summernote({
            height: 300,
            placeholder: 'Tulis konten thread Anda di sini...\n\nAnda bisa menggunakan formatting seperti bold, italic, list, dan lainnya.',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onChange: function(contents) {
                    updateContentCounter(contents);
                }
            }
        });

        // Title character counter
        $('#title').on('input', function() {
            const length = $(this).val().length;
            const counter = $('#titleCounter');
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

        // Content character counter
        function updateContentCounter(contents) {
            const text = $('<div>').html(contents).text();
            const length = text.length;
            const counter = $('#contentCounter');
            counter.text(length + ' karakter');

            if (length < 20 && length > 0) {
                counter.addClass('danger');
                counter.text(length + ' karakter (minimal 20)');
            } else {
                counter.removeClass('danger');
            }
        }

        // Toggle Preview
        $('#togglePreview').on('click', function() {
            const isActive = $(this).hasClass('active');

            if (!isActive) {
                // Show preview
                updatePreview();
                $('#previewBox').slideDown();
                $(this).addClass('active');
                $(this).html('<i class="bi bi-x-lg"></i> Tutup Preview');

                // Scroll to preview
                $('html, body').animate({
                    scrollTop: $('#previewBox').offset().top - 100
                }, 500);
            } else {
                // Hide preview
                $('#previewBox').slideUp();
                $(this).removeClass('active');
                $(this).html('<i class="bi bi-eye"></i> Preview');
            }
        });

        // Close Preview Button
        $('#closePreview').on('click', function() {
            $('#previewBox').slideUp();
            $('#togglePreview').removeClass('active');
            $('#togglePreview').html('<i class="bi bi-eye"></i> Preview');
        });

        // Update Preview Content
        function updatePreview() {
            // Update title
            const title = $('#title').val() || 'Judul Thread Akan Muncul Di Sini';
            $('#previewTitle').text(title);

            // Update category
            const categorySelect = $('#category');
            const categoryText = categorySelect.find('option:selected').text();
            if (categorySelect.val() && categoryText !== 'Pilih Kategori (Opsional)') {
                $('#previewCategory').text(categoryText).show();
            } else {
                $('#previewCategory').hide();
            }

            // Update content
            const content = $('#content').summernote('code');
            if (content && content.trim() !== '') {
                $('#previewContent').html(content);
            } else {
                $('#previewContent').html('<p class="text-muted">Konten thread akan muncul di sini...</p>');
            }
        }

        // Real-time preview update (optional - update on change)
        $('#title, #category').on('change input', function() {
            if ($('#togglePreview').hasClass('active')) {
                updatePreview();
            }
        });

        $('#content').on('summernote.change', function() {
            if ($('#togglePreview').hasClass('active')) {
                updatePreview();
            }
        });

        // Form validation before submit
        $('#createThreadForm').on('submit', function(e) {
            const title = $('#title').val().trim();
            const content = $('#content').summernote('code').trim();
            const contentText = $('<div>').html(content).text().trim();

            if (title.length < 5) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Judul Terlalu Pendek',
                    text: 'Judul thread minimal 5 karakter',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            if (contentText.length < 20) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Konten Terlalu Pendek',
                    text: 'Konten thread minimal 20 karakter',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            // Show loading
            Swal.fire({
                title: 'Memposting Thread...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Trigger initial counter
        $('#title').trigger('input');
        updateContentCounter($('#content').summernote('code'));

        // Confirm before leave if there's unsaved content
        let formChanged = false;
        $('#createThreadForm').on('change input', function() {
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
        $('#createThreadForm').on('submit', function() {
            formChanged = false;
        });
    });
</script>
<?= $this->endSection() ?>