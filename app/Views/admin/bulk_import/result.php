<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Import Selesai</div>
                <h2 class="page-title">Hasil Import Data Anggota</h2>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <a href="<?= site_url('admin/bulk-import/history') ?>" class="btn btn-outline-secondary">
                        <i class="ti ti-history me-1"></i>
                        Riwayat Import
                    </a>
                    <a href="<?= site_url('admin/bulk-import') ?>" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i>
                        Import Lagi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Body -->
<div class="page-body">
    <div class="container-xl">

        <!-- Success Alert -->
        <?php if ($importLog->status === 'completed'): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-check icon alert-icon"></i>
                    </div>
                    <div>
                        <h4 class="alert-title">Import Berhasil!</h4>
                        <div class="text-muted">
                            Data anggota berhasil diimport. Email aktivasi telah dikirim ke setiap member.
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Import Summary Cards -->
        <div class="row row-cards mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Data</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="h1 mb-0 me-2"><?= number_format($importLog->total_rows) ?></div>
                            <div class="me-auto">
                                <span class="text-muted">baris</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Berhasil</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="h1 mb-0 me-2 text-success"><?= number_format($importLog->success_count) ?></div>
                            <div class="me-auto">
                                <span class="text-green d-inline-flex align-items-center lh-1">
                                    <?= $importLog->total_rows > 0 ? round(($importLog->success_count / $importLog->total_rows) * 100, 1) : 0 ?>%
                                    <i class="ti ti-trending-up ms-1"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Gagal</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="h1 mb-0 me-2 text-danger"><?= number_format($importLog->failed_count) ?></div>
                            <div class="me-auto">
                                <span class="text-red d-inline-flex align-items-center lh-1">
                                    <?= $importLog->total_rows > 0 ? round(($importLog->failed_count / $importLog->total_rows) * 100, 1) : 0 ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Duplikat</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="h1 mb-0 me-2 text-warning"><?= number_format($importLog->duplicate_count) ?></div>
                            <div class="me-auto">
                                <span class="text-warning d-inline-flex align-items-center lh-1">
                                    <?= $importLog->total_rows > 0 ? round(($importLog->duplicate_count / $importLog->total_rows) * 100, 1) : 0 ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activation Statistics -->
        <div class="row row-cards mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status Aktivasi Member</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-lg me-3 bg-blue-lt">
                                        <i class="ti ti-users"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted small">Total Member</div>
                                        <div class="h3 mb-0"><?= number_format($activationStats['total']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-lg me-3 bg-green-lt">
                                        <i class="ti ti-check"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted small">Sudah Aktivasi</div>
                                        <div class="h3 mb-0 text-success"><?= number_format($activationStats['activated']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-lg me-3 bg-yellow-lt">
                                        <i class="ti ti-clock"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted small">Belum Aktivasi</div>
                                        <div class="h3 mb-0 text-warning"><?= number_format($activationStats['pending']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-lg me-3 bg-red-lt">
                                        <i class="ti ti-x"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted small">Token Expired</div>
                                        <div class="h3 mb-0 text-danger"><?= number_format($activationStats['expired']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activation Progress Bar -->
                        <div class="mt-4">
                            <div class="d-flex mb-2">
                                <div>Activation Rate</div>
                                <div class="ms-auto">
                                    <span class="text-muted"><?= number_format($activationStats['activation_rate'], 1) ?>%</span>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success"
                                    style="width: <?= $activationStats['activation_rate'] ?>%"
                                    role="progressbar"
                                    aria-valuenow="<?= $activationStats['activation_rate'] ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                    <?= number_format($activationStats['activation_rate'], 1) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Details -->
        <div class="row row-cards">
            <div class="col-lg-8">
                <!-- Error Details -->
                <?php if (!empty($errorDetails)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Detail Error (<?= count($errorDetails) ?>)</h3>
                            <div class="card-actions">
                                <a href="<?= site_url('admin/bulk-import/download-error-report/' . $importLog->id) ?>"
                                    class="btn btn-sm btn-outline-danger">
                                    <i class="ti ti-download me-1"></i>
                                    Download Error Report
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($errorDetails as $error): ?>
                                    <div class="list-group-item">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <span class="badge bg-red">Row <?= esc($error['row']) ?></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong><?= esc($error['data']['email'] ?? 'N/A') ?></strong>
                                                </div>
                                                <div class="text-muted small"><?= esc($error['error']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Pending Activation Members -->
                <?php if ($activationStats['pending'] > 0): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Member Belum Aktivasi</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-sm btn-primary" onclick="resendAllActivation()">
                                    <i class="ti ti-mail me-1"></i>
                                    Kirim Ulang Semua Email
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="pending-members-list">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted">Memuat data...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Import Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Info Import</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col text-muted">Filename</div>
                                <div class="col-auto">
                                    <strong><?= esc($importLog->filename) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col text-muted">Tanggal Import</div>
                                <div class="col-auto">
                                    <?= date('d M Y H:i', strtotime($importLog->created_at)) ?>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col text-muted">Status</div>
                                <div class="col-auto">
                                    <?php if ($importLog->status === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($importLog->status === 'processing'): ?>
                                        <span class="badge bg-warning">Processing</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= site_url('admin/members') ?>" class="list-group-item list-group-item-action">
                            <i class="ti ti-users me-2"></i>
                            Lihat Semua Member
                        </a>
                        <a href="<?= site_url('admin/members?status=pending_activation') ?>" class="list-group-item list-group-item-action">
                            <i class="ti ti-clock me-2"></i>
                            Member Pending Aktivasi
                        </a>
                        <a href="<?= site_url('admin/bulk-import') ?>" class="list-group-item list-group-item-action">
                            <i class="ti ti-upload me-2"></i>
                            Import Data Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Load pending members if any
        <?php if ($activationStats['pending'] > 0): ?>
            loadPendingMembers();
        <?php endif; ?>

        // Auto refresh activation stats every 30 seconds
        setInterval(function() {
            refreshActivationStats();
        }, 30000);
    });

    function loadPendingMembers() {
        $.ajax({
            url: '<?= site_url('admin/bulk-import/get-activation-stats/' . $importLog->id) ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update stats display
                    updateStatsDisplay(response.data);
                }
            },
            error: function() {
                $('#pending-members-list').html(
                    '<div class="alert alert-danger">Gagal memuat data</div>'
                );
            }
        });
    }

    function refreshActivationStats() {
        $.ajax({
            url: '<?= site_url('admin/bulk-import/get-activation-stats/' . $importLog->id) ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Silently update stats
                    updateStatsDisplay(response.data);
                }
            }
        });
    }

    function updateStatsDisplay(stats) {
        // Update activation stats on page
        // Implementation depends on your needs
        console.log('Stats updated:', stats);
    }

    function resendActivation(userId) {
        if (!confirm('Kirim ulang email aktivasi ke member ini?')) {
            return;
        }

        $.ajax({
            url: '<?= site_url('admin/bulk-import/resend-activation/') ?>' + userId,
            type: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat mengirim email');
            }
        });
    }

    function resendAllActivation() {
        if (!confirm('Kirim ulang email aktivasi ke semua member yang belum aktivasi?')) {
            return;
        }

        toastr.info('Fitur ini akan segera tersedia');
        // TODO: Implement bulk resend activation
    }
</script>
<?= $this->endSection() ?>