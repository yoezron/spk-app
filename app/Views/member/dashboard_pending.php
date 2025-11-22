<?php

/**
 * View: Member Dashboard Pending (Calon Anggota)
 * Controller: Member\DashboardController::index()
 * Description: Dashboard untuk calon anggota yang menampilkan status pendaftaran
 *
 * Features:
 * - Status pendaftaran (pending/waiting approval)
 * - Timeline langkah-langkah yang perlu dilakukan
 * - Informasi kontak pengurus
 * - Link ke dokumen SPK
 * - Warning/info boxes
 *
 * @package App\Views\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Status Card */
    .status-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 32px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
    }

    .status-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 40px;
    }

    .status-title {
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        margin-bottom: 12px;
    }

    .status-subtitle {
        font-size: 16px;
        text-align: center;
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* Info Box */
    .info-box {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .info-box-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f1f3f5;
    }

    .info-box-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .info-box-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -28px;
        top: 8px;
        bottom: -22px;
        width: 2px;
        background: #e3e6f0;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: -38px;
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .timeline-marker.completed {
        background: #48bb78;
    }

    .timeline-marker.pending {
        background: #f6ad55;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    .timeline-title {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 6px;
    }

    .timeline-description {
        font-size: 14px;
        color: #6c757d;
        margin: 0;
    }

    /* Alert Box */
    .alert-custom {
        background: white;
        border-left: 4px solid;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .alert-custom.warning {
        border-left-color: #f6ad55;
        background: #fffaf0;
    }

    .alert-custom.info {
        border-left-color: #4299e1;
        background: #ebf8ff;
    }

    .alert-custom i {
        font-size: 24px;
        flex-shrink: 0;
    }

    .alert-custom.warning i {
        color: #dd6b20;
    }

    .alert-custom.info i {
        color: #2b6cb0;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #2c3e50;
    }

    .alert-text {
        font-size: 13px;
        color: #495057;
        margin: 0;
    }

    /* Contact Card */
    .contact-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 12px;
        padding: 24px;
        color: white;
        text-align: center;
    }

    .contact-card h4 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .contact-card p {
        font-size: 14px;
        opacity: 0.95;
        margin-bottom: 16px;
    }

    .contact-card .btn {
        background: white;
        color: #f5576c;
        font-weight: 600;
    }

    /* Quick Links */
    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    .quick-link-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .quick-link-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        text-decoration: none;
        color: inherit;
    }

    .quick-link-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }

    .quick-link-content h5 {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 4px 0;
        color: #2c3e50;
    }

    .quick-link-content p {
        font-size: 13px;
        color: #6c757d;
        margin: 0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Status Card -->
    <div class="status-card">
        <div class="status-icon">
            <i class="material-icons-outlined">pending_actions</i>
        </div>
        <h2 class="status-title">Pendaftaran Anda Sedang Diproses</h2>
        <p class="status-subtitle">
            Terima kasih telah mendaftar sebagai anggota SPK. Pengurus sedang memverifikasi data Anda.
        </p>
    </div>

    <!-- Alert Info -->
    <div class="alert-custom info">
        <i class="material-icons-outlined">info</i>
        <div class="alert-content">
            <div class="alert-title">Informasi Penting</div>
            <p class="alert-text">
                Proses verifikasi biasanya memakan waktu 1-3 hari kerja. Anda akan mendapat notifikasi melalui
                email ketika keanggotaan Anda telah disetujui. Pastikan untuk mengecek email Anda secara berkala.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Timeline Progress -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-box-icon">
                        <i class="material-icons-outlined">timeline</i>
                    </div>
                    <h3 class="info-box-title">Timeline Pendaftaran</h3>
                </div>

                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker completed">
                            <i class="material-icons-outlined" style="font-size: 14px;">check</i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Registrasi Berhasil</div>
                            <p class="timeline-description">
                                Anda telah berhasil mendaftar dan mengisi formulir pendaftaran.
                                Tanggal: <?= date('d F Y', strtotime($member->created_at ?? 'now')) ?>
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker pending">
                            <i class="material-icons-outlined" style="font-size: 14px;">pending</i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Verifikasi Data oleh Pengurus</div>
                            <p class="timeline-description">
                                Pengurus sedang memverifikasi data dan dokumen yang Anda kirimkan.
                                Proses ini biasanya memakan waktu 1-3 hari kerja.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <i class="material-icons-outlined" style="font-size: 14px;">schedule</i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Persetujuan Keanggotaan</div>
                            <p class="timeline-description">
                                Setelah data terverifikasi, Anda akan mendapat email konfirmasi dan
                                dapat mengakses seluruh fitur portal anggota.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <i class="material-icons-outlined" style="font-size: 14px;">badge</i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Aktivasi Akun & Kartu Anggota</div>
                            <p class="timeline-description">
                                Akun Anda akan diaktifkan dan Anda dapat mengakses kartu anggota digital,
                                forum diskusi, survei, dan fitur lainnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Status -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-box-icon">
                        <i class="material-icons-outlined">person</i>
                    </div>
                    <h3 class="info-box-title">Informasi Profil Anda</h3>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block mb-1">Nama Lengkap</small>
                        <strong><?= esc($member->full_name ?? '-') ?></strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block mb-1">Email</small>
                        <strong><?= esc($user->email ?? '-') ?></strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block mb-1">Perguruan Tinggi</small>
                        <strong><?= esc($member->university_name ?? '-') ?></strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block mb-1">Status Keanggotaan</small>
                        <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="material-icons-outlined align-middle me-2">info</i>
                    <small>
                        Jika ada data yang perlu diubah, silakan hubungi pengurus melalui kontak di bawah ini.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Contact Card -->
            <div class="contact-card mb-4">
                <h4>Butuh Bantuan?</h4>
                <p>Hubungi pengurus jika Anda memiliki pertanyaan atau butuh bantuan</p>
                <a href="mailto:info@spk.org" class="btn btn-light">
                    <i class="material-icons-outlined align-middle" style="font-size: 18px;">email</i>
                    Hubungi Kami
                </a>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-box-icon">
                        <i class="material-icons-outlined">tips_and_updates</i>
                    </div>
                    <h3 class="info-box-title">Yang Dapat Anda Lakukan</h3>
                </div>

                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="material-icons-outlined text-success me-2">check_circle</i>
                        <small>Lihat dan edit profil Anda</small>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="material-icons-outlined text-success me-2">check_circle</i>
                        <small>Baca Manifesto SPK</small>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="material-icons-outlined text-success me-2">check_circle</i>
                        <small>Pelajari AD/ART</small>
                    </li>
                    <li class="mb-0 d-flex align-items-start">
                        <i class="material-icons-outlined text-success me-2">check_circle</i>
                        <small>Baca sejarah SPK</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="<?= base_url('member/profile') ?>" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="material-icons-outlined">person</i>
            </div>
            <div class="quick-link-content">
                <h5>Profil Saya</h5>
                <p>Lihat dan edit informasi profil</p>
            </div>
        </a>

        <a href="<?= base_url('manifesto') ?>" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="material-icons-outlined">article</i>
            </div>
            <div class="quick-link-content">
                <h5>Manifesto SPK</h5>
                <p>Baca manifesto Serikat Pekerja Kampus</p>
            </div>
        </a>

        <a href="<?= base_url('adart') ?>" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="material-icons-outlined">gavel</i>
            </div>
            <div class="quick-link-content">
                <h5>AD/ART</h5>
                <p>Anggaran Dasar & Anggaran Rumah Tangga</p>
            </div>
        </a>

        <a href="<?= base_url('sejarah') ?>" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="material-icons-outlined">history_edu</i>
            </div>
            <div class="quick-link-content">
                <h5>Sejarah SPK</h5>
                <p>Pelajari sejarah Serikat Pekerja Kampus</p>
            </div>
        </a>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Auto-refresh page every 5 minutes to check for status update
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    });
</script>
<?= $this->endSection() ?>
