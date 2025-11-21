<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">Detail Posisi</h1>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('org-structure/chart') ?>" class="btn btn-light">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <?php if (!empty($position)): ?>
        <div class="row">
            <!-- Position Overview -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar bg-primary-bright text-primary rounded-circle mx-auto mb-3"
                             style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="material-icons-outlined" style="font-size: 60px;">badge</i>
                        </div>

                        <h4 class="mb-2"><?= esc($position->name) ?></h4>

                        <?php if (!empty($position->department)): ?>
                            <p class="text-muted mb-3">
                                <i class="material-icons-outlined" style="font-size: 18px;">business</i>
                                <?= esc($position->department) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($position->level)): ?>
                            <span class="badge bg-primary mb-3">Level <?= esc($position->level) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Position Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="material-icons-outlined">analytics</i>
                            Statistik Posisi
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (isset($stats['current_holder'])): ?>
                            <div class="list-group-item">
                                <small class="text-muted d-block">Pemegang Jabatan Saat Ini</small>
                                <?php if ($stats['current_holder']): ?>
                                    <a href="<?= base_url('org-structure/detail/' . $stats['current_holder']->id) ?>">
                                        <strong><?= esc($stats['current_holder']->name) ?></strong>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Kosong</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($stats['direct_reports'])): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Bawahan Langsung</span>
                                <span class="badge bg-primary rounded-pill"><?= number_format($stats['direct_reports']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($stats['total_team'])): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Total Tim</span>
                                <span class="badge bg-info rounded-pill"><?= number_format($stats['total_team']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($stats['past_holders'])): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Pemegang Jabatan Sebelumnya</span>
                                <span class="badge bg-secondary rounded-pill"><?= number_format($stats['past_holders']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Position Details -->
            <div class="col-lg-8">
                <!-- Description -->
                <?php if (!empty($position->description)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="material-icons-outlined">description</i>
                                Deskripsi Posisi
                            </h5>
                        </div>
                        <div class="card-body">
                            <?= nl2br(esc($position->description)) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Responsibilities -->
                <?php if (!empty($position->responsibilities)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="material-icons-outlined">task_alt</i>
                                Tanggung Jawab
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $responsibilities = explode("\n", $position->responsibilities);
                            ?>
                            <ul class="list-unstyled">
                                <?php foreach ($responsibilities as $resp): ?>
                                    <?php if (trim($resp)): ?>
                                        <li class="mb-2">
                                            <i class="material-icons-outlined text-success" style="font-size: 20px;">check_circle</i>
                                            <?= esc(trim($resp)) ?>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Requirements -->
                <?php if (!empty($position->requirements)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="material-icons-outlined">verified</i>
                                Persyaratan
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $requirements = explode("\n", $position->requirements);
                            ?>
                            <ul class="list-unstyled">
                                <?php foreach ($requirements as $req): ?>
                                    <?php if (trim($req)): ?>
                                        <li class="mb-2">
                                            <i class="material-icons-outlined text-primary" style="font-size: 20px;">arrow_right</i>
                                            <?= esc(trim($req)) ?>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reporting Structure -->
                <div class="row mb-3">
                    <!-- Reports To -->
                    <?php if (!empty($position->reports_to)): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="material-icons-outlined">supervisor_account</i>
                                        Melapor Kepada
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <a href="<?= base_url('org-structure/position/' . $position->reports_to) ?>">
                                        <?= esc($position->parent_position_name) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Subordinate Positions -->
                    <?php if (!empty($subordinate_positions)): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="material-icons-outlined">account_tree</i>
                                        Posisi Bawahan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($subordinate_positions as $subPos): ?>
                                            <li class="mb-1">
                                                <a href="<?= base_url('org-structure/position/' . $subPos->id) ?>">
                                                    <?= esc($subPos->name) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Current and Past Holders -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="material-icons-outlined">history</i>
                            Riwayat Pemegang Jabatan
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($holders)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Periode</th>
                                            <th>Durasi</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($holders as $holder): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= base_url('org-structure/detail/' . $holder->id) ?>">
                                                        <strong><?= esc($holder->name) ?></strong>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?= date('M Y', strtotime($holder->start_date)) ?>
                                                    <?php if ($holder->end_date): ?>
                                                        - <?= date('M Y', strtotime($holder->end_date)) ?>
                                                    <?php else: ?>
                                                        - Sekarang
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $start = new DateTime($holder->start_date);
                                                    $end = $holder->end_date ? new DateTime($holder->end_date) : new DateTime();
                                                    $interval = $start->diff($end);
                                                    $years = $interval->y;
                                                    $months = $interval->m;

                                                    if ($years > 0) {
                                                        echo $years . ' tahun ';
                                                    }
                                                    echo $months . ' bulan';
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!$holder->end_date): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Selesai</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <p class="mb-0">Belum ada riwayat pemegang jabatan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Competencies -->
                <?php if (!empty($position->competencies)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="material-icons-outlined">military_tech</i>
                                Kompetensi yang Dibutuhkan
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $competencies = explode("\n", $position->competencies);
                            ?>
                            <div class="row">
                                <?php foreach ($competencies as $comp): ?>
                                    <?php if (trim($comp)): ?>
                                        <div class="col-md-6 mb-2">
                                            <span class="badge bg-info">
                                                <i class="material-icons-outlined" style="font-size: 14px;">star</i>
                                                <?= esc(trim($comp)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="material-icons-outlined text-muted" style="font-size: 64px;">badge</i>
                <h4 class="mt-3 text-muted">Posisi Tidak Ditemukan</h4>
                <p class="text-muted">Posisi yang Anda cari tidak ditemukan dalam struktur organisasi.</p>
                <a href="<?= base_url('org-structure/chart') ?>" class="btn btn-primary mt-3">
                    Kembali ke Struktur Organisasi
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
