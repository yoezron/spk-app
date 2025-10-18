<?= $this->extend('layouts/super') ?>

<?= $this->section('styles') ?>
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .permission-module {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .module-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #667eea;
    }

    .module-title {
        font-size: 1rem;
        font-weight: 600;
        color: #2c3e50;
        text-transform: capitalize;
        margin: 0;
    }

    .module-select-all {
        font-size: 0.875rem;
        color: #667eea;
        cursor: pointer;
        user-select: none;
    }

    .module-select-all:hover {
        text-decoration: underline;
    }

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .permission-item {
        background: white;
        border-radius: 8px;
        padding: 0.75rem;
        border: 2px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .permission-item:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    .permission-item.selected {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.1);
    }

    .permission-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .permission-label {
        font-size: 0.875rem;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        cursor: pointer;
    }

    .permission-description {
        font-size: 0.75rem;
        color: #6c757d;
        margin: 0;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 1px solid #e9ecef;
    }

    .info-alert {
        background: #e7f3ff;
        border-left: 4px solid #2196f3;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .warning-alert {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">
                <i class="material-icons-outlined align-middle">shield</i>
                <?= isset($role) ? 'Edit Role' : 'Tambah Role Baru' ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/roles') ?>">Roles</a></li>
                    <li class="breadcrumb-item active"><?= isset($role) ? 'Edit' : 'Tambah Baru' ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="material-icons-outlined align-middle me-2">error</i>
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($memberCount) && $memberCount > 0): ?>
        <div class="warning-alert">
            <i class="material-icons-outlined align-middle me-2">warning</i>
            <strong>Perhatian:</strong> Role ini memiliki <strong><?= number_format($memberCount) ?> members</strong>.
            Perubahan permissions akan mempengaruhi akses mereka.
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="form-card">
                <form action="<?= isset($role) ? base_url('super/roles/' . $role->id . '/update') : base_url('super/roles/store') ?>"
                    method="POST" id="roleForm">
                    <?= csrf_field() ?>

                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="title" class="form-label">
                                Nama Role <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="title"
                                name="title"
                                value="<?= old('title', $role->title ?? '') ?>"
                                placeholder="e.g., pengurus, koordinator"
                                required>
                            <small class="text-muted">Gunakan huruf kecil, tanpa spasi (gunakan underscore jika perlu)</small>
                        </div>

                        <div class="col-md-6">
                            <label for="description" class="form-label">Deskripsi</label>
                            <input type="text"
                                class="form-control"
                                id="description"
                                name="description"
                                value="<?= old('description', $role->description ?? '') ?>"
                                placeholder="Deskripsi singkat role ini">
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="material-icons-outlined align-middle">vpn_key</i>
                            Permissions
                        </h5>
                        <div class="info-alert">
                            <i class="material-icons-outlined align-middle me-2">info</i>
                            Pilih permissions yang akan diberikan kepada role ini.
                            Anda dapat memilih satu per satu atau pilih semua per modul.
                        </div>

                        <?php if (!empty($permissions)): ?>
                            <?php foreach ($permissions as $module => $modulePermissions): ?>
                                <div class="permission-module">
                                    <div class="module-header">
                                        <h6 class="module-title">
                                            <i class="material-icons-outlined align-middle me-2">folder</i>
                                            <?= esc(ucfirst($module)) ?>
                                        </h6>
                                        <span class="module-select-all" onclick="toggleModulePermissions('<?= esc($module) ?>')">
                                            <i class="material-icons-outlined align-middle" style="font-size: 16px;">check_box</i>
                                            Pilih Semua
                                        </span>
                                    </div>

                                    <div class="permission-grid">
                                        <?php foreach ($modulePermissions as $permission): ?>
                                            <div class="permission-item <?= in_array($permission->id, $rolePermissionIds ?? []) ? 'selected' : '' ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox module-<?= esc($module) ?>"
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="<?= $permission->id ?>"
                                                        id="perm_<?= $permission->id ?>"
                                                        <?= in_array($permission->id, $rolePermissionIds ?? []) ? 'checked' : '' ?>
                                                        onchange="updatePermissionItem(this)">
                                                    <label class="form-check-label permission-label" for="perm_<?= $permission->id ?>">
                                                        <?= esc($permission->name) ?>
                                                    </label>
                                                    <p class="permission-description">
                                                        <?= esc($permission->description) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="material-icons-outlined align-middle me-2">warning</i>
                                Belum ada permissions tersedia. Silakan buat permissions terlebih dahulu.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="<?= base_url('super/roles') ?>" class="btn btn-outline-secondary">
                            <i class="material-icons-outlined align-middle">close</i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="material-icons-outlined align-middle">save</i>
                            <?= isset($role) ? 'Update Role' : 'Buat Role' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleModulePermissions(module) {
        const checkboxes = document.querySelectorAll(`.module-${module}`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);

        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
            updatePermissionItem(checkbox);
        });
    }

    function updatePermissionItem(checkbox) {
        const permissionItem = checkbox.closest('.permission-item');
        if (checkbox.checked) {
            permissionItem.classList.add('selected');
        } else {
            permissionItem.classList.remove('selected');
        }
    }

    // Form validation
    document.getElementById('roleForm').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();

        if (!title) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Nama Role Wajib',
                text: 'Silakan isi nama role terlebih dahulu.'
            });
            return;
        }

        // Check if title contains spaces
        if (title.includes(' ')) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Format Nama Tidak Valid',
                text: 'Nama role tidak boleh mengandung spasi. Gunakan underscore (_) jika perlu.'
            });
            return;
        }
    });
</script>
<?= $this->endSection() ?>