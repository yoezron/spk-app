<?php

/**
 * View: Register
 * Controller: Auth\RegisterController
 * Description: Halaman registrasi anggota baru SPK
 * 
 * Features:
 * - Multi-section form (Data Pribadi, Kepegawaian, Kampus, Pembayaran)
 * - Dynamic dropdowns (cascade: provinsi, kampus, prodi)
 * - File upload (foto & bukti bayar)
 * - Password strength indicator
 * - Validation error display
 * - CSRF protection
 * 
 * @package App\Views\Auth
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('styles') ?>
<style>
    /* Wider auth card for register form */
    .auth-container {
        max-width: 700px;
    }

    /* Section Headers */
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        margin: -40px -30px 30px -30px;
        font-weight: 600;
        font-size: 16px;
        border-radius: 16px 16px 0 0;
    }

    .section-header:not(:first-child) {
        margin-top: 30px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .section-header i {
        margin-right: 8px;
        vertical-align: middle;
    }

    /* Form sections */
    .form-section {
        margin-bottom: 30px;
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

    /* Required field indicator */
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

<div class="required-note">
    <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">info</i>
    <span class="text-danger">*</span> wajib diisi
</div>

<form action="<?= base_url('auth/register') ?>" method="POST" enctype="multipart/form-data" id="registerForm">
    <?= csrf_field() ?>

    <!-- SECTION 1: Data Pribadi -->
    <div class="form-section">
        <h6 class="text-muted mb-3">
            <i class="material-icons-outlined" style="font-size: 18px; vertical-align: middle;">badge</i>
            Data Pribadi
        </h6>

        <div class="row">
            <!-- Nama Lengkap -->
            <div class="col-md-12 mb-3">
                <label for="nama_lengkap" class="form-label">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    class="form-control <?= session('errors.nama_lengkap') ? 'is-invalid' : '' ?>"
                    id="nama_lengkap"
                    name="nama_lengkap"
                    value="<?= old('nama_lengkap') ?>"
                    placeholder="Masukkan nama lengkap"
                    required>
                <?php if (session('errors.nama_lengkap')): ?>
                    <div class="invalid-feedback"><?= session('errors.nama_lengkap') ?></div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <input
                    type="email"
                    class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                    id="email"
                    name="email"
                    value="<?= old('email') ?>"
                    placeholder="contoh@email.com"
                    required>
                <div class="form-text">Gunakan email aktif untuk verifikasi</div>
                <?php if (session('errors.email')): ?>
                    <div class="invalid-feedback"><?= session('errors.email') ?></div>
                <?php endif; ?>
            </div>

            <!-- No. WhatsApp -->
            <div class="col-md-6 mb-3">
                <label for="no_wa" class="form-label">
                    No. WhatsApp <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    class="form-control <?= session('errors.no_wa') ? 'is-invalid' : '' ?>"
                    id="no_wa"
                    name="no_wa"
                    value="<?= old('no_wa') ?>"
                    placeholder="08xxxxxxxxxx"
                    required>
                <?php if (session('errors.no_wa')): ?>
                    <div class="invalid-feedback"><?= session('errors.no_wa') ?></div>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">
                    Password <span class="text-danger">*</span>
                </label>
                <input
                    type="password"
                    class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                    id="password"
                    name="password"
                    placeholder="Min. 8 karakter"
                    required>
                <div class="password-strength">
                    <div class="password-strength-bar"></div>
                </div>
                <div class="form-text" id="passwordHelp">Minimal 8 karakter, kombinasi huruf dan angka</div>
                <?php if (session('errors.password')): ?>
                    <div class="invalid-feedback"><?= session('errors.password') ?></div>
                <?php endif; ?>
            </div>

            <!-- Konfirmasi Password -->
            <div class="col-md-6 mb-3">
                <label for="password_confirm" class="form-label">
                    Konfirmasi Password <span class="text-danger">*</span>
                </label>
                <input
                    type="password"
                    class="form-control <?= session('errors.password_confirm') ? 'is-invalid' : '' ?>"
                    id="password_confirm"
                    name="password_confirm"
                    placeholder="Ulangi password"
                    required>
                <?php if (session('errors.password_confirm')): ?>
                    <div class="invalid-feedback"><?= session('errors.password_confirm') ?></div>
                <?php endif; ?>
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    Jenis Kelamin <span class="text-danger">*</span>
                </label>
                <div>
                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="jenis_kelamin"
                            id="jk_l"
                            value="L"
                            <?= old('jenis_kelamin') === 'L' ? 'checked' : '' ?>
                            required>
                        <label class="form-check-label" for="jk_l">Laki-laki</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="jenis_kelamin"
                            id="jk_p"
                            value="P"
                            <?= old('jenis_kelamin') === 'P' ? 'checked' : '' ?>
                            required>
                        <label class="form-check-label" for="jk_p">Perempuan</label>
                    </div>
                </div>
                <?php if (session('errors.jenis_kelamin')): ?>
                    <div class="text-danger small"><?= session('errors.jenis_kelamin') ?></div>
                <?php endif; ?>
            </div>

            <!-- Alamat -->
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">
                    Alamat Lengkap <span class="text-danger">*</span>
                </label>
                <textarea
                    class="form-control <?= session('errors.alamat') ? 'is-invalid' : '' ?>"
                    id="alamat"
                    name="alamat"
                    rows="3"
                    placeholder="Masukkan alamat lengkap"
                    required><?= old('alamat') ?></textarea>
                <?php if (session('errors.alamat')): ?>
                    <div class="invalid-feedback"><?= session('errors.alamat') ?></div>
                <?php endif; ?>
            </div>

            <!-- Foto -->
            <div class="col-md-12 mb-3">
                <label for="foto" class="form-label">
                    Foto <span class="text-danger">*</span>
                </label>
                <input
                    type="file"
                    class="form-control <?= session('errors.foto') ? 'is-invalid' : '' ?>"
                    id="foto"
                    name="foto"
                    accept="image/jpeg,image/jpg,image/png"
                    required>
                <div class="form-text">Format: JPG, PNG. Maksimal 2MB</div>
                <div class="file-preview" id="fotoPreview">
                    <img src="" alt="Preview Foto" id="fotoPreviewImg">
                </div>
                <?php if (session('errors.foto')): ?>
                    <div class="invalid-feedback"><?= session('errors.foto') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Data Kepegawaian -->
    <div class="form-section">
        <h6 class="text-muted mb-3">
            <i class="material-icons-outlined" style="font-size: 18px; vertical-align: middle;">work</i>
            Data Kepegawaian
        </h6>

        <div class="row">
            <!-- Status Kepegawaian -->
            <div class="col-md-6 mb-3">
                <label for="status_kepegawaian_id" class="form-label">
                    Status Kepegawaian <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.status_kepegawaian_id') ? 'is-invalid' : '' ?>"
                    id="status_kepegawaian_id"
                    name="status_kepegawaian_id"
                    required>
                    <option value="">-- Pilih Status --</option>
                    <?php if (isset($status_kepegawaian)): ?>
                        <?php foreach ($status_kepegawaian as $status): ?>
                            <option value="<?= $status->id ?>" <?= old('status_kepegawaian_id') == $status->id ? 'selected' : '' ?>>
                                <?= esc($status->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (session('errors.status_kepegawaian_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.status_kepegawaian_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- NIDN/NIP -->
            <div class="col-md-6 mb-3">
                <label for="nidn_nip" class="form-label">
                    NIDN/NIP
                </label>
                <input
                    type="text"
                    class="form-control <?= session('errors.nidn_nip') ? 'is-invalid' : '' ?>"
                    id="nidn_nip"
                    name="nidn_nip"
                    value="<?= old('nidn_nip') ?>"
                    placeholder="Opsional">
                <?php if (session('errors.nidn_nip')): ?>
                    <div class="invalid-feedback"><?= session('errors.nidn_nip') ?></div>
                <?php endif; ?>
            </div>

            <!-- Pemberi Gaji -->
            <div class="col-md-6 mb-3">
                <label for="pemberi_gaji_id" class="form-label">
                    Pemberi Gaji <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.pemberi_gaji_id') ? 'is-invalid' : '' ?>"
                    id="pemberi_gaji_id"
                    name="pemberi_gaji_id"
                    required>
                    <option value="">-- Pilih Pemberi Gaji --</option>
                    <?php if (isset($pemberi_gaji)): ?>
                        <?php foreach ($pemberi_gaji as $pemberi): ?>
                            <option value="<?= $pemberi->id ?>" <?= old('pemberi_gaji_id') == $pemberi->id ? 'selected' : '' ?>>
                                <?= esc($pemberi->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (session('errors.pemberi_gaji_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.pemberi_gaji_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- Range Gaji -->
            <div class="col-md-6 mb-3">
                <label for="range_gaji_id" class="form-label">
                    Range Gaji <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.range_gaji_id') ? 'is-invalid' : '' ?>"
                    id="range_gaji_id"
                    name="range_gaji_id"
                    required>
                    <option value="">-- Pilih Range Gaji --</option>
                    <?php if (isset($range_gaji)): ?>
                        <?php foreach ($range_gaji as $range): ?>
                            <option value="<?= $range->id ?>" <?= old('range_gaji_id') == $range->id ? 'selected' : '' ?>>
                                <?= esc($range->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (session('errors.range_gaji_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.range_gaji_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- Gaji Pokok -->
            <div class="col-md-12 mb-3">
                <label for="gaji_pokok" class="form-label">
                    Gaji Pokok <span class="text-danger">*</span>
                </label>
                <input
                    type="number"
                    class="form-control <?= session('errors.gaji_pokok') ? 'is-invalid' : '' ?>"
                    id="gaji_pokok"
                    name="gaji_pokok"
                    value="<?= old('gaji_pokok') ?>"
                    placeholder="Contoh: 5000000"
                    required>
                <div class="form-text">Isi dengan angka tanpa titik atau koma</div>
                <?php if (session('errors.gaji_pokok')): ?>
                    <div class="invalid-feedback"><?= session('errors.gaji_pokok') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Data Kampus -->
    <div class="form-section">
        <h6 class="text-muted mb-3">
            <i class="material-icons-outlined" style="font-size: 18px; vertical-align: middle;">school</i>
            Data Kampus
        </h6>

        <div class="row">
            <!-- Provinsi -->
            <div class="col-md-6 mb-3">
                <label for="provinsi_id" class="form-label">
                    Provinsi <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.provinsi_id') ? 'is-invalid' : '' ?>"
                    id="provinsi_id"
                    name="provinsi_id"
                    required>
                    <option value="">-- Pilih Provinsi --</option>
                    <?php if (isset($provinsi)): ?>
                        <?php foreach ($provinsi as $prov): ?>
                            <option value="<?= $prov->id ?>" <?= old('provinsi_id') == $prov->id ? 'selected' : '' ?>>
                                <?= esc($prov->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (session('errors.provinsi_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.provinsi_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- Jenis PT -->
            <div class="col-md-6 mb-3">
                <label for="jenis_pt_id" class="form-label">
                    Jenis Perguruan Tinggi <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.jenis_pt_id') ? 'is-invalid' : '' ?>"
                    id="jenis_pt_id"
                    name="jenis_pt_id"
                    required>
                    <option value="">-- Pilih Jenis PT --</option>
                    <?php if (isset($jenis_pt)): ?>
                        <?php foreach ($jenis_pt as $jenis): ?>
                            <option value="<?= $jenis->id ?>" <?= old('jenis_pt_id') == $jenis->id ? 'selected' : '' ?>>
                                <?= esc($jenis->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (session('errors.jenis_pt_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.jenis_pt_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- Kampus -->
            <div class="col-md-6 mb-3">
                <label for="kampus_id" class="form-label">
                    Kampus <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.kampus_id') ? 'is-invalid' : '' ?>"
                    id="kampus_id"
                    name="kampus_id"
                    required
                    disabled>
                    <option value="">-- Pilih Provinsi & Jenis PT Dulu --</option>
                </select>
                <?php if (session('errors.kampus_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.kampus_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- Program Studi -->
            <div class="col-md-6 mb-3">
                <label for="prodi_id" class="form-label">
                    Program Studi <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select <?= session('errors.prodi_id') ? 'is-invalid' : '' ?>"
                    id="prodi_id"
                    name="prodi_id"
                    required
                    disabled>
                    <option value="">-- Pilih Kampus Dulu --</option>
                </select>
                <?php if (session('errors.prodi_id')): ?>
                    <div class="invalid-feedback"><?= session('errors.prodi_id') ?></div>
                <?php endif; ?>
            </div>

            <!-- Keahlian -->
            <div class="col-md-12 mb-3">
                <label for="expertise" class="form-label">
                    Keahlian/Bidang Ilmu
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="expertise"
                    name="expertise"
                    value="<?= old('expertise') ?>"
                    placeholder="Contoh: Pendidikan Matematika, Teknologi Informasi">
            </div>
        </div>
    </div>

    <!-- SECTION 4: Data Tambahan & Pembayaran -->
    <div class="form-section">
        <h6 class="text-muted mb-3">
            <i class="material-icons-outlined" style="font-size: 18px; vertical-align: middle;">description</i>
            Data Tambahan & Pembayaran
        </h6>

        <div class="row">
            <!-- Motivasi -->
            <div class="col-md-12 mb-3">
                <label for="motivasi" class="form-label">
                    Motivasi Bergabung
                </label>
                <textarea
                    class="form-control"
                    id="motivasi"
                    name="motivasi"
                    rows="3"
                    placeholder="Ceritakan motivasi Anda bergabung dengan SPK"><?= old('motivasi') ?></textarea>
            </div>

            <!-- Social Media -->
            <div class="col-md-12 mb-3">
                <label for="sosmed" class="form-label">
                    Link Social Media
                </label>
                <input
                    type="url"
                    class="form-control"
                    id="sosmed"
                    name="sosmed"
                    value="<?= old('sosmed') ?>"
                    placeholder="https://facebook.com/username atau https://instagram.com/username">
            </div>

            <!-- Bukti Bayar -->
            <div class="col-md-12 mb-3">
                <label for="bukti_bayar" class="form-label">
                    Bukti Pembayaran Iuran Pertama <span class="text-danger">*</span>
                </label>
                <input
                    type="file"
                    class="form-control <?= session('errors.bukti_bayar') ? 'is-invalid' : '' ?>"
                    id="bukti_bayar"
                    name="bukti_bayar"
                    accept="image/jpeg,image/jpg,image/png,application/pdf"
                    required>
                <div class="form-text">Format: JPG, PNG, PDF. Maksimal 4MB</div>
                <div class="file-preview" id="buktiBayarPreview">
                    <img src="" alt="Preview Bukti Bayar" id="buktiBayarPreviewImg">
                </div>
                <?php if (session('errors.bukti_bayar')): ?>
                    <div class="invalid-feedback"><?= session('errors.bukti_bayar') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div class="mb-4">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                name="terms"
                id="terms"
                required>
            <label class="form-check-label" for="terms">
                Saya menyetujui <a href="<?= base_url('terms') ?>" target="_blank">Syarat & Ketentuan</a>
                serta <a href="<?= base_url('privacy') ?>" target="_blank">Kebijakan Privasi</a>
            </label>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary btn-lg" id="submitButton">
            <i class="material-icons-outlined align-middle me-1" style="font-size: 20px;">how_to_reg</i>
            Daftar Sekarang
        </button>
    </div>

    <!-- Login Link -->
    <div class="text-center">
        <p class="mb-0">
            Sudah punya akun?
            <a href="<?= base_url('auth/login') ?>" class="text-decoration-none fw-bold">
                Login di sini
            </a>
        </p>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<div class="auth-footer">
    <p class="mb-0">
        <a href="<?= base_url('/') ?>" class="me-3">
            <i class="material-icons-outlined align-middle" style="font-size: 16px;">home</i>
            Kembali ke Beranda
        </a>
        <a href="<?= base_url('help') ?>">
            <i class="material-icons-outlined align-middle" style="font-size: 16px;">help</i>
            Bantuan
        </a>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const submitButton = document.getElementById('submitButton');
        const passwordInput = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const passwordStrengthBar = document.querySelector('.password-strength-bar');

        // Password strength checker
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            passwordStrengthBar.className = 'password-strength-bar';
            if (strength <= 1) {
                passwordStrengthBar.classList.add('weak');
            } else if (strength <= 3) {
                passwordStrengthBar.classList.add('medium');
            } else {
                passwordStrengthBar.classList.add('strong');
            }
        });

        // Password confirmation validation
        passwordConfirm.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });

        // File preview - Foto
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

        // File preview - Bukti Bayar
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

        // Dynamic cascade dropdowns
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

                // AJAX call to get kampus
                fetch(`<?= base_url('api/kampus') ?>?provinsi_id=${provinsiId}&jenis_pt_id=${jenisPtId}`)
                    .then(response => response.json())
                    .then(data => {
                        kampusSelect.innerHTML = '<option value="">-- Pilih Kampus --</option>';
                        data.forEach(kampus => {
                            kampusSelect.innerHTML += `<option value="${kampus.id}">${kampus.name}</option>`;
                        });
                        kampusSelect.disabled = false;
                    })
                    .catch(error => {
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

                // AJAX call to get prodi
                fetch(`<?= base_url('api/prodi') ?>?kampus_id=${kampusId}`)
                    .then(response => response.json())
                    .then(data => {
                        prodiSelect.innerHTML = '<option value="">-- Pilih Program Studi --</option>';
                        data.forEach(prodi => {
                            prodiSelect.innerHTML += `<option value="${prodi.id}">${prodi.name}</option>`;
                        });
                        prodiSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        prodiSelect.innerHTML = '<option value="">Error loading data</option>';
                    });
            }
        }

        provinsiSelect.addEventListener('change', loadKampus);
        jenisPtSelect.addEventListener('change', loadKampus);
        kampusSelect.addEventListener('change', loadProdi);

        // Form submission
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            }

            form.classList.add('was-validated');
        });
    });
</script>
<?= $this->endSection() ?>