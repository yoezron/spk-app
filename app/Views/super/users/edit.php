<?php

/**
 * View: Edit User
 * Path: app/Views/super/users/edit.php
 *
 * Comprehensive form untuk edit user account dan member profile
 *
 * @var object $user User data with profile
 * @var string $userGroup Current user group/role
 * @var array $roles Available roles
 * @var array $provinces Available provinces
 * @var array $universities Available universities
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

<form action="<?= base_url('super/users/' . $user->id . '/update') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Account Information Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <!-- User ID (Read-only) -->
                    <div class="mb-3">
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" value="<?= esc($user->id) ?>" readonly>
                        <small class="text-muted">User ID cannot be changed</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
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
                                <small class="text-muted">Username must be 3-30 characters, alphanumeric with dash/underscore</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                    class="form-control <?= $validation->hasError('email') ? 'is-invalid' : '' ?>"
                                    name="email"
                                    value="<?= old('email', $user->email) ?>"
                                    required>
                                <?php if ($validation->hasError('email')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('email') ?>
                                    </div>
                                <?php endif; ?>
                                <small class="text-muted">Valid email address</small>
                            </div>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select <?= $validation->hasError('role') ? 'is-invalid' : '' ?>"
                            name="role"
                            id="roleSelect"
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
                        <small class="text-muted">Changing role will affect user's permissions immediately</small>
                    </div>

                    <!-- Current Status -->
                    <div class="mb-0">
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
                        <small class="text-muted">Use "Toggle Status" button in user detail page to change status</small>
                    </div>
                </div>
            </div>

            <!-- Member Profile Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>Member Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text"
                                    class="form-control <?= $validation->hasError('full_name') ? 'is-invalid' : '' ?>"
                                    name="full_name"
                                    value="<?= old('full_name', $user->full_name ?? '') ?>">
                                <?php if ($validation->hasError('full_name')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('full_name') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- NIK -->
                            <div class="mb-3">
                                <label class="form-label">NIK (KTP)</label>
                                <input type="text"
                                    class="form-control <?= $validation->hasError('nik') ? 'is-invalid' : '' ?>"
                                    name="nik"
                                    value="<?= old('nik', $user->nik ?? '') ?>"
                                    maxlength="16"
                                    placeholder="16 digits">
                                <?php if ($validation->hasError('nik')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('nik') ?>
                                    </div>
                                <?php endif; ?>
                                <small class="text-muted">16 digit NIK</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Gender -->
                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select <?= $validation->hasError('gender') ? 'is-invalid' : '' ?>"
                                    name="gender">
                                    <option value="">-- Select Gender --</option>
                                    <option value="L" <?= old('gender', $user->gender ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= old('gender', $user->gender ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                                <?php if ($validation->hasError('gender')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('gender') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Birth Place -->
                            <div class="mb-3">
                                <label class="form-label">Birth Place</label>
                                <input type="text"
                                    class="form-control <?= $validation->hasError('birth_place') ? 'is-invalid' : '' ?>"
                                    name="birth_place"
                                    value="<?= old('birth_place', $user->birth_place ?? '') ?>">
                                <?php if ($validation->hasError('birth_place')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('birth_place') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Birth Date -->
                            <div class="mb-3">
                                <label class="form-label">Birth Date</label>
                                <input type="date"
                                    class="form-control <?= $validation->hasError('birth_date') ? 'is-invalid' : '' ?>"
                                    name="birth_date"
                                    value="<?= old('birth_date', $user->birth_date ?? '') ?>">
                                <?php if ($validation->hasError('birth_date')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('birth_date') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text"
                                    class="form-control <?= $validation->hasError('phone') ? 'is-invalid' : '' ?>"
                                    name="phone"
                                    value="<?= old('phone', $user->phone ?? '') ?>"
                                    placeholder="08xxxxxxxxxxxx">
                                <?php if ($validation->hasError('phone')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('phone') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- WhatsApp -->
                            <div class="mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text"
                                    class="form-control <?= $validation->hasError('whatsapp') ? 'is-invalid' : '' ?>"
                                    name="whatsapp"
                                    value="<?= old('whatsapp', $user->whatsapp ?? '') ?>"
                                    placeholder="08xxxxxxxxxxxx">
                                <?php if ($validation->hasError('whatsapp')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('whatsapp') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Province -->
                            <div class="mb-3">
                                <label class="form-label">Province</label>
                                <select class="form-select <?= $validation->hasError('province_id') ? 'is-invalid' : '' ?>"
                                    name="province_id">
                                    <option value="">-- Select Province --</option>
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?= $province->id ?>"
                                            <?= old('province_id', $user->province_id ?? '') == $province->id ? 'selected' : '' ?>>
                                            <?= esc($province->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($validation->hasError('province_id')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('province_id') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- University -->
                            <div class="mb-3">
                                <label class="form-label">University</label>
                                <select class="form-select <?= $validation->hasError('university_id') ? 'is-invalid' : '' ?>"
                                    name="university_id">
                                    <option value="">-- Select University --</option>
                                    <?php foreach ($universities as $university): ?>
                                        <option value="<?= $university->id ?>"
                                            <?= old('university_id', $user->university_id ?? '') == $university->id ? 'selected' : '' ?>>
                                            <?= esc($university->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($validation->hasError('university_id')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('university_id') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>Important Notes:
                </h6>
                <ul class="mb-0">
                    <li>Changing the username may affect user login</li>
                    <li>Changing the email will update user's login email</li>
                    <li>Changing the role will immediately affect user's access permissions</li>
                    <li>User will need to log out and log in again to see the role changes</li>
                    <li>Cannot change your own role for security reasons</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-3">
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
        </div>

        <div class="col-lg-4">
            <!-- Additional Actions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Change User Status</h6>
                        <p class="text-muted small">Activate or deactivate user account</p>
                        <a href="<?= base_url('super/users/' . $user->id) ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-toggle-on me-2"></i>Manage Status
                        </a>
                    </div>
                    <div>
                        <h6>Force Password Reset</h6>
                        <p class="text-muted small">Send password reset link to user</p>
                        <button type="button"
                            class="btn btn-sm btn-outline-info w-100"
                            onclick="forceResetPassword(<?= $user->id ?>)">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Role Permissions Preview -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Role Permissions Preview
                    </h6>
                </div>
                <div class="card-body">
                    <div id="permissionsPreview">
                        <p class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>Select a role to preview permissions
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

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

    // Role selection handler for permissions preview (AJAX)
    document.getElementById('roleSelect').addEventListener('change', function() {
        const selectedRole = this.value;
        const previewDiv = document.getElementById('permissionsPreview');

        if (!selectedRole) {
            previewDiv.innerHTML = '<p class="text-center text-muted"><i class="fas fa-info-circle me-2"></i>Select a role to preview permissions</p>';
            return;
        }

        // Show loading
        previewDiv.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Loading permissions...</p>';

        // Fetch permissions via AJAX
        fetch('<?= base_url('super/users/permissions-by-role') ?>/' + selectedRole)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = `
                        <div class="alert alert-info mb-3">
                            <strong>${data.count}</strong> permissions for
                            <strong>${selectedRole.replace('_', ' ')}</strong>
                        </div>
                    `;

                    if (data.permissions.length > 0) {
                        html += '<div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">';
                        data.permissions.forEach(perm => {
                            html += `
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                        <div>
                                            <strong>${perm.name}</strong>
                                            ${perm.description ? '<br><small class="text-muted">' + perm.description + '</small>' : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                    } else {
                        html += '<p class="text-muted text-center">No permissions assigned to this role.</p>';
                    }

                    previewDiv.innerHTML = html;
                } else {
                    previewDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                previewDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading permissions: ${error.message}
                    </div>
                `;
            });
    });

    // Trigger permissions preview on page load if role is selected
    window.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('roleSelect');
        if (roleSelect.value) {
            roleSelect.dispatchEvent(new Event('change'));
        }
    });

    // Confirm before submit if role changed
    document.querySelector('form').addEventListener('submit', function(e) {
        const currentRole = '<?= esc($userGroup) ?>';
        const newRole = document.getElementById('roleSelect').value;
        const currentUsername = '<?= esc($user->username) ?>';
        const newUsername = document.querySelector('input[name="username"]').value;

        if (currentRole !== newRole) {
            if (!confirm(`Are you sure you want to change this user's role from "${currentRole}" to "${newRole}"?\n\nThis will change their access permissions immediately.`)) {
                e.preventDefault();
                return false;
            }
        }

        if (currentUsername !== newUsername) {
            if (!confirm(`You are changing the username from "${currentUsername}" to "${newUsername}".\n\nThis may affect user login. Continue?`)) {
                e.preventDefault();
                return false;
            }
        }
    });
</script>
<?= $this->endSection() ?>
