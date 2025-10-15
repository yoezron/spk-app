<?php

/**
 * View: Admin Member Detail
 * Controller: App\Controllers\Admin\MemberController::show()
 * Description: Comprehensive member detail page dengan complete information dan actions
 * 
 * Features:
 * - Complete member profile display
 * - Photo/avatar preview
 * - Personal information section
 * - Membership information
 * - Academic/Work information
 * - Contact information
 * - Personal statistics cards
 * - Recent activities timeline
 * - Action buttons (Edit, Approve, Reject, Suspend, Activate, Delete)
 * - Document preview (bukti bayar untuk pending members)
 * - Tabs navigation (Profile, Activities, Documents)
 * - Status badges & indicators
 * - Regional scope support
 * - Responsive design (mobile-first)
 * - Permission-based action visibility
 * 
 * @package App\Views\Admin\Members
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">
<!-- Lightbox CSS -->
<link href="<?= base_url('assets/plugins/lightbox/lightbox.min.css') ?>" rel="stylesheet">

<style>
    /* Member Detail Wrapper */
    .member-detail-wrapper {
        padding: 24px;
        background: #f8f9fa;
        min-height: calc(100vh - 80px);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 24px 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        color: white;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
    }

    .page-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .back-button:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
    }

    .page-title-section h1 {
        font-size: 24px;
        font-weight: 700;
        margin: 8px 0 4px 0;
        color: white;
    }

    .page-title-section p {
        font-size: 14px;
        opacity: 0.95;
        margin: 0;
    }

    /* Main Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    /* Profile Card (Left Sidebar) */
    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
        position: sticky;
        top: 24px;
        height: fit-content;
    }

    .profile-avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #f7fafc;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .profile-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
    }

    .profile-status-indicator {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 3px solid white;
        background: #48bb78;
    }

    .profile-status-indicator.pending {
        background: #f6ad55;
    }

    .profile-status-indicator.suspended {
        background: #f56565;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .profile-email {
        font-size: 14px;
        color: #718096;
        margin-bottom: 8px;
    }

    .profile-member-number {
        display: inline-block;
        background: #f7fafc;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        color: #4a5568;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .profile-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 24px;
    }

    .profile-status-badge.active {
        background: #c6f6d5;
        color: #22543d;
    }

    .profile-status-badge.pending {
        background: #feebc8;
        color: #7c2d12;
    }

    .profile-status-badge.suspended {
        background: #fed7d7;
        color: #742a2a;
    }

    /* Profile Stats */
    .profile-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
        padding-top: 24px;
        border-top: 2px solid #e2e8f0;
    }

    .profile-stat {
        text-align: center;
    }

    .profile-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #667eea;
        display: block;
    }

    .profile-stat-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* Action Buttons */
    .profile-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-top: 24px;
        border-top: 2px solid #e2e8f0;
    }

    .profile-actions .btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
    }

    /* Main Content Area */
    .main-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Tabs Navigation */
    .tabs-nav {
        background: white;
        border-radius: 12px;
        padding: 8px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 8px;
        overflow-x: auto;
    }

    .tab-button {
        flex: 1;
        min-width: 150px;
        padding: 14px 20px;
        border: none;
        background: transparent;
        color: #718096;
        font-weight: 600;
        font-size: 14px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .tab-button:hover {
        background: #f7fafc;
        color: #667eea;
    }

    .tab-button.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .tab-button i {
        font-size: 20px;
    }

    /* Tab Content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Info Sections */
    .info-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .info-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .info-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-section-title i {
        color: #667eea;
        font-size: 24px;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-label {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        color: #2d3748;
        font-weight: 500;
    }

    .info-value.empty {
        color: #a0aec0;
        font-style: italic;
    }

    .info-value a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    .info-value a:hover {
        text-decoration: underline;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary {
        border-left-color: #667eea;
    }

    .stat-card.success {
        border-left-color: #48bb78;
    }

    .stat-card.warning {
        border-left-color: #f6ad55;
    }

    .stat-card.info {
        border-left-color: #4299e1;
    }

    .stat-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 12px;
    }

    .stat-card.primary .stat-card-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.success .stat-card-icon {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }

    .stat-card.warning .stat-card-icon {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    }

    .stat-card.info .stat-card-icon {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }

    .stat-card-label {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .stat-card-value {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
    }

    /* Activities Timeline */
    .activities-timeline {
        position: relative;
    }

    .activity-item {
        position: relative;
        padding-left: 50px;
        margin-bottom: 28px;
        padding-bottom: 28px;
        border-bottom: 1px solid #e2e8f0;
    }

    .activity-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 36px;
        bottom: -28px;
        width: 2px;
        background: #e2e8f0;
    }

    .activity-item:last-child::before {
        display: none;
    }

    .activity-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-size: 15px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .activity-description {
        font-size: 14px;
        color: #718096;
        margin-bottom: 6px;
    }

    .activity-time {
        font-size: 12px;
        color: #a0aec0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Document Preview */
    .document-preview {
        background: #f7fafc;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 2px dashed #cbd5e0;
    }

    .document-preview img {
        max-width: 100%;
        max-height: 400px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .document-preview img:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .document-preview-label {
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        margin-top: 12px;
    }

    /* Alert Box */
    .alert-box {
        background: white;
        border-left: 4px solid;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .alert-box.warning {
        border-left-color: #f6ad55;
        background: #fffaf0;
    }

    .alert-box.info {
        border-left-color: #4299e1;
        background: #ebf8ff;
    }

    .alert-box.success {
        border-left-color: #48bb78;
        background: #f0fff4;
    }

    .alert-box i {
        font-size: 24px;
        flex-shrink: 0;
    }

    .alert-box.warning i {
        color: #dd6b20;
    }

    .alert-box.info i {
        color: #2b6cb0;
    }

    .alert-box.success i {
        color: #2f855a;
    }

    .alert-box-content {
        flex: 1;
    }

    .alert-box-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #2d3748;
    }

    .alert-box-text {
        font-size: 13px;
        color: #4a5568;
        margin: 0;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 18px;
        font-weight: 700;
        color: #718096;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .profile-card {
            position: relative;
            top: 0;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .member-detail-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 20px;
        }

        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .tabs-nav {
            flex-direction: column;
        }

        .tab-button {
            min-width: 100%;
        }

        .profile-actions {
            flex-direction: column;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="member-detail-wrapper">

    <!-- Page Header -->
    <div class="page-header">
        <a href="<?= base_url('admin/members') ?>" class="back-button">
            <i class="material-icons-outlined">arrow_back</i>
            Kembali ke Daftar
        </a>

        <div class="page-header-content">
            <div class="page-title-section">
                <h1>Detail Anggota</h1>
                <p>Informasi lengkap dan riwayat aktivitas anggota</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Pending Member Alert -->
    <?php if (isset($member) && $member->membership_status === 'calon_anggota'): ?>
        <div class="alert-box warning">
            <i class="material-icons-outlined">pending</i>
            <div class="alert-box-content">
                <div class="alert-box-title">Calon Anggota - Menunggu Approval</div>
                <p class="alert-box-text">
                    Anggota ini mendaftar pada <?= date('d M Y H:i', strtotime($member->created_at)) ?>.
                    Silakan review data dan dokumen sebelum menyetujui keanggotaan.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Suspended Member Alert -->
    <?php if (isset($member) && $member->membership_status === 'tidak_aktif'): ?>
        <div class="alert-box warning">
            <i class="material-icons-outlined">block</i>
            <div class="alert-box-content">
                <div class="alert-box-title">Anggota Ditangguhkan (Suspended)</div>
                <p class="alert-box-text">
                    Anggota ini saat ini dalam status tidak aktif dan tidak dapat mengakses sistem.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($member)): ?>

        <!-- Content Grid -->
        <div class="content-grid">

            <!-- Profile Card (Left Sidebar) -->
            <div class="profile-card">
                <div class="profile-avatar-wrapper">
                    <img
                        src="<?= !empty($member->photo) ? base_url('uploads/photos/' . $member->photo) : base_url('assets/images/avatars/avatar.png') ?>"
                        alt="Profile Photo"
                        class="profile-avatar"
                        data-lightbox="member-photo"
                        data-title="<?= esc($member->full_name) ?>">
                    <?php
                    $statusClass = 'suspended';
                    if ($member->membership_status === 'aktif') {
                        $statusClass = 'active';
                    } elseif ($member->membership_status === 'calon_anggota') {
                        $statusClass = 'pending';
                    }
                    ?>
                    <div class="profile-status-indicator <?= $statusClass ?>"></div>
                </div>

                <div class="profile-name"><?= esc($member->full_name) ?></div>
                <div class="profile-email"><?= esc($member->email) ?></div>

                <?php if (!empty($member->member_number)): ?>
                    <div class="profile-member-number">
                        <?= esc($member->member_number) ?>
                    </div>
                <?php endif; ?>

                <?php
                $badgeClass = 'suspended';
                $badgeIcon = 'block';
                $badgeText = 'Tidak Aktif';

                if ($member->membership_status === 'aktif') {
                    $badgeClass = 'active';
                    $badgeIcon = 'check_circle';
                    $badgeText = 'Aktif';
                } elseif ($member->membership_status === 'calon_anggota') {
                    $badgeClass = 'pending';
                    $badgeIcon = 'pending';
                    $badgeText = 'Pending Approval';
                }
                ?>
                <div class="profile-status-badge <?= $badgeClass ?>">
                    <i class="material-icons-outlined"><?= $badgeIcon ?></i>
                    <?= $badgeText ?>
                </div>

                <!-- Profile Stats -->
                <div class="profile-stats">
                    <div class="profile-stat">
                        <span class="profile-stat-value"><?= $stats['forum_posts'] ?? 0 ?></span>
                        <span class="profile-stat-label">Forum Posts</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-value"><?= $stats['surveys_completed'] ?? 0 ?></span>
                        <span class="profile-stat-label">Surveys</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-value"><?= $stats['complaints'] ?? 0 ?></span>
                        <span class="profile-stat-label">Complaints</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-value">
                            <?php
                            $joinDate = strtotime($member->join_date ?? $member->created_at);
                            $days = floor((time() - $joinDate) / (60 * 60 * 24));
                            echo $days;
                            ?>
                        </span>
                        <span class="profile-stat-label">Days Member</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="profile-actions">

                    <?php if ($member->membership_status === 'calon_anggota' && auth()->user()->can('member.approve')): ?>
                        <!-- Approve Button -->
                        <button type="button" class="btn btn-success" onclick="approveMember(<?= $member->id ?>)">
                            <i class="material-icons-outlined">check_circle</i>
                            Approve Anggota
                        </button>

                        <!-- Reject Button -->
                        <button type="button" class="btn btn-danger" onclick="rejectMember(<?= $member->id ?>)">
                            <i class="material-icons-outlined">cancel</i>
                            Reject
                        </button>
                    <?php endif; ?>

                    <?php if (auth()->user()->can('member.edit')): ?>
                        <!-- Edit Button -->
                        <a href="<?= base_url('admin/members/edit/' . $member->id) ?>" class="btn btn-primary">
                            <i class="material-icons-outlined">edit</i>
                            Edit Profile
                        </a>
                    <?php endif; ?>

                    <?php if (auth()->user()->can('member.suspend')): ?>
                        <?php if ($member->membership_status === 'aktif'): ?>
                            <!-- Suspend Button -->
                            <button type="button" class="btn btn-warning" onclick="suspendMember(<?= $member->id ?>)">
                                <i class="material-icons-outlined">block</i>
                                Suspend Anggota
                            </button>
                        <?php elseif ($member->membership_status === 'tidak_aktif'): ?>
                            <!-- Activate Button -->
                            <button type="button" class="btn btn-success" onclick="activateMember(<?= $member->id ?>)">
                                <i class="material-icons-outlined">check_circle</i>
                                Aktifkan Kembali
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (auth()->user()->can('member.delete')): ?>
                        <!-- Delete Button -->
                        <button type="button" class="btn btn-outline-danger" onclick="deleteMember(<?= $member->id ?>)">
                            <i class="material-icons-outlined">delete</i>
                            Hapus Anggota
                        </button>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">

                <!-- Tabs Navigation -->
                <div class="tabs-nav">
                    <button class="tab-button active" data-tab="profile">
                        <i class="material-icons-outlined">person</i>
                        <span>Profil</span>
                    </button>
                    <button class="tab-button" data-tab="statistics">
                        <i class="material-icons-outlined">bar_chart</i>
                        <span>Statistik</span>
                    </button>
                    <button class="tab-button" data-tab="activities">
                        <i class="material-icons-outlined">history</i>
                        <span>Aktivitas</span>
                    </button>
                    <?php if ($member->membership_status === 'calon_anggota'): ?>
                        <button class="tab-button" data-tab="documents">
                            <i class="material-icons-outlined">description</i>
                            <span>Dokumen</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Tab Content: Profile -->
                <div class="tab-content active" id="profile-tab">

                    <!-- Personal Information -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">person</i>
                                Informasi Personal
                            </h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Nama Lengkap</div>
                                <div class="info-value"><?= esc($member->full_name) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <a href="mailto:<?= esc($member->email) ?>"><?= esc($member->email) ?></a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Nomor Telepon</div>
                                <div class="info-value <?= empty($member->phone) ? 'empty' : '' ?>">
                                    <?= !empty($member->phone) ? esc($member->phone) : 'Belum diisi' ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tempat, Tanggal Lahir</div>
                                <div class="info-value <?= empty($member->birth_place) ? 'empty' : '' ?>">
                                    <?php
                                    if (!empty($member->birth_place) && !empty($member->birth_date)) {
                                        echo esc($member->birth_place) . ', ' . date('d M Y', strtotime($member->birth_date));
                                    } else {
                                        echo 'Belum diisi';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Jenis Kelamin</div>
                                <div class="info-value <?= empty($member->gender) ? 'empty' : '' ?>">
                                    <?php
                                    if (!empty($member->gender)) {
                                        echo $member->gender === 'L' ? 'Laki-laki' : 'Perempuan';
                                    } else {
                                        echo 'Belum diisi';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">NIK</div>
                                <div class="info-value <?= empty($member->nik) ? 'empty' : '' ?>">
                                    <?= !empty($member->nik) ? esc($member->nik) : 'Belum diisi' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">location_on</i>
                                Informasi Alamat
                            </h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item" style="grid-column: 1 / -1;">
                                <div class="info-label">Alamat Lengkap</div>
                                <div class="info-value <?= empty($member->address) ? 'empty' : '' ?>">
                                    <?= !empty($member->address) ? esc($member->address) : 'Belum diisi' ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Provinsi</div>
                                <div class="info-value"><?= esc($member->province_name ?? '-') ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Kabupaten/Kota</div>
                                <div class="info-value"><?= esc($member->regency_name ?? '-') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic/Work Information -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">school</i>
                                Informasi Akademik & Pekerjaan
                            </h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Perguruan Tinggi</div>
                                <div class="info-value"><?= esc($member->university_name ?? '-') ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Program Studi</div>
                                <div class="info-value <?= empty($member->study_program_name) ? 'empty' : '' ?>">
                                    <?= !empty($member->study_program_name) ? esc($member->study_program_name) : 'Belum diisi' ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Jabatan/Posisi</div>
                                <div class="info-value <?= empty($member->position) ? 'empty' : '' ?>">
                                    <?= !empty($member->position) ? esc($member->position) : 'Belum diisi' ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status Kepegawaian</div>
                                <div class="info-value <?= empty($member->employment_status) ? 'empty' : '' ?>">
                                    <?= !empty($member->employment_status) ? esc($member->employment_status) : 'Belum diisi' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Information -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">card_membership</i>
                                Informasi Keanggotaan
                            </h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Nomor Anggota</div>
                                <div class="info-value <?= empty($member->member_number) ? 'empty' : '' ?>">
                                    <?= !empty($member->member_number) ? esc($member->member_number) : 'Belum diberikan' ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status Keanggotaan</div>
                                <div class="info-value">
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <i class="material-icons-outlined"><?= $badgeIcon ?></i>
                                        <?= $badgeText ?>
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tanggal Bergabung</div>
                                <div class="info-value">
                                    <?php
                                    $joinDate = $member->join_date ?? $member->created_at;
                                    echo date('d F Y', strtotime($joinDate));
                                    ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tanggal Registrasi</div>
                                <div class="info-value">
                                    <?= date('d F Y H:i', strtotime($member->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tab Content: Statistics -->
                <div class="tab-content" id="statistics-tab">

                    <div class="stats-grid">

                        <div class="stat-card primary">
                            <div class="stat-card-icon">
                                <i class="material-icons-outlined">forum</i>
                            </div>
                            <div class="stat-card-label">Forum Posts</div>
                            <div class="stat-card-value"><?= number_format($stats['forum_posts'] ?? 0) ?></div>
                        </div>

                        <div class="stat-card success">
                            <div class="stat-card-icon">
                                <i class="material-icons-outlined">poll</i>
                            </div>
                            <div class="stat-card-label">Surveys Completed</div>
                            <div class="stat-card-value"><?= number_format($stats['surveys_completed'] ?? 0) ?></div>
                        </div>

                        <div class="stat-card warning">
                            <div class="stat-card-icon">
                                <i class="material-icons-outlined">support_agent</i>
                            </div>
                            <div class="stat-card-label">Complaints</div>
                            <div class="stat-card-value"><?= number_format($stats['complaints'] ?? 0) ?></div>
                        </div>

                        <div class="stat-card info">
                            <div class="stat-card-icon">
                                <i class="material-icons-outlined">calendar_today</i>
                            </div>
                            <div class="stat-card-label">Days as Member</div>
                            <div class="stat-card-value">
                                <?php
                                $joinDate = strtotime($member->join_date ?? $member->created_at);
                                $days = floor((time() - $joinDate) / (60 * 60 * 24));
                                echo number_format($days);
                                ?>
                            </div>
                        </div>

                    </div>

                    <!-- Additional Stats Info -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">analytics</i>
                                Rincian Statistik
                            </h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Total Forum Threads</div>
                                <div class="info-value"><?= number_format($stats['forum_threads'] ?? 0) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Open Complaints</div>
                                <div class="info-value"><?= number_format($stats['open_complaints'] ?? 0) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Closed Complaints</div>
                                <div class="info-value"><?= number_format($stats['closed_complaints'] ?? 0) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Last Activity</div>
                                <div class="info-value">
                                    <?php
                                    if (!empty($stats['last_activity'])) {
                                        echo date('d M Y H:i', strtotime($stats['last_activity']));
                                    } else {
                                        echo 'Belum ada aktivitas';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tab Content: Activities -->
                <div class="tab-content" id="activities-tab">

                    <div class="info-section">
                        <div class="info-section-header">
                            <h3 class="info-section-title">
                                <i class="material-icons-outlined">history</i>
                                Riwayat Aktivitas Terbaru
                            </h3>
                        </div>

                        <div class="activities-timeline">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="material-icons-outlined">
                                                <?= $activity['icon'] ?? 'circle' ?>
                                            </i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?= esc($activity['title']) ?></div>
                                            <div class="activity-description"><?= esc($activity['description']) ?></div>
                                            <div class="activity-time">
                                                <i class="material-icons-outlined" style="font-size: 14px;">schedule</i>
                                                <?= esc($activity['time']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="material-icons-outlined">history</i>
                                    <h3>Belum Ada Aktivitas</h3>
                                    <p>Anggota ini belum memiliki riwayat aktivitas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Tab Content: Documents (for pending members) -->
                <?php if ($member->membership_status === 'calon_anggota'): ?>
                    <div class="tab-content" id="documents-tab">

                        <div class="info-section">
                            <div class="info-section-header">
                                <h3 class="info-section-title">
                                    <i class="material-icons-outlined">description</i>
                                    Dokumen Pendaftaran
                                </h3>
                            </div>

                            <!-- Photo -->
                            <?php if (!empty($member->photo)): ?>
                                <div class="document-preview" style="margin-bottom: 24px;">
                                    <img
                                        src="<?= base_url('uploads/photos/' . $member->photo) ?>"
                                        alt="Foto Profile"
                                        data-lightbox="documents"
                                        data-title="Foto Profil - <?= esc($member->full_name) ?>">
                                    <div class="document-preview-label">Foto Profil</div>
                                </div>
                            <?php endif; ?>

                            <!-- Payment Proof -->
                            <?php if (!empty($member->payment_proof)): ?>
                                <div class="document-preview">
                                    <img
                                        src="<?= base_url('uploads/payment_proofs/' . $member->payment_proof) ?>"
                                        alt="Bukti Pembayaran"
                                        data-lightbox="documents"
                                        data-title="Bukti Pembayaran - <?= esc($member->full_name) ?>">
                                    <div class="document-preview-label">Bukti Pembayaran Iuran Pertama</div>
                                </div>
                            <?php else: ?>
                                <div class="alert-box warning">
                                    <i class="material-icons-outlined">warning</i>
                                    <div class="alert-box-content">
                                        <div class="alert-box-title">Bukti Pembayaran Belum Diupload</div>
                                        <p class="alert-box-text">
                                            Calon anggota belum mengupload bukti pembayaran iuran pertama.
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>

                    </div>
                <?php endif; ?>

            </div>

        </div>

    <?php else: ?>

        <!-- Member Not Found -->
        <div class="info-section">
            <div class="empty-state">
                <i class="material-icons-outlined">person_off</i>
                <h3>Data Anggota Tidak Ditemukan</h3>
                <p>Anggota yang Anda cari tidak ditemukan atau telah dihapus</p>
                <a href="<?= base_url('admin/members') ?>" class="btn btn-primary mt-3">
                    Kembali ke Daftar Anggota
                </a>
            </div>
        </div>

    <?php endif; ?>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
<!-- Lightbox JS -->
<script src="<?= base_url('assets/plugins/lightbox/lightbox.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Tab Navigation
        $('.tab-button').on('click', function() {
            const targetTab = $(this).data('tab');

            // Update button states
            $('.tab-button').removeClass('active');
            $(this).addClass('active');

            // Update content
            $('.tab-content').removeClass('active');
            $('#' + targetTab + '-tab').addClass('active');
        });

        // Lightbox Configuration
        lightbox.option({
            'resizeDuration': 300,
            'wrapAround': true,
            'albumLabel': 'Gambar %1 dari %2'
        });

    });

    // Approve Member
    function approveMember(id) {
        Swal.fire({
            title: 'Approve Anggota?',
            text: 'Anggota akan disetujui dan dapat mengakses sistem penuh',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Approve!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/members/approve/') ?>${id}`;
            }
        });
    }

    // Reject Member
    function rejectMember(id) {
        Swal.fire({
            title: 'Reject Anggota?',
            text: 'Anggota akan ditolak dan tidak dapat bergabung',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Reject!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/members/reject/') ?>${id}`;
            }
        });
    }

    // Suspend Member
    function suspendMember(id) {
        Swal.fire({
            title: 'Suspend Anggota?',
            text: 'Anggota akan ditangguhkan dan tidak dapat mengakses sistem',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Suspend!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/members/suspend/') ?>${id}`;
            }
        });
    }

    // Activate Member
    function activateMember(id) {
        Swal.fire({
            title: 'Aktifkan Anggota?',
            text: 'Anggota akan diaktifkan kembali dan dapat mengakses sistem',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/members/activate/') ?>${id}`;
            }
        });
    }

    // Delete Member
    function deleteMember(id) {
        Swal.fire({
            title: 'Hapus Anggota?',
            html: '<strong>PERINGATAN!</strong><br>Data anggota akan dihapus permanen beserta seluruh riwayat aktivitasnya.<br><br>Tindakan ini TIDAK DAPAT dibatalkan!',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            input: 'checkbox',
            inputPlaceholder: 'Saya memahami konsekuensinya',
            inputValidator: (result) => {
                return !result && 'Anda harus mencentang checkbox untuk melanjutkan'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('admin/members/delete/') ?>${id}`;
            }
        });
    }
</script>
<?= $this->endSection() ?>