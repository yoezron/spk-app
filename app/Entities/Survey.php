<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * Survey Entity
 * 
 * Representasi object-oriented dari survey
 * Menyediakan business logic methods untuk survey management
 * 
 * @package App\Entities
 * @author  SPK Development Team
 * @version 1.0.0
 */
class Survey extends Entity
{
    /**
     * Data mapping (if column names differ from property names)
     */
    protected $datamap = [];

    /**
     * Define date fields for automatic Time conversion
     */
    protected $dates = [
        'start_date',
        'end_date',
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Type casting for properties
     */
    protected $casts = [
        'id' => 'integer',
        'created_by' => 'integer',
        'published_by' => '?integer',
        'is_published' => 'boolean',
        'is_anonymous' => 'boolean',
        'allow_multiple_responses' => 'boolean',
    ];

    // ========================================
    // BASIC GETTERS
    // ========================================

    /**
     * Get survey ID
     * 
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->attributes['id'];
    }

    /**
     * Get survey title
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return $this->attributes['title'] ?? '';
    }

    /**
     * Get survey description
     * 
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    /**
     * Get survey slug
     * 
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->attributes['slug'] ?? null;
    }

    /**
     * Get survey type
     * 
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->attributes['type'] ?? null;
    }

    /**
     * Get target audience
     * 
     * @return string|null
     */
    public function getTargetAudience(): ?string
    {
        return $this->attributes['target_audience'] ?? null;
    }

    // ========================================
    // STATUS METHODS
    // ========================================

    /**
     * Check if survey is published
     * 
     * @return bool
     */
    public function isPublished(): bool
    {
        return (bool) ($this->attributes['is_published'] ?? false);
    }

    /**
     * Check if survey is draft
     * 
     * @return bool
     */
    public function isDraft(): bool
    {
        return !$this->isPublished();
    }

    /**
     * Check if survey is active (published and within date range)
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $now = Time::now();
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        // Check if current time is within survey period
        if ($startDate && $now->isBefore($startDate)) {
            return false;
        }

        if ($endDate && $now->isAfter($endDate)) {
            return false;
        }

        return true;
    }

    /**
     * Check if survey is scheduled (published but not started yet)
     * 
     * @return bool
     */
    public function isScheduled(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $startDate = $this->getStartDate();

        if (!$startDate) {
            return false;
        }

        return Time::now()->isBefore($startDate);
    }

    /**
     * Check if survey is closed (ended)
     * 
     * @return bool
     */
    public function isClosed(): bool
    {
        $endDate = $this->getEndDate();

        if (!$endDate) {
            return false;
        }

        return Time::now()->isAfter($endDate);
    }

    /**
     * Check if survey is expired (closed)
     * Alias for isClosed()
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->isClosed();
    }

    /**
     * Check if survey is open for responses
     * 
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->isActive() && !$this->isClosed();
    }

    /**
     * Get survey status
     * 
     * @return string 'draft', 'scheduled', 'active', 'closed'
     */
    public function getStatus(): string
    {
        if ($this->isDraft()) {
            return 'draft';
        }

        if ($this->isClosed()) {
            return 'closed';
        }

        if ($this->isScheduled()) {
            return 'scheduled';
        }

        if ($this->isActive()) {
            return 'active';
        }

        return 'unknown';
    }

    /**
     * Get status label in Indonesian
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        return match ($this->getStatus()) {
            'draft' => 'Draft',
            'scheduled' => 'Terjadwal',
            'active' => 'Aktif',
            'closed' => 'Ditutup',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge class for CSS
     * 
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->getStatus()) {
            'draft' => 'badge-secondary',
            'scheduled' => 'badge-info',
            'active' => 'badge-success',
            'closed' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    // ========================================
    // DATE METHODS
    // ========================================

    /**
     * Get start date
     * 
     * @return Time|null
     */
    public function getStartDate(): ?Time
    {
        return $this->attributes['start_date'] ?? null;
    }

    /**
     * Get formatted start date
     * 
     * @param string $format Date format (default: 'd F Y')
     * @return string|null
     */
    public function getFormattedStartDate(string $format = 'd F Y'): ?string
    {
        $startDate = $this->getStartDate();

        if (!$startDate) {
            return null;
        }

        return $startDate->toLocalizedString($format);
    }

    /**
     * Get end date
     * 
     * @return Time|null
     */
    public function getEndDate(): ?Time
    {
        return $this->attributes['end_date'] ?? null;
    }

    /**
     * Get formatted end date
     * 
     * @param string $format Date format (default: 'd F Y')
     * @return string|null
     */
    public function getFormattedEndDate(string $format = 'd F Y'): ?string
    {
        $endDate = $this->getEndDate();

        if (!$endDate) {
            return null;
        }

        return $endDate->toLocalizedString($format);
    }

    /**
     * Get survey period (start - end)
     * 
     * @return string|null
     */
    public function getPeriod(): ?string
    {
        $start = $this->getFormattedStartDate();
        $end = $this->getFormattedEndDate();

        if (!$start && !$end) {
            return null;
        }

        if (!$start) {
            return 'Sampai ' . $end;
        }

        if (!$end) {
            return 'Mulai ' . $start;
        }

        return $start . ' - ' . $end;
    }

    /**
     * Get remaining days until end date
     * 
     * @return int|null Days remaining (negative if expired)
     */
    public function getRemainingDays(): ?int
    {
        $endDate = $this->getEndDate();

        if (!$endDate) {
            return null;
        }

        $now = Time::now();
        $diff = $now->difference($endDate);

        return $diff->getDays() * ($now->isBefore($endDate) ? 1 : -1);
    }

    /**
     * Get days until start date
     * 
     * @return int|null Days until start (negative if already started)
     */
    public function getDaysUntilStart(): ?int
    {
        $startDate = $this->getStartDate();

        if (!$startDate) {
            return null;
        }

        $now = Time::now();
        $diff = $now->difference($startDate);

        return $diff->getDays() * ($now->isBefore($startDate) ? 1 : -1);
    }

    /**
     * Get survey duration in days
     * 
     * @return int|null
     */
    public function getDuration(): ?int
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if (!$startDate || !$endDate) {
            return null;
        }

        return $startDate->difference($endDate)->getDays();
    }

    /**
     * Get published date
     * 
     * @return Time|null
     */
    public function getPublishedAt(): ?Time
    {
        return $this->attributes['published_at'] ?? null;
    }

    /**
     * Get formatted published date
     * 
     * @param string $format Date format (default: 'd F Y H:i')
     * @return string|null
     */
    public function getFormattedPublishedDate(string $format = 'd F Y H:i'): ?string
    {
        $publishedAt = $this->getPublishedAt();

        if (!$publishedAt) {
            return null;
        }

        return $publishedAt->toLocalizedString($format);
    }

    // ========================================
    // QUESTION & RESPONSE METHODS
    // ========================================

    /**
     * Get question count
     * 
     * @return int
     */
    public function getQuestionCount(): int
    {
        // Check if count available from joined data
        if (isset($this->attributes['question_count'])) {
            return (int) $this->attributes['question_count'];
        }

        return 0;
    }

    /**
     * Get response count
     * 
     * @return int
     */
    public function getResponseCount(): int
    {
        // Check if count available from joined data
        if (isset($this->attributes['response_count'])) {
            return (int) $this->attributes['response_count'];
        }

        return 0;
    }

    /**
     * Check if survey has questions
     * 
     * @return bool
     */
    public function hasQuestions(): bool
    {
        return $this->getQuestionCount() > 0;
    }

    /**
     * Check if survey has responses
     * 
     * @return bool
     */
    public function hasResponses(): bool
    {
        return $this->getResponseCount() > 0;
    }

    /**
     * Get response rate percentage
     * Requires target_respondent_count attribute
     * 
     * @return float|null Percentage (0-100)
     */
    public function getResponseRate(): ?float
    {
        $targetCount = $this->attributes['target_respondent_count'] ?? null;

        if (!$targetCount || $targetCount <= 0) {
            return null;
        }

        $responseCount = $this->getResponseCount();

        return round(($responseCount / $targetCount) * 100, 2);
    }

    // ========================================
    // PERMISSION & ACCESS METHODS
    // ========================================

    /**
     * Check if user can respond to this survey
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function canUserRespond(int $userId): bool
    {
        // Survey must be open
        if (!$this->isOpen()) {
            return false;
        }

        // If multiple responses not allowed, check if user already responded
        if (!$this->allowsMultipleResponses()) {
            // This would need to query the database
            // For now, return true
            // TODO: Implement actual check via SurveyResponseModel
            return true;
        }

        return true;
    }

    /**
     * Check if survey is anonymous
     * 
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return (bool) ($this->attributes['is_anonymous'] ?? false);
    }

    /**
     * Check if survey allows multiple responses from same user
     * 
     * @return bool
     */
    public function allowsMultipleResponses(): bool
    {
        return (bool) ($this->attributes['allow_multiple_responses'] ?? false);
    }

    /**
     * Check if survey is restricted to specific audience
     * 
     * @return bool
     */
    public function hasTargetAudience(): bool
    {
        return !empty($this->getTargetAudience());
    }

    /**
     * Get created by user ID
     * 
     * @return int|null
     */
    public function getCreatedBy(): ?int
    {
        return $this->attributes['created_by'] ?? null;
    }

    /**
     * Get published by user ID
     * 
     * @return int|null
     */
    public function getPublishedBy(): ?int
    {
        return $this->attributes['published_by'] ?? null;
    }

    // ========================================
    // DISPLAY METHODS
    // ========================================

    /**
     * Get survey icon class based on type
     * 
     * @return string
     */
    public function getIconClass(): string
    {
        $type = strtolower($this->getType() ?? '');

        return match ($type) {
            'opinion', 'pendapat' => 'fas fa-comments',
            'feedback' => 'fas fa-comment-dots',
            'evaluation', 'evaluasi' => 'fas fa-clipboard-check',
            'research', 'penelitian' => 'fas fa-flask',
            'poll', 'polling' => 'fas fa-poll',
            'questionnaire', 'kuesioner' => 'fas fa-list-check',
            default => 'fas fa-poll-h',
        };
    }

    /**
     * Get survey type badge class
     * 
     * @return string
     */
    public function getTypeBadgeClass(): string
    {
        $type = strtolower($this->getType() ?? '');

        return match ($type) {
            'opinion', 'pendapat' => 'badge-info',
            'feedback' => 'badge-warning',
            'evaluation', 'evaluasi' => 'badge-success',
            'research', 'penelitian' => 'badge-primary',
            'poll', 'polling' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get survey summary for display
     * 
     * @return string
     */
    public function getSummary(): string
    {
        $summary = $this->getTitle();

        $questionCount = $this->getQuestionCount();
        $responseCount = $this->getResponseCount();

        $summary .= sprintf(
            ' (%d pertanyaan, %d respons)',
            $questionCount,
            $responseCount
        );

        if ($this->isActive()) {
            $remaining = $this->getRemainingDays();
            if ($remaining !== null && $remaining > 0) {
                $summary .= sprintf(' - Tersisa %d hari', $remaining);
            }
        }

        return $summary;
    }

    /**
     * Get time remaining text
     * 
     * @return string|null
     */
    public function getTimeRemainingText(): ?string
    {
        $remaining = $this->getRemainingDays();

        if ($remaining === null) {
            return null;
        }

        if ($remaining < 0) {
            return 'Sudah berakhir';
        }

        if ($remaining === 0) {
            return 'Berakhir hari ini';
        }

        if ($remaining === 1) {
            return 'Tersisa 1 hari';
        }

        return sprintf('Tersisa %d hari', $remaining);
    }

    /**
     * Get completion percentage (questions answered / total questions)
     * This would typically be calculated per user response
     * 
     * @return float
     */
    public function getCompletionRate(): float
    {
        $questionCount = $this->getQuestionCount();

        if ($questionCount <= 0) {
            return 0;
        }

        // This is a placeholder
        // Actual completion rate would need to be calculated from responses
        return 100.0;
    }

    // ========================================
    // VALIDATION METHODS
    // ========================================

    /**
     * Check if survey is ready to publish
     * Survey must have questions and valid dates
     * 
     * @return bool
     */
    public function isReadyToPublish(): bool
    {
        // Must have questions
        if (!$this->hasQuestions()) {
            return false;
        }

        // Must have title
        if (empty($this->getTitle())) {
            return false;
        }

        // If has start date, it must be valid
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if ($startDate && $endDate && $startDate->isAfter($endDate)) {
            return false;
        }

        return true;
    }

    /**
     * Get validation errors (reasons why survey cannot be published)
     * 
     * @return array
     */
    public function getPublishValidationErrors(): array
    {
        $errors = [];

        if (empty($this->getTitle())) {
            $errors[] = 'Survey harus memiliki judul';
        }

        if (!$this->hasQuestions()) {
            $errors[] = 'Survey harus memiliki minimal 1 pertanyaan';
        }

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if ($startDate && $endDate && $startDate->isAfter($endDate)) {
            $errors[] = 'Tanggal mulai tidak boleh setelah tanggal selesai';
        }

        return $errors;
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get survey URL
     * 
     * @return string
     */
    public function getUrl(): string
    {
        $slug = $this->getSlug();

        if ($slug) {
            return base_url('survey/' . $slug);
        }

        return base_url('survey/' . $this->getId());
    }

    /**
     * Get survey results URL (admin)
     * 
     * @return string
     */
    public function getResultsUrl(): string
    {
        return base_url('admin/survey/results/' . $this->getId());
    }

    /**
     * Convert survey to array for JSON response
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'slug' => $this->getSlug(),
            'type' => $this->getType(),
            'status' => $this->getStatus(),
            'status_label' => $this->getStatusLabel(),
            'is_published' => $this->isPublished(),
            'is_active' => $this->isActive(),
            'is_closed' => $this->isClosed(),
            'is_anonymous' => $this->isAnonymous(),
            'allow_multiple_responses' => $this->allowsMultipleResponses(),
            'start_date' => $this->getFormattedStartDate(),
            'end_date' => $this->getFormattedEndDate(),
            'period' => $this->getPeriod(),
            'remaining_days' => $this->getRemainingDays(),
            'question_count' => $this->getQuestionCount(),
            'response_count' => $this->getResponseCount(),
            'duration' => $this->getDuration(),
            'url' => $this->getUrl(),
        ];
    }

    /**
     * Magic getter for better property access
     * 
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        // Custom getters
        if ($key === 'status') {
            return $this->getStatus();
        }

        if ($key === 'question_count') {
            return $this->getQuestionCount();
        }

        if ($key === 'response_count') {
            return $this->getResponseCount();
        }

        if ($key === 'url') {
            return $this->getUrl();
        }

        // Default Entity behavior
        return parent::__get($key);
    }

    /**
     * Magic toString method
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
