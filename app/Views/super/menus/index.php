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
                <a href="#">Menu Management</a>
            </li>
        </ul>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Berhasil!</strong> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Daftar Menu</h4>
                        <a href="<?= base_url('super/menus/create') ?>" class="btn btn-primary btn-round ms-auto">
                            <i class="fa fa-plus"></i>
                            Tambah Menu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <strong>Total Menu:</strong> <?= $totalMenus ?>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Tree -->
                    <?php if (empty($menuTree)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
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
                                        <th width="10%" class="text-center">Aksi</th>
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
                                                    <i class="<?= esc($menu->icon) ?>"></i>
                                                <?php endif; ?>
                                                <strong><?= esc($menu->title) ?></strong>
                                                <?php if ($menu->is_external): ?>
                                                    <span class="badge badge-info ms-2">External</span>
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
                                                    <span class="badge badge-secondary"><?= esc($menu->permission_key) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Public</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($menu->is_active): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-dark"><?= $menu->sort_order ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('super/menus/' . $menu->id . '/edit') ?>"
                                                        class="btn btn-sm btn-warning"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete(<?= $menu->id ?>, '<?= esc($menu->title) ?>')"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Child Menus -->
                                        <?php if (!empty($menu->children)): ?>
                                            <?php foreach ($menu->children as $child): ?>
                                                <tr class="bg-light">
                                                    <td></td>
                                                    <td class="ps-5">
                                                        <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>
                                                        <?php if (!empty($child->icon)): ?>
                                                            <i class="<?= esc($child->icon) ?>"></i>
                                                        <?php endif; ?>
                                                        <?= esc($child->title) ?>
                                                        <?php if ($child->is_external): ?>
                                                            <span class="badge badge-info ms-2">External</span>
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
                                                            <span class="badge badge-secondary"><?= esc($child->permission_key) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Public</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($child->is_active): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-dark"><?= $child->sort_order ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <a href="<?= base_url('super/menus/' . $child->id . '/edit') ?>"
                                                                class="btn btn-sm btn-warning"
                                                                title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="confirmDelete(<?= $child->id ?>, '<?= esc($child->title) ?>')"
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
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus menu <strong id="menuTitle"></strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Menu dan semua sub-menu di dalamnya akan dihapus!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmDelete(menuId, menuTitle) {
        // Set menu title in modal
        document.getElementById('menuTitle').textContent = menuTitle;

        // Set form action
        document.getElementById('deleteForm').action = '<?= base_url('super/menus') ?>/' + menuId + '/delete';

        // Show modal
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

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