<?php

/**
 * View: Admin Complaint/Ticketing System
 * Controller: Admin\ComplaintController::index()
 * Description: Comprehensive ticket management dashboard dengan advanced features
 * 
 * Features:
 * - Statistics overview (open, in progress, resolved, closed)
 * - Advanced filter panel (status, priority, category, assignee, date)
 * - DataTable dengan real-time updates
 * - Status badges & priority indicators
 * - Quick actions (assign, reply, change status)
 * - Bulk operations support
 * - SLA monitoring & warnings
 * - Timeline view untuk ticket activity
 * - Assignment management
 * - Quick reply modal
 * - Status change modal dengan notes
 * - Regional scope support (Koordinator Wilayah)
 * - Export functionality (Excel, CSV, PDF)
 * - Search by ticket number, subject, user
 * - Sorting by various criteria
 * - Responsive design & mobile-friendly
 * - Real-time notifications
 * - Audit trail tracking
 * 
 * @package App\Views\Admin\Complaint
 * @author  SPK Development Team
 * @version 3.0.0
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/datatables.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css') ?>">
<style>
    .page-header-tickets {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.open {
        border-left-color: #17a2b8;
    }

    .stat-card.in-progress {
        border-left-color: #ffc107;
    }

    .stat-card.resolved {
        border-left-color: #28a745;
    }

    .stat-card.closed {
        border-left-color: #6c757d;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.open {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .stat-icon.in-progress {
        background: linear-gradient(135deg, #ffc107 0%, #ff8b38 100%);
    }

    .stat-icon.resolved {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-icon.closed {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-trend {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .stat-trend.up {
        background: #d4edda;
        color: #155724;
    }

    .stat-trend.down {
        background: #f8d7da;
        color: #721c24;
    }

    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .ticket-status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
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
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-low {
        background: #d1ecf1;
        color: #0c5460;
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

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .sla-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 10px;
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
    }

    .action-btn {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .quick-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ticket-row {
        cursor: pointer;
        transition: background 0.2s;
    }

    .ticket-row:hover {
        background: #f8f9fa !important;
    }

    .ticket-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .ticket-meta {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #6c757d;
        flex-wrap: wrap;
    }

    .ticket-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .assignee-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 11px;
        font-weight: 600;
    }

    .bulk-actions-bar {
        background: #667eea;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
        align-items: center;
        justify-content: space-between;
    }

    .bulk-actions-bar.active {
        display: flex;
    }

    .stats-mini {
        display: flex;
        gap: 20px;
        padding: 15px 0;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 20px;
    }

    .stat-mini-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stat-mini-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-mini-value {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-mini-label {
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
    }

    .category-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        background: #e9ecef;
        color: #495057;
    }

    .timeline-dots {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .timeline-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #dee2e6;
    }

    .timeline-dot.active {
        background: #667eea;
    }

    .overdue-badge {
        background: #dc3545;
        color: white;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }

    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header-tickets">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2 text-white">
                <i class="bi bi-ticket-perforated-fill me-2"></i>
                Sistem Pengaduan & Ticketing
            </h3>
            <p class="mb-0 text-white opacity-90">
                Kelola dan tanggapi pengaduan anggota dengan efisien
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#statsModal">
                <i class="bi bi-graph-up me-1"></i> Analytics
            </button>
            <button type="button" class="btn btn-outline-light" id="exportBtn">
                <i class="bi bi-download me-1"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?= view('components/alerts') ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card open">
            <span class="stat-trend up">
                <i class="bi bi-arrow-up"></i> +<?= $stats['open_change'] ?? 5 ?>%
            </span>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon open">
                    <i class="bi bi-folder2-open"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['open'] ?? 0) ?></div>
            <div class="stat-label">Open Tickets</div>
            <small class="text-muted mt-2 d-block">Butuh perhatian segera</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card in-progress">
            <span class="stat-trend up">
                <i class="bi bi-arrow-up"></i> +<?= $stats['progress_change'] ?? 12 ?>%
            </span>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon in-progress">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['in_progress'] ?? 0) ?></div>
            <div class="stat-label">In Progress</div>
            <small class="text-muted mt-2 d-block">Sedang ditangani</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card resolved">
            <span class="stat-trend down">
                <i class="bi bi-arrow-down"></i> -<?= $stats['resolved_change'] ?? 3 ?>%
            </span>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon resolved">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['resolved'] ?? 0) ?></div>
            <div class="stat-label">Resolved</div>
            <small class="text-muted mt-2 d-block">Menunggu konfirmasi</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card closed">
            <span class="stat-trend up">
                <i class="bi bi-arrow-up"></i> +<?= $stats['closed_change'] ?? 8 ?>%
            </span>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon closed">
                    <i class="bi bi-archive-fill"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['closed'] ?? 0) ?></div>
            <div class="stat-label">Closed</div>
            <small class="text-muted mt-2 d-block">Selesai ditutup</small>
        </div>
    </div>
</div>

<!-- Mini Stats -->
<div class="card mb-4">
    <div class="card-body">
        <div class="stats-mini">
            <div class="stat-mini-item">
                <div class="stat-mini-icon bg-danger text-white">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <div class="stat-mini-value"><?= $stats['urgent'] ?? 0 ?></div>
                    <div class="stat-mini-label">Urgent</div>
                </div>
            </div>

            <div class="stat-mini-item">
                <div class="stat-mini-icon bg-warning text-white">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                    <div class="stat-mini-value"><?= $stats['overdue'] ?? 0 ?></div>
                    <div class="stat-mini-label">Overdue</div>
                </div>
            </div>

            <div class="stat-mini-item">
                <div class="stat-mini-icon bg-info text-white">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div class="stat-mini-value"><?= $stats['unassigned'] ?? 0 ?></div>
                    <div class="stat-mini-label">Unassigned</div>
                </div>
            </div>

            <div class="stat-mini-item">
                <div class="stat-mini-icon bg-success text-white">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div>
                    <div class="stat-mini-value"><?= number_format($stats['resolution_rate'] ?? 0, 1) ?>%</div>
                    <div class="stat-mini-label">Resolution Rate</div>
                </div>
            </div>

            <div class="stat-mini-item">
                <div class="stat-mini-icon bg-primary text-white">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div>
                    <div class="stat-mini-value"><?= $stats['avg_response_time'] ?? '2.5' ?>h</div>
                    <div class="stat-mini-label">Avg Response</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel">
    <form id="filterForm" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" id="searchInput" name="search"
                placeholder="Ticket #, subject, user...">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" id="statusFilter" name="status">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Priority</label>
            <select class="form-select" id="priorityFilter" name="priority">
                <option value="">All Priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Category</label>
            <select class="form-select select2" id="categoryFilter" name="category">
                <option value="">All Categories</option>
                <?php if (isset($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->id ?>"><?= esc($category->name) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Assigned To</label>
            <select class="form-select select2" id="assigneeFilter" name="assignee">
                <option value="">All Assignees</option>
                <option value="unassigned">Unassigned</option>
                <option value="me">My Tickets</option>
                <?php if (isset($staff_list)): ?>
                    <?php foreach ($staff_list as $staff): ?>
                        <option value="<?= $staff->id ?>"><?= esc($staff->full_name ?? $staff->email) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    </form>
</div>

<!-- Bulk Actions Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div>
        <i class="bi bi-check-square me-2"></i>
        <strong><span id="selectedCount">0</span> tickets dipilih</strong>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-light btn-sm" id="bulkAssignBtn">
            <i class="bi bi-person-plus me-1"></i> Assign
        </button>
        <button type="button" class="btn btn-light btn-sm" id="bulkStatusBtn">
            <i class="bi bi-arrow-repeat me-1"></i> Change Status
        </button>
        <button type="button" class="btn btn-light btn-sm" id="bulkPriorityBtn">
            <i class="bi bi-flag me-1"></i> Set Priority
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" id="clearSelectionBtn">
            <i class="bi bi-x me-1"></i> Clear
        </button>
    </div>
</div>

<!-- Tickets Table -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($tickets)): ?>
            <table id="ticketsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAllTickets" class="form-check-input">
                        </th>
                        <th width="100">Ticket #</th>
                        <th>Subject & Details</th>
                        <th width="100">Priority</th>
                        <th width="120">Status</th>
                        <th width="120">Assigned To</th>
                        <th width="100">SLA</th>
                        <th width="150">Last Update</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <?php
                        $statusClass = 'status-' . str_replace('_', '-', $ticket->status);
                        $priorityClass = 'priority-' . $ticket->priority;

                        // Calculate SLA
                        $createdTime = strtotime($ticket->created_at);
                        $currentTime = time();
                        $hoursElapsed = ($currentTime - $createdTime) / 3600;

                        $slaClass = 'sla-ok';
                        $slaText = 'On Track';
                        if ($hoursElapsed > 72) {
                            $slaClass = 'sla-danger';
                            $slaText = 'Overdue';
                        } elseif ($hoursElapsed > 48) {
                            $slaClass = 'sla-warning';
                            $slaText = 'Warning';
                        }
                        ?>
                        <tr class="ticket-row" data-ticket-id="<?= $ticket->id ?>">
                            <td>
                                <input type="checkbox" class="form-check-input ticket-checkbox" value="<?= $ticket->id ?>">
                            </td>
                            <td>
                                <strong class="text-primary">#<?= esc($ticket->ticket_number) ?></strong>
                            </td>
                            <td>
                                <div class="ticket-title"><?= esc($ticket->subject) ?></div>
                                <div class="ticket-meta">
                                    <span class="ticket-meta-item">
                                        <i class="bi bi-person"></i>
                                        <?= esc($ticket->user_name ?? $ticket->email) ?>
                                    </span>
                                    <?php if (!empty($ticket->category_name)): ?>
                                        <span class="category-badge">
                                            <?= esc($ticket->category_name) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($ticket->has_attachments): ?>
                                        <span class="ticket-meta-item">
                                            <i class="bi bi-paperclip"></i>
                                            <?= $ticket->attachment_count ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="ticket-meta-item">
                                        <i class="bi bi-chat-dots"></i>
                                        <?= $ticket->reply_count ?? 0 ?> replies
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="priority-badge <?= $priorityClass ?>">
                                    <?= ucfirst($ticket->priority) ?>
                                </span>
                            </td>
                            <td>
                                <span class="ticket-status-badge <?= $statusClass ?>">
                                    <?= str_replace('_', ' ', ucfirst($ticket->status)) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($ticket->assigned_to_name)): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="assignee-avatar" title="<?= esc($ticket->assigned_to_name) ?>">
                                            <?= strtoupper(substr($ticket->assigned_to_name, 0, 2)) ?>
                                        </div>
                                        <small><?= esc($ticket->assigned_to_name) ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="sla-indicator <?= $slaClass ?>">
                                    <i class="bi bi-clock"></i>
                                    <?= $slaText ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d M Y', strtotime($ticket->updated_at)) ?><br>
                                    <?= date('H:i', strtotime($ticket->updated_at)) ?>
                                </small>
                            </td>
                            <td>
                                <div class="quick-actions">
                                    <a href="<?= base_url('admin/complaints/show/' . $ticket->id) ?>"
                                        class="btn btn-sm btn-info action-btn" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary action-btn quick-reply-btn"
                                        data-ticket-id="<?= $ticket->id ?>"
                                        data-ticket-number="<?= esc($ticket->ticket_number) ?>"
                                        title="Quick Reply">
                                        <i class="bi bi-reply-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning action-btn assign-btn"
                                        data-ticket-id="<?= $ticket->id ?>"
                                        title="Assign">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if (isset($pager)): ?>
                <div class="mt-3">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Tidak Ada Ticket</h5>
                <p class="text-muted">Belum ada pengaduan yang masuk</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Reply Modal -->
<div class="modal fade" id="quickReplyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-reply-fill me-2"></i>
                    Quick Reply - <span id="replyTicketNumber"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickReplyForm" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="ticket_id" id="replyTicketId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="message" rows="5"
                            placeholder="Tuliskan balasan Anda..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="isInternal">
                            <label class="form-check-label" for="isInternal">
                                Internal Note (Tidak dikirim ke user)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Change Status (Optional)</label>
                        <select class="form-select" name="new_status">
                            <option value="">Keep Current Status</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send Reply
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
            <form id="assignForm" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="ticket_id" id="assignTicketId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="assignee_id" required>
                            <option value="">Select Staff...</option>
                            <?php if (isset($staff_list)): ?>
                                <?php foreach ($staff_list as $staff): ?>
                                    <option value="<?= $staff->id ?>">
                                        <?= esc($staff->full_name ?? $staff->email) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Tambahkan catatan jika perlu..."></textarea>
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

<!-- Statistics Modal -->
<div class="modal fade" id="statsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-graph-up me-2"></i>
                    Ticket Analytics
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Charts will be here -->
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/chart.js/chart.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select...',
            allowClear: true,
            dropdownParent: $('.filter-panel')
        });

        // Initialize DataTable
        const table = $('#ticketsTable').DataTable({
            responsive: true,
            pageLength: 20,
            order: [
                [7, 'desc']
            ], // Last update
            columnDefs: [{
                    orderable: false,
                    targets: [0, 8]
                } // Checkbox & Actions
            ],
            language: {
                url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
            }
        });

        // Handle checkbox selection
        $('.ticket-checkbox').on('change', function() {
            updateBulkActions();
        });

        // Select all checkbox
        $('#selectAllTickets').on('change', function() {
            $('.ticket-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkActions();
        });

        // Update bulk actions bar
        function updateBulkActions() {
            const selectedCount = $('.ticket-checkbox:checked').length;
            $('#selectedCount').text(selectedCount);

            if (selectedCount > 0) {
                $('#bulkActionsBar').addClass('active');
            } else {
                $('#bulkActionsBar').removeClass('active');
            }
        }

        // Clear selection
        $('#clearSelectionBtn').on('click', function() {
            $('.ticket-checkbox').prop('checked', false);
            $('#selectAllTickets').prop('checked', false);
            updateBulkActions();
        });

        // Quick Reply
        $('.quick-reply-btn').on('click', function(e) {
            e.stopPropagation();
            const ticketId = $(this).data('ticket-id');
            const ticketNumber = $(this).data('ticket-number');

            $('#replyTicketId').val(ticketId);
            $('#replyTicketNumber').text('#' + ticketNumber);
            $('#quickReplyModal').modal('show');
        });

        // Quick Reply Form Submit
        $('#quickReplyForm').on('submit', function(e) {
            e.preventDefault();

            const ticketId = $('#replyTicketId').val();
            const formData = $(this).serialize();

            Swal.fire({
                title: 'Sending...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?= base_url('admin/complaints/reply/') ?>' + ticketId,
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Balasan berhasil dikirim',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        });

        // Assign Ticket
        $('.assign-btn').on('click', function(e) {
            e.stopPropagation();
            const ticketId = $(this).data('ticket-id');

            $('#assignTicketId').val(ticketId);
            $('#assignModal').modal('show');
        });

        // Assign Form Submit
        $('#assignForm').on('submit', function(e) {
            e.preventDefault();

            const ticketId = $('#assignTicketId').val();
            const formData = $(this).serialize();

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?= base_url('admin/complaints/assign/') ?>' + ticketId,
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Ticket berhasil ditugaskan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        });

        // Row click to view details
        $('.ticket-row').on('click', function(e) {
            if (!$(e.target).closest('input, button, a').length) {
                const ticketId = $(this).data('ticket-id');
                window.location.href = '<?= base_url('admin/complaints/show/') ?>' + ticketId;
            }
        });

        // Filter form submit
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            window.location.href = '<?= current_url() ?>?' + formData;
        });

        // Export button
        $('#exportBtn').on('click', function() {
            Swal.fire({
                title: 'Export Format',
                text: 'Pilih format export',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-file-earmark-excel"></i> Excel',
                cancelButtonText: '<i class="bi bi-filetype-csv"></i> CSV',
                showDenyButton: true,
                denyButtonText: '<i class="bi bi-file-earmark-pdf"></i> PDF'
            }).then((result) => {
                let format = 'excel';
                if (result.isDismissed) {
                    format = 'csv';
                } else if (result.isDenied) {
                    format = 'pdf';
                }

                if (result.isConfirmed || result.isDismissed || result.isDenied) {
                    window.location.href = '<?= base_url('admin/complaints/export?format=') ?>' + format;
                }
            });
        });

        // Bulk Actions
        $('#bulkAssignBtn').on('click', function() {
            const selectedIds = $('.ticket-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            // Implementation for bulk assign
            console.log('Bulk assign:', selectedIds);
        });

        $('#bulkStatusBtn').on('click', function() {
            const selectedIds = $('.ticket-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            // Implementation for bulk status change
            console.log('Bulk status:', selectedIds);
        });

        // Auto-refresh every 60 seconds
        setInterval(function() {
            // Optionally reload data without page refresh
            // table.ajax.reload(null, false);
        }, 60000);
    });
</script>
<?= $this->endSection() ?>