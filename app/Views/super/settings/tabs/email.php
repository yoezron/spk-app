<?php
$emailSettings = $settings['App\\Config\\Email'] ?? [];
?>

<form action="<?= base_url('super/settings/update/email') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>SMTP Configuration</strong> - Configure email settings to enable email notifications.
    </div>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">SMTP Server Settings</h6>

            <div class="mb-3">
                <label for="smtp_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control"
                    id="smtp_host"
                    name="smtp_host"
                    value="<?= old('smtp_host', $emailSettings['smtp_host'] ?? '') ?>"
                    placeholder="smtp.gmail.com"
                    required>
            </div>

            <div class="mb-3">
                <label for="smtp_user" class="form-label">SMTP Username <span class="text-danger">*</span></label>
                <input type="email"
                    class="form-control"
                    id="smtp_user"
                    name="smtp_user"
                    value="<?= old('smtp_user', $emailSettings['smtp_user'] ?? '') ?>"
                    placeholder="your-email@gmail.com"
                    required>
            </div>

            <div class="mb-3">
                <label for="smtp_pass" class="form-label">SMTP Password</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control"
                        id="smtp_pass"
                        name="smtp_pass"
                        placeholder="Leave empty to keep current password">
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="text-muted">Leave empty if you don't want to change the password</small>
            </div>

            <div class="mb-3">
                <label for="smtp_port" class="form-label">SMTP Port <span class="text-danger">*</span></label>
                <select class="form-select" id="smtp_port" name="smtp_port" required>
                    <option value="587" <?= ($emailSettings['smtp_port'] ?? 587) == 587 ? 'selected' : '' ?>>587 (TLS - Recommended)</option>
                    <option value="465" <?= ($emailSettings['smtp_port'] ?? '') == 465 ? 'selected' : '' ?>>465 (SSL)</option>
                    <option value="25" <?= ($emailSettings['smtp_port'] ?? '') == 25 ? 'selected' : '' ?>>25 (Standard)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="smtp_crypto" class="form-label">Encryption <span class="text-danger">*</span></label>
                <select class="form-select" id="smtp_crypto" name="smtp_crypto" required>
                    <option value="tls" <?= ($emailSettings['smtp_crypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= ($emailSettings['smtp_crypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= ($emailSettings['smtp_crypto'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Email Identity</h6>

            <div class="mb-3">
                <label for="from_email" class="form-label">From Email <span class="text-danger">*</span></label>
                <input type="email"
                    class="form-control"
                    id="from_email"
                    name="from_email"
                    value="<?= old('from_email', $emailSettings['from_email'] ?? '') ?>"
                    placeholder="noreply@spk.com"
                    required>
            </div>

            <div class="mb-3">
                <label for="from_name" class="form-label">From Name <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control"
                    id="from_name"
                    name="from_name"
                    value="<?= old('from_name', $emailSettings['from_name'] ?? 'SPK System') ?>"
                    required>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="email_enabled"
                        name="email_enabled"
                        value="1"
                        <?= ($emailSettings['email_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="email_enabled">
                        <i class="fas fa-power-off me-2"></i>Enable Email Service
                    </label>
                </div>
                <small class="text-muted">Enable/disable email notifications system-wide</small>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Test Email Configuration</h6>

            <div class="mb-3">
                <label for="test_email" class="form-label">Test Email Address</label>
                <input type="email"
                    class="form-control"
                    id="test_email"
                    placeholder="Enter email to receive test message">
            </div>

            <button type="button" class="btn btn-outline-primary w-100" id="testEmailBtn">
                <i class="fas fa-paper-plane me-2"></i>Send Test Email
            </button>

            <div class="alert alert-warning mt-3">
                <small>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Gmail Users:</strong> Enable "Less secure app access" or use App Password
                </small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger reset-default-btn" data-class="App\Config\Email">
            <i class="fas fa-undo me-2"></i>Reset to Default
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Email Settings
        </button>
    </div>
</form>