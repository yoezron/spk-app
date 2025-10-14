<?php

/**
 * View: Survey Index - Member Area
 * Controller: Member\SurveyController@index
 * Description: Menampilkan daftar survei yang tersedia untuk anggota
 * 
 * Features:
 * - Stats cards (active surveys, completed surveys)
 * - Active surveys section dengan participate button
 * - Upcoming surveys section
 * - Completed surveys history
 * - Survey cards dengan progress & deadline info
 * - Empty states untuk setiap section
 * - Responsive grid layout
 * 
 * @package App\Views\Member\Survey
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header p {
        opacity: 0.9;
        margin-bottom: 0;
        font-size: 15px;
    }

    /* Stats Cards */
    .stats-row {
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-icon.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .stat-info {
        flex: 1;
    }

    .stat-info h3 {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
        line-height: 1;
    }

    .stat-info p {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
        margin-top: 5px;
    }

    /* Section Header */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    .section-title {
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #667eea;
        font-size: 24px;
    }

    .section-badge {
        background: #667eea;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    /* Survey Card */
    .survey-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .survey-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }

    .survey-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .survey-card.completed {
        opacity: 0.85;
    }

    .survey-card.completed::before {
        background: linear-gradient(180deg, #11998e 0%, #38ef7d 100%);
    }

    .survey-card.upcoming {
        opacity: 0.75;
    }

    .survey-card.upcoming::before {
        background: linear-gradient(180deg, #f093fb 0%, #f5576c 100%);
    }

    .survey-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .survey-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        flex: 1;
    }

    .survey-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
        margin-left: 15px;
    }

    .survey-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .survey-badge.completed {
        background: #cce5ff;
        color: #004085;
    }

    .survey-badge.upcoming {
        background: #fff3cd;
        color: #856404;
    }

    .survey-description {
        color: #6c757d;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .survey-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 15px;
        font-size: 13px;
        color: #6c757d;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-item i {
        font-size: 16px;
        color: #667eea;
    }

    .survey-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #f1f3f5;
    }

    .survey-stats {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 13px;
        color: #6c757d;
    }

    .stat {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat i {
        color: #667eea;
    }

    /* Progress Bar */
    .progress-container {
        margin-bottom: 15px;
    }

    .progress {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.3s ease;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    /* Deadline Warning */
    .deadline-warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #856404;
    }

    .deadline-warning i {
        font-size: 18px;
        color: #ffc107;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 64px;
        color: #e3e6f0;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #6c757d;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 0;
        font-size: 14px;
    }

    /* Info Box */
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .info-box h5 {
        color: #1565c0;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-box p {
        color: #1976d2;
        margin-bottom: 0;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .stat-card {
            padding: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 24px;
        }

        .stat-info h3 {
            font-size: 28px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .survey-card {
            padding: 20px;
        }

        .survey-header {
            flex-direction: column;
        }

        .survey-badge {
            margin-left: 0;
            margin-top: 10px;
        }

        .survey-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-clipboard-check"></i> <?= esc($pageTitle) ?></h1>
    <p>Ikuti survei dan berikan masukan untuk kemajuan SPK</p>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Stats Cards -->
<div class="stats-row">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalActive ?? 0) ?></h3>
                    <p>Survei Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalCompleted ?? 0) ?></h3>
                    <p>Survei Selesai</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format(count($upcomingSurveys ?? [])) ?></h3>
                    <p>Akan Datang</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Box -->
<?php if (!empty($activeSurveys)): ?>
    <div class="info-box">
        <h5>
            <i class="bi bi-info-circle"></i>
            Pentingnya Partisipasi Anda
        </h5>
        <p>
            Suara Anda sangat berarti! Dengan mengisi survei, Anda membantu SPK dalam mengambil keputusan yang lebih baik untuk kesejahteraan seluruh anggota.
        </p>
    </div>
<?php endif; ?>

<!-- Active Surveys Section -->
<div class="section-header">
    <h2 class="section-title">
        <i class="bi bi-clipboard-pulse"></i>
        <span>Survei Aktif</span>
    </h2>
    <?php if (!empty($activeSurveys)): ?>
        <span class="section-badge"><?= count($activeSurveys) ?> Tersedia</span>
    <?php endif; ?>
</div>

<?php if (!empty($activeSurveys)): ?>
    <div class="row">
        <?php foreach ($activeSurveys as $survey): ?>
            <div class="col-lg-6">
                <div class="survey-card">
                    <div class="survey-header">
                        <div class="flex-grow-1">
                            <h3 class="survey-title"><?= esc($survey->title) ?></h3>
                        </div>
                        <span class="survey-badge active">
                            <i class="bi bi-circle-fill"></i> Aktif
                        </span>
                    </div>

                    <?php if (!empty($survey->description)): ?>
                        <div class="survey-description">
                            <?= esc($survey->description) ?>
                        </div>
                    <?php endif; ?>

                    <div class="survey-meta">
                        <div class="meta-item">
                            <i class="bi bi-question-circle"></i>
                            <span><?= $survey->questions_count ?? 0 ?> pertanyaan</span>
                        </div>
                        <?php if (!empty($survey->end_date)): ?>
                            <?php
                            $daysLeft = max(0, floor((strtotime($survey->end_date) - time()) / 86400));
                            ?>
                            <div class="meta-item">
                                <i class="bi bi-clock"></i>
                                <span>
                                    <?php if ($daysLeft == 0): ?>
                                        Berakhir hari ini
                                    <?php elseif ($daysLeft == 1): ?>
                                        1 hari lagi
                                    <?php else: ?>
                                        <?= $daysLeft ?> hari lagi
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($survey->responses_count)): ?>
                            <div class="meta-item">
                                <i class="bi bi-people"></i>
                                <span><?= number_format($survey->responses_count) ?> responden</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($survey->end_date) && $daysLeft <= 3): ?>
                        <div class="deadline-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span><strong>Segera berakhir!</strong> Jangan lewatkan kesempatan untuk berpartisipasi.</span>
                        </div>
                    <?php endif; ?>

                    <div class="survey-footer">
                        <div class="survey-stats">
                            <div class="stat">
                                <i class="bi bi-calendar-event"></i>
                                <span>Dibuat: <?= date('d M Y', strtotime($survey->created_at)) ?></span>
                            </div>
                        </div>
                        <a href="<?= base_url('member/survey/participate/' . $survey->id) ?>"
                            class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i> Isi Survei
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="bi bi-clipboard-x"></i>
        <h4>Tidak Ada Survei Aktif</h4>
        <p>Belum ada survei yang tersedia saat ini. Pantau terus untuk survei terbaru!</p>
    </div>
<?php endif; ?>

<!-- Upcoming Surveys Section -->
<?php if (!empty($upcomingSurveys)): ?>
    <div class="section-header mt-5">
        <h2 class="section-title">
            <i class="bi bi-calendar-plus"></i>
            <span>Survei Akan Datang</span>
        </h2>
        <span class="section-badge"><?= count($upcomingSurveys) ?> Survei</span>
    </div>

    <div class="row">
        <?php foreach ($upcomingSurveys as $survey): ?>
            <div class="col-lg-6">
                <div class="survey-card upcoming">
                    <div class="survey-header">
                        <div class="flex-grow-1">
                            <h3 class="survey-title"><?= esc($survey->title) ?></h3>
                        </div>
                        <span class="survey-badge upcoming">
                            <i class="bi bi-hourglass-split"></i> Akan Datang
                        </span>
                    </div>

                    <?php if (!empty($survey->description)): ?>
                        <div class="survey-description">
                            <?= esc($survey->description) ?>
                        </div>
                    <?php endif; ?>

                    <div class="survey-meta">
                        <div class="meta-item">
                            <i class="bi bi-calendar-check"></i>
                            <span>Mulai: <?= date('d M Y', strtotime($survey->start_date)) ?></span>
                        </div>
                        <?php if (!empty($survey->questions_count)): ?>
                            <div class="meta-item">
                                <i class="bi bi-question-circle"></i>
                                <span><?= $survey->questions_count ?> pertanyaan</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="survey-footer">
                        <div class="survey-stats">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Survei akan dibuka pada tanggal yang dijadwalkan
                            </small>
                        </div>
                        <button class="btn btn-secondary" disabled>
                            <i class="bi bi-lock"></i> Belum Tersedia
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Completed Surveys Section -->
<?php if (!empty($completedSurveys)): ?>
    <div class="section-header mt-5">
        <h2 class="section-title">
            <i class="bi bi-check-circle"></i>
            <span>Survei yang Telah Diselesaikan</span>
        </h2>
        <span class="section-badge"><?= count($completedSurveys) ?> Survei</span>
    </div>

    <div class="row">
        <?php foreach ($completedSurveys as $survey): ?>
            <div class="col-lg-6">
                <div class="survey-card completed">
                    <div class="survey-header">
                        <div class="flex-grow-1">
                            <h3 class="survey-title"><?= esc($survey->title) ?></h3>
                        </div>
                        <span class="survey-badge completed">
                            <i class="bi bi-check-circle-fill"></i> Selesai
                        </span>
                    </div>

                    <?php if (!empty($survey->description)): ?>
                        <div class="survey-description">
                            <?= esc($survey->description) ?>
                        </div>
                    <?php endif; ?>

                    <div class="survey-meta">
                        <div class="meta-item">
                            <i class="bi bi-calendar-check"></i>
                            <span>Selesai: <?= date('d M Y', strtotime($survey->completed_at ?? $survey->updated_at)) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-question-circle"></i>
                            <span><?= $survey->questions_count ?? 0 ?> pertanyaan</span>
                        </div>
                    </div>

                    <div class="survey-footer">
                        <div class="survey-stats">
                            <div class="stat">
                                <i class="bi bi-check-all text-success"></i>
                                <span class="text-success">Terima kasih atas partisipasi Anda!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Empty State for All -->
<?php if (empty($activeSurveys) && empty($upcomingSurveys) && empty($completedSurveys)): ?>
    <div class="empty-state mt-4">
        <i class="bi bi-clipboard-data"></i>
        <h4>Belum Ada Survei</h4>
        <p>Belum ada survei yang tersedia atau Anda belum pernah mengisi survei.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Animation on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });

        // Observe all survey cards
        document.querySelectorAll('.survey-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });

        // Observe stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    });
</script>
<?= $this->endSection() ?>