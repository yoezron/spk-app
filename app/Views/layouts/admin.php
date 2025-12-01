<?php

/**
 * Layout: Admin
 * Neptune Admin Template - Admin Dashboard Layout
 * 
 * Layout untuk Admin/Pengurus Dashboard
 * Extended menu dengan admin features: Member management, Content, Statistics, etc.
 * 
 * @package App\Views\Layouts
 * @author  SPK Development Team
 * @version 1.0.0
 */

// Get current user
$currentUser = auth()->user();
$hasMemberPortalAccess = $currentUser->inGroup('superadmin') || $currentUser->inGroup('anggota') || $currentUser->inGroup('Anggota') || $currentUser->inGroup('calon_anggota') || $currentUser->inGroup('Calon Anggota');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Panel - Serikat Pekerja Kampus">
    <meta name="author" content="SPK Development Team">

    <!-- Title -->
    <title><?= esc($title ?? 'Admin Panel - SI SPK') ?></title>

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
</head>

<body>
    <div class="app align-content-stretch d-flex flex-wrap">
        <!-- Sidebar - Using Dynamic Component -->
        <?= view('components/sidebar', [
            'currentUser' => $currentUser,
            'sidebarType' => 'admin'
        ]); ?>

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
                                        <?php if (auth()->user()->can('survey.create')): ?>
                                            <li><a class="dropdown-item" href="<?= base_url('admin/survey/create') ?>">Buat Survei Baru</a></li>
                                        <?php endif; ?>
                                        <?php if (auth()->user()->can('content.create')): ?>
                                            <li><a class="dropdown-item" href="<?= base_url('admin/content/posts/create') ?>">Tulis Artikel</a></li>
                                        <?php endif; ?>
                                        <?php if (auth()->user()->can('member.import')): ?>
                                            <li><a class="dropdown-item" href="<?= base_url('admin/bulk-import') ?>">Import Anggota</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex">
                            <ul class="navbar-nav">
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('admin/dashboard') ? 'active' : '' ?>" href="<?= base_url('admin/dashboard') ?>">Admin</a>
                                </li>
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('admin/statistics*') ? 'active' : '' ?>" href="<?= base_url('admin/statistics') ?>">Statistik</a>
                                </li>
                                <li class="nav-item hidden-on-mobile">
                                    <a class="nav-link <?= url_is('admin/members*') ? 'active' : '' ?>" href="<?= base_url('admin/members') ?>">Anggota</a>
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
                                                        <p class="bold-notifications-text">Calon anggota baru menunggu verifikasi</p>
                                                        <small>5 menit yang lalu</small>
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
                                                alt="<?= esc($currentUser->full_name ?? 'User') ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="material-icons">account_circle</i>
                                        <?php endif; ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
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
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('member/profile/change-password') ?>">
                                                    <i class="material-icons-outlined">lock</i> Ubah Password
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('logout') ?>"
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
                                                    <a href="<?= base_url('admin/dashboard') ?>">Dashboard</a>
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