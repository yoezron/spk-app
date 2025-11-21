<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $pageTitle ?></h1>
            <p class="text-muted">Riwayat aktivitas kartu anggota Anda</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/card') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Kartu
            </a>
        </div>
    </div>
</div>

<!-- History Timeline -->
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">history</i>
                    Aktivitas Kartu
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="material-icons-outlined" style="font-size: 64px;">history</i>
                        <p class="mt-3 mb-0">Belum ada riwayat aktivitas</p>
                        <small>Aktivitas terkait kartu Anda akan muncul di sini</small>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($logs as $log): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <?php
                                    $iconMap = [
                                        'card_generated' => ['icon' => 'add_card', 'color' => 'success'],
                                        'card_downloaded' => ['icon' => 'download', 'color' => 'primary'],
                                        'card_verified' => ['icon' => 'verified', 'color' => 'info']
                                    ];
                                    $actionInfo = $iconMap[$log->action] ?? ['icon' => 'circle', 'color' => 'secondary'];
                                    ?>
                                    <i class="material-icons-outlined text-<?= $actionInfo['color'] ?>">
                                        <?= $actionInfo['icon'] ?>
                                    </i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php
                                                $actionLabels = [
                                                    'card_generated' => 'Kartu Dibuat',
                                                    'card_downloaded' => 'Kartu Diunduh',
                                                    'card_verified' => 'Kartu Diverifikasi'
                                                ];
                                                echo $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action));
                                                ?>
                                            </h6>
                                            <p class="text-muted mb-1"><?= esc($log->description ?? '') ?></p>
                                            <small class="text-muted">
                                                <i class="material-icons-outlined" style="font-size: 12px;">access_time</i>
                                                <?= date('d F Y, H:i', strtotime($log->created_at)) ?>
                                            </small>
                                            <?php if (!empty($log->ip_address)): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="material-icons-outlined" style="font-size: 12px;">computer</i>
                                                    IP: <?= esc($log->ip_address) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-<?= $actionInfo['color'] ?>">
                                            <?= ucfirst($log->action) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }

    .timeline-item {
        position: relative;
        padding-left: 60px;
        padding-bottom: 30px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 30px;
        bottom: -10px;
        width: 2px;
        background-color: #e0e0e0;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .timeline-content {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
</style>
<?= $this->endSection() ?>
