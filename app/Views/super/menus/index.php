<?php

/**
 * View: Menus Index
 * Menampilkan daftar menu dalam tree structure
 * 
 * @var array $menuTree Menu tree structure
 * @var int $totalMenus Total menu count
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-bars me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Menu Management</li>
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
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Total Menu</span>
                        <span class="widget-stats-amount"><?= number_format($totalMenus) ?></span>
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
                        <span class="widget-stats-title">Parent Menu</span>
                        <span class="widget-stats-amount"><?= count($menuTree) ?></span>
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
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Menu System</span>
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
                        <i class="fas fa-list me-2"></i>Daftar Menu
                    </h5>
                    <a href="<?= base_url('super/menus/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Menu
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Menu Tree -->
                <?php if (empty($menuTree)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada menu. Silakan tambahkan menu baru.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Menu</th>
                                    <th width="20%">URL/Route</th>
                                    <th width="15%">Permission</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Urutan</th>
                                    <th width="10%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($menuTree as $menu):
                                ?>
                                    <!-- Parent Menu -->
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <?php if (!empty($menu->icon)): ?>
                                                <i class="<?= esc($menu->icon) ?> me-2"></i>
                                            <?php endif; ?>
                                            <strong><?= esc($menu->title) ?></strong>
                                            <?php if ($menu->is_external): ?>
                                                <span class="badge bg-info ms-2">External</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($menu->url)): ?>
                                                <code><?= esc($menu->url) ?></code>
                                            <?php elseif (!empty($menu->route_name)): ?>
                                                <code class="text-primary"><?= esc($menu->route_name) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($menu->permission_key)): ?>
                                                <span class="badge bg-secondary"><?= esc($menu->permission_key) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Public</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($menu->is_active): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark"><?= $menu->sort_order ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('super/menus/' . $menu->id . '/edit') ?>"
                                                    class="btn btn-warning"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-danger"
                                                    onclick="deleteMenu(<?= $menu->id ?>, '<?= esc($menu->title, 'js') ?>')"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Child Menus -->
                                    <?php if (!empty($menu->children)): ?>
                                        <?php foreach ($menu->children as $child): ?>
                                            <tr class="table-light">
                                                <td></td>
                                                <td class="ps-5">
                                                    <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>
                                                    <?php if (!empty($child->icon)): ?>
                                                        <i class="<?= esc($child->icon) ?> me-2"></i>
                                                    <?php endif; ?>
                                                    <?= esc($child->title) ?>
                                                    <?php if ($child->is_external): ?>
                                                        <span class="badge bg-info ms-2">External</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($child->url)): ?>
                                                        <code><?= esc($child->url) ?></code>
                                                    <?php elseif (!empty($child->route_name)): ?>
                                                        <code class="text-primary"><?= esc($child->route_name) ?></code>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($child->permission_key)): ?>
                                                        <span class="badge bg-secondary"><?= esc($child->permission_key) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Public</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($child->is_active): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-dark"><?= $child->sort_order ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= base_url('super/menus/' . $child->id . '/edit') ?>"
                                                            class="btn btn-warning"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-danger"
                                                            onclick="deleteMenu(<?= $child->id ?>, '<?= esc($child->title, 'js') ?>')"
                                                            title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi Menu Management</h6>
                <ul class="mb-0">
                    <li><strong>Hierarchical Structure:</strong> Menu mendukung parent-child relationship (unlimited level)</li>
                    <li><strong>Permission-Based:</strong> Menu dapat dibatasi aksesnya berdasarkan permission yang dimiliki user</li>
                    <li><strong>Sort Order:</strong> Urutan menu dapat diatur menggunakan field sort_order (semakin kecil, semakin atas)</li>
                    <li><strong>External Link:</strong> Menu dapat mengarah ke internal route atau external URL</li>
                    <li><strong>Active Status:</strong> Menu yang inactive tidak akan ditampilkan di sidebar</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    // Delete menu
    function deleteMenu(id, title) {
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus menu <strong>${title}</strong>?<br><br>
               <span class="text-danger">Menu dan semua sub-menu di dalamnya akan dihapus!</span>`,
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
                form.action = '<?= base_url('super/menus') ?>/' + id + '/delete';

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
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
<?php $this->endSection(); ?>