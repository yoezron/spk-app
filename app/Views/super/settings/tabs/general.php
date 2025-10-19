<?php
$generalSettings = $settings['App\\Config\\General'] ?? [];
?>

<form action="<?= base_url('super/settings/update/general') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Application Information</h6>

            <div class="mb-3">
                <label for="app_name" class="form-label">Application Name <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control <?= isset($errors['app_name']) ? 'is-invalid' : '' ?>"
                    id="app_name"
                    name="app_name"
                    value="<?= old('app_name', $generalSettings['app_name'] ?? 'SPK System') ?>"
                    required>
                <?php if (isset($errors['app_name'])): ?>
                    <div class="invalid-feedback"><?= $errors['app_name'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="app_tagline" class="form-label">Tagline</label>
                <input type="text"
                    class="form-control"
                    id="app_tagline"
                    name="app_tagline"
                    value="<?= old('app_tagline', $generalSettings['app_tagline'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="app_description" class="form-label">Description</label>
                <textarea class="form-control"
                    id="app_description"
                    name="app_description"
                    rows="3"><?= old('app_description', $generalSettings['app_description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Logo</label>
                <div class="mb-2">
                    <?php if (!empty($generalSettings['logo_path'])): ?>
                        <img src="<?= base_url($generalSettings['logo_path']) ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                    <?php else: ?>
                        <p class="text-muted">No logo uploaded</p>
                    <?php endif; ?>
                </div>
                <input type="file"
                    class="form-control"
                    id="logo"
                    name="logo"
                    accept="image/png,image/jpeg,image/jpg">
                <small class="text-muted">Max size: 2MB. Format: JPG, PNG</small>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Contact Information</h6>

            <div class="mb-3">
                <label for="contact_email" class="form-label">Contact Email</label>
                <input type="email"
                    class="form-control"
                    id="contact_email"
                    name="contact_email"
                    value="<?= old('contact_email', $generalSettings['contact_email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="contact_phone" class="form-label">Contact Phone</label>
                <input type="text"
                    class="form-control"
                    id="contact_phone"
                    name="contact_phone"
                    value="<?= old('contact_phone', $generalSettings['contact_phone'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="contact_address" class="form-label">Address</label>
                <textarea class="form-control"
                    id="contact_address"
                    name="contact_address"
                    rows="3"><?= old('contact_address', $generalSettings['contact_address'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-6">
            <h6 class="fw-bold mb-3">Localization</h6>

            <div class="mb-3">
                <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                <select class="form-select" id="timezone" name="timezone" required>
                    <option value="Asia/Jakarta" <?= ($generalSettings['timezone'] ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta (WIB)</option>
                    <option value="Asia/Makassar" <?= ($generalSettings['timezone'] ?? '') === 'Asia/Makassar' ? 'selected' : '' ?>>Asia/Makassar (WITA)</option>
                    <option value="Asia/Jayapura" <?= ($generalSettings['timezone'] ?? '') === 'Asia/Jayapura' ? 'selected' : '' ?>>Asia/Jayapura (WIT)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="date_format" class="form-label">Date Format <span class="text-danger">*</span></label>
                <select class="form-select" id="date_format" name="date_format" required>
                    <option value="Y-m-d" <?= ($generalSettings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD (2025-01-15)</option>
                    <option value="d-m-Y" <?= ($generalSettings['date_format'] ?? '') === 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY (15-01-2025)</option>
                    <option value="d/m/Y" <?= ($generalSettings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY (15/01/2025)</option>
                    <option value="m/d/Y" <?= ($generalSettings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY (01/15/2025)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="time_format" class="form-label">Time Format <span class="text-danger">*</span></label>
                <select class="form-select" id="time_format" name="time_format" required>
                    <option value="H:i:s" <?= ($generalSettings['time_format'] ?? 'H:i:s') === 'H:i:s' ? 'selected' : '' ?>>24 Hour (14:30:00)</option>
                    <option value="h:i:s A" <?= ($generalSettings['time_format'] ?? '') === 'h:i:s A' ? 'selected' : '' ?>>12 Hour (02:30:00 PM)</option>
                </select>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Display Settings</h6>

            <div class="mb-3">
                <label for="items_per_page" class="form-label">Items Per Page <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control"
                    id="items_per_page"
                    name="items_per_page"
                    value="<?= old('items_per_page', $generalSettings['items_per_page'] ?? 10) ?>"
                    min="5"
                    max="100"
                    required>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">System Status</h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="maintenance_mode"
                        name="maintenance_mode"
                        value="1"
                        <?= ($generalSettings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="maintenance_mode">
                        <i class="fas fa-tools me-2"></i>Maintenance Mode
                    </label>
                </div>
                <small class="text-muted">When enabled, only Super Admin can access the system</small>
            </div>

            <div class="mb-3" id="maintenance_message_wrapper" style="display: <?= ($generalSettings['maintenance_mode'] ?? 0) ? 'block' : 'none' ?>;">
                <label for="maintenance_message" class="form-label">Maintenance Message</label>
                <textarea class="form-control"
                    id="maintenance_message"
                    name="maintenance_message"
                    rows="3"><?= old('maintenance_message', $generalSettings['maintenance_message'] ?? 'System is under maintenance. Please try again later.') ?></textarea>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        id="registration_enabled"
                        name="registration_enabled"
                        value="1"
                        <?= ($generalSettings['registration_enabled'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="registration_enabled">
                        <i class="fas fa-user-plus me-2"></i>Public Registration
                    </label>
                </div>
                <small class="text-muted">Allow new members to register publicly</small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <button type="button"
                class="btn btn-outline-danger reset-default-btn"
                data-class="App\Config\General">
                <i class="fas fa-undo me-2"></i>Reset to Default
            </button>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save Changes
            </button>
        </div>
    </div>
</form>

<script>
    $('#maintenance_mode').on('change', function() {
        if ($(this).is(':checked')) {
            $('#maintenance_message_wrapper').slideDown();
        } else {
            $('#maintenance_message_wrapper').slideUp();
        }
    });
</script>