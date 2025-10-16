<?php

/**
 * View: Admin Statistics & Analytics Dashboard
 * Controller: Admin\StatisticsController::index()
 * Description: Comprehensive statistics dashboard dengan charts, analytics, dan insights
 * 
 * Features:
 * - Overview statistics cards (members, growth, regional, etc)
 * - Interactive charts (Chart.js) - line, bar, pie, doughnut
 * - Member growth trend analysis
 * - Regional distribution map/chart
 * - University distribution analytics
 * - Gender & age demographics
 * - Membership status breakdown
 * - Top statistics (provinces, universities, etc)
 * - Filter by date range
 * - Export reports (Excel/PDF)
 * - Real-time data updates (AJAX)
 * - Regional scope untuk Koordinator Wilayah
 * - Responsive design
 * 
 * @package App\Views\Admin\Statistics
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css') ?>">
<style>
    /* Page Header */
    .page-header-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .page-header-content h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header-content p {
        opacity: 0.95;
        margin-bottom: 0;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto auto;
        gap: 15px;
        align-items: end;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.primary {
        border-left-color: #667eea;
    }

    .stat-card.success {
        border-left-color: #27ae60;
    }

    .stat-card.info {
        border-left-color: #17a2b8;
    }

    .stat-card.warning {
        border-left-color: #f39c12;
    }

    .stat-card.danger {
        border-left-color: #e74c3c;
    }

    .stat-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }

    .stat-card.primary .stat-card-icon {
        background: #e8eaf6;
        color: #667eea;
    }

    .stat-card.success .stat-card-icon {
        background: #d5f4e6;
        color: #27ae60;
    }

    .stat-card.info .stat-card-icon {
        background: #d1ecf1;
        color: #17a2b8;
    }

    .stat-card.warning .stat-card-icon {
        background: #fff3cd;
        color: #f39c12;
    }

    .stat-card.danger .stat-card-icon {
        background: #f8d7da;
        color: #e74c3c;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 8px;
        line-height: 1;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 12px;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .stat-trend.up {
        background: #d5f4e6;
        color: #0d5826;
    }

    .stat-trend.down {
        background: #f8d7da;
        color: #721c24;
    }

    .stat-trend i {
        font-size: 14px;
    }

    /* Charts Section */
    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .chart-card.full-width {
        grid-column: 1 / -1;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .chart-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .chart-actions {
        display: flex;
        gap: 8px;
    }

    .chart-container {
        position: relative;
        height: 350px;
    }

    /* Top Lists */
    .top-list-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .top-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .top-list-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .top-list-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .top-list-item:hover {
        border-color: #667eea;
        background: #f5f7ff;
    }

    .top-list-rank {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .top-list-rank.gold {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: white;
    }

    .top-list-rank.silver {
        background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
        color: white;
    }

    .top-list-rank.bronze {
        background: linear-gradient(135deg, #CD7F32, #8B4513);
        color: white;
    }

    .top-list-rank.other {
        background: #e9ecef;
        color: #495057;
    }

    .top-list-info {
        flex: 1;
    }

    .top-list-name {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 4px 0;
    }

    .top-list-detail {
        font-size: 13px;
        color: #6c757d;
    }

    .top-list-value {
        font-size: 18px;
        font-weight: 700;
        color: #667eea;
    }

    /* Scope Badge */
    .scope-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        margin-left: 12px;
        backdrop-filter: blur(10px);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .charts-section {
            grid-template-columns: 1fr;
        }

        .filter-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-header-content {
            padding: 20px;
        }

        .page-header-content h1 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .chart-container {
            height: 250px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1>
                <i class="bi bi-graph-up me-2"></i>
                Statistik & Analytics
                <?php if (isset($scope_data) && $scope_data): ?>
                    <span class="scope-badge">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= esc($scope_data['province_name']) ?>
                    </span>
                <?php endif; ?>
            </h1>
            <p>Dashboard analytics comprehensive dengan insights dan trends</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-light" onclick="refreshData()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
            <a href="<?= base_url('admin/statistics/export') ?>" class="btn btn-success">
                <i class="bi bi-download me-1"></i> Export Report
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Statistik</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Filter Section -->
<div class="filter-section">
    <form method="GET" action="<?= base_url('admin/statistics') ?>" id="filterForm">
        <div class="filter-row">
            <div>
                <label class="form-label">Periode</label>
                <input
                    type="text"
                    class="form-control"
                    id="daterange"
                    name="daterange"
                    value="<?= date('01/m/Y') ?> - <?= date('d/m/Y') ?>">
            </div>

            <div>
                <label class="form-label">Tipe Statistik</label>
                <select class="form-select" name="stat_type">
                    <option value="overview">Overview</option>
                    <option value="members">Keanggotaan</option>
                    <option value="regional">Regional</option>
                    <option value="university">Universitas</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>

            <div>
                <a href="<?= base_url('admin/statistics') ?>" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <!-- Total Members -->
    <div class="stat-card primary">
        <div class="stat-card-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['total_members'] ?? 0) ?></div>
        <div class="stat-label">Total Anggota</div>
        <?php if (isset($stats['member_growth']) && $stats['member_growth'] != 0): ?>
            <div class="stat-trend <?= $stats['member_growth'] > 0 ? 'up' : 'down' ?>">
                <i class="bi bi-arrow-<?= $stats['member_growth'] > 0 ? 'up' : 'down' ?>"></i>
                <?= abs($stats['member_growth']) ?>% vs bulan lalu
            </div>
        <?php endif; ?>
    </div>

    <!-- New Members -->
    <div class="stat-card success">
        <div class="stat-card-icon">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['new_members'] ?? 0) ?></div>
        <div class="stat-label">Anggota Baru (Bulan Ini)</div>
    </div>

    <!-- Active Members -->
    <div class="stat-card info">
        <div class="stat-card-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['active_members'] ?? 0) ?></div>
        <div class="stat-label">Anggota Aktif</div>
    </div>

    <!-- Pending Approvals -->
    <div class="stat-card warning">
        <div class="stat-card-icon">
            <i class="bi bi-clock-fill"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['pending_approvals'] ?? 0) ?></div>
        <div class="stat-label">Menunggu Approval</div>
    </div>

    <!-- Total Provinces -->
    <?php if (!isset($is_koordinator) || !$is_koordinator): ?>
        <div class="stat-card primary">
            <div class="stat-card-icon">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($stats['total_provinces'] ?? 0) ?></div>
            <div class="stat-label">Provinsi Terjangkau</div>
        </div>
    <?php endif; ?>

    <!-- Total Universities -->
    <div class="stat-card info">
        <div class="stat-card-icon">
            <i class="bi bi-building"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['total_universities'] ?? 0) ?></div>
        <div class="stat-label">Universitas/Kampus</div>
    </div>

    <!-- Forum Threads -->
    <div class="stat-card success">
        <div class="stat-card-icon">
            <i class="bi bi-chat-dots-fill"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['forum_threads'] ?? 0) ?></div>
        <div class="stat-label">Diskusi Forum</div>
    </div>

    <!-- Surveys -->
    <div class="stat-card warning">
        <div class="stat-card-icon">
            <i class="bi bi-clipboard-data"></i>
        </div>
        <div class="stat-value"><?= number_format($stats['total_surveys'] ?? 0) ?></div>
        <div class="stat-label">Survei Aktif</div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <!-- Member Growth Chart -->
    <div class="chart-card full-width">
        <div class="chart-header">
            <h3>
                <i class="bi bi-graph-up-arrow me-2"></i>
                Pertumbuhan Anggota
            </h3>
            <div class="chart-actions">
                <select class="form-select form-select-sm" id="growthPeriod" onchange="updateGrowthChart()">
                    <option value="6">6 Bulan Terakhir</option>
                    <option value="12" selected>12 Bulan Terakhir</option>
                    <option value="24">24 Bulan Terakhir</option>
                </select>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="memberGrowthChart"></canvas>
        </div>
    </div>

    <!-- Regional Distribution -->
    <?php if (!isset($is_koordinator) || !$is_koordinator): ?>
        <div class="chart-card">
            <div class="chart-header">
                <h3>
                    <i class="bi bi-geo-alt me-2"></i>
                    Distribusi Regional
                </h3>
            </div>
            <div class="chart-container">
                <canvas id="regionalChart"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- University Distribution -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>
                <i class="bi bi-building me-2"></i>
                Distribusi Universitas
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="universityChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>
                <i class="bi bi-pie-chart me-2"></i>
                Status Keanggotaan
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Gender Distribution -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>
                <i class="bi bi-gender-ambiguous me-2"></i>
                Distribusi Gender
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="genderChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Statistics -->
<div class="row">
    <!-- Top Provinces -->
    <?php if (!isset($is_koordinator) || !$is_koordinator): ?>
        <div class="col-lg-6 mb-4">
            <div class="top-list-card">
                <div class="top-list-header">
                    <h3>
                        <i class="bi bi-trophy me-2"></i>
                        Top 10 Provinsi
                    </h3>
                    <span class="badge bg-primary">Terbanyak Anggota</span>
                </div>

                <?php if (!empty($top_stats['provinces'])): ?>
                    <?php foreach ($top_stats['provinces'] as $index => $province): ?>
                        <div class="top-list-item">
                            <div class="top-list-rank <?= $index < 3 ? ($index === 0 ? 'gold' : ($index === 1 ? 'silver' : 'bronze')) : 'other' ?>">
                                <?= $index + 1 ?>
                            </div>
                            <div class="top-list-info">
                                <div class="top-list-name"><?= esc($province->name) ?></div>
                                <div class="top-list-detail">
                                    <?= $province->university_count ?> Universitas
                                </div>
                            </div>
                            <div class="top-list-value">
                                <?= number_format($province->member_count) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-4">Tidak ada data</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Universities -->
    <div class="col-lg-6 mb-4">
        <div class="top-list-card">
            <div class="top-list-header">
                <h3>
                    <i class="bi bi-mortarboard me-2"></i>
                    Top 10 Universitas
                </h3>
                <span class="badge bg-success">Terbanyak Anggota</span>
            </div>

            <?php if (!empty($top_stats['universities'])): ?>
                <?php foreach ($top_stats['universities'] as $index => $university): ?>
                    <div class="top-list-item">
                        <div class="top-list-rank <?= $index < 3 ? ($index === 0 ? 'gold' : ($index === 1 ? 'silver' : 'bronze')) : 'other' ?>">
                            <?= $index + 1 ?>
                        </div>
                        <div class="top-list-info">
                            <div class="top-list-name"><?= esc($university->name) ?></div>
                            <div class="top-list-detail">
                                <?= esc($university->province_name) ?>
                            </div>
                        </div>
                        <div class="top-list-value">
                            <?= number_format($university->member_count) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js -->
<script src="<?= base_url('assets/plugins/chart.js/chart.min.js') ?>"></script>
<!-- Date Range Picker -->
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>

<script>
    $(document).ready(function() {

        // ==========================================
        // DATE RANGE PICKER
        // ==========================================
        $('#daterange').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Custom',
                weekLabel: 'W',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ],
                firstDay: 1
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment()]
            }
        });

        // ==========================================
        // CHART.JS CONFIGURATION
        // ==========================================
        Chart.defaults.font.family = 'Poppins, sans-serif';
        Chart.defaults.color = '#6c757d';

        // Chart Data (from controller)
        const trendData = <?= json_encode($trend_data ?? []) ?>;

        // ==========================================
        // MEMBER GROWTH CHART
        // ==========================================
        const growthCtx = document.getElementById('memberGrowthChart');
        if (growthCtx && trendData.member_growth) {
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: trendData.member_growth.map(item => item.month),
                    datasets: [{
                        label: 'Jumlah Anggota',
                        data: trendData.member_growth.map(item => item.count),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return 'Anggota: ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // ==========================================
        // REGIONAL DISTRIBUTION CHART
        // ==========================================
        const regionalCtx = document.getElementById('regionalChart');
        if (regionalCtx && trendData.regional_distribution) {
            new Chart(regionalCtx, {
                type: 'bar',
                data: {
                    labels: trendData.regional_distribution.map(item => item.province_name),
                    datasets: [{
                        label: 'Jumlah Anggota',
                        data: trendData.regional_distribution.map(item => item.total),
                        backgroundColor: '#667eea',
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // ==========================================
        // STATUS DISTRIBUTION CHART
        // ==========================================
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && trendData.status_distribution) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: trendData.status_distribution.map(item => item.membership_status),
                    datasets: [{
                        data: trendData.status_distribution.map(item => item.total),
                        backgroundColor: [
                            '#667eea',
                            '#27ae60',
                            '#f39c12',
                            '#e74c3c',
                            '#95a5a6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Additional charts for university and gender can be added similarly

        console.log('✓ Statistics Dashboard initialized');
    });

    // ==========================================
    // REFRESH DATA
    // ==========================================
    function refreshData() {
        location.reload();
    }

    // ==========================================
    // UPDATE GROWTH CHART
    // ==========================================
    function updateGrowthChart() {
        // Implementation for changing growth period
        // You can make an AJAX call to get new data
        console.log('Updating growth chart...');
    }
</script>
<?= $this->endSection() ?>