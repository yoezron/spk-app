<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Riwayat Bulk Import</h1>
            <p class="text-muted">Histori semua proses bulk import data</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('admin/bulk-import') ?>" class="btn btn-primary">
                <i class="material-icons-outlined">upload</i> Import Baru
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Import</h6>
                        <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">cloud_upload</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Berhasil</h6>
                        <h3 class="mb-0 text-success"><?= number_format($stats['completed'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
                        <i class="material-icons-outlined">check_circle</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Gagal</h6>
                        <h3 class="mb-0 text-danger"><?= number_format($stats['failed'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-danger-bright text-danger rounded">
                        <i class="material-icons-outlined">error</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Proses</h6>
                        <h3 class="mb-0 text-info"><?= number_format($stats['processing'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-info-bright text-info rounded">
                        <i class="material-icons-outlined">sync</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form action="<?= current_url() ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="failed" <?= (isset($_GET['status']) && $_GET['status'] == 'failed') ? 'selected' : '' ?>>Failed</option>
                    <option value="partial" <?= (isset($_GET['status']) && $_GET['status'] == 'partial') ? 'selected' : '' ?>>Partial</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="import_type" class="form-label">Tipe Import</label>
                <select name="import_type" id="import_type" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="members" <?= (isset($_GET['import_type']) && $_GET['import_type'] == 'members') ? 'selected' : '' ?>>Members</option>
                    <option value="payments" <?= (isset($_GET['import_type']) && $_GET['import_type'] == 'payments') ? 'selected' : '' ?>>Payments</option>
                    <option value="events" <?= (isset($_GET['import_type']) && $_GET['import_type'] == 'events') ? 'selected' : '' ?>>Events</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $_GET['date_from'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Sampai Tanggal</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $_GET['date_to'] ?? '' ?>">
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons-outlined">filter_list</i> Filter
                </button>
                <?php if (!empty($_GET)): ?>
                    <a href="<?= current_url() ?>" class="btn btn-secondary">
                        <i class="material-icons-outlined">clear</i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Import History Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Daftar Import</h5>
            </div>
            <div class="col-auto">
                <span class="text-muted">
                    <?= !empty($imports) ? count($imports) : 0 ?> import
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($imports)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File</th>
                            <th>Tipe</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Sukses</th>
                            <th class="text-center">Gagal</th>
                            <th>Diimpor Oleh</th>
                            <th>Tanggal</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imports as $import): ?>
                            <tr>
                                <td><code><?= esc($import->id) ?></code></td>
                                <td>
                                    <strong><?= esc($import->filename) ?></strong>
                                    <?php if (!empty($import->file_size)): ?>
                                        <br><small class="text-muted"><?= number_format($import->file_size / 1024, 2) ?> KB</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($import->import_type ?? 'members') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= number_format($import->total_rows ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($import->success_count ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= number_format($import->failed_count ?? 0) ?></span>
                                </td>
                                <td>
                                    <small><?= esc($import->imported_by_name ?? 'Admin') ?></small>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d M Y', strtotime($import->created_at)) ?>
                                        <br>
                                        <?= date('H:i', strtotime($import->created_at)) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusConfig = [
                                        'pending' => ['class' => 'bg-warning', 'label' => 'Pending'],
                                        'processing' => ['class' => 'bg-info', 'label' => 'Processing'],
                                        'completed' => ['class' => 'bg-success', 'label' => 'Completed'],
                                        'failed' => ['class' => 'bg-danger', 'label' => 'Failed'],
                                        'partial' => ['class' => 'bg-warning', 'label' => 'Partial']
                                    ];
                                    $status = $statusConfig[$import->status] ?? $statusConfig['pending'];
                                    ?>
                                    <span class="badge <?= $status['class'] ?>">
                                        <?= $status['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('admin/bulk-import/detail/' . $import->id) ?>"
                                           class="btn btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Lihat Detail">
                                            <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                        </a>
                                        <?php if ($import->status === 'completed' || $import->status === 'partial'): ?>
                                            <a href="<?= base_url('admin/bulk-import/download-report/' . $import->id) ?>"
                                               class="btn btn-outline-success"
                                               data-bs-toggle="tooltip"
                                               title="Download Laporan">
                                                <i class="material-icons-outlined" style="font-size: 16px;">download</i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($import->status === 'failed'): ?>
                                            <a href="<?= base_url('admin/bulk-import/retry/' . $import->id) ?>"
                                               class="btn btn-outline-warning"
                                               data-bs-toggle="tooltip"
                                               title="Coba Lagi">
                                                <i class="material-icons-outlined" style="font-size: 16px;">refresh</i>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?= $import->id ?>"
                                                title="Hapus">
                                            <i class="material-icons-outlined" style="font-size: 16px;">delete</i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?= $import->id ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Hapus Riwayat Import</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus riwayat import "<strong><?= esc($import->filename) ?></strong>"?</p>
                                                    <div class="alert alert-warning">
                                                        <i class="material-icons-outlined">warning</i>
                                                        Hanya riwayat yang akan dihapus. Data yang sudah diimpor tidak akan terpengaruh.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="<?= base_url('admin/bulk-import/delete/' . $import->id) ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">cloud_off</i>
                <p class="mt-3 mb-0">Belum ada riwayat import</p>
                <small>
                    <?php if (!empty($_GET)): ?>
                        Tidak ada hasil yang sesuai dengan filter. Silakan ubah filter atau reset.
                    <?php else: ?>
                        Riwayat import akan muncul di sini setelah Anda melakukan import pertama.
                    <?php endif; ?>
                </small>
                <br>
                <a href="<?= base_url('admin/bulk-import') ?>" class="btn btn-primary mt-3">
                    <i class="material-icons-outlined">upload</i>
                    Import Data Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($imports) && isset($pager)): ?>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Auto-refresh for processing imports
    <?php if (!empty($stats['processing']) && $stats['processing'] > 0): ?>
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds if there are processing imports
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
