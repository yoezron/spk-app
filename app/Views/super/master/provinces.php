<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        Master Data Provinsi
                    </h5>
                    <div class="btn-group">
                        <!-- Button Import -->
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i> Import Excel
                        </button>

                        <!-- Button Download Template -->
                        <a href="<?= base_url('super/master/provinces/download-template') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-download me-1"></i> Download Template
                        </a>

                        <!-- Button Tambah Manual -->
                        <button type="button" class="btn btn-gradient-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus me-1"></i> Tambah Provinsi
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->has('import_stats')): ?>
                    <?php $stats = session('import_stats'); ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <h5><i class="fas fa-check-circle me-2"></i> Import Berhasil!</h5>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <strong>Total Data:</strong> <?= $stats['total'] ?>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-success">Berhasil:</strong> <?= $stats['success'] ?>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-danger">Gagal:</strong> <?= $stats['failed'] ?>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-warning">Duplikat:</strong> <?= $stats['duplicates'] ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('import_errors')): ?>
                    <?php $errors = session('import_errors'); ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <h5><i class="fas fa-exclamation-triangle me-2"></i> Detail Error Import</h5>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Baris</th>
                                        <th>Nama Provinsi</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($errors as $error): ?>
                                        <tr>
                                            <td><?= $error['row'] ?></td>
                                            <td><?= esc($error['name']) ?></td>
                                            <td>
                                                <?php foreach ($error['errors'] as $err): ?>
                                                    <span class="badge bg-danger"><?= esc($err) ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-map-marked-alt me-2"></i>
                            <strong><?= number_format($total) ?></strong> Total Provinsi
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover datatable" id="provincesTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode</th>
                                <th width="35%">Nama Provinsi</th>
                                <th width="15%" class="text-center">Jumlah Kab/Kota</th>
                                <th width="15%" class="text-center">Jumlah Anggota</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($provinces)): ?>
                                <?php foreach ($provinces as $index => $province): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php if ($province->code): ?>
                                                <span class="badge bg-secondary"><?= esc($province->code) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= esc($province->name) ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= number_format($province->regency_count) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= number_format($province->member_count) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editProvince(<?= $province->id ?>, '<?= esc($province->name) ?>', '<?= esc($province->code ?? '') ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteProvince(<?= $province->id ?>, '<?= esc($province->name) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data provinsi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('super/master/provinces/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Provinsi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Provinsi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Jawa Barat">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Provinsi</label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: JABAR">
                        <small class="text-muted">Opsional - untuk keperluan internal</small>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Provinsi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Provinsi <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Provinsi</label>
                        <input type="text" name="code" id="edit_code" class="form-control">
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('super/master/provinces/import') ?>" method="POST" enctype="multipart/form-data" id="importForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-import me-2"></i>
                        Import Provinsi dari Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data provinsi sesuai format</li>
                            <li>Upload file Excel yang sudah diisi</li>
                            <li>Sistem akan validasi dan import otomatis</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required id="fileInput">
                        <small class="text-muted">Format: .xlsx atau .xls (Maksimal 5MB)</small>
                    </div>

                    <div id="fileInfo" class="alert alert-secondary d-none">
                        <strong>File yang dipilih:</strong>
                        <div id="fileName"></div>
                        <div id="fileSize" class="text-muted small"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary" id="btnImport">
                        <i class="fas fa-upload me-1"></i> Import Data
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
        // Initialize DataTable
        $('#provincesTable').DataTable({
            responsive: true,
            order: [
                [2, 'asc']
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });
    });

    function editProvince(id, name, code) {
        $('#edit_name').val(name);
        $('#edit_code').val(code);
        $('#editForm').attr('action', `${BASE_URL}/super/master/provinces/${id}/update`);
        $('#editModal').modal('show');
    }

    function deleteProvince(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Yakin ingin menghapus provinsi <strong>${name}</strong>?`,
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
                form.action = `${BASE_URL}/super/master/provinces/${id}/delete`;

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

    // File upload preview
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            fileName.textContent = file.name;
            fileSize.textContent = 'Ukuran: ' + (file.size / 1024).toFixed(2) + ' KB';
            fileInfo.classList.remove('d-none');
        }
    });

    // Import form validation
    document.getElementById('importForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('fileInput');
        const file = fileInput.files[0];

        if (!file) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Silakan pilih file Excel terlebih dahulu'
            });
            return false;
        }

        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 5MB'
            });
            return false;
        }

        // Show loading
        const btnImport = document.getElementById('btnImport');
        btnImport.disabled = true;
        btnImport.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengimport...';
    });
</script>
<?= $this->endSection() ?>