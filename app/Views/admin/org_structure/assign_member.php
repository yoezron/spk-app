<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><?= esc($title) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/org-structure') ?>">Struktur Organisasi</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/org-structure/unit/' . ($position['unit_id'] ?? 0)) ?>">
                        <?= esc($position['unit_name'] ?? 'Unit') ?>
                    </a></li>
                    <li class="breadcrumb-item active" aria-current="page">Assign Anggota</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Position Info -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Informasi Posisi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 150px;">Posisi:</th>
                                <td><strong><?= esc($position['title'] ?? 'N/A') ?></strong></td>
                            </tr>
                            <tr>
                                <th>Unit:</th>
                                <td><?= esc($position['unit_name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Tipe:</th>
                                <td>
                                    <span class="badge bg-info">
                                        <?= esc(ucfirst($position['position_type'] ?? 'N/A')) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 180px;">Max Pemegang:</th>
                                <td><?= esc($position['max_holders'] ?? 1) ?> orang</td>
                            </tr>
                            <tr>
                                <th>Saat Ini Terisi:</th>
                                <td>
                                    <?= count($position['current_holders'] ?? []) ?> orang
                                    <?php if (count($position['current_holders'] ?? []) >= ($position['max_holders'] ?? 1)): ?>
                                        <span class="badge bg-warning ms-2">Penuh</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Current Holders -->
                <?php if (!empty($position['current_holders'])): ?>
                    <div class="mt-3">
                        <h6>Pemegang Jabatan Saat Ini:</h6>
                        <div class="list-group">
                            <?php foreach ($position['current_holders'] as $holder): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="material-icons-outlined">person</i>
                                            <strong><?= esc($holder['full_name'] ?? $holder['email'] ?? 'N/A') ?></strong>
                                            <?php if (!empty($holder['started_at'])): ?>
                                                <br><small class="text-muted">Sejak: <?= date('d/m/Y', strtotime($holder['started_at'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-end-assignment"
                                                data-assignment-id="<?= $holder['assignment_id'] ?? 0 ?>"
                                                data-member-name="<?= esc($holder['full_name'] ?? $holder['email'] ?? '') ?>">
                                            <i class="material-icons-outlined">person_remove</i> Akhiri
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Assign Anggota ke Posisi</h5>
            </div>
            <div class="card-body">
                <?php if (count($position['current_holders'] ?? []) >= ($position['max_holders'] ?? 1)): ?>
                    <div class="alert alert-warning">
                        <i class="material-icons-outlined">warning</i>
                        <strong>Perhatian:</strong> Posisi ini sudah mencapai batas maksimal pemegang jabatan.
                        Akhiri assignment yang ada terlebih dahulu sebelum menambah anggota baru.
                    </div>
                <?php else: ?>
                    <form action="<?= base_url('admin/org-structure/position/' . ($position['id'] ?? 0) . '/assign') ?>"
                          method="POST"
                          id="assignForm">
                        <?= csrf_field() ?>

                        <!-- Select Member -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">
                                Pilih Anggota <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= session('errors.user_id') ? 'is-invalid' : '' ?>"
                                    id="user_id"
                                    name="user_id"
                                    required>
                                <option value="">-- Cari dan Pilih Anggota --</option>
                                <?php if (!empty($members) && is_array($members)): ?>
                                    <?php foreach ($members as $member): ?>
                                        <option value="<?= $member['user_id'] ?>">
                                            <?= esc($member['full_name']) ?>
                                            <?= !empty($member['member_number']) ? ' (' . $member['member_number'] . ')' : '' ?>
                                            <?= !empty($member['university_name']) ? ' - ' . $member['university_name'] : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (session('errors.user_id')): ?>
                                <div class="invalid-feedback"><?= session('errors.user_id') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Hanya menampilkan anggota aktif yang belum di-assign ke posisi ini
                            </small>
                        </div>

                        <!-- Start Date -->
                        <div class="mb-3">
                            <label for="started_at" class="form-label">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control <?= session('errors.started_at') ? 'is-invalid' : '' ?>"
                                   id="started_at"
                                   name="started_at"
                                   value="<?= old('started_at', date('Y-m-d')) ?>"
                                   required>
                            <?php if (session('errors.started_at')): ?>
                                <div class="invalid-feedback"><?= session('errors.started_at') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- End Date (Optional) -->
                        <div class="mb-3">
                            <label for="ended_at" class="form-label">
                                Tanggal Berakhir (Opsional)
                            </label>
                            <input type="date"
                                   class="form-control <?= session('errors.ended_at') ? 'is-invalid' : '' ?>"
                                   id="ended_at"
                                   name="ended_at"
                                   value="<?= old('ended_at') ?>">
                            <?php if (session('errors.ended_at')): ?>
                                <div class="invalid-feedback"><?= session('errors.ended_at') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Kosongkan jika tidak ada batas waktu (permanent assignment)
                            </small>
                        </div>

                        <!-- Assignment Type -->
                        <div class="mb-3">
                            <label for="assignment_type" class="form-label">
                                Tipe Penugasan
                            </label>
                            <select class="form-select <?= session('errors.assignment_type') ? 'is-invalid' : '' ?>"
                                    id="assignment_type"
                                    name="assignment_type">
                                <option value="permanent" <?= old('assignment_type', 'permanent') === 'permanent' ? 'selected' : '' ?>>
                                    Permanent (Tetap)
                                </option>
                                <option value="temporary" <?= old('assignment_type') === 'temporary' ? 'selected' : '' ?>>
                                    Temporary (Sementara)
                                </option>
                                <option value="acting" <?= old('assignment_type') === 'acting' ? 'selected' : '' ?>>
                                    Acting (Pelaksana Tugas)
                                </option>
                            </select>
                            <?php if (session('errors.assignment_type')): ?>
                                <div class="invalid-feedback"><?= session('errors.assignment_type') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Appointment Letter -->
                        <div class="mb-3">
                            <label for="appointment_letter_number" class="form-label">
                                Nomor SK Pengangkatan
                            </label>
                            <input type="text"
                                   class="form-control <?= session('errors.appointment_letter_number') ? 'is-invalid' : '' ?>"
                                   id="appointment_letter_number"
                                   name="appointment_letter_number"
                                   value="<?= old('appointment_letter_number') ?>"
                                   placeholder="Contoh: SK-001/SPK/2025">
                            <?php if (session('errors.appointment_letter_number')): ?>
                                <div class="invalid-feedback"><?= session('errors.appointment_letter_number') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="appointment_letter_date" class="form-label">
                                Tanggal SK
                            </label>
                            <input type="date"
                                   class="form-control <?= session('errors.appointment_letter_date') ? 'is-invalid' : '' ?>"
                                   id="appointment_letter_date"
                                   name="appointment_letter_date"
                                   value="<?= old('appointment_letter_date') ?>">
                            <?php if (session('errors.appointment_letter_date')): ?>
                                <div class="invalid-feedback"><?= session('errors.appointment_letter_date') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                Catatan
                            </label>
                            <textarea class="form-control <?= session('errors.notes') ? 'is-invalid' : '' ?>"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="Catatan tambahan tentang assignment ini..."><?= old('notes') ?></textarea>
                            <?php if (session('errors.notes')): ?>
                                <div class="invalid-feedback"><?= session('errors.notes') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('admin/org-structure/unit/' . ($position['unit_id'] ?? 0)) ?>"
                               class="btn btn-secondary">
                                <i class="material-icons-outlined">cancel</i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="material-icons-outlined">person_add</i> Assign Anggota
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Panduan Assignment</h5>
            </div>
            <div class="card-body">
                <h6><i class="material-icons-outlined">info</i> Tipe Penugasan</h6>
                <ul class="small mb-3">
                    <li><strong>Permanent:</strong> Penugasan tetap tanpa batas waktu</li>
                    <li><strong>Temporary:</strong> Penugasan sementara dengan batas waktu</li>
                    <li><strong>Acting:</strong> Pelaksana tugas (menggantikan sementara)</li>
                </ul>

                <div class="alert alert-info">
                    <small>
                        <i class="material-icons-outlined">lightbulb</i>
                        <strong>Tips:</strong> Gunakan tipe "Acting" untuk penugasan PLT/PLH,
                        dan "Temporary" untuk penugasan dengan periode tertentu.
                    </small>
                </div>

                <div class="alert alert-warning">
                    <small>
                        <i class="material-icons-outlined">warning</i>
                        Assignment yang sudah dibuat bisa diakhiri kapan saja melalui tombol "Akhiri"
                        pada daftar pemegang jabatan di atas.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Assignment Modal -->
<div class="modal fade" id="endAssignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Akhiri Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengakhiri assignment untuk <strong id="memberNameDisplay"></strong>?</p>

                <div class="mb-3">
                    <label for="endDate" class="form-label">Tanggal Berakhir</label>
                    <input type="date" class="form-control" id="endDate" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="mb-3">
                    <label for="endReason" class="form-label">Alasan (Opsional)</label>
                    <textarea class="form-control" id="endReason" rows="3" placeholder="Alasan mengakhiri assignment..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmEndAssignment">Akhiri Assignment</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/select2/css/select2-bootstrap-5-theme.min.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/select2/js/select2.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for member selection
    $('#user_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Cari dan Pilih Anggota --',
        allowClear: true,
        width: '100%'
    });

    // Form validation
    $('#assignForm').on('submit', function(e) {
        const userId = $('#user_id').val();
        const startedAt = $('#started_at').val();

        if (!userId) {
            e.preventDefault();
            alert('Pilih anggota yang akan di-assign');
            return false;
        }

        if (!startedAt) {
            e.preventDefault();
            alert('Tanggal mulai wajib diisi');
            return false;
        }

        // Validate end date is after start date
        const endedAt = $('#ended_at').val();
        if (endedAt && endedAt < startedAt) {
            e.preventDefault();
            alert('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai');
            return false;
        }

        return true;
    });

    // End Assignment
    let currentAssignmentId = null;

    $('.btn-end-assignment').on('click', function() {
        currentAssignmentId = $(this).data('assignment-id');
        const memberName = $(this).data('member-name');

        $('#memberNameDisplay').text(memberName);
        $('#endAssignmentModal').modal('show');
    });

    $('#confirmEndAssignment').on('click', function() {
        if (!currentAssignmentId) return;

        const endDate = $('#endDate').val();
        const reason = $('#endReason').val();

        $.ajax({
            url: '<?= base_url('admin/org-structure/assignment/') ?>' + currentAssignmentId + '/end',
            type: 'POST',
            dataType: 'json',
            data: {
                <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                end_date: endDate,
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message || 'Assignment berhasil diakhiri');
                    location.reload();
                } else {
                    alert(response.message || 'Gagal mengakhiri assignment');
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat mengakhiri assignment');
            },
            complete: function() {
                $('#endAssignmentModal').modal('hide');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
