<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="<?= site_url() ?>" class="navbar-brand navbar-brand-autodark">
            <img src="<?= base_url('assets/img/logo.png') ?>" height="36" alt="SPK">
        </a>
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Aktivasi Akun SPK</h2>

            <?php if (isset($error)): ?>
                <!-- Error State -->
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-alert-circle icon alert-icon"></i>
                        </div>
                        <div>
                            <h4 class="alert-title">Aktivasi Gagal</h4>
                            <div class="text-muted"><?= esc($error) ?></div>
                        </div>
                    </div>
                </div>

                <?php if (isset($expired) && $expired): ?>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" onclick="requestNewToken()">
                            <i class="ti ti-mail me-1"></i>
                            Kirim Ulang Email Aktivasi
                        </button>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="<?= site_url('login') ?>" class="btn btn-link">
                        Kembali ke Login
                    </a>
                </div>

            <?php elseif (isset($alreadyActivated) && $alreadyActivated): ?>
                <!-- Already Activated State -->
                <div class="alert alert-info" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-info-circle icon alert-icon"></i>
                        </div>
                        <div>
                            <h4 class="alert-title">Akun Sudah Aktif</h4>
                            <div class="text-muted">
                                Akun Anda sudah diaktivasi sebelumnya pada <?= date('d M Y H:i', strtotime($activatedAt)) ?>.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="<?= site_url('login') ?>" class="btn btn-primary w-100">
                        <i class="ti ti-login me-1"></i>
                        Login ke Akun Anda
                    </a>
                </div>

            <?php else: ?>
                <!-- Activation Form -->
                <div class="alert alert-info mb-4" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-info-circle icon alert-icon"></i>
                        </div>
                        <div>
                            <h4 class="alert-title">Selamat Datang!</h4>
                            <div class="text-muted">
                                Untuk mengaktifkan akun Anda, silakan set password baru terlebih dahulu.
                            </div>
                        </div>
                    </div>
                </div>

                <form action="<?= site_url('auth/activate/' . esc($token)) ?>" method="POST" id="activationForm">
                    <?= csrf_field() ?>

                    <!-- Member Info -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?= esc($memberData['email'] ?? '') ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?= esc($memberData['full_name'] ?? '') ?>" disabled>
                    </div>

                    <hr class="my-4">

                    <!-- Password Fields -->
                    <div class="mb-3">
                        <label class="form-label required">Password Baru</label>
                        <div class="input-group input-group-flat">
                            <input type="password"
                                class="form-control"
                                name="password"
                                id="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                required>
                            <span class="input-group-text">
                                <a href="#" class="link-secondary" id="togglePassword" data-bs-toggle="tooltip" title="Show password">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </span>
                        </div>
                        <small class="form-hint">
                            Password minimal 8 karakter, kombinasi huruf dan angka
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Konfirmasi Password</label>
                        <div class="input-group input-group-flat">
                            <input type="password"
                                class="form-control"
                                name="password_confirm"
                                id="password_confirm"
                                placeholder="Ulangi password"
                                autocomplete="new-password"
                                required>
                            <span class="input-group-text">
                                <a href="#" class="link-secondary" id="togglePasswordConfirm" data-bs-toggle="tooltip" title="Show password">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </span>
                        </div>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="strengthText"></small>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="agree_terms" required>
                            <span class="form-check-label">
                                Saya setuju dengan <a href="<?= site_url('terms') ?>" target="_blank">Syarat & Ketentuan</a> dan <a href="<?= site_url('privacy') ?>" target="_blank">Kebijakan Privasi</a>
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100" id="btnSubmit">
                            <i class="ti ti-check me-1"></i>
                            Aktivasi Akun Saya
                        </button>
                    </div>
                </form>

                <!-- Info -->
                <div class="text-center text-muted mt-3">
                    <small>
                        Setelah aktivasi, Anda akan diarahkan untuk melengkapi profil Anda.
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center text-muted mt-3">
        Butuh bantuan? <a href="<?= site_url('contact') ?>">Hubungi Kami</a>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePassword').on('click', function(e) {
            e.preventDefault();
            const passwordInput = $('#password');
            const icon = $(this).find('i');

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('ti-eye').addClass('ti-eye-off');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('ti-eye-off').addClass('ti-eye');
            }
        });

        $('#togglePasswordConfirm').on('click', function(e) {
            e.preventDefault();
            const passwordInput = $('#password_confirm');
            const icon = $(this).find('i');

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('ti-eye').addClass('ti-eye-off');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('ti-eye-off').addClass('ti-eye');
            }
        });

        // Password strength checker
        $('#password').on('keyup', function() {
            const password = $(this).val();
            const strength = checkPasswordStrength(password);

            const progressBar = $('#passwordStrength');
            const strengthText = $('#strengthText');

            progressBar.css('width', strength.percentage + '%');
            progressBar.removeClass('bg-danger bg-warning bg-success');
            progressBar.addClass(strength.class);

            strengthText.text(strength.text);
            strengthText.removeClass('text-danger text-warning text-success');
            strengthText.addClass('text-' + strength.color);
        });

        // Password match validation
        $('#password_confirm').on('keyup', function() {
            const password = $('#password').val();
            const confirm = $(this).val();

            if (confirm.length > 0) {
                if (password === confirm) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        // Form validation
        $('#activationForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirm = $('#password_confirm').val();

            if (password.length < 8) {
                e.preventDefault();
                toastr.error('Password minimal 8 karakter');
                return false;
            }

            if (password !== confirm) {
                e.preventDefault();
                toastr.error('Password dan konfirmasi password tidak sama');
                return false;
            }

            // Show loading
            const btnSubmit = $('#btnSubmit');
            btnSubmit.prop('disabled', true);
            btnSubmit.html('<span class="spinner-border spinner-border-sm me-2"></span>Mengaktivasi...');
        });
    });

    function checkPasswordStrength(password) {
        let strength = 0;

        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;

        let result = {
            percentage: strength,
            class: 'bg-danger',
            color: 'danger',
            text: 'Lemah'
        };

        if (strength >= 40 && strength < 70) {
            result.class = 'bg-warning';
            result.color = 'warning';
            result.text = 'Sedang';
        } else if (strength >= 70) {
            result.class = 'bg-success';
            result.color = 'success';
            result.text = 'Kuat';
        }

        return result;
    }

    function requestNewToken() {
        // TODO: Implement request new activation token
        toastr.info('Fitur kirim ulang email akan segera tersedia');
    }
</script>
<?= $this->endSection() ?>