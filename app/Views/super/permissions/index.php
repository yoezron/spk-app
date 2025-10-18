<?php

/**
 * View: Permissions Index
 * Menampilkan daftar permissions grouped by module
 * 
 * @var array $groupedPermissions Permissions grouped by module
 * @var int $totalPermissions Total permission count
 * @var int $moduleCount Total module count
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-key me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-primary">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Total Permissions</span>
                        <span class="widget-stats-amount"><?= number_format($totalPermissions) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-info">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Modules</span>
                        <span class="widget-stats-amount"><?= number_format($moduleCount) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-success">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">System Protected</span>
                        <span class="widget-stats-amount"><i class="fas fa-check-circle"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Daftar Permissions
                    </h5>
                    <div class="btn-group" role="group">
                        <a href="<?= base_url('super/permissions/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Tambah Permission
                        </a>
                        <button type="button" class="btn btn-success" onclick="syncPermissions()">
                            <i class="fas fa-sync me-1"></i>Sync Permissions
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchPermission"
                                placeholder="Cari permission atau module...">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            Menampilkan <?= count($groupedPermissions) ?> module dengan <?= $totalPermissions ?> permissions
                        </small>
                    </div>
                </div>

                <!-- Permissions by Module -->
                <?php if (empty($groupedPermissions)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada permission yang terdaftar. Silakan tambah permission baru atau lakukan sync.
                    </div>
                <?php else: ?>
                    <div class="accordion" id="permissionsAccordion">
                        <?php $index = 0;
                        foreach ($groupedPermissions as $moduleName => $moduleData): ?>
                            <div class="accordion-item module-item" data-module="<?= esc($moduleName) ?>">
                                <h2 class="accordion-header" id="heading<?= $index ?>">
                                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?= $index ?>"
                                        aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div>
                                                <i class="fas fa-folder-open me-2"></i>
                                                <strong><?= esc($moduleData['name']) ?></strong>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $moduleData['count'] ?> permissions
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $index ?>"
                                    class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                    data-bs-parent="#permissionsAccordion">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover permission-table">
                                                <thead>
                                                    <tr>
                                                        <th width="40%">Permission Name</th>
                                                        <th width="35%">Description</th>
                                                        <th width="10%" class="text-center">Roles</th>
                                                        <th width="15%" class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($moduleData['permissions'] as $permission): ?>
                                                        <tr class="permission-row" data-permission="<?= esc($permission->name) ?>">
                                                            <td>
                                                                <code class="text-primary"><?= esc($permission->name) ?></code>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"><?= esc($permission->description) ?></small>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($permission->role_count > 0): ?>
                                                                    <a href="<?= base_url('super/permissions/' . $permission->id . '/roles') ?>"
                                                                        class="badge bg-info text-decoration-none"
                                                                        title="Lihat roles yang memiliki permission ini">
                                                                        <?= $permission->role_count ?> role(s)
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Not assigned</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="<?= base_url('super/permissions/' . $permission->id . '/roles') ?>"
                                                                        class="btn btn-info"
                                                                        title="Lihat Roles">
                                                                        <i class="fas fa-users"></i>
                                                                    </a>
                                                                    <a href="<?= base_url('super/permissions/' . $permission->id . '/edit') ?>"
                                                                        class="btn btn-warning"
                                                                        title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-danger"
                                                                        onclick="deletePermission(<?= $permission->id ?>, '<?= esc($permission->name) ?>', <?= $permission->role_count ?>)"
                                                                        title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php $index++;
                        endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi Permission</h6>
                <ul class="mb-0">
                    <li><strong>Format Permission:</strong> <code>module.action</code> (contoh: <code>member.view</code>, <code>forum.manage</code>)</li>
                    <li><strong>Naming Convention:</strong> Gunakan lowercase dan underscore untuk action (view, create, edit, delete, manage)</li>
                    <li><strong>Module:</strong> Nama modul sesuai dengan fitur sistem (member, forum, survey, complaint, content, system)</li>
                    <li><strong>Assignment:</strong> Permission dapat diassign ke multiple roles melalui menu Role Management</li>
                    <li><strong>Sync:</strong> Gunakan tombol "Sync Permissions" untuk menambahkan predefined permissions ke database</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    // Search functionality
    document.getElementById('searchPermission').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();

        // Search in modules
        document.querySelectorAll('.module-item').forEach(module => {
            const moduleName = module.dataset.module.toLowerCase();
            let hasMatch = moduleName.includes(searchTerm);

            // Search in permissions within module
            if (!hasMatch) {
                module.querySelectorAll('.permission-row').forEach(row => {
                    const permName = row.dataset.permission.toLowerCase();
                    const permDesc = row.querySelector('small').textContent.toLowerCase();

                    if (permName.includes(searchTerm) || permDesc.includes(searchTerm)) {
                        hasMatch = true;
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            } else {
                // Show all permissions in module
                module.querySelectorAll('.permission-row').forEach(row => {
                    row.style.display = '';
                });
            }

            // Show/hide module
            module.style.display = hasMatch ? '' : 'none';

            // Expand module if has match
            if (hasMatch && searchTerm !== '') {
                const collapse = module.querySelector('.accordion-collapse');
                const button = module.querySelector('.accordion-button');
                collapse.classList.add('show');
                button.classList.remove('collapsed');
            }
        });
    });

    // Delete permission
    function deletePermission(id, name, roleCount) {
        if (roleCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Permission Masih Digunakan',
                html: `Permission <strong>${name}</strong> masih digunakan oleh <strong>${roleCount} role(s)</strong>.<br><br>
                   Hapus permission dari role tersebut terlebih dahulu.`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus permission <strong>${name}</strong>?<br><br>
               <span class="text-danger">Tindakan ini tidak dapat dibatalkan!</span>`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('super/permissions') ?>/' + id + '/delete';

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

    // Sync permissions
    function syncPermissions() {
        Swal.fire({
            icon: 'question',
            title: 'Sync Permissions',
            html: 'Sistem akan menambahkan predefined permissions yang belum ada di database.<br><br>Lanjutkan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sync!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    html: 'Sedang melakukan sinkronisasi permissions...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Redirect to sync endpoint
                window.location.href = '<?= base_url('super/permissions/sync') ?>';
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
<?php $this->endSection(); ?>