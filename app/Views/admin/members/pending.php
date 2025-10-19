<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/lightbox/lightbox.min.css') ?>">
<style>
    .page-header-pending {
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
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .member-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .member-card-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 2px solid #dee2e6;
    }

    .member-photo {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: pointer;
    }

    .member-name {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .member-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        color: #6c757d;
        font-size: 14px;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: #fff3cd;
        color: #856404;
    }

    .verification-checklist {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .checklist-item:last-child {
        border-bottom: none;
    }

    .checklist-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 14px;
    }

    .checklist-icon.complete {
        background: #d4edda;
        color: #28a745;
    }

    .checklist-icon.incomplete {
        background: #f8d7da;
        color: #dc3545;
    }

    .document-preview {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        max-width: 120px;
    }

    .document-preview:hover {
        transform: scale(1.05);
    }

    .document-preview img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn-approve {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
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
        padding: 10px 20px;
        border-radius: 8px;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        color: white;
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

    .info-item {
        display: flex;
        padding: 8px 0;
        font-size: 14px;
    }

    .info-label {
        font-weight: 600;
        color: #6c757d;
        width: 140px;
    }

    .info-value {
        color: #2c3e50;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-pending">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2 text-white">
                <i class="feather icon-user-check"></i> Verifikasi & Approval Anggota
            </h3>
            <p class="mb-0 text-white opacity-90">
                Review dan setujui calon anggota yang telah mendaftar
            </p>
        </div>
        <div>
            <a href="<?= base_url('admin/members') ?>" class="btn btn-light">
                <i class="feather icon-users"></i> Semua Anggota
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> <?= session('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= session('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card pending">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon pending">
                    <i class="feather icon-clock"></i>
                </div>
            </div>
            <div class="stat-value"><?= count($members) ?></div>
            <div class="stat-label">Menunggu Approval</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card approved">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon approved">
                    <i class="feather icon-check-circle"></i>
                </div>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Disetujui Hari Ini</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card rejected">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon rejected">
                    <i class="feather icon-x-circle"></i>
                </div>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>
</div>

<!-- Search & Bulk Actions -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="get" action="<?= base_url('admin/members/pending') ?>">
            <div class="input-group">
                <input type="text" name="search" class="form-control"
                    placeholder="Cari nama, email, telepon..."
                    value="<?= esc($search ?? '') ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="feather icon-search"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-right">
        <?php if (!empty($members)): ?>
            <button type="button" class="btn btn-success" onclick="bulkApprove()">
                <i class="feather icon-check-circle"></i> Setujui Terpilih
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div>
        <i class="feather icon-check-square"></i>
        <strong><span id="selectedCount">0</span> anggota dipilih</strong>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-light btn-sm" id="clearSelection">
            <i class="feather icon-x"></i> Batal
        </button>
    </div>
</div>

<!-- Pending Members List -->
<?php if (!empty($members) && count($members) > 0): ?>
    <form id="bulkApproveForm" method="post" action="<?= base_url('admin/members/bulk-approve') ?>">
        <?= csrf_field() ?>

        <?php foreach ($members as $member): ?>
            <div class="member-card">
                <div class="member-card-header">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input member-checkbox"
                                id="member_<?= $member->id ?>"
                                name="member_ids[]"
                                value="<?= $member->id ?>">
                            <label class="custom-control-label" for="member_<?= $member->id ?>"></label>
                        </div>

                        <?php if (!empty($member->photo_url)): ?>
                            <img src="<?= base_url('uploads/photos/' . $member->photo_url) ?>"
                                alt="Photo"
                                class="member-photo"
                                data-lightbox="member-<?= $member->id ?>"
                                data-title="<?= esc($member->full_name) ?>">
                        <?php else: ?>
                            <div class="member-photo d-flex align-items-center justify-content-center bg-secondary">
                                <i class="feather icon-user text-white" style="font-size: 32px;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="flex-grow-1">
                            <div class="member-name"><?= esc($member->full_name) ?></div>
                            <div class="member-meta">
                                <span><i class="feather icon-mail"></i> <?= esc($member->email) ?></span>
                                <span><i class="feather icon-phone"></i> <?= esc($member->phone ?? '-') ?></span>
                                <span><i class="feather icon-map-pin"></i> <?= esc($member->province_name ?? '-') ?></span>
                            </div>
                            <div class="mt-2">
                                <span class="status-badge">
                                    <i class="feather icon-clock"></i> Pending Review
                                </span>
                                <small class="text-muted ms-3">
                                    <i class="feather icon-calendar"></i>
                                    Mendaftar: <?= date('d M Y H:i', strtotime($member->registered_at)) ?>
                                </small>
                            </div>
                        </div>

                        <button class="btn btn-sm btn-outline-primary" type="button"
                            data-toggle="collapse" data-target="#detail-<?= $member->id ?>">
                            <i class="feather icon-chevron-down"></i> Detail
                        </button>
                    </div>
                </div>

                <div class="collapse" id="detail-<?= $member->id ?>">
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Info -->
                            <div class="col-md-7">
                                <h6 class="mb-3"><i class="feather icon-user"></i> Informasi Lengkap</h6>

                                <div class="info-item">
                                    <div class="info-label">NIK/NIP:</div>
                                    <div class="info-value"><?= esc($member->nik ?? '-') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Jenis Kelamin:</div>
                                    <div class="info-value"><?= esc($member->gender ?? '-') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Alamat:</div>
                                    <div class="info-value"><?= esc($member->address ?? '-') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Universitas:</div>
                                    <div class="info-value"><?= esc($member->university_name ?? '-') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Program Studi:</div>
                                    <div class="info-value"><?= esc($member->study_program_name ?? '-') ?></div>
                                </div>

                                <!-- Verification Checklist -->
                                <div class="verification-checklist">
                                    <h6 class="mb-2"><i class="feather icon-check-square"></i> Verifikasi</h6>

                                    <div class="checklist-item">
                                        <div class="checklist-icon <?= !empty($member->full_name) ? 'complete' : 'incomplete' ?>">
                                            <i class="feather icon-<?= !empty($member->full_name) ? 'check' : 'x' ?>"></i>
                                        </div>
                                        <div>Data Pribadi Lengkap</div>
                                    </div>

                                    <div class="checklist-item">
                                        <div class="checklist-icon <?= !empty($member->photo_url) ? 'complete' : 'incomplete' ?>">
                                            <i class="feather icon-<?= !empty($member->photo_url) ? 'check' : 'x' ?>"></i>
                                        </div>
                                        <div>Foto Profil</div>
                                    </div>

                                    <div class="checklist-item">
                                        <div class="checklist-icon <?= !empty($member->payment_proof_url) ? 'complete' : 'incomplete' ?>">
                                            <i class="feather icon-<?= !empty($member->payment_proof_url) ? 'check' : 'x' ?>"></i>
                                        </div>
                                        <div>Bukti Pembayaran</div>
                                    </div>

                                    <div class="checklist-item">
                                        <div class="checklist-icon <?= !empty($member->university_id) ? 'complete' : 'incomplete' ?>">
                                            <i class="feather icon-<?= !empty($member->university_id) ? 'check' : 'x' ?>"></i>
                                        </div>
                                        <div>Data Kepegawaian</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Documents & Actions -->
                            <div class="col-md-5">
                                <h6 class="mb-3"><i class="feather icon-file"></i> Dokumen Pendukung</h6>

                                <div class="row g-2 mb-3">
                                    <?php if (!empty($member->photo_url)): ?>
                                        <div class="col-6">
                                            <div class="document-preview" data-lightbox="member-<?= $member->id ?>"
                                                data-src="<?= base_url('uploads/photos/' . $member->photo_url) ?>">
                                                <img src="<?= base_url('uploads/photos/' . $member->photo_url) ?>" alt="Foto">
                                            </div>
                                            <small class="text-muted d-block text-center mt-1">Foto Profil</small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($member->payment_proof_url)): ?>
                                        <div class="col-6">
                                            <div class="document-preview" data-lightbox="member-<?= $member->id ?>"
                                                data-src="<?= base_url('uploads/bukti_bayar/' . $member->payment_proof_url) ?>">
                                                <img src="<?= base_url('uploads/bukti_bayar/' . $member->payment_proof_url) ?>" alt="Bukti Bayar">
                                            </div>
                                            <small class="text-muted d-block text-center mt-1">Bukti Bayar</small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    <a href="<?= base_url('admin/members/show/' . $member->id) ?>"
                                        class="btn btn-info flex-fill">
                                        <i class="feather icon-eye"></i> Lihat Detail
                                    </a>
                                </div>
                                <div class="action-buttons">
                                    <button type="button"
                                        class="btn btn-approve flex-fill"
                                        onclick="approveConfirm(<?= $member->id ?>, '<?= esc($member->full_name) ?>')">
                                        <i class="feather icon-check-circle"></i> Approve
                                    </button>
                                    <button type="button"
                                        class="btn btn-reject flex-fill"
                                        onclick="rejectModal(<?= $member->id ?>, '<?= esc($member->full_name) ?>')">
                                        <i class="feather icon-x-circle"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <div class="row mt-3">
            <div class="col-md-12">
                <?= $pager->links('default', 'bootstrap_pagination') ?>
            </div>
        </div>
    </form>

<?php else: ?>
    <!-- Empty State -->
    <div class="text-center py-5">
        <i class="feather icon-users" style="font-size: 72px; color: #ccc;"></i>
        <h5 class="mt-3">Tidak Ada Calon Anggota</h5>
        <p class="text-muted">Saat ini tidak ada calon anggota yang menunggu persetujuan.</p>
    </div>
<?php endif; ?>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="feather icon-x-circle"></i> Tolak Pendaftaran
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menolak pendaftaran:</p>
                    <p class="font-weight-bold" id="rejectMemberName"></p>

                    <div class="form-group">
                        <label for="reason">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" class="form-control" rows="4" required
                            placeholder="Berikan alasan penolakan yang jelas..."></textarea>
                        <small class="form-text text-muted">Alasan akan dikirimkan via email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="feather icon-x"></i> Tolak Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/lightbox/lightbox.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Initialize Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        // Select All Checkbox
        $('#selectAll').on('change', function() {
            $('.member-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkActions();
        });

        // Individual Checkbox
        $('.member-checkbox').on('change', function() {
            updateBulkActions();
            const allChecked = $('.member-checkbox').length === $('.member-checkbox:checked').length;
            $('#selectAll').prop('checked', allChecked);
        });

        // Update Bulk Actions
        function updateBulkActions() {
            const count = $('.member-checkbox:checked').length;
            $('#selectedCount').text(count);
            if (count > 0) {
                $('#bulkActionsBar').addClass('active');
            } else {
                $('#bulkActionsBar').removeClass('active');
            }
        }

        // Clear Selection
        $('#clearSelection').on('click', function() {
            $('.member-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateBulkActions();
        });

        // Initialize Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    // Approve Confirmation
    function approveConfirm(memberId, memberName) {
        if (confirm(`Anda yakin ingin menyetujui pendaftaran "${memberName}"?\n\nSetelah disetujui, anggota akan:\n- Mendapat nomor anggota\n- Dapat login ke sistem\n- Menerima email konfirmasi`)) {
            window.location.href = `<?= base_url('admin/members/approve/') ?>${memberId}`;
        }
    }

    // Bulk Approve
    function bulkApprove() {
        const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');

        if (checkedBoxes.length === 0) {
            alert('Pilih minimal satu calon anggota untuk disetujui.');
            return;
        }

        if (confirm(`Anda yakin ingin menyetujui ${checkedBoxes.length} calon anggota?\n\nSemua anggota terpilih akan:\n- Mendapat nomor anggota\n- Dapat login ke sistem\n- Menerima email konfirmasi`)) {
            document.getElementById('bulkApproveForm').submit();
        }
    }

    // Reject Modal
    function rejectModal(memberId, memberName) {
        document.getElementById('rejectMemberName').textContent = memberName;
        document.getElementById('rejectForm').action = `<?= base_url('admin/members/reject/') ?>${memberId}`;
        $('#rejectModal').modal('show');
    }

    // Document Preview Click
    $('.document-preview').on('click', function() {
        const src = $(this).data('src');
        if (src) {
            lightbox.start($(this));
        }
    });
</script>
<?= $this->endSection() ?>