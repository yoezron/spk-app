<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-university me-2"></i>
                        Master Data Perguruan Tinggi
                    </h5>
                    <button type="button" class="btn btn-gradient-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-1"></i> Tambah Perguruan Tinggi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Stats & Filter -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-university me-2"></i>
                            <strong><?= number_format($total) ?></strong> Total PT
                        </div>
                    </div>
                    <div class="col-md-9">
                        <form method="GET" action="<?= base_url('super/master/universities') ?>" class="row g-2">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama universitas..." value="<?= esc($search ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="type" class="form-select">
                                    <option value="">-- Semua Jenis PT --</option>
                                    <option value="Negeri" <?= ($selectedType == 'Negeri') ? 'selected' : '' ?>>Negeri</option>
                                    <option value="Swasta" <?= ($selectedType == 'Swasta') ? 'selected' : '' ?>>Swasta</option>
                                    <option value="Kedinasan" <?= ($selectedType == 'Kedinasan') ? 'selected' : '' ?>>Kedinasan</option>
                                </select>
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
                    <table class="table table-hover" id="universitiesTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="8%">Kode</th>
                                <th width="35%">Nama Perguruan Tinggi</th>
                                <th width="12%">Jenis</th>
                                <th width="20%">Alamat</th>
                                <th width="10%" class="text-center">Anggota</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($universities)): ?>
                                <?php foreach ($universities as $index => $univ): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php if ($univ->code): ?>
                                                <span class="badge bg-secondary"><?= esc($univ->code) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= esc($univ->name) ?></strong></td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($univ->type) {
                                                'Negeri' => 'bg-success',
                                                'Swasta' => 'bg-primary',
                                                'Kedinasan' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= esc($univ->type) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= esc($univ->address ?? '-') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= number_format($univ->member_count) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick='editUniversity(<?= json_encode($univ) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick='deleteUniversity(<?= $univ->id ?>, "<?= esc($univ->name) ?>")'>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data perguruan tinggi</td>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('super/master/universities/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Perguruan Tinggi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Perguruan Tinggi <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Universitas Padjadjaran">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode PT</label>
                            <input type="text" name="code" class="form-control" placeholder="UNPAD">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis PT <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Negeri">Negeri</option>
                            <option value="Swasta">Swasta</option>
                            <option value="Kedinasan">Kedinasan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap (opsional)"></textarea>
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
                        Edit Perguruan Tinggi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Perguruan Tinggi <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode PT</label>
                            <input type="text" name="code" id="edit_code" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis PT <span class="text-danger">*</span></label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Negeri">Negeri</option>
                            <option value="Swasta">Swasta</option>
                            <option value="Kedinasan">Kedinasan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
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
        $('#universitiesTable').DataTable({
            responsive: true,
            order: [
                [2, 'asc']
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });
    });

    function editUniversity(univ) {
        $('#edit_name').val(univ.name);
        $('#edit_code').val(univ.code || '');
        $('#edit_type').val(univ.type);
        $('#edit_address').val(univ.address || '');
        $('#editForm').attr('action', `${BASE_URL}/super/master/universities/${univ.id}/update`);
        $('#editModal').modal('show');
    }

    function deleteUniversity(id, name) {
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
                form.action = `${BASE_URL}/super/master/universities/${id}/delete`;

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