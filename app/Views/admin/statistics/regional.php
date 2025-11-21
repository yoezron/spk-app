<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Statistik Regional</h1>
            <p class="text-muted">Analisis distribusi keanggotaan per wilayah</p>
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

<!-- Regional Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Provinsi</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_provinces'] ?? 0) ?></h3>
                        <small class="text-muted">Dengan anggota aktif</small>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">map</i>
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
                        <h6 class="text-muted mb-1">Total Universitas</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_universities'] ?? 0) ?></h3>
                        <small class="text-muted">Di seluruh Indonesia</small>
                    </div>
                    <div class="avatar bg-info-bright text-info rounded">
                        <i class="material-icons-outlined">school</i>
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
                        <h6 class="text-muted mb-1">Provinsi Terbesar</h6>
                        <h3 class="mb-0 text-success"><?= esc($stats['largest_province_name'] ?? '-') ?></h3>
                        <small class="text-muted"><?= number_format($stats['largest_province_count'] ?? 0) ?> anggota</small>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
                        <i class="material-icons-outlined">star</i>
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
                        <h6 class="text-muted mb-1">Rata-rata/Provinsi</h6>
                        <h3 class="mb-0"><?= number_format($stats['avg_per_province'] ?? 0) ?></h3>
                        <small class="text-muted">Anggota per provinsi</small>
                    </div>
                    <div class="avatar bg-warning-bright text-warning rounded">
                        <i class="material-icons-outlined">show_chart</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Province Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form action="<?= current_url() ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="province" class="form-label">Filter Provinsi</label>
                <select name="province_id" id="province" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Provinsi</option>
                    <?php if (!empty($provinces)): ?>
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?= $province->id ?>"
                                    <?= (isset($_GET['province_id']) && $_GET['province_id'] == $province->id) ? 'selected' : '' ?>>
                                <?= esc($province->name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="sort" class="form-label">Urutkan Berdasarkan</label>
                <select name="sort" id="sort" class="form-select" onchange="this.form.submit()">
                    <option value="members_desc" <?= ($sort ?? 'members_desc') == 'members_desc' ? 'selected' : '' ?>>Jumlah Anggota (Terbanyak)</option>
                    <option value="members_asc" <?= ($sort ?? '') == 'members_asc' ? 'selected' : '' ?>>Jumlah Anggota (Tersedikit)</option>
                    <option value="name_asc" <?= ($sort ?? '') == 'name_asc' ? 'selected' : '' ?>>Nama Provinsi (A-Z)</option>
                    <option value="growth_desc" <?= ($sort ?? '') == 'growth_desc' ? 'selected' : '' ?>>Pertumbuhan (Tertinggi)</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Regional Distribution Map/Chart -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">bar_chart</i>
            Distribusi Anggota per Provinsi
        </h5>
    </div>
    <div class="card-body">
        <canvas id="regionalChart" height="80"></canvas>
    </div>
</div>

<!-- Detailed Province Statistics -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">table_chart</i>
            Detail Statistik Regional
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($regional_data)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Provinsi</th>
                            <th class="text-center">Total Anggota</th>
                            <th class="text-center">Aktif</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Universitas</th>
                            <th class="text-center">% dari Total</th>
                            <th>Pertumbuhan (30 Hari)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; ?>
                        <?php foreach ($regional_data as $data): ?>
                            <tr>
                                <td>
                                    <?php if ($rank <= 3): ?>
                                        <span class="badge bg-warning">#<?= $rank ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">#<?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= esc($data['province_name']) ?></strong>
                                    <?php if ($data['has_coordinator']): ?>
                                        <span class="badge bg-info" data-bs-toggle="tooltip" title="Memiliki Koordinator Wilayah">
                                            <i class="material-icons-outlined" style="font-size: 12px;">person</i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format($data['total_members']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($data['active_members']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= number_format($data['pending_members']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?= number_format($data['university_count']) ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px; max-width: 100px;">
                                            <div class="progress-bar bg-primary"
                                                 role="progressbar"
                                                 style="width: <?= $data['percentage'] ?>%"
                                                 aria-valuenow="<?= $data['percentage'] ?>"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span><?= number_format($data['percentage'], 1) ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $growth = $data['growth_30days'] ?? 0;
                                    $growthClass = $growth > 0 ? 'text-success' : ($growth < 0 ? 'text-danger' : 'text-muted');
                                    $growthIcon = $growth > 0 ? 'trending_up' : ($growth < 0 ? 'trending_down' : 'trending_flat');
                                    ?>
                                    <span class="<?= $growthClass ?>">
                                        <i class="material-icons-outlined" style="font-size: 14px;"><?= $growthIcon ?></i>
                                        <?= $growth > 0 ? '+' : '' ?><?= number_format($growth) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php $rank++; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">map</i>
                <p class="mt-3 mb-0">Tidak ada data regional</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Universities by Province -->
<?php if (isset($_GET['province_id']) && !empty($_GET['province_id'])): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="material-icons-outlined">school</i>
                Universitas di <?= esc($selected_province_name ?? 'Provinsi') ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($universities_in_province)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Universitas</th>
                                <th class="text-center">Total Anggota</th>
                                <th class="text-center">Aktif</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">% dari Provinsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($universities_in_province as $univ): ?>
                                <tr>
                                    <td><strong><?= esc($univ['university_name']) ?></strong></td>
                                    <td class="text-center"><?= number_format($univ['total_members']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= number_format($univ['active_members']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning"><?= number_format($univ['pending_members']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($univ['province_percentage'], 1) ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <p class="mb-0">Tidak ada data universitas untuk provinsi ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Regional Distribution Chart
    <?php if (!empty($regional_data)): ?>
    const ctx = document.getElementById('regionalChart').getContext('2d');

    // Get top 15 provinces for better readability
    const topProvinces = <?= json_encode(array_slice($regional_data, 0, 15)) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: topProvinces.map(p => p.province_name),
            datasets: [{
                label: 'Total Anggota',
                data: topProvinces.map(p => p.total_members),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Anggota Aktif',
                data: topProvinces.map(p => p.active_members),
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
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        }
    });
    <?php endif; ?>

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
<?= $this->endSection() ?>
