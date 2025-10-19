<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Master Data Program Studi
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-excel me-1"></i> Import Excel
                        </button>
                        <a href="<?= base_url('super/master/study-programs/download-template') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-download me-1"></i> Download Template
                        </a>
                        <button type="button" class="btn btn-gradient-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus me-1"></i> Tambah Program Studi
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Import Stats Alert -->
                <?php if (session()->has('import_stats')): ?>
                    <?php $stats = session('import_stats'); ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-chart-bar me-2"></i>
                            Hasil Import Program Studi
                        </h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Total Data:</strong> <?= number_format($stats['total']) ?>
                            </div>
                            <div class="col-md-3 text-success">
                                <strong>Berhasil:</strong> <?= number_format($stats['success']) ?>
                            </div>
                            <div class="col-md-3 text-danger">
                                <strong>Gagal:</strong> <?= number_format($stats['failed']) ?>
                            </div>
                            <div class="col-md-3 text-warning">
                                <strong>Duplikat:</strong> <?= number_format($stats['duplicates']) ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Import Errors Table -->
                <?php if (session()->has('import_errors')): ?>
                    <?php $errors = session('import_errors'); ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Detail Error Import (<?= count($errors) ?> baris)
                        </h5>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th width="8%">Baris</th>
                                        <th width="25%">Perguruan Tinggi</th>
                                        <th width="25%">Program Studi</th>
                                        <th width="10%">Jenjang</th>
                                        <th width="32%">Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($errors as $error): ?>
                                        <tr>
                                            <td><?= $error['row'] ?></td>
                                            <td><?= esc($error['university'] ?? '-') ?></td>
                                            <td><?= esc($error['program'] ?? '-') ?></td>
                                            <td><?= esc($error['level'] ?? '-') ?></td>
                                            <td><small class="text-danger"><?= esc($error['message'] ?? $error['error'] ?? '-') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats & Filter -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>
                            <strong><?= number_format($total) ?></strong> Total Prodi
                        </div>
                    </div>
                    <div class="col-md-9">
                        <form method="GET" action="<?= base_url('super/master/study-programs') ?>" class="row g-2">
                            <div class="col-md-5">
                                <select name="university_id" class="form-select">
                                    <option value="">-- Semua Universitas --</option>
                                    <?php foreach ($universities as $univ): ?>
                                        <option value="<?= $univ->id ?>" <?= ($selectedUniversityId == $univ->id) ? 'selected' : '' ?>>
                                            <?= esc($univ->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama prodi..." value="<?= esc($search ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover datatable" id="programsTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="8%">Kode</th>
                                <th width="30%">Nama Program Studi</th>
                                <th width="25%">Perguruan Tinggi</th>
                                <th width="10%" class="text-center">Jenjang</th>
                                <th width="10%" class="text-center">Anggota</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($programs)): ?>
                                <?php foreach ($programs as $index => $prog): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php if ($prog->code): ?>
                                                <span class="badge bg-secondary"><?= esc($prog->code) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= esc($prog->name) ?></strong></td>
                                        <td>
                                            <small class="text-muted"><?= esc($prog->university_name) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($prog->level): ?>
                                                <span class="badge bg-success"><?= esc($prog->level) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= number_format($prog->member_count) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='editProgram(<?= json_encode($prog) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='deleteProgram(<?= $prog->id ?>, "<?= esc($prog->name) ?>")'>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data program studi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('super/master/study-programs/import') ?>" method="POST" enctype="multipart/form-data" id="importForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-excel me-2"></i>
                        Import Program Studi dari Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Petunjuk Import
                        </h6>
                        <ul class="mb-0 small">
                            <li><strong>Nama Perguruan Tinggi</strong> harus PERSIS sama dengan data master</li>
                            <li><strong>Jenjang</strong> yang valid: <code>D3, D4, S1, S2, S3, Profesi</code></li>
                            <li>Download template terlebih dahulu untuk format yang benar</li>
                            <li>Maksimal ukuran file: 5MB</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: .xlsx atau .xls</small>
                    </div>

                    <div id="filePreview" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-file-excel me-2"></i>
                            <strong id="fileName"></strong>
                            <br>
                            <small id="fileSize"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="importBtn">
                        <i class="fas fa-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('super/master/study-programs/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Program Studi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Perguruan Tinggi <span class="text-danger">*</span></label>
                        <select name="university_id" class="form-select" required>
                            <option value="">-- Pilih Perguruan Tinggi --</option>
                            <?php foreach ($universities as $univ): ?>
                                <option value="<?= $univ->id ?>"><?= esc($univ->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Teknik Informatika">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode Prodi</label>
                            <input type="text" name="code" class="form-control" placeholder="IF">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenjang</label>
                        <select name="level" class="form-select">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3">D3 - Diploma 3</option>
                            <option value="D4">D4 - Diploma 4</option>
                            <option value="S1">S1 - Sarjana</option>
                            <option value="S2">S2 - Magister</option>
                            <option value="S3">S3 - Doktor</option>
                            <option value="Profesi">Profesi</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Program Studi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Perguruan Tinggi <span class="text-danger">*</span></label>
                        <select name="university_id" id="edit_university_id" class="form-select" required>
                            <option value="">-- Pilih Perguruan Tinggi --</option>
                            <?php foreach ($universities as $univ): ?>
                                <option value="<?= $univ->id ?>"><?= esc($univ->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode Prodi</label>
                            <input type="text" name="code" id="edit_code" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenjang</label>
                        <select name="level" id="edit_level" class="form-select">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3">D3 - Diploma 3</option>
                            <option value="D4">D4 - Diploma 4</option>
                            <option value="S1">S1 - Sarjana</option>
                            <option value="S2">S2 - Magister</option>
                            <option value="S3">S3 - Doktor</option>
                            <option value="Profesi">Profesi</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#programsTable').DataTable({
            responsive: true,
            order: [
                [3, 'asc'],
                [2, 'asc']
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });

        // File preview on change
        $('#importFile').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (max 5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar',
                        text: 'Ukuran file maksimal 5MB'
                    });
                    $(this).val('');
                    $('#filePreview').hide();
                    return;
                }

                // Show preview
                $('#fileName').text(file.name);
                $('#fileSize').text((file.size / 1024).toFixed(2) + ' KB');
                $('#filePreview').show();
            } else {
                $('#filePreview').hide();
            }
        });

        // Form validation before submit
        $('#importForm').on('submit', function(e) {
            const file = $('#importFile')[0].files[0];
            if (!file) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan pilih file Excel terlebih dahulu'
                });
                return false;
            }

            // Show loading state
            $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Mengupload...');
        });
    });

    function editProgram(prog) {
        $('#edit_university_id').val(prog.university_id);
        $('#edit_name').val(prog.name);
        $('#edit_code').val(prog.code || '');
        $('#edit_level').val(prog.level || '');
        $('#editForm').attr('action', `${BASE_URL}/super/master/study-programs/${prog.id}/update`);
        $('#editModal').modal('show');
    }

    function deleteProgram(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Yakin ingin menghapus Program Studi <strong>${name}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${BASE_URL}/super/master/study-programs/${id}/delete`;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = CSRF_TOKEN;

                form.appendChild(csrfInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>