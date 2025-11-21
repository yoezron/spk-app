<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg">
                <div class="card-body text-center py-5">
                    <?php if (isset($verification) && $verification['success']): ?>
                        <!-- Successful Verification -->
                        <div class="mb-4">
                            <i class="material-icons-outlined text-success" style="font-size: 96px;">verified</i>
                        </div>
                        <h2 class="text-success mb-3">Kartu Terverifikasi!</h2>
                        <p class="lead mb-4"><?= esc($verification['message'] ?? 'Kartu anggota valid dan terverifikasi') ?></p>

                        <?php if (isset($member)): ?>
                            <div class="card bg-light mt-4">
                                <div class="card-body text-start">
                                    <h5 class="card-title mb-3">Informasi Anggota</h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Nama</small>
                                            <p class="mb-2"><strong><?= esc($member->full_name ?? 'N/A') ?></strong></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">No. Anggota</small>
                                            <p class="mb-2"><strong><?= esc($member->member_number ?? 'N/A') ?></strong></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Universitas</small>
                                            <p class="mb-2"><?= esc($member->university_name ?? 'N/A') ?></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Provinsi</small>
                                            <p class="mb-2"><?= esc($member->province_name ?? 'N/A') ?></p>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted">Status</small>
                                            <p class="mb-0">
                                                <span class="badge bg-success">Aktif</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (isset($member->card_expiry_date)): ?>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="material-icons-outlined" style="font-size: 14px;">event</i>
                                        Berlaku hingga: <?= date('d F Y', strtotime($member->card_expiry_date)) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Failed Verification -->
                        <div class="mb-4">
                            <i class="material-icons-outlined text-danger" style="font-size: 96px;">cancel</i>
                        </div>
                        <h2 class="text-danger mb-3">Verifikasi Gagal</h2>
                        <p class="lead mb-4">
                            <?= esc($verification['message'] ?? 'Kartu anggota tidak valid atau sudah kadaluarsa') ?>
                        </p>

                        <div class="alert alert-warning text-start">
                            <h6 class="alert-heading">Kemungkinan Penyebab:</h6>
                            <ul class="mb-0">
                                <li>QR Code sudah kadaluarsa</li>
                                <li>Kartu anggota sudah tidak aktif</li>
                                <li>Link verifikasi tidak valid</li>
                                <li>Status keanggotaan belum diverifikasi</li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="mt-4 d-grid gap-2">
                        <a href="<?= base_url() ?>" class="btn btn-primary">
                            <i class="material-icons-outlined">home</i>
                            Kembali ke Beranda
                        </a>
                        <?php if (!isset($verification) || !$verification['success']): ?>
                            <a href="<?= base_url('contact') ?>" class="btn btn-outline-secondary">
                                <i class="material-icons-outlined">help</i>
                                Hubungi Pengurus
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Information Box -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="material-icons-outlined">info</i>
                        Tentang Verifikasi Kartu
                    </h6>
                    <p class="small mb-0">
                        Sistem verifikasi kartu digital SPK menggunakan QR Code yang terenkripsi untuk memastikan
                        keaslian kartu anggota. Jika Anda mengalami masalah dalam verifikasi, silakan hubungi
                        pengurus SPK melalui email di <a href="mailto:info@spk.or.id">info@spk.or.id</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Log verification attempt
    console.log('Card verification page loaded');
    <?php if (isset($verification) && $verification['success']): ?>
        console.log('Verification successful');
    <?php else: ?>
        console.log('Verification failed');
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
