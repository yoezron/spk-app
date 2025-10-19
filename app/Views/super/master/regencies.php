<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-city me-2"></i>
                        Master Data Kabupaten/Kota
                    </h5>
                    <button type="button" class="btn btn-gradient-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-1"></i> Tambah Kabupaten/Kota
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Stats & Filter -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-city me-2"></i>
                            <strong><?= number_format($total) ?></strong> Total Kabupaten/Kota
                        </div>
                    </div>
                    <div class="col-md-8">
                        <form method="GET" action="<?= base_url('super/master/regencies') ?>">
                            <div class="input-group">
                                <select name="province_id" class="form-select">
                                    <option value="">-- Semua Provinsi --</option>
                                    <?php foreach ($provinces as $prov): ?>
                                        <option value="<?= $prov->id ?>" <?= ($selectedProvinceId == $prov->id) ? 'selected' : '' ?>>
                                            <?= esc($prov->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <?php if ($selectedProvinceId): ?>
                                    <a href="<?= base_url('super/master/regencies') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="regenciesTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode</th>
                                <th width="30%">Nama Kabupaten/Kota</th>
                                <th width="25%">Provinsi</th>
                                <th width="15%" class="text-center">Jumlah Anggota</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($regencies)): ?>
                                <?php foreach ($regencies as $index => $regency): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php if ($regency->code): ?>
                                                <span class="badge bg-secondary"><?= esc($regency->code) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= esc($regency->name) ?></strong></td>
                                        <td>
                                            <span class="badge bg-info"><?= esc($regency->province_name) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= number_format($regency->member_count) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='editRegency(<?= $regency->id ?>, <?= $regency->province_id ?>, "<?= esc($regency->name) ?>", "<?= esc($regency->code ?? "") ?>")'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='deleteRegency(<?= $regency->id ?>, "<?= esc($regency->name) ?>")'>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data kabupaten/kota</td>
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
            <form action="<?= base_url('super/master/regencies/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Kabupaten/Kota
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                        <select name="province_id" class="form-select" required>
                            <option value="">-- Pilih Provinsi --</option>
                            <?php foreach ($provinces as $prov): ?>
                                <option value="<?= $prov->id ?>"><?= esc($prov->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kabupaten/Kota <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Kota Bandung">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: BDG">
                        <small class="text-muted">Opsional</small>
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
                        Edit Kabupaten/Kota
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                        <select name="province_id" id="edit_province_id" class="form-select" required>
                            <option value="">-- Pilih Provinsi --</option>
                            <?php foreach ($provinces as $prov): ?>
                                <option value="<?= $prov->id ?>"><?= esc($prov->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kabupaten/Kota <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Wait for DOM to be fully loaded
        setTimeout(function() {
            // Destroy any existing instance
            if ($.fn.DataTable.isDataTable('#regenciesTable')) {
                $('#regenciesTable').DataTable().destroy();
            }

            // Remove any DataTables classes
            $('#regenciesTable').removeClass('dataTable');

            // Initialize fresh
            $('#regenciesTable').DataTable({
                responsive: true,
                destroy: true, // Force destroy on init
                order: [
                    [3, 'asc'],
                    [2, 'asc']
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                }
            });
        }, 100);
    });

    function editRegency(id, provinceId, name, code) {
        $('#edit_province_id').val(provinceId);
        $('#edit_name').val(name);
        $('#edit_code').val(code);
        $('#editForm').attr('action', `${BASE_URL}/super/master/regencies/${id}/update`);
        $('#editModal').modal('show');
    }

    function deleteRegency(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Yakin ingin menghapus <strong>${name}</strong>?`,
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
                form.action = `${BASE_URL}/super/master/regencies/${id}/delete`;

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