<?php
$generalSettings = $settings['App\\Config\\General'] ?? [];
?>

<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Advanced Settings</strong> - These settings are for advanced users only. Be careful!
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-md-6">
        <h6 class="fw-bold mb-3">Cache Management</h6>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-database me-2"></i>Clear System Cache
                </h6>
                <p class="card-text text-muted">
                    Clear all cached data including views, routes, and config files.
                </p>
                <button type="button" class="btn btn-warning" id="clearCacheBtn">
                    <i class="fas fa-broom me-2"></i>Clear Cache Now
                </button>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-sync-alt me-2"></i>Rebuild Cache
                </h6>
                <p class="card-text text-muted">
                    Rebuild system cache for better performance.
                </p>
                <button type="button" class="btn btn-info" disabled>
                    <i class="fas fa-cog me-2"></i>Rebuild Cache
                    <small>(Coming Soon)</small>
                </button>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3">Backup & Restore</h6>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-download me-2"></i>Export Settings
                </h6>
                <p class="card-text text-muted">
                    Download all settings as JSON file for backup.
                </p>
                <a href="<?= base_url('super/settings/export') ?>" class="btn btn-success">
                    <i class="fas fa-file-export me-2"></i>Export Settings
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-upload me-2"></i>Import Settings
                </h6>
                <p class="card-text text-muted">
                    Restore settings from JSON backup file.
                </p>
                <form action="<?= base_url('super/settings/import') ?>" method="POST" enctype="multipart/form-data" id="importForm">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <input type="file"
                            class="form-control"
                            name="settings_file"
                            accept=".json"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-import me-2"></i>Import Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-md-6">
        <h6 class="fw-bold mb-3">System Information</h6>

        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="fw-bold" width="40%">PHP Version</td>
                        <td><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">CodeIgniter Version</td>
                        <td><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Environment</td>
                        <td>
                            <span class="badge bg-<?= ENVIRONMENT === 'production' ? 'success' : 'warning' ?>">
                                <?= strtoupper(ENVIRONMENT) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Base URL</td>
                        <td><code><?= base_url() ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Server Software</td>
                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Database</td>
                        <td>
                            <?php
                            $db = \Config\Database::connect();
                            echo $db->getPlatform();
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3">Developer Tools</h6>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-bug me-2"></i>Debug Mode
                </h6>
                <p class="card-text text-muted">
                    Current environment: <strong><?= ENVIRONMENT ?></strong>
                </p>
                <?php if (ENVIRONMENT === 'development'): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Debug mode is enabled. Disable in production!
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Production mode is active.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-shield-alt me-2"></i>Security Headers
                </h6>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="csrf_protection" checked disabled>
                    <label class="form-check-label" for="csrf_protection">
                        CSRF Protection
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="xss_protection" checked disabled>
                    <label class="form-check-label" for="xss_protection">
                        XSS Protection
                    </label>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3">Maintenance</h6>

        <div class="card border-danger mb-3">
            <div class="card-body">
                <h6 class="card-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                </h6>
                <p class="card-text text-muted">
                    Reset all settings to factory defaults. This action cannot be undone!
                </p>
                <button type="button" class="btn btn-danger" id="resetAllBtn">
                    <i class="fas fa-trash-restore me-2"></i>Reset All Settings
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Import form confirmation
        $('#importForm').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Import Settings?',
                text: 'This will overwrite current settings. Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Import',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Reset all settings
        $('#resetAllBtn').on('click', function() {
            Swal.fire({
                title: 'Reset All Settings?',
                html: '<p class="text-danger">This will reset ALL settings to factory defaults!</p><p>Type <strong>RESET</strong> to confirm:</p>',
                input: 'text',
                inputPlaceholder: 'Type RESET',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Reset Everything',
                cancelButtonText: 'Cancel',
                preConfirm: (value) => {
                    if (value !== 'RESET') {
                        Swal.showValidationMessage('Please type RESET to confirm');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Here you would call the reset endpoint
                    Swal.fire('Not Implemented', 'This feature is not yet implemented', 'info');
                }
            });
        });
    });
</script>