<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Hasil Survei</h1>
            <?php if (!empty($survey)): ?>
                <p class="text-muted"><?= esc($survey->title) ?></p>
            <?php endif; ?>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('member/surveys/show/' . ($survey->id ?? '')) ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="material-icons-outlined">print</i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($survey)): ?>
    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Respon</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_responses'] ?? 0) ?></h3>
                        </div>
                        <div class="avatar bg-primary-bright text-primary rounded">
                            <i class="material-icons-outlined">people</i>
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
                            <h6 class="text-muted mb-1">Tingkat Respon</h6>
                            <h3 class="mb-0"><?= number_format($stats['response_rate'] ?? 0, 1) ?>%</h3>
                        </div>
                        <div class="avatar bg-success-bright text-success rounded">
                            <i class="material-icons-outlined">trending_up</i>
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
                            <h3 class="mb-0"><?= number_format($stats['avg_time'] ?? 0) ?> menit</h3>
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
                            <h6 class="text-muted mb-1">Pertanyaan</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_questions'] ?? 0) ?></h3>
                        </div>
                        <div class="avatar bg-warning-bright text-warning rounded">
                            <i class="material-icons-outlined">quiz</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Results -->
    <?php if (!empty($results)): ?>
        <?php foreach ($results as $index => $result): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <strong><?= $index + 1 ?>.</strong> <?= esc($result['question']) ?>
                    </h5>
                    <small class="text-muted">
                        Tipe: <?= ucfirst(str_replace('_', ' ', $result['type'])) ?>
                        | <?= number_format($result['response_count']) ?> respon
                    </small>
                </div>
                <div class="card-body">
                    <?php if (in_array($result['type'], ['multiple_choice', 'checkbox', 'yes_no'])): ?>
                        <!-- Chart for choice questions -->
                        <div class="mb-4">
                            <canvas id="chart_<?= $index ?>" height="80"></canvas>
                        </div>

                        <!-- Results table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pilihan</th>
                                        <th class="text-center">Respon</th>
                                        <th class="text-center">Persentase</th>
                                        <th>Grafik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['answers'] as $answer): ?>
                                        <tr>
                                            <td><strong><?= esc($answer['option']) ?></strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?= number_format($answer['count']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <strong><?= number_format($answer['percentage'], 1) ?>%</strong>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-primary"
                                                         role="progressbar"
                                                         style="width: <?= $answer['percentage'] ?>%"
                                                         aria-valuenow="<?= $answer['percentage'] ?>"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        <?= number_format($answer['percentage'], 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($result['type'] === 'rating'): ?>
                        <!-- Rating visualization -->
                        <div class="row text-center mb-3">
                            <div class="col-md-6">
                                <h2><?= number_format($result['average_rating'], 2) ?></h2>
                                <div class="text-warning mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($result['average_rating'])): ?>
                                            <i class="material-icons-outlined">star</i>
                                        <?php elseif ($i <= ceil($result['average_rating'])): ?>
                                            <i class="material-icons-outlined">star_half</i>
                                        <?php else: ?>
                                            <i class="material-icons-outlined">star_border</i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted">Rata-rata Rating</p>
                            </div>
                            <div class="col-md-6">
                                <h2><?= number_format($result['response_count']) ?></h2>
                                <p class="text-muted">Total Rating</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Rating</th>
                                        <th class="text-center">Jumlah</th>
                                        <th>Distribusi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <?php
                                        $count = $result['rating_distribution'][$i] ?? 0;
                                        $percentage = $result['response_count'] > 0 ? ($count / $result['response_count']) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php for ($j = 1; $j <= $i; $j++): ?>
                                                    <i class="material-icons-outlined text-warning" style="font-size: 16px;">star</i>
                                                <?php endfor; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?= number_format($count) ?></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-warning"
                                                         style="width: <?= $percentage ?>%">
                                                        <?= number_format($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <!-- Text responses -->
                        <div class="alert alert-info">
                            <i class="material-icons-outlined">info</i>
                            <strong><?= number_format($result['response_count']) ?> jawaban teks</strong>
                        </div>

                        <?php if (!empty($result['text_samples'])): ?>
                            <h6>Contoh Jawaban:</h6>
                            <div class="list-group">
                                <?php foreach (array_slice($result['text_samples'], 0, 5) as $sample): ?>
                                    <div class="list-group-item">
                                        <p class="mb-1">"<?= esc($sample['answer']) ?>"</p>
                                        <small class="text-muted">
                                            <?= $survey->is_anonymous ? 'Anonim' : esc($sample['respondent_name']) ?>
                                            - <?= date('d M Y', strtotime($sample['submitted_at'])) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($result['text_samples']) > 5): ?>
                                <p class="text-center mt-3">
                                    <small class="text-muted">
                                        Dan <?= number_format(count($result['text_samples']) - 5) ?> jawaban lainnya...
                                    </small>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="material-icons-outlined text-muted" style="font-size: 64px;">analytics</i>
                <h4 class="mt-3 text-muted">Belum Ada Hasil</h4>
                <p class="text-muted">Belum ada respon untuk survei ini.</p>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="material-icons-outlined text-muted" style="font-size: 64px;">quiz</i>
            <h4 class="mt-3 text-muted">Survei Tidak Ditemukan</h4>
            <p class="text-muted">Survei yang Anda cari tidak ditemukan.</p>
            <a href="<?= base_url('member/surveys') ?>" class="btn btn-primary mt-3">
                Kembali ke Survei
            </a>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    <?php if (!empty($results)): ?>
        <?php foreach ($results as $index => $result): ?>
            <?php if (in_array($result['type'], ['multiple_choice', 'checkbox', 'yes_no'])): ?>
                // Chart for question <?= $index + 1 ?>

                const ctx<?= $index ?> = document.getElementById('chart_<?= $index ?>').getContext('2d');
                new Chart(ctx<?= $index ?>, {
                    type: '<?= count($result['answers']) > 5 ? 'bar' : 'doughnut' ?>',
                    data: {
                        labels: [<?php foreach ($result['answers'] as $answer): ?>'<?= addslashes($answer['option']) ?>',<?php endforeach; ?>],
                        datasets: [{
                            label: 'Respon',
                            data: [<?php foreach ($result['answers'] as $answer): ?><?= $answer['count'] ?>,<?php endforeach; ?>],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                                'rgba(255, 159, 64, 0.8)',
                                'rgba(201, 203, 207, 0.8)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: '<?= count($result['answers']) > 5 ? 'top' : 'right' ?>',
                            }
                        }
                        <?php if (count($result['answers']) > 5): ?>
                        ,scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                        <?php endif; ?>
                    }
                });
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
