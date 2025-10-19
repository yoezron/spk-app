<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4>
                <i class="fas fa-chart-bar me-2"></i>
                Audit Log Statistics
            </h4>
            <div>
                <select class="form-select" id="daysFilter" onchange="window.location.href='?days='+this.value">
                    <option value="7" <?= $days == 7 ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30" <?= $days == 30 ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="90" <?= $days == 90 ? 'selected' : '' ?>>Last 90 Days</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards Row 1 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Events</h6>
                        <h3 class="mb-0"><?= number_format($stats['total']) ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-list fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Success Events</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_status']['success']) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Failed Events</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_status']['failed']) ?></h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Warning Events</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_status']['warning']) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards Row 2 - Severity -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Critical Events</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_severity']['critical']) ?></h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-skull-crossbones fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">High Priority</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_severity']['high']) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Medium Priority</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_severity']['medium']) ?></h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-info-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Low Priority</h6>
                        <h3 class="mb-0"><?= number_format($stats['by_severity']['low']) ?></h3>
                    </div>
                    <div class="text-secondary">
                        <i class="fas fa-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <!-- Activity Trend Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Activity Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="activityChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables -->
<div class="row mb-4">
    <!-- Top Actions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Actions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($stats['by_action'])): ?>
                    <p class="text-muted text-center">No data available</p>
                <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['by_action'] as $action): ?>
                                <tr>
                                    <td><code><?= esc($action->action) ?></code></td>
                                    <td class="text-end"><strong><?= number_format($action->count) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Module Activity -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Module Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($stats['by_module'])): ?>
                    <p class="text-muted text-center">No data available</p>
                <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['by_module'] as $module): ?>
                                <tr>
                                    <td><span class="badge bg-info"><?= esc(ucfirst($module->module)) ?></span></td>
                                    <td class="text-end"><strong><?= number_format($module->count) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="row">
    <div class="col-12">
        <div class="text-center">
            <a href="<?= base_url('super/audit-logs') ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Audit Logs
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Activity Trend Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($stats['by_day'] as $day): ?> '<?= date('M d', strtotime($day['date'])) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Events',
                data: [
                    <?php foreach ($stats['by_day'] as $day): ?>
                        <?= $day['count'] ?>,
                    <?php endforeach; ?>
                ],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Failed', 'Warning'],
            datasets: [{
                data: [
                    <?= $stats['by_status']['success'] ?>,
                    <?= $stats['by_status']['failed'] ?>,
                    <?= $stats['by_status']['warning'] ?>
                ],
                backgroundColor: [
                    '#28a745',
                    '#dc3545',
                    '#ffc107'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
<?= $this->endSection() ?>