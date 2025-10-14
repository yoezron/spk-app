<?php

/**
 * View: Member Profile Index
 * Controller: Member\ProfileController
 * Description: Halaman profil anggota dengan informasi lengkap
 * 
 * Features:
 * - Profile header dengan avatar
 * - Personal information section
 * - Employment information section
 * - Membership information section
 * - Contact information section
 * - Account information section
 * - Edit profile & change password buttons
 * - Beautiful card layout
 * - Responsive design
 * 
 * @package App\Views\Member\Profile
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .profile-header .container-fluid {
        position: relative;
        z-index: 1;
    }

    .profile-avatar-section {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 24px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid rgba(255, 255, 255, 0.3);
        background: white;
    }

    .profile-info h2 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .profile-info .member-number {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .profile-info .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.pending {
        background: #feebc8;
        color: #7c2d12;
    }

    .status-badge.inactive {
        background: #fed7d7;
        color: #742a2a;
    }

    .profile-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .profile-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-light {
        background: white;
        color: #667eea;
        border: none;
    }

    .btn-light:hover {
        background: #f7fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-outline-light {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .btn-outline-light:hover {
        background: white;
        color: #667eea;
        transform: translateY(-2px);
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .info-card-header i {
        font-size: 28px;
        color: #667eea;
    }

    .info-card-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .info-row {
        display: flex;
        padding: 16px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-label {
        flex: 0 0 200px;
        font-weight: 600;
        color: #718096;
        font-size: 14px;
    }

    .info-value {
        flex: 1;
        color: #2d3748;
        font-size: 15px;
    }

    .info-value.empty {
        color: #a0aec0;
        font-style: italic;
    }

    /* Stats Grid */
    .stats-mini-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-mini-card {
        background: #f7fafc;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .stat-mini-card:hover {
        border-color: #667eea;
        background: white;
        transform: translateY(-2px);
    }

    .stat-mini-card i {
        font-size: 32px;
        color: #667eea;
        margin-bottom: 8px;
    }

    .stat-mini-card .value {
        font-size: 24px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .stat-mini-card .label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* Verification Badge */
    .verification-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
    }

    .verification-badge.verified {
        background: #c6f6d5;
        color: #22543d;
    }

    .verification-badge.not-verified {
        background: #fed7d7;
        color: #742a2a;
    }

    /* Alert Box */
    .alert-profile {
        background: #fff5e6;
        border-left: 4px solid #f6ad55;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .alert-profile .alert-icon {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-profile i {
        font-size: 24px;
        color: #ed8936;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-profile .alert-content {
        flex: 1;
    }

    .alert-profile h5 {
        font-size: 16px;
        font-weight: 700;
        color: #7c2d12;
        margin-bottom: 4px;
    }

    .alert-profile p {
        font-size: 14px;
        color: #92400e;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .profile-avatar-section {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-actions {
            justify-content: center;
        }

        .info-label {
            flex: 0 0 150px;
        }
    }

    @media (max-width: 767px) {
        .profile-header {
            padding: 30px 20px;
        }

        .profile-info h2 {
            font-size: 24px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
        }

        .info-card {
            padding: 20px;
        }

        .info-row {
            flex-direction: column;
            gap: 8px;
        }

        .info-label {
            flex: none;
        }

        .stats-mini-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="container-fluid">
        <div class="profile-avatar-section">
            <img src="<?= !empty($member->photo) ? esc($member->photo) : base_url('assets/images/default-avatar.png') ?>"
                alt="<?= esc($member->full_name) ?>"
                class="profile-avatar">

            <div class="profile-info">
                <h2><?= esc($member->full_name) ?></h2>

                <?php if (!empty($member->member_number)): ?>
                    <div class="member-number">
                        <i class="bi bi-person-badge"></i>
                        <span><?= esc($member->member_number) ?></span>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <span class="status-badge <?= $member->membership_status === 'active' ? 'active' : ($member->membership_status === 'calon_anggota' ? 'pending' : 'inactive') ?>">
                        <i class="bi bi-<?= $member->membership_status === 'active' ? 'check-circle' : ($member->membership_status === 'calon_anggota' ? 'hourglass-split' : 'x-circle') ?>"></i>
                        <?= $member->membership_status === 'active' ? 'Anggota Aktif' : ($member->membership_status === 'calon_anggota' ? 'Calon Anggota' : 'Tidak Aktif') ?>
                    </span>

                    <?php if ($user->active): ?>
                        <span class="verification-badge verified ms-2">
                            <i class="bi bi-patch-check-fill"></i>
                            Email Terverifikasi
                        </span>
                    <?php endif; ?>
                </div>

                <div class="profile-actions">
                    <a href="<?= base_url('member/profile/edit') ?>" class="btn btn-light">
                        <i class="bi bi-pencil-square"></i>
                        Edit Profil
                    </a>
                    <a href="<?= base_url('member/profile/change-password') ?>" class="btn btn-outline-light">
                        <i class="bi bi-shield-lock"></i>
                        Ubah Password
                    </a>
                    <a href="<?= base_url('member/card') ?>" class="btn btn-outline-light">
                        <i class="bi bi-credit-card-2-front"></i>
                        Kartu Anggota
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Profile Incomplete Alert -->
<?php if (empty($member->phone) || empty($member->address)): ?>
    <div class="alert-profile">
        <div class="alert-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div class="alert-content">
                <h5>Profil Belum Lengkap</h5>
                <p>Lengkapi profil Anda untuk mendapatkan akses penuh ke semua fitur. Klik tombol "Edit Profil" untuk melengkapi data.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Mini Stats -->
<div class="stats-mini-grid">
    <div class="stat-mini-card">
        <i class="bi bi-calendar-check"></i>
        <div class="value"><?= date('Y', strtotime($user->created_at)) ?></div>
        <div class="label">Tahun Bergabung</div>
    </div>

    <div class="stat-mini-card">
        <i class="bi bi-geo-alt-fill"></i>
        <div class="value"><?= esc($province->name ?? '-') ?></div>
        <div class="label">Wilayah</div>
    </div>

    <div class="stat-mini-card">
        <i class="bi bi-building"></i>
        <div class="value"><?= !empty($university->name) ? (strlen($university->name) > 20 ? substr($university->name, 0, 20) . '...' : $university->name) : '-' ?></div>
        <div class="label">Kampus</div>
    </div>

    <div class="stat-mini-card">
        <i class="bi bi-briefcase-fill"></i>
        <div class="value"><?= esc($member->employment_type ?? '-') ?></div>
        <div class="label">Status Kerja</div>
    </div>
</div>

<div class="row">
    <!-- Personal Information -->
    <div class="col-lg-6">
        <div class="info-card">
            <div class="info-card-header">
                <i class="bi bi-person-circle"></i>
                <h3>Informasi Pribadi</h3>
            </div>

            <div class="info-row">
                <div class="info-label">Nama Lengkap</div>
                <div class="info-value"><?= esc($member->full_name) ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">NIK</div>
                <div class="info-value <?= empty($member->nik) ? 'empty' : '' ?>">
                    <?= !empty($member->nik) ? esc($member->nik) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Tempat, Tanggal Lahir</div>
                <div class="info-value <?= empty($member->birth_place) ? 'empty' : '' ?>">
                    <?php if (!empty($member->birth_place) && !empty($member->birth_date)): ?>
                        <?= esc($member->birth_place) ?>, <?= date('d F Y', strtotime($member->birth_date)) ?>
                    <?php else: ?>
                        Belum diisi
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Jenis Kelamin</div>
                <div class="info-value <?= empty($member->gender) ? 'empty' : '' ?>">
                    <?= !empty($member->gender) ? ($member->gender === 'L' ? 'Laki-laki' : 'Perempuan') : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Agama</div>
                <div class="info-value <?= empty($member->religion) ? 'empty' : '' ?>">
                    <?= !empty($member->religion) ? esc($member->religion) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Status Pernikahan</div>
                <div class="info-value <?= empty($member->marital_status) ? 'empty' : '' ?>">
                    <?= !empty($member->marital_status) ? esc($member->marital_status) : 'Belum diisi' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="col-lg-6">
        <div class="info-card">
            <div class="info-card-header">
                <i class="bi bi-telephone-fill"></i>
                <h3>Informasi Kontak</h3>
            </div>

            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">
                    <?= esc($user->email) ?>
                    <?php if ($user->active): ?>
                        <span class="badge bg-success ms-2">Terverifikasi</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">No. Telepon</div>
                <div class="info-value <?= empty($member->phone) ? 'empty' : '' ?>">
                    <?= !empty($member->phone) ? esc($member->phone) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Alamat</div>
                <div class="info-value <?= empty($member->address) ? 'empty' : '' ?>">
                    <?= !empty($member->address) ? nl2br(esc($member->address)) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Provinsi</div>
                <div class="info-value"><?= esc($province->name ?? '-') ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Kabupaten/Kota</div>
                <div class="info-value"><?= esc($regency->name ?? '-') ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Kode Pos</div>
                <div class="info-value <?= empty($member->postal_code) ? 'empty' : '' ?>">
                    <?= !empty($member->postal_code) ? esc($member->postal_code) : 'Belum diisi' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Employment Information -->
    <div class="col-lg-6">
        <div class="info-card">
            <div class="info-card-header">
                <i class="bi bi-building"></i>
                <h3>Informasi Pekerjaan</h3>
            </div>

            <div class="info-row">
                <div class="info-label">Kampus</div>
                <div class="info-value"><?= esc($university->name ?? '-') ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Program Studi</div>
                <div class="info-value <?= empty($study_program->name) ? 'empty' : '' ?>">
                    <?= !empty($study_program->name) ? esc($study_program->name) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Status Kepegawaian</div>
                <div class="info-value <?= empty($member->employment_type) ? 'empty' : '' ?>">
                    <?= !empty($member->employment_type) ? esc($member->employment_type) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Jabatan</div>
                <div class="info-value <?= empty($member->position) ? 'empty' : '' ?>">
                    <?= !empty($member->position) ? esc($member->position) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">NIP/NIDN</div>
                <div class="info-value <?= empty($member->employee_id) ? 'empty' : '' ?>">
                    <?= !empty($member->employee_id) ? esc($member->employee_id) : 'Belum diisi' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Tanggal Mulai Bekerja</div>
                <div class="info-value <?= empty($member->join_date) ? 'empty' : '' ?>">
                    <?= !empty($member->join_date) ? date('d F Y', strtotime($member->join_date)) : 'Belum diisi' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Membership Information -->
    <div class="col-lg-6">
        <div class="info-card">
            <div class="info-card-header">
                <i class="bi bi-patch-check-fill"></i>
                <h3>Informasi Keanggotaan</h3>
            </div>

            <div class="info-row">
                <div class="info-label">Nomor Anggota</div>
                <div class="info-value">
                    <strong><?= esc($member->member_number ?? '-') ?></strong>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Status Keanggotaan</div>
                <div class="info-value">
                    <span class="status-badge <?= $member->membership_status === 'active' ? 'active' : ($member->membership_status === 'calon_anggota' ? 'pending' : 'inactive') ?>">
                        <i class="bi bi-<?= $member->membership_status === 'active' ? 'check-circle' : ($member->membership_status === 'calon_anggota' ? 'hourglass-split' : 'x-circle') ?>"></i>
                        <?= $member->membership_status === 'active' ? 'Aktif' : ($member->membership_status === 'calon_anggota' ? 'Calon Anggota' : 'Tidak Aktif') ?>
                    </span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Tanggal Bergabung</div>
                <div class="info-value">
                    <?= date('d F Y', strtotime($user->created_at)) ?>
                </div>
            </div>

            <?php if (!empty($member->approved_at)): ?>
                <div class="info-row">
                    <div class="info-label">Tanggal Disetujui</div>
                    <div class="info-value">
                        <?= date('d F Y', strtotime($member->approved_at)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($member->approved_by_name)): ?>
                <div class="info-row">
                    <div class="info-label">Disetujui Oleh</div>
                    <div class="info-value">
                        <?= esc($member->approved_by_name) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="info-row">
                <div class="info-label">Masa Berlaku Kartu</div>
                <div class="info-value <?= empty($member->card_expiry) ? 'empty' : '' ?>">
                    <?= !empty($member->card_expiry) ? date('d F Y', strtotime($member->card_expiry)) : 'Tidak terbatas' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Information -->
<div class="info-card">
    <div class="info-card-header">
        <i class="bi bi-shield-lock-fill"></i>
        <h3>Informasi Akun</h3>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="info-row">
                <div class="info-label">Username</div>
                <div class="info-value"><?= esc($user->username) ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Status Akun</div>
                <div class="info-value">
                    <?php if ($user->active): ?>
                        <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Tidak Aktif</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-row">
                <div class="info-label">Terakhir Login</div>
                <div class="info-value">
                    <?= !empty($user->last_active) ? date('d F Y, H:i', strtotime($user->last_active)) . ' WIB' : 'Belum pernah login' ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Akun Dibuat</div>
                <div class="info-value">
                    <?= date('d F Y, H:i', strtotime($user->created_at)) ?> WIB
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.info-card, .stat-mini-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>
<?= $this->endSection() ?>