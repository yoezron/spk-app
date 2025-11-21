<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $title ?></h1>
            <p class="text-muted">Laporan keuangan dan statistik pembayaran iuran</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('admin/payment') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <a href="<?= base_url('admin/payment/export?' . http_build_query($_GET ?? [])) ?>" class="btn btn-success">
                    <i class="material-icons-outlined">download</i> Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="material-icons-outlined">filter_list</i>
            Filter Laporan
        </h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/payment/report') ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun</label>
                <select name="year" id="year" class="form-select">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan (Opsional)</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua Bulan</option>
                    <?php
                    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for ($m = 1; $m <= 12; $m++):
                    ?>
                        <option value="<?= $m ?>" <?= $m == $currentMonth ? 'selected' : '' ?>>
                            <?= $months[$m] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="group_by" class="form-label">Kelompokkan Berdasarkan</label>
                <select name="group_by" id="group_by" class="form-select">
                    <option value="month" <?= $groupBy == 'month' ? 'selected' : '' ?>>Bulan</option>
                    <option value="province" <?= $groupBy == 'province' ? 'selected' : '' ?>>Provinsi</option>
                    <option value="type" <?= $groupBy == 'type' ? 'selected' : '' ?>>Tipe Pembayaran</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="material-icons-outlined">search</i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Pendapatan</h6>
                        <h3 class="mb-0">
                            Rp <?= number_format($summary['total_revenue'] ?? 0, 0, ',', '.') ?>
                        </h3>
                    </div>
                    <div class="display-4 opacity-50">
                        <i class="material-icons-outlined">account_balance_wallet</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Pembayaran Terverifikasi</h6>
                        <h3 class="mb-0"><?= number_format($summary['verified_count'] ?? 0) ?></h3>
                    </div>
                    <div class="display-4 opacity-50">
                        <i class="material-icons-outlined">check_circle</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Menunggu Verifikasi</h6>
                        <h3 class="mb-0"><?= number_format($summary['pending_count'] ?? 0) ?></h3>
                    </div>
                    <div class="display-4 opacity-50">
                        <i class="material-icons-outlined">pending_actions</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Ditolak</h6>
                        <h3 class="mb-0"><?= number_format($summary['rejected_count'] ?? 0) ?></h3>
                    </div>
                    <div class="display-4 opacity-50">
                        <i class="material-icons-outlined">cancel</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Data -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    Detail Laporan
                    <?php if ($currentMonth): ?>
                        - <?= $months[$currentMonth] ?? '' ?> <?= $currentYear ?>
                    <?php else: ?>
                        - Tahun <?= $currentYear ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-info">
                    Dikelompokkan per <?= ucfirst($groupBy) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($reportData)): ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">bar_chart</i>
                <p class="mt-3 mb-0">Tidak ada data pembayaran</p>
                <small>Pilih periode atau filter yang berbeda</small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <?php if ($groupBy == 'month'): ?>
                                    Bulan
                                <?php elseif ($groupBy == 'province'): ?>
                                    Provinsi
                                <?php else: ?>
                                    Tipe Pembayaran
                                <?php endif; ?>
                            </th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <th class="text-end">Total Pendapatan</th>
                            <th class="text-end">Rata-rata</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalTransactions = 0;
                        $grandTotal = 0;

                        foreach ($reportData as $index => $row):
                            $totalTransactions += $row['transaction_count'] ?? 0;
                            $grandTotal += $row['total_amount'] ?? 0;
                        ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= esc($row['label'] ?? 'N/A') ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        <?= number_format($row['transaction_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp <?= number_format($row['total_amount'] ?? 0, 0, ',', '.') ?></strong>
                                </td>
                                <td class="text-end">
                                    <?php
                                    $avg = ($row['transaction_count'] ?? 0) > 0
                                        ? ($row['total_amount'] ?? 0) / $row['transaction_count']
                                        : 0;
                                    ?>
                                    Rp <?= number_format($avg, 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <?php
                                        $verified = $row['verified_count'] ?? 0;
                                        $pending = $row['pending_count'] ?? 0;
                                        $rejected = $row['rejected_count'] ?? 0;
                                        $total = $verified + $pending + $rejected;

                                        if ($total > 0):
                                            $verifiedPct = ($verified / $total) * 100;
                                            $pendingPct = ($pending / $total) * 100;
                                            $rejectedPct = ($rejected / $total) * 100;
                                        ?>
                                            <?php if ($verifiedPct > 0): ?>
                                                <div class="progress-bar bg-success" style="width: <?= $verifiedPct ?>%"
                                                     data-bs-toggle="tooltip"
                                                     title="<?= $verified ?> Verified">
                                                    <?= $verified ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pendingPct > 0): ?>
                                                <div class="progress-bar bg-warning" style="width: <?= $pendingPct ?>%"
                                                     data-bs-toggle="tooltip"
                                                     title="<?= $pending ?> Pending">
                                                    <?= $pending ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($rejectedPct > 0): ?>
                                                <div class="progress-bar bg-danger" style="width: <?= $rejectedPct ?>%"
                                                     data-bs-toggle="tooltip"
                                                     title="<?= $rejected ?> Rejected">
                                                    <?= $rejected ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="progress-bar bg-secondary" style="width: 100%">0</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">TOTAL</th>
                            <th class="text-center">
                                <span class="badge bg-primary"><?= number_format($totalTransactions) ?></span>
                            </th>
                            <th class="text-end">
                                <strong>Rp <?= number_format($grandTotal, 0, ',', '.') ?></strong>
                            </th>
                            <th class="text-end">
                                <?php
                                $grandAvg = $totalTransactions > 0 ? $grandTotal / $totalTransactions : 0;
                                ?>
                                <strong>Rp <?= number_format($grandAvg, 0, ',', '.') ?></strong>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart Section (Optional - if you want to add visualization) -->
<?php if (!empty($reportData)): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Grafik Pendapatan</h5>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="80"></canvas>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Revenue Chart
        <?php if (!empty($reportData)): ?>
        const chartData = {
            labels: [<?php foreach ($reportData as $row): ?>'<?= esc($row['label']) ?>',<?php endforeach; ?>],
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: [<?php foreach ($reportData as $row): ?><?= $row['total_amount'] ?? 0 ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        };

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>
