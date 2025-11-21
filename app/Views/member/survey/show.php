<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Detail Survei</h1>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('member/surveys') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali
            </a>
        </div>
    </div>
</div>

<?php if (!empty($survey)): ?>
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Survey Information -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="mb-2"><?= esc($survey->title) ?></h3>
                            <div class="text-muted">
                                <span class="me-3">
                                    <i class="material-icons-outlined" style="font-size: 16px;">category</i>
                                    <?= ucfirst($survey->survey_type ?? 'survey') ?>
                                </span>
                                <?php if (!empty($survey->category)): ?>
                                    <span class="me-3">
                                        <i class="material-icons-outlined" style="font-size: 16px;">label</i>
                                        <?= ucfirst($survey->category) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        $now = time();
                        $start = strtotime($survey->start_date);
                        $end = strtotime($survey->end_date);
                        $isActive = $now >= $start && $now <= $end && $survey->is_active;
                        $hasResponded = !empty($user_response);
                        ?>
                        <?php if ($isActive && !$hasResponded): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php elseif ($hasResponded): ?>
                            <span class="badge bg-info">Sudah Diisi</span>
                        <?php elseif ($now < $start): ?>
                            <span class="badge bg-warning">Belum Dimulai</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Berakhir</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($survey->description)): ?>
                        <div class="alert alert-info">
                            <strong><i class="material-icons-outlined">info</i> Deskripsi:</strong>
                            <p class="mb-0 mt-2"><?= nl2br(esc($survey->description)) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="row text-center mb-3">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4><?= number_format($survey->response_count ?? 0) ?></h4>
                                    <small class="text-muted">Respon</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4><?= number_format($survey->question_count ?? 0) ?></h4>
                                    <small class="text-muted">Pertanyaan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4><?= date('d M Y', strtotime($survey->start_date)) ?></h4>
                                    <small class="text-muted">Mulai</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4><?= date('d M Y', strtotime($survey->end_date)) ?></h4>
                                    <small class="text-muted">Berakhir</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($isActive && !$hasResponded): ?>
                        <div class="d-grid">
                            <a href="<?= base_url('member/surveys/participate/' . $survey->id) ?>"
                               class="btn btn-primary btn-lg">
                                <i class="material-icons-outlined">edit</i>
                                Isi Survei Sekarang
                            </a>
                        </div>
                    <?php elseif ($hasResponded): ?>
                        <div class="alert alert-success">
                            <i class="material-icons-outlined">check_circle</i>
                            Terima kasih! Anda telah mengisi survei ini pada
                            <strong><?= date('d M Y, H:i', strtotime($user_response->submitted_at)) ?></strong>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('member/surveys/my-response/' . $user_response->id) ?>"
                               class="btn btn-outline-primary">
                                <i class="material-icons-outlined">visibility</i>
                                Lihat Respon Saya
                            </a>
                            <?php if ($survey->show_results): ?>
                                <a href="<?= base_url('member/surveys/results/' . $survey->id) ?>"
                                   class="btn btn-outline-info">
                                    <i class="material-icons-outlined">bar_chart</i>
                                    Lihat Hasil Survei
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="material-icons-outlined">info</i>
                            Survei ini <?= $now < $start ? 'belum dimulai' : 'sudah berakhir' ?>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Survey Questions Preview -->
            <?php if (!empty($questions)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="material-icons-outlined">quiz</i>
                            Pertanyaan Survei (Preview)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="mb-4 pb-3 <?= $index < count($questions) - 1 ? 'border-bottom' : '' ?>">
                                <h6>
                                    <?= $index + 1 ?>. <?= esc($question->question) ?>
                                    <?php if ($question->is_required): ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </h6>
                                <p class="text-muted small mb-2">
                                    <i class="material-icons-outlined" style="font-size: 14px;">help_outline</i>
                                    Tipe: <?= ucfirst(str_replace('_', ' ', $question->type)) ?>
                                </p>

                                <?php if (in_array($question->type, ['multiple_choice', 'checkbox'])): ?>
                                    <?php $options = json_decode($question->options, true); ?>
                                    <?php if (!empty($options)): ?>
                                        <ul class="list-unstyled ms-3">
                                            <?php foreach ($options as $option): ?>
                                                <li class="mb-1">
                                                    <span class="text-muted">○</span> <?= esc($option) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                <?php elseif ($question->type === 'rating'): ?>
                                    <div class="ms-3">
                                        <span class="text-muted">☆ ☆ ☆ ☆ ☆ (Rating 1-5)</span>
                                    </div>
                                <?php elseif ($question->type === 'yes_no'): ?>
                                    <div class="ms-3">
                                        <span class="text-muted">○ Ya</span><br>
                                        <span class="text-muted">○ Tidak</span>
                                    </div>
                                <?php else: ?>
                                    <div class="ms-3">
                                        <span class="text-muted">[Jawaban <?= $question->type === 'textarea' ? 'panjang' : 'pendek' ?>]</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Survey Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="material-icons-outlined">info</i>
                        Informasi Survei
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <small class="text-muted d-block">Dibuat Oleh</small>
                        <strong><?= esc($survey->creator_name ?? 'Admin') ?></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Tanggal Dibuat</small>
                        <strong><?= date('d M Y', strtotime($survey->created_at)) ?></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Periode</small>
                        <strong>
                            <?= date('d M Y', strtotime($survey->start_date)) ?>
                            <br>s/d<br>
                            <?= date('d M Y', strtotime($survey->end_date)) ?>
                        </strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted d-block">Target Responden</small>
                        <strong><?= ucfirst($survey->target_audience ?? 'All') ?></strong>
                    </div>
                    <?php if ($survey->is_anonymous): ?>
                        <div class="list-group-item">
                            <span class="badge bg-info">
                                <i class="material-icons-outlined" style="font-size: 14px;">security</i>
                                Respon Anonim
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="material-icons-outlined">settings</i>
                        Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($isActive && !$hasResponded): ?>
                        <a href="<?= base_url('member/surveys/participate/' . $survey->id) ?>"
                           class="btn btn-primary w-100 mb-2">
                            <i class="material-icons-outlined">edit</i>
                            Isi Survei
                        </a>
                    <?php endif; ?>

                    <?php if ($hasResponded): ?>
                        <a href="<?= base_url('member/surveys/my-response/' . $user_response->id) ?>"
                           class="btn btn-outline-primary w-100 mb-2">
                            <i class="material-icons-outlined">visibility</i>
                            Lihat Respon Saya
                        </a>
                    <?php endif; ?>

                    <?php if ($survey->show_results): ?>
                        <a href="<?= base_url('member/surveys/results/' . $survey->id) ?>"
                           class="btn btn-outline-info w-100 mb-2">
                            <i class="material-icons-outlined">bar_chart</i>
                            Lihat Hasil
                        </a>
                    <?php endif; ?>

                    <a href="<?= base_url('member/surveys') ?>"
                       class="btn btn-outline-secondary w-100">
                        <i class="material-icons-outlined">list</i>
                        Survei Lainnya
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="material-icons-outlined text-muted" style="font-size: 64px;">quiz</i>
            <h4 class="mt-3 text-muted">Survei Tidak Ditemukan</h4>
            <p class="text-muted">Survei yang Anda cari tidak ditemukan atau sudah dihapus.</p>
            <a href="<?= base_url('member/surveys') ?>" class="btn btn-primary mt-3">
                Lihat Survei Lainnya
            </a>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
