<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Detail Import</h1>
            <p class="text-muted">Informasi lengkap proses bulk import</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('admin/bulk-import/history') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <?php if (!empty($import)): ?>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="material-icons-outlined">print</i> Cetak
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($import)): ?>
    <div class="row">
        <!-- Import Summary -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2"><?= esc($import->filename) ?></h4>
                            <div class="text-muted mb-3">
                                <i class="material-icons-outlined" style="font-size: 16px;">person</i>
                                Diimpor oleh: <strong><?= esc($import->imported_by_name ?? 'Admin') ?></strong>
                                <span class="mx-2">•</span>
                                <i class="material-icons-outlined" style="font-size: 16px;">event</i>
                                <?= date('d F Y, H:i', strtotime($import->created_at)) ?>
                            </div>

                            <?php if (!empty($import->notes)): ?>
                                <div class="alert alert-info mb-0">
                                    <i class="material-icons-outlined">notes</i>
                                    <strong>Catatan:</strong> <?= nl2br(esc($import->notes)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php
                            $statusConfig = [
                                'pending' => ['class' => 'bg-warning', 'icon' => 'hourglass_empty', 'label' => 'Pending'],
                                'processing' => ['class' => 'bg-info', 'icon' => 'sync', 'label' => 'Processing'],
                                'completed' => ['class' => 'bg-success', 'icon' => 'check_circle', 'label' => 'Completed'],
                                'failed' => ['class' => 'bg-danger', 'icon' => 'error', 'label' => 'Failed'],
                                'partial' => ['class' => 'bg-warning', 'icon' => 'warning', 'label' => 'Partial Success']
                            ];
                            $status = $statusConfig[$import->status] ?? $statusConfig['pending'];
                            ?>
                            <div class="alert <?= $status['class'] ?> text-white mb-0">
                                <i class="material-icons-outlined" style="font-size: 48px;"><?= $status['icon'] ?></i>
                                <h5 class="mt-2"><?= $status['label'] ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-3">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h2 class="mb-1"><?= number_format($import->total_rows ?? 0) ?></h2>
                            <small class="text-muted">Total Rows</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h2 class="mb-1 text-success"><?= number_format($import->success_count ?? 0) ?></h2>
                            <small class="text-muted">Berhasil</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h2 class="mb-1 text-danger"><?= number_format($import->failed_count ?? 0) ?></h2>
                            <small class="text-muted">Gagal</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h2 class="mb-1 text-warning"><?= number_format($import->skipped_count ?? 0) ?></h2>
                            <small class="text-muted">Dilewati</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <?php if ($import->status === 'processing'): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Progress Import</h6>
                        <?php
                        $progress = $import->total_rows > 0 ? (($import->success_count + $import->failed_count + $import->skipped_count) / $import->total_rows) * 100 : 0;
                        ?>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                 role="progressbar"
                                 style="width: <?= $progress ?>%"
                                 aria-valuenow="<?= $progress ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <?= number_format($progress, 1) ?>%
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Error Details -->
            <?php if (!empty($import->errors) || !empty($errors)): ?>
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="material-icons-outlined">error_outline</i>
                            Error Details (<?= count($errors ?? json_decode($import->errors, true) ?? []) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Baris</th>
                                        <th>Data</th>
                                        <th>Error Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $errorList = $errors ?? json_decode($import->errors, true) ?? [];
                                    ?>
                                    <?php foreach ($errorList as $error): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-danger">#<?= esc($error['row'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($error['data'])): ?>
                                                    <small class="text-muted">
                                                        <?= esc(is_array($error['data']) ? json_encode($error['data']) : $error['data']) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    <i class="material-icons-outlined" style="font-size: 14px;">warning</i>
                                                    <?= esc($error['error'] ?? $error['message'] ?? 'Unknown error') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Successfully Imported Data -->
            <?php if (!empty($imported_data)): ?>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="material-icons-outlined">check_circle</i>
                            Data Berhasil Diimpor (<?= count($imported_data) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>No. Anggota</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($imported_data as $index => $data): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= esc($data->full_name ?? $data->name ?? '-') ?></td>
                                            <td><?= esc($data->email ?? '-') ?></td>
                                            <td><code><?= esc($data->member_number ?? '-') ?></code></td>
                                            <td>
                                                <span class="badge bg-success">Imported</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Import Info -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="material-icons-outlined">info</i>
                        Informasi Import
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <small class="text-muted d-block">ID Import</small>
                        <strong><code><?= esc($import->id) ?></code></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Nama File</small>
                        <strong><?= esc($import->filename) ?></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Tipe Import</small>
                        <strong><?= ucfirst($import->import_type ?? 'members') ?></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Ukuran File</small>
                        <strong><?= !empty($import->file_size) ? number_format($import->file_size / 1024, 2) . ' KB' : 'N/A' ?></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Waktu Mulai</small>
                        <strong><?= date('d M Y, H:i', strtotime($import->created_at)) ?></strong>
                    </div>
                    <?php if (!empty($import->completed_at)): ?>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Waktu Selesai</small>
                            <strong><?= date('d M Y, H:i', strtotime($import->completed_at)) ?></strong>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Durasi</small>
                            <?php
                            $start = new DateTime($import->created_at);
                            $end = new DateTime($import->completed_at);
                            $duration = $start->diff($end);
                            ?>
                            <strong><?= $duration->format('%H:%I:%S') ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="material-icons-outlined">settings</i>
                        Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($import->status === 'completed' || $import->status === 'partial'): ?>
                        <a href="<?= base_url('admin/bulk-import/download-report/' . $import->id) ?>"
                           class="btn btn-primary w-100 mb-2">
                            <i class="material-icons-outlined">download</i>
                            Download Laporan
                        </a>
                    <?php endif; ?>

                    <?php if ($import->status === 'failed'): ?>
                        <a href="<?= base_url('admin/bulk-import/retry/' . $import->id) ?>"
                           class="btn btn-warning w-100 mb-2">
                            <i class="material-icons-outlined">refresh</i>
                            Coba Lagi
                        </a>
                    <?php endif; ?>

                    <a href="<?= base_url('admin/bulk-import/history') ?>"
                       class="btn btn-outline-secondary w-100 mb-2">
                        <i class="material-icons-outlined">history</i>
                        Riwayat Import
                    </a>

                    <a href="<?= base_url('admin/bulk-import') ?>"
                       class="btn btn-outline-primary w-100">
                        <i class="material-icons-outlined">upload</i>
                        Import Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="material-icons-outlined text-muted" style="font-size: 64px;">cloud_off</i>
            <h4 class="mt-3 text-muted">Import Tidak Ditemukan</h4>
            <p class="text-muted">Data import yang Anda cari tidak ditemukan atau sudah dihapus.</p>
            <a href="<?= base_url('admin/bulk-import/history') ?>" class="btn btn-primary mt-3">
                Lihat Riwayat Import
            </a>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
