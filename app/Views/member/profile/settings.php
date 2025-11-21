<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Pengaturan</h1>
            <p class="text-muted">Kelola preferensi dan pengaturan akun Anda</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/profile') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Notification Settings -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">notifications</i>
                    Pengaturan Notifikasi
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('member/profile/update-settings') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="settings_type" value="notifications">

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="email_notifications" value="1" id="email_notifications" checked>
                            <label class="form-check-label" for="email_notifications">
                                <strong>Notifikasi Email</strong>
                                <p class="text-muted mb-0 small">Terima notifikasi penting via email</p>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="forum_notifications" value="1" id="forum_notifications" checked>
                            <label class="form-check-label" for="forum_notifications">
                                <strong>Notifikasi Forum</strong>
                                <p class="text-muted mb-0 small">Notifikasi balasan thread dan mention</p>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="payment_reminders" value="1" id="payment_reminders" checked>
                            <label class="form-check-label" for="payment_reminders">
                                <strong>Pengingat Pembayaran</strong>
                                <p class="text-muted mb-0 small">Pengingat jatuh tempo iuran bulanan</p>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="event_notifications" value="1" id="event_notifications" checked>
                            <label class="form-check-label" for="event_notifications">
                                <strong>Notifikasi Event</strong>
                                <p class="text-muted mb-0 small">Informasi event dan kegiatan SPK</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons-outlined">save</i>
                        Simpan Pengaturan Notifikasi
                    </button>
                </form>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">privacy_tip</i>
                    Pengaturan Privasi
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('member/profile/update-settings') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="settings_type" value="privacy">

                    <div class="mb-3">
                        <label for="profile_visibility" class="form-label">Visibilitas Profil</label>
                        <select class="form-select" id="profile_visibility" name="profile_visibility">
                            <option value="public">Publik - Semua orang dapat melihat</option>
                            <option value="members" selected>Anggota SPK - Hanya anggota</option>
                            <option value="private">Privat - Hanya saya</option>
                        </select>
                        <small class="text-muted">Atur siapa yang dapat melihat profil Anda</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="show_email" value="1" id="show_email">
                            <label class="form-check-label" for="show_email">
                                <strong>Tampilkan Email di Profil</strong>
                                <p class="text-muted mb-0 small">Email akan ditampilkan di profil publik</p>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="show_phone" value="1" id="show_phone">
                            <label class="form-check-label" for="show_phone">
                                <strong>Tampilkan Nomor Telepon</strong>
                                <p class="text-muted mb-0 small">Nomor telepon akan ditampilkan di profil</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons-outlined">save</i>
                        Simpan Pengaturan Privasi
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">manage_accounts</i>
                    Pengaturan Akun
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= base_url('member/profile/change-password') ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="material-icons-outlined me-2">lock</i>
                                <strong>Ubah Password</strong>
                                <p class="text-muted mb-0 small">Kelola keamanan akun Anda</p>
                            </div>
                            <i class="material-icons-outlined">chevron_right</i>
                        </div>
                    </a>
                    <a href="<?= base_url('member/profile/change-email') ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="material-icons-outlined me-2">email</i>
                                <strong>Ubah Email</strong>
                                <p class="text-muted mb-0 small">Perbarui alamat email Anda</p>
                            </div>
                            <i class="material-icons-outlined">chevron_right</i>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger mb-3">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">warning</i>
                    Zona Bahaya
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.</p>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                        <i class="material-icons-outlined">block</i>
                        Nonaktifkan Akun
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">help</i>
                    Bantuan Pengaturan
                </h6>
                <p class="small text-muted">
                    Pengaturan ini membantu Anda mengontrol bagaimana informasi Anda ditampilkan dan digunakan dalam sistem SPK.
                </p>
                <ul class="small mb-0">
                    <li>Notifikasi dapat diatur sesuai preferensi Anda</li>
                    <li>Privasi profil mengontrol visibilitas data Anda</li>
                    <li>Ubah password secara berkala untuk keamanan</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="material-icons-outlined">contact_support</i>
                    Butuh Bantuan?
                </h6>
                <p class="small mb-2">Hubungi kami jika ada pertanyaan:</p>
                <ul class="list-unstyled small mb-0">
                    <li><i class="material-icons-outlined" style="font-size: 14px;">email</i> info@spk.or.id</li>
                    <li><i class="material-icons-outlined" style="font-size: 14px;">phone</i> +62 xxx-xxxx-xxxx</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Account Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Nonaktifkan Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="material-icons-outlined">warning</i>
                    <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
                <p>Jika Anda menonaktifkan akun:</p>
                <ul>
                    <li>Anda tidak akan dapat login lagi</li>
                    <li>Kartu anggota digital akan tidak aktif</li>
                    <li>Data Anda akan tetap tersimpan untuk keperluan administrasi</li>
                    <li>Anda perlu menghubungi pengurus untuk mengaktifkan kembali</li>
                </ul>
                <form action="<?= base_url('member/profile/deactivate') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="deactivate_reason" class="form-label">Alasan (Opsional)</label>
                        <textarea class="form-control" id="deactivate_reason" name="reason" rows="3"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmDeactivate" required>
                        <label class="form-check-label" for="confirmDeactivate">
                            Saya memahami konsekuensi dari menonaktifkan akun
                        </label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger">
                            Nonaktifkan Akun Saya
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
