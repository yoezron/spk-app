<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Audit Log Detail #<?= $log->id ?>
                </h5>
                <div>
                    <a href="<?= base_url('super/audit-logs') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Basic Information</h6>

                        <table class="table table-bordered">
                            <tr>
                                <th width="40%" class="bg-light">Log ID</th>
                                <td><?= $log->id ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Date/Time</th>
                                <td><?= date('d F Y, H:i:s', strtotime($log->created_at)) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">User</th>
                                <td>
                                    <?php if ($log->username): ?>
                                        <strong><?= esc($log->username) ?></strong>
                                        <br><small class="text-muted"><?= esc($log->email) ?></small>
                                        <br><span class="badge bg-secondary">User ID: <?= $log->user_id ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-dark">System</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Action</th>
                                <td><code class="fs-6"><?= esc($log->action) ?></code></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Module</th>
                                <td>
                                    <?php if ($log->module): ?>
                                        <span class="badge bg-info"><?= esc(ucfirst($log->module)) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Status</th>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        'warning' => 'warning'
                                    ];
                                    $color = $statusColors[$log->status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?> fs-6">
                                        <?= strtoupper($log->status) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Request Information</h6>

                        <table class="table table-bordered">
                            <tr>
                                <th width="40%" class="bg-light">URL</th>
                                <td>
                                    <?php if ($log->url): ?>
                                        <small><code><?= esc($log->url) ?></code></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">HTTP Method</th>
                                <td>
                                    <?php if ($log->method): ?>
                                        <span class="badge bg-primary"><?= esc(strtoupper($log->method)) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">IP Address</th>
                                <td>
                                    <?php if ($log->ip_address): ?>
                                        <code><?= esc($log->ip_address) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">User Agent</th>
                                <td>
                                    <?php if ($log->user_agent): ?>
                                        <small><?= esc($log->user_agent) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tags</th>
                                <td>
                                    <?php if ($log->tags): ?>
                                        <?php foreach (explode(',', $log->tags) as $tag): ?>
                                            <span class="badge bg-light text-dark"><?= esc(trim($tag)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Entity Information -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Entity Information</h6>

                        <table class="table table-bordered">
                            <tr>
                                <th width="20%" class="bg-light">Entity Type</th>
                                <td>
                                    <?php if ($log->entity_type): ?>
                                        <strong><?= esc($log->entity_type) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Entity ID</th>
                                <td>
                                    <?php if ($log->entity_id): ?>
                                        <code><?= $log->entity_id ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Entity Name</th>
                                <td>
                                    <?php if ($log->entity_name): ?>
                                        <?= esc($log->entity_name) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Description</th>
                                <td>
                                    <?php if ($log->action_description): ?>
                                        <?= esc($log->action_description) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Changed Fields -->
                <?php if ($log->changed_fields): ?>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Changed Fields</h6>
                            <div class="alert alert-info">
                                <?php foreach (explode(',', $log->changed_fields) as $field): ?>
                                    <span class="badge bg-primary me-1"><?= esc(trim($field)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Data Changes -->
                <?php if ($log->old_values || $log->new_values): ?>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Old Values</h6>
                            <?php if ($log->old_values): ?>
                                <pre class="bg-light p-3 rounded"><code><?= json_encode($log->old_values, JSON_PRETTY_PRINT) ?></code></pre>
                            <?php else: ?>
                                <p class="text-muted">No old values</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">New Values</h6>
                            <?php if ($log->new_values): ?>
                                <pre class="bg-light p-3 rounded"><code><?= json_encode($log->new_values, JSON_PRETTY_PRINT) ?></code></pre>
                            <?php else: ?>
                                <p class="text-muted">No new values</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Metadata -->
                <?php if ($log->metadata): ?>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Additional Metadata</h6>
                            <pre class="bg-light p-3 rounded"><code><?= json_encode($log->metadata, JSON_PRETTY_PRINT) ?></code></pre>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>