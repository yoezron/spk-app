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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Super Admin Panel - Serikat Pekerja Kampus">
    <meta name="author" content="SPK Development Team">

    <!-- Title -->
    <title><?= esc($title ?? 'Super Admin Panel - SI SPK') ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

    <!-- Neptune Admin CSS -->
    <link href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/plugins/perfectscroll/perfect-scrollbar.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/plugins/pace/pace.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/main.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">

    <!-- Additional CSS -->
    <?= $this->renderSection('styles') ?>

    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/spk-icon.png') ?>" />

    <style>
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
                        <?php if ($currentUser && isset($currentUser->photo)): ?>
                            <img src="<?= base_url('uploads/photos/' . $currentUser->photo) ?>" alt="User">
                        <?php else: ?>
                            <img src="<?= base_url('assets/images/avatars/avatar.png') ?>" alt="User">
                        <?php endif; ?>
                        <span class="activity-indicator"></span>
                        <span class="user-info-text">
                            <?= esc($currentUser->full_name ?? $currentUser->username) ?><br>
                            <span class="user-state-info">
                                <span class="super-admin-badge">Super Admin</span>
                            </span>
                        </span>
                    </a>
                </div>
            </div>

            <div class="app-menu">
                <ul class="accordion-menu">
                    <li class="sidebar-title">Dashboard</li>

                    <!-- Super Admin Dashboard -->
                    <li class="<?= url_is('super/dashboard') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/dashboard') ?>" class="<?= url_is('super/dashboard') ? 'active' : '' ?>">
                            <i class="material-icons-two-tone">dashboard</i>Super Dashboard
                        </a>
                    </li>

                    <!-- System Management -->
                    <li class="sidebar-title">Manajemen Sistem</li>

                    <li class="<?= url_is('super/roles*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">admin_panel_settings</i>Role Management
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('super/roles') ?>">Daftar Role</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/roles/create') ?>">Buat Role Baru</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/roles/permissions') ?>">Kelola Permissions</a>
                            </li>
                        </ul>
                    </li>

                    <li class="<?= url_is('super/permissions*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">verified_user</i>Permission Management
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('super/permissions') ?>">Daftar Permission</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/permissions/create') ?>">Buat Permission Baru</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/permissions/sync') ?>">Sync ke Shield</a>
                            </li>
                        </ul>
                    </li>

                    <li class="<?= url_is('super/menus*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">menu</i>Menu Management
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('super/menus') ?>">Daftar Menu</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/menus/create') ?>">Buat Menu Baru</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/menus/preview') ?>">Preview Menu</a>
                            </li>
                        </ul>
                    </li>

                    <li class="<?= url_is('super/master*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">storage</i>Master Data
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('super/master/provinces') ?>">Provinsi</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/regencies') ?>">Kabupaten/Kota</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/universities') ?>">Perguruan Tinggi</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/study-programs') ?>">Program Studi</a>
                            </li>
                            <li>
                                <a href="<?= base_url('super/master/bulk-import') ?>">Bulk Import</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Member Management -->
                    <li class="sidebar-title">Manajemen Anggota</li>

                    <li class="<?= url_is('admin/members*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">group</i>Kelola Anggota
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('admin/members') ?>">Daftar Anggota</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/members/pending') ?>">Calon Anggota</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/members/suspended') ?>">Anggota Ditangguhkan</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/members/export') ?>">Export Data</a>
                            </li>
                        </ul>
                    </li>

                    <li class="<?= url_is('admin/bulk-import*') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/bulk-import') ?>">
                            <i class="material-icons-two-tone">upload_file</i>Import Anggota
                        </a>
                    </li>

                    <li class="<?= url_is('admin/statistics*') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/statistics') ?>">
                            <i class="material-icons-two-tone">analytics</i>Statistik & Laporan
                        </a>
                    </li>

                    <!-- Community Management -->
                    <li class="sidebar-title">Kelola Komunitas</li>

                    <li class="<?= url_is('admin/forum*') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/forum') ?>">
                            <i class="material-icons-two-tone">forum</i>Moderasi Forum
                        </a>
                    </li>

                    <li class="<?= url_is('admin/survey*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">poll</i>Kelola Survei
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('admin/survey') ?>">Daftar Survei</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/survey/create') ?>">Buat Survei Baru</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/survey/responses') ?>">Lihat Respon</a>
                            </li>
                        </ul>
                    </li>

                    <li class="<?= url_is('admin/complaint*') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/complaint') ?>">
                            <i class="material-icons-two-tone">support</i>Pengaduan
                        </a>
                    </li>

                    <li class="<?= url_is('admin/wa-groups*') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('admin/wa-groups') ?>">
                            <i class="material-icons-two-tone">groups</i>WhatsApp Groups
                        </a>
                    </li>

                    <!-- Content Management -->
                    <li class="sidebar-title">Kelola Konten</li>

                    <li class="<?= url_is('admin/content*') ? 'active-page' : '' ?>">
                        <a href="">
                            <i class="material-icons-two-tone">article</i>Konten & Blog
                            <i class="material-icons has-sub-menu">keyboard_arrow_right</i>
                        </a>
                        <ul class="sub-menu">
                            <li>
                                <a href="<?= base_url('admin/content/posts') ?>">Artikel/Blog</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/content/pages') ?>">Halaman Statis</a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/content/categories') ?>">Kategori</a>
                            </li>
                        </ul>
                    </li>

                    <li class="<?= url_is('super/org-structure*') ? 'active-page' : '' ?>">
                        <a href="<?= base_url('super/org-structure') ?>">
                            <i class="material-icons-two-tone">account_tree</i>Struktur Organisasi
                        </a>
                    </li>

                    <!-- Portal Access -->
                    <li class="sidebar-title">Portal</li>

                    <li>
                        <a href="<?= base_url('admin/dashboard') ?>">
                            <i class="material-icons-two-tone">admin_panel_settings</i>Admin Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url('member/dashboard') ?>">
                            <i class="material-icons-two-tone">home</i>Member Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url('member/profile') ?>">
                            <i class="material-icons-two-tone">person</i>Profil Saya
                        </a>
                    </li>

                    <!-- Settings -->
                    <li class="sidebar-title">Pengaturan</li>

                    <li>
                        <a href="<?= base_url('member/profile/change-password') ?>">
                            <i class="material-icons-two-tone">lock</i>Ubah Password
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url('auth/logout') ?>" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                            <i class="material-icons-two-tone">logout</i>Logout
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
                                <li class="nav-item dropdown hidden-on-mobile">
                                    <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="material-icons">add</i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="quickActionsDropdown">
                                        <li><a class="dropdown-item" href="<?= base_url('super/roles/create') ?>">Buat Role Baru</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('super/permissions/create') ?>">Buat Permission Baru</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('super/menus/create') ?>">Buat Menu Baru</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="<?= base_url('admin/survey/create') ?>">Buat Survei</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('admin/content/posts/create') ?>">Tulis Artikel</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown hidden-on-mobile">
                                    <a class="nav-link dropdown-toggle" href="#" id="exploreDropdownLink" role="button" data-bs-toggle="dropdown">
                                        <i class="material-icons-outlined">explore</i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-lg large-items-menu" aria-labelledby="exploreDropdownLink">
                                        <li>
                                            <h6 class="dropdown-header">Quick Access</h6>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('super/roles') ?>">
                                                <h5 class="dropdown-item-title">
                                                    Role Management
                                                    <span class="badge badge-info">System</span>
                                                </h5>
                                                <span class="dropdown-item-description">Kelola roles dan permissions sistem</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('admin/members') ?>">
                                                <h5 class="dropdown-item-title">
                                                    Member Management
                                                    <span class="badge badge-success">Active</span>
                                                </h5>
                                                <span class="dropdown-item-description">Kelola data anggota SPK</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('admin/statistics') ?>">
                                                <h5 class="dropdown-item-title">
                                                    Analytics
                                                    <span class="badge badge-warning">Reports</span>
                                                </h5>
                                                <span class="dropdown-item-description">Statistik dan laporan sistem</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex">
                            <ul class="navbar-nav">
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('super/dashboard') ? 'active' : '' ?>" href="<?= base_url('super/dashboard') ?>">System</a>
                                </li>
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('admin/dashboard') ? 'active' : '' ?>" href="<?= base_url('admin/dashboard') ?>">Admin</a>
                                </li>
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('admin/statistics*') ? 'active' : '' ?>" href="<?= base_url('admin/statistics') ?>">Reports</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link toggle-search" href="#">
                                        <i class="material-icons">search</i>
                                    </a>
                                </li>

                                <!-- Notifications -->
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link nav-notifications-toggle" id="notificationsDropDown" href="#" data-bs-toggle="dropdown">
                                        <?php
                                        $unreadNotifications = 0; // TODO: Get from NotificationService
                                        echo $unreadNotifications > 0 ? $unreadNotifications : '0';
                                        ?>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsDropDown">
                                        <h6 class="dropdown-header">Notifikasi Sistem</h6>
                                        <div class="notifications-dropdown-list">
                                            <a href="#">
                                                <div class="notifications-dropdown-item">
                                                    <div class="notifications-dropdown-item-image">
                                                        <span class="notifications-badge bg-danger text-white">
                                                            <i class="material-icons-outlined">warning</i>
                                                        </span>
                                                    </div>
                                                    <div class="notifications-dropdown-item-text">
                                                        <p class="bold-notifications-text">System update available</p>
                                                        <small>1 jam yang lalu</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </li>

                                <!-- User Menu -->
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link" href="#" data-bs-toggle="dropdown">
                                        <?php if ($currentUser && isset($currentUser->photo)): ?>
                                            <img src="<?= base_url('uploads/photos/' . $currentUser->photo) ?>"
                                                alt="User" style="width: 32px; height: 32px; border-radius: 50%;">
                                        <?php else: ?>
                                            <i class="material-icons">account_circle</i>
                                        <?php endif; ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('member/profile') ?>">
                                                <i class="material-icons-outlined">person</i> Profil Saya
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('admin/dashboard') ?>">
                                                <i class="material-icons-outlined">admin_panel_settings</i> Admin Panel
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('member/dashboard') ?>">
                                                <i class="material-icons-outlined">dashboard</i> Member Portal
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('member/profile/change-password') ?>">
                                                <i class="material-icons-outlined">lock</i> Ubah Password
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('auth/logout') ?>"
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
                                                    <a href="<?= base_url('super/dashboard') ?>">Super Dashboard</a>
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
                        <?php if (session()->has('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">check_circle</i>
                                <?= session('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">error</i>
                                <?= session('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">warning</i>
                                <?= session('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('info')): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="material-icons-outlined align-middle me-2">info</i>
                                <?= session('info') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('errors') && is_array(session('errors'))): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="material-icons-outlined align-middle me-2">error</i>Terdapat kesalahan:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach (session('errors') as $error): ?>
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

    <!-- Custom Scripts -->
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // Confirm logout
            $('a[href*="logout"]').on('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin keluar?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>