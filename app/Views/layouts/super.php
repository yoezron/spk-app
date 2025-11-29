<?php

/**
 * Layout: Super Admin
 * Neptune Admin Template - Super Admin Dashboard Layout
 * 
 * Layout untuk Super Admin Dashboard dengan full access ke semua modul
 * Includes: Dynamic menu, RBAC integration, responsive design, Neptune components
 * 
 * @package App\Views\Layouts
 * @author  SPK Development Team
 * @version 2.0.0 - Complete Rewrite
 */

// Get current user and session
$currentUser = auth()->user();
$session = session();
$hasMemberPortalAccess = $currentUser->inGroup('anggota') || $currentUser->inGroup('Anggota') ||
    $currentUser->inGroup('calon_anggota') || $currentUser->inGroup('Calon Anggota');
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
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

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
            margin-left: 5px;
            display: inline-block;
        }

        /* Sidebar Styling (konsisten dengan Neptune) */
        .app-sidebar {
            background: linear-gradient(180deg, #ffffffff 0%, #ffffffff 100%);
        }

        .app-sidebar .logo {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .app-sidebar .logo .logo-text {
            color: white;
            font-weight: 700;
        }

        /* Menu Styling */
        .accordion-menu>ul>li.active-page>a {
            background: rgba(102, 126, 234, 0.2);
            color: white !important;
            border-left: 3px solid var(--primary-color);
        }

        .accordion-menu>ul>li>a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        /* Sub-menu styling */
        .accordion-menu>ul>li ul {
            background: rgba(0, 0, 0, 0.2);
        }

        .accordion-menu>ul>li ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Header Styling */
        .app-header {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            animation: slideDown 0.3s ease-in-out;
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

        /* Button Gradient */
        .btn-gradient-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
            min-width: 18px;
            text-align: center;
        }

        /* User info in header */
        .user-dropdown-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Quick Actions Dropdown */
        .quick-actions-dropdown {
            min-width: 250px;
        }

        .quick-actions-dropdown .dropdown-item {
            padding: 12px 20px;
            transition: all 0.2s ease;
        }

        .quick-actions-dropdown .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(5px);
        }

        .quick-actions-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-description h1 {
                font-size: 24px;
            }

            .breadcrumb {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="app align-content-stretch d-flex flex-wrap">
        <!-- Sidebar -->
        <div class="app-sidebar">
            <div class="logo">
                <a href="<?= base_url('super/dashboard') ?>" class="logo-icon">
                    <?php $logo = app_logo(); ?>
                    <?php if ($logo): ?>
                        <img src="<?= $logo ?>" alt="<?= app_name() ?>" style="max-height: 40px; max-width: 150px;">
                    <?php else: ?>
                        <span class="logo-text"><?= app_name() ?> System</span>
                    <?php endif; ?>
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
                        <a href="<?= base_url('super/dashboard') ?>">
                            <i class="material-icons-two-tone">dashboard</i>
                            Dashboard
                        </a>
                    </li>

                    <!-- System Management -->
                    <li class="sidebar-title">System Management</li>

                    <!-- User Management -->
                    <li class="<?= (strpos(uri_string(), 'super/users') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/users') ?>">
                            <i class="material-icons-two-tone">people</i>
                            User Management
                        </a>
                    </li>

                    <!-- Roles -->
                    <li class="<?= (strpos(uri_string(), 'super/roles') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/roles') ?>">
                            <i class="material-icons-two-tone">admin_panel_settings</i>
                            Roles
                        </a>
                    </li>

                    <!-- Permissions -->
                    <li class="<?= (strpos(uri_string(), 'super/permissions') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/permissions') ?>">
                            <i class="material-icons-two-tone">verified_user</i>
                            Permissions
                        </a>
                    </li>

                    <!-- Menu Management -->
                    <li class="<?= (strpos(uri_string(), 'super/menus') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/menus') ?>">
                            <i class="material-icons-two-tone">menu</i>
                            Menu Management
                        </a>
                    </li>

                    <!-- Master Data -->
                    <li class="sidebar-title">Data Management</li>
                    <li class="<?= (strpos(uri_string(), 'super/master') !== false) ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">storage</i>
                            Master Data
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('super/master/provinces') ?>">
                                    <i class="material-icons-outlined">location_on</i>
                                    Provinsi
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/regencies') ?>">
                                    <i class="material-icons-outlined">location_city</i>
                                    Kabupaten/Kota
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/universities') ?>">
                                    <i class="material-icons-outlined">school</i>
                                    Perguruan Tinggi
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/study-programs') ?>">
                                    <i class="material-icons-outlined">menu_book</i>
                                    Program Studi
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/employment-status') ?>">
                                    <i class="material-icons-outlined">work</i>
                                    Status Kepegawaian
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/salary-ranges') ?>">
                                    <i class="material-icons-outlined">attach_money</i>
                                    Range Gaji
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Admin Portal Access -->
                    <li class="sidebar-title">Admin Operations</li>

                    <!-- Admin Dashboard -->
                    <li class="<?= (uri_string() == 'admin/dashboard') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/dashboard') ?>">
                            <i class="material-icons-two-tone">dashboard</i>
                            Admin Dashboard
                        </a>
                    </li>

                    <!-- Member Management -->
                    <li class="<?= (strpos(uri_string(), 'admin/members') !== false) ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">group</i>
                            Member Management
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('admin/members') ?>">
                                    <i class="material-icons-outlined">list</i>
                                    All Members
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/members/pending') ?>">
                                    <i class="material-icons-outlined">hourglass_empty</i>
                                    Pending Approval
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/bulk-import') ?>">
                                    <i class="material-icons-outlined">upload_file</i>
                                    Bulk Import
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Community Management -->
                    <li class="<?= (strpos(uri_string(), 'admin/forum') !== false ||
                                    strpos(uri_string(), 'admin/survey') !== false ||
                                    strpos(uri_string(), 'admin/complaint') !== false ||
                                    strpos(uri_string(), 'admin/wa-groups') !== false) ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">forum</i>
                            Community
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('admin/forum') ?>">
                                    <i class="material-icons-outlined">question_answer</i>
                                    Forum Moderation
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/survey') ?>">
                                    <i class="material-icons-outlined">poll</i>
                                    Surveys
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/complaint') ?>">
                                    <i class="material-icons-outlined">support_agent</i>
                                    Complaints
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/wa-groups') ?>">
                                    <i class="material-icons-outlined">groups</i>
                                    WhatsApp Groups
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Content Management -->
                    <li class="<?= (strpos(uri_string(), 'admin/content') !== false) ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">article</i>
                            Content
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('admin/content/posts') ?>">
                                    <i class="material-icons-outlined">feed</i>
                                    Blog Posts
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/content/pages') ?>">
                                    <i class="material-icons-outlined">description</i>
                                    Static Pages
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/content/categories') ?>">
                                    <i class="material-icons-outlined">label</i>
                                    Categories
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Statistics -->
                    <li class="<?= (strpos(uri_string(), 'admin/statistics') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/statistics') ?>">
                            <i class="material-icons-two-tone">analytics</i>
                            Statistics & Reports
                        </a>
                    </li>

                    <!-- Member Portal (if has access) -->
                    <?php if ($hasMemberPortalAccess): ?>
                        <li class="sidebar-title">Portal Anggota</li>

                        <li class="<?= (uri_string() == 'member/dashboard') ? 'active-page' : '' ?>">
                            <a href="<?= base_url('member/dashboard') ?>">
                                <i class="material-icons-two-tone">home</i>
                                Dashboard Anggota
                            </a>
                        </li>

                        <li class="<?= (strpos(uri_string(), 'member/profile') !== false) ? 'active-page' : '' ?>">
                            <a href="<?= base_url('member/profile') ?>">
                                <i class="material-icons-two-tone">person</i>
                                Profil Saya
                            </a>
                        </li>

                        <li class="<?= (strpos(uri_string(), 'member/card') !== false) ? 'active-page' : '' ?>">
                            <a href="<?= base_url('member/card') ?>">
                                <i class="material-icons-two-tone">badge</i>
                                Kartu Anggota
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- System Configuration -->
                    <li class="sidebar-title">System Configuration</li>

                    <!-- Settings -->
                    <li class="<?= (strpos(uri_string(), 'super/settings') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/settings') ?>">
                            <i class="material-icons-two-tone">settings</i>
                            Settings
                        </a>
                    </li>

                    <!-- Audit Logs -->
                    <li class="<?= (strpos(uri_string(), 'super/audit-logs') !== false) ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/audit-logs') ?>">
                            <i class="material-icons-two-tone">history</i>
                            Audit Logs
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="app-container">
            <!-- Search -->
            <div class="search">
                <form>
                    <input class="form-control" type="text" placeholder="Cari sesuatu..." aria-label="Search">
                </form>
                <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
            </div>

            <!-- Header -->
            <div class="app-header">
                <nav class="navbar navbar-light navbar-expand-lg">
                    <div class="container-fluid">
                        <div class="navbar-nav" id="navbarNav">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link hide-sidebar-toggle-button" href="#">
                                        <i class="material-icons">first_page</i>
                                    </a>
                                </li>
                                <!-- Quick Actions Dropdown -->
                                <li class="nav-item dropdown hidden-on-mobile">
                                    <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="material-icons">add</i>
                                    </a>
                                    <ul class="dropdown-menu quick-actions-dropdown" aria-labelledby="quickActionsDropdown">
                                        <li><a class="dropdown-item" href="<?= base_url('super/users/create') ?>">
                                                <i class="material-icons-outlined">person_add</i>New User
                                            </a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('super/roles/create') ?>">
                                                <i class="material-icons-outlined">admin_panel_settings</i>New Role
                                            </a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('admin/bulk-import') ?>">
                                                <i class="material-icons-outlined">upload_file</i>Import Members
                                            </a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('admin/survey/create') ?>">
                                                <i class="material-icons-outlined">poll</i>New Survey
                                            </a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="<?= base_url('super/settings') ?>">
                                                <i class="material-icons-outlined">settings</i>System Settings
                                            </a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex">
                            <ul class="navbar-nav">
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('super/dashboard') ? 'active' : '' ?>" href="<?= base_url('super/dashboard') ?>">Super</a>
                                </li>
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('admin/dashboard') ? 'active' : '' ?>" href="<?= base_url('admin/dashboard') ?>">Admin</a>
                                </li>
                                <?php if ($hasMemberPortalAccess): ?>
                                    <li class="nav-item hidden-on-mobile">
                                        <a class="nav-link <?= url_is('member/dashboard') ? 'active' : '' ?>" href="<?= base_url('member/dashboard') ?>">Portal</a>
                                    </li>
                                <?php endif; ?>

                                <!-- Search Toggle -->
                                <li class="nav-item">
                                    <a class="nav-link toggle-search" href="#">
                                        <i class="material-icons">search</i>
                                    </a>
                                </li>

                                <!-- Notifications -->
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link nav-notifications-toggle" id="notificationsDropDown" href="#" data-bs-toggle="dropdown">
                                        <?php
                                        $unreadNotifications = 3; // TODO: Get from NotificationService
                                        echo $unreadNotifications > 0 ? $unreadNotifications : '0';
                                        ?>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsDropDown">
                                        <h6 class="dropdown-header">Notifikasi</h6>
                                        <div class="notifications-dropdown-list">
                                            <a href="#">
                                                <div class="notifications-dropdown-item">
                                                    <div class="notifications-dropdown-item-image">
                                                        <span class="notifications-badge bg-success text-white">
                                                            <i class="material-icons-outlined">person_add</i>
                                                        </span>
                                                    </div>
                                                    <div class="notifications-dropdown-item-text">
                                                        <p class="bold-notifications-text">5 anggota baru terdaftar</p>
                                                        <small>5 menit yang lalu</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="notifications-dropdown-item">
                                                    <div class="notifications-dropdown-item-image">
                                                        <span class="notifications-badge bg-warning text-white">
                                                            <i class="material-icons-outlined">backup</i>
                                                        </span>
                                                    </div>
                                                    <div class="notifications-dropdown-item-text">
                                                        <p class="bold-notifications-text">System backup diperlukan</p>
                                                        <small>1 jam yang lalu</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="notifications-dropdown-item">
                                                    <div class="notifications-dropdown-item-image">
                                                        <span class="notifications-badge bg-info text-white">
                                                            <i class="material-icons-outlined">file_upload</i>
                                                        </span>
                                                    </div>
                                                    <div class="notifications-dropdown-item-text">
                                                        <p class="bold-notifications-text">Import data berhasil</p>
                                                        <small>2 jam yang lalu</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </li>

                                <!-- User Menu -->
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link" href="#" data-bs-toggle="dropdown">
                                        <?php if ($currentUser && !empty($currentUser->photo)): ?>
                                            <img src="<?= base_url('uploads/photos/' . esc($currentUser->photo)) ?>"
                                                alt="User" class="user-dropdown-img">
                                        <?php else: ?>
                                            <i class="material-icons">account_circle</i>
                                        <?php endif; ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
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
                                        <?php if ($hasMemberPortalAccess): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('member/profile') ?>">
                                                    <i class="material-icons-outlined">person</i> Profil Saya
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('member/dashboard') ?>">
                                                    <i class="material-icons-outlined">dashboard</i> Portal Anggota
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('member/profile/change-password') ?>">
                                                <i class="material-icons-outlined">lock</i> Ubah Password
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>"
                                                onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                                                <i class="material-icons-outlined">logout</i> Logout
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- App Content -->
            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <!-- Breadcrumb -->
                        <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-description">
                                        <h1><?= esc($pageTitle ?? $title) ?></h1>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item">
                                                    <a href="<?= base_url('super/dashboard') ?>">Dashboard</a>
                                                </li>
                                                <?php foreach ($breadcrumb as $key => $item): ?>
                                                    <?php if ($key === array_key_last($breadcrumb)): ?>
                                                        <li class="breadcrumb-item active" aria-current="page">
                                                            <?= esc($item['title']) ?>
                                                        </li>
                                                    <?php else: ?>
                                                        <li class="breadcrumb-item">
                                                            <a href="<?= esc($item['url']) ?>"><?= esc($item['title']) ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Flash Messages -->
                        <?php if ($session->has('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">check_circle</i>
                                <?= $session->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">error</i>
                                <?= $session->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">warning</i>
                                <?= $session->getFlashdata('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('info')): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">info</i>
                                <?= $session->getFlashdata('info') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($session->has('errors') && is_array($session->getFlashdata('errors'))): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="material-icons-outlined align-middle me-2">error</i>Terdapat kesalahan:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($session->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Main Content -->
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascripts -->
    <script src="<?= base_url('assets/plugins/jquery/jquery-3.5.1.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/perfectscroll/perfect-scrollbar.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/pace/pace.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/custom.js') ?>"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- Global Variables -->
    <script>
        const BASE_URL = '<?= base_url() ?>';
        const CSRF_TOKEN = '<?= csrf_hash() ?>';
        const CSRF_HEADER = '<?= csrf_header() ?>';
    </script>

    <!-- Custom Script -->
    <script>
        $(document).ready(function() {
            // ===========================================
            // SIDEBAR FUNCTIONALITY
            // ===========================================

            // Active menu highlighting based on current URL
            const currentUrl = window.location.href;
            $('.accordion-menu a').each(function() {
                if (this.href === currentUrl) {
                    $(this).closest('li').addClass('active-page');
                    // Open parent sub-menu if exists
                    const $parentSubMenu = $(this).closest('.sub-menu');
                    if ($parentSubMenu.length) {
                        $parentSubMenu.show();
                        $parentSubMenu.prev('a').addClass('active');
                    }
                }
            });

            // Sidebar toggle
            $('.hide-sidebar-toggle-button').on('click', function(e) {
                e.preventDefault();
                $('.app').toggleClass('sidenav-toggled');
            });

            // Sub-menu toggle with smooth animation
            $('.accordion-menu > ul > li > a').on('click', function(e) {
                const $subMenu = $(this).next('.sub-menu');
                if ($subMenu.length) {
                    e.preventDefault();

                    // Close other sub-menus
                    $(this).closest('ul').find('.sub-menu').not($subMenu).slideUp(300);
                    $(this).closest('ul').find('.has-sub-menu').not($(this).find('.has-sub-menu'))
                        .removeClass('rotate-arrow');

                    // Toggle current sub-menu
                    $subMenu.slideToggle(300);
                    $(this).find('.has-sub-menu').toggleClass('rotate-arrow');
                }
            });

            // ===========================================
            // SEARCH FUNCTIONALITY
            // ===========================================

            // Toggle search bar
            $('.toggle-search').on('click', function(e) {
                e.preventDefault();
                $('.search').toggleClass('active');
                if ($('.search').hasClass('active')) {
                    $('.search input').focus();
                }
            });

            // Close search on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('.search').hasClass('active')) {
                    $('.search').removeClass('active');
                }
            });

            // ===========================================
            // ALERTS AUTO-HIDE
            // ===========================================

            setTimeout(function() {
                $('.alert').not('.alert-permanent').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // Manual alert dismiss with animation
            $('.alert .btn-close').on('click', function() {
                $(this).closest('.alert').fadeOut('fast', function() {
                    $(this).remove();
                });
            });

            // ===========================================
            // NOTIFICATION DROPDOWN
            // ===========================================

            // Mark notification as read on click
            $('.notifications-dropdown-item').on('click', function(e) {
                $(this).addClass('read');
                // TODO: Send AJAX request to mark as read
            });

            // ===========================================
            // LOGOUT CONFIRMATION
            // ===========================================

            $('a[href*="logout"]').on('click', function(e) {
                e.preventDefault();
                const logoutUrl = $(this).attr('href');

                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar dari sistem?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = logoutUrl;
                    }
                });
            });

            // ===========================================
            // FORM VALIDATION HELPER
            // ===========================================

            // Prevent double submit
            $('form').on('submit', function() {
                const $submitBtn = $(this).find('button[type="submit"]');
                if (!$submitBtn.prop('disabled')) {
                    $submitBtn.prop('disabled', true);
                    const originalText = $submitBtn.html();
                    $submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...');

                    // Re-enable after 3 seconds (safety)
                    setTimeout(function() {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html(originalText);
                    }, 3000);
                }
            });

            // ===========================================
            // CSRF TOKEN UPDATE FOR AJAX
            // ===========================================

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 419) {
                        // CSRF token mismatch
                        Swal.fire({
                            title: 'Session Expired',
                            text: 'Sesi Anda telah berakhir. Halaman akan di-refresh.',
                            icon: 'warning',
                            confirmButtonColor: '#667eea'
                        }).then(() => {
                            location.reload();
                        });
                    }
                }
            });

            // Update CSRF token after successful AJAX
            $(document).ajaxSuccess(function(event, xhr, settings) {
                const newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (newToken) {
                    CSRF_TOKEN = newToken;
                    $('meta[name="csrf-token"]').attr('content', newToken);
                }
            });

            // ===========================================
            // PERFECT SCROLLBAR INITIALIZATION
            // ===========================================

            if ($('.app-menu').length) {
                const ps = new PerfectScrollbar('.app-menu', {
                    wheelSpeed: 2,
                    wheelPropagation: true,
                    minScrollbarLength: 20
                });
            }

            // ===========================================
            // TOOLTIPS & POPOVERS
            // ===========================================

            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize Bootstrap popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // ===========================================
            // DATATABLE DEFAULT CONFIGURATION
            // ===========================================

            // Set default DataTable options
            if ($.fn.DataTable) {
                $.extend(true, $.fn.dataTable.defaults, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                    },
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Semua"]
                    ],
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
                });
            }

            // ===========================================
            // HELPER FUNCTIONS
            // ===========================================

            // Show loading overlay
            window.showLoading = function(message = 'Memproses...') {
                Swal.fire({
                    title: message,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            };

            // Hide loading overlay
            window.hideLoading = function() {
                Swal.close();
            };

            // Show success message
            window.showSuccess = function(message, callback = null) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    confirmButtonColor: '#667eea'
                }).then(() => {
                    if (typeof callback === 'function') {
                        callback();
                    }
                });
            };

            // Show error message
            window.showError = function(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message,
                    confirmButtonColor: '#667eea'
                });
            };

            // Show confirmation dialog
            window.showConfirm = function(title, text, callback) {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback();
                    }
                });
            };

            // Format number to Indonesian currency
            window.formatRupiah = function(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(angka);
            };

            // Format date to Indonesian format
            window.formatTanggal = function(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            };

            // ===========================================
            // RESPONSIVE MENU FOR MOBILE
            // ===========================================

            if ($(window).width() < 768) {
                $('.accordion-menu a').on('click', function() {
                    // Auto-close sidebar on mobile after menu click
                    if (!$(this).next('.sub-menu').length) {
                        setTimeout(function() {
                            $('.app').addClass('sidenav-toggled');
                        }, 300);
                    }
                });
            }

            // ===========================================
            // KEYBOARD SHORTCUTS
            // ===========================================

            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + K for search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $('.toggle-search').click();
                }

                // Ctrl/Cmd + / for sidebar toggle
                if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                    e.preventDefault();
                    $('.hide-sidebar-toggle-button').click();
                }
            });

            // ===========================================
            // PAGE LOAD COMPLETE
            // ===========================================

            console.log('%c Super Admin Panel Loaded ', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold;');
            console.log('Version: 2.0.0');
            console.log('Template: Neptune Admin');
        });

        // ===========================================
        // WINDOW RESIZE HANDLER
        // ===========================================

        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Re-initialize perfect scrollbar on resize
                if ($('.app-menu').length) {
                    $('.app-menu').perfectScrollbar('update');
                }
            }, 250);
        });
    </script>

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>