<?php

/**
 * Layout: Super Admin
 * Neptune Admin Template - Super Admin Dashboard Layout
 * 
 * Layout untuk Super Admin Dashboard
 * Full access ke semua modul termasuk system configuration
 * 
 * @package App\Views\Layouts
 * @author  SPK Development Team
 * @version 1.0.0
 */

// Get current user
$currentUser = auth()->user();
$session = session();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Super Admin Panel - Serikat Pekerja Kampus">
    <meta name="author" content="SPK Development Team">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">

    <!-- Title -->
    <title><?= esc($title ?? 'Super Admin Panel - SI SPK') ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/spk-icon.png') ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Neptune Admin CSS -->
    <link href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/plugins/perfectscroll/perfect-scrollbar.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/plugins/pace/pace.css') ?>" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="<?= base_url('assets/css/main.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">

    <!-- Additional CSS -->
    <?= $this->renderSection('styles') ?>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        /* Super Admin Badge */
        .super-admin-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 8px;
        }

        /* Custom Sidebar Styling */
        .app-sidebar {
            background: var(--primary-gradient);
        }

        .app-sidebar .logo {
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .app-sidebar .logo-text {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .app-menu .menu-item {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .app-menu .menu-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .app-menu .menu-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border-left: 3px solid white;
        }

        .app-menu .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Header Styling */
        .app-header {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Button Gradient */
        .btn-gradient-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f5576c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="app align-content-stretch d-flex flex-wrap">
        <!-- Sidebar -->
        <div class="app-sidebar">
            <div class="logo">
                <a href="<?= base_url('super/dashboard') ?>" class="logo-icon">
                    <span class="logo-text">SPK System</span>
                </a>
                <div class="sidebar-user-switcher user-activity-online">
                    <a href="<?= base_url('member/profile') ?>">
                        <?php if ($currentUser && !empty($currentUser->photo)): ?>
                            <img src="<?= base_url('uploads/photos/' . esc($currentUser->photo)) ?>" alt="User">
                        <?php else: ?>
                            <img src="<?= base_url('assets/images/avatars/avatar.png') ?>" alt="User">
                        <?php endif; ?>
                        <span class="activity-indicator"></span>
                        <span class="user-info-text">
                            <?= esc($currentUser->full_name ?? $currentUser->username ?? 'Super Admin') ?>
                            <span class="super-admin-badge">SUPER</span>
                            <br>
                            <span class="user-state-info"><?= esc($currentUser->email ?? '') ?></span>
                        </span>
                    </a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <div class="app-menu">
                <ul class="accordion-menu">
                    <!-- Dashboard -->
                    <li class="sidebar-title">Main</li>
                    <li class="<?= (uri_string() == 'super/dashboard') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/dashboard') ?>" class="menu-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- System Management -->
                    <li class="sidebar-title">System Management</li>

                    <!-- Roles -->
                    <li class="<?= (strpos(uri_string(), 'super/roles') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/roles') ?>" class="menu-item">
                            <i class="fas fa-users-cog"></i>
                            <span>Roles</span>
                        </a>
                    </li>

                    <!-- Permissions -->
                    <li class="<?= (strpos(uri_string(), 'super/permissions') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/permissions') ?>" class="menu-item">
                            <i class="fas fa-key"></i>
                            <span>Permissions</span>
                        </a>
                    </li>

                    <!-- Menu Management -->
                    <li class="<?= (strpos(uri_string(), 'super/menus') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/menus') ?>" class="menu-item">
                            <i class="fas fa-bars"></i>
                            <span>Menu Management</span>
                        </a>
                    </li>

                    <!-- Master Data -->
                    <li class="sidebar-title">Data Management</li>
                    <li class="<?= (strpos(uri_string(), 'super/master') !== false) ? 'active-page' : '' ?>">
                        <a href="#" class="menu-item">
                            <i class="fas fa-database"></i>
                            <span>Master Data</span>
                            <i class="fas fa-chevron-right dropdown-icon ms-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('super/master/provinces') ?>">
                                    <i class="fas fa-map-marked-alt"></i> Provinsi
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/regencies') ?>">
                                    <i class="fas fa-city"></i> Kabupaten/Kota
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/universities') ?>">
                                    <i class="fas fa-university"></i> Perguruan Tinggi
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/study-programs') ?>">
                                    <i class="fas fa-graduation-cap"></i> Program Studi
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/employment-status') ?>">
                                    <i class="fas fa-id-badge"></i> Status Kepegawaian
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/salary-ranges') ?>">
                                    <i class="fas fa-money-bill-wave"></i> Range Gaji
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- System Settings -->
                    <li class="sidebar-title">System Configuration</li>

                    <!-- Settings -->
                    <li class="<?= (strpos(uri_string(), 'super/settings') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/settings') ?>" class="menu-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>

                    <!-- Audit Logs -->
                    <li class="<?= (strpos(uri_string(), 'super/audit-logs') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/audit-logs') ?>" class="menu-item">
                            <i class="fas fa-history"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="app-container">
            <!-- Header -->
            <div class="app-header">
                <nav class="navbar navbar-light navbar-expand-lg">
                    <div class="container-fluid">
                        <div class="navbar-nav" id="navbarNav">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link hide-sidebar-toggle-button" href="#" id="sidebarToggle">
                                        <i class="material-icons">menu</i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Breadcrumb -->
                        <div class="d-flex align-items-center flex-grow-1">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="<?= base_url('super/dashboard') ?>">
                                            <i class="fas fa-home"></i>
                                        </a>
                                    </li>
                                    <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                            <?php if ($index < count($breadcrumbs) - 1): ?>
                                                <li class="breadcrumb-item">
                                                    <a href="<?= esc($crumb['url'] ?? '#') ?>">
                                                        <?= esc($crumb['title']) ?>
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <?= esc($crumb['title']) ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex">
                            <!-- Notifications -->
                            <div class="dropdown">
                                <button class="btn btn-link position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="material-icons">notifications</i>
                                    <span class="notification-badge">3</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                    <li>
                                        <h6 class="dropdown-header">Notifications</h6>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="fas fa-user-plus text-primary"></i>
                                            <span>5 new members registered</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="fas fa-exclamation-circle text-warning"></i>
                                            <span>System backup needed</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>Data import completed</span>
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-center" href="<?= base_url('super/notifications') ?>">
                                            View All
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- User Dropdown -->
                            <div class="dropdown ms-3">
                                <button class="btn btn-link dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if ($currentUser && !empty($currentUser->photo)): ?>
                                        <img src="<?= base_url('uploads/photos/' . esc($currentUser->photo)) ?>" alt="User" class="rounded-circle" width="32" height="32">
                                    <?php else: ?>
                                        <img src="<?= base_url('assets/images/avatars/avatar.png') ?>" alt="User" class="rounded-circle" width="32" height="32">
                                    <?php endif; ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li>
                                        <div class="dropdown-header">
                                            <strong><?= esc($currentUser->full_name ?? $currentUser->username ?? 'Super Admin') ?></strong>
                                            <br>
                                            <small class="text-muted"><?= esc($currentUser->email ?? '') ?></small>
                                        </div>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('member/profile') ?>">
                                            <i class="fas fa-user me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('member/change-password') ?>">
                                            <i class="fas fa-lock me-2"></i> Change Password
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <!-- Flash Messages -->
                        <?php if ($session->has('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= $session->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-times-circle me-2"></i>
                                <?= $session->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $session->getFlashdata('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('info')): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <?= $session->getFlashdata('info') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Page Content -->
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="app-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <span>&copy; <?= date('Y') ?> SPK (Serikat Pekerja Kampus). All rights reserved.</span>
                        </div>
                        <div class="col-md-6 text-end">
                            <span>Version 1.0.0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/plugins/jquery/jquery-3.5.1.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/perfectscroll/perfect-scrollbar.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/pace/pace.min.js') ?>"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- Main JS -->
    <script src="<?= base_url('assets/js/main.min.js') ?>"></script>

    <!-- Global Variables -->
    <script>
        const BASE_URL = '<?= base_url() ?>';
        const CSRF_TOKEN = '<?= csrf_hash() ?>';
        const CSRF_HEADER = '<?= csrf_header() ?>';
    </script>

    <!-- Custom Script -->
    <script>
        $(document).ready(function() {
            // Active menu highlighting
            const currentUrl = window.location.href;
            $('.app-menu a').each(function() {
                if (this.href === currentUrl) {
                    $(this).closest('li').addClass('active-page');
                    $(this).closest('.sub-menu').show();
                    $(this).closest('.sub-menu').prev('a').addClass('active');
                }
            });

            // Sidebar toggle
            $('#sidebarToggle').on('click', function(e) {
                e.preventDefault();
                $('.app').toggleClass('sidenav-toggled');
            });

            // Sub-menu toggle
            $('.app-menu .menu-item').on('click', function(e) {
                const $subMenu = $(this).next('.sub-menu');
                if ($subMenu.length) {
                    e.preventDefault();
                    $subMenu.slideToggle(300);
                    $(this).find('.dropdown-icon').toggleClass('fa-chevron-down fa-chevron-right');
                }
            });

            // Logout confirmation
            $('#logoutBtn').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Logout Confirmation',
                    text: 'Are you sure you want to logout?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Logout',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '<?= base_url('logout') ?>';
                    }
                });
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);


            // CSRF Token Update for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });
        });
    </script>

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>