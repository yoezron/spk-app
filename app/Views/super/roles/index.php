<?= $this->extend('layouts/super') ?>

<?= $this->section('styles') ?>
<style>
    .role-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border-left: 4px solid #667eea;
    }

    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .role-card.protected {
        border-left-color: #e74c3c;
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.05) 0%, rgba(255, 255, 255, 1) 100%);
    }

    .role-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .role-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .role-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
    }

    .role-stats {
        display: flex;
        gap: 2rem;
        margin: 1rem 0;
        padding: 1rem 0;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
    }

    .role-stat {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .role-stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .role-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #667eea;
    }

    .role-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #e9ecef;
        margin-bottom: 1rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="material-icons-outlined align-middle">shield</i>
                        Manajemen Role
                    </h1>
                    <p class="text-muted mb-0">Kelola roles dan permissions sistem</p>
                </div>
                <div>
                    <a href="<?= base_url('super/roles/create') ?>" class="btn btn-primary">
                        <i class="material-icons-outlined align-middle">add</i>
                        Tambah Role
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="material-icons-outlined align-middle me-2">check_circle</i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="material-icons-outlined align-middle me-2">error</i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Roles Grid -->
    <?php if (!empty($roles)): ?>
        <div class="row g-4">
            <?php foreach ($roles as $role): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="role-card <?= $role->title === 'superadmin' ? 'protected' : '' ?>">
                        <div class="role-header">
                            <div class="flex-grow-1">
                                <h5 class="role-title">
                                    <?= esc($role->title) ?>
                                    <?php if ($role->title === 'superadmin'): ?>
                                        <span class="role-badge bg-danger text-white ms-2">
                                            <i class="material-icons-outlined" style="font-size: 14px;">lock</i>
                                            Protected
                                        </span>
                                    <?php endif; ?>
                                </h5>
                                <p class="text-muted small mb-0">
                                    <?= esc($role->description) ?>
                                </p>
                            </div>
                        </div>

                        <div class="role-stats">
                            <div class="role-stat">
                                <span class="role-stat-label">Permissions</span>
                                <span class="role-stat-value"><?= number_format($role->permission_count) ?></span>
                            </div>
                            <div class="role-stat">
                                <span class="role-stat-label">Members</span>
                                <span class="role-stat-value"><?= number_format($role->member_count) ?></span>
                            </div>
                        </div>

                        <div class="role-actions">
                            <a href="<?= base_url('super/roles/' . $role->id . '/members') ?>"
                                class="btn btn-sm btn-outline-primary flex-grow-1"
                                title="View Members">
                                <i class="material-icons-outlined align-middle">people</i>
                                Members
                            </a>

                            <?php if ($role->title !== 'superadmin'): ?>
                                <a href="<?= base_url('super/roles/' . $role->id . '/edit') ?>"
                                    class="btn btn-sm btn-outline-secondary btn-icon"
                                    title="Edit Role">
                                    <i class="material-icons-outlined">edit</i>
                                </a>

                                <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-icon"
                                    onclick="confirmDelete(<?= $role->id ?>, '<?= esc($role->title) ?>')"
                                    title="Delete Role">
                                    <i class="material-icons-outlined">delete</i>
                                </button>
                            <?php else: ?>
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary btn-icon disabled"
                                    title="Cannot edit protected role">
                                    <i class="material-icons-outlined">lock</i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="empty-state">
                    <i class="material-icons-outlined">shield</i>
                    <h4>Belum Ada Role</h4>
                    <p class="text-muted">Mulai dengan membuat role pertama untuk sistem Anda</p>
                    <a href="<?= base_url('super/roles/create') ?>" class="btn btn-primary mt-3">
                        <i class="material-icons-outlined align-middle">add</i>
                        Buat Role Pertama
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmDelete(roleId, roleName) {
        Swal.fire({
            title: 'Hapus Role?',
            html: `Apakah Anda yakin ingin menghapus role <strong>${roleName}</strong>?<br><small class="text-muted">Role yang masih memiliki member tidak dapat dihapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = `<?= base_url('super/roles') ?>/${roleId}/delete`;
                form.submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>