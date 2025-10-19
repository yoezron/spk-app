<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>
                    Filter Audit Logs
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('super/audit-logs') ?>" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search description, entity..." value="<?= esc($filters['search'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="<?= base_url('super/audit-logs') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                        <div>
                            <a href="<?= base_url('super/audit-logs/statistics') ?>" class="btn btn-info">
                                <i class="fas fa-chart-bar me-2"></i>Statistics
                            </a>
                            <a href="<?= base_url('super/audit-logs/export') ?>?<?= http_build_query($filters) ?>" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Export
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Audit Logs
                </h5>
                <span class="badge bg-primary"><?= number_format($pagination['total']) ?> records</span>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No audit logs found with current filters.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="12%">Date/Time</th>
                                    <th width="10%">User</th>
                                    <th width="10%">Action</th>
                                    <th width="8%">Module</th>
                                    <th width="10%">Entity</th>
                                    <th width="8%">Severity</th>
                                    <th width="10%">IP Address</th>
                                    <th width="20%">Description</th>
                                    <th width="7%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= $log->id ?></td>
                                        <td>
                                            <small><?= date('d M Y', strtotime($log->created_at)) ?><br><?= date('H:i:s', strtotime($log->created_at)) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($log->username): ?>
                                                <span class="badge bg-secondary"><?= esc($log->username) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-dark">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?= esc($log->action) ?></code>
                                        </td>
                                        <td>
                                            <?php if ($log->module): ?>
                                                <span class="badge bg-info"><?= esc(ucfirst($log->module)) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log->entity_type): ?>
                                                <small>
                                                    <strong><?= esc($log->entity_type) ?></strong><br>
                                                    <?= esc($log->entity_name ?? "ID: {$log->entity_id}") ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $severityColors = [
                                                'low' => 'secondary',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'critical' => 'danger'
                                            ];
                                            $color = $severityColors[$log->severity] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $color ?>">
                                                <?= strtoupper($log->severity) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= esc($log->ip_address ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <small><?= esc(substr($log->action_description ?? '-', 0, 50)) ?><?= strlen($log->action_description ?? '') > 50 ? '...' : '' ?></small>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('super/audit-logs/view/' . $log->id) ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation" class="mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    Showing <?= $pagination['showing_from'] ?> to <?= $pagination['showing_to'] ?> of <?= number_format($pagination['total']) ?> entries
                                </div>
                                <ul class="pagination mb-0">
                                    <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= base_url('super/audit-logs') ?>?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>">Previous</a>
                                    </li>

                                    <?php
                                    $startPage = max(1, $pagination['current_page'] - 2);
                                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= base_url('super/audit-logs') ?>?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= base_url('super/audit-logs') ?>?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>">Next</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Clean Old Logs Card -->
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="card-title mb-0">
                    <i class="fas fa-broom me-2"></i>
                    Clean Old Logs
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Delete old low-severity logs to keep database clean.</p>
                <form action="<?= base_url('super/audit-logs/clean') ?>" method="POST" id="cleanForm">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="days_to_keep" class="form-label">Keep logs from last (days)</label>
                            <input type="number" class="form-control" id="days_to_keep" name="days_to_keep" value="90" min="30" max="365">
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-trash me-2"></i>Clean Old Logs
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Clean form confirmation
        $('#cleanForm').on('submit', function(e) {
            e.preventDefault();

            const days = $('#days_to_keep').val();

            Swal.fire({
                title: 'Clean Old Logs?',
                html: `This will delete low-severity logs older than <strong>${days} days</strong>.<br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Clean Logs',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Auto-submit filter on change (optional)
        $('#user_id, #action, #module, #severity, #entity_type').on('change', function() {
            // Uncomment to auto-submit
            // $('#filterForm').submit();
        });
    });
</script>
<?= $this->endSection() ?> mb-3">
<label for="user_id" class="form-label">User</label>
<select class="form-select" id="user_id" name="user_id">
    <option value="">All Users</option>
    <?php foreach ($users as $user): ?>
        <option value="<?= $user->id ?>" <?= ($filters['user_id'] == $user->id) ? 'selected' : '' ?>>
            <?= esc($user->username) ?>
        </option>
    <?php endforeach; ?>
</select>
</div>

<div class="col-md-3 mb-3">
    <label for="action" class="form-label">Action</label>
    <select class="form-select" id="action" name="action">
        <option value="">All Actions</option>
        <?php foreach ($actions as $action): ?>
            <option value="<?= esc($action) ?>" <?= ($filters['action'] == $action) ? 'selected' : '' ?>>
                <?= esc($action) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="module" class="form-label">Module</label>
    <select class="form-select" id="module" name="module">
        <option value="">All Modules</option>
        <?php foreach ($modules as $module): ?>
            <option value="<?= esc($module) ?>" <?= ($filters['module'] == $module) ? 'selected' : '' ?>>
                <?= esc(ucfirst($module)) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="status" class="form-label">Status</label>
    <select class="form-select" id="status" name="status">
        <option value="">All Status</option>
        <option value="success" <?= ($filters['status'] == 'success') ? 'selected' : '' ?>>Success</option>
        <option value="failed" <?= ($filters['status'] == 'failed') ? 'selected' : '' ?>>Failed</option>
        <option value="warning" <?= ($filters['status'] == 'warning') ? 'selected' : '' ?>>Warning</option>
    </select>
</div>

<div class="col-md-3 mb-3">
    <label for="date_from" class="form-label">Date From</label>
    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
</div>

<div class="col-md-3 mb-3">
    <label for="date_to" class="form-label">Date To</label>
    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
</div>

<div class="col-md-3