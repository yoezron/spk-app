<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Statistik Anggota</h1>
            <p class="text-muted">Analisis demografi dan statistik keanggotaan SPK</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="material-icons-outlined">print</i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Anggota</h6>
                        <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                        <small class="text-muted">Semua status</small>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">groups</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Anggota Aktif</h6>
                        <h3 class="mb-0 text-success"><?= number_format($stats['active'] ?? 0) ?></h3>
                        <small class="text-success">
                            <?= number_format(($stats['active'] / max($stats['total'], 1)) * 100, 1) ?>% dari total
                        </small>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
                        <i class="material-icons-outlined">check_circle</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending Verifikasi</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($stats['pending'] ?? 0) ?></h3>
                        <small class="text-muted">Menunggu verifikasi</small>
                    </div>
                    <div class="avatar bg-warning-bright text-warning rounded">
                        <i class="material-icons-outlined">hourglass_empty</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tidak Aktif</h6>
                        <h3 class="mb-0 text-danger"><?= number_format($stats['inactive'] ?? 0) ?></h3>
                        <small class="text-muted">Status nonaktif</small>
                    </div>
                    <div class="avatar bg-danger-bright text-danger rounded">
                        <i class="material-icons-outlined">cancel</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Demographics Charts -->
<div class="row mb-3">
    <!-- Gender Distribution -->
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">pie_chart</i>
                    Distribusi Gender
                </h5>
            </div>
            <div class="card-body">
                <canvas id="genderChart" height="200"></canvas>
                <?php if (!empty($gender_stats)): ?>
                    <div class="mt-3">
                        <?php foreach ($gender_stats as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?= esc($stat['gender_label']) ?></span>
                                <strong><?= number_format($stat['count']) ?> (<?= number_format($stat['percentage'], 1) ?>%)</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Age Distribution -->
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">bar_chart</i>
                    Distribusi Usia
                </h5>
            </div>
            <div class="card-body">
                <canvas id="ageChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- University Distribution -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">school</i>
            Distribusi per Universitas
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($university_stats)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Universitas</th>
                            <th class="text-center">Total Anggota</th>
                            <th class="text-center">Aktif</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">% dari Total</th>
                            <th>Tren</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($university_stats as $univ): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($univ['university_name']) ?></strong>
                                    <br><small class="text-muted"><?= esc($univ['province_name']) ?></small>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format($univ['total_members']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($univ['active_members']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= number_format($univ['pending_members']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?= number_format($univ['percentage'], 1) ?>%
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary"
                                             role="progressbar"
                                             style="width: <?= $univ['percentage'] ?>%"
                                             aria-valuenow="<?= $univ['percentage'] ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <?= number_format($univ['percentage'], 1) ?>%
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
                <i class="material-icons-outlined" style="font-size: 64px;">school</i>
                <p class="mt-3 mb-0">Tidak ada data universitas</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Membership Type Distribution -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">category</i>
            Distribusi Tipe Keanggotaan
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php if (!empty($membership_type_stats)): ?>
                <?php foreach ($membership_type_stats as $type): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= number_format($type['count']) ?></h3>
                                <p class="text-muted mb-1"><?= esc($type['type_label']) ?></p>
                                <small class="text-muted"><?= number_format($type['percentage'], 1) ?>% dari total</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-4">
                    <p class="mb-0">Tidak ada data tipe keanggotaan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Registrations -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">person_add</i>
                    Registrasi Terbaru
                </h5>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/members') ?>" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($recent_members)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No. Anggota</th>
                            <th>Universitas</th>
                            <th>Provinsi</th>
                            <th>Tanggal Daftar</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_members as $member): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($member->full_name) ?></strong>
                                    <br><small class="text-muted"><?= esc($member->email) ?></small>
                                </td>
                                <td><code><?= esc($member->member_number) ?></code></td>
                                <td><?= esc($member->university_name) ?></td>
                                <td><?= esc($member->province_name) ?></td>
                                <td>
                                    <small><?= date('d M Y, H:i', strtotime($member->created_at)) ?></small>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusBadges = [
                                        'pending' => 'bg-warning',
                                        'active' => 'bg-success',
                                        'inactive' => 'bg-secondary',
                                        'suspended' => 'bg-danger'
                                    ];
                                    $badgeClass = $statusBadges[$member->status] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($member->status) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">person_add</i>
                <p class="mt-3 mb-0">Tidak ada registrasi terbaru</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Gender Chart
    <?php if (!empty($gender_stats)): ?>
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($gender_stats as $stat): ?>'<?= $stat['gender_label'] ?>',<?php endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($gender_stats as $stat): ?><?= $stat['count'] ?>,<?php endforeach; ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>

    // Age Chart
    <?php if (!empty($age_stats)): ?>
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'bar',
        data: {
            labels: [<?php foreach ($age_stats as $stat): ?>'<?= $stat['age_group'] ?>',<?php endforeach; ?>],
            datasets: [{
                label: 'Jumlah Anggota',
                data: [<?php foreach ($age_stats as $stat): ?><?= $stat['count'] ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
