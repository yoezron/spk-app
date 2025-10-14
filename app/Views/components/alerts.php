<?php

/**
 * Component: Alerts
 * Reusable alert component untuk flash messages
 * 
 * Menampilkan flash messages dari session:
 * - success: Alert hijau untuk operasi berhasil
 * - error: Alert merah untuk error
 * - warning: Alert kuning untuk warning
 * - info: Alert biru untuk informasi
 * - errors: Array of validation errors (merah)
 * 
 * Features:
 * - Auto-hide after 5 seconds (optional)
 * - Dismissible dengan close button
 * - Icon untuk setiap tipe alert
 * - Responsive design
 * 
 * @package App\Views\Components
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="material-icons-outlined me-2">check_circle</i>
            <div class="flex-grow-1">
                <?= session('success') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="material-icons-outlined me-2">error</i>
            <div class="flex-grow-1">
                <?= session('error') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="material-icons-outlined me-2">warning</i>
            <div class="flex-grow-1">
                <?= session('warning') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('info')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="material-icons-outlined me-2">info</i>
            <div class="flex-grow-1">
                <?= session('info') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('errors') && is_array(session('errors'))): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="material-icons-outlined me-2">error</i>
            <div class="flex-grow-1">
                <strong>Terdapat kesalahan pada form:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session('errors') as $field => $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<style>
    /* Custom alert styling */
    .alert {
        border-radius: 8px;
        border: none;
        padding: 16px;
        margin-bottom: 20px;
        animation: slideDown 0.3s ease-in-out;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert .material-icons-outlined {
        font-size: 24px;
        margin-top: 2px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }

    .alert ul {
        padding-left: 20px;
    }

    .alert .btn-close {
        padding: 0.75rem;
    }
</style>

<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>