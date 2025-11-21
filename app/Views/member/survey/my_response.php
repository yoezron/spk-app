<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Respon Saya</h1>
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

<?php if (!empty($response) && !empty($survey)): ?>
    <!-- Response Summary -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-3"><?= esc($survey->title) ?></h4>

                    <div class="row text-muted">
                        <div class="col-md-6 mb-2">
                            <i class="material-icons-outlined" style="font-size: 16px;">event</i>
                            <strong>Waktu Mengisi:</strong>
                            <?= date('d F Y, H:i', strtotime($response->submitted_at)) ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <i class="material-icons-outlined" style="font-size: 16px;">timer</i>
                            <strong>Durasi:</strong>
                            <?= !empty($response->completion_time) ? number_format($response->completion_time) . ' menit' : 'N/A' ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <i class="material-icons-outlined" style="font-size: 16px;">quiz</i>
                            <strong>Total Pertanyaan:</strong>
                            <?= count($answers) ?> pertanyaan
                        </div>
                        <div class="col-md-6 mb-2">
                            <i class="material-icons-outlined" style="font-size: 16px;">check_circle</i>
                            <strong>Status:</strong>
                            <span class="badge bg-success">Terkirim</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="alert alert-success mb-0">
                        <i class="material-icons-outlined" style="font-size: 48px;">task_alt</i>
                        <h5 class="mt-2">Terima Kasih!</h5>
                        <p class="mb-0 small">Respon Anda telah tersimpan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Responses -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="material-icons-outlined">assignment</i>
                Jawaban Anda
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($answers)): ?>
                <?php foreach ($answers as $index => $answer): ?>
                    <div class="mb-4 pb-4 <?= $index < count($answers) - 1 ? 'border-bottom' : '' ?>">
                        <h6 class="mb-3">
                            <strong><?= $index + 1 ?>.</strong> <?= esc($answer->question) ?>
                            <?php if ($answer->is_required): ?>
                                <span class="badge bg-danger">Wajib</span>
                            <?php endif; ?>
                        </h6>

                        <p class="text-muted small mb-2">
                            <i class="material-icons-outlined" style="font-size: 14px;">help_outline</i>
                            Tipe: <?= ucfirst(str_replace('_', ' ', $answer->type)) ?>
                        </p>

                        <div class="card bg-light">
                            <div class="card-body">
                                <?php if ($answer->type === 'multiple_choice' || $answer->type === 'checkbox'): ?>
                                    <?php
                                    $userAnswers = is_array($answer->answer) ? $answer->answer : json_decode($answer->answer, true);
                                    $options = json_decode($answer->options, true);
                                    ?>

                                    <?php if (is_array($userAnswers)): ?>
                                        <?php foreach ($options as $option): ?>
                                            <div class="form-check mb-2">
                                                <?php $isChecked = in_array($option, $userAnswers); ?>
                                                <i class="material-icons-outlined text-<?= $isChecked ? 'primary' : 'muted' ?>" style="font-size: 20px;">
                                                    <?= $isChecked ? 'check_box' : 'check_box_outline_blank' ?>
                                                </i>
                                                <label class="<?= $isChecked ? 'fw-bold text-primary' : 'text-muted' ?>">
                                                    <?= esc($option) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <strong><?= esc($answer->answer) ?></strong>
                                    <?php endif; ?>

                                <?php elseif ($answer->type === 'rating'): ?>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $answer->answer): ?>
                                                <i class="material-icons-outlined">star</i>
                                            <?php else: ?>
                                                <i class="material-icons-outlined">star_border</i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span class="text-dark ms-2"><strong><?= $answer->answer ?> / 5</strong></span>
                                    </div>

                                <?php elseif ($answer->type === 'yes_no'): ?>
                                    <div class="d-flex align-items-center">
                                        <?php if (strtolower($answer->answer) === 'yes' || strtolower($answer->answer) === 'ya'): ?>
                                            <i class="material-icons-outlined text-success me-2" style="font-size: 28px;">check_circle</i>
                                            <strong class="text-success">Ya</strong>
                                        <?php else: ?>
                                            <i class="material-icons-outlined text-danger me-2" style="font-size: 28px;">cancel</i>
                                            <strong class="text-danger">Tidak</strong>
                                        <?php endif; ?>
                                    </div>

                                <?php else: ?>
                                    <!-- Text or Textarea -->
                                    <div class="text-break">
                                        <?= nl2br(esc($answer->answer)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($answer->notes)): ?>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="material-icons-outlined" style="font-size: 12px;">notes</i>
                                    Catatan: <?= esc($answer->notes) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="material-icons-outlined" style="font-size: 48px;">quiz</i>
                    <p class="mt-3 mb-0">Tidak ada jawaban</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex gap-2">
                <?php if ($survey->show_results): ?>
                    <a href="<?= base_url('member/surveys/results/' . $survey->id) ?>"
                       class="btn btn-primary">
                        <i class="material-icons-outlined">bar_chart</i>
                        Lihat Hasil Survei
                    </a>
                <?php endif; ?>

                <a href="<?= base_url('member/surveys/history') ?>"
                   class="btn btn-outline-secondary">
                    <i class="material-icons-outlined">history</i>
                    Riwayat Respon
                </a>

                <a href="<?= base_url('member/surveys') ?>"
                   class="btn btn-outline-secondary">
                    <i class="material-icons-outlined">list</i>
                    Survei Lainnya
                </a>

                <?php if ($survey->allow_multiple_responses && $survey->is_active): ?>
                    <a href="<?= base_url('member/surveys/participate/' . $survey->id) ?>"
                       class="btn btn-outline-primary">
                        <i class="material-icons-outlined">refresh</i>
                        Isi Ulang Survei
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="material-icons-outlined text-muted" style="font-size: 64px;">assignment</i>
            <h4 class="mt-3 text-muted">Respon Tidak Ditemukan</h4>
            <p class="text-muted">Respon yang Anda cari tidak ditemukan atau sudah dihapus.</p>
            <a href="<?= base_url('member/surveys/history') ?>" class="btn btn-primary mt-3">
                Lihat Riwayat Respon
            </a>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
