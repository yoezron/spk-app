<?= $this->extend('layouts/super') ?>

<?= $this->section('styles') ?>
<style>
    /* Dashboard Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--card-color);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary::before {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.success::before {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card.warning::before {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card.danger::before {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stat-card.info::before {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .stat-content {
        margin-top: 1rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .stat-description {
        font-size: 0.75rem;
        color: #95a5a6;
        margin: 0;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-top: 0.5rem;
    }

    .stat-trend.up {
        background: #d4edda;
        color: #155724;
    }

    .stat-trend.down {
        background: #f8d7da;
        color: #721c24;
    }

    /* Chart Card */
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }

    .chart-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .chart-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .chart-subtitle {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }

    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-action-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }

    .quick-action-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .quick-action-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .quick-action-description {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Recent Activities */
    .activity-list {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .activity-item {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-user {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .activity-description {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #95a5a6;
    }

    /* System Health */
    .health-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .health-badge.healthy {
        background: #d4edda;
        color: #155724;
    }

    .health-badge.warning {
        background: #fff3cd;
        color: #856404;
    }

    .health-badge.error {
        background: #f8d7da;
        color: #721c24;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4 animate-fade-in-up">
        <div class="col-12">
            <h1 class="h3 mb-2">
                <i class="fas fa-tachometer-alt me-2" style="color: #667eea;"></i>
                Dashboard Super Admin
            </h1>
            <p class="text-muted mb-0">
                Selamat datang kembali, <strong><?= esc($user->username ?? 'Super Admin') ?></strong>!
                Berikut adalah ringkasan lengkap sistem SPK.
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Total Users -->
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">people</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= number_format($stats['total_users'] ?? 0) ?></div>
                <p class="stat-description">Semua pengguna terdaftar</p>
                <?php if (($stats['new_members_this_month'] ?? 0) > 0): ?>
                    <span class="stat-trend up">
                        <i class="material-icons-outlined" style="font-size: 16px;">trending_up</i>
                        +<?= number_format($stats['new_members_this_month']) ?> bulan ini
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Members -->
        <div class="stat-card warning">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">pending</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Approval</div>
                <div class="stat-value"><?= number_format($stats['pending_members'] ?? 0) ?></div>
                <p class="stat-description">Menunggu verifikasi</p>
                <?php if (($stats['pending_members'] ?? 0) > 0): ?>
                    <a href="<?= base_url('admin/members?status=pending') ?>" class="btn btn-sm btn-warning mt-2">
                        Review Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Members -->
        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">check_circle</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Users</div>
                <div class="stat-value"><?= number_format($stats['active_members'] ?? 0) ?></div>
                <p class="stat-description">Aktif 30 hari terakhir</p>
                <?php
                $activePercentage = ($stats['total_users'] ?? 0) > 0
                    ? round((($stats['active_members'] ?? 0) / $stats['total_users']) * 100, 1)
                    : 0;
                ?>
                <small class="text-muted"><?= $activePercentage ?>% engagement</small>
            </div>
        </div>

        <!-- Total Roles -->
        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">shield</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Roles</div>
                <div class="stat-value"><?= number_format($stats['total_roles'] ?? 0) ?></div>
                <p class="stat-description"><?= number_format($stats['total_permissions'] ?? 0) ?> permissions</p>
                <a href="<?= base_url('super/roles') ?>" class="btn btn-sm btn-info mt-2">
                    Manage Roles
                </a>
            </div>
        </div>

        <!-- Master Data Summary -->
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">database</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Master Data</div>
                <div class="stat-value"><?= number_format($stats['total_provinces'] ?? 0) ?></div>
                <p class="stat-description">Provinsi terdaftar</p>
                <small class="text-muted">
                    <?= number_format($stats['total_universities'] ?? 0) ?> PT,
                    <?= number_format($stats['total_study_programs'] ?? 0) ?> Prodi
                </small>
            </div>
        </div>

        <!-- Complaints -->
        <div class="stat-card danger">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">support_agent</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Complaints</div>
                <div class="stat-value"><?= number_format($stats['open_complaints'] ?? 0) ?></div>
                <p class="stat-description">Perlu ditangani</p>
                <small class="text-muted">dari <?= number_format($stats['total_complaints'] ?? 0) ?> total</small>
            </div>
        </div>

        <!-- Forums -->
        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">forum</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Forum Threads</div>
                <div class="stat-value"><?= number_format($stats['total_forums'] ?? 0) ?></div>
                <p class="stat-description">Total diskusi</p>
            </div>
        </div>

        <!-- Surveys -->
        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="material-icons-outlined">poll</i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Surveys</div>
                <div class="stat-value"><?= number_format($stats['active_surveys'] ?? 0) ?></div>
                <p class="stat-description">dari <?= number_format($stats['total_surveys'] ?? 0) ?> total</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row animate-fade-in-up" style="animation-delay: 0.2s;">
        <!-- User Growth Chart -->
        <div class="col-lg-8 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <h5 class="chart-title">User Growth Trend</h5>
                        <p class="chart-subtitle">Pertumbuhan pengguna 12 bulan terakhir</p>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshCharts()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <canvas id="userGrowthChart" height="80"></canvas>
            </div>
        </div>

        <!-- Users by Role Pie Chart -->
        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <h5 class="chart-title">Users by Role</h5>
                        <p class="chart-subtitle">Distribusi pengguna per role</p>
                    </div>
                </div>
                <canvas id="usersByRoleChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="row animate-fade-in-up" style="animation-delay: 0.3s;">
        <!-- Members by Province Chart -->
        <div class="col-lg-8 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <h5 class="chart-title">Members by Province</h5>
                        <p class="chart-subtitle">Top 10 provinsi dengan anggota terbanyak</p>
                    </div>
                </div>
                <canvas id="membersByProvinceChart" height="80"></canvas>
            </div>
        </div>

        <!-- Activity Trend Chart -->
        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <h5 class="chart-title">System Activity</h5>
                        <p class="chart-subtitle">30 hari terakhir</p>
                    </div>
                </div>
                <canvas id="activityTrendChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.4s;">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </h5>
        </div>
        <div class="col-12">
            <div class="quick-actions-grid">
                <?php foreach ($quickActions as $action): ?>
                    <a href="<?= esc($action['url']) ?>" class="quick-action-card">
                        <div class="quick-action-icon text-<?= esc($action['color']) ?>">
                            <i class="<?= esc($action['icon']) ?>"></i>
                        </div>
                        <div class="quick-action-title"><?= esc($action['title']) ?></div>
                        <div class="quick-action-description"><?= esc($action['description']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activities & System Health -->
    <div class="row animate-fade-in-up" style="animation-delay: 0.5s;">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="activity-list">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activities
                    </h5>
                    <a href="<?= base_url('super/audit-logs') ?>" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>

                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-circle text-primary"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-user"><?= esc($activity['user']) ?></div>
                                <p class="activity-description">
                                    <?= esc($activity['description']) ?>
                                    <span class="text-muted">• <?= esc($activity['ip_address']) ?></span>
                                </p>
                            </div>
                            <div class="activity-time">
                                <?= date('d M Y, H:i', strtotime($activity['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Belum ada aktivitas tercatat</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Health -->
        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <h5 class="mb-4">
                    <i class="fas fa-heartbeat me-2"></i>System Health
                </h5>

                <!-- Database Status -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Database</span>
                        <span class="health-badge <?= $systemHealth['database'] == 'healthy' ? 'healthy' : 'error' ?>">
                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                            <?= ucfirst($systemHealth['database']) ?>
                        </span>
                    </div>
                </div>

                <!-- Disk Space -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Disk Space</span>
                        <span class="fw-bold"><?= esc($systemHealth['disk_space']['percentage']) ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-<?= $systemHealth['disk_space']['percentage'] > 80 ? 'danger' : 'success' ?>"
                            style="width: <?= esc($systemHealth['disk_space']['percentage']) ?>%"></div>
                    </div>
                    <small class="text-muted">
                        <?= esc($systemHealth['disk_space']['used']) ?> / <?= esc($systemHealth['disk_space']['total']) ?>
                    </small>
                </div>

                <!-- PHP Version -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">PHP Version</span>
                        <span class="fw-bold"><?= esc($systemHealth['php_version']) ?></span>
                    </div>
                </div>

                <!-- CI Version -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">CodeIgniter</span>
                        <span class="fw-bold"><?= esc($systemHealth['ci_version']) ?></span>
                    </div>
                </div>

                <!-- Environment -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Environment</span>
                        <span class="badge bg-<?= $systemHealth['environment'] == 'production' ? 'success' : 'warning' ?>">
                            <?= strtoupper($systemHealth['environment']) ?>
                        </span>
                    </div>
                </div>

                <!-- Audit Logs Info -->
                <div class="alert alert-info mt-4" role="alert">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        <?= number_format($stats['recent_audit_logs'] ?? 0) ?> aktivitas tercatat dalam 7 hari terakhir
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart Data from Controller
        const chartData = <?= json_encode($chartData) ?>;

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: chartData.user_growth.map(item => item.month),
                datasets: [{
                    label: 'New Users',
                    data: chartData.user_growth.map(item => item.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Users by Role Pie Chart
        const usersByRoleCtx = document.getElementById('usersByRoleChart').getContext('2d');
        new Chart(usersByRoleCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.users_by_role.map(item => item.role),
                datasets: [{
                    data: chartData.users_by_role.map(item => item.count),
                    backgroundColor: [
                        '#667eea',
                        '#38ef7d',
                        '#f5576c',
                        '#00f2fe',
                        '#fee140'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
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

        // Members by Province Chart
        if (chartData.members_by_province.length > 0) {
            const membersByProvinceCtx = document.getElementById('membersByProvinceChart').getContext('2d');
            new Chart(membersByProvinceCtx, {
                type: 'bar',
                data: {
                    labels: chartData.members_by_province.map(item => item.province),
                    datasets: [{
                        label: 'Members',
                        data: chartData.members_by_province.map(item => item.count),
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: '#667eea',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
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
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Activity Trend Chart
        if (chartData.activity_trend.length > 0) {
            const activityTrendCtx = document.getElementById('activityTrendChart').getContext('2d');
            new Chart(activityTrendCtx, {
                type: 'line',
                data: {
                    labels: chartData.activity_trend.map(item => item.date),
                    datasets: [{
                        label: 'Activities',
                        data: chartData.activity_trend.map(item => item.count),
                        borderColor: '#38ef7d',
                        backgroundColor: 'rgba(56, 239, 125, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
    });

    // Refresh Charts Function
    function refreshCharts() {
        Swal.fire({
            title: 'Refreshing Data...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(BASE_URL + '/super/dashboard/refresh', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Dashboard data refreshed successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to refresh data: ' + error.message
                });
            });
    }
</script>
<?= $this->endSection() ?>