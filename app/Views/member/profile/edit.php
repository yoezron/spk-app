<?php

/**
 * View: Member Profile Edit - Enhanced Version
 * Controller: Member\ProfileController
 * Description: Form edit profil anggota dengan UI/UX modern
 *
 * Features:
 * - Modern step-by-step form layout
 * - Real-time validation
 * - Photo upload with drag & drop
 * - Dynamic dropdowns with better UX
 * - Character counters
 * - Smooth animations
 * - Mobile-optimized
 * - Unsaved changes warning
 *
 * @package App\Views\Member\Profile
 * @author  SPK Development Team
 * @version 2.0.0 - Enhanced
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --primary-color: #667eea;
        --secondary-color: #764ba2;
        --text-dark: #2d3748;
        --text-light: #718096;
        --border-color: #e2e8f0;
        --bg-light: #f7fafc;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    /* Page Container */
    .profile-edit-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Enhanced Page Header with Stats */
    .page-header {
        background: var(--primary-gradient);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 15s infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        33% { transform: translate(30px, -30px) rotate(120deg); }
        66% { transform: translate(-20px, 20px) rotate(240deg); }
    }

    .page-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
    }

    .page-header-info h1 {
        font-size: 32px;
        font-weight: 800;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-header-info p {
        opacity: 0.95;
        font-size: 16px;
        margin: 0;
    }

    .header-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    /* Progress Steps Indicator */
    .progress-steps {
        background: white;
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-md);
    }

    .steps-container {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .steps-container::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 50px;
        right: 50px;
        height: 3px;
        background: var(--border-color);
        z-index: 0;
    }

    .step-item {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-weight: 700;
        color: var(--text-light);
        transition: all 0.3s ease;
        position: relative;
    }

    .step-item.active .step-circle,
    .step-item.completed .step-circle {
        background: var(--primary-gradient);
        border-color: var(--primary-color);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .step-item.completed .step-circle::after {
        content: '✓';
        position: absolute;
        font-size: 20px;
    }

    .step-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-light);
        transition: color 0.3s ease;
    }

    .step-item.active .step-label,
    .step-item.completed .step-label {
        color: var(--primary-color);
    }

    /* Enhanced Form Card */
    .form-section {
        background: white;
        border-radius: 16px;
        padding: 40px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-section:hover {
        box-shadow: var(--shadow-lg);
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 35px;
        padding-bottom: 20px;
        border-bottom: 3px solid transparent;
        border-image: var(--primary-gradient) 1;
        border-image-slice: 1;
    }

    .section-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .section-info h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 5px 0;
    }

    .section-info p {
        font-size: 14px;
        color: var(--text-light);
        margin: 0;
    }

    /* Enhanced Photo Upload */
    .photo-upload-zone {
        display: flex;
        align-items: center;
        gap: 40px;
        padding: 40px;
        background: linear-gradient(135deg, #f6f8fb 0%, #f0f4f8 100%);
        border-radius: 16px;
        border: 2px dashed #cbd5e0;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .photo-upload-zone:hover {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, #eef2f7 0%, #e8ecf1 100%);
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
    }

    .photo-upload-zone.drag-over {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, #e6f0ff 0%, #d9e7ff 100%);
        transform: scale(1.02);
    }

    .photo-preview-container {
        position: relative;
    }

    .photo-preview-wrapper {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        overflow: hidden;
        position: relative;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        border: 5px solid white;
    }

    .photo-preview-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .photo-preview-wrapper:hover img {
        transform: scale(1.1);
    }

    .photo-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .photo-badge:hover {
        transform: scale(1.15);
    }

    .photo-upload-content {
        flex: 1;
    }

    .photo-upload-content h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 10px 0;
    }

    .photo-upload-content p {
        color: var(--text-light);
        margin: 0 0 20px 0;
        line-height: 1.6;
    }

    .upload-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-upload {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 28px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-remove-photo {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 28px;
        background: #fff;
        color: #ef4444;
        border: 2px solid #ef4444;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-remove-photo:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
    }

    /* Enhanced Form Groups */
    .form-group {
        margin-bottom: 28px;
        animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
        font-size: 15px;
    }

    .form-label .required {
        color: #ef4444;
        font-size: 18px;
    }

    .form-label .optional-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        background: #e0e7ff;
        color: #6366f1;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .field-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        font-size: 18px;
        transition: color 0.3s ease;
    }

    .form-control,
    .form-select {
        width: 100%;
        height: 52px;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 0 16px;
        font-size: 15px;
        color: var(--text-dark);
        background: white;
        transition: all 0.3s ease;
        outline: none;
    }

    .has-icon .form-control,
    .has-icon .form-select {
        padding-left: 48px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-control:hover,
    .form-select:hover {
        border-color: #cbd5e0;
    }

    .form-control.is-valid,
    .form-select.is-valid {
        border-color: #10b981;
        background-color: #f0fdf4;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #ef4444;
        background-color: #fef2f2;
        animation: shake 0.4s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    textarea.form-control {
        height: auto;
        min-height: 120px;
        padding: 16px;
        resize: vertical;
    }

    /* Character Counter */
    .char-counter {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
        font-size: 13px;
        color: var(--text-light);
    }

    .char-counter.warning {
        color: #f59e0b;
    }

    .char-counter.danger {
        color: #ef4444;
    }

    /* Feedback Messages */
    .form-text {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        font-size: 13px;
        color: var(--text-light);
    }

    .invalid-feedback {
        display: none;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: 13px;
        color: #ef4444;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .form-control.is-invalid + .invalid-feedback,
    .form-select.is-invalid + .invalid-feedback {
        display: flex;
    }

    .valid-feedback {
        display: none;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: 13px;
        color: #10b981;
    }

    .form-control.is-valid + .valid-feedback,
    .form-select.is-valid + .valid-feedback {
        display: flex;
    }

    /* Alert Info Box */
    .info-alert {
        background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
        border-left: 4px solid #3b82f6;
        padding: 20px 24px;
        border-radius: 12px;
        margin-bottom: 30px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        box-shadow: var(--shadow-sm);
    }

    .info-alert i {
        font-size: 24px;
        color: #3b82f6;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .info-alert p {
        margin: 0;
        color: #1e40af;
        line-height: 1.6;
        font-size: 14px;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 16px;
        padding-top: 40px;
        margin-top: 40px;
        border-top: 2px solid var(--border-color);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 40px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: white;
        color: var(--text-dark);
        border: 2px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--bg-light);
        border-color: #cbd5e0;
        transform: translateY(-2px);
    }

    /* Loading Overlay */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-content {
        background: white;
        padding: 50px 60px;
        border-radius: 20px;
        text-align: center;
        box-shadow: var(--shadow-lg);
        animation: scaleIn 0.3s ease;
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .spinner {
        width: 60px;
        height: 60px;
        border: 4px solid var(--border-color);
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            padding: 30px 24px;
        }

        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header-info h1 {
            font-size: 24px;
        }

        .progress-steps {
            padding: 20px;
        }

        .steps-container {
            flex-wrap: wrap;
            gap: 20px;
        }

        .step-item {
            flex: 1 1 50%;
        }

        .form-section {
            padding: 24px;
        }

        .photo-upload-zone {
            flex-direction: column;
            text-align: center;
            padding: 30px 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .page-header-info h1 {
            font-size: 20px;
        }

        .step-item {
            flex: 1 1 100%;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="profile-edit-container">
    <!-- Enhanced Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-info">
                <h1>
                    <i class="bi bi-person-circle"></i>
                    Edit Profil Saya
                </h1>
                <p>Perbarui informasi profil Anda dan pastikan data selalu akurat</p>
            </div>
            <div class="header-badge">
                <i class="bi bi-shield-check"></i>
                Data Terenkripsi
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Info Alert -->
    <div class="info-alert">
        <i class="bi bi-info-circle-fill"></i>
        <p>
            <strong>Tips:</strong> Pastikan semua data yang Anda masukkan adalah benar dan sesuai dengan dokumen resmi.
            Field yang ditandai dengan <span class="required">*</span> wajib diisi. Data dapat diubah kembali kapan saja.
        </p>
    </div>

    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="steps-container">
            <div class="step-item active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Foto & Pribadi</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Kontak</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Pekerjaan</div>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Tambahan</div>
            </div>
        </div>
    </div>

    <form action="<?= base_url('member/profile/update') ?>" method="POST" enctype="multipart/form-data" id="profileForm">
        <?= csrf_field() ?>

        <!-- Section 1: Photo Upload -->
        <div class="form-section" data-section="1">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div class="section-info">
                    <h2>Foto Profil</h2>
                    <p>Upload foto terbaik Anda untuk profil</p>
                </div>
            </div>

            <div class="photo-upload-zone" id="photoDropZone">
                <div class="photo-preview-container">
                    <div class="photo-preview-wrapper">
                        <img src="<?= !empty($member->photo_path) ? base_url(esc($member->photo_path)) : base_url('assets/images/default-avatar.png') ?>"
                            alt="Preview"
                            id="photoPreview">
                    </div>
                    <div class="photo-badge" onclick="document.getElementById('photo').click()">
                        <i class="bi bi-camera"></i>
                    </div>
                </div>
                <div class="photo-upload-content">
                    <h3>Upload atau Drag & Drop</h3>
                    <p>
                        <i class="bi bi-check-circle text-success"></i> Format: JPG, PNG • Maksimal 2MB<br>
                        <i class="bi bi-check-circle text-success"></i> Ukuran disarankan: 500x500 pixels untuk hasil terbaik
                    </p>
                    <div class="upload-actions">
                        <label for="photo" class="btn-upload">
                            <i class="bi bi-cloud-upload"></i>
                            Pilih Foto
                        </label>
                        <button type="button" class="btn-remove-photo" id="removePhoto" style="display: none;">
                            <i class="bi bi-trash"></i>
                            Hapus Foto
                        </button>
                        <input type="file" id="photo" name="photo" accept="image/*" style="display: none;">
                    </div>
                    <small class="form-text">
                        <i class="bi bi-lightbulb"></i> Biarkan kosong jika tidak ingin mengubah foto
                    </small>
                </div>
            </div>
        </div>

        <!-- Section 2: Personal Information -->
        <div class="form-section" data-section="1">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="section-info">
                    <h2>Informasi Pribadi</h2>
                    <p>Data identitas diri Anda</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-person"></i>
                            Nama Lengkap
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-person-fill"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                                id="full_name"
                                name="full_name"
                                value="<?= old('full_name', $member->full_name) ?>"
                                placeholder="Masukkan nama lengkap sesuai KTP"
                                required>
                        </div>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['full_name'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-credit-card"></i>
                            NIK
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-credit-card-fill"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['nik']) ? 'is-invalid' : '' ?>"
                                id="nik"
                                name="nik"
                                value="<?= old('nik', $member->nik) ?>"
                                maxlength="16"
                                placeholder="16 digit NIK KTP"
                                required>
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            16 digit NIK sesuai KTP
                        </small>
                        <?php if (isset($errors['nik'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['nik'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-card-heading"></i>
                            NIDN/NIP
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-card-heading"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['nidn_nip']) ? 'is-invalid' : '' ?>"
                                id="nidn_nip"
                                name="nidn_nip"
                                value="<?= old('nidn_nip', $member->nidn_nip) ?>"
                                maxlength="30"
                                placeholder="NIDN untuk Dosen atau NIP">
                        </div>
                        <small class="form-text">
                            <i class="bi bi-lightbulb"></i>
                            NIDN untuk Dosen atau NIP untuk Pegawai
                        </small>
                        <?php if (isset($errors['nidn_nip'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['nidn_nip'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-pin-map"></i>
                            Tempat Lahir
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-geo-alt-fill"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['birth_place']) ? 'is-invalid' : '' ?>"
                                id="birth_place"
                                name="birth_place"
                                value="<?= old('birth_place', $member->birth_place) ?>"
                                placeholder="Kota/Kabupaten kelahiran"
                                required>
                        </div>
                        <?php if (isset($errors['birth_place'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['birth_place'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-calendar"></i>
                            Tanggal Lahir
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-calendar-event"></i>
                            <input type="date"
                                class="form-control <?= isset($errors['birth_date']) ? 'is-invalid' : '' ?>"
                                id="birth_date"
                                name="birth_date"
                                value="<?= old('birth_date', $member->birth_date) ?>"
                                required>
                        </div>
                        <?php if (isset($errors['birth_date'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['birth_date'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-gender-ambiguous"></i>
                            Jenis Kelamin
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-gender-ambiguous"></i>
                            <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>"
                                id="gender"
                                name="gender"
                                required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" <?= old('gender', $member->gender) === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= old('gender', $member->gender) === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <?php if (isset($errors['gender'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['gender'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-peace"></i>
                            Agama
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-peace"></i>
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
                        </div>
                        <?php if (isset($errors['religion'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['religion'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-heart"></i>
                            Status Pernikahan
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-heart-fill"></i>
                            <select class="form-select <?= isset($errors['marital_status']) ? 'is-invalid' : '' ?>"
                                id="marital_status"
                                name="marital_status">
                                <option value="">Pilih Status</option>
                                <option value="Belum Menikah" <?= old('marital_status', $member->marital_status) === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                                <option value="Menikah" <?= old('marital_status', $member->marital_status) === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                                <option value="Cerai" <?= old('marital_status', $member->marital_status) === 'Cerai' ? 'selected' : '' ?>>Cerai</option>
                            </select>
                        </div>
                        <?php if (isset($errors['marital_status'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['marital_status'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Contact Information -->
        <div class="form-section" data-section="2">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <div class="section-info">
                    <h2>Informasi Kontak</h2>
                    <p>Alamat dan cara menghubungi Anda</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-phone"></i>
                            No. Telepon
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-phone-fill"></i>
                            <input type="tel"
                                class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                id="phone"
                                name="phone"
                                value="<?= old('phone', $member->phone) ?>"
                                placeholder="08123456789"
                                required>
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['phone'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-whatsapp"></i>
                            No. WhatsApp
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-whatsapp"></i>
                            <input type="tel"
                                class="form-control <?= isset($errors['whatsapp']) ? 'is-invalid' : '' ?>"
                                id="whatsapp"
                                name="whatsapp"
                                value="<?= old('whatsapp', $member->whatsapp) ?>"
                                placeholder="08123456789"
                                required>
                        </div>
                        <?php if (isset($errors['whatsapp'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['whatsapp'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i>
                            Email
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-envelope-fill"></i>
                            <input type="email"
                                class="form-control"
                                id="email"
                                value="<?= esc($user->email) ?>"
                                disabled>
                        </div>
                        <small class="form-text">
                            <i class="bi bi-lock"></i>
                            Email tidak dapat diubah untuk keamanan akun
                        </small>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-house"></i>
                            Alamat Lengkap
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative">
                            <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                id="address"
                                name="address"
                                rows="4"
                                placeholder="Masukkan alamat lengkap termasuk RT/RW, Kelurahan/Desa, Kecamatan"
                                maxlength="500"
                                required><?= old('address', $member->address) ?></textarea>
                        </div>
                        <div class="char-counter">
                            <span>
                                <i class="bi bi-text-paragraph"></i>
                                Karakter yang tersisa
                            </span>
                            <span id="addressCounter">500</span>
                        </div>
                        <?php if (isset($errors['address'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['address'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-globe"></i>
                            Provinsi
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-globe"></i>
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
                        </div>
                        <?php if (isset($errors['province_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['province_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-pin-map"></i>
                            Kabupaten/Kota
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-pin-map-fill"></i>
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
                        </div>
                        <?php if (isset($errors['regency_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['regency_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-mailbox"></i>
                            Kode Pos
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-mailbox"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['postal_code']) ? 'is-invalid' : '' ?>"
                                id="postal_code"
                                name="postal_code"
                                value="<?= old('postal_code', $member->postal_code) ?>"
                                maxlength="5"
                                placeholder="5 digit kode pos">
                        </div>
                        <?php if (isset($errors['postal_code'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['postal_code'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Employment Information -->
        <div class="form-section" data-section="3">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="section-info">
                    <h2>Informasi Pekerjaan</h2>
                    <p>Data institusi dan kepegawaian Anda</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-bank"></i>
                            Kampus
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-bank"></i>
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
                        </div>
                        <?php if (isset($errors['university_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['university_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-book"></i>
                            Program Studi
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-book-fill"></i>
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
                        </div>
                        <small class="form-text">
                            <i class="bi bi-lightbulb"></i>
                            Akan otomatis terisi setelah memilih kampus
                        </small>
                        <?php if (isset($errors['study_program_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['study_program_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-briefcase"></i>
                            Jenis Kepegawaian
                            <span class="required">*</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-briefcase-fill"></i>
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
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Jenis pegawai (Dosen/Tendik)
                        </small>
                        <?php if (isset($errors['employment_type'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['employment_type'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-award"></i>
                            Status Kepegawaian
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-award-fill"></i>
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
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            PNS, Non-PNS, Kontrak, dll
                        </small>
                        <?php if (isset($errors['employment_status_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['employment_status_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-person-badge"></i>
                            Jabatan
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-person-badge-fill"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['position']) ? 'is-invalid' : '' ?>"
                                id="position"
                                name="job_position"
                                value="<?= old('job_position', $member->job_position ?? '') ?>"
                                placeholder="contoh: Dosen, Staf Administrasi">
                        </div>
                        <?php if (isset($errors['position'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['position'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-hash"></i>
                            Nomor Induk Pegawai
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-hash"></i>
                            <input type="text"
                                class="form-control <?= isset($errors['employee_id']) ? 'is-invalid' : '' ?>"
                                id="employee_id"
                                name="employee_id"
                                value="<?= old('employee_id', $member->employee_id) ?>"
                                maxlength="50"
                                placeholder="Nomor induk pegawai">
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Nomor induk pegawai dari institusi
                        </small>
                        <?php if (isset($errors['employee_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['employee_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-wallet"></i>
                            Pemberi Gaji
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-wallet-fill"></i>
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
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Sumber pembayaran gaji
                        </small>
                        <?php if (isset($errors['salary_payer'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['salary_payer'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-cash-stack"></i>
                            Range Gaji
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-cash-stack"></i>
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
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Rentang gaji bulanan
                        </small>
                        <?php if (isset($errors['salary_range_id'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['salary_range_id'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-calendar-check"></i>
                            Tanggal Mulai Bekerja
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-calendar-check-fill"></i>
                            <input type="date"
                                class="form-control <?= isset($errors['work_start_date']) ? 'is-invalid' : '' ?>"
                                id="work_start_date"
                                name="work_start_date"
                                value="<?= old('work_start_date', $member->work_start_date ?? '') ?>">
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Tanggal mulai bekerja di institusi
                        </small>
                        <?php if (isset($errors['work_start_date'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['work_start_date'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-star"></i>
                            Tanggal Bergabung SPK
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative has-icon">
                            <i class="field-icon bi bi-star-fill"></i>
                            <input type="date"
                                class="form-control <?= isset($errors['join_date']) ? 'is-invalid' : '' ?>"
                                id="join_date"
                                name="join_date"
                                value="<?= old('join_date', $member->join_date) ?>">
                        </div>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Tanggal bergabung dengan Serikat Pekerja Kampus
                        </small>
                        <?php if (isset($errors['join_date'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['join_date'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Additional Information -->
        <div class="form-section" data-section="4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-file-text"></i>
                </div>
                <div class="section-info">
                    <h2>Informasi Tambahan</h2>
                    <p>Keahlian dan motivasi bergabung</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-lightbulb"></i>
                            Keahlian/Kompetensi
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative">
                            <textarea class="form-control <?= isset($errors['skills']) ? 'is-invalid' : '' ?>"
                                id="skills"
                                name="skills"
                                rows="4"
                                placeholder="Tuliskan keahlian atau kompetensi yang Anda miliki, misalnya: Pengajaran, Penelitian, Administrasi, dll."
                                maxlength="1000"><?= old('skills', $member->skills ?? '') ?></textarea>
                        </div>
                        <div class="char-counter">
                            <span>
                                <i class="bi bi-text-paragraph"></i>
                                Karakter yang tersisa
                            </span>
                            <span id="skillsCounter">1000</span>
                        </div>
                        <small class="form-text">
                            <i class="bi bi-lightbulb"></i>
                            Keahlian atau kompetensi khusus yang Anda miliki
                        </small>
                        <?php if (isset($errors['skills'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['skills'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-heart-pulse"></i>
                            Motivasi Bergabung
                            <span class="optional-badge">Opsional</span>
                        </label>
                        <div class="position-relative">
                            <textarea class="form-control <?= isset($errors['motivation']) ? 'is-invalid' : '' ?>"
                                id="motivation"
                                name="motivation"
                                rows="4"
                                placeholder="Tuliskan motivasi Anda bergabung dengan Serikat Pekerja Kampus..."
                                maxlength="1000"><?= old('motivation', $member->motivation ?? '') ?></textarea>
                        </div>
                        <div class="char-counter">
                            <span>
                                <i class="bi bi-text-paragraph"></i>
                                Karakter yang tersisa
                            </span>
                            <span id="motivationCounter">1000</span>
                        </div>
                        <small class="form-text">
                            <i class="bi bi-heart-pulse"></i>
                            Alasan dan harapan Anda bergabung dengan SPK
                        </small>
                        <?php if (isset($errors['motivation'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['motivation'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Simpan Perubahan
                </button>
                <a href="<?= base_url('member/profile') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <p class="loading-text">Menyimpan perubahan...</p>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Photo Preview & Drag-Drop
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photoPreview');
const photoDropZone = document.getElementById('photoDropZone');
const removePhotoBtn = document.getElementById('removePhoto');
let hasNewPhoto = false;

// Photo upload via input
photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        handlePhotoUpload(file);
    }
});

// Drag & Drop handlers
photoDropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    photoDropZone.classList.add('drag-over');
});

photoDropZone.addEventListener('dragleave', () => {
    photoDropZone.classList.remove('drag-over');
});

photoDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    photoDropZone.classList.remove('drag-over');

    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        photoInput.files = e.dataTransfer.files;
        handlePhotoUpload(file);
    } else {
        alert('File harus berupa gambar (JPG, PNG)');
    }
});

function handlePhotoUpload(file) {
    // Validate file size
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB');
        return;
    }

    // Validate file type
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
        alert('Format file harus JPG atau PNG');
        return;
    }

    // Preview
    const reader = new FileReader();
    reader.onload = function(e) {
        photoPreview.src = e.target.result;
        hasNewPhoto = true;
        removePhotoBtn.style.display = 'inline-flex';
    }
    reader.readAsDataURL(file);
}

// Remove photo
removePhotoBtn.addEventListener('click', function() {
    photoInput.value = '';
    photoPreview.src = '<?= base_url('assets/images/default-avatar.png') ?>';
    hasNewPhoto = false;
    removePhotoBtn.style.display = 'none';
});

// Dynamic regencies based on province
document.getElementById('province_id').addEventListener('change', function() {
    const provinceId = this.value;
    const regencySelect = document.getElementById('regency_id');

    if (!provinceId) {
        regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
        return;
    }

    // Show loading
    regencySelect.innerHTML = '<option value="">Loading...</option>';
    regencySelect.disabled = true;

    // Fetch regencies
    fetch(`<?= base_url('api/regencies/') ?>${provinceId}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Pilih Kabupaten/Kota</option>';
            data.forEach(regency => {
                options += `<option value="${regency.id}">${regency.name}</option>`;
            });
            regencySelect.innerHTML = options;
            regencySelect.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching regencies:', error);
            regencySelect.innerHTML = '<option value="">Error loading data</option>';
            regencySelect.disabled = false;
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

    // Show loading
    programSelect.innerHTML = '<option value="">Loading...</option>';
    programSelect.disabled = true;

    // Fetch study programs
    fetch(`<?= base_url('api/study-programs/') ?>${universityId}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Pilih Program Studi</option>';
            data.forEach(program => {
                options += `<option value="${program.id}">${program.name}</option>`;
            });
            programSelect.innerHTML = options;
            programSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching study programs:', error);
            programSelect.innerHTML = '<option value="">Error loading data</option>';
            programSelect.disabled = false;
        });
});

// Character counters
function setupCharCounter(textareaId, counterId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);

    if (textarea && counter) {
        textarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.textContent = remaining;

            // Update counter styling
            const counterParent = counter.parentElement;
            counterParent.classList.remove('warning', 'danger');

            if (remaining < 100) {
                counterParent.classList.add('warning');
            }
            if (remaining < 50) {
                counterParent.classList.add('danger');
            }
        });

        // Initialize
        const remaining = maxLength - textarea.value.length;
        counter.textContent = remaining;
    }
}

setupCharCounter('address', 'addressCounter', 500);
setupCharCounter('skills', 'skillsCounter', 1000);
setupCharCounter('motivation', 'motivationCounter', 1000);

// Progress steps tracking
const sections = document.querySelectorAll('.form-section[data-section]');
const stepItems = document.querySelectorAll('.step-item');

// Intersection Observer untuk tracking section yang visible
const observerOptions = {
    threshold: 0.5,
    rootMargin: '-100px 0px -100px 0px'
};

const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const sectionNum = entry.target.dataset.section;
            updateActiveStep(sectionNum);
        }
    });
}, observerOptions);

sections.forEach(section => {
    sectionObserver.observe(section);
});

function updateActiveStep(stepNum) {
    stepItems.forEach((item, index) => {
        const itemNum = item.dataset.step;
        item.classList.remove('active', 'completed');

        if (itemNum < stepNum) {
            item.classList.add('completed');
        } else if (itemNum === stepNum) {
            item.classList.add('active');
        }
    });
}

// Form submission with loading
document.getElementById('profileForm').addEventListener('submit', function(e) {
    document.getElementById('loadingOverlay').classList.add('show');
});

// Unsaved changes warning
let formChanged = false;
const formElements = document.querySelectorAll('#profileForm input, #profileForm select, #profileForm textarea');

formElements.forEach(element => {
    element.addEventListener('change', () => {
        formChanged = true;
    });
});

window.addEventListener('beforeunload', (e) => {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Reset flag on submit
document.getElementById('profileForm').addEventListener('submit', () => {
    formChanged = false;
});

// Smooth scroll animation on load
document.querySelectorAll('.form-section').forEach((section, index) => {
    section.style.animationDelay = `${index * 0.1}s`;
});
</script>
<?= $this->endSection() ?>
