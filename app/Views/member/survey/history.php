<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Riwayat Respon Survei</h1>
            <p class="text-muted">Semua survei yang pernah Anda isi</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/surveys') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Survei
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Survei Diisi</h6>
                        <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">assignment_turned_in</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Bulan Ini</h6>
                        <h3 class="mb-0 text-success"><?= number_format($stats['this_month'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
                        <i class="material-icons-outlined">event_available</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Rata-rata Waktu</h6>
                        <h3 class="mb-0 text-info"><?= number_format($stats['avg_time'] ?? 0) ?> min</h3>
                    </div>
                    <div class="avatar bg-info-bright text-info rounded">
                        <i class="material-icons-outlined">timer</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tingkat Partisipasi</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($stats['participation_rate'] ?? 0) ?>%</h3>
                    </div>
                    <div class="avatar bg-warning-bright text-warning rounded">
                        <i class="material-icons-outlined">trending_up</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form action="<?= current_url() ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="survey_type" class="form-label">Tipe Survei</label>
                <select name="survey_type" id="survey_type" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="survey" <?= (isset($_GET['survey_type']) && $_GET['survey_type'] == 'survey') ? 'selected' : '' ?>>Survei</option>
                    <option value="poll" <?= (isset($_GET['survey_type']) && $_GET['survey_type'] == 'poll') ? 'selected' : '' ?>>Polling</option>
                    <option value="feedback" <?= (isset($_GET['survey_type']) && $_GET['survey_type'] == 'feedback') ? 'selected' : '' ?>>Feedback</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun</label>
                <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '' ?>>
                            <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan</label>
                <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    <?php
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    ?>
                    <?php foreach ($months as $num => $name): ?>
                        <option value="<?= $num ?>" <?= (isset($_GET['month']) && $_GET['month'] == $num) ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <?php if (!empty($_GET)): ?>
                    <a href="<?= current_url() ?>" class="btn btn-secondary w-100">
                        <i class="material-icons-outlined">clear</i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Response History -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Riwayat Respon</h5>
            </div>
            <div class="col-auto">
                <span class="text-muted">
                    <?= !empty($responses) ? count($responses) : 0 ?> respon
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($responses)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Survei</th>
                            <th>Tipe</th>
                            <th class="text-center">Pertanyaan</th>
                            <th>Tanggal Diisi</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $response): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($response->survey_title) ?></strong>
                                    <?php if (!empty($response->survey_category)): ?>
                                        <br><span class="badge bg-secondary"><?= ucfirst($response->survey_category) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= ucfirst($response->survey_type) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= number_format($response->question_count ?? 0) ?></span>
                                </td>
                                <td>
                                    <i class="material-icons-outlined" style="font-size: 14px;">event</i>
                                    <?= date('d M Y', strtotime($response->submitted_at)) ?>
                                    <br>
                                    <small class="text-muted"><?= date('H:i', strtotime($response->submitted_at)) ?> WIB</small>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($response->completion_time)): ?>
                                        <i class="material-icons-outlined text-muted" style="font-size: 14px;">timer</i>
                                        <?= number_format($response->completion_time) ?> menit
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('member/surveys/my-response/' . $response->id) ?>"
                                           class="btn btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Lihat Respon">
                                            <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                        </a>
                                        <?php if ($response->show_results): ?>
                                            <a href="<?= base_url('member/surveys/results/' . $response->survey_id) ?>"
                                               class="btn btn-outline-info"
                                               data-bs-toggle="tooltip"
                                               title="Lihat Hasil">
                                                <i class="material-icons-outlined" style="font-size: 16px;">bar_chart</i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('member/surveys/show/' . $response->survey_id) ?>"
                                           class="btn btn-outline-secondary"
                                           data-bs-toggle="tooltip"
                                           title="Info Survei">
                                            <i class="material-icons-outlined" style="font-size: 16px;">info</i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">assignment</i>
                <p class="mt-3 mb-0">Belum ada riwayat respon survei</p>
                <small>Survei yang Anda isi akan muncul di sini</small>
                <br>
                <a href="<?= base_url('member/surveys') ?>" class="btn btn-primary mt-3">
                    <i class="material-icons-outlined">quiz</i>
                    Lihat Survei Tersedia
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($responses) && isset($pager)): ?>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<!-- Activity Timeline (Optional) -->
<?php if (!empty($recent_activity)): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="material-icons-outlined">timeline</i>
                Aktivitas Terbaru
            </h6>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach ($recent_activity as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <p class="mb-1">
                                <strong><?= esc($activity->survey_title) ?></strong>
                            </p>
                            <small class="text-muted">
                                <i class="material-icons-outlined" style="font-size: 12px;">access_time</i>
                                <?= timeAgo($activity->submitted_at) ?? date('d M Y, H:i', strtotime($activity->submitted_at)) ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -22px;
        top: 8px;
        bottom: -12px;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-marker {
        position: absolute;
        left: -26px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .timeline-content {
        padding-left: 10px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
<?= $this->endSection() ?>
