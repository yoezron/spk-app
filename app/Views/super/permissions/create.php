<?php

/**
 * View: Permissions Create
 * Form untuk menambah permission baru
 * 
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
            <h1><i class="fas fa-plus-circle me-2"></i><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('super/dashboard') ?>">Super Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('super/permissions') ?>">Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Baru</li>
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
                    <i class="fas fa-keyboard me-2"></i>Form Permission Baru
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('super/permissions/store') ?>" method="POST" id="permissionForm">
                    <?= csrf_field() ?>

                    <!-- Permission Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Permission Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= $validation->hasError('name') ? 'is-invalid' : '' ?>"
                            id="name"
                            name="name"
                            value="<?= old('name') ?>"
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
                                            <?php foreach ($existingModules as $module): ?>
                                                <option value="<?= esc($module) ?>"><?= esc(ucfirst($module)) ?></option>
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
                                    <option value="view">view (Lihat data)</option>
                                    <option value="create">create (Tambah data)</option>
                                    <option value="edit">edit (Edit data)</option>
                                    <option value="delete">delete (Hapus data)</option>
                                    <option value="approve">approve (Setujui data)</option>
                                    <option value="export">export (Export data)</option>
                                    <option value="import">import (Import data)</option>
                                    <option value="manage">manage (Kelola penuh)</option>
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
                            required><?= old('description') ?></textarea>
                        <div class="form-text">
                            Minimal 10 karakter, maksimal 255 karakter
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
                            <i class="fas fa-save me-1"></i>Simpan Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4 col-xl-5">
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

                <h6 class="fw-bold">Contoh Permission:</h6>
                <div class="mb-2">
                    <code class="text-primary">member.view</code>
                    <div class="form-text">Dapat melihat data anggota</div>
                </div>
                <div class="mb-2">
                    <code class="text-primary">member.create</code>
                    <div class="form-text">Dapat menambah anggota baru</div>
                </div>
                <div class="mb-2">
                    <code class="text-primary">forum.manage</code>
                    <div class="form-text">Dapat mengelola forum (full access)</div>
                </div>
                <div class="mb-2">
                    <code class="text-primary">report.export</code>
                    <div class="form-text">Dapat export laporan</div>
                </div>

                <hr>

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

        <!-- Common Modules Card -->
        <div class="card border-success mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Common Modules</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-primary">member</span>
                    <span class="badge bg-primary">forum</span>
                    <span class="badge bg-primary">survey</span>
                    <span class="badge bg-primary">complaint</span>
                    <span class="badge bg-primary">content</span>
                    <span class="badge bg-primary">event</span>
                    <span class="badge bg-primary">report</span>
                    <span class="badge bg-primary">system</span>
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

        // Auto-generate description suggestion
        const actionText = document.getElementById('actionSelect').selectedOptions[0].text;
        const moduleName = module.charAt(0).toUpperCase() + module.slice(1);
        const suggestedDesc = `Dapat ${actionText.split('(')[1].replace(')', '')} ${moduleName.toLowerCase()}`;

        if (!document.getElementById('description').value) {
            document.getElementById('description').value = suggestedDesc;
        }

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
            title: 'Permission name generated!'
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
        const formText = this.nextElementSibling;

        if (length < 10) {
            formText.innerHTML = `Minimal 10 karakter, maksimal 255 karakter (${length}/10)`;
            formText.classList.add('text-danger');
        } else if (length > 255) {
            formText.innerHTML = `Maksimal 255 karakter terlampaui! (${length}/255)`;
            formText.classList.add('text-danger');
        } else {
            formText.innerHTML = `${length}/255 karakter`;
            formText.classList.remove('text-danger');
            formText.classList.add('text-success');
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
    });

    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
<?php $this->endSection(); ?>