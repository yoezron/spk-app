<?php
$securitySettings = $settings['App\\Config\\Security'] ?? [];
?>

<form action="<?= base_url('super/settings/update/security') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="alert alert-warning">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Warning:</strong> Changes to security settings affect all users. Be careful!
    </div>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Password Policy</h6>

            <div class="mb-3">
                <label for="password_min_length" class="form-label">
                    Minimum Password Length <span class="text-danger">*</span>
                </label>
                <input type="number"
                    class="form-control"
                    id="password_min_length"
                    name="password_min_length"
                    value="<?= old('password_min_length', $securitySettings['password_min_length'] ?? 8) ?>"
                    min="6"
                    max="32"
                    required>
                <small class="text-muted">Recommended: 8-16 characters</small>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input"
                        type="checkbox"
                        id="password_require_uppercase"
                        name="password_require_uppercase"
                        value="1"
                        <?= ($securitySettings['password_require_uppercase'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="password_require_uppercase">
                        Require Uppercase Letter (A-Z)
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input"
                        type="checkbox"
                        id="password_require_number"
                        name="password_require_number"
                        value="1"
                        <?= ($securitySettings['password_require_number'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="password_require_number">
                        Require Number (0-9)
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input"
                        type="checkbox"
                        id="password_require_symbol"
                        name="password_require_symbol"
                        value="1"
                        <?= ($securitySettings['password_require_symbol'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="password_require_symbol">
                        Require Special Character (!@#$%^&*)
                    </label>
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Session Management</h6>

            <div class="mb-3">
                <label for="session_expiration" class="form-label">
                    Session Expiration (seconds) <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="session_expiration" name="session_expiration" required>
                    <option value="1800" <?= ($securitySettings['session_expiration'] ?? 7200) == 1800 ? 'selected' : '' ?>>30 minutes</option>
                    <option value="3600" <?= ($securitySettings['session_expiration'] ?? 7200) == 3600 ? 'selected' : '' ?>>1 hour</option>
                    <option value="7200" <?= ($securitySettings['session_expiration'] ?? 7200) == 7200 ? 'selected' : '' ?>>2 hours</option>
                    <option value="14400" <?= ($securitySettings['session_expiration'] ?? '') == 14400 ? 'selected' : '' ?>>4 hours</option>
                    <option value="28800" <?= ($securitySettings['session_expiration'] ?? '') == 28800 ? 'selected' : '' ?>>8 hours</option>
                    <option value="86400" <?= ($securitySettings['session_expiration'] ?? '') == 86400 ? 'selected' : '' ?>>24 hours</option>
                </select>
                <small class="text-muted">How long users stay logged in when inactive</small>
            </div>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Login Security</h6>

            <div class="mb-3">
                <label for="max_login_attempts" class="form-label">
                    Max Login Attempts <span class="text-danger">*</span>
                </label>
                <input type="number"
                    class="form-control"
                    id="max_login_attempts"
                    name="max_login_attempts"
                    value="<?= old('max_login_attempts', $securitySettings['max_login_attempts'] ?? 5) ?>"
                    min="1"
                    max="10"
                    required>
                <small class="text-muted">Number of failed login attempts before lockout</small>
            </div>

            <div class="mb-3">
                <label for="lockout_time" class="form-label">
                    Lockout Duration (seconds) <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="lockout_time" name="lockout_time" required>
                    <option value="300" <?= ($securitySettings['lockout_time'] ?? 900) == 300 ? 'selected' : '' ?>>5 minutes</option>
                    <option value="600" <?= ($securitySettings['lockout_time'] ?? 900) == 600 ? 'selected' : '' ?>>10 minutes</option>
                    <option value="900" <?= ($securitySettings['lockout_time'] ?? 900) == 900 ? 'selected' : '' ?>>15 minutes</option>
                    <option value="1800" <?= ($securitySettings['lockout_time'] ?? '') == 1800 ? 'selected' : '' ?>>30 minutes</option>
                    <option value="3600" <?= ($securitySettings['lockout_time'] ?? '') == 3600 ? 'selected' : '' ?>>1 hour</option>
                </select>
                <small class="text-muted">How long account is locked after max attempts</small>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Additional Security</h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="two_factor_enabled"
                        name="two_factor_enabled"
                        value="1"
                        <?= ($securitySettings['two_factor_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="two_factor_enabled">
                        <i class="fas fa-mobile-alt me-2"></i>Enable Two-Factor Authentication (2FA)
                    </label>
                </div>
                <small class="text-muted d-block ms-4">Require 2FA for enhanced security</small>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="force_https"
                        name="force_https"
                        value="1"
                        <?= ($securitySettings['force_https'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="force_https">
                        <i class="fas fa-lock me-2"></i>Force HTTPS
                    </label>
                </div>
                <small class="text-muted d-block ms-4">Redirect HTTP to HTTPS automatically</small>
            </div>

            <div class="alert alert-info mt-4">
                <small>
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Best Practices:</strong><br>
                    • Use strong password policies<br>
                    • Enable 2FA for admin accounts<br>
                    • Review login attempts regularly<br>
                    • Keep sessions reasonably short
                </small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger reset-default-btn" data-class="App\Config\Security">
            <i class="fas fa-undo me-2"></i>Reset to Default
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Security Settings
        </button>
    </div>
</form>