<?php

/**
 * View: Admin Categories Management
 * Controller: Admin\ContentController::categories()
 * Description: Manage blog post categories dengan CRUD operations
 * 
 * Features:
 * - List semua categories dengan post count
 * - Inline add new category
 * - Quick edit modal
 * - Delete with confirmation
 * - Post count per category
 * - Search & filter
 * - Sortable list
 * - Color coding badges
 * - Responsive design
 * 
 * @package App\Views\Admin\Content\Categories
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
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

    /* Stats Card */
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        border-left: 4px solid #667eea;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: #e8eaf6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 24px;
    }

    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 4px 0;
    }

    .stat-info p {
        font-size: 13px;
        color: #6c757d;
        margin: 0;
    }

    /* Grid Layout */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 25px;
    }

    /* Categories List Card */
    .categories-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .card-header-custom h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    /* Search Box */
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }

    .search-box input {
        padding-left: 40px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    /* Category Item */
    .category-item {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        background: white;
    }

    .category-item:hover {
        border-color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .category-name {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .category-actions {
        display: flex;
        gap: 6px;
    }

    .btn-category-action {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-category-action.edit {
        background: #667eea;
        color: white;
    }

    .btn-category-action.edit:hover {
        background: #5568d3;
    }

    .btn-category-action.delete {
        background: #e74c3c;
        color: white;
    }

    .btn-category-action.delete:hover {
        background: #c0392b;
    }

    .category-meta {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .category-slug {
        font-size: 12px;
        color: #6c757d;
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 4px 10px;
        border-radius: 4px;
    }

    .post-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #e8eaf6;
        color: #667eea;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .category-description {
        color: #6c757d;
        font-size: 13px;
        margin: 8px 0 0 0;
        line-height: 1.5;
    }

    /* Add Category Card */
    .add-category-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 20px;
    }

    .add-category-header {
        margin-bottom: 20px;
    }

    .add-category-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 8px 0;
    }

    .add-category-header p {
        font-size: 13px;
        color: #6c757d;
        margin: 0;
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

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #6c757d;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #adb5bd;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .add-category-card {
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

        .category-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .category-meta {
            flex-wrap: wrap;
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
                <i class="bi bi-folder me-2"></i>
                Kelola Kategori
            </h1>
            <p>Manage kategori artikel untuk mengorganisir konten blog</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= base_url('admin/content/posts') ?>" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Artikel
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/content/posts') ?>">Konten</a></li>
        <li class="breadcrumb-item active">Kategori</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Stats Card -->
<div class="stats-card">
    <div class="stat-item">
        <div class="stat-icon">
            <i class="bi bi-folder-fill"></i>
        </div>
        <div class="stat-info">
            <h3><?= count($categories ?? []) ?></h3>
            <p>Total Kategori Artikel</p>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- Categories List -->
    <div class="categories-card">
        <div class="card-header-custom">
            <h3>
                <i class="bi bi-list-ul me-2"></i>
                Daftar Kategori
            </h3>
        </div>

        <!-- Search Box -->
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input
                type="text"
                class="form-control"
                id="searchCategory"
                placeholder="Cari kategori...">
        </div>

        <!-- Categories List -->
        <div id="categoriesList">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-item" data-category-name="<?= strtolower(esc($category->name)) ?>">
                        <div class="category-header">
                            <h4 class="category-name"><?= esc($category->name) ?></h4>
                            <div class="category-actions">
                                <button
                                    type="button"
                                    class="btn-category-action edit"
                                    onclick="editCategory(<?= $category->id ?>, '<?= esc($category->name, 'js') ?>', '<?= esc($category->slug, 'js') ?>', '<?= esc($category->description ?? '', 'js') ?>')"
                                    title="Edit Kategori">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($category->post_count == 0): ?>
                                    <button
                                        type="button"
                                        class="btn-category-action delete"
                                        onclick="deleteCategory(<?= $category->id ?>, '<?= esc($category->name, 'js') ?>')"
                                        title="Hapus Kategori">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button
                                        type="button"
                                        class="btn-category-action delete"
                                        disabled
                                        title="Tidak bisa dihapus (<?= $category->post_count ?> artikel)">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="category-meta">
                            <span class="category-slug">
                                <i class="bi bi-link-45deg"></i>
                                <?= esc($category->slug) ?>
                            </span>
                            <span class="post-count-badge">
                                <i class="bi bi-file-text"></i>
                                <?= $category->post_count ?> Artikel
                            </span>
                        </div>

                        <?php if (!empty($category->description)): ?>
                            <p class="category-description">
                                <?= esc($category->description) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="bi bi-folder-x"></i>
                    <h5>Belum Ada Kategori</h5>
                    <p>Tambahkan kategori pertama Anda untuk mengorganisir artikel</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="add-category-card">
        <div class="add-category-header">
            <h3 id="formTitle">
                <i class="bi bi-plus-circle me-2"></i>
                Tambah Kategori Baru
            </h3>
            <p id="formSubtitle">Buat kategori baru untuk artikel</p>
        </div>

        <form id="categoryForm" method="POST" action="<?= base_url('admin/content/categories/store') ?>">
            <?= csrf_field() ?>
            <input type="hidden" id="categoryId" name="category_id" value="">
            <input type="hidden" id="formMethod" name="_method" value="">

            <!-- Category Name -->
            <div class="mb-3">
                <label for="name" class="form-label">
                    Nama Kategori<span class="required">*</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    placeholder="Contoh: Berita, Tutorial, Opini"
                    required>
                <div class="invalid-feedback" id="nameError"></div>
            </div>

            <!-- Slug -->
            <div class="mb-3">
                <label for="slug" class="form-label">
                    Slug<span class="required">*</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="slug"
                    name="slug"
                    placeholder="berita-spk"
                    pattern="[a-z0-9-]+"
                    required>
                <div class="form-text">Huruf kecil dan tanda (-) saja</div>
                <div class="invalid-feedback" id="slugError"></div>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="form-label">
                    Deskripsi
                </label>
                <textarea
                    class="form-control"
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Deskripsi singkat kategori (opsional)..."></textarea>
                <div class="form-text">Maksimal 200 karakter</div>
            </div>

            <!-- Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-check-circle me-2"></i>
                    <span id="submitBtnText">Simpan Kategori</span>
                </button>
                <button type="button" class="btn btn-secondary d-none" id="cancelBtn" onclick="resetForm()">
                    <i class="bi bi-x-circle me-2"></i>
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // ==========================================
        // AUTO-GENERATE SLUG FROM NAME
        // ==========================================
        $('#name').on('input', function() {
            let name = $(this).val();
            let slug = generateSlug(name);
            $('#slug').val(slug);
        });

        function generateSlug(text) {
            return text
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
        }

        // ==========================================
        // SEARCH CATEGORIES
        // ==========================================
        $('#searchCategory').on('input', function() {
            let searchTerm = $(this).val().toLowerCase();

            $('.category-item').each(function() {
                let categoryName = $(this).data('category-name');
                if (categoryName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // ==========================================
        // FORM VALIDATION
        // ==========================================
        $('#categoryForm').on('submit', function(e) {
            // Clear previous errors
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            let isValid = true;

            // Validate name
            if ($('#name').val().trim().length < 3) {
                $('#name').addClass('is-invalid');
                $('#nameError').text('Nama kategori minimal 3 karakter');
                isValid = false;
            }

            // Validate slug
            let slug = $('#slug').val();
            if (slug.length < 2) {
                $('#slug').addClass('is-invalid');
                $('#slugError').text('Slug minimal 2 karakter');
                isValid = false;
            } else if (!/^[a-z0-9-]+$/.test(slug)) {
                $('#slug').addClass('is-invalid');
                $('#slugError').text('Slug hanya boleh huruf kecil, angka, dan tanda -');
                isValid = false;
            }

            return isValid;
        });

        console.log('✓ Categories page initialized');
    });

    // ==========================================
    // EDIT CATEGORY
    // ==========================================
    function editCategory(id, name, slug, description) {
        // Update form title
        $('#formTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Kategori');
        $('#formSubtitle').text('Update informasi kategori');

        // Fill form
        $('#categoryId').val(id);
        $('#name').val(name);
        $('#slug').val(slug);
        $('#description').val(description);

        // Update form action
        $('#categoryForm').attr('action', '<?= base_url('admin/content/categories/update') ?>/' + id);
        $('#formMethod').val('PUT');

        // Update button
        $('#submitBtnText').text('Update Kategori');
        $('#cancelBtn').removeClass('d-none');

        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#categoryForm').offset().top - 100
        }, 500);
    }

    // ==========================================
    // RESET FORM
    // ==========================================
    function resetForm() {
        // Reset form
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#formMethod').val('');

        // Reset form action
        $('#categoryForm').attr('action', '<?= base_url('admin/content/categories/store') ?>');

        // Update title
        $('#formTitle').html('<i class="bi bi-plus-circle me-2"></i>Tambah Kategori Baru');
        $('#formSubtitle').text('Buat kategori baru untuk artikel');

        // Update button
        $('#submitBtnText').text('Simpan Kategori');
        $('#cancelBtn').addClass('d-none');

        // Clear errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // ==========================================
    // DELETE CATEGORY
    // ==========================================
    function deleteCategory(id, name) {
        Swal.fire({
            title: 'Hapus Kategori?',
            html: `Apakah Anda yakin ingin menghapus kategori <strong>${name}</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: '<i class="bi bi-trash me-2"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit delete request
                $.ajax({
                    url: '<?= base_url('admin/content/categories/delete') ?>/' + id,
                    type: 'POST',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Kategori berhasil dihapus',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus kategori'
                        });
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>