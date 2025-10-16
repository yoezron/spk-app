<?php

/**
 * View: Admin WhatsApp Groups Management
 * Controller: Admin\WAGroupController::index()
 * Description: Manage WhatsApp groups per provinsi/wilayah
 * 
 * Features:
 * - List semua WA groups per provinsi
 * - Filter by province & status
 * - Regional scope untuk Koordinator Wilayah
 * - Member count tracking
 * - Invite link management
 * - Quick actions (edit, delete, view members)
 * - Statistics cards
 * - Responsive design
 * 
 * @package App\Views\Admin\WAGroups
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>">
<style>
    /* Page Header */
    .page-header-content {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    }

    .page-header-content h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header-content p {
        opacity: 0.95;
        margin-bottom: 0;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
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
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.total {
        border-color: #25D366;
    }

    .stat-card.active {
        border-color: #128C7E;
    }

    .stat-card.members {
        border-color: #667eea;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 12px;
    }

    .stat-card.total .stat-icon {
        background: #d5f4e6;
        color: #25D366;
    }

    .stat-card.active .stat-icon {
        background: #c8ede0;
        color: #128C7E;
    }

    .stat-card.members .stat-icon {
        background: #e8eaf6;
        color: #667eea;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    /* Groups Card */
    .groups-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .card-header-custom h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    /* Group Item */
    .group-item {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: white;
    }

    .group-item:hover {
        border-color: #25D366;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.15);
    }

    .group-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .group-info {
        flex: 1;
    }

    .group-name {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wa-icon {
        color: #25D366;
        font-size: 24px;
    }

    .province-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #e8f4fd;
        color: #0c5460;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
    }

    .group-description {
        color: #6c757d;
        font-size: 14px;
        margin: 8px 0;
        line-height: 1.6;
    }

    .group-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #6c757d;
    }

    .meta-item i {
        color: #adb5bd;
    }

    .meta-item.members {
        background: #e8eaf6;
        color: #667eea;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
    }

    .meta-item.members i {
        color: #667eea;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #d5f4e6;
        color: #0d5826;
    }

    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .status-badge i {
        font-size: 8px;
    }

    /* Invite Link */
    .invite-link-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 15px;
        margin: 12px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .invite-link-box i {
        color: #25D366;
        font-size: 18px;
    }

    .invite-link {
        flex: 1;
        font-size: 13px;
        color: #495057;
        font-family: 'Courier New', monospace;
        word-break: break-all;
    }

    .btn-copy-link {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        background: #25D366;
        color: white;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-copy-link:hover {
        background: #128C7E;
        color: white;
    }

    /* Action Buttons */
    .group-actions {
        display: flex;
        gap: 8px;
    }

    .btn-group-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-group-action:hover {
        transform: translateY(-2px);
    }

    .btn-group-action.members {
        background: #667eea;
        color: white;
    }

    .btn-group-action.members:hover {
        background: #5568d3;
    }

    .btn-group-action.edit {
        background: #17a2b8;
        color: white;
    }

    .btn-group-action.edit:hover {
        background: #138496;
    }

    .btn-group-action.delete {
        background: #e74c3c;
        color: white;
    }

    .btn-group-action.delete:hover {
        background: #c0392b;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #6c757d;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 25px;
    }

    /* Info Box */
    .info-box {
        background: #fff9e6;
        border-left: 4px solid #f39c12;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box .d-flex {
        gap: 12px;
    }

    .info-box p {
        color: #856404;
        font-size: 14px;
        margin: 0;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .filter-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-header-content {
            padding: 20px;
        }

        .page-header-content h1 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .group-header {
            flex-direction: column;
            gap: 15px;
        }

        .group-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1>
                <i class="bi bi-whatsapp me-2"></i>
                Kelola Grup WhatsApp
            </h1>
            <p>Manage grup WhatsApp per provinsi untuk komunikasi anggota SPK</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= base_url('admin/wa-groups/create') ?>" class="btn btn-light">
                <i class="bi bi-plus-circle me-1"></i> Tambah Grup Baru
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">WhatsApp Groups</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Info Box -->
<?php if (isset($is_koordinator) && $is_koordinator): ?>
    <div class="info-box">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle-fill" style="font-size: 24px; color: #f39c12;"></i>
            <div>
                <p>
                    <strong>Koordinator Wilayah:</strong> Anda hanya dapat mengelola grup WhatsApp untuk provinsi Anda.
                    Pastikan semua anggota di provinsi Anda sudah bergabung ke grup.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<?php
$totalGroups = count($groups ?? []);
$activeGroups = 0;
$totalMembers = 0;

if (!empty($groups)) {
    foreach ($groups as $group) {
        if ($group->status === 'active') $activeGroups++;
        $totalMembers += $group->members_count ?? 0;
    }
}
?>

<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon">
            <i class="bi bi-collection"></i>
        </div>
        <div class="stat-value"><?= $totalGroups ?></div>
        <div class="stat-label">Total Grup WhatsApp</div>
    </div>

    <div class="stat-card active">
        <div class="stat-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="stat-value"><?= $activeGroups ?></div>
        <div class="stat-label">Grup Aktif</div>
    </div>

    <div class="stat-card members">
        <div class="stat-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-value"><?= number_format($totalMembers) ?></div>
        <div class="stat-label">Total Anggota</div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="GET" action="<?= base_url('admin/wa-groups') ?>" id="filterForm">
        <div class="filter-row">
            <!-- Province Filter -->
            <?php if (!isset($is_koordinator) || !$is_koordinator): ?>
                <div>
                    <label class="form-label">Provinsi</label>
                    <select class="form-select" name="province_id" onchange="this.form.submit()">
                        <option value="">Semua Provinsi</option>
                        <?php if (isset($provinces)): ?>
                            <?php foreach ($provinces as $province): ?>
                                <option
                                    value="<?= $province->id ?>"
                                    <?= (isset($filters['province_id']) && $filters['province_id'] == $province->id) ? 'selected' : '' ?>>
                                    <?= esc($province->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Status Filter -->
            <div>
                <label class="form-label">Status</label>
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" <?= (isset($filters['status']) && $filters['status'] === 'active') ? 'selected' : '' ?>>
                        Aktif
                    </option>
                    <option value="inactive" <?= (isset($filters['status']) && $filters['status'] === 'inactive') ? 'selected' : '' ?>>
                        Tidak Aktif
                    </option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="form-label">Cari</label>
                <input
                    type="text"
                    class="form-control"
                    name="search"
                    placeholder="Nama grup..."
                    value="<?= esc($filters['search'] ?? '') ?>">
            </div>

            <!-- Buttons -->
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="<?= base_url('admin/wa-groups') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Groups List -->
<div class="groups-card">
    <div class="card-header-custom">
        <h3>
            <i class="bi bi-list-ul me-2"></i>
            Daftar Grup WhatsApp
        </h3>
    </div>

    <?php if (!empty($groups)): ?>
        <?php foreach ($groups as $group): ?>
            <div class="group-item">
                <div class="group-header">
                    <div class="group-info">
                        <h4 class="group-name">
                            <i class="bi bi-whatsapp wa-icon"></i>
                            <?= esc($group->name) ?>
                        </h4>

                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <span class="province-badge">
                                <i class="bi bi-geo-alt-fill"></i>
                                <?= esc($group->province_name) ?>
                            </span>
                            <span class="status-badge <?= esc($group->status) ?>">
                                <i class="bi bi-circle-fill"></i>
                                <?= ucfirst(esc($group->status)) ?>
                            </span>
                        </div>

                        <?php if (!empty($group->description)): ?>
                            <p class="group-description"><?= esc($group->description) ?></p>
                        <?php endif; ?>

                        <!-- Invite Link -->
                        <?php if (!empty($group->invite_link)): ?>
                            <div class="invite-link-box">
                                <i class="bi bi-link-45deg"></i>
                                <span class="invite-link"><?= esc($group->invite_link) ?></span>
                                <button
                                    type="button"
                                    class="btn-copy-link"
                                    onclick="copyInviteLink('<?= esc($group->invite_link, 'js') ?>')"
                                    title="Copy Link">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- Meta Info -->
                        <div class="group-meta">
                            <div class="meta-item members">
                                <i class="bi bi-people-fill"></i>
                                <?= number_format($group->members_count ?? 0) ?> Anggota
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-check-circle"></i>
                                <?= number_format($group->joined_count ?? 0) ?> Joined
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-clock"></i>
                                Dibuat <?= date('d M Y', strtotime($group->created_at)) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="group-actions">
                        <a
                            href="<?= base_url('admin/wa-groups/members/' . $group->id) ?>"
                            class="btn-group-action members"
                            title="Lihat Anggota">
                            <i class="bi bi-people"></i>
                        </a>
                        <a
                            href="<?= base_url('admin/wa-groups/edit/' . $group->id) ?>"
                            class="btn-group-action edit"
                            title="Edit Grup">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button
                            type="button"
                            class="btn-group-action delete"
                            onclick="deleteGroup(<?= $group->id ?>, '<?= esc($group->name, 'js') ?>')"
                            title="Hapus Grup">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if (isset($pager)): ?>
            <div class="mt-4">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="bi bi-whatsapp"></i>
            <h5>Belum Ada Grup WhatsApp</h5>
            <p>Mulai buat grup WhatsApp pertama untuk provinsi</p>
            <a href="<?= base_url('admin/wa-groups/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Buat Grup Baru
            </a>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        console.log('✓ WA Groups page initialized');
    });

    // ==========================================
    // COPY INVITE LINK
    // ==========================================
    function copyInviteLink(link) {
        // Create temporary input
        const tempInput = document.createElement('input');
        tempInput.value = link;
        document.body.appendChild(tempInput);
        tempInput.select();

        try {
            document.execCommand('copy');

            // Show success notification
            Swal.fire({
                icon: 'success',
                title: 'Link Tersalin!',
                text: 'Link invite telah disalin ke clipboard',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Tidak dapat menyalin link'
            });
        }

        document.body.removeChild(tempInput);
    }

    // ==========================================
    // DELETE GROUP
    // ==========================================
    function deleteGroup(id, name) {
        Swal.fire({
            title: 'Hapus Grup WhatsApp?',
            html: `Apakah Anda yakin ingin menghapus grup <strong>${name}</strong>?<br><br>
               <small class="text-danger">⚠️ Semua data anggota grup akan ikut terhapus!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: '<i class="bi bi-trash me-2"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit delete request
                $.ajax({
                    url: '<?= base_url('admin/wa-groups/delete') ?>/' + id,
                    type: 'POST',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Grup WhatsApp berhasil dihapus',
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
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus grup'
                        });
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>