<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $title ?></h1>
            <p class="text-muted">Upload bukti transfer pembayaran iuran Anda</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/payment') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Existing Payment Warning -->
        <?php if (isset($existingPayment) && $existingPayment): ?>
            <div class="alert alert-warning">
                <i class="material-icons-outlined">warning</i>
                <strong>Perhatian!</strong> Anda sudah memiliki pembayaran untuk bulan ini dengan status
                <span class="badge bg-<?= $existingPayment->status == 'pending' ? 'warning' : 'success' ?>">
                    <?= ucfirst($existingPayment->status) ?>
                </span>.
                Jika ingin mengupload bukti baru, batalkan pembayaran sebelumnya terlebih dahulu.
            </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">upload_file</i>
                    Formulir Upload Bukti Pembayaran
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('member/payment/upload') ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <?= csrf_field() ?>

                    <!-- Payment Type -->
                    <div class="mb-3">
                        <label for="payment_type" class="form-label">
                            Tipe Pembayaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?= session('errors.payment_type') ? 'is-invalid' : '' ?>"
                                id="payment_type"
                                name="payment_type"
                                required
                                onchange="updatePaymentInfo()">
                            <option value="">-- Pilih Tipe Pembayaran --</option>
                            <option value="registration" <?= old('payment_type') == 'registration' ? 'selected' : '' ?>>
                                Registrasi (Rp 100,000)
                            </option>
                            <option value="monthly" <?= old('payment_type') == 'monthly' ? 'selected' : '' ?>>
                                Iuran Bulanan (Rp 50,000)
                            </option>
                            <option value="annual" <?= old('payment_type') == 'annual' ? 'selected' : '' ?>>
                                Iuran Tahunan (Rp 500,000)
                            </option>
                            <option value="donation" <?= old('payment_type') == 'donation' ? 'selected' : '' ?>>
                                Donasi (Sukarela)
                            </option>
                        </select>
                        <?php if (session('errors.payment_type')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.payment_type') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Period (for monthly/annual) -->
                    <div class="row mb-3" id="periodFields" style="display: none;">
                        <div class="col-md-6">
                            <label for="period_month" class="form-label">Bulan Periode</label>
                            <select class="form-select" id="period_month" name="period_month">
                                <?php
                                $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                $currentMonth = old('period_month') ?? date('n');
                                for ($m = 1; $m <= 12; $m++):
                                ?>
                                    <option value="<?= $m ?>" <?= $m == $currentMonth ? 'selected' : '' ?>><?= $months[$m] ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="period_year" class="form-label">Tahun Periode</label>
                            <select class="form-select" id="period_year" name="period_year">
                                <?php
                                $currentYear = old('period_year') ?? date('Y');
                                for ($y = date('Y'); $y >= date('Y') - 2; $y--):
                                ?>
                                    <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label for="amount" class="form-label">
                            Jumlah Pembayaran <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number"
                                   class="form-control <?= session('errors.amount') ? 'is-invalid' : '' ?>"
                                   id="amount"
                                   name="amount"
                                   value="<?= old('amount') ?>"
                                   required
                                   min="1"
                                   step="1000"
                                   placeholder="Masukkan jumlah yang dibayarkan">
                        </div>
                        <?php if (session('errors.amount')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.amount') ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted" id="amountHint">Masukkan jumlah sesuai tipe pembayaran</small>
                    </div>

                    <!-- Payment Date -->
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">
                            Tanggal Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               class="form-control <?= session('errors.payment_date') ? 'is-invalid' : '' ?>"
                               id="payment_date"
                               name="payment_date"
                               value="<?= old('payment_date') ?? date('Y-m-d') ?>"
                               required
                               max="<?= date('Y-m-d') ?>">
                        <?php if (session('errors.payment_date')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.payment_date') ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Tanggal sesuai bukti transfer</small>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">
                            Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?= session('errors.payment_method') ? 'is-invalid' : '' ?>"
                                id="payment_method"
                                name="payment_method"
                                required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="bank_transfer" <?= old('payment_method') == 'bank_transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                            <option value="e-wallet" <?= old('payment_method') == 'e-wallet' ? 'selected' : '' ?>>E-Wallet (GoPay, OVO, Dana, dll)</option>
                            <option value="cash" <?= old('payment_method') == 'cash' ? 'selected' : '' ?>>Tunai</option>
                            <option value="other" <?= old('payment_method') == 'other' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                        <?php if (session('errors.payment_method')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.payment_method') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bank Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">Nama Bank/E-Wallet</label>
                            <input type="text"
                                   class="form-control <?= session('errors.bank_name') ? 'is-invalid' : '' ?>"
                                   id="bank_name"
                                   name="bank_name"
                                   value="<?= old('bank_name') ?>"
                                   placeholder="Contoh: BCA, Mandiri, GoPay">
                            <?php if (session('errors.bank_name')): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.bank_name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                            <input type="text"
                                   class="form-control <?= session('errors.account_name') ? 'is-invalid' : '' ?>"
                                   id="account_name"
                                   name="account_name"
                                   value="<?= old('account_name') ?? $member->full_name ?? '' ?>"
                                   placeholder="Nama sesuai rekening">
                            <?php if (session('errors.account_name')): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.account_name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Account Number -->
                    <div class="mb-3">
                        <label for="account_number" class="form-label">Nomor Rekening/E-Wallet</label>
                        <input type="text"
                               class="form-control"
                               id="account_number"
                               name="account_number"
                               value="<?= old('account_number') ?>"
                               placeholder="Nomor rekening pengirim (opsional)">
                    </div>

                    <!-- Proof File Upload -->
                    <div class="mb-3">
                        <label for="proof_file" class="form-label">
                            Bukti Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               class="form-control <?= session('errors.proof_file') ? 'is-invalid' : '' ?>"
                               id="proof_file"
                               name="proof_file"
                               required
                               accept=".jpg,.jpeg,.png,.pdf"
                               onchange="previewFile()">
                        <?php if (session('errors.proof_file')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.proof_file') ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Format: JPG, PNG, atau PDF. Maksimal 2MB</small>

                        <!-- Preview -->
                        <div id="filePreview" class="mt-2" style="display: none;">
                            <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control <?= session('errors.notes') ? 'is-invalid' : '' ?>"
                                  id="notes"
                                  name="notes"
                                  rows="3"
                                  placeholder="Tambahkan catatan jika diperlukan"><?= old('notes') ?></textarea>
                        <?php if (session('errors.notes')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.notes') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="material-icons-outlined">upload</i>
                            Upload Bukti Pembayaran
                        </button>
                        <a href="<?= base_url('member/payment') ?>" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bank Account Information -->
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">account_balance</i>
                    Rekening SPK untuk Transfer
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-2">Bank BCA</h6>
                        <p class="mb-1"><strong>No. Rekening:</strong> 1234567890</p>
                        <p class="mb-0"><strong>Atas Nama:</strong> Serikat Pekerja Kampus</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-2">Bank Mandiri</h6>
                        <p class="mb-1"><strong>No. Rekening:</strong> 0987654321</p>
                        <p class="mb-0"><strong>Atas Nama:</strong> Serikat Pekerja Kampus</p>
                    </div>
                </div>
                <div class="alert alert-info mb-0 mt-2">
                    <i class="material-icons-outlined">info</i>
                    <strong>Penting:</strong> Pastikan transfer ke rekening yang benar dan simpan bukti transfer dengan baik.
                </div>
            </div>
        </div>

        <!-- Upload Instructions -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">help</i>
                    Petunjuk Upload
                </h6>
                <ol class="mb-0">
                    <li>Lakukan transfer sesuai nominal dan tipe pembayaran</li>
                    <li>Simpan bukti transfer (screenshot atau foto)</li>
                    <li>Upload bukti transfer melalui form di atas</li>
                    <li>Tunggu verifikasi dari pengurus (1-3 hari kerja)</li>
                    <li>Anda akan menerima notifikasi email setelah diverifikasi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Update payment info based on type
    function updatePaymentInfo() {
        const paymentType = document.getElementById('payment_type').value;
        const amountField = document.getElementById('amount');
        const amountHint = document.getElementById('amountHint');
        const periodFields = document.getElementById('periodFields');

        // Show/hide period fields
        if (paymentType === 'monthly' || paymentType === 'annual') {
            periodFields.style.display = 'flex';
        } else {
            periodFields.style.display = 'none';
        }

        // Set suggested amount
        const amounts = {
            'registration': 100000,
            'monthly': 50000,
            'annual': 500000,
            'donation': ''
        };

        if (amounts[paymentType] !== undefined) {
            amountField.value = amounts[paymentType];

            if (paymentType === 'donation') {
                amountHint.textContent = 'Masukkan jumlah donasi sesuai keinginan';
            } else {
                amountHint.textContent = 'Nominal yang disarankan: Rp ' + amounts[paymentType].toLocaleString('id-ID');
            }
        }
    }

    // Preview uploaded file
    function previewFile() {
        const file = document.getElementById('proof_file').files[0];
        const preview = document.getElementById('filePreview');
        const imagePreview = document.getElementById('imagePreview');

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                if (file.type.match('image.*')) {
                    imagePreview.src = e.target.result;
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            };

            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }

    // Form validation
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const paymentType = document.getElementById('payment_type').value;
        const amount = document.getElementById('amount').value;
        const file = document.getElementById('proof_file').files[0];

        // Validate file size
        if (file && file.size > 2 * 1024 * 1024) {
            e.preventDefault();
            alert('Ukuran file maksimal 2MB!');
            return false;
        }

        // Validate amount
        if (!amount || amount <= 0) {
            e.preventDefault();
            alert('Jumlah pembayaran harus lebih dari 0!');
            return false;
        }

        // Confirm submission
        if (!confirm('Apakah Anda yakin data yang diinput sudah benar?')) {
            e.preventDefault();
            return false;
        }
    });
</script>
<?= $this->endSection() ?>
