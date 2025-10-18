<?php

/**
 * View: Menu Edit
 * Form edit menu existing
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-edit me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/menus') ?>">Menu Management</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Menu</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Validation Error!</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>Form Edit Menu
                </h5>
            </div>

            <form action="<?= base_url('super/menus/' . $menu->id . '/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="card-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Parent Menu -->
                            <div class="mb-3">
                                <label for="parent_id" class="form-label">
                                    Parent Menu <small class="text-muted">(Opsional)</small>
                                </label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">- Top Level Menu -</option>
                                    <?php foreach ($parentMenus as $parent): ?>
                                        <?php if ($parent->id != $menu->id): ?>
                                            <option value="<?= $parent->id ?>"
                                                <?= (old('parent_id', $menu->parent_id) == $parent->id) ? 'selected' : '' ?>>
                                                <?= esc($parent->title) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pilih parent menu jika ini adalah sub-menu</small>
                            </div>

                            <!-- Title -->
                            <div class="mb-3">
                                <label for="title" class="form-label">
                                    Judul Menu <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                                    id="title"
                                    name="title"
                                    value="<?= old('title', $menu->title) ?>"
                                    placeholder="Contoh: Dashboard, Data Master, dll"
                                    required>
                                <?php if (session('errors.title')): ?>
                                    <div class="invalid-feedback"><?= session('errors.title') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Icon -->
                            <div class="mb-3">
                                <label for="icon" class="form-label">
                                    Icon Class <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="icon"
                                    name="icon"
                                    value="<?= old('icon', $menu->icon) ?>"
                                    placeholder="Contoh: fas fa-home, icon-home">
                                <small class="form-text text-muted">
                                    Gunakan class icon dari FontAwesome atau Material Icons
                                </small>
                            </div>

                            <!-- URL -->
                            <div class="mb-3">
                                <label for="url" class="form-label">
                                    URL <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="url"
                                    name="url"
                                    value="<?= old('url', $menu->url) ?>"
                                    placeholder="Contoh: admin/dashboard">
                                <small class="form-text text-muted">
                                    URL relatif tanpa base_url. Kosongkan jika menggunakan Route Name
                                </small>
                            </div>

                            <!-- Route Name -->
                            <div class="mb-3">
                                <label for="route_name" class="form-label">
                                    Route Name <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="route_name"
                                    name="route_name"
                                    value="<?= old('route_name', $menu->route_name) ?>"
                                    placeholder="Contoh: admin.dashboard">
                                <small class="form-text text-muted">
                                    Named route dari Routes.php. Prioritas lebih tinggi dari URL
                                </small>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Permission Key -->
                            <div class="mb-3">
                                <label for="permission_key" class="form-label">
                                    Permission Required <small class="text-muted">(Opsional)</small>
                                </label>
                                <select class="form-select" id="permission_key" name="permission_key">
                                    <option value="">- Public Menu (Tidak perlu permission) -</option>
                                    <?php foreach ($permissions as $permission): ?>
                                        <option value="<?= esc($permission->name) ?>"
                                            <?= (old('permission_key', $menu->permission_key) == $permission->name) ? 'selected' : '' ?>>
                                            <?= esc($permission->name) ?> - <?= esc($permission->description) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    Menu hanya ditampilkan jika user memiliki permission ini
                                </small>
                            </div>

                            <!-- Target -->
                            <div class="mb-3">
                                <label for="target" class="form-label">Target Link</label>
                                <select class="form-select" id="target" name="target">
                                    <option value="_self" <?= old('target', $menu->target) == '_self' ? 'selected' : '' ?>>Same Window (_self)</option>
                                    <option value="_blank" <?= old('target', $menu->target) == '_blank' ? 'selected' : '' ?>>New Window (_blank)</option>
                                </select>
                            </div>

                            <!-- Sort Order -->
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Urutan Tampilan</label>
                                <input type="number"
                                    class="form-control"
                                    id="sort_order"
                                    name="sort_order"
                                    value="<?= old('sort_order', $menu->sort_order) ?>"
                                    min="0">
                                <small class="form-text text-muted">
                                    Semakin kecil angka, semakin atas posisinya
                                </small>
                            </div>

                            <!-- Is External -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="is_external"
                                        name="is_external"
                                        value="1"
                                        <?= old('is_external', $menu->is_external) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_external">
                                        External Link
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Centang jika link mengarah ke website eksternal
                                </small>
                            </div>

                            <!-- Is Active -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="is_active"
                                        name="is_active"
                                        value="1"
                                        <?= old('is_active', $menu->is_active) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        Menu Aktif
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Menu hanya ditampilkan jika aktif
                                </small>
                            </div>

                            <!-- CSS Class -->
                            <div class="mb-3">
                                <label for="css_class" class="form-label">
                                    Custom CSS Class <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="css_class"
                                    name="css_class"
                                    value="<?= old('css_class', $menu->css_class) ?>"
                                    placeholder="Contoh: menu-highlight">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Deskripsi <small class="text-muted">(Opsional)</small>
                                </label>
                                <textarea class="form-control"
                                    id="description"
                                    name="description"
                                    rows="3"
                                    placeholder="Deskripsi menu untuk tooltip atau keterangan"><?= old('description', $menu->description) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Informasi Menu</h6>
                        <ul class="mb-0">
                            <li>Menu ID: <strong><?= $menu->id ?></strong></li>
                            <li>Dibuat: <?= date('d M Y H:i', strtotime($menu->created_at)) ?></li>
                            <?php if ($menu->updated_at): ?>
                                <li>Terakhir diupdate: <?= date('d M Y H:i', strtotime($menu->updated_at)) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($menu->children_count)): ?>
                                <li class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Menu ini memiliki <strong><?= $menu->children_count ?></strong> sub-menu
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="<?= base_url('super/menus') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-danger me-2" onclick="confirmDelete()">
                                <i class="fas fa-trash me-1"></i>Hapus Menu
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Menu
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    function confirmDelete() {
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus menu <strong><?= esc($menu->title, 'js') ?></strong>?
                   <?php if (!empty($menu->children_count)): ?>
                   <br><br><span class="text-danger">
                   <i class="fas fa-exclamation-triangle"></i>
                   Menu ini memiliki <strong><?= $menu->children_count ?></strong> sub-menu yang juga akan dihapus!
                   </span>
                   <?php endif; ?>`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('super/menus/' . $menu->id . '/delete') ?>';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
<?php $this->endSection(); ?>