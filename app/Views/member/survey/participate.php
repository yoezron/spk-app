<?php

/**
 * View: Survey Participate - Member Area
 * Controller: Member\SurveyController@show
 * Description: Form untuk mengisi survei dengan dynamic question types
 * 
 * Features:
 * - Survey header dengan deadline info
 * - Progress indicator
 * - Dynamic question types (text, textarea, radio, checkbox, rating, scale, date, number)
 * - Validation (required fields, min/max values)
 * - Auto-save draft (optional)
 * - Submit confirmation
 * - Responsive form layout
 * 
 * @package App\Views\Member\Survey
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/member') ?>

<?= $this->section('styles') ?>
<style>
    /* Survey Header */
    .survey-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .survey-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .survey-description {
        opacity: 0.95;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .survey-meta {
        display: flex;
        align-items: center;
        gap: 25px;
        flex-wrap: wrap;
        font-size: 14px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        opacity: 0.9;
    }

    .meta-item i {
        font-size: 16px;
    }

    /* Progress Bar */
    .progress-section {
        background: white;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .progress-title {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
    }

    .progress-percentage {
        font-size: 14px;
        font-weight: 600;
        color: #667eea;
    }

    .progress {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    /* Question Card */
    .question-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .question-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .question-header {
        display: flex;
        align-items: start;
        gap: 15px;
        margin-bottom: 20px;
    }

    .question-number {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        flex-shrink: 0;
    }

    .question-content {
        flex: 1;
    }

    .question-text {
        font-size: 17px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .question-required {
        color: #e74c3c;
        font-size: 14px;
        margin-left: 5px;
    }

    .question-help {
        font-size: 13px;
        color: #6c757d;
        margin-top: 5px;
        font-style: italic;
    }

    /* Input Styles */
    .form-control,
    .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #e74c3c;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    /* Radio & Checkbox Styles */
    .form-check {
        padding: 12px 15px;
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .form-check:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }

    .form-check-input {
        width: 20px;
        height: 20px;
        margin-top: 0;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .form-check-label {
        font-size: 15px;
        color: #2c3e50;
        cursor: pointer;
        margin-left: 10px;
    }

    /* Rating Stars */
    .rating-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rating-stars {
        display: flex;
        gap: 8px;
    }

    .rating-star {
        font-size: 32px;
        color: #e3e6f0;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .rating-star:hover,
    .rating-star.active {
        color: #ffc107;
        transform: scale(1.1);
    }

    .rating-value {
        font-size: 18px;
        font-weight: 600;
        color: #667eea;
        min-width: 60px;
    }

    /* Scale Slider */
    .scale-container {
        padding: 20px 0;
    }

    .scale-slider {
        width: 100%;
        height: 8px;
        border-radius: 5px;
        background: #e3e6f0;
        outline: none;
        -webkit-appearance: none;
    }

    .scale-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .scale-slider::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .scale-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 13px;
        color: #6c757d;
    }

    .scale-value-display {
        text-align: center;
        margin-top: 15px;
        font-size: 20px;
        font-weight: 600;
        color: #667eea;
    }

    /* Submit Section */
    .submit-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-top: 30px;
    }

    .submit-info {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .submit-info p {
        color: #1976d2;
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
    }

    .submit-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    /* Deadline Warning */
    .deadline-warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .deadline-warning i {
        font-size: 24px;
        color: #ffc107;
    }

    .deadline-warning-text {
        flex: 1;
    }

    .deadline-warning h5 {
        color: #856404;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .deadline-warning p {
        color: #856404;
        font-size: 13px;
        margin: 0;
    }

    /* Empty State for No Questions */
    .empty-questions {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .empty-questions i {
        font-size: 64px;
        color: #e3e6f0;
        margin-bottom: 20px;
    }

    .empty-questions h4 {
        color: #6c757d;
        margin-bottom: 10px;
    }

    .empty-questions p {
        color: #adb5bd;
        margin-bottom: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .survey-header {
            padding: 20px;
        }

        .survey-header h1 {
            font-size: 24px;
        }

        .question-card {
            padding: 20px;
        }

        .question-number {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        .question-text {
            font-size: 16px;
        }

        .rating-star {
            font-size: 28px;
        }

        .submit-actions {
            flex-direction: column;
        }

        .submit-actions .btn {
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('member/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('member/survey') ?>">Survei</a></li>
        <li class="breadcrumb-item active" aria-current="page">Isi Survei</li>
    </ol>
</nav>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Survey Header -->
<div class="survey-header">
    <h1><?= esc($survey->title ?? 'Survei') ?></h1>

    <?php if (!empty($survey->description)): ?>
        <div class="survey-description">
            <?= esc($survey->description) ?>
        </div>
    <?php endif; ?>

    <div class="survey-meta">
        <div class="meta-item">
            <i class="bi bi-question-circle"></i>
            <span><?= count($questions ?? []) ?> Pertanyaan</span>
        </div>
        <?php if (!empty($survey->end_date)): ?>
            <div class="meta-item">
                <i class="bi bi-calendar-event"></i>
                <span>Berakhir: <?= date('d M Y', strtotime($survey->end_date)) ?></span>
            </div>
        <?php endif; ?>
        <div class="meta-item">
            <i class="bi bi-clock"></i>
            <span>Estimasi: ~<?= ceil(count($questions ?? []) * 0.5) ?> menit</span>
        </div>
    </div>
</div>

<!-- Deadline Warning -->
<?php if (!empty($survey->end_date)): ?>
    <?php
    $daysLeft = max(0, floor((strtotime($survey->end_date) - time()) / 86400));
    if ($daysLeft <= 3):
    ?>
        <div class="deadline-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <div class="deadline-warning-text">
                <h5>Perhatian: Survei Akan Segera Berakhir</h5>
                <p>
                    <?php if ($daysLeft == 0): ?>
                        Survei ini berakhir hari ini. Pastikan Anda menyelesaikan sebelum tengah malam.
                    <?php elseif ($daysLeft == 1): ?>
                        Survei ini akan berakhir besok. Segera selesaikan untuk memastikan partisipasi Anda.
                    <?php else: ?>
                        Survei ini akan berakhir dalam <?= $daysLeft ?> hari lagi.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Progress Section -->
<div class="progress-section">
    <div class="progress-header">
        <span class="progress-title">Progress Pengisian</span>
        <span class="progress-percentage" id="progressPercentage">0%</span>
    </div>
    <div class="progress">
        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
    </div>
</div>

<!-- Survey Form -->
<?php if (!empty($questions)): ?>
    <form id="surveyForm" action="<?= base_url('member/survey/submit/' . $survey->id) ?>" method="POST">
        <?= csrf_field() ?>

        <?php foreach ($questions as $index => $question): ?>
            <div class="question-card" data-question-id="<?= $question->id ?>">
                <div class="question-header">
                    <div class="question-number"><?= $index + 1 ?></div>
                    <div class="question-content">
                        <div class="question-text">
                            <?= esc($question->question_text) ?>
                            <?php if (!empty($question->is_required)): ?>
                                <span class="question-required">*</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($question->help_text)): ?>
                            <div class="question-help">
                                <i class="bi bi-info-circle"></i>
                                <?= esc($question->help_text) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="question-answer">
                    <?php
                    $fieldName = 'answers[' . $question->id . ']';
                    $options = !empty($question->options) ? json_decode($question->options, true) : [];
                    ?>

                    <?php if ($question->question_type === 'text'): ?>
                        <!-- Text Input -->
                        <input type="text"
                            class="form-control"
                            name="<?= $fieldName ?>"
                            id="question_<?= $question->id ?>"
                            placeholder="<?= esc($question->placeholder ?? 'Masukkan jawaban Anda...') ?>"
                            <?= !empty($question->is_required) ? 'required' : '' ?>>

                    <?php elseif ($question->question_type === 'textarea'): ?>
                        <!-- Textarea -->
                        <textarea class="form-control"
                            name="<?= $fieldName ?>"
                            id="question_<?= $question->id ?>"
                            placeholder="<?= esc($question->placeholder ?? 'Masukkan jawaban Anda...') ?>"
                            <?= !empty($question->is_required) ? 'required' : '' ?>></textarea>

                    <?php elseif ($question->question_type === 'single_choice' && !empty($options)): ?>
                        <!-- Radio Buttons -->
                        <?php foreach ($options as $optIndex => $option): ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="radio"
                                    name="<?= $fieldName ?>"
                                    id="question_<?= $question->id ?>_<?= $optIndex ?>"
                                    value="<?= esc($option) ?>"
                                    <?= !empty($question->is_required) ? 'required' : '' ?>>
                                <label class="form-check-label" for="question_<?= $question->id ?>_<?= $optIndex ?>">
                                    <?= esc($option) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($question->question_type === 'multiple_choice' && !empty($options)): ?>
                        <!-- Checkboxes -->
                        <?php foreach ($options as $optIndex => $option): ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="<?= $fieldName ?>[]"
                                    id="question_<?= $question->id ?>_<?= $optIndex ?>"
                                    value="<?= esc($option) ?>">
                                <label class="form-check-label" for="question_<?= $question->id ?>_<?= $optIndex ?>">
                                    <?= esc($option) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!empty($question->is_required)): ?>
                            <input type="hidden"
                                class="checkbox-required"
                                data-group="<?= $fieldName ?>"
                                required>
                        <?php endif; ?>

                    <?php elseif ($question->question_type === 'rating'): ?>
                        <!-- Rating Stars -->
                        <?php
                        $maxRating = $question->max_value ?? 5;
                        $minRating = $question->min_value ?? 1;
                        ?>
                        <div class="rating-container">
                            <div class="rating-stars" data-question="<?= $question->id ?>">
                                <?php for ($i = $minRating; $i <= $maxRating; $i++): ?>
                                    <i class="bi bi-star-fill rating-star" data-value="<?= $i ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value" id="rating_value_<?= $question->id ?>">0/<?= $maxRating ?></span>
                        </div>
                        <input type="hidden"
                            name="<?= $fieldName ?>"
                            id="question_<?= $question->id ?>"
                            <?= !empty($question->is_required) ? 'required' : '' ?>>

                    <?php elseif ($question->question_type === 'scale'): ?>
                        <!-- Scale Slider -->
                        <?php
                        $minScale = $question->min_value ?? 1;
                        $maxScale = $question->max_value ?? 10;
                        $stepScale = $question->step_value ?? 1;
                        ?>
                        <div class="scale-container">
                            <input type="range"
                                class="scale-slider"
                                name="<?= $fieldName ?>"
                                id="question_<?= $question->id ?>"
                                min="<?= $minScale ?>"
                                max="<?= $maxScale ?>"
                                step="<?= $stepScale ?>"
                                value="<?= floor(($minScale + $maxScale) / 2) ?>"
                                <?= !empty($question->is_required) ? 'required' : '' ?>>
                            <div class="scale-labels">
                                <span><?= $minScale ?></span>
                                <span><?= $maxScale ?></span>
                            </div>
                            <div class="scale-value-display" id="scale_value_<?= $question->id ?>">
                                <?= floor(($minScale + $maxScale) / 2) ?>
                            </div>
                        </div>

                    <?php elseif ($question->question_type === 'date'): ?>
                        <!-- Date Input -->
                        <input type="date"
                            class="form-control"
                            name="<?= $fieldName ?>"
                            id="question_<?= $question->id ?>"
                            <?= !empty($question->is_required) ? 'required' : '' ?>>

                    <?php elseif ($question->question_type === 'number'): ?>
                        <!-- Number Input -->
                        <input type="number"
                            class="form-control"
                            name="<?= $fieldName ?>"
                            id="question_<?= $question->id ?>"
                            placeholder="<?= esc($question->placeholder ?? 'Masukkan angka...') ?>"
                            <?= !empty($question->min_value) ? 'min="' . $question->min_value . '"' : '' ?>
                            <?= !empty($question->max_value) ? 'max="' . $question->max_value . '"' : '' ?>
                            <?= !empty($question->is_required) ? 'required' : '' ?>>

                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Submit Section -->
        <div class="submit-section">
            <div class="submit-info">
                <p>
                    <i class="bi bi-info-circle"></i>
                    <strong>Pastikan semua jawaban sudah benar.</strong> Setelah mengirim, Anda tidak dapat mengubah jawaban.
                    <?php if (!empty($survey->is_anonymous)): ?>
                        Jawaban Anda akan disimpan secara anonim.
                    <?php endif; ?>
                </p>
            </div>

            <div class="submit-actions">
                <a href="<?= base_url('member/survey') ?>" class="btn btn-light btn-lg">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="submitButton">
                    <i class="bi bi-send"></i> Kirim Jawaban
                </button>
            </div>
        </div>
    </form>

<?php else: ?>
    <!-- Empty State -->
    <div class="empty-questions">
        <i class="bi bi-clipboard-x"></i>
        <h4>Tidak Ada Pertanyaan</h4>
        <p>Survei ini belum memiliki pertanyaan. Silakan hubungi administrator.</p>
        <a href="<?= base_url('member/survey') ?>" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Survei
        </a>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        const totalQuestions = $('.question-card').length;

        // Update progress bar
        function updateProgress() {
            let answeredCount = 0;

            $('.question-card').each(function() {
                const questionId = $(this).data('question-id');
                let isAnswered = false;

                // Check different input types
                if ($(this).find('input[type="text"], input[type="number"], input[type="date"], textarea').length) {
                    isAnswered = $(this).find('input[type="text"], input[type="number"], input[type="date"], textarea').val() !== '';
                } else if ($(this).find('input[type="radio"]').length) {
                    isAnswered = $(this).find('input[type="radio"]:checked').length > 0;
                } else if ($(this).find('input[type="checkbox"]').length) {
                    isAnswered = $(this).find('input[type="checkbox"]:checked').length > 0;
                } else if ($(this).find('input[type="hidden"][name*="answers"]').length) {
                    // For rating
                    isAnswered = $(this).find('input[type="hidden"][name*="answers"]').val() !== '';
                } else if ($(this).find('input[type="range"]').length) {
                    // Scale always has value
                    isAnswered = true;
                }

                if (isAnswered) {
                    answeredCount++;
                }
            });

            const percentage = totalQuestions > 0 ? Math.round((answeredCount / totalQuestions) * 100) : 0;

            $('#progressBar').css('width', percentage + '%');
            $('#progressPercentage').text(percentage + '%');
        }

        // Rating stars functionality
        $('.rating-star').on('click', function() {
            const value = $(this).data('value');
            const questionId = $(this).closest('.rating-stars').data('question');
            const stars = $(this).closest('.rating-stars').find('.rating-star');

            // Update hidden input
            $(`#question_${questionId}`).val(value);

            // Update visual stars
            stars.each(function() {
                if ($(this).data('value') <= value) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });

            // Update value display
            const maxRating = stars.length;
            $(`#rating_value_${questionId}`).text(`${value}/${maxRating}`);

            updateProgress();
        });

        // Scale slider functionality
        $('.scale-slider').on('input', function() {
            const value = $(this).val();
            const questionId = $(this).attr('id').replace('question_', '');
            $(`#scale_value_${questionId}`).text(value);
        });

        // Update progress on any input change
        $('input, textarea, select').on('change input', function() {
            updateProgress();
        });

        // Checkbox required validation
        $('.form-check-input[type="checkbox"]').on('change', function() {
            const groupName = $(this).attr('name');
            const groupCheckboxes = $(`input[name="${groupName}"]`);
            const checkedCount = groupCheckboxes.filter(':checked').length;

            // Update hidden required field
            const hiddenRequired = $(this).closest('.question-answer').find('.checkbox-required');
            if (hiddenRequired.length) {
                if (checkedCount > 0) {
                    hiddenRequired.prop('required', false);
                } else {
                    hiddenRequired.prop('required', true);
                }
            }
        });

        // Form submission
        $('#surveyForm').on('submit', function(e) {
            e.preventDefault();

            // Validate required checkboxes
            let allCheckboxValid = true;
            $('.checkbox-required').each(function() {
                const groupName = $(this).data('group');
                const checkedCount = $(`input[name="${groupName}"]:checked`).length;
                if (checkedCount === 0) {
                    allCheckboxValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Pertanyaan Belum Dijawab',
                        text: 'Mohon jawab semua pertanyaan yang wajib diisi (ditandai dengan *).',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
            });

            if (!allCheckboxValid) {
                return false;
            }

            // Validate all required fields
            if (!this.checkValidity()) {
                this.reportValidity();
                return false;
            }

            // Confirmation dialog
            Swal.fire({
                title: 'Kirim Jawaban?',
                text: 'Pastikan semua jawaban sudah benar. Anda tidak dapat mengubah jawaban setelah mengirim.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Cek Lagi'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Mengirim Jawaban...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit form
                    this.submit();
                }
            });
        });

        // Initial progress update
        updateProgress();

        // Smooth scroll animation
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

        document.querySelectorAll('.question-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    });
</script>
<?= $this->endSection() ?>