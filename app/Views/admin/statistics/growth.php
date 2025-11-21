<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Statistik Pertumbuhan</h1>
            <p class="text-muted">Analisis pertumbuhan anggota dan aktivitas SPK</p>
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

<!-- Time Period Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form action="<?= current_url() ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="period" class="form-label">Periode</label>
                <select name="period" id="period" class="form-select" onchange="this.form.submit()">
                    <option value="30" <?= ($period ?? 30) == 30 ? 'selected' : '' ?>>30 Hari Terakhir</option>
                    <option value="90" <?= ($period ?? 30) == 90 ? 'selected' : '' ?>>90 Hari Terakhir</option>
                    <option value="180" <?= ($period ?? 30) == 180 ? 'selected' : '' ?>>6 Bulan Terakhir</option>
                    <option value="365" <?= ($period ?? 30) == 365 ? 'selected' : '' ?>>1 Tahun Terakhir</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Growth Overview Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Anggota Baru</h6>
                        <h3 class="mb-0"><?= number_format($growth['new_members'] ?? 0) ?></h3>
                        <small class="text-success">
                            <i class="material-icons-outlined" style="font-size: 12px;">trending_up</i>
                            +<?= number_format($growth['new_members_percentage'] ?? 0, 1) ?>%
                        </small>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">person_add</i>
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
                        <h6 class="text-muted mb-1">Total Aktif</h6>
                        <h3 class="mb-0"><?= number_format($growth['total_active'] ?? 0) ?></h3>
                        <small class="text-muted">Anggota aktif saat ini</small>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
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
                        <h6 class="text-muted mb-1">Tingkat Retensi</h6>
                        <h3 class="mb-0"><?= number_format($growth['retention_rate'] ?? 0, 1) ?>%</h3>
                        <small class="text-muted">Anggota yang bertahan</small>
                    </div>
                    <div class="avatar bg-info-bright text-info rounded">
                        <i class="material-icons-outlined">analytics</i>
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
                        <h6 class="text-muted mb-1">Avg. Pertumbuhan/Bulan</h6>
                        <h3 class="mb-0"><?= number_format($growth['avg_growth_per_month'] ?? 0) ?></h3>
                        <small class="text-muted">Anggota baru per bulan</small>
                    </div>
                    <div class="avatar bg-warning-bright text-warning rounded">
                        <i class="material-icons-outlined">show_chart</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Growth Chart -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">insert_chart</i>
            Grafik Pertumbuhan Anggota
        </h5>
    </div>
    <div class="card-body">
        <canvas id="growthChart" height="80"></canvas>
    </div>
</div>

<!-- Monthly Growth Table -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">table_chart</i>
            Detail Pertumbuhan per Bulan
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($monthly_data)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-center">Anggota Baru</th>
                            <th class="text-center">Total Aktif</th>
                            <th class="text-center">Keluar/Nonaktif</th>
                            <th class="text-center">Pertumbuhan Bersih</th>
                            <th class="text-center">% Pertumbuhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthly_data as $data): ?>
                            <tr>
                                <td><strong><?= esc($data['month_label']) ?></strong></td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($data['new_members']) ?></span>
                                </td>
                                <td class="text-center"><?= number_format($data['total_active']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= number_format($data['churned']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $net_growth = $data['new_members'] - $data['churned'];
                                    $growth_class = $net_growth >= 0 ? 'text-success' : 'text-danger';
                                    ?>
                                    <strong class="<?= $growth_class ?>">
                                        <?= $net_growth >= 0 ? '+' : '' ?><?= number_format($net_growth) ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $growth_pct = $data['growth_percentage'];
                                    $pct_class = $growth_pct >= 0 ? 'text-success' : 'text-danger';
                                    ?>
                                    <span class="<?= $pct_class ?>">
                                        <?= $growth_pct >= 0 ? '+' : '' ?><?= number_format($growth_pct, 1) ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">bar_chart</i>
                <p class="mt-3 mb-0">Tidak ada data pertumbuhan</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Growth by Province -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">map</i>
            Pertumbuhan per Provinsi
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($province_growth)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Provinsi</th>
                            <th class="text-center">Anggota Baru</th>
                            <th class="text-center">Total Saat Ini</th>
                            <th class="text-center">% dari Total</th>
                            <th>Tren</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($province_growth as $province): ?>
                            <tr>
                                <td><strong><?= esc($province['province_name']) ?></strong></td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= number_format($province['new_members']) ?></span>
                                </td>
                                <td class="text-center"><?= number_format($province['total_members']) ?></td>
                                <td class="text-center"><?= number_format($province['percentage'], 1) ?>%</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary"
                                             role="progressbar"
                                             style="width: <?= $province['percentage'] ?>%"
                                             aria-valuenow="<?= $province['percentage'] ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <?= number_format($province['percentage'], 1) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Growth Chart
    <?php if (!empty($monthly_data)): ?>
    const ctx = document.getElementById('growthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [<?php foreach ($monthly_data as $data): ?>'<?= $data['month_label'] ?>',<?php endforeach; ?>],
            datasets: [{
                label: 'Anggota Baru',
                data: [<?php foreach ($monthly_data as $data): ?><?= $data['new_members'] ?>,<?php endforeach; ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }, {
                label: 'Total Aktif',
                data: [<?php foreach ($monthly_data as $data): ?><?= $data['total_active'] ?>,<?php endforeach; ?>],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
