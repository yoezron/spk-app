<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Ubah Password</h1>
            <p class="text-muted">Kelola keamanan akun Anda dengan mengubah password secara berkala</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/profile') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Profil
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">lock</i>
                    Formulir Ubah Password
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('member/profile/update-password') ?>" method="POST" id="changePasswordForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            Password Saat Ini <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control <?= session('errors.current_password') ? 'is-invalid' : '' ?>"
                                   id="current_password"
                                   name="current_password"
                                   required
                                   placeholder="Masukkan password saat ini">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="material-icons-outlined">visibility</i>
                            </button>
                        </div>
                        <?php if (session('errors.current_password')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.current_password') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            Password Baru <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control <?= session('errors.new_password') ? 'is-invalid' : '' ?>"
                                   id="new_password"
                                   name="new_password"
                                   required
                                   minlength="8"
                                   placeholder="Minimal 8 karakter">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="material-icons-outlined">visibility</i>
                            </button>
                        </div>
                        <?php if (session('errors.new_password')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.new_password') ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            Konfirmasi Password Baru <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control <?= session('errors.confirm_password') ? 'is-invalid' : '' ?>"
                                   id="confirm_password"
                                   name="confirm_password"
                                   required
                                   minlength="8"
                                   placeholder="Ulangi password baru">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                <i class="material-icons-outlined">visibility</i>
                            </button>
                        </div>
                        <?php if (session('errors.confirm_password')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.confirm_password') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <label class="form-label">Kekuatan Password:</label>
                        <div class="progress" style="height: 5px;">
                            <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="passwordStrengthText" class="text-muted"></small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="material-icons-outlined">save</i>
                            Simpan Password Baru
                        </button>
                        <a href="<?= base_url('member/profile') ?>" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">tips_and_updates</i>
                    Tips Keamanan Password
                </h6>
                <ul class="mb-0">
                    <li>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
                    <li>Jangan gunakan informasi pribadi (nama, tanggal lahir, dll)</li>
                    <li>Gunakan password yang unik untuk setiap akun</li>
                    <li>Ubah password secara berkala (setiap 3-6 bulan)</li>
                    <li>Jangan bagikan password Anda kepada siapapun</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling.querySelector('i');

        if (field.type === 'password') {
            field.type = 'text';
            button.textContent = 'visibility_off';
        } else {
            field.type = 'password';
            button.textContent = 'visibility';
        }
    }

    // Password strength checker
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthText');

        let strength = 0;
        let feedback = '';

        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        const percentage = (strength / 6) * 100;
        strengthBar.style.width = percentage + '%';

        if (strength < 3) {
            strengthBar.className = 'progress-bar bg-danger';
            feedback = 'Lemah';
        } else if (strength < 5) {
            strengthBar.className = 'progress-bar bg-warning';
            feedback = 'Sedang';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            feedback = 'Kuat';
        }

        strengthText.textContent = feedback;
    });

    // Form validation
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Password baru dan konfirmasi password tidak cocok!');
            return false;
        }

        if (newPassword.length < 8) {
            e.preventDefault();
            alert('Password minimal 8 karakter!');
            return false;
        }
    });
</script>
<?= $this->endSection() ?>
