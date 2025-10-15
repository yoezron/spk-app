<?php

/**
 * View: Admin Survey Create
 * Controller: Admin\SurveyController::create()
 * Description: Form untuk membuat survey baru dengan dynamic question builder
 * 
 * Features:
 * - Survey basic information form
 * - Survey settings (anonymous, multiple responses, show results)
 * - Dynamic question builder (add/remove/reorder)
 * - Multiple question types support
 * - Options builder untuk multiple choice/checkbox
 * - Sortable questions dengan drag & drop
 * - Save as draft atau publish
 * - Client-side validation
 * - Preview functionality
 * 
 * @package App\Views\Admin\Survey
 * @author  SPK Development Team
 * @version 1.0.0
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
    }

    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .question-card.dragging {
        opacity: 0.5;
        cursor: grabbing;
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-title-box">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clipboard-plus me-2"></i>
                <?= esc($title) ?>
            </h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/surveys') ?>">Survey</a></li>
                <li class="breadcrumb-item active">Buat Survey</li>
            </ol>
        </div>
        <div>
            <a href="<?= base_url('admin/surveys') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Main Form -->
<form id="surveyForm" method="POST" action="<?= base_url('admin/surveys/store') ?>">
    <?= csrf_field() ?>

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
                            value="<?= old('title') ?>"
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
                            placeholder="Jelaskan tujuan dan konteks survey ini..."><?= old('description') ?></textarea>
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
                                    value="<?= old('start_date', date('Y-m-d')) ?>"
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
                                    value="<?= old('end_date') ?>"
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
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="addQuestionBtn">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Pertanyaan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="questionsContainer">
                        <div class="empty-questions" id="emptyState">
                            <i class="bi bi-clipboard2-x"></i>
                            <h5>Belum Ada Pertanyaan</h5>
                            <p class="text-muted">Klik tombol "Tambah Pertanyaan" untuk memulai</p>
                        </div>
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
                            <option value="draft" <?= old('status', 'draft') === 'draft' ? 'selected' : '' ?>>
                                Draft (Belum Dipublikasi)
                            </option>
                            <option value="published" <?= old('status') === 'published' ? 'selected' : '' ?>>
                                Published (Aktif)
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
                            <input type="checkbox" name="is_anonymous" value="1" <?= old('is_anonymous') ? 'checked' : '' ?>>
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
                            <input type="checkbox" name="multiple_responses" value="1" <?= old('multiple_responses') ? 'checked' : '' ?>>
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
                            <input type="checkbox" name="show_results" value="1" <?= old('show_results', '1') ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-lightbulb me-2"></i>
                        Tips Membuat Survey
                    </h6>
                    <ul class="small mb-0">
                        <li>Gunakan judul yang jelas dan menarik</li>
                        <li>Buat pertanyaan yang mudah dipahami</li>
                        <li>Hindari pertanyaan yang ambigu</li>
                        <li>Urutkan pertanyaan secara logis</li>
                        <li>Batasi jumlah pertanyaan (ideal 5-10)</li>
                        <li>Gunakan tipe pertanyaan yang sesuai</li>
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
                    <i class="bi bi-check-circle me-1"></i> Simpan Survey
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Question Template (Hidden) -->
<template id="questionTemplate">
    <div class="question-card" data-question-index="">
        <div class="question-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-grip-vertical drag-handle"></i>
                <span class="question-number">Pertanyaan #<span class="q-num"></span></span>
            </div>
            <div class="question-actions">
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

            <!-- Options Container (for multiple_choice & checkbox) -->
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
        let questionIndex = 0;

        // Initialize
        initializeDateValidation();
        initializeSortable();

        // Add Question
        $('#addQuestionBtn').on('click', function() {
            addQuestion();
        });

        // Add Question Function
        function addQuestion() {
            questionIndex++;

            // Clone template
            const template = $('#questionTemplate').prop('content').cloneNode(true);
            const $questionCard = $(template).find('.question-card');

            // Set index
            $questionCard.attr('data-question-index', questionIndex);
            $questionCard.find('.q-num').text(questionIndex);

            // Update name attributes with index
            $questionCard.find('[name*="[]"]').each(function() {
                const name = $(this).attr('name').replace('[]', '[' + questionIndex + ']');
                $(this).attr('name', name);
            });

            // Append to container
            $('#questionsContainer').append($questionCard);
            $('#emptyState').hide();

            // Initialize events for this question
            initializeQuestionEvents($questionCard);

            // Update numbering
            updateQuestionNumbers();

            // Scroll to new question
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
                    // Add default options if empty
                    if ($optionsContainer.find('.option-item').length === 0) {
                        addOption($optionsContainer);
                        addOption($optionsContainer);
                    }
                } else {
                    $optionsContainer.hide();
                }
            });

            // Add option button
            $question.find('.add-option-btn').on('click', function() {
                const $optionsContainer = $(this).closest('.options-container');
                addOption($optionsContainer);
            });

            // Delete question
            $question.find('.delete-question').on('click', function() {
                Swal.fire({
                    title: 'Hapus Pertanyaan?',
                    text: 'Pertanyaan ini akan dihapus permanen',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $question.fadeOut(300, function() {
                            $(this).remove();
                            updateQuestionNumbers();

                            if ($('#questionsContainer .question-card').length === 0) {
                                $('#emptyState').show();
                            }
                        });
                    }
                });
            });
        }

        // Add Option
        function addOption($container) {
            const template = $('#optionTemplate').prop('content').cloneNode(true);
            const $option = $(template).find('.option-item');
            const questionIndex = $container.closest('.question-card').data('question-index');
            const optionIndex = $container.find('.option-item').length;

            // Set name with proper indexing
            $option.find('.option-input').attr('name', `questions[${questionIndex}][options][${optionIndex}]`);

            // Append
            $container.find('.options-list').append($option);

            // Remove option event
            $option.find('.option-remove').on('click', function() {
                if ($container.find('.option-item').length > 1) {
                    $(this).closest('.option-item').fadeOut(200, function() {
                        $(this).remove();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Minimal 1 Opsi',
                        text: 'Pertanyaan harus memiliki minimal 1 opsi jawaban'
                    });
                }
            });
        }

        // Initialize Sortable
        function initializeSortable() {
            if (typeof Sortable !== 'undefined') {
                new Sortable(document.getElementById('questionsContainer'), {
                    handle: '.drag-handle',
                    animation: 150,
                    draggable: '.question-card',
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
            $('#questionsContainer .question-card').each(function(index) {
                $(this).find('.q-num').text(index + 1);
            });
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

        // Preview Survey
        $('#previewBtn').on('click', function() {
            const title = $('#title').val() || 'Untitled Survey';
            const description = $('#description').val() || '';
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            let previewHtml = `
            <div class="p-3">
                <h3 class="mb-3">${title}</h3>
                ${description ? `<p class="text-muted mb-4">${description}</p>` : ''}
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

            const $questions = $('#questionsContainer .question-card');

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

                    // Render preview based on type
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

        // Form Validation
        $('#surveyForm').on('submit', function(e) {
            const questionCount = $('#questionsContainer .question-card').length;

            if (questionCount === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Survey Kosong',
                    text: 'Silakan tambahkan minimal 1 pertanyaan'
                });
                return false;
            }

            // Validate all questions have text
            let hasEmptyQuestion = false;
            $('#questionsContainer .question-card').each(function() {
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

            // Show loading
            $('#submitBtn').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Menyimpan...');
        });
    });
</script>
<?= $this->endSection() ?>