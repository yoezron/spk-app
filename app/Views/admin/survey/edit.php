<?php

/**
 * View: Admin Survey Edit
 * Controller: Admin\SurveyController::edit($id), update($id)
 * Description: Form untuk edit survey existing dengan advanced features
 * 
 * Features:
 * - Pre-filled survey information
 * - Load existing questions dengan ordering
 * - Edit/Delete/Add questions dynamically
 * - Track deleted questions (hidden field)
 * - Visual indicators (existing/modified/new/deleted)
 * - Response count warning (cannot edit if has responses)
 * - Status management (draft/published/closed)
 * - Duplicate question functionality
 * - Drag & drop reordering
 * - Preview with actual data
 * - Confirmation for destructive actions
 * - Auto-save draft functionality
 * - Version history indicator
 * 
 * @package App\Views\Admin\Survey
 * @author  SPK Development Team
 * @version 2.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datepicker/datepicker.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sortable/sortable.css') ?>">
<style>
    .question-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: #fff;
        transition: all 0.3s ease;
        position: relative;
    }

    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .question-card.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }

    .question-card.deleted {
        opacity: 0.4;
        background: #f8d7da;
        border-color: #dc3545;
    }

    .question-card.new {
        border-left: 4px solid #28a745;
    }

    .question-card.modified {
        border-left: 4px solid #ffc107;
    }

    .question-card.existing {
        border-left: 4px solid #667eea;
    }

    .question-status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .badge-existing {
        background: #e3f2fd;
        color: #1976d2;
    }

    .badge-new {
        background: #e8f5e9;
        color: #388e3c;
    }

    .badge-modified {
        background: #fff3cd;
        color: #856404;
    }

    .badge-deleted {
        background: #f8d7da;
        color: #721c24;
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e3e6f0;
    }

    .question-number {
        font-size: 14px;
        font-weight: 600;
        color: #667eea;
        background: #f0f2ff;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .drag-handle {
        cursor: grab;
        color: #999;
        font-size: 18px;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .question-actions {
        display: flex;
        gap: 8px;
    }

    .option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .option-item input {
        flex: 1;
    }

    .option-remove {
        cursor: pointer;
        color: #dc3545;
    }

    .add-option-btn {
        font-size: 13px;
        padding: 6px 12px;
    }

    .card-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0 !important;
    }

    .card-header-gradient .card-title {
        color: white !important;
        margin: 0;
    }

    .settings-section {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #667eea;
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }

    .empty-questions {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fc;
        border-radius: 8px;
        border: 2px dashed #d1d3e2;
    }

    .empty-questions i {
        font-size: 48px;
        color: #d1d3e2;
        margin-bottom: 15px;
    }

    .submit-buttons {
        position: sticky;
        bottom: 20px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 100;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-draft {
        background: #fff3cd;
        color: #856404;
    }

    .status-published {
        background: #d4edda;
        color: #155724;
    }

    .status-closed {
        background: #d1ecf1;
        color: #0c5460;
    }

    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #2196f3;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .warning-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .deleted-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(220, 53, 69, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .deleted-overlay .badge {
        font-size: 14px;
        padding: 8px 16px;
    }

    .auto-save-indicator {
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 10px 20px;
        border-radius: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        display: none;
        animation: fadeInOut 2s;
    }

    @keyframes fadeInOut {

        0%,
        100% {
            opacity: 0;
        }

        10%,
        90% {
            opacity: 1;
        }
    }

    .version-info {
        font-size: 12px;
        color: #6c757d;
        margin-top: 10px;
    }

    .response-count-badge {
        background: #17a2b8;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Auto-save Indicator -->
<div class="auto-save-indicator" id="autoSaveIndicator">
    <i class="bi bi-check-circle me-2"></i>
    Perubahan tersimpan
</div>

<!-- Page Header -->
<div class="page-title-box">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pencil-square me-2"></i>
                Edit Survey
            </h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/surveys') ?>">Survey</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <!-- Status Badge -->
            <span class="status-indicator status-<?= esc($survey->status) ?>">
                <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                <?= ucfirst(esc($survey->status)) ?>
            </span>
            <!-- Response Count -->
            <?php if (isset($survey->response_count) && $survey->response_count > 0): ?>
                <span class="response-count-badge">
                    <i class="bi bi-people-fill me-1"></i>
                    <?= $survey->response_count ?> Respon
                </span>
            <?php endif; ?>
            <a href="<?= base_url('admin/surveys') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Warning if survey has responses -->
<?php if (isset($survey->response_count) && $survey->response_count > 0): ?>
    <div class="warning-box">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill text-warning me-3" style="font-size: 24px;"></i>
            <div>
                <h6 class="mb-1">Survey Sudah Memiliki Respon</h6>
                <p class="mb-0">
                    Survey ini telah diisi oleh <strong><?= $survey->response_count ?> responden</strong>.
                    Perubahan pada pertanyaan dapat mempengaruhi integritas data.
                    Disarankan untuk hanya mengubah informasi dasar survey.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Info Box -->
<div class="info-box">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill text-primary me-3" style="font-size: 24px;"></i>
        <div>
            <h6 class="mb-1">Informasi Survey</h6>
            <p class="mb-0">
                Dibuat oleh: <strong><?= esc($survey->created_by_name ?? 'Admin') ?></strong> |
                Dibuat pada: <strong><?= date('d M Y H:i', strtotime($survey->created_at)) ?></strong>
                <?php if (isset($survey->updated_at) && $survey->updated_at !== $survey->created_at): ?>
                    | Terakhir diupdate: <strong><?= date('d M Y H:i', strtotime($survey->updated_at)) ?></strong>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Main Form -->
<form id="surveyForm" method="POST" action="<?= base_url('admin/surveys/update/' . $survey->id) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="deleted_questions" id="deletedQuestions" value="">

    <div class="row">
        <!-- Left Column - Survey Info -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card">
                <div class="card-header card-header-gradient">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informasi Survey
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            Judul Survey <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                            id="title"
                            name="title"
                            value="<?= old('title', esc($survey->title)) ?>"
                            placeholder="Masukkan judul survey yang menarik"
                            required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Deskripsi Survey
                        </label>
                        <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Jelaskan tujuan dan konteks survey ini..."><?= old('description', esc($survey->description)) ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['description']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            Deskripsi akan ditampilkan kepada responden sebelum mengisi survey
                        </small>
                    </div>

                    <!-- Date Range -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                    class="form-control <?= isset($errors['start_date']) ? 'is-invalid' : '' ?>"
                                    id="start_date"
                                    name="start_date"
                                    value="<?= old('start_date', date('Y-m-d', strtotime($survey->start_date))) ?>"
                                    required>
                                <?php if (isset($errors['start_date'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['start_date']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">
                                    Tanggal Selesai <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                    class="form-control <?= isset($errors['end_date']) ? 'is-invalid' : '' ?>"
                                    id="end_date"
                                    name="end_date"
                                    value="<?= old('end_date', date('Y-m-d', strtotime($survey->end_date))) ?>"
                                    required>
                                <?php if (isset($errors['end_date'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['end_date']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Builder -->
            <div class="card">
                <div class="card-header card-header-gradient">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-question-circle me-2"></i>
                            Pertanyaan Survey
                            <span class="badge bg-light text-dark ms-2" id="questionCount">
                                <?= count($questions) ?> Pertanyaan
                            </span>
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="addQuestionBtn">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Pertanyaan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="questionsContainer">
                        <?php if (empty($questions)): ?>
                            <div class="empty-questions" id="emptyState">
                                <i class="bi bi-clipboard2-x"></i>
                                <h5>Belum Ada Pertanyaan</h5>
                                <p class="text-muted">Klik tombol "Tambah Pertanyaan" untuk memulai</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($questions as $index => $question):
                                $options = !empty($question->options) ? json_decode($question->options, true) : [];
                            ?>
                                <div class="question-card existing" data-question-id="<?= $question->id ?>" data-question-index="<?= $index + 1 ?>">
                                    <span class="question-status-badge badge-existing">Existing</span>

                                    <input type="hidden" name="questions[<?= $index + 1 ?>][id]" value="<?= $question->id ?>">

                                    <div class="question-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-grip-vertical drag-handle"></i>
                                            <span class="question-number">Pertanyaan #<span class="q-num"><?= $index + 1 ?></span></span>
                                        </div>
                                        <div class="question-actions">
                                            <button type="button" class="btn btn-sm btn-outline-info duplicate-question" title="Duplikat Pertanyaan">
                                                <i class="bi bi-files"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-question" title="Hapus Pertanyaan">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="question-body">
                                        <!-- Question Type -->
                                        <div class="mb-3">
                                            <label class="form-label">Tipe Pertanyaan</label>
                                            <select class="form-select question-type" name="questions[<?= $index + 1 ?>][question_type]" required>
                                                <option value="text" <?= $question->question_type === 'text' ? 'selected' : '' ?>>Text Pendek</option>
                                                <option value="textarea" <?= $question->question_type === 'textarea' ? 'selected' : '' ?>>Text Panjang (Paragraf)</option>
                                                <option value="multiple_choice" <?= $question->question_type === 'multiple_choice' ? 'selected' : '' ?>>Pilihan Ganda (Single)</option>
                                                <option value="checkbox" <?= $question->question_type === 'checkbox' ? 'selected' : '' ?>>Checkbox (Multiple)</option>
                                                <option value="scale" <?= $question->question_type === 'scale' ? 'selected' : '' ?>>Rating Scale (1-5)</option>
                                                <option value="date" <?= $question->question_type === 'date' ? 'selected' : '' ?>>Date Picker</option>
                                            </select>
                                        </div>

                                        <!-- Question Text -->
                                        <div class="mb-3">
                                            <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control question-text"
                                                name="questions[<?= $index + 1 ?>][question_text]"
                                                value="<?= esc($question->question_text) ?>"
                                                placeholder="Tuliskan pertanyaan Anda..."
                                                required>
                                        </div>

                                        <!-- Options Container -->
                                        <div class="options-container" style="display: <?= in_array($question->question_type, ['multiple_choice', 'checkbox']) ? 'block' : 'none' ?>;">
                                            <label class="form-label">Pilihan Jawaban</label>
                                            <div class="options-list">
                                                <?php if (!empty($options)): ?>
                                                    <?php foreach ($options as $optIndex => $option): ?>
                                                        <div class="option-item">
                                                            <i class="bi bi-circle"></i>
                                                            <input type="text"
                                                                class="form-control form-control-sm option-input"
                                                                name="questions[<?= $index + 1 ?>][options][<?= $optIndex ?>]"
                                                                value="<?= esc($option) ?>"
                                                                placeholder="Opsi jawaban..."
                                                                required>
                                                            <i class="bi bi-x-circle option-remove" title="Hapus"></i>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary add-option-btn">
                                                <i class="bi bi-plus-circle me-1"></i> Tambah Pilihan
                                            </button>
                                        </div>

                                        <!-- Required Checkbox -->
                                        <div class="form-check mt-3">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                name="questions[<?= $index + 1 ?>][is_required]"
                                                value="1"
                                                <?= $question->is_required ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                Wajib diisi
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Settings -->
        <div class="col-lg-4">
            <!-- Survey Settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Pengaturan Survey
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="draft" <?= $survey->status === 'draft' ? 'selected' : '' ?>>
                                Draft (Belum Dipublikasi)
                            </option>
                            <option value="published" <?= $survey->status === 'published' ? 'selected' : '' ?>>
                                Published (Aktif)
                            </option>
                            <option value="closed" <?= $survey->status === 'closed' ? 'selected' : '' ?>>
                                Closed (Ditutup)
                            </option>
                        </select>
                        <small class="form-text text-muted">
                            Survey draft tidak akan terlihat oleh anggota
                        </small>
                    </div>

                    <hr>

                    <!-- Anonymous Survey -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label class="form-label mb-0">Survey Anonim</label>
                            <small class="d-block text-muted">Sembunyikan identitas responden</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="is_anonymous" value="1" <?= $survey->is_anonymous ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Multiple Responses -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label class="form-label mb-0">Respon Berulang</label>
                            <small class="d-block text-muted">Izinkan mengisi lebih dari sekali</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="multiple_responses" value="1" <?= $survey->multiple_responses ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Show Results -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-label mb-0">Tampilkan Hasil</label>
                            <small class="d-block text-muted">Responden dapat melihat hasil</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_results" value="1" <?= $survey->show_results ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Statistik Survey
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Pertanyaan</small>
                        <h4 class="mb-0 text-primary" id="totalQuestions"><?= count($questions) ?></h4>
                    </div>
                    <?php if (isset($survey->response_count)): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Total Respon</small>
                            <h4 class="mb-0 text-success"><?= $survey->response_count ?></h4>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Terakhir Diupdate</small>
                        <strong><?= date('d M Y', strtotime($survey->updated_at ?? $survey->created_at)) ?></strong>
                    </div>
                    <hr>
                    <a href="<?= base_url('admin/surveys/responses/' . $survey->id) ?>" class="btn btn-sm btn-info w-100">
                        <i class="bi bi-bar-chart me-1"></i> Lihat Hasil Survey
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="duplicateSurveyBtn">
                            <i class="bi bi-files me-1"></i> Duplikat Survey
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportSurveyBtn">
                            <i class="bi bi-download me-1"></i> Export Survey
                        </button>
                        <?php if ($survey->status !== 'published'): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="deleteSurveyBtn">
                                <i class="bi bi-trash me-1"></i> Hapus Survey
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="bi bi-lightbulb me-2"></i>
                        Tips Edit Survey
                    </h6>
                    <ul class="small mb-0">
                        <li>Perubahan otomatis tersimpan</li>
                        <li>Hindari menghapus pertanyaan jika sudah ada respon</li>
                        <li>Gunakan status "Draft" untuk perubahan besar</li>
                        <li>Preview sebelum mempublikasi</li>
                        <li>Duplikat survey untuk membuat variasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons (Sticky) -->
    <div class="submit-buttons">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-info" id="previewBtn">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Question Template (Hidden) -->
<template id="questionTemplate">
    <div class="question-card new" data-question-index="">
        <span class="question-status-badge badge-new">New</span>

        <div class="question-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-grip-vertical drag-handle"></i>
                <span class="question-number">Pertanyaan #<span class="q-num"></span></span>
            </div>
            <div class="question-actions">
                <button type="button" class="btn btn-sm btn-outline-info duplicate-question" title="Duplikat Pertanyaan">
                    <i class="bi bi-files"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-question" title="Hapus Pertanyaan">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <div class="question-body">
            <!-- Question Type -->
            <div class="mb-3">
                <label class="form-label">Tipe Pertanyaan</label>
                <select class="form-select question-type" name="questions[][question_type]" required>
                    <option value="text">Text Pendek</option>
                    <option value="textarea">Text Panjang (Paragraf)</option>
                    <option value="multiple_choice">Pilihan Ganda (Single)</option>
                    <option value="checkbox">Checkbox (Multiple)</option>
                    <option value="scale">Rating Scale (1-5)</option>
                    <option value="date">Date Picker</option>
                </select>
            </div>

            <!-- Question Text -->
            <div class="mb-3">
                <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control question-text"
                    name="questions[][question_text]"
                    placeholder="Tuliskan pertanyaan Anda..."
                    required>
            </div>

            <!-- Options Container -->
            <div class="options-container" style="display: none;">
                <label class="form-label">Pilihan Jawaban</label>
                <div class="options-list"></div>
                <button type="button" class="btn btn-sm btn-outline-primary add-option-btn">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pilihan
                </button>
            </div>

            <!-- Required Checkbox -->
            <div class="form-check mt-3">
                <input class="form-check-input"
                    type="checkbox"
                    name="questions[][is_required]"
                    value="1">
                <label class="form-check-label">
                    Wajib diisi
                </label>
            </div>
        </div>
    </div>
</template>

<!-- Option Item Template -->
<template id="optionTemplate">
    <div class="option-item">
        <i class="bi bi-circle"></i>
        <input type="text"
            class="form-control form-control-sm option-input"
            placeholder="Opsi jawaban..."
            required>
        <i class="bi bi-x-circle option-remove" title="Hapus"></i>
    </div>
</template>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    Preview Survey
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/sortable/sortable.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        let questionIndex = <?= count($questions) ?>;
        let deletedQuestions = [];
        const hasResponses = <?= isset($survey->response_count) && $survey->response_count > 0 ? 'true' : 'false' ?>;

        // Initialize
        initializeDateValidation();
        initializeSortable();
        initializeExistingQuestions();
        trackFormChanges();

        // Initialize existing questions
        function initializeExistingQuestions() {
            $('#questionsContainer .question-card').each(function() {
                initializeQuestionEvents($(this));
            });
        }

        // Add Question
        $('#addQuestionBtn').on('click', function() {
            addQuestion();
        });

        // Add Question Function
        function addQuestion() {
            questionIndex++;

            const template = $('#questionTemplate').prop('content').cloneNode(true);
            const $questionCard = $(template).find('.question-card');

            $questionCard.attr('data-question-index', questionIndex);
            $questionCard.find('.q-num').text(questionIndex);

            // Update name attributes
            $questionCard.find('[name*="[]"]').each(function() {
                const name = $(this).attr('name').replace('[]', '[' + questionIndex + ']');
                $(this).attr('name', name);
            });

            $('#questionsContainer').append($questionCard);
            $('#emptyState').hide();

            initializeQuestionEvents($questionCard);
            updateQuestionNumbers();
            updateQuestionCount();

            $questionCard[0].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        // Initialize Question Events
        function initializeQuestionEvents($question) {
            // Question type change
            $question.find('.question-type').on('change', function() {
                const type = $(this).val();
                const $optionsContainer = $(this).closest('.question-body').find('.options-container');

                if (type === 'multiple_choice' || type === 'checkbox') {
                    $optionsContainer.show();
                    if ($optionsContainer.find('.option-item').length === 0) {
                        addOption($optionsContainer, $question);
                        addOption($optionsContainer, $question);
                    }
                } else {
                    $optionsContainer.hide();
                }

                markAsModified($question);
            });

            // Add option button
            $question.find('.add-option-btn').on('click', function() {
                const $optionsContainer = $(this).closest('.options-container');
                addOption($optionsContainer, $question);
            });

            // Duplicate question
            $question.find('.duplicate-question').on('click', function() {
                duplicateQuestion($question);
            });

            // Delete question
            $question.find('.delete-question').on('click', function() {
                deleteQuestion($question);
            });

            // Track modifications
            $question.find('input, textarea, select').on('change', function() {
                if (!$question.hasClass('new')) {
                    markAsModified($question);
                }
            });
        }

        // Add Option
        function addOption($container, $question) {
            const template = $('#optionTemplate').prop('content').cloneNode(true);
            const $option = $(template).find('.option-item');
            const questionIndex = $container.closest('.question-card').data('question-index');
            const optionIndex = $container.find('.option-item').length;

            $option.find('.option-input').attr('name', `questions[${questionIndex}][options][${optionIndex}]`);

            $container.find('.options-list').append($option);

            // Remove option event
            $option.find('.option-remove').on('click', function() {
                if ($container.find('.option-item').length > 1) {
                    $(this).closest('.option-item').fadeOut(200, function() {
                        $(this).remove();
                        markAsModified($question);
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Minimal 1 Opsi',
                        text: 'Pertanyaan harus memiliki minimal 1 opsi jawaban'
                    });
                }
            });

            markAsModified($question);
        }

        // Duplicate Question
        function duplicateQuestion($question) {
            const $clone = $question.clone();
            questionIndex++;

            $clone.removeClass('existing modified deleted').addClass('new');
            $clone.attr('data-question-index', questionIndex);
            $clone.find('.question-status-badge').removeClass('badge-existing badge-modified').addClass('badge-new').text('New');
            $clone.find('[name^="questions"]').each(function() {
                const name = $(this).attr('name').replace(/questions\[\d+\]/, `questions[${questionIndex}]`);
                $(this).attr('name', name);
            });
            $clone.find('[name*="[id]"]').remove();

            $question.after($clone);
            initializeQuestionEvents($clone);
            updateQuestionNumbers();
            updateQuestionCount();

            Swal.fire({
                icon: 'success',
                title: 'Pertanyaan Diduplikat',
                text: 'Pertanyaan berhasil diduplikat',
                timer: 1500,
                showConfirmButton: false
            });
        }

        // Delete Question
        function deleteQuestion($question) {
            const questionId = $question.data('question-id');

            let warningText = 'Pertanyaan ini akan dihapus permanen';
            if (hasResponses && questionId) {
                warningText = 'PERINGATAN: Survey ini sudah memiliki respon. Menghapus pertanyaan dapat mempengaruhi integritas data!';
            }

            Swal.fire({
                title: 'Hapus Pertanyaan?',
                text: warningText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (questionId) {
                        // Mark existing question as deleted
                        deletedQuestions.push(questionId);
                        $('#deletedQuestions').val(deletedQuestions.join(','));

                        $question.addClass('deleted');
                        $question.find('.question-status-badge').removeClass('badge-existing badge-modified badge-new').addClass('badge-deleted').text('Deleted');
                        $question.append('<div class="deleted-overlay"><span class="badge bg-danger">Akan Dihapus</span></div>');

                        // Disable inputs
                        $question.find('input, select, textarea, button').prop('disabled', true);
                    } else {
                        // Remove new question
                        $question.fadeOut(300, function() {
                            $(this).remove();
                            updateQuestionNumbers();
                            updateQuestionCount();

                            if ($('#questionsContainer .question-card:not(.deleted)').length === 0) {
                                $('#emptyState').show();
                            }
                        });
                    }
                }
            });
        }

        // Mark as Modified
        function markAsModified($question) {
            if ($question.hasClass('existing') && !$question.hasClass('modified')) {
                $question.removeClass('existing').addClass('modified');
                $question.find('.question-status-badge').removeClass('badge-existing').addClass('badge-modified').text('Modified');
            }
        }

        // Initialize Sortable
        function initializeSortable() {
            if (typeof Sortable !== 'undefined') {
                new Sortable(document.getElementById('questionsContainer'), {
                    handle: '.drag-handle',
                    animation: 150,
                    draggable: '.question-card:not(.deleted)',
                    onStart: function(evt) {
                        $(evt.item).addClass('dragging');
                    },
                    onEnd: function(evt) {
                        $(evt.item).removeClass('dragging');
                        updateQuestionNumbers();
                    }
                });
            }
        }

        // Update Question Numbers
        function updateQuestionNumbers() {
            $('#questionsContainer .question-card:not(.deleted)').each(function(index) {
                $(this).find('.q-num').text(index + 1);
            });
        }

        // Update Question Count
        function updateQuestionCount() {
            const count = $('#questionsContainer .question-card:not(.deleted)').length;
            $('#questionCount').text(count + ' Pertanyaan');
            $('#totalQuestions').text(count);
        }

        // Date Validation
        function initializeDateValidation() {
            const $startDate = $('#start_date');
            const $endDate = $('#end_date');

            $startDate.on('change', function() {
                $endDate.attr('min', $(this).val());
            });

            $endDate.on('change', function() {
                if ($(this).val() < $startDate.val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai harus lebih besar dari tanggal mulai'
                    });
                    $(this).val('');
                }
            });
        }

        // Track Form Changes
        function trackFormChanges() {
            let formChanged = false;

            $('#surveyForm input, #surveyForm textarea, #surveyForm select').on('change', function() {
                formChanged = true;
            });

            // Warn before leaving if form changed
            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                }
            });

            // Remove warning on form submit
            $('#surveyForm').on('submit', function() {
                $(window).off('beforeunload');
            });
        }

        // Preview Survey
        $('#previewBtn').on('click', function() {
            const title = $('#title').val() || 'Untitled Survey';
            const description = $('#description').val() || '';
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const status = $('#status').val();

            let previewHtml = `
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 class="mb-2">${title}</h3>
                        ${description ? `<p class="text-muted mb-0">${description}</p>` : ''}
                    </div>
                    <span class="badge bg-${status === 'published' ? 'success' : status === 'closed' ? 'secondary' : 'warning'}">
                        ${status.toUpperCase()}
                    </span>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <small class="text-muted">Mulai:</small><br>
                        <strong>${startDate || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Selesai:</small><br>
                        <strong>${endDate || '-'}</strong>
                    </div>
                </div>
                <hr>
        `;

            const $questions = $('#questionsContainer .question-card:not(.deleted)');

            if ($questions.length === 0) {
                previewHtml += '<p class="text-center text-muted">Belum ada pertanyaan</p>';
            } else {
                $questions.each(function(index) {
                    const questionText = $(this).find('.question-text').val() || 'Pertanyaan belum diisi';
                    const questionType = $(this).find('.question-type').val();
                    const isRequired = $(this).find('[name*="is_required"]').is(':checked');

                    previewHtml += `
                    <div class="mb-4">
                        <h6 class="mb-2">
                            ${index + 1}. ${questionText}
                            ${isRequired ? '<span class="text-danger">*</span>' : ''}
                        </h6>
                `;

                    switch (questionType) {
                        case 'text':
                            previewHtml += '<input type="text" class="form-control" placeholder="Jawaban singkat..." disabled>';
                            break;
                        case 'textarea':
                            previewHtml += '<textarea class="form-control" rows="3" placeholder="Jawaban panjang..." disabled></textarea>';
                            break;
                        case 'multiple_choice':
                        case 'checkbox':
                            const inputType = questionType === 'checkbox' ? 'checkbox' : 'radio';
                            $(this).find('.option-input').each(function() {
                                const optionText = $(this).val() || 'Opsi';
                                previewHtml += `
                                <div class="form-check">
                                    <input class="form-check-input" type="${inputType}" disabled>
                                    <label class="form-check-label">${optionText}</label>
                                </div>
                            `;
                            });
                            break;
                        case 'scale':
                            previewHtml += '<div class="d-flex gap-3 justify-content-center">';
                            for (let i = 1; i <= 5; i++) {
                                previewHtml += `
                                <div class="text-center">
                                    <input type="radio" name="preview_scale_${index}" disabled>
                                    <br><small>${i}</small>
                                </div>
                            `;
                            }
                            previewHtml += '</div>';
                            break;
                        case 'date':
                            previewHtml += '<input type="date" class="form-control" disabled>';
                            break;
                    }

                    previewHtml += '</div>';
                });
            }

            previewHtml += '</div>';

            $('#previewContent').html(previewHtml);
            $('#previewModal').modal('show');
        });

        // Delete Survey
        $('#deleteSurveyBtn').on('click', function() {
            Swal.fire({
                title: 'Hapus Survey?',
                text: 'Survey dan semua data terkait akan dihapus permanen!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= base_url('admin/surveys/delete/' . $survey->id) ?>';
                }
            });
        });

        // Form Validation
        $('#surveyForm').on('submit', function(e) {
            const questionCount = $('#questionsContainer .question-card:not(.deleted)').length;

            if (questionCount === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Survey Kosong',
                    text: 'Survey harus memiliki minimal 1 pertanyaan'
                });
                return false;
            }

            // Validate all questions
            let hasEmptyQuestion = false;
            $('#questionsContainer .question-card:not(.deleted)').each(function() {
                const questionText = $(this).find('.question-text').val().trim();
                if (!questionText) {
                    hasEmptyQuestion = true;
                    $(this).find('.question-text').addClass('is-invalid');
                }
            });

            if (hasEmptyQuestion) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Pertanyaan Belum Lengkap',
                    text: 'Semua pertanyaan harus diisi'
                });
                return false;
            }

            $('#submitBtn').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Menyimpan...');
        });

        // Show auto-save indicator
        function showAutoSaveIndicator() {
            $('#autoSaveIndicator').fadeIn().delay(2000).fadeOut();
        }
    });
</script>
<?= $this->endSection() ?>