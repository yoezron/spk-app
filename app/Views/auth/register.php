<?php

/**
 * View: Register
 * Controller: Auth\RegisterController
 * Description: Halaman registrasi anggota baru SPK
 * * Features:
 * - Multi-section form (Data Pribadi, Kepegawaian, Kampus, Pembayaran)
 * - Dynamic dropdowns (cascade: provinsi, kampus, prodi)
 * - File upload (foto & bukti bayar)
 * - Password strength indicator
 * - Validation error display
 * - CSRF protection
 * * @package App\Views\Auth
 * @author  SPK Development Team
 * @version 2.1.0 (Fixed header overflow issue)
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('styles') ?>
<style>
    /* Wider auth card for register form */
    .auth-container {
        max-width: 750px;
    }

    /* Stepper Wizard */
    .stepper-wrapper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
        position: relative;
    }

    .stepper-line {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #e0e0e0;
        transform: translateY(-50%);
        z-index: 1;
    }

    .stepper-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        background-color: #fff;
        /* Match auth card background */
        padding: 0 8px;
    }

    .step-counter {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        background: #e0e0e0;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        transition: all 0.3s ease;
        border: 3px solid #e0e0e0;
    }

    .stepper-item.active .step-counter {
        background: #0d6efd;
        /* Bootstrap primary */
        border-color: #0d6efd;
    }

    .stepper-item.completed .step-counter {
        background: #198754;
        /* Bootstrap success */
        border-color: #198754;
    }

    .step-name {
        margin-top: 8px;
        font-size: 12px;
        font-weight: 500;
        color: #6c757d;
    }

    .stepper-item.active .step-name {
        color: #0d6efd;
    }

    .stepper-item.completed .step-name {
        color: #198754;
    }

    /* Form Steps */
    .form-step {
        display: none;
        animation: fadeIn 0.5s;
    }

    .form-step.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Navigation Buttons */
    .step-navigation {
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
    }

    /* Section Headers */
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        /* KUNCI PERBAIKAN: Mengubah margin-top dari -40px menjadi 0 */
        margin: 0 -30px 30px -30px;
        font-weight: 600;
        font-size: 16px;
        border-radius: 16px 16px 0 0;
        text-align: center;
    }

    .section-header i {
        margin-right: 8px;
        vertical-align: middle;
    }

    /* File upload preview */
    .file-preview {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        display: none;
    }

    .file-preview.show {
        display: block;
    }

    .file-preview img {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
    }

    /* Password strength indicator */
    .password-strength {
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        margin-top: 8px;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
    }

    .password-strength-bar.weak {
        width: 33%;
        background: #dc3545;
    }

    .password-strength-bar.medium {
        width: 66%;
        background: #ffc107;
    }

    .password-strength-bar.strong {
        width: 100%;
        background: #28a745;
    }

    /* Helper text */
    .form-text {
        font-size: 12px;
        color: #6c757d;
    }

    .required-note {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 20px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="section-header">
    <i class="material-icons-outlined">person_add</i>
    Formulir Pendaftaran Anggota Baru
</div>

<div class="stepper-wrapper">
    <div class="stepper-line"></div>
    <div class="stepper-item active" data-step="1">
        <div class="step-counter">1</div>
        <div class="step-name">Pribadi</div>
    </div>
    <div class="stepper-item" data-step="2">
        <div class="step-counter">2</div>
        <div class="step-name">Kepegawaian</div>
    </div>
    <div class="stepper-item" data-step="3">
        <div class="step-counter">3</div>
        <div class="step-name">Kampus</div>
    </div>
    <div class="stepper-item" data-step="4">
        <div class="step-counter">4</div>
        <div class="step-name">Pembayaran</div>
    </div>
</div>

<div class="required-note">
    <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">info</i>
    <span class="text-danger">*</span> wajib diisi
</div>

<form action="<?= base_url('auth/register') ?>" method="POST" enctype="multipart/form-data" id="registerForm">
    <?= csrf_field() ?>

    <div class="form-step active" data-step="1">
        <h5 class="mb-3">
            <i class="material-icons-outlined" style="font-size: 20px; vertical-align: middle; color: #6c757d;">badge</i>
            Data Pribadi
        </h5>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= session('errors.nama_lengkap') ? 'is-invalid' : '' ?>" id="nama_lengkap" name="nama_lengkap" value="<?= old('nama_lengkap') ?>" placeholder="Masukkan nama lengkap" required>
                <?php if (session('errors.nama_lengkap')): ?><div class="invalid-feedback"><?= session('errors.nama_lengkap') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email') ?>" placeholder="contoh@email.com" required>
                <div class="form-text">Gunakan email aktif untuk verifikasi</div>
                <?php if (session('errors.email')): ?><div class="invalid-feedback"><?= session('errors.email') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="no_wa" class="form-label">No. WhatsApp <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= session('errors.no_wa') ? 'is-invalid' : '' ?>" id="no_wa" name="no_wa" value="<?= old('no_wa') ?>" placeholder="08xxxxxxxxxx" required>
                <?php if (session('errors.no_wa')): ?><div class="invalid-feedback"><?= session('errors.no_wa') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Min. 8 karakter" required>
                <div class="password-strength">
                    <div class="password-strength-bar"></div>
                </div>
                <div class="form-text" id="passwordHelp">Minimal 8 karakter, kombinasi huruf dan angka</div>
                <?php if (session('errors.password')): ?><div class="invalid-feedback"><?= session('errors.password') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirm" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control <?= session('errors.password_confirm') ? 'is-invalid' : '' ?>" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                <?php if (session('errors.password_confirm')): ?><div class="invalid-feedback"><?= session('errors.password_confirm') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" <?= old('jenis_kelamin') === 'L' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="jk_l">Laki-laki</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" <?= old('jenis_kelamin') === 'P' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="jk_p">Perempuan</label>
                    </div>
                </div>
                <?php if (session('errors.jenis_kelamin')): ?><div class="text-danger small"><?= session('errors.jenis_kelamin') ?></div><?php endif; ?>
            </div>
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea class="form-control <?= session('errors.alamat') ? 'is-invalid' : '' ?>" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap" required><?= old('alamat') ?></textarea>
                <?php if (session('errors.alamat')): ?><div class="invalid-feedback"><?= session('errors.alamat') ?></div><?php endif; ?>
            </div>
            <div class="col-md-12 mb-3">
                <label for="foto" class="form-label">Foto <span class="text-danger">*</span></label>
                <input type="file" class="form-control <?= session('errors.foto') ? 'is-invalid' : '' ?>" id="foto" name="foto" accept="image/jpeg,image/jpg,image/png" required>
                <div class="form-text">Format: JPG, PNG. Maksimal 2MB</div>
                <div class="file-preview" id="fotoPreview"><img src="" alt="Preview Foto" id="fotoPreviewImg"></div>
                <?php if (session('errors.foto')): ?><div class="invalid-feedback"><?= session('errors.foto') ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-step" data-step="2">
        <h5 class="mb-3">
            <i class="material-icons-outlined" style="font-size: 20px; vertical-align: middle; color: #6c757d;">work</i>
            Data Kepegawaian
        </h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="status_kepegawaian_id" class="form-label">Status Kepegawaian <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.status_kepegawaian_id') ? 'is-invalid' : '' ?>" id="status_kepegawaian_id" name="status_kepegawaian_id" required>
                    <option value="">-- Pilih Status --</option>
                    <?php if (isset($status_kepegawaian)) foreach ($status_kepegawaian as $status): ?>
                        <option value="<?= $status->id ?>" <?= old('status_kepegawaian_id') == $status->id ? 'selected' : '' ?>><?= esc($status->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.status_kepegawaian_id')): ?><div class="invalid-feedback"><?= session('errors.status_kepegawaian_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="nidn_nip" class="form-label">NIDN/NIP</label>
                <input type="text" class="form-control <?= session('errors.nidn_nip') ? 'is-invalid' : '' ?>" id="nidn_nip" name="nidn_nip" value="<?= old('nidn_nip') ?>" placeholder="Opsional">
                <?php if (session('errors.nidn_nip')): ?><div class="invalid-feedback"><?= session('errors.nidn_nip') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="pemberi_gaji_id" class="form-label">Pemberi Gaji <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.pemberi_gaji_id') ? 'is-invalid' : '' ?>" id="pemberi_gaji_id" name="pemberi_gaji_id" required>
                    <option value="">-- Pilih Pemberi Gaji --</option>
                    <?php if (isset($pemberi_gaji)) foreach ($pemberi_gaji as $pemberi): ?>
                        <option value="<?= $pemberi->id ?>" <?= old('pemberi_gaji_id') == $pemberi->id ? 'selected' : '' ?>><?= esc($pemberi->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.pemberi_gaji_id')): ?><div class="invalid-feedback"><?= session('errors.pemberi_gaji_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="range_gaji_id" class="form-label">Range Gaji <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.range_gaji_id') ? 'is-invalid' : '' ?>" id="range_gaji_id" name="range_gaji_id" required>
                    <option value="">-- Pilih Range Gaji --</option>
                    <?php if (isset($range_gaji)) foreach ($range_gaji as $range): ?>
                        <option value="<?= $range->id ?>" <?= old('range_gaji_id') == $range->id ? 'selected' : '' ?>><?= esc($range->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.range_gaji_id')): ?><div class="invalid-feedback"><?= session('errors.range_gaji_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-12 mb-3">
                <label for="gaji_pokok" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                <input type="number" class="form-control <?= session('errors.gaji_pokok') ? 'is-invalid' : '' ?>" id="gaji_pokok" name="gaji_pokok" value="<?= old('gaji_pokok') ?>" placeholder="Contoh: 5000000" required>
                <div class="form-text">Isi dengan angka tanpa titik atau koma</div>
                <?php if (session('errors.gaji_pokok')): ?><div class="invalid-feedback"><?= session('errors.gaji_pokok') ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-step" data-step="3">
        <h5 class="mb-3">
            <i class="material-icons-outlined" style="font-size: 20px; vertical-align: middle; color: #6c757d;">school</i>
            Data Kampus
        </h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.provinsi_id') ? 'is-invalid' : '' ?>" id="provinsi_id" name="provinsi_id" required>
                    <option value="">-- Pilih Provinsi --</option>
                    <?php if (isset($provinsi)) foreach ($provinsi as $prov): ?>
                        <option value="<?= $prov->id ?>" <?= old('provinsi_id') == $prov->id ? 'selected' : '' ?>><?= esc($prov->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.provinsi_id')): ?><div class="invalid-feedback"><?= session('errors.provinsi_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="jenis_pt_id" class="form-label">Jenis Perguruan Tinggi <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.jenis_pt_id') ? 'is-invalid' : '' ?>" id="jenis_pt_id" name="jenis_pt_id" required>
                    <option value="">-- Pilih Jenis PT --</option>
                    <?php if (isset($jenis_pt)) foreach ($jenis_pt as $jenis): ?>
                        <option value="<?= $jenis->id ?>" <?= old('jenis_pt_id') == $jenis->id ? 'selected' : '' ?>><?= esc($jenis->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.jenis_pt_id')): ?><div class="invalid-feedback"><?= session('errors.jenis_pt_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="kampus_id" class="form-label">Kampus <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.kampus_id') ? 'is-invalid' : '' ?>" id="kampus_id" name="kampus_id" required disabled>
                    <option value="">-- Pilih Provinsi & Jenis PT Dulu --</option>
                </select>
                <?php if (session('errors.kampus_id')): ?><div class="invalid-feedback"><?= session('errors.kampus_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="prodi_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                <select class="form-select <?= session('errors.prodi_id') ? 'is-invalid' : '' ?>" id="prodi_id" name="prodi_id" required disabled>
                    <option value="">-- Pilih Kampus Dulu --</option>
                </select>
                <?php if (session('errors.prodi_id')): ?><div class="invalid-feedback"><?= session('errors.prodi_id') ?></div><?php endif; ?>
            </div>
            <div class="col-md-12 mb-3">
                <label for="expertise" class="form-label">Keahlian/Bidang Ilmu</label>
                <input type="text" class="form-control" id="expertise" name="expertise" value="<?= old('expertise') ?>" placeholder="Contoh: Pendidikan Matematika, Teknologi Informasi">
            </div>
        </div>
    </div>

    <div class="form-step" data-step="4">
        <h5 class="mb-3">
            <i class="material-icons-outlined" style="font-size: 20px; vertical-align: middle; color: #6c757d;">description</i>
            Data Tambahan & Pembayaran
        </h5>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="motivasi" class="form-label">Motivasi Bergabung</label>
                <textarea class="form-control" id="motivasi" name="motivasi" rows="3" placeholder="Ceritakan motivasi Anda bergabung dengan SPK"><?= old('motivasi') ?></textarea>
            </div>
            <div class="col-md-12 mb-3">
                <label for="sosmed" class="form-label">Link Social Media</label>
                <input type="url" class="form-control" id="sosmed" name="sosmed" value="<?= old('sosmed') ?>" placeholder="https://facebook.com/username">
            </div>
            <div class="col-md-12 mb-3">
                <label for="bukti_bayar" class="form-label">Bukti Pembayaran Iuran Pertama <span class="text-danger">*</span></label>
                <input type="file" class="form-control <?= session('errors.bukti_bayar') ? 'is-invalid' : '' ?>" id="bukti_bayar" name="bukti_bayar" accept="image/jpeg,image/jpg,image/png,application/pdf" required>
                <div class="form-text">Format: JPG, PNG, PDF. Maksimal 4MB</div>
                <div class="file-preview" id="buktiBayarPreview"><img src="" alt="Preview Bukti Bayar" id="buktiBayarPreviewImg"></div>
                <?php if (session('errors.bukti_bayar')): ?><div class="invalid-feedback"><?= session('errors.bukti_bayar') ?></div><?php endif; ?>
            </div>
            <div class="col-md-12 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                    <label class="form-check-label" for="terms">
                        Saya menyetujui <a href="<?= base_url('terms') ?>" target="_blank">Syarat & Ketentuan</a>
                        serta <a href="<?= base_url('privacy') ?>" target="_blank">Kebijakan Privasi</a>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="step-navigation">
        <button type="button" class="btn btn-secondary" id="prevButton" style="display: none;">
            <i class="material-icons-outlined align-middle" style="font-size: 18px;">arrow_back</i>
            Sebelumnya
        </button>
        <button type="button" class="btn btn-primary ms-auto" id="nextButton">
            Berikutnya
            <i class="material-icons-outlined align-middle" style="font-size: 18px;">arrow_forward</i>
        </button>
        <button type="submit" class="btn btn-success ms-auto" id="submitButton" style="display: none;">
            <i class="material-icons-outlined align-middle" style="font-size: 18px;">how_to_reg</i>
            Daftar Sekarang
        </button>
    </div>

    <div class="text-center mt-4">
        <p class="mb-0">
            Sudah punya akun?
            <a href="<?= base_url('auth/login') ?>" class="text-decoration-none fw-bold">Login di sini</a>
        </p>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<div class="auth-footer">
    <p class="mb-0">
        <a href="<?= base_url('/') ?>" class="me-3"><i class="material-icons-outlined align-middle" style="font-size: 16px;">home</i> Kembali ke Beranda</a>
        <a href="<?= base_url('help') ?>"><i class="material-icons-outlined align-middle" style="font-size: 16px;">help</i> Bantuan</a>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');

        // --- ORIGINAL SCRIPTS ---
        const passwordInput = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const passwordStrengthBar = document.querySelector('.password-strength-bar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            passwordStrengthBar.className = 'password-strength-bar';
            if (strength <= 1) passwordStrengthBar.classList.add('weak');
            else if (strength <= 3) passwordStrengthBar.classList.add('medium');
            else passwordStrengthBar.classList.add('strong');
        });

        passwordConfirm.addEventListener('input', function() {
            this.setCustomValidity(this.value !== passwordInput.value ? 'Password tidak cocok' : '');
        });

        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreviewImg').src = e.target.result;
                    document.getElementById('fotoPreview').classList.add('show');
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('bukti_bayar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('buktiBayarPreviewImg').src = e.target.result;
                    document.getElementById('buktiBayarPreview').classList.add('show');
                };
                reader.readAsDataURL(file);
            }
        });

        const provinsiSelect = document.getElementById('provinsi_id');
        const jenisPtSelect = document.getElementById('jenis_pt_id');
        const kampusSelect = document.getElementById('kampus_id');
        const prodiSelect = document.getElementById('prodi_id');

        function loadKampus() {
            const provinsiId = provinsiSelect.value;
            const jenisPtId = jenisPtSelect.value;
            if (provinsiId && jenisPtId) {
                kampusSelect.disabled = true;
                kampusSelect.innerHTML = '<option value="">Loading...</option>';
                fetch(`<?= base_url('api/kampus') ?>?provinsi_id=${provinsiId}&jenis_pt_id=${jenisPtId}`)
                    .then(response => response.json())
                    .then(data => {
                        kampusSelect.innerHTML = '<option value="">-- Pilih Kampus --</option>';
                        data.forEach(kampus => {
                            kampusSelect.innerHTML += `<option value="${kampus.id}">${kampus.name}</option>`;
                        });
                        kampusSelect.disabled = false;
                    }).catch(error => {
                        console.error('Error:', error);
                        kampusSelect.innerHTML = '<option value="">Error loading data</option>';
                    });
            }
        }

        function loadProdi() {
            const kampusId = kampusSelect.value;
            if (kampusId) {
                prodiSelect.disabled = true;
                prodiSelect.innerHTML = '<option value="">Loading...</option>';
                fetch(`<?= base_url('api/prodi') ?>?kampus_id=${kampusId}`)
                    .then(response => response.json())
                    .then(data => {
                        prodiSelect.innerHTML = '<option value="">-- Pilih Program Studi --</option>';
                        data.forEach(prodi => {
                            prodiSelect.innerHTML += `<option value="${prodi.id}">${prodi.name}</option>`;
                        });
                        prodiSelect.disabled = false;
                    }).catch(error => {
                        console.error('Error:', error);
                        prodiSelect.innerHTML = '<option value="">Error loading data</option>';
                    });
            }
        }

        provinsiSelect.addEventListener('change', loadKampus);
        jenisPtSelect.addEventListener('change', loadKampus);
        kampusSelect.addEventListener('change', loadProdi);

        // --- NEW MULTI-STEP WIZARD SCRIPT ---
        const steps = Array.from(document.querySelectorAll('.form-step'));
        const stepperItems = Array.from(document.querySelectorAll('.stepper-item'));
        const nextButton = document.getElementById('nextButton');
        const prevButton = document.getElementById('prevButton');
        const submitButton = document.getElementById('submitButton');
        let currentStep = 0;

        const updateButtons = () => {
            prevButton.style.display = currentStep > 0 ? 'inline-flex' : 'none';
            nextButton.style.display = currentStep < steps.length - 1 ? 'inline-flex' : 'none';
            submitButton.style.display = currentStep === steps.length - 1 ? 'inline-flex' : 'none';
        };

        const goToStep = (stepIndex) => {
            steps[currentStep].classList.remove('active');
            stepperItems[currentStep].classList.remove('active');

            steps[stepIndex].classList.add('active');
            stepperItems[stepIndex].classList.add('active');
            stepperItems[stepIndex].classList.add('completed'); // Mark as visited

            currentStep = stepIndex;
            updateButtons();
        };

        const validateStep = (stepIndex) => {
            const currentStepElement = steps[stepIndex];
            const inputs = Array.from(currentStepElement.querySelectorAll('input[required], select[required], textarea[required]'));
            let isValid = true;
            for (const input of inputs) {
                if (!input.checkValidity()) {
                    isValid = false;
                    // Trigger bootstrap validation styles
                    input.classList.add('is-invalid');
                    // Add was-validated to the form to show all messages in the step
                    form.classList.add('was-validated');
                } else {
                    input.classList.remove('is-invalid');
                }
            }
            return isValid;
        };

        nextButton.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                form.classList.remove('was-validated'); // Reset for the next step
                if (currentStep < steps.length - 1) {
                    goToStep(currentStep + 1);
                }
            }
        });

        prevButton.addEventListener('click', () => {
            form.classList.remove('was-validated'); // No need to show errors when going back
            if (currentStep > 0) {
                goToStep(currentStep - 1);
            }
        });

        form.addEventListener('submit', function(e) {
            if (!validateStep(currentStep)) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            }
            form.classList.add('was-validated');
        });

        // Initialize
        updateButtons();
    });
</script>
<?= $this->endSection() ?>