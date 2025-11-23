<?php

/**
 * View: Member Profile Edit
 * Controller: Member\ProfileController
 * Description: Form edit profil anggota dengan validasi lengkap
 * 
 * Features:
 * - Complete edit profile form
 * - Photo upload with preview
 * - Dynamic regencies dropdown (by province)
 * - Dynamic study programs dropdown (by university)
 * - Form validation display
 * - Personal info section
 * - Contact info section
 * - Employment info section
 * - Save & cancel buttons
 * - Responsive form layout
 * 
 * @package App\Views\Member\Profile
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 30px;
    }

    .page-header h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header p {
        opacity: 0.95;
        margin: 0;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .form-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }

    .form-card-header i {
        font-size: 28px;
        color: #667eea;
    }

    .form-card-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    /* Photo Upload */
    .photo-upload-section {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        padding: 30px;
        background: #f7fafc;
        border-radius: 12px;
        border: 2px dashed #e2e8f0;
    }

    .photo-preview {
        position: relative;
    }

    .photo-preview img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .photo-upload-info {
        flex: 1;
    }

    .photo-upload-info h4 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .photo-upload-info p {
        color: #718096;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .photo-upload-info .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .photo-upload-info .file-input-wrapper input[type=file] {
        position: absolute;
        left: -9999px;
    }

    .photo-upload-info .btn-upload {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .photo-upload-info .btn-upload:hover {
        background: #5568d3;
        transform: translateY(-2px);
    }

    /* Form Groups */
    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group label .required {
        color: #f56565;
        margin-left: 4px;
    }

    .form-group .form-control,
    .form-group .form-select {
        height: 48px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 16px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-group textarea.form-control {
        height: auto;
        min-height: 120px;
        padding: 12px 16px;
    }

    .form-group .form-control:focus,
    .form-group .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-group .invalid-feedback {
        display: block;
        margin-top: 8px;
        font-size: 13px;
        color: #f56565;
    }

    .form-group .form-control.is-invalid,
    .form-group .form-select.is-invalid {
        border-color: #f56565;
    }

    .form-group .form-text {
        display: block;
        margin-top: 6px;
        font-size: 13px;
        color: #a0aec0;
    }

    .form-group .input-group {
        position: relative;
    }

    .form-group .input-group-text {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #a0aec0;
        font-size: 18px;
        z-index: 5;
    }

    .form-group .input-group .form-control {
        padding-left: 48px;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 16px;
        padding-top: 30px;
        border-top: 2px solid #e2e8f0;
        margin-top: 30px;
    }

    .form-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #e2e8f0;
        border: none;
        color: #2d3748;
    }

    .btn-secondary:hover {
        background: #cbd5e0;
        transform: translateY(-2px);
    }

    /* Alert Box */
    .alert-info-box {
        background: #e6f2ff;
        border-left: 4px solid #4299e1;
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .alert-info-box p {
        margin: 0;
        color: #2c5282;
        font-size: 14px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-info-box i {
        font-size: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Loading Overlay */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-spinner {
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        text-align: center;
    }

    .spinner-border {
        width: 50px;
        height: 50px;
        border-width: 4px;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .page-header {
            padding: 20px;
        }

        .page-header h2 {
            font-size: 24px;
        }

        .form-card {
            padding: 24px 20px;
        }

        .photo-upload-section {
            flex-direction: column;
            text-align: center;
        }

        .photo-preview img {
            width: 120px;
            height: 120px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="bi bi-pencil-square"></i> Edit Profil</h2>
    <p>Perbarui informasi profil Anda</p>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Info Alert -->
<div class="alert-info-box">
    <p>
        <i class="bi bi-info-circle-fill"></i>
        <span>Pastikan semua data yang Anda masukkan adalah benar dan sesuai dengan dokumen resmi. Data yang sudah disimpan dapat diubah kembali kapan saja.</span>
    </p>
</div>

<form action="<?= base_url('member/profile/update') ?>" method="POST" enctype="multipart/form-data" id="profileForm">
    <?= csrf_field() ?>

    <!-- Photo Upload Section -->
    <div class="form-card">
        <div class="form-card-header">
            <i class="bi bi-camera-fill"></i>
            <h3>Foto Profil</h3>
        </div>

        <div class="photo-upload-section">
            <div class="photo-preview">
                <img src="<?= !empty($member->photo) ? esc($member->photo) : base_url('assets/images/default-avatar.png') ?>"
                    alt="Preview"
                    id="photoPreview">
            </div>
            <div class="photo-upload-info">
                <h4>Upload Foto Profil</h4>
                <p>Format: JPG, PNG, maksimal 2MB. Disarankan ukuran 500x500 pixels.</p>
                <div class="file-input-wrapper">
                    <label for="photo" class="btn-upload">
                        <i class="bi bi-cloud-upload"></i>
                        Pilih Foto
                    </label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>
                <small class="form-text">Biarkan kosong jika tidak ingin mengubah foto</small>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="form-card">
        <div class="form-card-header">
            <i class="bi bi-person-circle"></i>
            <h3>Informasi Pribadi</h3>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap <span class="required">*</span></label>
                    <input type="text"
                        class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                        id="full_name"
                        name="full_name"
                        value="<?= old('full_name', $member->full_name) ?>"
                        required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="nik">NIK <span class="required">*</span></label>
                    <input type="text"
                        class="form-control <?= isset($errors['nik']) ? 'is-invalid' : '' ?>"
                        id="nik"
                        name="nik"
                        value="<?= old('nik', $member->nik) ?>"
                        maxlength="16"
                        required>
                    <?php if (isset($errors['nik'])): ?>
                        <div class="invalid-feedback"><?= $errors['nik'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">16 digit NIK sesuai KTP</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="nidn_nip">NIDN/NIP</label>
                    <input type="text"
                        class="form-control <?= isset($errors['nidn_nip']) ? 'is-invalid' : '' ?>"
                        id="nidn_nip"
                        name="nidn_nip"
                        value="<?= old('nidn_nip', $member->nidn_nip) ?>"
                        maxlength="30">
                    <?php if (isset($errors['nidn_nip'])): ?>
                        <div class="invalid-feedback"><?= $errors['nidn_nip'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">NIDN untuk Dosen atau NIP untuk Pegawai</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="birth_place">Tempat Lahir <span class="required">*</span></label>
                    <input type="text"
                        class="form-control <?= isset($errors['birth_place']) ? 'is-invalid' : '' ?>"
                        id="birth_place"
                        name="birth_place"
                        value="<?= old('birth_place', $member->birth_place) ?>"
                        required>
                    <?php if (isset($errors['birth_place'])): ?>
                        <div class="invalid-feedback"><?= $errors['birth_place'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="birth_date">Tanggal Lahir <span class="required">*</span></label>
                    <input type="date"
                        class="form-control <?= isset($errors['birth_date']) ? 'is-invalid' : '' ?>"
                        id="birth_date"
                        name="birth_date"
                        value="<?= old('birth_date', $member->birth_date) ?>"
                        required>
                    <?php if (isset($errors['birth_date'])): ?>
                        <div class="invalid-feedback"><?= $errors['birth_date'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="gender">Jenis Kelamin <span class="required">*</span></label>
                    <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>"
                        id="gender"
                        name="gender"
                        required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" <?= old('gender', $member->gender) === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= old('gender', $member->gender) === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                    <?php if (isset($errors['gender'])): ?>
                        <div class="invalid-feedback"><?= $errors['gender'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="religion">Agama <span class="required">*</span></label>
                    <select class="form-select <?= isset($errors['religion']) ? 'is-invalid' : '' ?>"
                        id="religion"
                        name="religion"
                        required>
                        <option value="">Pilih Agama</option>
                        <option value="Islam" <?= old('religion', $member->religion) === 'Islam' ? 'selected' : '' ?>>Islam</option>
                        <option value="Kristen" <?= old('religion', $member->religion) === 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                        <option value="Katolik" <?= old('religion', $member->religion) === 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                        <option value="Hindu" <?= old('religion', $member->religion) === 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                        <option value="Buddha" <?= old('religion', $member->religion) === 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                        <option value="Konghucu" <?= old('religion', $member->religion) === 'Konghucu' ? 'selected' : '' ?>>Konghucu</option>
                    </select>
                    <?php if (isset($errors['religion'])): ?>
                        <div class="invalid-feedback"><?= $errors['religion'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="marital_status">Status Pernikahan</label>
                    <select class="form-select <?= isset($errors['marital_status']) ? 'is-invalid' : '' ?>"
                        id="marital_status"
                        name="marital_status">
                        <option value="">Pilih Status</option>
                        <option value="Belum Menikah" <?= old('marital_status', $member->marital_status) === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                        <option value="Menikah" <?= old('marital_status', $member->marital_status) === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                        <option value="Cerai" <?= old('marital_status', $member->marital_status) === 'Cerai' ? 'selected' : '' ?>>Cerai</option>
                    </select>
                    <?php if (isset($errors['marital_status'])): ?>
                        <div class="invalid-feedback"><?= $errors['marital_status'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="form-card">
        <div class="form-card-header">
            <i class="bi bi-telephone-fill"></i>
            <h3>Informasi Kontak</h3>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="phone">No. Telepon <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                        <input type="tel"
                            class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                            id="phone"
                            name="phone"
                            value="<?= old('phone', $member->phone) ?>"
                            placeholder="08123456789"
                            required>
                    </div>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="whatsapp">No. WhatsApp <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                        <input type="tel"
                            class="form-control <?= isset($errors['whatsapp']) ? 'is-invalid' : '' ?>"
                            id="whatsapp"
                            name="whatsapp"
                            value="<?= old('whatsapp', $member->whatsapp) ?>"
                            placeholder="08123456789"
                            required>
                    </div>
                    <?php if (isset($errors['whatsapp'])): ?>
                        <div class="invalid-feedback"><?= $errors['whatsapp'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                            class="form-control"
                            id="email"
                            value="<?= esc($user->email) ?>"
                            disabled>
                    </div>
                    <small class="form-text">Email tidak dapat diubah</small>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="address">Alamat Lengkap <span class="required">*</span></label>
                    <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                        id="address"
                        name="address"
                        rows="4"
                        required><?= old('address', $member->address) ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback"><?= $errors['address'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="province_id">Provinsi <span class="required">*</span></label>
                    <select class="form-select <?= isset($errors['province_id']) ? 'is-invalid' : '' ?>"
                        id="province_id"
                        name="province_id"
                        required>
                        <option value="">Pilih Provinsi</option>
                        <?php if (!empty($provinces)): ?>
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province->id ?>" <?= old('province_id', $member->province_id) == $province->id ? 'selected' : '' ?>>
                                    <?= esc($province->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['province_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['province_id'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="regency_id">Kabupaten/Kota <span class="required">*</span></label>
                    <select class="form-select <?= isset($errors['regency_id']) ? 'is-invalid' : '' ?>"
                        id="regency_id"
                        name="regency_id"
                        required>
                        <option value="">Pilih Kabupaten/Kota</option>
                        <?php if (!empty($regencies)): ?>
                            <?php foreach ($regencies as $regency): ?>
                                <option value="<?= $regency->id ?>" <?= old('regency_id', $member->regency_id) == $regency->id ? 'selected' : '' ?>>
                                    <?= esc($regency->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['regency_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['regency_id'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input type="text"
                        class="form-control <?= isset($errors['postal_code']) ? 'is-invalid' : '' ?>"
                        id="postal_code"
                        name="postal_code"
                        value="<?= old('postal_code', $member->postal_code) ?>"
                        maxlength="5">
                    <?php if (isset($errors['postal_code'])): ?>
                        <div class="invalid-feedback"><?= $errors['postal_code'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Employment Information -->
    <div class="form-card">
        <div class="form-card-header">
            <i class="bi bi-building"></i>
            <h3>Informasi Pekerjaan</h3>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="university_id">Kampus <span class="required">*</span></label>
                    <select class="form-select <?= isset($errors['university_id']) ? 'is-invalid' : '' ?>"
                        id="university_id"
                        name="university_id"
                        required>
                        <option value="">Pilih Kampus</option>
                        <?php if (!empty($universities)): ?>
                            <?php foreach ($universities as $university): ?>
                                <option value="<?= $university->id ?>" <?= old('university_id', $member->university_id) == $university->id ? 'selected' : '' ?>>
                                    <?= esc($university->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['university_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['university_id'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="study_program_id">Program Studi</label>
                    <select class="form-select <?= isset($errors['study_program_id']) ? 'is-invalid' : '' ?>"
                        id="study_program_id"
                        name="study_program_id">
                        <option value="">Pilih Program Studi</option>
                        <?php if (!empty($studyPrograms)): ?>
                            <?php foreach ($studyPrograms as $program): ?>
                                <option value="<?= $program->id ?>" <?= old('study_program_id', $member->study_program_id) == $program->id ? 'selected' : '' ?>>
                                    <?= esc($program->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['study_program_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['study_program_id'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="employment_type">Jenis Kepegawaian <span class="required">*</span></label>
                    <select class="form-select <?= isset($errors['employment_type']) ? 'is-invalid' : '' ?>"
                        id="employment_type"
                        name="employment_type"
                        required>
                        <option value="">Pilih Jenis</option>
                        <option value="Dosen Tetap" <?= old('employment_type', $member->employment_type) === 'Dosen Tetap' ? 'selected' : '' ?>>Dosen Tetap</option>
                        <option value="Dosen Tidak Tetap" <?= old('employment_type', $member->employment_type) === 'Dosen Tidak Tetap' ? 'selected' : '' ?>>Dosen Tidak Tetap</option>
                        <option value="Tendik Tetap" <?= old('employment_type', $member->employment_type) === 'Tendik Tetap' ? 'selected' : '' ?>>Tendik Tetap</option>
                        <option value="Tendik Tidak Tetap" <?= old('employment_type', $member->employment_type) === 'Tendik Tidak Tetap' ? 'selected' : '' ?>>Tendik Tidak Tetap</option>
                        <option value="Honorer" <?= old('employment_type', $member->employment_type) === 'Honorer' ? 'selected' : '' ?>>Honorer</option>
                    </select>
                    <?php if (isset($errors['employment_type'])): ?>
                        <div class="invalid-feedback"><?= $errors['employment_type'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Jenis pegawai (Dosen/Tendik)</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="employment_status_id">Status Kepegawaian</label>
                    <select class="form-select <?= isset($errors['employment_status_id']) ? 'is-invalid' : '' ?>"
                        id="employment_status_id"
                        name="employment_status_id">
                        <option value="">Pilih Status</option>
                        <?php if (!empty($employment_statuses)): ?>
                            <?php foreach ($employment_statuses as $status): ?>
                                <option value="<?= $status->id ?>" <?= old('employment_status_id', $member->employment_status_id) == $status->id ? 'selected' : '' ?>>
                                    <?= esc($status->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['employment_status_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['employment_status_id'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">PNS, Non-PNS, Kontrak, dll</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="position">Jabatan</label>
                    <input type="text"
                        class="form-control <?= isset($errors['position']) ? 'is-invalid' : '' ?>"
                        id="position"
                        name="job_position"
                        value="<?= old('job_position', $member->job_position ?? '') ?>"
                        placeholder="contoh: Dosen, Staf Administrasi">
                    <?php if (isset($errors['position'])): ?>
                        <div class="invalid-feedback"><?= $errors['position'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="employee_id">Nomor Induk Pegawai</label>
                    <input type="text"
                        class="form-control <?= isset($errors['employee_id']) ? 'is-invalid' : '' ?>"
                        id="employee_id"
                        name="employee_id"
                        value="<?= old('employee_id', $member->employee_id) ?>"
                        maxlength="50">
                    <?php if (isset($errors['employee_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['employee_id'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Nomor induk pegawai dari institusi</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="salary_payer">Pemberi Gaji</label>
                    <select class="form-select <?= isset($errors['salary_payer']) ? 'is-invalid' : '' ?>"
                        id="salary_payer"
                        name="salary_payer">
                        <option value="">Pilih Pemberi Gaji</option>
                        <?php if (!empty($salary_payers)): ?>
                            <?php foreach ($salary_payers as $key => $label): ?>
                                <option value="<?= $key ?>" <?= old('salary_payer', $member->salary_payer ?? '') === $key ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['salary_payer'])): ?>
                        <div class="invalid-feedback"><?= $errors['salary_payer'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Sumber pembayaran gaji</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="salary_range_id">Range Gaji</label>
                    <select class="form-select <?= isset($errors['salary_range_id']) ? 'is-invalid' : '' ?>"
                        id="salary_range_id"
                        name="salary_range_id">
                        <option value="">Pilih Range Gaji</option>
                        <?php if (!empty($salary_ranges)): ?>
                            <?php foreach ($salary_ranges as $range): ?>
                                <option value="<?= $range->id ?>" <?= old('salary_range_id', $member->salary_range_id) == $range->id ? 'selected' : '' ?>>
                                    <?= esc($range->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['salary_range_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['salary_range_id'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Rentang gaji bulanan</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="work_start_date">Tanggal Mulai Bekerja</label>
                    <input type="date"
                        class="form-control <?= isset($errors['work_start_date']) ? 'is-invalid' : '' ?>"
                        id="work_start_date"
                        name="work_start_date"
                        value="<?= old('work_start_date', $member->work_start_date ?? '') ?>">
                    <?php if (isset($errors['work_start_date'])): ?>
                        <div class="invalid-feedback"><?= $errors['work_start_date'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Tanggal mulai bekerja di institusi</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="join_date">Tanggal Bergabung SPK</label>
                    <input type="date"
                        class="form-control <?= isset($errors['join_date']) ? 'is-invalid' : '' ?>"
                        id="join_date"
                        name="join_date"
                        value="<?= old('join_date', $member->join_date) ?>">
                    <?php if (isset($errors['join_date'])): ?>
                        <div class="invalid-feedback"><?= $errors['join_date'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Tanggal bergabung dengan SPK</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="form-card">
        <div class="form-card-header">
            <i class="bi bi-card-text"></i>
            <h3>Informasi Tambahan</h3>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="skills">Keahlian/Kompetensi</label>
                    <textarea class="form-control <?= isset($errors['skills']) ? 'is-invalid' : '' ?>"
                        id="skills"
                        name="skills"
                        rows="4"
                        placeholder="Tuliskan keahlian atau kompetensi yang Anda miliki, misalnya: Pengajaran, Penelitian, Administrasi, dll."><?= old('skills', $member->skills ?? '') ?></textarea>
                    <?php if (isset($errors['skills'])): ?>
                        <div class="invalid-feedback"><?= $errors['skills'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Opsional - Keahlian atau kompetensi khusus</small>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="motivation">Motivasi Bergabung</label>
                    <textarea class="form-control <?= isset($errors['motivation']) ? 'is-invalid' : '' ?>"
                        id="motivation"
                        name="motivation"
                        rows="4"
                        placeholder="Tuliskan motivasi Anda bergabung dengan SPK..."><?= old('motivation', $member->motivation ?? '') ?></textarea>
                    <?php if (isset($errors['motivation'])): ?>
                        <div class="invalid-feedback"><?= $errors['motivation'] ?></div>
                    <?php endif; ?>
                    <small class="form-text">Opsional - Alasan bergabung dengan Serikat Pekerja Kampus</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-card">
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                Simpan Perubahan
            </button>
            <a href="<?= base_url('member/profile') ?>" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i>
                Batal
            </a>
        </div>
    </div>
</form>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-3 mb-0">Menyimpan perubahan...</p>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Photo preview
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Dynamic regencies based on province
    document.getElementById('province_id').addEventListener('change', function() {
        const provinceId = this.value;
        const regencySelect = document.getElementById('regency_id');

        if (!provinceId) {
            regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            return;
        }

        // Fetch regencies
        fetch(`<?= base_url('api/regencies/') ?>${provinceId}`)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Pilih Kabupaten/Kota</option>';
                data.forEach(regency => {
                    options += `<option value="${regency.id}">${regency.name}</option>`;
                });
                regencySelect.innerHTML = options;
            })
            .catch(error => {
                console.error('Error fetching regencies:', error);
            });
    });

    // Dynamic study programs based on university
    document.getElementById('university_id').addEventListener('change', function() {
        const universityId = this.value;
        const programSelect = document.getElementById('study_program_id');

        if (!universityId) {
            programSelect.innerHTML = '<option value="">Pilih Program Studi</option>';
            return;
        }

        // Fetch study programs
        fetch(`<?= base_url('api/study-programs/') ?>${universityId}`)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Pilih Program Studi</option>';
                data.forEach(program => {
                    options += `<option value="${program.id}">${program.name}</option>`;
                });
                programSelect.innerHTML = options;
            })
            .catch(error => {
                console.error('Error fetching study programs:', error);
            });
    });

    // Form submission with loading
    document.getElementById('profileForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.add('show');
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.form-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>
<?= $this->endSection() ?>