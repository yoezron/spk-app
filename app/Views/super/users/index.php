<?php

/**
 * View: User Management Index
 * Path: app/Views/super/users/index.php
 * 
 * Menampilkan daftar semua users dengan filter dan statistics
 * 
 * @var array $users List of users
 * @var array $roles Available roles
 * @var array $stats Statistics data
 * @var array $filters Current filter values
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-users me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Management</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Total Users</span>
                        <span class="widget-stats-amount"><?= number_format($stats['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Active Users</span>
                        <span class="widget-stats-amount"><?= number_format($stats['active']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-warning">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Inactive Users</span>
                        <span class="widget-stats-amount"><?= number_format($stats['inactive']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card widget widget-stats">
            <div class="card-body">
                <div class="widget-stats-container d-flex">
                    <div class="widget-stats-icon widget-stats-icon-info">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="widget-stats-content flex-fill">
                        <span class="widget-stats-title">Super Admin</span>
                        <span class="widget-stats-amount"><?= number_format($stats['superadmin']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filter Section -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= base_url('super/users') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role->title) ?>"
                            <?= ($filters['role'] === $role->title) ? 'selected' : '' ?>>
                            <?= esc(ucfirst($role->title)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?= ($filters['status'] === '1') ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($filters['status'] === '0') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                    placeholder="Username, Name, or ID..."
                    value="<?= esc($filters['search']) ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>Users List
            <span class="badge bg-primary ms-2"><?= count($users) ?></span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No users found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <code><?= esc($user->id) ?></code>
                                </td>
                                <td>
                                    <strong><?= esc($user->username) ?></strong>
                                </td>
                                <td>
                                    <?= esc($user->full_name ?? '-') ?>
                                </td>
                                <td>
                                    <?php
                                    $roleColors = [
                                        'superadmin' => 'danger',
                                        'pengurus' => 'primary',
                                        'koordinator_wilayah' => 'info',
                                        'anggota' => 'success',
                                        'calon_anggota' => 'warning'
                                    ];
                                    $roleColor = $roleColors[$user->group] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $roleColor ?>">
                                        <?= esc(ucwords(str_replace('_', ' ', $user->group ?? 'No Role'))) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user->active): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times-circle"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($user->phone ?? '-') ?>
                                </td>
                                <td>
                                    <small><?= date('d M Y', strtotime($user->created_at)) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= base_url('super/users/' . $user->id) ?>"
                                            class="btn btn-sm btn-info"
                                            title="View Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('super/users/' . $user->id . '/edit') ?>"
                                            class="btn btn-sm btn-warning"
                                            title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <?php if ($user->id != auth()->id()): ?>
                                            <button type="button"
                                                class="btn btn-sm <?= $user->active ? 'btn-secondary' : 'btn-success' ?>"
                                                onclick="toggleStatus(<?= $user->id ?>, <?= $user->active ? 0 : 1 ?>)"
                                                title="<?= $user->active ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $user->active ? 'ban' : 'check' ?>"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteUser(<?= $user->id ?>, '<?= esc($user->username) ?>')"
                                                title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUsername"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Toggle user status
    function toggleStatus(userId, newStatus) {
        const statusText = newStatus ? 'activate' : 'deactivate';

        if (confirm(`Are you sure you want to ${statusText} this user?`)) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('super/users') ?>/' + userId + '/toggle-status';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= csrf_token() ?>';
            csrfInput.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete user
    function deleteUser(userId, username) {
        document.getElementById('deleteUsername').textContent = username;
        document.getElementById('deleteForm').action = '<?= base_url('super/users') ?>/' + userId;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Initialize DataTable if available
    <?php if (!empty($users)): ?>
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('#usersTable').DataTable({
                    "pageLength": 25,
                    "order": [
                        [0, "desc"]
                    ],
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ users",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    }
                });
            }
        });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>