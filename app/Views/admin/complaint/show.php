<?php

/**
 * View: Admin Complaint Detail & Reply System
 * Controller: Admin\ComplaintController::show($id)
 * Description: Comprehensive ticket detail dengan timeline, reply, dan actions
 * 
 * Features:
 * - Full ticket information display
 * - Status & priority badges dengan animations
 * - SLA tracker dengan visual indicators
 * - Timeline conversation (chronological)
 * - Public & Internal reply system
 * - File attachments viewer dengan lightbox
 * - Status change panel dengan notes
 * - Assignment management
 * - Activity history timeline
 * - Quick actions sidebar
 * - Related tickets suggestions
 * - Export ticket to PDF
 * - Print functionality
 * - Responsive layout
 * - Real-time updates
 * - Audit trail display
 * 
 * @package App\Views\Admin\Complaint
 * @author  SPK Development Team
 * @version 3.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/lightbox/lightbox.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-lite.min.css') ?>">
<style>
    .ticket-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px 12px 0 0;
        margin-bottom: 0;
    }

    .ticket-number {
        font-size: 16px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ticket-subject {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 15px;
    }

    .ticket-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
        opacity: 0.95;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ticket-info-bar {
        background: white;
        padding: 20px 30px;
        border-bottom: 2px solid #f1f3f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status-priority-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-badge {
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-open {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-in-progress {
        background: #fff3cd;
        color: #856404;
    }

    .status-resolved {
        background: #d4edda;
        color: #155724;
    }

    .status-closed {
        background: #e2e3e5;
        color: #383d41;
    }

    .priority-badge {
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-low {
        background: #d4edda;
        color: #155724;
    }

    .priority-medium {
        background: #fff3cd;
        color: #856404;
    }

    .priority-high {
        background: #f8d7da;
        color: #721c24;
    }

    .priority-urgent {
        background: #dc3545;
        color: white;
        animation: pulse 2s infinite;
    }

    .sla-tracker {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .sla-ok {
        background: #d4edda;
        color: #155724;
    }

    .sla-warning {
        background: #fff3cd;
        color: #856404;
    }

    .sla-danger {
        background: #f8d7da;
        color: #721c24;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

    .content-section {
        background: white;
        padding: 30px;
        border-bottom: 2px solid #f1f3f5;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #667eea;
    }

    .ticket-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .detail-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .detail-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 500;
    }

    .original-message {
        background: #f8f9fa;
        border-left: 4px solid #667eea;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .message-content {
        line-height: 1.7;
        color: #495057;
    }

    .attachments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .attachment-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid #e9ecef;
    }

    .attachment-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }

    .attachment-preview {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .attachment-info {
        padding: 8px;
        background: white;
        font-size: 11px;
        text-align: center;
    }

    .timeline-container {
        position: relative;
        padding-left: 40px;
        margin-top: 20px;
    }

    .timeline-line {
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #667eea, #e9ecef);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        animation: fadeInUp 0.5s;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .timeline-marker {
        position: absolute;
        left: -27px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
        z-index: 1;
    }

    .timeline-marker.system {
        border-color: #6c757d;
    }

    .timeline-marker.internal {
        border-color: #ffc107;
    }

    .reply-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }

    .reply-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    }

    .reply-card.staff-reply {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-color: #667eea;
    }

    .reply-card.internal-note {
        background: #fff3cd;
        border-color: #ffc107;
    }

    .reply-card.system-message {
        background: #e2e3e5;
        border: 1px dashed #6c757d;
    }

    .reply-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .reply-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .reply-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 700;
        font-size: 16px;
    }

    .reply-avatar.staff {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .reply-author-name {
        font-weight: 700;
        color: #2c3e50;
        font-size: 15px;
    }

    .staff-badge {
        background: #28a745;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 8px;
    }

    .internal-badge {
        background: #ffc107;
        color: #000;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 8px;
    }

    .reply-date {
        font-size: 12px;
        color: #6c757d;
    }

    .reply-content {
        line-height: 1.7;
        color: #495057;
    }

    .reply-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        gap: 10px;
    }

    .reply-form-card {
        background: white;
        border: 2px solid #667eea;
        border-radius: 12px;
        padding: 25px;
        margin-top: 30px;
    }

    .reply-form-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f5;
    }

    .reply-form-header i {
        font-size: 24px;
        color: #667eea;
    }

    .reply-form-header h5 {
        margin: 0;
        font-weight: 700;
        color: #2c3e50;
    }

    .sidebar-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .sidebar-title {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-btn-full {
        width: 100%;
        padding: 12px;
        margin-bottom: 10px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .action-btn-full:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .history-item {
        padding: 12px;
        background: #f8f9fa;
        border-left: 3px solid #667eea;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .history-time {
        font-size: 11px;
        color: #6c757d;
    }

    .history-text {
        font-size: 13px;
        color: #495057;
        margin-top: 4px;
    }

    .related-ticket {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .related-ticket:hover {
        background: #e9ecef;
        transform: translateX(4px);
    }

    .empty-timeline {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-timeline i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 15px;
    }

    .category-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #e9ecef;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Navigation -->
<div class="mb-3">
    <a href="<?= base_url('admin/complaints') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Tickets
    </a>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Ticket Header -->
        <div class="card mb-4">
            <div class="ticket-header">
                <div class="ticket-number">
                    <i class="bi bi-ticket-perforated"></i>
                    <strong>Ticket #<?= esc($ticket->ticket_number) ?></strong>
                </div>
                <h2 class="ticket-subject"><?= esc($ticket->subject) ?></h2>
                <div class="ticket-meta">
                    <span class="meta-item">
                        <i class="bi bi-person-circle"></i>
                        <?= esc($ticket->user_name ?? $ticket->email) ?>
                    </span>
                    <span class="meta-item">
                        <i class="bi bi-calendar3"></i>
                        Created: <?= date('d M Y, H:i', strtotime($ticket->created_at)) ?>
                    </span>
                    <span class="meta-item">
                        <i class="bi bi-clock-history"></i>
                        Last Update: <?= date('d M Y, H:i', strtotime($ticket->updated_at)) ?>
                    </span>
                </div>
            </div>

            <div class="ticket-info-bar">
                <div class="status-priority-group">
                    <span class="status-badge status-<?= str_replace('_', '-', $ticket->status) ?>">
                        <?= str_replace('_', ' ', ucfirst($ticket->status)) ?>
                    </span>
                    <span class="priority-badge priority-<?= $ticket->priority ?>">
                        <i class="bi bi-flag-fill"></i>
                        <?= ucfirst($ticket->priority) ?>
                    </span>
                    <?php if (!empty($ticket->category_name)): ?>
                        <span class="category-badge">
                            <i class="bi bi-tag"></i>
                            <?= esc($ticket->category_name) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php
                $createdTime = strtotime($ticket->created_at);
                $currentTime = time();
                $hoursElapsed = ($currentTime - $createdTime) / 3600;

                $slaClass = 'sla-ok';
                $slaText = 'On Track';
                $slaIcon = 'check-circle';

                if ($hoursElapsed > 72) {
                    $slaClass = 'sla-danger';
                    $slaText = 'Overdue';
                    $slaIcon = 'exclamation-triangle';
                } elseif ($hoursElapsed > 48) {
                    $slaClass = 'sla-warning';
                    $slaText = 'Warning';
                    $slaIcon = 'clock';
                }
                ?>

                <div class="sla-tracker <?= $slaClass ?>">
                    <i class="bi bi-<?= $slaIcon ?>"></i>
                    <span>SLA: <?= $slaText ?></span>
                    <span>(<?= number_format($hoursElapsed, 1) ?>h)</span>
                </div>
            </div>

            <!-- Ticket Details -->
            <div class="content-section">
                <div class="section-title">
                    <i class="bi bi-info-circle-fill"></i>
                    Ticket Information
                </div>

                <div class="ticket-details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Reporter</span>
                        <span class="detail-value"><?= esc($ticket->user_name ?? 'Unknown') ?></span>
                        <small class="text-muted"><?= esc($ticket->email) ?></small>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Province</span>
                        <span class="detail-value"><?= esc($ticket->province_name ?? '-') ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">University</span>
                        <span class="detail-value"><?= esc($ticket->university_name ?? '-') ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Assigned To</span>
                        <?php if (!empty($ticket->assigned_to_name)): ?>
                            <span class="detail-value"><?= esc($ticket->assigned_to_name) ?></span>
                            <small class="text-muted">
                                Assigned: <?= date('d M Y', strtotime($ticket->assigned_at)) ?>
                            </small>
                        <?php else: ?>
                            <span class="detail-value text-muted">Unassigned</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($ticket->status === 'resolved' && !empty($ticket->resolved_at)): ?>
                        <div class="detail-item">
                            <span class="detail-label">Resolved</span>
                            <span class="detail-value"><?= date('d M Y, H:i', strtotime($ticket->resolved_at)) ?></span>
                            <small class="text-muted">By: <?= esc($ticket->resolved_by_name ?? 'System') ?></small>
                        </div>
                    <?php endif; ?>

                    <?php if ($ticket->status === 'closed' && !empty($ticket->closed_at)): ?>
                        <div class="detail-item">
                            <span class="detail-label">Closed</span>
                            <span class="detail-value"><?= date('d M Y, H:i', strtotime($ticket->closed_at)) ?></span>
                            <small class="text-muted">By: <?= esc($ticket->closed_by_name ?? 'System') ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Original Message -->
            <div class="content-section">
                <div class="section-title">
                    <i class="bi bi-chat-left-text-fill"></i>
                    Original Message
                </div>

                <div class="original-message">
                    <div class="message-content">
                        <?= nl2br(esc($ticket->message)) ?>
                    </div>
                </div>

                <!-- Attachments -->
                <?php if (!empty($ticket->attachments)): ?>
                    <div class="attachments-section">
                        <h6 class="mb-3">
                            <i class="bi bi-paperclip me-2"></i>
                            Attachments (<?= count($ticket->attachments) ?>)
                        </h6>
                        <div class="attachments-grid">
                            <?php foreach ($ticket->attachments as $attachment): ?>
                                <div class="attachment-item">
                                    <a href="<?= base_url('uploads/complaints/' . $attachment->file_path) ?>"
                                        data-lightbox="ticket-attachments"
                                        data-title="<?= esc($attachment->file_name) ?>">
                                        <?php if (in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                            <img src="<?= base_url('uploads/complaints/' . $attachment->file_path) ?>"
                                                alt="Attachment" class="attachment-preview">
                                        <?php else: ?>
                                            <div class="attachment-preview d-flex align-items-center justify-content-center bg-light">
                                                <i class="bi bi-file-earmark text-muted" style="font-size: 48px;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="attachment-info">
                                        <?= esc($attachment->file_name) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Timeline / Replies -->
            <div class="content-section">
                <div class="section-title">
                    <i class="bi bi-clock-history"></i>
                    Activity Timeline
                    <span class="badge bg-primary ms-2"><?= count($replies ?? []) ?> Replies</span>
                </div>

                <?php if (!empty($replies)): ?>
                    <div class="timeline-container">
                        <div class="timeline-line"></div>

                        <?php foreach ($replies as $reply): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker <?= $reply->is_internal ? 'internal' : ($reply->is_staff ? 'staff' : '') ?>"></div>

                                <div class="reply-card <?= $reply->is_staff ? 'staff-reply' : '' ?> <?= $reply->is_internal ? 'internal-note' : '' ?>">
                                    <div class="reply-header">
                                        <div class="reply-author">
                                            <div class="reply-avatar <?= $reply->is_staff ? 'staff' : '' ?>">
                                                <?= strtoupper(substr($reply->replier_name ?? 'U', 0, 2)) ?>
                                            </div>
                                            <div>
                                                <span class="reply-author-name">
                                                    <?= esc($reply->replier_name ?? 'Unknown') ?>
                                                    <?php if ($reply->is_staff): ?>
                                                        <span class="staff-badge">Staff</span>
                                                    <?php endif; ?>
                                                    <?php if ($reply->is_internal): ?>
                                                        <span class="internal-badge">Internal Note</span>
                                                    <?php endif; ?>
                                                </span>
                                                <div class="reply-date">
                                                    <?= date('d M Y, H:i', strtotime($reply->created_at)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="reply-content">
                                        <?= nl2br(esc($reply->message)) ?>
                                    </div>

                                    <?php if (!empty($reply->attachment_path)): ?>
                                        <div class="reply-actions">
                                            <a href="<?= base_url('uploads/complaints/' . $reply->attachment_path) ?>"
                                                class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-paperclip me-1"></i> View Attachment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-timeline">
                        <i class="bi bi-chat-left-dots"></i>
                        <h6>No replies yet</h6>
                        <p class="text-muted">Be the first to respond to this ticket</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reply Form -->
            <?php if ($ticket->status !== 'closed'): ?>
                <div class="content-section">
                    <div class="reply-form-card">
                        <div class="reply-form-header">
                            <i class="bi bi-reply-fill"></i>
                            <h5>Add Reply</h5>
                        </div>

                        <form id="replyForm" method="POST" action="<?= base_url('admin/complaints/reply/' . $ticket->id) ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="message" id="replyMessage" rows="6"
                                    placeholder="Type your reply here..." required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Attachment (Optional)</label>
                                        <input type="file" class="form-control" name="attachment" accept="image/*,.pdf,.doc,.docx">
                                        <small class="form-text text-muted">Max 5MB. Images, PDF, or Documents</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Change Status (Optional)</label>
                                        <select class="form-select" name="new_status">
                                            <option value="">Keep Current</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="isInternal">
                                <label class="form-check-label" for="isInternal">
                                    <strong>Internal Note</strong> (Not visible to user)
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Send Reply
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">
                                    <i class="bi bi-save me-1"></i> Save as Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="content-section">
                    <div class="alert alert-secondary">
                        <i class="bi bi-lock me-2"></i>
                        This ticket is closed. Replies are disabled.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="sidebar-card">
            <div class="sidebar-title">
                <i class="bi bi-lightning-fill"></i>
                Quick Actions
            </div>

            <button type="button" class="btn btn-success action-btn-full" data-bs-toggle="modal" data-bs-target="#statusModal">
                <i class="bi bi-arrow-repeat me-2"></i> Change Status
            </button>

            <button type="button" class="btn btn-warning action-btn-full" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="bi bi-person-plus me-2"></i> Assign Ticket
            </button>

            <button type="button" class="btn btn-info action-btn-full" data-bs-toggle="modal" data-bs-target="#priorityModal">
                <i class="bi bi-flag me-2"></i> Set Priority
            </button>

            <button type="button" class="btn btn-danger action-btn-full" id="closeTicketBtn">
                <i class="bi bi-x-circle me-2"></i> Close Ticket
            </button>

            <hr>

            <button type="button" class="btn btn-outline-primary action-btn-full" id="exportPdfBtn">
                <i class="bi bi-file-earmark-pdf me-2"></i> Export to PDF
            </button>

            <button type="button" class="btn btn-outline-secondary action-btn-full" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Print
            </button>
        </div>

        <!-- Activity History -->
        <div class="sidebar-card">
            <div class="sidebar-title">
                <i class="bi bi-clock-history"></i>
                Activity History
            </div>

            <?php if (!empty($ticket->history)): ?>
                <?php foreach (array_slice($ticket->history, 0, 5) as $history): ?>
                    <div class="history-item">
                        <div class="history-time"><?= date('d M Y, H:i', strtotime($history->created_at)) ?></div>
                        <div class="history-text"><?= esc($history->description) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted small">No activity history</p>
            <?php endif; ?>
        </div>

        <!-- Related Tickets -->
        <?php if (!empty($related_tickets)): ?>
            <div class="sidebar-card">
                <div class="sidebar-title">
                    <i class="bi bi-link-45deg"></i>
                    Related Tickets
                </div>

                <?php foreach ($related_tickets as $related): ?>
                    <a href="<?= base_url('admin/complaints/show/' . $related->id) ?>" class="related-ticket text-decoration-none">
                        <strong class="d-block">#<?= esc($related->ticket_number) ?></strong>
                        <small class="text-muted"><?= esc(substr($related->subject, 0, 50)) ?>...</small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Reporter Info -->
        <div class="sidebar-card">
            <div class="sidebar-title">
                <i class="bi bi-person-circle"></i>
                Reporter Info
            </div>

            <div class="detail-item mb-3">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= esc($ticket->user_name ?? 'Unknown') ?></span>
            </div>

            <div class="detail-item mb-3">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= esc($ticket->email) ?></span>
            </div>

            <?php if (!empty($ticket->phone)): ?>
                <div class="detail-item mb-3">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value"><?= esc($ticket->phone) ?></span>
                </div>
            <?php endif; ?>

            <a href="<?= base_url('admin/members/detail/' . $ticket->user_id) ?>" class="btn btn-sm btn-outline-primary w-100">
                <i class="bi bi-eye me-1"></i> View Profile
            </a>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Change Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('admin/complaints/update-status/' . $ticket->id) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="open" <?= $ticket->status === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="in_progress" <?= $ticket->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="resolved" <?= $ticket->status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            <option value="closed" <?= $ticket->status === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Add a note about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-1"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Assign Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('admin/complaints/assign/' . $ticket->id) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select" name="assignee_id" required>
                            <option value="">Select Staff...</option>
                            <?php if (isset($staff_list)): ?>
                                <?php foreach ($staff_list as $staff): ?>
                                    <option value="<?= $staff->id ?>" <?= $ticket->assigned_to == $staff->id ? 'selected' : '' ?>>
                                        <?= esc($staff->full_name ?? $staff->email) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Add instructions or context..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-1"></i> Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Priority Modal -->
<div class="modal fade" id="priorityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-flag me-2"></i>
                    Set Priority
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('admin/complaints/set-priority/' . $ticket->id) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" required>
                            <option value="low" <?= $ticket->priority === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $ticket->priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $ticket->priority === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="urgent" <?= $ticket->priority === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-1"></i> Update Priority
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/lightbox/lightbox.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/summernote/summernote-lite.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Initialize Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        // Initialize Summernote (optional for rich text)
        // $('#replyMessage').summernote({ height: 200 });

        // Close Ticket
        $('#closeTicketBtn').on('click', function() {
            Swal.fire({
                title: 'Close Ticket?',
                text: 'The ticket will be closed and no further replies will be accepted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Close It',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('admin/complaints/close/' . $ticket->id) ?>',
                        method: 'POST',
                        data: {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Closed!',
                                text: 'Ticket has been closed',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: xhr.responseJSON?.message || 'An error occurred'
                            });
                        }
                    });
                }
            });
        });

        // Export to PDF
        $('#exportPdfBtn').on('click', function() {
            window.location.href = '<?= base_url('admin/complaints/export-pdf/' . $ticket->id) ?>';
        });

        // Form submission with loading
        $('#replyForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Sending...');
        });

        // Auto-scroll to latest reply
        <?php if (!empty($replies)): ?>
            const lastReply = $('.timeline-item').last();
            if (lastReply.length) {
                $('html, body').animate({
                    scrollTop: lastReply.offset().top - 100
                }, 1000);
            }
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>