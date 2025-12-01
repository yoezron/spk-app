<?php

/**
 * Component: Header
 * Top navigation header component
 * 
 * Header dengan search, notifications, quick actions, dan user menu
 * Digunakan untuk member, admin, dan super admin dashboards
 * 
 * Required variables:
 * - $currentUser: Current logged-in user object
 * - $headerType: 'member', 'admin', or 'super' (optional, default: 'member')
 * 
 * Features:
 * - Search functionality
 * - Notifications dropdown dengan unread count
 * - Quick actions dropdown (admin/super only)
 * - User menu dropdown
 * - Top navigation tabs (admin/super only)
 * - Responsive design
 * - Sidebar toggle button
 * 
 * @package App\Views\Components
 * @author  SPK Development Team
 * @version 1.0.0
 */

// Set default values
$currentUser = $currentUser ?? auth()->user();
$headerType = $headerType ?? 'member';

// Get notifications (TODO: integrate with NotificationService)
$unreadNotifications = 0;
$notifications = [];

// Configuration based on header type
$showQuickActions = in_array($headerType, ['admin', 'super']);
$showTopNav = in_array($headerType, ['admin', 'super']);
?>

<!-- Search Overlay -->
<div class="search">
    <form action="<?= base_url('search') ?>" method="GET">
        <input class="form-control" type="text" name="q" placeholder="Cari sesuatu..." aria-label="Search">
    </form>
    <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
</div>

<!-- Header Navigation -->
<div class="app-header">
    <nav class="navbar navbar-light navbar-expand-lg">
        <div class="container-fluid">
            <!-- Left Section -->
            <div class="navbar-nav" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Sidebar Toggle -->
                    <li class="nav-item">
                        <a class="nav-link hide-sidebar-toggle-button" href="#">
                            <i class="material-icons">first_page</i>
                        </a>
                    </li>

                    <?php if ($showQuickActions): ?>
                        <!-- Quick Actions (Admin/Super) -->
                        <li class="nav-item dropdown hidden-on-mobile">
                            <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="material-icons">add</i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quickActionsDropdown">
                                <?php if ($headerType === 'super'): ?>
                                    <li><a class="dropdown-item" href="<?= base_url('super/roles/create') ?>">
                                            <i class="material-icons-outlined">admin_panel_settings</i> Buat Role Baru
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('super/permissions/create') ?>">
                                            <i class="material-icons-outlined">verified_user</i> Buat Permission
                                        </a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('super/menus/create') ?>">
                                            <i class="material-icons-outlined">menu</i> Buat Menu Baru
                                        </a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>

                                <?php if (auth()->user()->can('survey.create')): ?>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/survey/create') ?>">
                                            <i class="material-icons-outlined">poll</i> Buat Survei
                                        </a></li>
                                <?php endif; ?>

                                <?php if (auth()->user()->can('content.create')): ?>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/content/posts/create') ?>">
                                            <i class="material-icons-outlined">article</i> Tulis Artikel
                                        </a></li>
                                <?php endif; ?>

                                <?php if (auth()->user()->can('member.import')): ?>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/bulk-import') ?>">
                                            <i class="material-icons-outlined">upload_file</i> Import Anggota
                                        </a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Explore Menu (Super Admin) -->
                        <?php if ($headerType === 'super'): ?>
                            <li class="nav-item dropdown hidden-on-mobile">
                                <a class="nav-link dropdown-toggle" href="#" id="exploreDropdownLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Right Section -->
            <div class="d-flex">
                <ul class="navbar-nav">
                    <?php if ($showTopNav): ?>
                        <!-- Top Navigation Tabs -->
                        <li class="nav-item hidden-on-mobile">
                            <a class="nav-link <?= url_is(($headerType === 'super' ? 'super' : 'admin') . '/dashboard') ? 'active' : '' ?>"
                                href="<?= base_url(($headerType === 'super' ? 'super' : 'admin') . '/dashboard') ?>">
                                <?= $headerType === 'super' ? 'System' : 'Admin' ?>
                            </a>
                        </li>
                        <li class="nav-item hidden-on-mobile">
                            <a class="nav-link <?= url_is('admin/statistics*') ? 'active' : '' ?>"
                                href="<?= base_url('admin/statistics') ?>">
                                <?= $headerType === 'super' ? 'Reports' : 'Statistik' ?>
                            </a>
                        </li>
                        <li class="nav-item hidden-on-mobile">
                            <a class="nav-link <?= url_is('admin/members*') ? 'active' : '' ?>"
                                href="<?= base_url('admin/members') ?>">
                                Anggota
                            </a>
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
                            <?= $unreadNotifications > 0 ? $unreadNotifications : '0' ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsDropDown">
                            <h6 class="dropdown-header">Notifikasi</h6>
                            <div class="notifications-dropdown-list">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-3 text-muted">
                                        <i class="material-icons-outlined" style="font-size: 48px;">notifications_none</i>
                                        <p class="mb-0 mt-2">Tidak ada notifikasi</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <a href="<?= base_url($notification['url'] ?? '#') ?>">
                                            <div class="notifications-dropdown-item">
                                                <div class="notifications-dropdown-item-image">
                                                    <span class="notifications-badge bg-<?= $notification['color'] ?? 'info' ?> text-white">
                                                        <i class="material-icons-outlined"><?= $notification['icon'] ?? 'notifications' ?></i>
                                                    </span>
                                                </div>
                                                <div class="notifications-dropdown-item-text">
                                                    <p class="bold-notifications-text"><?= esc($notification['title']) ?></p>
                                                    <small><?= $notification['time'] ?? 'Baru saja' ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($notifications)): ?>
                                <div class="dropdown-footer text-center">
                                    <a href="<?= base_url('notifications') ?>" class="text-primary">Lihat Semua Notifikasi</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>

                    <!-- User Menu -->
                    <li class="nav-item hidden-on-mobile">
                        <a class="nav-link" href="#" data-bs-toggle="dropdown">
                            <?php if ($currentUser && !empty($currentUser->photo)): ?>
                                <img src="<?= base_url('uploads/photos/' . esc($currentUser->photo)) ?>"
                                    alt="User Photo" style="width: 32px; height: 32px; border-radius: 50%;">
                            <?php else: ?>
                                <i class="material-icons">account_circle</i>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <?php if ($currentUser && !empty($currentUser->photo)): ?>
                                        <img src="<?= base_url('uploads/photos/' . esc($currentUser->photo)) ?>"
                                            alt="User Photo" style="width: 40px; height: 40px; border-radius: 50%;" class="me-2">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= esc($currentUser->full_name ?? $currentUser->username ?? 'User') ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($currentUser->email ?? '') ?></small>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php $hasMemberPortalAccess = has_role('anggota') || has_role('Anggota') || has_role('calon_anggota') || has_role('Calon Anggota'); ?>
                            <?php if ($hasMemberPortalAccess): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('member/profile') ?>">
                                        <i class="material-icons-outlined">person</i> Profil Saya
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($hasMemberPortalAccess && in_array($headerType, ['admin', 'super'])): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('member/dashboard') ?>">
                                        <i class="material-icons-outlined">dashboard</i> Portal Anggota
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($headerType === 'super'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('admin/dashboard') ?>">
                                        <i class="material-icons-outlined">admin_panel_settings</i> Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($hasMemberPortalAccess): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('member/card') ?>">
                                        <i class="material-icons-outlined">badge</i> Kartu Anggota
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php if ($hasMemberPortalAccess): ?>
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
                                <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"
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

<style>
    /* Notification badge styling */
    .nav-notifications-toggle {
        position: relative;
        font-weight: 600;
        min-width: 30px;
        text-align: center;
    }

    /* Notification dropdown styling */
    .notifications-dropdown {
        width: 350px;
        max-height: 400px;
        overflow-y: auto;
    }

    .notifications-dropdown-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .notifications-dropdown-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .notifications-dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .notifications-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    .bold-notifications-text {
        font-weight: 500;
        margin-bottom: 4px;
        color: #2d3748;
    }

    .dropdown-footer {
        padding: 12px;
        border-top: 1px solid #e2e8f0;
    }

    /* User menu dropdown */
    .dropdown-menu .dropdown-item {
        padding: 10px 16px;
        display: flex;
        align-items: center;
    }

    .dropdown-menu .dropdown-item i {
        margin-right: 10px;
        font-size: 20px;
    }

    /* Active nav link */
    .navbar-nav .nav-link.active {
        color: #667eea;
        font-weight: 600;
    }

    /* Search overlay */
    .search {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .search.active {
        display: flex;
    }

    .search form {
        width: 100%;
        max-width: 600px;
        position: relative;
    }

    .search input {
        width: 100%;
        padding: 20px 25px;
        font-size: 24px;
        border: none;
        border-radius: 50px;
        background: white;
    }

    .toggle-search {
        position: absolute;
        top: 30px;
        right: 30px;
        color: white;
        font-size: 36px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .notifications-dropdown {
            width: 300px;
        }

        .hidden-on-mobile {
            display: none !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search toggle functionality
        const searchOverlay = document.querySelector('.search');
        const searchToggleButtons = document.querySelectorAll('.toggle-search');

        searchToggleButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                searchOverlay.classList.toggle('active');

                if (searchOverlay.classList.contains('active')) {
                    searchOverlay.querySelector('input').focus();
                }
            });
        });

        // Close search on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.notifications-dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
</script>