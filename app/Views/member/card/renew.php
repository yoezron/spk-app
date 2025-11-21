<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $pageTitle ?></h1>
            <p class="text-muted">Perpanjangan masa aktif kartu anggota</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/card') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Kartu
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Card Status -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar avatar-lg">
                            <i class="material-icons-outlined text-<?= $cardStatus['class'] ?>" style="font-size: 48px;">
                                <?= $cardStatus['status'] == 'expired' ? 'error' : 'warning' ?>
                            </i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">Status Kartu: <span class="badge bg-<?= $cardStatus['class'] ?>"><?= $cardStatus['label'] ?></span></h5>
                        <p class="mb-1"><?= $cardStatus['message'] ?></p>
                        <?php if (isset($cardStatus['expiration_date'])): ?>
                            <small class="text-muted">
                                <i class="material-icons-outlined" style="font-size: 14px;">event</i>
                                Tanggal Kadaluarsa: <?= $cardStatus['expiration_date'] ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Renewal Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">info</i>
                    Tentang Perpanjangan Kartu
                </h5>
            </div>
            <div class="card-body">
                <h6 class="mb-3">Persyaratan Perpanjangan:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="material-icons-outlined text-success" style="font-size: 18px;">check_circle</i>
                        Status keanggotaan masih aktif
                    </li>
                    <li class="mb-2">
                        <i class="material-icons-outlined text-success" style="font-size: 18px;">check_circle</i>
                        Tidak ada tunggakan iuran
                    </li>
                    <li class="mb-2">
                        <i class="material-icons-outlined text-success" style="font-size: 18px;">check_circle</i>
                        Data profil masih valid dan up-to-date
                    </li>
                    <li class="mb-2">
                        <i class="material-icons-outlined text-success" style="font-size: 18px;">check_circle</i>
                        Kartu lama sudah/akan kadaluarsa
                    </li>
                </ul>

                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading">
                        <i class="material-icons-outlined">schedule</i>
                        Proses Perpanjangan
                    </h6>
                    <ol class="mb-0 ps-3">
                        <li>Ajukan permintaan perpanjangan melalui form di bawah</li>
                        <li>Pengurus akan meninjau permintaan Anda (1-3 hari kerja)</li>
                        <li>Jika disetujui, kartu baru akan otomatis tersedia</li>
                        <li>Anda akan menerima notifikasi via email</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Renewal Request Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">send</i>
                    Form Permintaan Perpanjangan
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('member/card/submit-renewal') ?>" method="POST" id="renewalForm">
                    <?= csrf_field() ?>

                    <!-- Member Info Display -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?= esc($member->full_name) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Anggota</label>
                            <input type="text" class="form-control" value="<?= esc($member->member_number) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Universitas</label>
                            <input type="text" class="form-control" value="<?= esc($member->university_name ?? 'N/A') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="text" class="form-control"
                                   value="<?= !empty($member->join_date) ? date('d F Y', strtotime($member->join_date)) : 'N/A' ?>"
                                   readonly>
                        </div>
                    </div>

                    <hr>

                    <!-- Additional Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4"
                                  placeholder="Tambahkan catatan jika diperlukan, misalnya perubahan data atau informasi penting lainnya..."></textarea>
                        <small class="text-muted">Jika ada perubahan data, mohon sebutkan di sini</small>
                    </div>

                    <!-- Confirmation -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmData" required>
                        <label class="form-check-label" for="confirmData">
                            Saya menyatakan bahwa data yang tertera di atas masih valid dan benar
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmPayment" required>
                        <label class="form-check-label" for="confirmPayment">
                            Saya menyatakan tidak memiliki tunggakan iuran
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="material-icons-outlined">send</i>
                            Ajukan Permintaan Perpanjangan
                        </button>
                        <a href="<?= base_url('member/card') ?>" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="alert alert-warning mt-3">
            <h6 class="alert-heading">
                <i class="material-icons-outlined">priority_high</i>
                Penting untuk Diperhatikan
            </h6>
            <ul class="mb-0 ps-3">
                <li>Pastikan data profil Anda sudah diperbarui sebelum mengajukan perpanjangan</li>
                <li>Jika ada tunggakan iuran, silakan selesaikan terlebih dahulu</li>
                <li>Perpanjangan kartu dapat diajukan maksimal 30 hari sebelum masa aktif berakhir</li>
                <li>Hubungi pengurus jika ada kendala dalam proses perpanjangan</li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">contact_support</i>
                    Butuh Bantuan?
                </h6>
                <p class="mb-2">Hubungi pengurus jika Anda memiliki pertanyaan:</p>
                <ul class="list-unstyled mb-0">
                    <li><i class="material-icons-outlined" style="font-size: 16px;">email</i> Email: <a href="mailto:info@spk.or.id">info@spk.or.id</a></li>
                    <li><i class="material-icons-outlined" style="font-size: 16px;">phone</i> Telepon: +62 xxx-xxxx-xxxx</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Form validation
    document.getElementById('renewalForm').addEventListener('submit', function(e) {
        const confirmData = document.getElementById('confirmData').checked;
        const confirmPayment = document.getElementById('confirmPayment').checked;

        if (!confirmData || !confirmPayment) {
            e.preventDefault();
            alert('Harap centang semua pernyataan konfirmasi untuk melanjutkan.');
            return false;
        }

        // Confirm submission
        if (!confirm('Apakah Anda yakin ingin mengajukan permintaan perpanjangan kartu?')) {
            e.preventDefault();
            return false;
        }
    });
</script>
<?= $this->endSection() ?>
