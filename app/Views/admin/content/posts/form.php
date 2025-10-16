<?php

/**
 * View: Admin Blog Post Form (Create & Edit)
 * Controller: Admin\ContentController::createPost() & editPost($id)
 * Description: Universal form untuk membuat dan mengedit blog post/artikel
 * 
 * Features:
 * - WYSIWYG Editor (Summernote) dengan image upload
 * - Featured image upload dengan preview
 * - Auto-generate slug dari title
 * - Category selection dropdown
 * - Tags input dengan Select2 (multiple tagging)
 * - SEO meta fields (description & keywords)
 * - Status: Draft / Published
 * - Featured post checkbox
 * - Character counter untuk excerpt
 * - Form validation dengan error display
 * - Preview mode untuk melihat hasil
 * - Responsive design untuk mobile
 * - Auto-save draft (localStorage backup)
 * 
 * @package App\Views\Admin\Content\Posts
 * @author  SPK Development Team
 * @version 1.0.0
 */

// Determine if this is edit mode or create mode
$isEdit = isset($post) && $post;
$formTitle = $isEdit ? 'Edit Artikel' : 'Buat Artikel Baru';
$formAction = $isEdit ? base_url('admin/content/posts/update/' . $post->id) : base_url('admin/content/posts/store');
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- Summernote CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-bs5.min.css') ?>">
<!-- Select2 CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

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
        font-size: 15px;
    }

    /* Form Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 350px;
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

    /* Sidebar Cards */
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

    .form-control.is-invalid,
    .form-select.is-invalid {
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

    /* Featured Image Upload */
    .image-upload-container {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .image-upload-container:hover {
        border-color: #667eea;
        background: #f5f7ff;
    }

    .image-upload-container.has-image {
        padding: 0;
        border: none;
        background: transparent;
    }

    .upload-placeholder {
        color: #6c757d;
    }

    .upload-placeholder i {
        font-size: 48px;
        color: #adb5bd;
        margin-bottom: 15px;
        display: block;
    }

    .image-preview-wrapper {
        position: relative;
        display: none;
    }

    .image-preview-wrapper.show {
        display: block;
    }

    .image-preview {
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .remove-image-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);
    }

    .remove-image-btn:hover {
        background: #c0392b;
        transform: scale(1.1);
    }

    /* Slug Generator */
    .slug-group {
        position: relative;
    }

    .slug-preview {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 13px;
        color: #6c757d;
        font-family: 'Courier New', monospace;
    }

    .slug-preview i {
        color: #667eea;
    }

    .slug-preview .slug-text {
        flex: 1;
        word-break: break-all;
    }

    .btn-regenerate-slug {
        padding: 4px 12px;
        font-size: 12px;
        border-radius: 6px;
    }

    /* Featured Post Toggle */
    .featured-toggle {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .featured-toggle:hover {
        border-color: #667eea;
        background: #f5f7ff;
    }

    .featured-toggle.active {
        background: #fff3cd;
        border-color: #f39c12;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
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

    /* Guidelines Card */
    .guidelines-card {
        background: #fff9e6;
        border: 2px solid #f39c12;
        border-radius: 10px;
        padding: 20px;
    }

    .guidelines-card h6 {
        color: #f39c12;
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .guidelines-card ul {
        margin: 0;
        padding-left: 20px;
    }

    .guidelines-card li {
        color: #856404;
        font-size: 13px;
        line-height: 1.8;
        margin-bottom: 6px;
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
        min-height: 400px;
    }

    /* Select2 Customization */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        min-height: 45px;
        padding: 5px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #667eea;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #667eea;
        border-color: #667eea;
        color: white;
        border-radius: 6px;
        padding: 4px 10px;
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
        animation: slideInUp 0.3s ease;
    }

    .autosave-indicator.show {
        display: flex;
    }

    .autosave-indicator i {
        color: #27ae60;
        font-size: 18px;
    }

    .autosave-indicator .text {
        font-size: 13px;
        color: #2c3e50;
        font-weight: 500;
    }

    @keyframes slideInUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
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
                <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
                <?= esc($formTitle) ?>
            </h1>
            <p>
                <?php if ($isEdit): ?>
                    Perbarui informasi artikel dan klik simpan untuk menyimpan perubahan
                <?php else: ?>
                    Buat artikel baru dengan editor lengkap dan fitur SEO
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
            <?php if ($isEdit && isset($post->status)): ?>
                <span class="badge bg-<?= $post->status === 'published' ? 'success' : 'warning' ?> px-3 py-2">
                    <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                    <?= ucfirst(esc($post->status)) ?>
                </span>
            <?php endif; ?>
            <a href="<?= base_url('admin/content/posts') ?>" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/content/posts') ?>">Artikel</a></li>
        <li class="breadcrumb-item active"><?= esc($formTitle) ?></li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Guidelines Card -->
<div class="guidelines-card mb-4">
    <h6>
        <i class="bi bi-lightbulb-fill"></i>
        Panduan Menulis Artikel
    </h6>
    <ul>
        <li><strong>Judul:</strong> Gunakan judul yang menarik dan deskriptif (5-100 karakter)</li>
        <li><strong>Excerpt:</strong> Ringkasan artikel untuk preview (max 500 karakter)</li>
        <li><strong>Konten:</strong> Gunakan heading, bold, italic untuk struktur yang baik</li>
        <li><strong>Featured Image:</strong> Gunakan gambar berkualitas tinggi (max 2MB, format: JPG, PNG)</li>
        <li><strong>SEO:</strong> Isi meta description dan keywords untuk optimasi pencarian</li>
        <li><strong>Tags:</strong> Gunakan 3-5 tags yang relevan untuk kategorisasi</li>
    </ul>
</div>

<!-- Form Container -->
<form id="postForm" method="POST" action="<?= esc($formAction) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-container">
        <!-- Main Form -->
        <div class="main-form-card">

            <!-- Basic Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="bi bi-info-circle"></i>
                    Informasi Dasar
                </h3>

                <!-- Title -->
                <div class="mb-4">
                    <label for="title" class="form-label">
                        Judul Artikel<span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                        id="title"
                        name="title"
                        value="<?= old('title', $post->title ?? '') ?>"
                        placeholder="Masukkan judul artikel yang menarik..."
                        maxlength="255"
                        required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Judul akan ditampilkan sebagai heading utama artikel</div>
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label for="slug" class="form-label">
                        Slug (URL)<span class="required">*</span>
                        <span class="hint">Auto-generate dari judul</span>
                    </label>
                    <div class="slug-group">
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>"
                                id="slug"
                                name="slug"
                                value="<?= old('slug', $post->slug ?? '') ?>"
                                placeholder="artikel-slug-otomatis"
                                pattern="[a-z0-9-]+"
                                required>
                            <button class="btn btn-outline-secondary btn-regenerate-slug" type="button" id="regenerateSlug">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['slug'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['slug']) ?></div>
                        <?php endif; ?>
                        <div class="slug-preview">
                            <i class="bi bi-link-45deg"></i>
                            <span class="slug-text"><?= base_url('blog/') ?><span id="slugPreview"><?= old('slug', $post->slug ?? 'artikel-slug') ?></span></span>
                        </div>
                    </div>
                </div>

                <!-- Excerpt -->
                <div class="mb-4">
                    <label for="excerpt" class="form-label">
                        Excerpt / Ringkasan
                        <span class="char-counter" id="excerptCounter">0 / 500</span>
                    </label>
                    <textarea
                        class="form-control <?= isset($errors['excerpt']) ? 'is-invalid' : '' ?>"
                        id="excerpt"
                        name="excerpt"
                        rows="3"
                        maxlength="500"
                        placeholder="Tuliskan ringkasan singkat artikel untuk preview (opsional)..."><?= old('excerpt', $post->excerpt ?? '') ?></textarea>
                    <?php if (isset($errors['excerpt'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['excerpt']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Ringkasan akan ditampilkan di listing artikel</div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="bi bi-file-text"></i>
                    Konten Artikel
                </h3>

                <div class="mb-4">
                    <label for="content" class="form-label">
                        Isi Artikel<span class="required">*</span>
                    </label>
                    <textarea
                        class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>"
                        id="content"
                        name="content"
                        required><?= old('content', $post->content ?? '') ?></textarea>
                    <?php if (isset($errors['content'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['content']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Gunakan editor untuk format teks, tambahkan gambar, link, dll.</div>
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
                        placeholder="Deskripsi singkat untuk search engine (opsional)..."><?= old('meta_description', $post->meta_description ?? '') ?></textarea>
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
                        value="<?= old('meta_keywords', $post->meta_keywords ?? '') ?>"
                        placeholder="keyword1, keyword2, keyword3">
                    <div class="form-text">Pisahkan dengan koma (,)</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="submit" name="status" value="draft" class="btn btn-secondary btn-submit">
                    <i class="bi bi-file-earmark me-2"></i>
                    Simpan sebagai Draft
                </button>
                <button type="submit" name="status" value="published" class="btn btn-primary btn-submit">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $isEdit && isset($post->status) && $post->status === 'published' ? 'Update & Publish' : 'Publish Artikel' ?>
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Featured Image Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">
                    <i class="bi bi-image"></i>
                    Featured Image
                </h4>

                <div class="image-upload-container" id="imageUploadContainer">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <i class="bi bi-cloud-upload"></i>
                        <p class="mb-2"><strong>Klik untuk upload gambar</strong></p>
                        <p class="text-muted small mb-0">JPG, PNG (Max 2MB)</p>
                    </div>
                    <div class="image-preview-wrapper <?= isset($post->featured_image) && $post->featured_image ? 'show' : '' ?>" id="imagePreviewWrapper">
                        <img src="<?= isset($post->featured_image) ? base_url('uploads/posts/' . $post->featured_image) : '' ?>" alt="Preview" class="image-preview" id="imagePreview">
                        <button type="button" class="remove-image-btn" id="removeImageBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input
                        type="file"
                        class="d-none"
                        id="featured_image"
                        name="featured_image"
                        accept="image/jpeg,image/jpg,image/png,image/webp">
                </div>
                <?php if (isset($errors['featured_image'])): ?>
                    <div class="text-danger small mt-2"><?= esc($errors['featured_image']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Category Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">
                    <i class="bi bi-folder"></i>
                    Kategori
                </h4>

                <select
                    class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>"
                    id="category_id"
                    name="category_id"
                    required>
                    <option value="">Pilih Kategori...</option>
                    <?php if (isset($categories) && !empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= $category->id ?>"
                                <?= old('category_id', $post->category_id ?? '') == $category->id ? 'selected' : '' ?>>
                                <?= esc($category->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Belum ada kategori</option>
                    <?php endif; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['category_id']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Tags Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">
                    <i class="bi bi-tags"></i>
                    Tags
                </h4>

                <select
                    class="form-select"
                    id="tags"
                    name="tags[]"
                    multiple="multiple"
                    style="width: 100%;">
                    <?php if (isset($tags) && !empty($tags)): ?>
                        <?php foreach ($tags as $tag): ?>
                            <option
                                value="<?= $tag->id ?>"
                                <?php if (isset($post_tags) && in_array($tag->id, array_column($post_tags, 'id'))): ?>
                                selected
                                <?php endif; ?>>
                                <?= esc($tag->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="form-text mt-2">Pilih atau ketik untuk menambah tag baru</div>
            </div>

            <!-- Featured Post Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">
                    <i class="bi bi-star"></i>
                    Artikel Unggulan
                </h4>

                <div class="featured-toggle <?= old('is_featured', $post->is_featured ?? 0) == 1 ? 'active' : '' ?>" id="featuredToggle">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="is_featured"
                            name="is_featured"
                            value="1"
                            <?= old('is_featured', $post->is_featured ?? 0) == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">
                            <strong>Tandai sebagai Artikel Unggulan</strong>
                            <small class="d-block text-muted mt-1">Artikel akan ditampilkan di halaman utama</small>
                        </label>
                    </div>
                </div>
            </div>

            <?php if ($isEdit): ?>
                <!-- Article Info Card -->
                <div class="sidebar-card">
                    <h4 class="sidebar-card-title">
                        <i class="bi bi-info-circle"></i>
                        Informasi
                    </h4>

                    <div class="small">
                        <div class="mb-2">
                            <strong>Author:</strong><br>
                            <?= esc($post->author_name ?? 'Unknown') ?>
                        </div>
                        <div class="mb-2">
                            <strong>Dibuat:</strong><br>
                            <?= date('d M Y H:i', strtotime($post->created_at)) ?>
                        </div>
                        <?php if (isset($post->published_at) && $post->published_at): ?>
                            <div class="mb-2">
                                <strong>Dipublish:</strong><br>
                                <?= date('d M Y H:i', strtotime($post->published_at)) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($post->views)): ?>
                            <div>
                                <strong>Views:</strong><br>
                                <?= number_format($post->views) ?> kali
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Auto-save Indicator -->
<div class="autosave-indicator" id="autosaveIndicator">
    <i class="bi bi-check-circle-fill"></i>
    <span class="text">Draft tersimpan otomatis</span>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Summernote JS -->
<script src="<?= base_url('assets/plugins/summernote/summernote-bs5.min.js') ?>"></script>
<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // ==========================================
        // SUMMERNOTE WYSIWYG EDITOR
        // ==========================================
        $('#content').summernote({
            height: 400,
            minHeight: 400,
            maxHeight: 600,
            placeholder: 'Tuliskan konten artikel di sini...',
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
                onImageUpload: function(files) {
                    // Handle image upload
                    uploadImage(files[0]);
                },
                onChange: function(contents) {
                    // Auto-save to localStorage
                    autoSaveDraft();
                }
            }
        });

        // Upload image to Summernote
        function uploadImage(file) {
            let formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: '<?= base_url('admin/content/upload-image') ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#content').summernote('insertImage', response.url);
                    } else {
                        alert('Gagal upload gambar: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat upload gambar');
                }
            });
        }

        // ==========================================
        // SELECT2 FOR TAGS
        // ==========================================
        $('#tags').select2({
            tags: true,
            tokenSeparators: [','],
            placeholder: 'Pilih atau ketik tag...',
            allowClear: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                }
            }
        });

        // ==========================================
        // AUTO-GENERATE SLUG FROM TITLE
        // ==========================================
        $('#title').on('input', function() {
            let title = $(this).val();
            let slug = generateSlug(title);
            $('#slug').val(slug);
            $('#slugPreview').text(slug || 'artikel-slug');
        });

        // Regenerate slug button
        $('#regenerateSlug').on('click', function() {
            let title = $('#title').val();
            let slug = generateSlug(title);
            $('#slug').val(slug);
            $('#slugPreview').text(slug || 'artikel-slug');
        });

        // Update slug preview on manual change
        $('#slug').on('input', function() {
            $('#slugPreview').text($(this).val() || 'artikel-slug');
        });

        // Function to generate slug
        function generateSlug(text) {
            return text
                .toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with -
                .replace(/--+/g, '-') // Replace multiple - with single -
                .trim();
        }

        // ==========================================
        // CHARACTER COUNTERS
        // ==========================================
        function updateCharCounter(inputId, counterId, maxLength) {
            let input = $('#' + inputId);
            let counter = $('#' + counterId);

            input.on('input', function() {
                let length = $(this).val().length;
                counter.text(length + ' / ' + maxLength);

                // Change color based on length
                counter.removeClass('warning danger');
                if (length > maxLength * 0.9) {
                    counter.addClass('danger');
                } else if (length > maxLength * 0.75) {
                    counter.addClass('warning');
                }
            });

            // Trigger on page load
            input.trigger('input');
        }

        updateCharCounter('excerpt', 'excerptCounter', 500);
        updateCharCounter('meta_description', 'metaCounter', 160);

        // ==========================================
        // FEATURED IMAGE UPLOAD
        // ==========================================
        const imageContainer = $('#imageUploadContainer');
        const imageInput = $('#featured_image');
        const uploadPlaceholder = $('#uploadPlaceholder');
        const previewWrapper = $('#imagePreviewWrapper');
        const imagePreview = $('#imagePreview');
        const removeBtn = $('#removeImageBtn');

        // Click to upload
        imageContainer.on('click', function(e) {
            if (!$(e.target).is(removeBtn) && !$(e.target).closest(removeBtn).length) {
                imageInput.click();
            }
        });

        // Image selected
        imageInput.on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Hanya file gambar yang diizinkan!');
                    return;
                }

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.attr('src', e.target.result);
                    uploadPlaceholder.hide();
                    previewWrapper.addClass('show');
                    imageContainer.addClass('has-image');
                };
                reader.readAsDataURL(file);
            }
        });

        // Remove image
        removeBtn.on('click', function(e) {
            e.stopPropagation();
            imageInput.val('');
            imagePreview.attr('src', '');
            previewWrapper.removeClass('show');
            uploadPlaceholder.show();
            imageContainer.removeClass('has-image');
        });

        // ==========================================
        // FEATURED POST TOGGLE
        // ==========================================
        $('#is_featured').on('change', function() {
            if ($(this).is(':checked')) {
                $('#featuredToggle').addClass('active');
            } else {
                $('#featuredToggle').removeClass('active');
            }
        });

        // ==========================================
        // AUTO-SAVE DRAFT
        // ==========================================
        let autoSaveTimer;

        function autoSaveDraft() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                // Save to localStorage
                const formData = {
                    title: $('#title').val(),
                    slug: $('#slug').val(),
                    excerpt: $('#excerpt').val(),
                    content: $('#content').summernote('code'),
                    category_id: $('#category_id').val(),
                    meta_description: $('#meta_description').val(),
                    meta_keywords: $('#meta_keywords').val(),
                    is_featured: $('#is_featured').is(':checked') ? 1 : 0,
                    tags: $('#tags').val(),
                    timestamp: new Date().getTime()
                };

                localStorage.setItem('post_draft', JSON.stringify(formData));

                // Show indicator
                $('#autosaveIndicator').addClass('show');
                setTimeout(function() {
                    $('#autosaveIndicator').removeClass('show');
                }, 2000);
            }, 3000); // Auto-save after 3 seconds of inactivity
        }

        // Restore draft on page load
        <?php if (!$isEdit): ?>
            const savedDraft = localStorage.getItem('post_draft');
            if (savedDraft) {
                const draft = JSON.parse(savedDraft);

                // Check if draft is less than 24 hours old
                const now = new Date().getTime();
                const draftAge = now - draft.timestamp;
                const oneDayInMs = 24 * 60 * 60 * 1000;

                if (draftAge < oneDayInMs) {
                    if (confirm('Draft tersimpan ditemukan. Apakah Anda ingin memulihkannya?')) {
                        $('#title').val(draft.title);
                        $('#slug').val(draft.slug);
                        $('#excerpt').val(draft.excerpt);
                        $('#content').summernote('code', draft.content);
                        $('#category_id').val(draft.category_id);
                        $('#meta_description').val(draft.meta_description);
                        $('#meta_keywords').val(draft.meta_keywords);
                        $('#is_featured').prop('checked', draft.is_featured == 1);
                        if (draft.tags) {
                            $('#tags').val(draft.tags).trigger('change');
                        }

                        // Trigger counters update
                        $('#excerpt').trigger('input');
                        $('#meta_description').trigger('input');
                    }
                }
            }
        <?php endif; ?>

        // Clear draft on successful submit
        $('#postForm').on('submit', function() {
            localStorage.removeItem('post_draft');
        });

        // ==========================================
        // FORM VALIDATION
        // ==========================================
        $('#postForm').on('submit', function(e) {
            let isValid = true;

            // Check title
            if ($('#title').val().trim().length < 5) {
                alert('Judul minimal 5 karakter');
                $('#title').focus();
                isValid = false;
            }

            // Check slug
            if ($('#slug').val().trim().length === 0) {
                alert('Slug tidak boleh kosong');
                $('#slug').focus();
                isValid = false;
            }

            // Check content
            const content = $('#content').summernote('code');
            if (content.replace(/<[^>]*>/g, '').trim().length < 50) {
                alert('Konten minimal 50 karakter');
                isValid = false;
            }

            // Check category
            if ($('#category_id').val() === '') {
                alert('Pilih kategori artikel');
                $('#category_id').focus();
                isValid = false;
            }

            return isValid;
        });

        // ==========================================
        // KEYBOARD SHORTCUTS
        // ==========================================
        $(document).on('keydown', function(e) {
            // Ctrl+S or Cmd+S to save draft
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('button[name="status"][value="draft"]').click();
            }
        });

        // Show unsaved changes warning
        let formChanged = false;
        $('#postForm :input').on('change input', function() {
            formChanged = true;
        });

        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            }
        });

        $('#postForm').on('submit', function() {
            formChanged = false;
        });

        console.log('✓ Post Form initialized successfully');
    });
</script>
<?= $this->endSection() ?>