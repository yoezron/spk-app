<?php

/**
 * View: User Detail
 * Path: app/Views/super/users/show.php
 * 
 * Menampilkan detail lengkap user dengan permissions dan activity logs
 * 
 * @var object $user User data
 * @var array $permissions User permissions
 * @var array $activities Recent activities
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-user me-2"></i>User Detail</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/users') ?>">User Management</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
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

<!-- User Profile Card -->
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if (!empty($user->photo_url)): ?>
                        <img src="<?= base_url('uploads/photos/' . $user->photo_url) ?>"
                            alt="Photo"
                            class="rounded-circle"
                            style="width: 120px; height: 120px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                            style="width: 120px; height: 120px; font-size: 48px;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <h4 class="mb-1"><?= esc($user->full_name ?? $user->username) ?></h4>
                <p class="text-muted mb-2">@<?= esc($user->username) ?></p>

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
                <span class="badge bg-<?= $roleColor ?> mb-3">
                    <?= esc(ucwords(str_replace('_', ' ', $user->group ?? 'No Role'))) ?>
                </span>

                <div class="mb-3">
                    <?php if ($user->active): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">
                            <i class="fas fa-times-circle"></i> Inactive
                        </span>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <a href="<?= base_url('super/users/' . $user->id . '/edit') ?>"
                        class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit User
                    </a>

                    <?php if ($user->id != auth()->id()): ?>
                        <button type="button"
                            class="btn btn-<?= $user->active ? 'secondary' : 'success' ?>"
                            onclick="toggleStatus(<?= $user->id ?>, <?= $user->active ? 0 : 1 ?>)">
                            <i class="fas fa-<?= $user->active ? 'ban' : 'check' ?> me-2"></i>
                            <?= $user->active ? 'Deactivate' : 'Activate' ?> Account
                        </button>

                        <button type="button"
                            class="btn btn-info"
                            onclick="forceResetPassword(<?= $user->id ?>)">
                            <i class="fas fa-key me-2"></i>Force Reset Password
                        </button>

                        <button type="button"
                            class="btn btn-danger"
                            onclick="deleteUser(<?= $user->id ?>, '<?= esc($user->username) ?>')">
                            <i class="fas fa-trash me-2"></i>Delete User
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Account Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Account Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">User ID:</th>
                                <td><code><?= esc($user->id) ?></code></td>
                            </tr>
                            <tr>
                                <th>Username:</th>
                                <td><strong><?= esc($user->username) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= esc($user->email ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= esc($user->phone ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Registered:</th>
                                <td><?= date('d M Y H:i', strtotime($user->created_at)) ?></td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td><?= date('d M Y H:i', strtotime($user->updated_at)) ?></td>
                            </tr>
                            <tr>
                                <th>Member Number:</th>
                                <td><?= esc($user->member_number ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?= $user->membership_status === 'anggota' ? 'success' : 'warning' ?>">
                                        <?= esc(ucfirst($user->membership_status ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <?php if ($user->full_name): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Full Name:</th>
                                    <td><?= esc($user->full_name) ?></td>
                                </tr>
                                <tr>
                                    <th>NIK:</th>
                                    <td><?= esc($user->nik ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td><?= esc($user->gender ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Birth Place:</th>
                                    <td><?= esc($user->birth_place ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Birth Date:</th>
                                    <td><?= $user->birth_date ? date('d M Y', strtotime($user->birth_date)) : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>WhatsApp:</th>
                                    <td><?= esc($user->whatsapp ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>University:</th>
                                    <td><?= esc($user->university_name ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Province:</th>
                                    <td><?= esc($user->province_name ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Permissions -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i>Permissions
                    <span class="badge bg-primary ms-2"><?= count($permissions) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($permissions)): ?>
                    <p class="text-muted">No permissions assigned to this role.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($permissions as $permission): ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <div>
                                        <strong><?= esc($permission->name) ?></strong>
                                        <?php if ($permission->description): ?>
                                            <br><small class="text-muted"><?= esc($permission->description) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Activities
                    <span class="badge bg-info ms-2"><?= count($activities) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <p class="text-muted">No recent activities.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td>
                                            <small><?= date('d M Y H:i', strtotime($activity->created_at)) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($activity->action) ?></span>
                                        </td>
                                        <td>
                                            <small><?= esc($activity->action_description) ?></small>
                                        </td>
                                        <td>
                                            <code><?= esc($activity->ip_address) ?></code>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
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
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('super/users') ?>/' + userId + '/toggle-status';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= csrf_token() ?>';
            csrfInput.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // Force reset password
    function forceResetPassword(userId) {
        if (confirm('Send password reset link to this user?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('super/users') ?>/' + userId + '/force-reset-password';

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
</script>
<?= $this->endSection() ?>