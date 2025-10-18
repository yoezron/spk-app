<?php

/**
 * View: Permission Roles
 * Menampilkan list roles yang memiliki permission tertentu
 * 
 * @var object $permission Permission object
 * @var array $roles List of roles that have this permission
 * @var int $role_count Total role count
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-users-cog me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/permissions') ?>">Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Permission Info Card -->
<div class="row">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>Permission Details
                    </h5>
                    <a href="<?= base_url('super/permissions/' . $permission->id . '/edit') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Permission
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="150">Permission Name:</th>
                                <td><code class="fs-5 text-primary"><?= esc($permission->name) ?></code></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td><?= esc($permission->description) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="150">Permission ID:</th>
                                <td><?= esc($permission->id) ?></td>
                            </tr>
                            <tr>
                                <th>Total Roles:</th>
                                <td>
                                    <span class="badge bg-info fs-6"><?= $role_count ?> role(s)</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-info">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Total Roles</span>
                        <span class="widget-stats-amount"><?= number_format($role_count) ?></span>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Total Members</span>
                        <span class="widget-stats-amount">
                            <?= number_format(array_sum(array_column($roles, 'member_count'))) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-primary">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Permission Status</span>
                        <span class="widget-stats-amount">
                            <i class="fas fa-check-circle text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Roles List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Roles dengan Permission Ini
                    </h5>
                    <a href="<?= base_url('super/permissions') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Permissions
                    </a>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($roles)): ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Role</h5>
                        <p class="text-muted">
                            Permission ini belum diassign ke role manapun.<br>
                            Assign permission ini ke role melalui menu <a href="<?= base_url('super/roles') ?>">Role Management</a>.
                        </p>
                        <a href="<?= base_url('super/roles') ?>" class="btn btn-primary mt-2">
                            <i class="fas fa-users-cog me-1"></i>Kelola Roles
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Roles Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="rolesTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Role Name</th>
                                    <th width="35%">Description</th>
                                    <th width="12%" class="text-center">Members</th>
                                    <th width="13%" class="text-center">Assigned Date</th>
                                    <th width="15%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <span class="text-white fw-bold">
                                                        <?= strtoupper(substr($role->title, 0, 2)) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong><?= esc($role->title) ?></strong>
                                                    <?php if ($role->title === 'superadmin'): ?>
                                                        <span class="badge bg-danger ms-2">SUPER</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= esc($role->description ?: '-') ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">
                                                <?= number_format($role->member_count) ?> member(s)
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <?= date('d M Y', strtotime($role->assigned_at)) ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('super/roles/' . $role->id . '/members') ?>"
                                                    class="btn btn-info"
                                                    title="Lihat Members">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                <a href="<?= base_url('super/roles/' . $role->id . '/edit') ?>"
                                                    class="btn btn-warning"
                                                    title="Edit Role">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Info -->
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Total <strong><?= $role_count ?> role(s)</strong> memiliki permission
                        <code><?= esc($permission->name) ?></code> dengan total
                        <strong><?= number_format(array_sum(array_column($roles, 'member_count'))) ?> member(s)</strong>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Additional Info Card -->
<?php if (!empty($roles)): ?>
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Informasi Penting</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Permission ini sedang digunakan oleh <strong><?= $role_count ?> role(s)</strong></li>
                        <li>Menghapus permission akan mempengaruhi semua role yang tercantum</li>
                        <li>Perubahan permission name harus diupdate di code yang menggunakan permission ini</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('super/permissions/' . $permission->id . '/edit') ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Permission
                        </a>
                        <a href="<?= base_url('super/roles') ?>" class="btn btn-primary">
                            <i class="fas fa-users-cog me-1"></i>Manage Roles
                        </a>
                        <a href="<?= base_url('super/permissions') ?>" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i>All Permissions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        // Initialize DataTable if roles exist
        <?php if (!empty($roles)): ?>
            $('#rolesTable').DataTable({
                responsive: true,
                order: [
                    [1, 'asc']
                ], // Sort by role name
                pageLength: 10,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ role",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 role",
                    infoFiltered: "(difilter dari _MAX_ total role)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data",
                    zeroRecords: "Tidak ada data yang cocok"
                },
                columnDefs: [{
                        orderable: false,
                        targets: [0, 5]
                    } // Disable ordering on # and Actions columns
                ]
            });
        <?php endif; ?>
    });

    // Copy permission name to clipboard
    function copyPermissionName() {
        const permissionName = '<?= esc($permission->name) ?>';

        // Create temporary input
        const tempInput = document.createElement('input');
        tempInput.value = permissionName;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        // Show toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'success',
            title: 'Permission name copied!'
        });
    }

    // Add copy button functionality to permission name
    document.addEventListener('DOMContentLoaded', function() {
        const permissionCode = document.querySelector('.card-body code.fs-5');
        if (permissionCode) {
            permissionCode.style.cursor = 'pointer';
            permissionCode.title = 'Click to copy';
            permissionCode.addEventListener('click', copyPermissionName);
        }
    });
</script>
<?php $this->endSection(); ?>