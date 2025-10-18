<?php

/**
 * View: Permissions Edit
 * Form untuk mengedit permission yang sudah ada
 * 
 * @var object $permission Permission object
 * @var array $existingModules List of existing modules
 * @var \CodeIgniter\Validation\Validation $validation
 */

$this->extend('layouts/super');
$this->section('content');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><i class="fas fa-edit me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/permissions') ?>">Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Terdapat kesalahan pada form:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8 col-xl-7">
        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-keyboard me-2"></i>Edit Permission
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('super/permissions/' . $permission->id . '/update') ?>" method="POST" id="permissionForm">
                    <?= csrf_field() ?>

                    <!-- Permission ID (Hidden) -->
                    <input type="hidden" name="id" value="<?= esc($permission->id) ?>">

                    <!-- Current Permission Info Alert -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fs-4 me-3"></i>
                            <div>
                                <strong>Current Permission ID:</strong> <?= esc($permission->id) ?><br>
                                <strong>Created:</strong> <?= date('d M Y H:i', strtotime($permission->created_at ?? 'now')) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Permission Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Permission Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= $validation->hasError('name') ? 'is-invalid' : '' ?>"
                            id="name"
                            name="name"
                            value="<?= old('name', $permission->name) ?>"
                            placeholder="e.g., member.view, forum.manage"
                            required>
                        <div class="form-text">
                            Format: <code>module.action</code> (gunakan lowercase dan underscore)
                        </div>
                        <?php if ($validation->hasError('name')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('name') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Name Builder -->
                    <div class="mb-4">
                        <label class="form-label">Quick Builder</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select class="form-select" id="moduleSelect">
                                    <option value="">-- Pilih Module --</option>
                                    <?php if (!empty($existingModules)): ?>
                                        <optgroup label="Existing Modules">
                                            <?php
                                            $currentModule = explode('.', $permission->name)[0] ?? '';
                                            foreach ($existingModules as $module):
                                            ?>
                                                <option value="<?= esc($module) ?>" <?= $module === $currentModule ? 'selected' : '' ?>>
                                                    <?= esc(ucfirst($module)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <optgroup label="Common Modules">
                                        <option value="member">Member</option>
                                        <option value="forum">Forum</option>
                                        <option value="survey">Survey</option>
                                        <option value="complaint">Complaint</option>
                                        <option value="content">Content</option>
                                        <option value="event">Event</option>
                                        <option value="report">Report</option>
                                        <option value="system">System</option>
                                    </optgroup>
                                </select>
                                <div class="form-text">Module name</div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="actionSelect">
                                    <option value="">-- Pilih Action --</option>
                                    <?php
                                    $currentAction = explode('.', $permission->name)[1] ?? '';
                                    $actions = [
                                        'view' => 'view (Lihat data)',
                                        'create' => 'create (Tambah data)',
                                        'edit' => 'edit (Edit data)',
                                        'delete' => 'delete (Hapus data)',
                                        'approve' => 'approve (Setujui data)',
                                        'export' => 'export (Export data)',
                                        'import' => 'import (Import data)',
                                        'manage' => 'manage (Kelola penuh)'
                                    ];
                                    foreach ($actions as $value => $label):
                                    ?>
                                        <option value="<?= $value ?>" <?= $value === $currentAction ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Action type</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="buildPermissionName()">
                            <i class="fas fa-magic me-1"></i>Generate Permission Name
                        </button>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control <?= $validation->hasError('description') ? 'is-invalid' : '' ?>"
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Deskripsikan apa yang dapat dilakukan dengan permission ini..."
                            required><?= old('description', $permission->description) ?></textarea>
                        <div class="form-text">
                            <span id="charCounter"><?= strlen($permission->description) ?>/255</span> karakter
                        </div>
                        <?php if ($validation->hasError('description')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('description') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('super/permissions') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4 col-xl-5">
        <!-- Warning Card -->
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Perhatian!</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Mengubah permission name dapat mempengaruhi:</strong>
                </p>
                <ul class="mb-0">
                    <li>Role assignments yang menggunakan permission ini</li>
                    <li>Menu yang terkait dengan permission ini</li>
                    <li>Authorization checks di controller</li>
                </ul>
                <hr>
                <p class="mb-0 text-danger">
                    <i class="fas fa-info-circle me-1"></i>
                    Pastikan untuk update code yang menggunakan permission ini jika mengubah name!
                </p>
            </div>
        </div>

        <!-- Guidelines Card -->
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Panduan Permission</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Format Permission:</h6>
                <p class="mb-3">
                    <code>module.action</code>
                </p>

                <h6 class="fw-bold">Naming Convention:</h6>
                <ul class="mb-3">
                    <li>Gunakan <strong>lowercase</strong></li>
                    <li>Pisahkan dengan <strong>titik (.)</strong></li>
                    <li>Module: nama fitur/modul</li>
                    <li>Action: operasi yang diizinkan</li>
                </ul>

                <h6 class="fw-bold">Common Actions:</h6>
                <ul class="mb-0">
                    <li><code>view</code> - Melihat data</li>
                    <li><code>create</code> - Menambah data</li>
                    <li><code>edit</code> - Mengubah data</li>
                    <li><code>delete</code> - Menghapus data</li>
                    <li><code>approve</code> - Menyetujui data</li>
                    <li><code>export</code> - Export data</li>
                    <li><code>manage</code> - Kelola penuh</li>
                </ul>
            </div>
        </div>

        <!-- Usage Stats Card -->
        <div class="card border-success mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Usage Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Digunakan oleh Role:</span>
                    <a href="<?= base_url('super/permissions/' . $permission->id . '/roles') ?>" class="btn btn-sm btn-info">
                        Lihat Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
    // Build permission name from quick builder
    function buildPermissionName() {
        const module = document.getElementById('moduleSelect').value;
        const action = document.getElementById('actionSelect').value;

        if (!module || !action) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Module dan Action',
                text: 'Silakan pilih module dan action terlebih dahulu.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const permissionName = `${module}.${action}`;
        document.getElementById('name').value = permissionName;

        // Show success toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'success',
            title: 'Permission name updated!'
        });
    }

    // Real-time validation
    document.getElementById('name').addEventListener('input', function() {
        const value = this.value;
        const pattern = /^[a-z]+\.[a-z_]+$/;

        if (value && !pattern.test(value)) {
            this.classList.add('is-invalid');
            if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Format harus: module.action (lowercase, tanpa spasi)';
                this.parentNode.appendChild(feedback);
            }
        } else {
            this.classList.remove('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback:last-child');
            if (feedback && feedback.textContent.includes('Format harus')) {
                feedback.remove();
            }
        }
    });

    // Character counter for description
    document.getElementById('description').addEventListener('input', function() {
        const length = this.value.length;
        const counter = document.getElementById('charCounter');

        counter.textContent = `${length}/255`;

        if (length < 10) {
            counter.parentElement.classList.add('text-danger');
            counter.parentElement.classList.remove('text-success');
        } else if (length > 255) {
            counter.parentElement.classList.add('text-danger');
            counter.parentElement.classList.remove('text-success');
        } else {
            counter.parentElement.classList.remove('text-danger');
            counter.parentElement.classList.add('text-success');
        }
    });

    // Form validation before submit
    document.getElementById('permissionForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value;
        const description = document.getElementById('description').value;

        // Validate permission name format
        const pattern = /^[a-z]+\.[a-z_]+$/;
        if (!pattern.test(name)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Format Permission Salah',
                html: 'Format permission harus: <code>module.action</code><br>Contoh: <code>member.view</code>',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Validate description length
        if (description.length < 10) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Deskripsi Terlalu Pendek',
                text: 'Deskripsi minimal 10 karakter.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (description.length > 255) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Deskripsi Terlalu Panjang',
                text: 'Deskripsi maksimal 255 karakter.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show confirmation for name change
        const originalName = '<?= esc($permission->name) ?>';
        if (name !== originalName) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi Perubahan',
                html: `Anda mengubah permission name dari:<br>
                   <code class="text-danger">${originalName}</code><br>
                   menjadi:<br>
                   <code class="text-success">${name}</code><br><br>
                   <strong>Pastikan untuk update code yang menggunakan permission ini!</strong>`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Update!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        }
    });

    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-info):not(.alert-warning)');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
<?php $this->endSection(); ?>