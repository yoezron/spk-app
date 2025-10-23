<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($unit) && !empty($unit['id']);
$formAction = $isEdit
    ? base_url('admin/org-structure/unit/' . $unit['id'] . '/update')
    : base_url('admin/org-structure/unit/store');
?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><?= $isEdit ? 'Edit Unit Organisasi' : 'Tambah Unit Organisasi' ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/org-structure') ?>">Struktur Organisasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Edit Unit' : 'Tambah Unit' ?></li>
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
                <h5 class="card-title"><?= $isEdit ? 'Edit' : 'Tambah' ?> Unit Organisasi</h5>
            </div>
            <div class="card-body">
                <form action="<?= $formAction ?>" method="POST" id="unitForm">
                    <?= csrf_field() ?>

                    <!-- Nama Unit -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nama Unit <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                               id="name"
                               name="name"
                               value="<?= old('name', $unit['name'] ?? '') ?>"
                               placeholder="Contoh: Departemen Hubungan Luar"
                               required>
                        <?php if (session('errors.name')): ?>
                            <div class="invalid-feedback"><?= session('errors.name') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Scope -->
                    <div class="mb-3">
                        <label for="scope" class="form-label">
                            Scope <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?= session('errors.scope') ? 'is-invalid' : '' ?>"
                                id="scope"
                                name="scope"
                                required>
                            <option value="">-- Pilih Scope --</option>
                            <option value="pusat" <?= old('scope', $unit['scope'] ?? '') === 'pusat' ? 'selected' : '' ?>>
                                Pusat (Nasional)
                            </option>
                            <option value="wilayah" <?= old('scope', $unit['scope'] ?? '') === 'wilayah' ? 'selected' : '' ?>>
                                Wilayah (Provinsi)
                            </option>
                            <option value="kampus" <?= old('scope', $unit['scope'] ?? '') === 'kampus' ? 'selected' : '' ?>>
                                Kampus (Universitas)
                            </option>
                            <option value="departemen" <?= old('scope', $unit['scope'] ?? '') === 'departemen' ? 'selected' : '' ?>>
                                Departemen
                            </option>
                            <option value="divisi" <?= old('scope', $unit['scope'] ?? '') === 'divisi' ? 'selected' : '' ?>>
                                Divisi
                            </option>
                            <option value="seksi" <?= old('scope', $unit['scope'] ?? '') === 'seksi' ? 'selected' : '' ?>>
                                Seksi
                            </option>
                        </select>
                        <?php if (session('errors.scope')): ?>
                            <div class="invalid-feedback"><?= session('errors.scope') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Tentukan tingkat scope unit organisasi
                        </small>
                    </div>

                    <!-- Level -->
                    <div class="mb-3">
                        <label for="level" class="form-label">
                            Level Hirarki <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control <?= session('errors.level') ? 'is-invalid' : '' ?>"
                               id="level"
                               name="level"
                               value="<?= old('level', $unit['level'] ?? 1) ?>"
                               min="1"
                               max="10"
                               required>
                        <?php if (session('errors.level')): ?>
                            <div class="invalid-feedback"><?= session('errors.level') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Level 1 = Paling atas, semakin besar angka semakin ke bawah dalam hirarki
                        </small>
                    </div>

                    <!-- Parent Unit -->
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">
                            Unit Induk (Parent)
                        </label>
                        <select class="form-select <?= session('errors.parent_id') ? 'is-invalid' : '' ?>"
                                id="parent_id"
                                name="parent_id">
                            <option value="">-- Tidak Ada Parent (Root Level) --</option>
                            <?php if (!empty($units) && is_array($units)): ?>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>"
                                            <?= old('parent_id', $unit['parent_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= str_repeat('—', ($u['level'] ?? 1) - 1) ?> <?= esc($u['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (session('errors.parent_id')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_id') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Pilih unit induk jika unit ini merupakan sub-unit dari unit lain
                        </small>
                    </div>

                    <!-- Provinsi (untuk scope wilayah) -->
                    <div class="mb-3" id="provinceField" style="display: none;">
                        <label for="province_id" class="form-label">
                            Provinsi
                        </label>
                        <select class="form-select <?= session('errors.province_id') ? 'is-invalid' : '' ?>"
                                id="province_id"
                                name="province_id">
                            <option value="">-- Pilih Provinsi --</option>
                            <?php if (!empty($provinces) && is_array($provinces)): ?>
                                <?php foreach ($provinces as $province): ?>
                                    <option value="<?= $province->id ?>"
                                            <?= old('province_id', $unit['province_id'] ?? '') == $province->id ? 'selected' : '' ?>>
                                        <?= esc($province->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (session('errors.province_id')): ?>
                            <div class="invalid-feedback"><?= session('errors.province_id') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Wajib diisi untuk unit dengan scope Wilayah
                        </small>
                    </div>

                    <!-- Universitas (untuk scope kampus) -->
                    <div class="mb-3" id="universityField" style="display: none;">
                        <label for="university_id" class="form-label">
                            Universitas
                        </label>
                        <select class="form-select <?= session('errors.university_id') ? 'is-invalid' : '' ?>"
                                id="university_id"
                                name="university_id">
                            <option value="">-- Pilih Universitas --</option>
                            <?php if (!empty($universities) && is_array($universities)): ?>
                                <?php foreach ($universities as $university): ?>
                                    <option value="<?= $university->id ?>"
                                            <?= old('university_id', $unit['university_id'] ?? '') == $university->id ? 'selected' : '' ?>>
                                        <?= esc($university->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (session('errors.university_id')): ?>
                            <div class="invalid-feedback"><?= session('errors.university_id') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Wajib diisi untuk unit dengan scope Kampus
                        </small>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Deskripsi
                        </label>
                        <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Deskripsi singkat tentang unit ini..."><?= old('description', $unit['description'] ?? '') ?></textarea>
                        <?php if (session('errors.description')): ?>
                            <div class="invalid-feedback"><?= session('errors.description') ?></div>
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
                                   <?= old('is_active', $unit['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Unit Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Unit yang tidak aktif akan disembunyikan dari struktur organisasi
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('admin/org-structure') ?>" class="btn btn-secondary">
                            <i class="material-icons-outlined">cancel</i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="material-icons-outlined">save</i>
                            <?= $isEdit ? 'Update Unit' : 'Simpan Unit' ?>
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
                <h6><i class="material-icons-outlined">info</i> Tentang Scope</h6>
                <ul class="small mb-3">
                    <li><strong>Pusat:</strong> Unit di tingkat nasional/pusat</li>
                    <li><strong>Wilayah:</strong> Unit di tingkat provinsi</li>
                    <li><strong>Kampus:</strong> Unit di tingkat universitas</li>
                    <li><strong>Departemen:</strong> Unit kerja utama</li>
                    <li><strong>Divisi:</strong> Sub-unit dari departemen</li>
                    <li><strong>Seksi:</strong> Sub-unit dari divisi</li>
                </ul>

                <h6><i class="material-icons-outlined">info</i> Tentang Level</h6>
                <ul class="small mb-3">
                    <li>Level 1: Tingkat paling atas (misal: Presidium)</li>
                    <li>Level 2: Sub-unit dari Level 1 (misal: Departemen)</li>
                    <li>Level 3: Sub-unit dari Level 2 (misal: Divisi)</li>
                    <li>Dan seterusnya...</li>
                </ul>

                <div class="alert alert-warning">
                    <small>
                        <i class="material-icons-outlined">warning</i>
                        <strong>Perhatian:</strong> Pastikan level unit konsisten dengan parent-nya.
                        Level child harus lebih besar dari parent.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Show/hide province and university fields based on scope
    function toggleScopeFields() {
        const scope = $('#scope').val();

        // Hide all conditional fields
        $('#provinceField, #universityField').hide();

        // Show relevant fields
        if (scope === 'wilayah') {
            $('#provinceField').show();
        } else if (scope === 'kampus') {
            $('#universityField').show();
        }
    }

    // Initial check
    toggleScopeFields();

    // On scope change
    $('#scope').on('change', function() {
        toggleScopeFields();
    });

    // Form validation
    $('#unitForm').on('submit', function(e) {
        const scope = $('#scope').val();

        // Validate province for wilayah scope
        if (scope === 'wilayah' && !$('#province_id').val()) {
            e.preventDefault();
            alert('Provinsi wajib dipilih untuk unit dengan scope Wilayah');
            $('#province_id').focus();
            return false;
        }

        // Validate university for kampus scope
        if (scope === 'kampus' && !$('#university_id').val()) {
            e.preventDefault();
            alert('Universitas wajib dipilih untuk unit dengan scope Kampus');
            $('#university_id').focus();
            return false;
        }

        return true;
    });
});
</script>
<?= $this->endSection() ?>
