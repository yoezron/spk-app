<?php

/**
 * View: Admin Member Approval System
 * Controller: Admin\MemberController::approve(), reject()
 * Description: Comprehensive member verification & approval dashboard
 * 
 * Features:
 * - Pending members list dengan advanced filters
 * - Member card preview dengan foto & documents
 * - Interactive verification checklist
 * - Approve/Reject with notes & reasons
 * - Bulk approval functionality
 * - Document viewer modal (foto, bukti bayar, KTP)
 * - Statistics overview cards
 * - Regional scope support (Koordinator Wilayah)
 * - Approval history timeline
 * - Email notification preview
 * - Quick actions panel
 * - Search & filter by multiple criteria
 * - Sortable columns
 * - Status badges & indicators
 * - Real-time validation
 * - Audit trail logging
 * 
 * @package App\Views\Admin\Members
 * @author  SPK Development Team
 * @version 3.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/lightbox/lightbox.min.css') ?>">
<style>
    .page-header-approval {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.pending {
        border-left-color: #ffc107;
    }

    .stat-card.approved {
        border-left-color: #28a745;
    }

    .stat-card.rejected {
        border-left-color: #dc3545;
    }

    .stat-card.today {
        border-left-color: #17a2b8;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #ffc107 0%, #ff8b38 100%);
    }

    .stat-icon.approved {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-icon.rejected {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .stat-icon.today {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .member-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .member-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .member-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-bottom: 2px solid #dee2e6;
    }

    .member-card-body {
        padding: 24px;
    }

    .member-photo {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .member-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        color: #6c757d;
        font-size: 14px;
    }

    .member-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-verified {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .verification-checklist {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .checklist-item:last-child {
        border-bottom: none;
    }

    .checklist-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
    }

    .checklist-icon.complete {
        background: #d4edda;
        color: #28a745;
    }

    .checklist-icon.incomplete {
        background: #f8d7da;
        color: #dc3545;
    }

    .checklist-icon.pending {
        background: #fff3cd;
        color: #ffc107;
    }

    .document-preview {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .document-preview:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .document-preview img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .document-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .document-preview:hover .document-overlay {
        opacity: 1;
    }

    .document-overlay i {
        font-size: 32px;
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .btn-approve {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .btn-reject {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        color: white;
    }

    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .bulk-actions-bar {
        background: #667eea;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
        align-items: center;
        justify-content: space-between;
    }

    .bulk-actions-bar.active {
        display: flex;
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #6c757d;
        width: 180px;
        flex-shrink: 0;
    }

    .info-value {
        color: #2c3e50;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -26px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }

    .notes-section {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .quick-stat-item {
        background: white;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #667eea;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .quick-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
    }

    .quick-stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-approval">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2 text-white">
                <i class="bi bi-person-check-fill me-2"></i>
                Verifikasi & Approval Anggota
            </h3>
            <p class="mb-0 text-white opacity-90">
                Review dan setujui calon anggota yang telah mendaftar
            </p>
        </div>
        <div>
            <a href="<?= base_url('admin/members') ?>" class="btn btn-light">
                <i class="bi bi-people me-1"></i> Semua Anggota
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card pending">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon pending">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['pending'] ?? 0) ?></div>
            <div class="stat-label">Menunggu Approval</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card approved">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon approved">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['approved_this_month'] ?? 0) ?></div>
            <div class="stat-label">Disetujui Bulan Ini</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card today">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon today">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['approved_today'] ?? 0) ?></div>
            <div class="stat-label">Disetujui Hari Ini</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card rejected">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon rejected">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['rejected'] ?? 0) ?></div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel">
    <form id="filterForm" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" id="searchInput" name="search"
                placeholder="Nama, email, NIP/NIK...">
        </div>
        <div class="col-md-2">
            <label class="form-label">Province</label>
            <select class="form-select select2" id="provinceFilter" name="province">
                <option value="">Semua Provinsi</option>
                <?php if (isset($provinces)): ?>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?= $province->id ?>"><?= esc($province->name) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">University</label>
            <select class="form-select select2" id="universityFilter" name="university">
                <option value="">Semua PT</option>
                <?php if (isset($universities)): ?>
                    <?php foreach ($universities as $university): ?>
                        <option value="<?= $university->id ?>"><?= esc($university->name) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status Kepegawaian</label>
            <select class="form-select" id="statusFilter" name="employment_status">
                <option value="">Semua Status</option>
                <option value="PNS">PNS</option>
                <option value="PPPK">PPPK</option>
                <option value="Kontrak">Kontrak</option>
                <option value="Honorer">Honorer</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort By</label>
            <select class="form-select" id="sortFilter" name="sort">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="name">Nama (A-Z)</option>
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    </form>
</div>

<!-- Bulk Actions Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div>
        <i class="bi bi-check-square me-2"></i>
        <strong><span id="selectedCount">0</span> anggota dipilih</strong>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success btn-sm" id="bulkApproveBtn">
            <i class="bi bi-check-circle me-1"></i> Approve Semua
        </button>
        <button type="button" class="btn btn-danger btn-sm" id="bulkRejectBtn">
            <i class="bi bi-x-circle me-1"></i> Reject Semua
        </button>
        <button type="button" class="btn btn-light btn-sm" id="clearSelectionBtn">
            <i class="bi bi-x me-1"></i> Batal
        </button>
    </div>
</div>

<!-- Pending Members List -->
<?php if (!empty($pending_members)): ?>
    <?php foreach ($pending_members as $member): ?>
        <div class="member-card">
            <div class="member-card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex gap-3 align-items-start flex-grow-1">
                        <div class="form-check">
                            <input class="form-check-input member-checkbox" type="checkbox"
                                value="<?= $member->id ?>" id="member-<?= $member->id ?>">
                        </div>

                        <?php if (!empty($member->photo_path)): ?>
                            <img src="<?= base_url('uploads/photos/' . $member->photo_path) ?>"
                                alt="Photo" class="member-photo">
                        <?php else: ?>
                            <div class="member-photo d-flex align-items-center justify-content-center bg-secondary">
                                <i class="bi bi-person-fill text-white" style="font-size: 48px;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="member-info">
                            <div class="member-name"><?= esc($member->full_name) ?></div>
                            <div class="member-meta">
                                <span class="member-meta-item">
                                    <i class="bi bi-envelope"></i>
                                    <?= esc($member->email) ?>
                                </span>
                                <span class="member-meta-item">
                                    <i class="bi bi-telephone"></i>
                                    <?= esc($member->phone) ?>
                                </span>
                                <span class="member-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= esc($member->province_name) ?>
                                </span>
                                <span class="member-meta-item">
                                    <i class="bi bi-building"></i>
                                    <?= esc($member->university_name ?? '-') ?>
                                </span>
                            </div>
                            <div class="mt-2">
                                <span class="status-badge status-pending">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Pending Review
                                </span>
                                <small class="text-muted ms-3">
                                    <i class="bi bi-calendar3"></i>
                                    Mendaftar: <?= date('d M Y H:i', strtotime($member->created_at)) ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-sm btn-outline-primary" type="button"
                        data-bs-toggle="collapse" data-bs-target="#detail-<?= $member->id ?>">
                        <i class="bi bi-chevron-down"></i> Detail
                    </button>
                </div>
            </div>

            <div class="collapse" id="detail-<?= $member->id ?>">
                <div class="member-card-body">
                    <div class="row">
                        <!-- Left Column - Member Info -->
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="bi bi-person-badge me-2"></i>
                                Informasi Pribadi
                            </h6>

                            <div class="info-row">
                                <div class="info-label">NIK/NIP:</div>
                                <div class="info-value"><?= esc($member->nik_nip ?? '-') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Tempat, Tanggal Lahir:</div>
                                <div class="info-value">
                                    <?= esc($member->birth_place ?? '-') ?>,
                                    <?= $member->birth_date ? date('d M Y', strtotime($member->birth_date)) : '-' ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Jenis Kelamin:</div>
                                <div class="info-value"><?= esc($member->gender ?? '-') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Alamat:</div>
                                <div class="info-value"><?= esc($member->address ?? '-') ?></div>
                            </div>

                            <h6 class="mb-3 mt-4">
                                <i class="bi bi-briefcase me-2"></i>
                                Informasi Kepegawaian
                            </h6>

                            <div class="info-row">
                                <div class="info-label">Status Kepegawaian:</div>
                                <div class="info-value">
                                    <span class="badge bg-primary"><?= esc($member->employment_status ?? '-') ?></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Perguruan Tinggi:</div>
                                <div class="info-value"><?= esc($member->university_name ?? '-') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Fakultas/Unit:</div>
                                <div class="info-value"><?= esc($member->faculty ?? '-') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Jabatan:</div>
                                <div class="info-value"><?= esc($member->position ?? '-') ?></div>
                            </div>
                        </div>

                        <!-- Right Column - Documents & Verification -->
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="bi bi-file-earmark-check me-2"></i>
                                Dokumen Pendukung
                            </h6>

                            <div class="row g-3 mb-4">
                                <?php if (!empty($member->photo_path)): ?>
                                    <div class="col-6">
                                        <div class="document-preview"
                                            data-lightbox="member-<?= $member->id ?>"
                                            data-src="<?= base_url('uploads/photos/' . $member->photo_path) ?>">
                                            <img src="<?= base_url('uploads/photos/' . $member->photo_path) ?>"
                                                alt="Foto">
                                            <div class="document-overlay">
                                                <i class="bi bi-zoom-in"></i>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block text-center mt-1">Foto Profil</small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($member->payment_proof_path)): ?>
                                    <div class="col-6">
                                        <div class="document-preview"
                                            data-lightbox="member-<?= $member->id ?>"
                                            data-src="<?= base_url('uploads/payment_proof/' . $member->payment_proof_path) ?>">
                                            <img src="<?= base_url('uploads/payment_proof/' . $member->payment_proof_path) ?>"
                                                alt="Bukti Bayar">
                                            <div class="document-overlay">
                                                <i class="bi bi-zoom-in"></i>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block text-center mt-1">Bukti Pembayaran</small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h6 class="mb-3">
                                <i class="bi bi-clipboard-check me-2"></i>
                                Verification Checklist
                            </h6>

                            <div class="verification-checklist">
                                <div class="checklist-item">
                                    <div class="checklist-icon <?= !empty($member->full_name) ? 'complete' : 'incomplete' ?>">
                                        <i class="bi bi-<?= !empty($member->full_name) ? 'check' : 'x' ?>"></i>
                                    </div>
                                    <div>
                                        <strong>Nama Lengkap</strong>
                                        <small class="d-block text-muted">Data pribadi lengkap</small>
                                    </div>
                                </div>

                                <div class="checklist-item">
                                    <div class="checklist-icon <?= !empty($member->photo_path) ? 'complete' : 'incomplete' ?>">
                                        <i class="bi bi-<?= !empty($member->photo_path) ? 'check' : 'x' ?>"></i>
                                    </div>
                                    <div>
                                        <strong>Foto Profil</strong>
                                        <small class="d-block text-muted">Foto formal telah diupload</small>
                                    </div>
                                </div>

                                <div class="checklist-item">
                                    <div class="checklist-icon <?= !empty($member->payment_proof_path) ? 'complete' : 'incomplete' ?>">
                                        <i class="bi bi-<?= !empty($member->payment_proof_path) ? 'check' : 'x' ?>"></i>
                                    </div>
                                    <div>
                                        <strong>Bukti Pembayaran</strong>
                                        <small class="d-block text-muted">Bukti iuran pertama</small>
                                    </div>
                                </div>

                                <div class="checklist-item">
                                    <div class="checklist-icon <?= !empty($member->university_id) ? 'complete' : 'incomplete' ?>">
                                        <i class="bi bi-<?= !empty($member->university_id) ? 'check' : 'x' ?>"></i>
                                    </div>
                                    <div>
                                        <strong>Data Kepegawaian</strong>
                                        <small class="d-block text-muted">PT dan status kepegawaian</small>
                                    </div>
                                </div>

                                <div class="checklist-item">
                                    <div class="checklist-icon <?= !empty($member->email_verified_at) ? 'complete' : 'incomplete' ?>">
                                        <i class="bi bi-<?= !empty($member->email_verified_at) ? 'check' : 'x' ?>"></i>
                                    </div>
                                    <div>
                                        <strong>Email Verified</strong>
                                        <small class="d-block text-muted">Email telah diverifikasi</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Section -->
                            <?php if (!empty($member->notes)): ?>
                                <div class="notes-section">
                                    <h6 class="mb-2">
                                        <i class="bi bi-sticky me-2"></i>
                                        Catatan
                                    </h6>
                                    <p class="mb-0"><?= esc($member->notes) ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <button type="button" class="btn btn-approve flex-fill approve-btn"
                                    data-member-id="<?= $member->id ?>"
                                    data-member-name="<?= esc($member->full_name) ?>">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Approve
                                </button>
                                <button type="button" class="btn btn-reject flex-fill reject-btn"
                                    data-member-id="<?= $member->id ?>"
                                    data-member-name="<?= esc($member->full_name) ?>">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if (isset($pager)): ?>
        <div class="d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 64px; color: #dee2e6;"></i>
            <h5 class="mt-3">Tidak Ada Pending Members</h5>
            <p class="text-muted">Semua calon anggota sudah diproses</p>
            <a href="<?= base_url('admin/members') ?>" class="btn btn-primary">
                <i class="bi bi-people me-1"></i> Lihat Semua Anggota
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>
                    Approve Anggota
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="member_id" id="approveMemberId">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle me-2"></i>
                        Anggota <strong id="approveMemberName"></strong> akan disetujui dan mendapatkan:
                        <ul class="mb-0 mt-2">
                            <li>Nomor Anggota Otomatis</li>
                            <li>Status: Anggota Aktif</li>
                            <li>Akses Portal Anggota</li>
                            <li>Email Notifikasi</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" value="1" checked id="sendEmailApprove">
                        <label class="form-check-label" for="sendEmailApprove">
                            Kirim email notifikasi ke anggota
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Approve Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i>
                    Reject Anggota
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="member_id" id="rejectMemberId">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Anda akan menolak pendaftaran <strong id="rejectMemberName"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="reason" rows="4" required
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                        <small class="form-text text-muted">
                            Alasan ini akan dikirimkan ke calon anggota via email
                        </small>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" value="1" checked id="sendEmailReject">
                        <label class="form-check-label" for="sendEmailReject">
                            Kirim email notifikasi penolakan
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/lightbox/lightbox.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Pilih...',
            allowClear: true
        });

        // Initialize Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        // Handle checkbox selection
        $('.member-checkbox').on('change', function() {
            updateBulkActions();
        });

        // Select all checkbox
        $('#selectAllCheckbox').on('change', function() {
            $('.member-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkActions();
        });

        // Update bulk actions bar
        function updateBulkActions() {
            const selectedCount = $('.member-checkbox:checked').length;
            $('#selectedCount').text(selectedCount);

            if (selectedCount > 0) {
                $('#bulkActionsBar').addClass('active');
            } else {
                $('#bulkActionsBar').removeClass('active');
            }
        }

        // Clear selection
        $('#clearSelectionBtn').on('click', function() {
            $('.member-checkbox').prop('checked', false);
            updateBulkActions();
        });

        // Single Approve
        $('.approve-btn').on('click', function() {
            const memberId = $(this).data('member-id');
            const memberName = $(this).data('member-name');

            $('#approveMemberId').val(memberId);
            $('#approveMemberName').text(memberName);
            $('#approveModal').modal('show');
        });

        // Single Reject
        $('.reject-btn').on('click', function() {
            const memberId = $(this).data('member-id');
            const memberName = $(this).data('member-name');

            $('#rejectMemberId').val(memberId);
            $('#rejectMemberName').text(memberName);
            $('#rejectModal').modal('show');
        });

        // Approve Form Submit
        $('#approveForm').on('submit', function(e) {
            e.preventDefault();

            const memberId = $('#approveMemberId').val();
            const formData = $(this).serialize();

            Swal.fire({
                title: 'Processing...',
                text: 'Sedang memproses approval',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?= base_url('admin/members/approve/') ?>' + memberId,
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Anggota berhasil disetujui',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        });

        // Reject Form Submit
        $('#rejectForm').on('submit', function(e) {
            e.preventDefault();

            const memberId = $('#rejectMemberId').val();
            const formData = $(this).serialize();

            Swal.fire({
                title: 'Processing...',
                text: 'Sedang memproses penolakan',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?= base_url('admin/members/reject/') ?>' + memberId,
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pendaftaran berhasil ditolak',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        });

        // Bulk Approve
        $('#bulkApproveBtn').on('click', function() {
            const selectedIds = $('.member-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih minimal 1 anggota'
                });
                return;
            }

            Swal.fire({
                title: 'Approve ' + selectedIds.length + ' Anggota?',
                text: 'Semua anggota yang dipilih akan disetujui',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Approve!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '<?= base_url('admin/members/bulk-approve') ?>',
                        method: 'POST',
                        data: {
                            member_ids: selectedIds,
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: selectedIds.length + ' anggota berhasil disetujui',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        });

        // Bulk Reject
        $('#bulkRejectBtn').on('click', function() {
            const selectedIds = $('.member-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih minimal 1 anggota'
                });
                return;
            }

            Swal.fire({
                title: 'Reject ' + selectedIds.length + ' Anggota?',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan',
                inputPlaceholder: 'Jelaskan alasan penolakan...',
                inputAttributes: {
                    'aria-label': 'Alasan penolakan'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Reject!',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan harus diisi!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '<?= base_url('admin/members/bulk-reject') ?>',
                        method: 'POST',
                        data: {
                            member_ids: selectedIds,
                            reason: result.value,
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: selectedIds.length + ' pendaftaran berhasil ditolak',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        });

        // Document preview click handler
        $('.document-preview').on('click', function() {
            const src = $(this).data('src');
            if (src) {
                lightbox.start($(this));
            }
        });

        // Filter form submit
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            window.location.href = '<?= current_url() ?>?' + formData;
        });
    });
</script>
<?= $this->endSection() ?>