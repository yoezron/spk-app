<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<style>
    .edit-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .form-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .form-section-header h5 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .form-section-header i {
        font-size: 24px;
        color: #667eea;
    }

    .form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 8px;
    }

    .form-control,
    .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-text {
        font-size: 12px;
        color: #718096;
        margin-top: 4px;
    }

    .required-mark {
        color: #dc3545;
        font-weight: bold;
    }

    .photo-preview {
        width: 150px;
        height: 150px;
        border-radius: 12px;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        margin-bottom: 15px;
    }

    .photo-upload-wrapper {
        text-align: center;
        padding: 20px;
        background: #f7fafc;
        border-radius: 12px;
        border: 2px dashed #cbd5e0;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 32px;
        font-weight: 600;
        border-radius: 8px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
        padding: 12px 32px;
        font-weight: 600;
        border-radius: 8px;
    }

    .sticky-actions {
        position: sticky;
        bottom: 20px;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        z-index: 100;
        margin-top: 30px;
    }

    .invalid-feedback {
        display: block;
        font-size: 13px;
        color: #dc3545;
        margin-top: 6px;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="edit-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2 text-white">
                <i class="feather icon-edit"></i> Edit Profil Anggota
            </h3>
            <p class="mb-0 text-white opacity-90">
                Update informasi anggota: <?= esc($member->full_name) ?>
            </p>
        </div>
        <a href="<?= base_url('admin/members/show/' . $member->id) ?>" class="btn btn-light">
            <i class="feather icon-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> <?= session('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= session('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validasi Error:</strong>
        <ul class="mb-0">
            <?php foreach (session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Edit Form -->
<form method="post" action="<?= base_url('admin/members/update/' . $member->id) ?>" enctype="multipart/form-data" id="editMemberForm">
    <?= csrf_field() ?>

    <!-- Personal Information -->
    <div class="form-section">
        <div class="form-section-header">
            <i class="feather icon-user"></i>
            <h5>Informasi Personal</h5>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">
                        Nama Lengkap <span class="required-mark">*</span>
                    </label>
                    <input type="text"
                        name="full_name"
                        class="form-control <?= isset(session('errors')['full_name']) ? 'is-invalid' : '' ?>"
                        value="<?= old('full_name', $member->full_name) ?>"
                        required>
                    <?php if (isset(session('errors')['full_name'])): ?>
                        <div class="invalid-feedback"><?= session('errors')['full_name'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email"
                        name="email"
                        class="form-control <?= isset(session('errors')['email']) ? 'is-invalid' : '' ?>"
                        value="<?= old('email', $member->email) ?>"
                        <?= !auth()->user()->inGroup('superadmin') ? 'readonly' : '' ?>>
                    <small class="form-text">
                        <?= auth()->user()->inGroup('superadmin') ? 'Email dapat diubah oleh Super Admin' : 'Email tidak dapat diubah' ?>
                    </small>
                    <?php if (isset(session('errors')['email'])): ?>
                        <div class="invalid-feedback"><?= session('errors')['email'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">NIK/NIP</label>
                    <input type="text"
                        name="nidn_nip"
                        class="form-control"
                        value="<?= old('nidn_nip', $member->nidn_nip) ?>">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" <?= old('gender', $member->gender) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= old('gender', $member->gender) === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text"
                        name="birth_place"
                        class="form-control"
                        value="<?= old('birth_place', $member->birth_place) ?>">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date"
                        name="birth_date"
                        class="form-control"
                        value="<?= old('birth_date', $member->birth_date) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="form-section">
        <div class="form-section-header">
            <i class="feather icon-phone"></i>
            <h5>Informasi Kontak</h5>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text"
                        name="phone"
                        class="form-control"
                        value="<?= old('phone', $member->phone) ?>"
                        placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="text"
                        name="whatsapp"
                        class="form-control"
                        value="<?= old('whatsapp', $member->whatsapp) ?>"
                        placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address"
                        class="form-control"
                        rows="3"><?= old('address', $member->address) ?></textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Provinsi</label>
                    <select name="province_id" id="province_id" class="form-select select2">
                        <option value="">Pilih Provinsi</option>
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?= $province->id ?>"
                                <?= old('province_id', $member->province_id) == $province->id ? 'selected' : '' ?>>
                                <?= esc($province->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Kabupaten/Kota</label>
                    <select name="regency_id" id="regency_id" class="form-select select2">
                        <option value="">Pilih Kabupaten/Kota</option>
                        <!-- Loaded via AJAX based on province -->
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic/Work Information -->
    <div class="form-section">
        <div class="form-section-header">
            <i class="feather icon-briefcase"></i>
            <h5>Informasi Akademik & Pekerjaan</h5>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Perguruan Tinggi</label>
                    <select name="university_id" id="university_id" class="form-select select2">
                        <option value="">Pilih Perguruan Tinggi</option>
                        <?php foreach ($universities as $university): ?>
                            <option value="<?= $university->id ?>"
                                <?= old('university_id', $member->university_id) == $university->id ? 'selected' : '' ?>>
                                <?= esc($university->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Program Studi</label>
                    <select name="study_program_id" id="study_program_id" class="form-select select2">
                        <option value="">Pilih Program Studi</option>
                        <!-- Loaded via AJAX based on university -->
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Membership Information (Super Admin Only) -->
    <?php if (auth()->user()->inGroup('superadmin')): ?>
        <div class="form-section">
            <div class="form-section-header">
                <i class="feather icon-shield"></i>
                <h5>Informasi Keanggotaan & Role</h5>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Role/Peran</label>
                        <select name="role" class="form-select">
                            <option value="">Pilih Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= is_object($role) ? $role->title : $role['title'] ?>">
                                    <?= esc(is_object($role) ? $role->title : $role['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Ubah role anggota (hanya Super Admin)</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Nomor Anggota</label>
                        <input type="text"
                            class="form-control"
                            value="<?= esc($member->member_number ?? 'Belum diberikan') ?>"
                            readonly>
                        <small class="form-text">Nomor anggota tidak dapat diubah</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sticky Actions -->
    <div class="sticky-actions">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    <i class="feather icon-info"></i>
                    Pastikan semua data sudah benar sebelum menyimpan
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('admin/members/show/' . $member->id) ?>"
                    class="btn btn-secondary">
                    <i class="feather icon-x"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="feather icon-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih...',
            allowClear: true
        });

        // Load Regencies based on Province
        $('#province_id').on('change', function() {
            const provinceId = $(this).val();
            const regencySelect = $('#regency_id');

            regencySelect.html('<option value="">Loading...</option>');

            if (provinceId) {
                $.ajax({
                    url: '<?= base_url('api/regencies') ?>',
                    type: 'GET',
                    data: {
                        province_id: provinceId
                    },
                    success: function(response) {
                        regencySelect.html('<option value="">Pilih Kabupaten/Kota</option>');

                        if (response.success && response.data) {
                            response.data.forEach(function(regency) {
                                regencySelect.append(
                                    `<option value="${regency.id}">${regency.name}</option>`
                                );
                            });
                        }
                    },
                    error: function() {
                        regencySelect.html('<option value="">Error loading data</option>');
                    }
                });
            } else {
                regencySelect.html('<option value="">Pilih Kabupaten/Kota</option>');
            }
        });

        // Load Study Programs based on University
        $('#university_id').on('change', function() {
            const universityId = $(this).val();
            const prodiSelect = $('#study_program_id');

            prodiSelect.html('<option value="">Loading...</option>');

            if (universityId) {
                $.ajax({
                    url: '<?= base_url('api/study-programs') ?>',
                    type: 'GET',
                    data: {
                        university_id: universityId
                    },
                    success: function(response) {
                        prodiSelect.html('<option value="">Pilih Program Studi</option>');

                        if (response.success && response.data) {
                            response.data.forEach(function(prodi) {
                                prodiSelect.append(
                                    `<option value="${prodi.id}">${prodi.name}</option>`
                                );
                            });
                        }
                    },
                    error: function() {
                        prodiSelect.html('<option value="">Error loading data</option>');
                    }
                });
            } else {
                prodiSelect.html('<option value="">Pilih Program Studi</option>');
            }
        });

        // Load initial data if exists
        <?php if (!empty($member->province_id)): ?>
            $('#province_id').trigger('change');
            setTimeout(function() {
                $('#regency_id').val(<?= $member->regency_id ?? 'null' ?>).trigger('change');
            }, 500);
        <?php endif; ?>

        <?php if (!empty($member->university_id)): ?>
            $('#university_id').trigger('change');
            setTimeout(function() {
                $('#study_program_id').val(<?= $member->study_program_id ?? 'null' ?>).trigger('change');
            }, 500);
        <?php endif; ?>

        // Form validation before submit
        $('#editMemberForm').on('submit', function(e) {
            const fullName = $('input[name="full_name"]').val().trim();

            if (fullName.length < 3) {
                e.preventDefault();
                alert('Nama lengkap minimal 3 karakter');
                $('input[name="full_name"]').focus();
                return false;
            }

            // Show loading indicator
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="feather icon-loader"></i> Menyimpan...');

            return true;
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
<?= $this->endSection() ?>