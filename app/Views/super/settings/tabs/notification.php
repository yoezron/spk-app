<?php
$notifSettings = $settings['App\\Config\\Notification'] ?? [];
?>

<form action="<?= base_url('super/settings/update/notification') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Notification Channels</h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="email_notifications"
                        name="email_notifications"
                        value="1"
                        <?= ($notifSettings['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="email_notifications">
                        <i class="fas fa-envelope me-2"></i>Email Notifications
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="wa_notifications"
                        name="wa_notifications"
                        value="1"
                        <?= ($notifSettings['wa_notifications'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="wa_notifications">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp Notifications
                    </label>
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Event Notifications</h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="notify_new_member"
                        name="notify_new_member"
                        value="1"
                        <?= ($notifSettings['notify_new_member'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="notify_new_member">
                        <i class="fas fa-user-plus me-2"></i>New Member Registration
                    </label>
                </div>
                <small class="text-muted d-block ms-4">Notify admins when new member registers</small>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="notify_new_complaint"
                        name="notify_new_complaint"
                        value="1"
                        <?= ($notifSettings['notify_new_complaint'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="notify_new_complaint">
                        <i class="fas fa-exclamation-circle me-2"></i>New Complaint/Ticket
                    </label>
                </div>
                <small class="text-muted d-block ms-4">Notify when new complaint is submitted</small>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="notify_new_survey"
                        name="notify_new_survey"
                        value="1"
                        <?= ($notifSettings['notify_new_survey'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="notify_new_survey">
                        <i class="fas fa-poll me-2"></i>New Survey Published
                    </label>
                </div>
                <small class="text-muted d-block ms-4">Notify members when new survey is published</small>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="notify_new_forum"
                        name="notify_new_forum"
                        value="1"
                        <?= ($notifSettings['notify_new_forum'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="notify_new_forum">
                        <i class="fas fa-comments me-2"></i>New Forum Thread
                    </label>
                </div>
                <small class="text-muted d-block ms-4">Notify when new forum thread is created</small>
            </div>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Admin Recipients</h6>

            <div class="mb-3">
                <label for="admin_email_list" class="form-label">Admin Email List</label>
                <textarea class="form-control"
                    id="admin_email_list"
                    name="admin_email_list"
                    rows="5"
                    placeholder="admin1@spk.com&#10;admin2@spk.com&#10;admin3@spk.com"><?= old('admin_email_list', $notifSettings['admin_email_list'] ?? '') ?></textarea>
                <small class="text-muted">One email per line. These admins will receive system notifications.</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <small>
                    <strong>Notification Flow:</strong><br>
                    1. Event occurs (e.g., new member)<br>
                    2. System checks if notification is enabled<br>
                    3. Sends via enabled channels (Email/WhatsApp)<br>
                    4. Logs notification in database
                </small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger reset-default-btn" data-class="App\Config\Notification">
            <i class="fas fa-undo me-2"></i>Reset to Default
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Notification Settings
        </button>
    </div>
</form>