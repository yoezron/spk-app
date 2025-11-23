<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - SPK</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .register-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 25px;
        }

        .section-title {
            color: #667eea;
            font-weight: 600;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        .password-strength {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s;
            margin-top: 5px;
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="mb-1"><i class="fas fa-user-plus"></i> Pendaftaran Anggota Baru</h3>
                <p class="mb-0 small">Serikat Pekerja Kampus (SPK)</p>
            </div>

            <div class="card-body p-4">
                <?php
                // Get flashdata once and store in variables to avoid multiple calls
                $error = session()->getFlashdata('error');
                $errors = session()->getFlashdata('errors');
                ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('auth/register') ?>" method="POST" enctype="multipart/form-data" id="registerForm">
                    <?= csrf_field() ?>

                    <!-- SECTION 1: Data Akun -->
                    <h5 class="section-title"><i class="fas fa-key"></i> Data Akun</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= old('email') ?>" required>
                            <small class="form-text text-muted">Email akan digunakan untuk login</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label required">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?= old('username') ?>" required minlength="5">
                            <small class="form-text text-muted">Min. 5 karakter, tanpa spasi</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label required">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                    required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <small class="form-text text-muted">Min. 8 karakter, kombinasi huruf, angka & simbol</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirm" class="form-label required">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirm"
                                name="password_confirm" required>
                        </div>
                    </div>

                    <!-- SECTION 2: Data Pribadi -->
                    <h5 class="section-title"><i class="fas fa-user"></i> Data Pribadi</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="full_name" class="form-label required">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                value="<?= old('full_name') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik"
                                value="<?= old('nik') ?>" maxlength="16" pattern="[0-9]{16}">
                            <small class="form-text text-muted">16 digit NIK KTP</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nidn_nip" class="form-label">NIDN/NIP</label>
                            <input type="text" class="form-control" id="nidn_nip" name="nidn_nip"
                                value="<?= old('nidn_nip') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label required">Jenis Kelamin</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki" <?= old('gender') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= old('gender') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="birth_place" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="birth_place" name="birth_place"
                                value="<?= old('birth_place') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="birth_date" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date"
                                value="<?= old('birth_date') ?>">
                        </div>
                    </div>

                    <!-- SECTION 3: Kontak -->
                    <h5 class="section-title"><i class="fas fa-phone"></i> Kontak</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label required">No. HP/Telepon</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                value="<?= old('phone') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="whatsapp" class="form-label required">No. WhatsApp</label>
                            <input type="tel" class="form-control" id="whatsapp" name="whatsapp"
                                value="<?= old('whatsapp') ?>" required>
                            <small class="form-text text-muted">Contoh: 081234567890</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label required">Alamat Lengkap</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?= old('address') ?></textarea>
                    </div>

                    <!-- SECTION 4: Data Kepegawaian -->
                    <h5 class="section-title"><i class="fas fa-briefcase"></i> Data Kepegawaian</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employment_status_id" class="form-label required">Status Kepegawaian</label>
                            <select class="form-select" id="employment_status_id" name="employment_status_id" required>
                                <option value="">-- Pilih Status --</option>
                                <?php if (isset($employmentStatuses)): ?>
                                    <?php foreach ($employmentStatuses as $status): ?>
                                        <option value="<?= $status['id'] ?>" <?= old('employment_status_id') == $status['id'] ? 'selected' : '' ?>>
                                            <?= esc($status['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payer_id" class="form-label required">Pemberi Gaji</label>
                            <select class="form-select" id="payer_id" name="payer_id" required>
                                <option value="">-- Pilih Pemberi Gaji --</option>
                                <?php if (isset($payers)): ?>
                                    <?php foreach ($payers as $payer): ?>
                                        <option value="<?= $payer['id'] ?>" <?= old('payer_id') == $payer['id'] ? 'selected' : '' ?>>
                                            <?= esc($payer['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="salary_range_id" class="form-label required">Range Gaji</label>
                            <select class="form-select" id="salary_range_id" name="salary_range_id" required>
                                <option value="">-- Pilih Range Gaji --</option>
                                <?php if (isset($salaryRanges)): ?>
                                    <?php foreach ($salaryRanges as $range): ?>
                                        <option value="<?= $range['id'] ?>" <?= old('salary_range_id') == $range['id'] ? 'selected' : '' ?>>
                                            <?= esc($range['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="basic_salary" class="form-label">Gaji Pokok (Opsional)</label>
                            <input type="number" class="form-control" id="basic_salary" name="basic_salary"
                                value="<?= old('basic_salary') ?>" min="0">
                            <small class="form-text text-muted">Data ini bersifat rahasia</small>
                        </div>
                    </div>

                    <!-- SECTION 5: Data Institusi -->
                    <h5 class="section-title"><i class="fas fa-university"></i> Data Institusi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="province_id" class="form-label required">Provinsi</label>
                            <select class="form-select" id="province_id" name="province_id" required>
                                <option value="">-- Pilih Provinsi --</option>
                                <?php if (isset($provinces)): ?>
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?= $province['id'] ?>" <?= old('province_id') == $province['id'] ? 'selected' : '' ?>>
                                            <?= esc($province['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="university_type_id" class="form-label required">Jenis Perguruan Tinggi</label>
                            <select class="form-select" id="university_type_id" name="university_type_id" required>
                                <option value="">-- Pilih Jenis PT --</option>
                                <?php if (isset($universityTypes)): ?>
                                    <?php foreach ($universityTypes as $type): ?>
                                        <option value="<?= $type['id'] ?>" <?= old('university_type_id') == $type['id'] ? 'selected' : '' ?>>
                                            <?= esc($type['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="university_id" class="form-label required">Perguruan Tinggi</label>
                            <select class="form-select" id="university_id" name="university_id" required>
                                <option value="">-- Pilih Provinsi dulu --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="study_program_id" class="form-label required">Program Studi</label>
                            <select class="form-select" id="study_program_id" name="study_program_id" required>
                                <option value="">-- Pilih Kampus dulu --</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECTION 6: Upload File -->
                    <h5 class="section-title"><i class="fas fa-upload"></i> Upload Dokumen</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label required">Pas Foto</label>
                            <input type="file" class="form-control" id="photo" name="photo"
                                accept="image/jpeg,image/png,image/jpg" required>
                            <small class="form-text text-muted">Format: JPG/PNG, Max: 2MB</small>
                            <img id="photoPreview" class="image-preview img-thumbnail" alt="Preview">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_proof" class="form-label required">Bukti Pembayaran Iuran</label>
                            <input type="file" class="form-control" id="payment_proof" name="payment_proof"
                                accept="image/jpeg,image/png,image/jpg,application/pdf" required>
                            <small class="form-text text-muted">Format: JPG/PNG/PDF, Max: 2MB</small>
                            <img id="paymentPreview" class="image-preview img-thumbnail" alt="Preview">
                        </div>
                    </div>

                    <!-- SECTION 7: Informasi Tambahan -->
                    <h5 class="section-title"><i class="fas fa-info-circle"></i> Informasi Tambahan</h5>
                    <div class="mb-3">
                        <label for="expertise" class="form-label">Bidang Keahlian</label>
                        <textarea class="form-control" id="expertise" name="expertise" rows="2"
                            placeholder="Contoh: Psikologi, Statistika, Hukum Perburuhan"><?= old('expertise') ?></textarea>
                        <small class="form-text text-muted">Pisahkan dengan titik koma (;)</small>
                    </div>

                    <div class="mb-3">
                        <label for="motivation" class="form-label required">Motivasi Bergabung</label>
                        <textarea class="form-control" id="motivation" name="motivation" rows="3"
                            required placeholder="Ceritakan motivasi Anda bergabung dengan SPK..."><?= old('motivation') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="social_media" class="form-label">Link Media Sosial</label>
                        <input type="url" class="form-control" id="social_media" name="social_media"
                            value="<?= old('social_media') ?>" placeholder="https://...">
                        <small class="form-text text-muted">LinkedIn, Instagram, atau media sosial lainnya (opsional)</small>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                Saya menyetujui <a href="<?= base_url('pages/terms') ?>" target="_blank">Syarat & Ketentuan</a>
                                serta <a href="<?= base_url('pages/privacy') ?>" target="_blank">Kebijakan Privasi</a> SPK
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-register btn-lg">
                            <i class="fas fa-paper-plane"></i> Daftar Sekarang
                        </button>
                        <a href="<?= base_url('auth/login') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Sudah punya akun? Login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-white small">
                <i class="fas fa-shield-alt"></i> Data Anda aman dan terlindungi
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Password Visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');

            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password Strength Indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;

            strengthBar.style.width = (strength * 20) + '%';

            if (strength < 2) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength < 4) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
        });

        // Image Preview
        function previewImage(input, previewId) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        document.getElementById('photo').addEventListener('change', function() {
            previewImage(this, 'photoPreview');
        });

        document.getElementById('payment_proof').addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.includes('image')) {
                previewImage(this, 'paymentPreview');
            }
        });

        // Dynamic Dropdown: Province -> University
        document.getElementById('province_id').addEventListener('change', function() {
            const provinceId = this.value;
            const universitySelect = document.getElementById('university_id');
            const studyProgramSelect = document.getElementById('study_program_id');

            // Reset
            universitySelect.innerHTML = '<option value="">-- Loading... --</option>';
            studyProgramSelect.innerHTML = '<option value="">-- Pilih Kampus dulu --</option>';

            if (provinceId) {
                fetch(`<?= base_url('api/universities') ?>?province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(data => {
                        universitySelect.innerHTML = '<option value="">-- Pilih Perguruan Tinggi --</option>';
                        if (data.success && data.data) {
                            data.data.forEach(university => {
                                universitySelect.innerHTML += `<option value="${university.id}">${university.name}</option>`;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        universitySelect.innerHTML = '<option value="">-- Error loading data --</option>';
                    });
            } else {
                universitySelect.innerHTML = '<option value="">-- Pilih Provinsi dulu --</option>';
            }
        });

        // Dynamic Dropdown: University -> Study Program
        document.getElementById('university_id').addEventListener('change', function() {
            const universityId = this.value;
            const studyProgramSelect = document.getElementById('study_program_id');

            studyProgramSelect.innerHTML = '<option value="">-- Loading... --</option>';

            if (universityId) {
                fetch(`<?= base_url('api/study-programs') ?>?university_id=${universityId}`)
                    .then(response => response.json())
                    .then(data => {
                        studyProgramSelect.innerHTML = '<option value="">-- Pilih Program Studi --</option>';
                        if (data.success && data.data) {
                            data.data.forEach(program => {
                                studyProgramSelect.innerHTML += `<option value="${program.id}">${program.name}</option>`;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        studyProgramSelect.innerHTML = '<option value="">-- Error loading data --</option>';
                    });
            } else {
                studyProgramSelect.innerHTML = '<option value="">-- Pilih Kampus dulu --</option>';
            }
        });

        // Form Validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        });
    </script>
</body>

</html>