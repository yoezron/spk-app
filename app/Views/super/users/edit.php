<?php

/**
 * View: Edit User
 * Path: app/Views/super/users/edit.php
 * 
 * Form untuk edit username dan role user
 * 
 * @var object $user User data
 * @var string $userGroup Current user group/role
 * @var array $roles Available roles
 * @var object $validation Validation object
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-user-edit me-2"></i>Edit User</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/users') ?>">User Management</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Validation Errors -->
<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors:</h6>
        <ul class="mb-0">
            <?php foreach (session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Edit Form Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>Edit User Information
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('super/users/' . $user->id . '/update') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- User ID (Read-only) -->
                    <div class="mb-3">
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" value="<?= esc($user->id) ?>" readonly>
                        <small class="text-muted">User ID cannot be changed</small>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control <?= $validation->hasError('username') ? 'is-invalid' : '' ?>"
                            name="username"
                            value="<?= old('username', $user->username) ?>"
                            required>
                        <?php if ($validation->hasError('username')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('username') ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Username must be unique and at least 3 characters</small>
                    </div>

                    <!-- Email (Read-only) -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= esc($user->email ?? '-') ?>" readonly>
                        <small class="text-muted">Email cannot be changed from this page</small>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select <?= $validation->hasError('role') ? 'is-invalid' : '' ?>"
                            name="role"
                            required>
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role->title) ?>"
                                    <?= (old('role', $userGroup) === $role->title) ? 'selected' : '' ?>>
                                    <?= esc(ucwords(str_replace('_', ' ', $role->title))) ?>
                                    <?php if ($role->description): ?>
                                        - <?= esc($role->description) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($validation->hasError('role')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('role') ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Changing role will affect user's permissions</small>
                    </div>

                    <!-- Current Status -->
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <div>
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
                        <small class="text-muted">Use "Toggle Status" button in user detail to change status</small>
                    </div>

                    <!-- Warning Box -->
                    <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>Important Notes:
                        </h6>
                        <ul class="mb-0">
                            <li>Changing the username may affect user login</li>
                            <li>Changing the role will immediately affect user's access permissions</li>
                            <li>User will need to log out and log in again to see the role changes</li>
                            <li>Cannot change your own role for security reasons</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update User
                        </button>
                        <a href="<?= base_url('super/users/' . $user->id) ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <a href="<?= base_url('super/users') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Additional Information Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Additional Options
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6>Change User Status</h6>
                        <p class="text-muted small">Activate or deactivate user account</p>
                        <a href="<?= base_url('super/users/' . $user->id) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-toggle-on me-2"></i>Manage Status
                        </a>
                    </div>
                    <div class="col-md-6">
                        <h6>Force Password Reset</h6>
                        <p class="text-muted small">Send password reset link to user</p>
                        <button type="button"
                            class="btn btn-sm btn-outline-info"
                            onclick="forceResetPassword(<?= $user->id ?>)">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Permissions Preview -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i>Role Permissions Preview
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Select a role above to see what permissions will be granted to this user.
                </p>
                <div id="permissionsPreview">
                    <p class="text-center text-muted">
                        <i class="fas fa-info-circle me-2"></i>Select a role to preview permissions
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
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

    // Role selection handler for permissions preview
    document.querySelector('select[name="role"]').addEventListener('change', function() {
        const selectedRole = this.value;
        const previewDiv = document.getElementById('permissionsPreview');

        if (!selectedRole) {
            previewDiv.innerHTML = '<p class="text-center text-muted"><i class="fas fa-info-circle me-2"></i>Select a role to preview permissions</p>';
            return;
        }

        // Show loading
        previewDiv.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Loading permissions...</p>';

        // Fetch permissions for selected role (you can implement AJAX here)
        // For now, just show a message
        setTimeout(() => {
            previewDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Permissions for <strong>${selectedRole}</strong> will be applied to this user.
                <br><small>View full permissions list in Roles Management page.</small>
            </div>
        `;
        }, 500);
    });

    // Confirm before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const currentRole = '<?= esc($userGroup) ?>';
        const newRole = document.querySelector('select[name="role"]').value;

        if (currentRole !== newRole) {
            if (!confirm(`Are you sure you want to change this user's role from "${currentRole}" to "${newRole}"?\n\nThis will change their access permissions immediately.`)) {
                e.preventDefault();
            }
        }
    });
</script>
<?= $this->endSection() ?>