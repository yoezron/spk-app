<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>
                    System Settings
                </h5>
            </div>

            <div class="card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'general' ? 'active' : '' ?>"
                            id="general-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#general"
                            type="button"
                            role="tab">
                            <i class="fas fa-sliders-h me-2"></i>General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'email' ? 'active' : '' ?>"
                            id="email-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#email"
                            type="button"
                            role="tab">
                            <i class="fas fa-envelope me-2"></i>Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'whatsapp' ? 'active' : '' ?>"
                            id="whatsapp-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#whatsapp"
                            type="button"
                            role="tab">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'notification' ? 'active' : '' ?>"
                            id="notification-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#notification"
                            type="button"
                            role="tab">
                            <i class="fas fa-bell me-2"></i>Notification
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>"
                            id="security-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#security"
                            type="button"
                            role="tab">
                            <i class="fas fa-shield-alt me-2"></i>Security
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'advanced' ? 'active' : '' ?>"
                            id="advanced-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#advanced"
                            type="button"
                            role="tab">
                            <i class="fas fa-tools me-2"></i>Advanced
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content mt-4" id="settingsTabsContent">

                    <!-- General Settings -->
                    <div class="tab-pane fade <?= $activeTab === 'general' ? 'show active' : '' ?>"
                        id="general"
                        role="tabpanel">
                        <?= $this->include('super/settings/tabs/general') ?>
                    </div>

                    <!-- Email Settings -->
                    <div class="tab-pane fade <?= $activeTab === 'email' ? 'show active' : '' ?>"
                        id="email"
                        role="tabpanel">
                        <?= $this->include('super/settings/tabs/email') ?>
                    </div>

                    <!-- WhatsApp Settings -->
                    <div class="tab-pane fade <?= $activeTab === 'whatsapp' ? 'show active' : '' ?>"
                        id="whatsapp"
                        role="tabpanel">
                        <?= $this->include('super/settings/tabs/whatsapp') ?>
                    </div>

                    <!-- Notification Settings -->
                    <div class="tab-pane fade <?= $activeTab === 'notification' ? 'show active' : '' ?>"
                        id="notification"
                        role="tabpanel">
                        <?= $this->include('super/settings/tabs/notification') ?>
                    </div>

                    <!-- Security Settings -->
                    <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : '' ?>"
                        id="security"
                        role="tabpanel">
                        <?= $this->include('super/settings/tabs/security') ?>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="tab-pane fade <?= $activeTab === 'advanced' ? 'show active' : '' ?>"
                        id="advanced"
                        role="tabpanel">
                        <?= $this->include('super/settings/tabs/advanced') ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Auto-save draft indicator
        let saveTimeout;
        $('input, textarea, select').on('change', function() {
            clearTimeout(saveTimeout);
            $('.save-indicator').text('Unsaved changes...');

            saveTimeout = setTimeout(function() {
                $('.save-indicator').text('');
            }, 2000);
        });

        // Test Email Button
        $('#testEmailBtn').on('click', function() {
            const btn = $(this);
            const originalText = btn.html();
            const testEmail = $('#test_email').val();

            if (!testEmail) {
                Swal.fire('Error', 'Masukkan email untuk test terlebih dahulu', 'error');
                return;
            }

            btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...').prop('disabled', true);

            $.ajax({
                url: '<?= base_url('super/settings/test-email') ?>',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    test_email: testEmail
                }),
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal mengirim test email', 'error');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Clear Cache Button
        $('#clearCacheBtn').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Clear Cache?',
                text: 'Semua cache sistem akan dibersihkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Clear Cache',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= base_url('super/settings/clear-cache') ?>';
                }
            });
        });

        // Reset to Default
        $('.reset-default-btn').on('click', function(e) {
            e.preventDefault();
            const settingClass = $(this).data('class');

            Swal.fire({
                title: 'Reset to Default?',
                text: 'Semua pengaturan akan dikembalikan ke nilai default.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reset',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= base_url('super/settings/reset/') ?>' + settingClass;
                }
            });
        });

        // Toggle Password Visibility
        $('.toggle-password').on('click', function() {
            const input = $(this).closest('.input-group').find('input');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
<?= $this->endSection() ?>