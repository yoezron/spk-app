<?php
$waSettings = $settings['App\\Config\\WhatsApp'] ?? [];
?>

<form action="<?= base_url('super/settings/update/whatsapp') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="alert alert-info">
        <i class="fab fa-whatsapp me-2"></i>
        <strong>WhatsApp Integration</strong> - Configure WhatsApp API for sending notifications and messages.
    </div>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">API Configuration</h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="wa_enabled"
                        name="wa_enabled"
                        value="1"
                        <?= ($waSettings['wa_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="wa_enabled">
                        <i class="fas fa-power-off me-2"></i>Enable WhatsApp Integration
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label for="wa_api_url" class="form-label">API URL</label>
                <input type="url"
                    class="form-control"
                    id="wa_api_url"
                    name="wa_api_url"
                    value="<?= old('wa_api_url', $waSettings['wa_api_url'] ?? '') ?>"
                    placeholder="https://api.whatsapp.com/v1">
                <small class="text-muted">WhatsApp Business API endpoint</small>
            </div>

            <div class="mb-3">
                <label for="wa_api_key" class="form-label">API Key</label>
                <input type="text"
                    class="form-control"
                    id="wa_api_key"
                    name="wa_api_key"
                    value="<?= old('wa_api_key', $waSettings['wa_api_key'] ?? '') ?>"
                    placeholder="Your API Key">
            </div>

            <div class="mb-3">
                <label for="wa_api_secret" class="form-label">API Secret</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control"
                        id="wa_api_secret"
                        name="wa_api_secret"
                        value="<?= old('wa_api_secret', $waSettings['wa_api_secret'] ?? '') ?>"
                        placeholder="Your API Secret">
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Sender Information</h6>

            <div class="mb-3">
                <label for="wa_sender_number" class="form-label">Sender Number</label>
                <input type="text"
                    class="form-control"
                    id="wa_sender_number"
                    name="wa_sender_number"
                    value="<?= old('wa_sender_number', $waSettings['wa_sender_number'] ?? '') ?>"
                    placeholder="628123456789">
                <small class="text-muted">Format: Country code + number (without +)</small>
            </div>

            <div class="mb-3">
                <label for="wa_sender_name" class="form-label">Sender Name</label>
                <input type="text"
                    class="form-control"
                    id="wa_sender_name"
                    name="wa_sender_name"
                    value="<?= old('wa_sender_name', $waSettings['wa_sender_name'] ?? 'SPK System') ?>">
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Notification Settings</h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="wa_notification_enabled"
                        name="wa_notification_enabled"
                        value="1"
                        <?= ($waSettings['wa_notification_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="wa_notification_enabled">
                        <i class="fas fa-bell me-2"></i>Enable WhatsApp Notifications
                    </label>
                </div>
                <small class="text-muted">Send notifications via WhatsApp</small>
            </div>

            <div class="alert alert-warning mt-3">
                <small>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> WhatsApp Business API atau third-party service seperti Fonnte, Wablas, dll.
                </small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger reset-default-btn" data-class="App\Config\WhatsApp">
            <i class="fas fa-undo me-2"></i>Reset to Default
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save WhatsApp Settings
        </button>
    </div>
</form>