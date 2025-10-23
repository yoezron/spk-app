<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($position) && !empty($position['id']);
$formAction = $isEdit
    ? base_url('admin/org-structure/position/' . $position['id'] . '/update')
    : base_url('admin/org-structure/position/store');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><?= $isEdit ? 'Edit Posisi' : 'Tambah Posisi' ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/org-structure') ?>">Struktur Organisasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Edit Posisi' : 'Tambah Posisi' ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?= $isEdit ? 'Edit' : 'Tambah' ?> Posisi/Jabatan</h5>
            </div>
            <div class="card-body">
                <form action="<?= $formAction ?>" method="POST" id="positionForm">
                    <?= csrf_field() ?>

                    <!-- Unit -->
                    <div class="mb-3">
                        <label for="unit_id" class="form-label">
                            Unit <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?= session('errors.unit_id') ? 'is-invalid' : '' ?>"
                                id="unit_id"
                                name="unit_id"
                                required>
                            <option value="">-- Pilih Unit --</option>
                            <?php if (!empty($units) && is_array($units)): ?>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>"
                                            <?= old('unit_id', $position['unit_id'] ?? $unit_id ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= str_repeat('—', ($u['level'] ?? 1) - 1) ?> <?= esc($u['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (session('errors.unit_id')): ?>
                            <div class="invalid-feedback"><?= session('errors.unit_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Posisi -->
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            Nama Posisi/Jabatan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                               id="title"
                               name="title"
                               value="<?= old('title', $position['title'] ?? '') ?>"
                               placeholder="Contoh: Ketua Departemen, Koordinator Wilayah Jawa Barat"
                               required>
                        <?php if (session('errors.title')): ?>
                            <div class="invalid-feedback"><?= session('errors.title') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tipe Posisi -->
                    <div class="mb-3">
                        <label for="position_type" class="form-label">
                            Tipe Posisi <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?= session('errors.position_type') ? 'is-invalid' : '' ?>"
                                id="position_type"
                                name="position_type"
                                required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="executive" <?= old('position_type', $position['position_type'] ?? '') === 'executive' ? 'selected' : '' ?>>
                                Executive (Pimpinan Eksekutif)
                            </option>
                            <option value="structural" <?= old('position_type', $position['position_type'] ?? '') === 'structural' ? 'selected' : '' ?>>
                                Structural (Struktural)
                            </option>
                            <option value="functional" <?= old('position_type', $position['position_type'] ?? '') === 'functional' ? 'selected' : '' ?>>
                                Functional (Fungsional)
                            </option>
                            <option value="coordinator" <?= old('position_type', $position['position_type'] ?? '') === 'coordinator' ? 'selected' : '' ?>>
                                Coordinator (Koordinator)
                            </option>
                            <option value="staff" <?= old('position_type', $position['position_type'] ?? '') === 'staff' ? 'selected' : '' ?>>
                                Staff (Staf)
                            </option>
                        </select>
                        <?php if (session('errors.position_type')): ?>
                            <div class="invalid-feedback"><?= session('errors.position_type') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Level Posisi -->
                    <div class="mb-3">
                        <label for="position_level" class="form-label">
                            Level Posisi <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?= session('errors.position_level') ? 'is-invalid' : '' ?>"
                                id="position_level"
                                name="position_level"
                                required>
                            <option value="">-- Pilih Level --</option>
                            <option value="top" <?= old('position_level', $position['position_level'] ?? '') === 'top' ? 'selected' : '' ?>>
                                Top Management (Pimpinan Tertinggi)
                            </option>
                            <option value="middle" <?= old('position_level', $position['position_level'] ?? '') === 'middle' ? 'selected' : '' ?>>
                                Middle Management (Pimpinan Menengah)
                            </option>
                            <option value="lower" <?= old('position_level', $position['position_level'] ?? '') === 'lower' ? 'selected' : '' ?>>
                                Lower Management (Pimpinan Bawah/Staff)
                            </option>
                        </select>
                        <?php if (session('errors.position_level')): ?>
                            <div class="invalid-feedback"><?= session('errors.position_level') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Reports To (Atasan Langsung) -->
                    <div class="mb-3">
                        <label for="reports_to" class="form-label">
                            Melapor Kepada (Atasan Langsung)
                        </label>
                        <select class="form-select <?= session('errors.reports_to') ? 'is-invalid' : '' ?>"
                                id="reports_to"
                                name="reports_to">
                            <option value="">-- Tidak Ada Atasan (Top Position) --</option>
                            <?php if (!empty($positions) && is_array($positions)): ?>
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?= $pos['id'] ?>"
                                            <?= old('reports_to', $position['reports_to'] ?? '') == $pos['id'] ? 'selected' : '' ?>>
                                        <?= esc($pos['title']) ?> (<?= esc($pos['unit_name'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (session('errors.reports_to')): ?>
                            <div class="invalid-feedback"><?= session('errors.reports_to') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Pilih posisi atasan langsung untuk membentuk hirarki
                        </small>
                    </div>

                    <!-- Max Holders -->
                    <div class="mb-3">
                        <label for="max_holders" class="form-label">
                            Maksimal Pemegang Jabatan
                        </label>
                        <input type="number"
                               class="form-control <?= session('errors.max_holders') ? 'is-invalid' : '' ?>"
                               id="max_holders"
                               name="max_holders"
                               value="<?= old('max_holders', $position['max_holders'] ?? 1) ?>"
                               min="1"
                               max="100">
                        <?php if (session('errors.max_holders')): ?>
                            <div class="invalid-feedback"><?= session('errors.max_holders') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Berapa banyak anggota yang bisa memegang posisi ini secara bersamaan (default: 1)
                        </small>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Deskripsi & Tanggung Jawab
                        </label>
                        <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                                  id="description"
                                  name="description"
                                  rows="5"
                                  placeholder="Deskripsi posisi dan tanggung jawab utama..."><?= old('description', $position['description'] ?? '') ?></textarea>
                        <?php if (session('errors.description')): ?>
                            <div class="invalid-feedback"><?= session('errors.description') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Kualifikasi -->
                    <div class="mb-3">
                        <label for="requirements" class="form-label">
                            Kualifikasi & Persyaratan
                        </label>
                        <textarea class="form-control <?= session('errors.requirements') ? 'is-invalid' : '' ?>"
                                  id="requirements"
                                  name="requirements"
                                  rows="4"
                                  placeholder="Kualifikasi yang diperlukan untuk posisi ini..."><?= old('requirements', $position['requirements'] ?? '') ?></textarea>
                        <?php if (session('errors.requirements')): ?>
                            <div class="invalid-feedback"><?= session('errors.requirements') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Status Aktif -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   <?= old('is_active', $position['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Posisi Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Posisi yang tidak aktif tidak bisa di-assign ke anggota
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('admin/org-structure') ?>" class="btn btn-secondary">
                            <i class="material-icons-outlined">cancel</i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="material-icons-outlined">save</i>
                            <?= $isEdit ? 'Update Posisi' : 'Simpan Posisi' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Panduan</h5>
            </div>
            <div class="card-body">
                <h6><i class="material-icons-outlined">info</i> Tipe Posisi</h6>
                <ul class="small mb-3">
                    <li><strong>Executive:</strong> Pimpinan eksekutif (Ketua, Wakil, dll)</li>
                    <li><strong>Structural:</strong> Jabatan struktural dalam organisasi</li>
                    <li><strong>Functional:</strong> Jabatan fungsional/teknis</li>
                    <li><strong>Coordinator:</strong> Koordinator tim/wilayah</li>
                    <li><strong>Staff:</strong> Staf/anggota tim</li>
                </ul>

                <h6><i class="material-icons-outlined">info</i> Level Posisi</h6>
                <ul class="small mb-3">
                    <li><strong>Top:</strong> Pimpinan tertinggi (Ketua Umum, dll)</li>
                    <li><strong>Middle:</strong> Pimpinan menengah (Kepala Dept, dll)</li>
                    <li><strong>Lower:</strong> Pimpinan bawah/staf</li>
                </ul>

                <div class="alert alert-info">
                    <small>
                        <i class="material-icons-outlined">lightbulb</i>
                        <strong>Tips:</strong> Gunakan field "Reports To" untuk membentuk chain of command
                        yang jelas dalam struktur organisasi.
                    </small>
                </div>
            </div>
        </div>

        <!-- Preview Card (if edit mode) -->
        <?php if ($isEdit && !empty($position['current_holders'])): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Pemegang Jabatan Saat Ini</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($position['current_holders'] as $holder): ?>
                        <div class="d-flex align-items-center mb-2">
                            <i class="material-icons-outlined me-2">person</i>
                            <div>
                                <div><?= esc($holder['full_name'] ?? $holder['email'] ?? 'N/A') ?></div>
                                <?php if (!empty($holder['started_at'])): ?>
                                    <small class="text-muted">Sejak: <?= date('d/m/Y', strtotime($holder['started_at'])) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Form validation
    $('#positionForm').on('submit', function(e) {
        const unitId = $('#unit_id').val();
        const title = $('#title').val().trim();
        const positionType = $('#position_type').val();
        const positionLevel = $('#position_level').val();

        if (!unitId) {
            e.preventDefault();
            alert('Unit wajib dipilih');
            $('#unit_id').focus();
            return false;
        }

        if (!title) {
            e.preventDefault();
            alert('Nama posisi wajib diisi');
            $('#title').focus();
            return false;
        }

        if (!positionType) {
            e.preventDefault();
            alert('Tipe posisi wajib dipilih');
            $('#position_type').focus();
            return false;
        }

        if (!positionLevel) {
            e.preventDefault();
            alert('Level posisi wajib dipilih');
            $('#position_level').focus();
            return false;
        }

        return true;
    });
});
</script>
<?= $this->endSection() ?>
