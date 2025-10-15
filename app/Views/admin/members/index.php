<?php

/**
 * View: Admin Members List
 * Controller: App\Controllers\Admin\MemberController
 * Description: Comprehensive member management dengan advanced filtering, DataTables, bulk actions
 * 
 * Features:
 * - DataTables dengan server-side processing
 * - Advanced filters (Province, University, Status, Search)
 * - Status badges dengan color coding
 * - Action buttons per row (View, Edit, Approve, Suspend, Delete)
 * - Bulk actions (Bulk Approve, Bulk Delete, Bulk Export)
 * - Export functionality (Excel/CSV)
 * - Statistics summary cards
 * - Regional scope support untuk Koordinator Wilayah
 * - Responsive design (mobile-first)
 * - SweetAlert2 confirmations
 * - Permission-based access control
 * 
 * @package App\Views\Admin\Members
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<!-- DataTables CSS -->
<link href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>" rel="stylesheet">
<!-- Select2 CSS -->
<link href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/select2/css/select2-bootstrap4.min.css') ?>" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet">

<style>
    /* Members Page Wrapper */
    .members-wrapper {
        padding: 24px;
        background: #f8f9fa;
        min-height: calc(100vh - 80px);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px;
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

    .page-title-section h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title-section p {
        font-size: 15px;
        opacity: 0.95;
        margin: 0;
    }

    .page-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .page-actions .btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Stats Summary Cards */
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-summary-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .stat-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-summary-card.total {
        border-left-color: #667eea;
    }

    .stat-summary-card.active {
        border-left-color: #48bb78;
    }

    .stat-summary-card.pending {
        border-left-color: #f6ad55;
    }

    .stat-summary-card.suspended {
        border-left-color: #f56565;
    }

    .stat-summary-label {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .stat-summary-value {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
    }

    /* Filters Section */
    .filters-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .filters-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .filters-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filters-header h3 i {
        color: #667eea;
    }

    .filters-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        color: #667eea;
        font-weight: 600;
        font-size: 14px;
    }

    .filters-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: #4a5568;
        margin: 0;
    }

    .filter-group .form-control,
    .filter-group .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .filter-group .form-control:focus,
    .filter-group .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filters-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    /* Table Section */
    .table-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .table-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .bulk-actions-bar {
        background: #f7fafc;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: none;
        align-items: center;
        justify-content: space-between;
        border: 2px solid #667eea;
    }

    .bulk-actions-bar.active {
        display: flex;
    }

    .bulk-actions-info {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
    }

    .bulk-actions-buttons {
        display: flex;
        gap: 8px;
    }

    /* DataTable Custom Styles */
    .dataTables_wrapper {
        padding: 0;
    }

    #membersTable {
        width: 100% !important;
    }

    #membersTable thead th {
        background: #f7fafc;
        color: #4a5568;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 12px;
        border-bottom: 2px solid #e2e8f0;
    }

    #membersTable tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        font-size: 14px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
    }

    #membersTable tbody tr {
        transition: all 0.2s ease;
    }

    #membersTable tbody tr:hover {
        background: #f7fafc;
    }

    /* Member Info Cell */
    .member-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }

    .member-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .member-name {
        font-weight: 600;
        color: #2d3748;
        font-size: 14px;
    }

    .member-email {
        font-size: 12px;
        color: #718096;
    }

    .member-number {
        font-size: 11px;
        color: #a0aec0;
        font-family: 'Courier New', monospace;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-badge.active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-badge.pending {
        background: #feebc8;
        color: #7c2d12;
    }

    .status-badge.suspended {
        background: #fed7d7;
        color: #742a2a;
    }

    .status-badge.inactive {
        background: #e2e8f0;
        color: #4a5568;
    }

    .status-badge i {
        font-size: 14px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        font-size: 16px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-action.view {
        background: #e6f2ff;
        color: #4299e1;
    }

    .btn-action.view:hover {
        background: #4299e1;
        color: white;
    }

    .btn-action.edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-action.edit:hover {
        background: #d97706;
        color: white;
    }

    .btn-action.approve {
        background: #d1fae5;
        color: #059669;
    }

    .btn-action.approve:hover {
        background: #059669;
        color: white;
    }

    .btn-action.suspend {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-action.suspend:hover {
        background: #dc2626;
        color: white;
    }

    .btn-action.delete {
        background: #fce7f3;
        color: #be185d;
    }

    .btn-action.delete:hover {
        background: #be185d;
        color: white;
    }

    /* Checkbox */
    .form-check-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
        border: 2px solid #cbd5e0;
        border-radius: 4px;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
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
        font-size: 20px;
        font-weight: 700;
        color: #718096;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        display: none;
    }

    .loading-overlay.active {
        display: flex;
    }

    .loading-spinner {
        background: white;
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .members-wrapper {
            padding: 16px;
        }

        .page-header {
            padding: 24px;
        }

        .page-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-title-section h1 {
            font-size: 22px;
        }

        .page-actions {
            width: 100%;
        }

        .page-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .stats-summary {
            grid-template-columns: 1fr;
        }

        .filters-body {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-actions {
            width: 100%;
        }

        .bulk-actions-bar {
            flex-direction: column;
            gap: 12px;
        }

        .bulk-actions-buttons {
            width: 100%;
            flex-direction: column;
        }

        .bulk-actions-buttons .btn {
            width: 100%;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="members-wrapper">

    <!-- Page Header -->
    <div class="page-header animate-fade-in-up">
        <div class="page-header-content">
            <div class="page-title-section">
                <h1>
                    <i class="material-icons-outlined">people</i>
                    Kelola Anggota
                </h1>
                <p>Manajemen data anggota SPK - Approve, Edit, Export, dan lainnya</p>
            </div>

            <div class="page-actions">
                <?php if (auth()->user()->can('member.create')): ?>
                    <a href="<?= base_url('admin/members/create') ?>" class="btn btn-light">
                        <i class="material-icons-outlined">person_add</i>
                        Tambah Anggota
                    </a>
                <?php endif; ?>

                <?php if (auth()->user()->can('member.import')): ?>
                    <a href="<?= base_url('admin/bulk-import') ?>" class="btn btn-light">
                        <i class="material-icons-outlined">upload_file</i>
                        Import Massal
                    </a>
                <?php endif; ?>

                <?php if (auth()->user()->can('member.export')): ?>
                    <button type="button" class="btn btn-light" id="exportBtn">
                        <i class="material-icons-outlined">download</i>
                        Export Data
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?= view('components/alerts') ?>

    <!-- Statistics Summary -->
    <div class="stats-summary animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="stat-summary-card total">
            <div class="stat-summary-label">Total Anggota</div>
            <div class="stat-summary-value"><?= number_format($pager->getTotal()) ?></div>
        </div>

        <div class="stat-summary-card active">
            <div class="stat-summary-label">Anggota Aktif</div>
            <div class="stat-summary-value">
                <?php
                $activeCount = 0;
                foreach ($members as $member) {
                    if ($member->membership_status === 'aktif') $activeCount++;
                }
                echo number_format($activeCount);
                ?>
            </div>
        </div>

        <div class="stat-summary-card pending">
            <div class="stat-summary-label">Menunggu Approval</div>
            <div class="stat-summary-value">
                <?php
                $pendingCount = 0;
                foreach ($members as $member) {
                    if ($member->membership_status === 'calon_anggota') $pendingCount++;
                }
                echo number_format($pendingCount);
                ?>
            </div>
        </div>

        <div class="stat-summary-card suspended">
            <div class="stat-summary-label">Suspended</div>
            <div class="stat-summary-value">
                <?php
                $suspendedCount = 0;
                foreach ($members as $member) {
                    if ($member->membership_status === 'tidak_aktif') $suspendedCount++;
                }
                echo number_format($suspendedCount);
                ?>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="filters-header">
            <h3>
                <i class="material-icons-outlined">filter_list</i>
                Filter & Pencarian
            </h3>
            <div class="filters-toggle" id="filtersToggle">
                <span id="filtersToggleText">Sembunyikan</span>
                <i class="material-icons-outlined" id="filtersToggleIcon">expand_less</i>
            </div>
        </div>

        <form method="GET" action="<?= current_url() ?>" id="filtersForm">
            <div class="filters-body" id="filtersBody">

                <!-- Province Filter -->
                <div class="filter-group">
                    <label for="filterProvince">Provinsi</label>
                    <select name="province_id" id="filterProvince" class="form-select select2">
                        <option value="">Semua Provinsi</option>
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?= $province->id ?>" <?= ($filters['province_id'] ?? '') == $province->id ? 'selected' : '' ?>>
                                <?= esc($province->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- University Filter -->
                <div class="filter-group">
                    <label for="filterUniversity">Perguruan Tinggi</label>
                    <select name="university_id" id="filterUniversity" class="form-select select2">
                        <option value="">Semua PT</option>
                        <?php foreach ($universities as $university): ?>
                            <option value="<?= $university->id ?>" <?= ($filters['university_id'] ?? '') == $university->id ? 'selected' : '' ?>>
                                <?= esc($university->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="filterStatus">Status Keanggotaan</label>
                    <select name="membership_status" id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?= ($filters['membership_status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="calon_anggota" <?= ($filters['membership_status'] ?? '') === 'calon_anggota' ? 'selected' : '' ?>>Calon Anggota</option>
                        <option value="tidak_aktif" <?= ($filters['membership_status'] ?? '') === 'tidak_aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="filter-group">
                    <label for="filterSearch">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        id="filterSearch"
                        class="form-control"
                        placeholder="Nama, email, nomor anggota, telepon..."
                        value="<?= esc($filters['search'] ?? '') ?>">
                </div>

            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons-outlined" style="font-size: 18px;">search</i>
                    Terapkan Filter
                </button>
                <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined" style="font-size: 18px;">refresh</i>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-section animate-fade-in-up" style="animation-delay: 0.3s;">

        <div class="table-header">
            <h3 class="table-title">
                Daftar Anggota
                <span style="color: #718096; font-weight: 400; font-size: 14px;">
                    (<?= number_format($pager->getTotal()) ?> total)
                </span>
            </h3>

            <div class="table-actions">
                <div class="input-group" style="width: 300px;">
                    <input
                        type="text"
                        class="form-control"
                        id="quickSearch"
                        placeholder="Cari cepat...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="material-icons-outlined">search</i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulkActionsBar">
            <div class="bulk-actions-info">
                <i class="material-icons-outlined" style="color: #667eea;">check_circle</i>
                <span id="selectedCount">0</span> item dipilih
            </div>
            <div class="bulk-actions-buttons">
                <?php if (auth()->user()->can('member.approve')): ?>
                    <button type="button" class="btn btn-sm btn-success" id="bulkApproveBtn">
                        <i class="material-icons-outlined" style="font-size: 16px;">check</i>
                        Approve Terpilih
                    </button>
                <?php endif; ?>

                <?php if (auth()->user()->can('member.delete')): ?>
                    <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                        <i class="material-icons-outlined" style="font-size: 16px;">delete</i>
                        Hapus Terpilih
                    </button>
                <?php endif; ?>

                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                    <i class="material-icons-outlined" style="font-size: 16px;">close</i>
                    Batal
                </button>
            </div>
        </div>

        <!-- DataTable -->
        <?php if (!empty($members)): ?>
            <div class="table-responsive">
                <table id="membersTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Anggota</th>
                            <th>Provinsi</th>
                            <th>Perguruan Tinggi</th>
                            <th>Status</th>
                            <th>Bergabung</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr data-member-id="<?= $member->id ?>">
                                <td>
                                    <input
                                        type="checkbox"
                                        class="form-check-input member-checkbox"
                                        value="<?= $member->id ?>">
                                </td>
                                <td>
                                    <div class="member-info">
                                        <?php if (!empty($member->photo)): ?>
                                            <img
                                                src="<?= base_url('uploads/photos/' . $member->photo) ?>"
                                                alt="Avatar"
                                                class="member-avatar">
                                        <?php else: ?>
                                            <img
                                                src="<?= base_url('assets/images/avatars/avatar.png') ?>"
                                                alt="Avatar"
                                                class="member-avatar">
                                        <?php endif; ?>

                                        <div class="member-details">
                                            <div class="member-name"><?= esc($member->full_name) ?></div>
                                            <div class="member-email"><?= esc($member->email) ?></div>
                                            <?php if (!empty($member->member_number)): ?>
                                                <div class="member-number"><?= esc($member->member_number) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($member->province_name ?? '-') ?></td>
                                <td><?= esc($member->university_name ?? '-') ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'inactive';
                                    $statusIcon = 'block';
                                    $statusText = 'Tidak Aktif';

                                    if ($member->membership_status === 'aktif') {
                                        $statusClass = 'active';
                                        $statusIcon = 'check_circle';
                                        $statusText = 'Aktif';
                                    } elseif ($member->membership_status === 'calon_anggota') {
                                        $statusClass = 'pending';
                                        $statusIcon = 'pending';
                                        $statusText = 'Pending';
                                    } elseif ($member->membership_status === 'tidak_aktif') {
                                        $statusClass = 'suspended';
                                        $statusIcon = 'block';
                                        $statusText = 'Suspended';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="material-icons-outlined"><?= $statusIcon ?></i>
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $joinDate = strtotime($member->registered_at ?? $member->created_at);
                                    echo date('d M Y', $joinDate);
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- View Button -->
                                        <?php if (auth()->user()->can('member.view_detail')): ?>
                                            <button
                                                type="button"
                                                class="btn-action view"
                                                onclick="viewMember(<?= $member->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Lihat Detail">
                                                <i class="material-icons-outlined">visibility</i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Edit Button -->
                                        <?php if (auth()->user()->can('member.edit')): ?>
                                            <a
                                                href="<?= base_url('admin/members/edit/' . $member->id) ?>"
                                                class="btn-action edit"
                                                data-bs-toggle="tooltip"
                                                title="Edit">
                                                <i class="material-icons-outlined">edit</i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Approve Button (only for pending) -->
                                        <?php if ($member->membership_status === 'calon_anggota' && auth()->user()->can('member.approve')): ?>
                                            <button
                                                type="button"
                                                class="btn-action approve"
                                                onclick="approveMember(<?= $member->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Approve">
                                                <i class="material-icons-outlined">check</i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Suspend/Activate Button -->
                                        <?php if (auth()->user()->can('member.suspend')): ?>
                                            <?php if ($member->membership_status === 'aktif'): ?>
                                                <button
                                                    type="button"
                                                    class="btn-action suspend"
                                                    onclick="suspendMember(<?= $member->id ?>)"
                                                    data-bs-toggle="tooltip"
                                                    title="Suspend">
                                                    <i class="material-icons-outlined">block</i>
                                                </button>
                                            <?php elseif ($member->membership_status === 'tidak_aktif'): ?>
                                                <button
                                                    type="button"
                                                    class="btn-action approve"
                                                    onclick="activateMember(<?= $member->id ?>)"
                                                    data-bs-toggle="tooltip"
                                                    title="Aktifkan">
                                                    <i class="material-icons-outlined">check_circle</i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Delete Button -->
                                        <?php if (auth()->user()->can('member.delete')): ?>
                                            <button
                                                type="button"
                                                class="btn-action delete"
                                                onclick="deleteMember(<?= $member->id ?>)"
                                                data-bs-toggle="tooltip"
                                                title="Hapus">
                                                <i class="material-icons-outlined">delete</i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan <?= ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 ?>
                    sampai <?= min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()) ?>
                    dari <?= number_format($pager->getTotal()) ?> data
                </div>
                <?= $pager->links('default', 'custom_pagination') ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="material-icons-outlined">people_outline</i>
                <h3>Tidak Ada Data Anggota</h3>
                <p>Belum ada anggota yang terdaftar atau sesuai dengan filter yang diterapkan</p>
                <?php if (auth()->user()->can('member.create')): ?>
                    <a href="<?= base_url('admin/members/create') ?>" class="btn btn-primary mt-3">
                        <i class="material-icons-outlined">person_add</i>
                        Tambah Anggota Pertama
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-3 text-center">Memproses...</div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables JS -->
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/js/select2.min.js') ?>"></script>
<!-- SweetAlert2 JS -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih...',
            allowClear: true
        });

        // Initialize DataTables
        const table = $('#membersTable').DataTable({
            responsive: true,
            pageLength: 20,
            ordering: true,
            searching: true,
            dom: 'rtip',
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>',
                emptyTable: 'Tidak ada data yang tersedia',
                zeroRecords: 'Tidak ditemukan data yang sesuai'
            },
            columnDefs: [{
                orderable: false,
                targets: [0, 6]
            }]
        });

        // Quick Search
        $('#quickSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Initialize Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Filters Toggle
        $('#filtersToggle').on('click', function() {
            const body = $('#filtersBody');
            const text = $('#filtersToggleText');
            const icon = $('#filtersToggleIcon');

            body.slideToggle();

            if (text.text() === 'Sembunyikan') {
                text.text('Tampilkan');
                icon.text('expand_more');
            } else {
                text.text('Sembunyikan');
                icon.text('expand_less');
            }
        });

        // Select All Checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.member-checkbox').prop('checked', isChecked);
            updateBulkActionsBar();
        });

        // Individual Checkbox
        $('.member-checkbox').on('change', function() {
            updateBulkActionsBar();

            // Update select all checkbox
            const totalCheckboxes = $('.member-checkbox').length;
            const checkedCheckboxes = $('.member-checkbox:checked').length;
            $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        // Update Bulk Actions Bar
        function updateBulkActionsBar() {
            const checkedCount = $('.member-checkbox:checked').length;
            $('#selectedCount').text(checkedCount);

            if (checkedCount > 0) {
                $('#bulkActionsBar').addClass('active');
            } else {
                $('#bulkActionsBar').removeClass('active');
            }
        }

        // Deselect All
        $('#deselectAllBtn').on('click', function() {
            $('.member-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateBulkActionsBar();
        });

        // Bulk Approve
        $('#bulkApproveBtn').on('click', function() {
            const selectedIds = [];
            $('.member-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                showAlert('error', 'Pilih minimal satu anggota');
                return;
            }

            Swal.fire({
                title: 'Approve Anggota?',
                text: `Anda akan menyetujui ${selectedIds.length} anggota`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#48bb78',
                cancelButtonColor: '#cbd5e0',
                confirmButtonText: 'Ya, Approve!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkAction('approve', selectedIds);
                }
            });
        });

        // Bulk Delete
        $('#bulkDeleteBtn').on('click', function() {
            const selectedIds = [];
            $('.member-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                showAlert('error', 'Pilih minimal satu anggota');
                return;
            }

            Swal.fire({
                title: 'Hapus Anggota?',
                text: `Anda akan menghapus ${selectedIds.length} anggota. Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f56565',
                cancelButtonColor: '#cbd5e0',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkAction('delete', selectedIds);
                }
            });
        });

        // Export Button
        $('#exportBtn').on('click', function() {
            Swal.fire({
                title: 'Export Data',
                text: 'Pilih format export:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Excel (.xlsx)',
                denyButtonText: 'CSV (.csv)',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#48bb78',
                denyButtonColor: '#4299e1'
            }).then((result) => {
                if (result.isConfirmed) {
                    exportData('excel');
                } else if (result.isDenied) {
                    exportData('csv');
                }
            });
        });

    });

    // View Member Detail
    function viewMember(id) {
        window.location.href = `<?= base_url('admin/members/detail/') ?>${id}`;
    }

    // Approve Member
    function approveMember(id) {
        Swal.fire({
            title: 'Approve Anggota?',
            text: 'Anggota akan disetujui dan dapat mengakses sistem',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Approve!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                window.location.href = `<?= base_url('admin/members/approve/') ?>${id}`;
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
                showLoading();
                window.location.href = `<?= base_url('admin/members/suspend/') ?>${id}`;
            }
        });
    }

    // Activate Member
    function activateMember(id) {
        Swal.fire({
            title: 'Aktifkan Anggota?',
            text: 'Anggota akan diaktifkan kembali',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                window.location.href = `<?= base_url('admin/members/activate/') ?>${id}`;
            }
        });
    }

    // Delete Member
    function deleteMember(id) {
        Swal.fire({
            title: 'Hapus Anggota?',
            text: 'Data anggota akan dihapus permanen. Tindakan ini tidak dapat dibatalkan!',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#f56565',
            cancelButtonColor: '#cbd5e0',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                window.location.href = `<?= base_url('admin/members/delete/') ?>${id}`;
            }
        });
    }

    // Bulk Action
    function bulkAction(action, ids) {
        showLoading();

        $.ajax({
            url: `<?= base_url('admin/members/bulk-') ?>${action}`,
            method: 'POST',
            data: {
                member_ids: ids,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                hideLoading();
                showAlert('success', 'Proses berhasil!');
                setTimeout(() => location.reload(), 1500);
            },
            error: function() {
                hideLoading();
                showAlert('error', 'Terjadi kesalahan saat memproses data');
            }
        });
    }

    // Export Data
    function exportData(format) {
        showLoading();

        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('export', format);

        window.location.href = `<?= base_url('admin/members/export') ?>?${currentUrl.searchParams.toString()}`;

        setTimeout(hideLoading, 2000);
    }

    // Show Loading
    function showLoading() {
        $('#loadingOverlay').addClass('active');
    }

    // Hide Loading
    function hideLoading() {
        $('#loadingOverlay').removeClass('active');
    }

    // Show Alert
    function showAlert(type, message) {
        const icon = type === 'success' ? 'check_circle' : 'error';
        const color = type === 'success' ? '#48bb78' : '#f56565';

        Swal.fire({
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 2000,
            iconColor: color
        });
    }
</script>
<?= $this->endSection() ?>