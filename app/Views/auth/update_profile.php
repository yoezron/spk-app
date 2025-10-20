<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="<?= site_url() ?>" class="navbar-brand navbar-brand-autodark">
            <img src="<?= base_url('assets/img/logo.png') ?>" height="36" alt="SPK">
        </a>
    </div>

    <!-- Progress Steps -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="steps steps-green steps-counter">
                <div class="step-item active">
                    <div class="h4 m-0">1</div>
                    <div>Set Password</div>
                </div>
                <div class="step-item active">
                    <div class="h4 m-0">2</div>
                    <div>Update Profil</div>
                </div>
                <div class="step-item">
                    <div class="h4 m-0">3</div>
                    <div>Selesai</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Lengkapi Profil Anda</h2>

            <div class="alert alert-info mb-4" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-info-circle icon alert-icon"></i>
                    </div>
                    <div>
                        <h4 class="alert-title">Langkah Terakhir!</h4>
                        <div class="text-muted">
                            Silakan review dan lengkapi data profil Anda. Pastikan semua informasi sudah benar sebelum melanjutkan.
                        </div>
                    </div>
                </div>
            </div>

            <form action="<?= site_url('auth/update-profile/' . esc($token)) ?>" method="POST" enctype="multipart/form-data" id="profileForm">
                <?= csrf_field() ?>

                <!-- Photo Upload -->
                <div class="mb-3 text-center">
                    <div class="avatar avatar-xl mb-3" id="photoPreview" style="margin: 0 auto;">
                        <img src="<?= base_url('assets/img/default-avatar.png') ?>" alt="Photo" id="previewImg">
                    </div>
                    <label class="btn btn-sm btn-primary">
                        <i class="ti ti-upload me-1"></i>
                        Upload Foto
                        <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                    </label>
                    <div class="form-hint mt-2">Format: JPG, PNG. Maksimal 2MB.</div>
                </div>

                <hr class="my-4">

                <!-- Personal Info -->
                <h3 class="mb-3">Data Pribadi</h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?= esc($member->full_name) ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?= esc($user->email) ?>" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" value="<?= esc($member->nik ?? '') ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <input type="text" class="form-control" value="<?= esc($member->gender ?? '') ?>" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text"
                            class="form-control"
                            name="birth_place"
                            value="<?= esc($member->birth_place ?? '') ?>"
                            placeholder="Contoh: Jakarta">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date"
                            class="form-control"
                            name="birth_date"
                            value="<?= esc($member->birth_date ?? '') ?>">
                    </div>
                </div>

                <hr class="my-4">

                <!-- Contact Info -->
                <h3 class="mb-3">Kontak</h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Nomor Telepon</label>
                        <input type="text"
                            class="form-control"
                            name="phone"
                            value="<?= esc($member->phone ?? '') ?>"
                            placeholder="08123456789"
                            required>
                        <div class="invalid-feedback">Nomor telepon wajib diisi</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Nomor WhatsApp</label>
                        <input type="text"
                            class="form-control"
                            name="whatsapp"
                            value="<?= esc($member->whatsapp ?? '') ?>"
                            placeholder="08123456789"
                            required>
                        <div class="invalid-feedback">Nomor WhatsApp wajib diisi</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Alamat Lengkap</label>
                    <textarea class="form-control"
                        name="address"
                        rows="3"
                        placeholder="Alamat lengkap sesuai KTP"
                        required><?= esc($member->address ?? '') ?></textarea>
                    <div class="invalid-feedback">Alamat wajib diisi</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" class="form-control" value="<?= esc($member->province_name ?? '') ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Universitas</label>
                        <input type="text" class="form-control" value="<?= esc($member->university_name ?? '') ?>" disabled>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Terms -->
                <div class="mb-3">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" name="confirm_data" required>
                        <span class="form-check-label">
                            Saya menyatakan bahwa data yang saya berikan adalah benar dan dapat dipertanggungjawabkan.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnSubmit">
                        <i class="ti ti-check me-1"></i>
                        Simpan & Selesaikan Aktivasi
                    </button>
                </div>
            </form>

            <div class="text-center text-muted mt-3">
                <small>
                    Data Anda akan tersimpan dengan aman dan hanya digunakan untuk keperluan keanggotaan SPK.
                </small>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Photo upload preview
        $('#photoInput').on('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('Ukuran file maksimal 2MB');
                    $(this).val('');
                    return;
                }

                // Validate file type
                if (!file.type.match('image.*')) {
                    toastr.error('File harus berupa gambar');
                    $(this).val('');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Phone number validation
        $('input[name="phone"], input[name="whatsapp"]').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(value);
        });

        // Form validation
        $('#profileForm').on('submit', function(e) {
            const phone = $('input[name="phone"]').val();
            const whatsapp = $('input[name="whatsapp"]').val();
            const address = $('textarea[name="address"]').val();

            let isValid = true;

            // Validate phone
            if (!phone || phone.length < 10 || phone.length > 15) {
                toastr.error('Nomor telepon harus 10-15 digit');
                $('input[name="phone"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('input[name="phone"]').removeClass('is-invalid');
            }

            // Validate whatsapp
            if (!whatsapp || whatsapp.length < 10 || whatsapp.length > 15) {
                toastr.error('Nomor WhatsApp harus 10-15 digit');
                $('input[name="whatsapp"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('input[name="whatsapp"]').removeClass('is-invalid');
            }

            // Validate address
            if (!address || address.trim().length < 10) {
                toastr.error('Alamat minimal 10 karakter');
                $('textarea[name="address"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('textarea[name="address"]').removeClass('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // Show loading
            const btnSubmit = $('#btnSubmit');
            btnSubmit.prop('disabled', true);
            btnSubmit.html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
        });

        // Auto-fill WhatsApp from phone
        $('input[name="phone"]').on('blur', function() {
            const phone = $(this).val();
            const whatsapp = $('input[name="whatsapp"]').val();

            if (phone && !whatsapp) {
                if (confirm('Gunakan nomor telepon yang sama untuk WhatsApp?')) {
                    $('input[name="whatsapp"]').val(phone);
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>