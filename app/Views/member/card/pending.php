<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title"><?= $pageTitle ?></h1>
    <p class="text-muted">Kartu anggota digital Serikat Pekerja Kampus</p>
</div>

<!-- Pending Verification Message -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card text-center">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="material-icons-outlined text-warning" style="font-size: 96px;">pending</i>
                </div>

                <h3 class="mb-3">Kartu Anggota Belum Tersedia</h3>

                <p class="lead text-muted mb-4">
                    Kartu anggota digital Anda belum dapat diakses karena status keanggotaan Anda masih dalam proses verifikasi.
                </p>

                <div class="alert alert-warning text-start">
                    <h6 class="alert-heading">
                        <i class="material-icons-outlined">info</i>
                        Status Saat Ini
                    </h6>
                    <p class="mb-2">
                        <strong>Status Keanggotaan:</strong> Calon Anggota (Menunggu Verifikasi)
                    </p>
                    <p class="mb-0">
                        Kartu anggota digital akan tersedia setelah data Anda diverifikasi dan disetujui oleh pengurus.
                    </p>
                </div>

                <div class="card bg-light mt-4">
                    <div class="card-body text-start">
                        <h6 class="card-title">Langkah Selanjutnya:</h6>
                        <ol class="mb-0">
                            <li class="mb-2">Pastikan semua data profil Anda sudah lengkap dan benar</li>
                            <li class="mb-2">Tunggu pengurus melakukan verifikasi terhadap data Anda</li>
                            <li class="mb-2">Anda akan menerima notifikasi email ketika status keanggotaan berubah</li>
                            <li>Setelah disetujui, kartu digital akan otomatis tersedia di halaman ini</li>
                        </ol>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="<?= base_url('member/profile') ?>" class="btn btn-primary me-2">
                        <i class="material-icons-outlined">person</i>
                        Lihat Profil Saya
                    </a>
                    <a href="<?= base_url('member/dashboard') ?>" class="btn btn-outline-secondary">
                        <i class="material-icons-outlined">dashboard</i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">contact_support</i>
                    Butuh Bantuan?
                </h6>
                <p class="mb-2">
                    Jika Anda memiliki pertanyaan tentang proses verifikasi atau kartu anggota, silakan hubungi:
                </p>
                <ul class="list-unstyled mb-0">
                    <li><i class="material-icons-outlined" style="font-size: 16px;">email</i> Email: <a href="mailto:info@spk.or.id">info@spk.or.id</a></li>
                    <li><i class="material-icons-outlined" style="font-size: 16px;">phone</i> Telepon: +62 xxx-xxxx-xxxx</li>
                    <li><i class="material-icons-outlined" style="font-size: 16px;">help</i> <a href="<?= base_url('member/help') ?>">Pusat Bantuan</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
