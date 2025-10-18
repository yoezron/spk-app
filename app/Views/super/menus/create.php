<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-inner">
    <!-- Page Header -->
    <div class="page-header">
        <h3 class="fw-bold mb-3"><?= esc($title) ?></h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="<?= base_url('super/dashboard') ?>">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Super Admin</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('super/menus') ?>">Menu Management</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Tambah Menu</a>
            </li>
        </ul>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Error!</strong>
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Form Tambah Menu</div>
                </div>
                <form action="<?= base_url('super/menus/store') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Parent Menu -->
                                <div class="form-group">
                                    <label for="parent_id">Parent Menu <small class="text-muted">(Opsional)</small></label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">- Top Level Menu -</option>
                                        <?php foreach ($parentMenus as $parent): ?>
                                            <option value="<?= $parent->id ?>" <?= old('parent_id') == $parent->id ? 'selected' : '' ?>>
                                                <?= esc($parent->title) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Pilih parent menu jika ini adalah sub-menu</small>
                                </div>

                                <!-- Title -->
                                <div class="form-group">
                                    <label for="title">Judul Menu <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                                        id="title"
                                        name="title"
                                        value="<?= old('title') ?>"
                                        placeholder="Contoh: Dashboard, Data Master, dll"
                                        required>
                                    <?php if (session('errors.title')): ?>
                                        <div class="invalid-feedback"><?= session('errors.title') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Icon -->
                                <div class="form-group">
                                    <label for="icon">Icon Class <small class="text-muted">(Opsional)</small></label>
                                    <input type="text"
                                        class="form-control"
                                        id="icon"
                                        name="icon"
                                        value="<?= old('icon') ?>"
                                        placeholder="Contoh: fas fa-home, icon-home">
                                    <small class="form-text text-muted">
                                        Gunakan class icon dari FontAwesome atau Material Icons
                                    </small>
                                </div>

                                <!-- URL -->
                                <div class="form-group">
                                    <label for="url">URL <small class="text-muted">(Opsional)</small></label>
                                    <input type="text"
                                        class="form-control"
                                        id="url"
                                        name="url"
                                        value="<?= old('url') ?>"
                                        placeholder="Contoh: admin/dashboard">
                                    <small class="form-text text-muted">
                                        URL relatif tanpa base_url. Kosongkan jika menggunakan Route Name
                                    </small>
                                </div>

                                <!-- Route Name -->
                                <div class="form-group">
                                    <label for="route_name">Route Name <small class="text-muted">(Opsional)</small></label>
                                    <input type="text"
                                        class="form-control"
                                        id="route_name"
                                        name="route_name"
                                        value="<?= old('route_name') ?>"
                                        placeholder="Contoh: admin.dashboard">
                                    <small class="form-text text-muted">
                                        Named route dari Routes.php. Prioritas lebih tinggi dari URL
                                    </small>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Permission Key -->
                                <div class="form-group">
                                    <label for="permission_key">Permission Required <small class="text-muted">(Opsional)</small></label>
                                    <select class="form-select" id="permission_key" name="permission_key">
                                        <option value="">- Public Menu (Tidak perlu permission) -</option>
                                        <?php foreach ($permissions as $permission): ?>
                                            <option value="<?= esc($permission->name) ?>" <?= old('permission_key') == $permission->name ? 'selected' : '' ?>>
                                                <?= esc($permission->name) ?> - <?= esc($permission->description) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        Menu hanya ditampilkan jika user memiliki permission ini
                                    </small>
                                </div>

                                <!-- Target -->
                                <div class="form-group">
                                    <label for="target">Target Link</label>
                                    <select class="form-select" id="target" name="target">
                                        <option value="_self" <?= old('target') == '_self' ? 'selected' : '' ?>>Same Window (_self)</option>
                                        <option value="_blank" <?= old('target') == '_blank' ? 'selected' : '' ?>>New Window (_blank)</option>
                                    </select>
                                </div>

                                <!-- Sort Order -->
                                <div class="form-group">
                                    <label for="sort_order">Urutan Tampilan</label>
                                    <input type="number"
                                        class="form-control"
                                        id="sort_order"
                                        name="sort_order"
                                        value="<?= old('sort_order', 0) ?>"
                                        min="0">
                                    <small class="form-text text-muted">
                                        Semakin kecil angka, semakin atas posisinya
                                    </small>
                                </div>

                                <!-- Is External -->
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            id="is_external"
                                            name="is_external"
                                            value="1"
                                            <?= old('is_external') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_external">
                                            External Link
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Centang jika link mengarah ke website eksternal
                                    </small>
                                </div>

                                <!-- Is Active -->
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            id="is_active"
                                            name="is_active"
                                            value="1"
                                            <?= old('is_active', '1') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Menu Aktif
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Menu hanya ditampilkan jika aktif
                                    </small>
                                </div>

                                <!-- CSS Class -->
                                <div class="form-group">
                                    <label for="css_class">Custom CSS Class <small class="text-muted">(Opsional)</small></label>
                                    <input type="text"
                                        class="form-control"
                                        id="css_class"
                                        name="css_class"
                                        value="<?= old('css_class') ?>"
                                        placeholder="Contoh: menu-highlight">
                                </div>

                                <!-- Description -->
                                <div class="form-group">
                                    <label for="description">Deskripsi <small class="text-muted">(Opsional)</small></label>
                                    <textarea class="form-control"
                                        id="description"
                                        name="description"
                                        rows="3"
                                        placeholder="Deskripsi menu untuk tooltip atau keterangan"><?= old('description') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-action">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Menu
                        </button>
                        <a href="<?= base_url('super/menus') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
<?= $this->endSection() ?>