<?php

/**
 * Component: Sidebar
 * Dynamic sidebar navigation component
 * 
 * Renders hierarchical menu structure dengan permission-based visibility
 * Digunakan untuk member, admin, dan super admin dashboards
 * 
 * Required variables:
 * - $currentUser: Current logged-in user object
 * - $menuItems: Array of menu items (optional, will fetch from MenuService if not provided)
 * - $sidebarType: 'member', 'admin', or 'super' (optional, default: 'member')
 * 
 * Features:
 * - Dynamic menu rendering dari database
 * - Hierarchical menu (parent-child relationships)
 * - Active menu highlighting
 * - Permission-based visibility
 * - Collapsible sub-menus
 * - User profile section
 * - Responsive design
 * 
 * @package App\Views\Components
 * @author  SPK Development Team
 * @version 1.0.0
 */

// Set default values
$currentUser = $currentUser ?? auth()->user();
$sidebarType = $sidebarType ?? 'member';

// Get menu items if not provided
if (!isset($menuItems)) {
    $menuService = new \App\Services\MenuService();
    $result = $menuService->getMenuForUser($currentUser->id);
    $menuItems = $result['success'] ? $result['data'] : [];
}

// Determine logo text and dashboard URL based on sidebar type
$logoConfig = [
    'member' => ['text' => 'SPK Portal', 'url' => 'member/dashboard', 'badge' => 'Anggota SPK'],
    'admin' => ['text' => 'SPK Admin', 'url' => 'admin/dashboard', 'badge' => 'Pengurus SPK'],
    'super' => ['text' => 'SPK System', 'url' => 'super/dashboard', 'badge' => 'Super Admin']
];

$config = $logoConfig[$sidebarType] ?? $logoConfig['member'];

/**
 * Check if menu item is active
 */
function isMenuActive($url)
{
    $currentUrl = uri_string();

    // Exact match
    if ($currentUrl === trim($url, '/')) {
        return true;
    }

    // Partial match for sub-pages
    if (strpos($currentUrl, trim($url, '/')) === 0) {
        return true;
    }

    return false;
}

/**
 * Render menu item recursively
 */
function renderMenuItem($item, $level = 0)
{
    // Convert array to object if necessary
    if (is_array($item)) {
        $item = (object) $item;
    }

    $hasChildren = isset($item->children) && count($item->children) > 0;
    $isActive = isMenuActive($item->url ?? '');
    $activeClass = $isActive ? 'active-page' : '';

    // Check permission if required - use permission_key field
    if (!empty($item->permission_key)) {
        if (!auth()->user()->can($item->permission_key)) {
            return '';
        }
    }

    echo '<li class="' . $activeClass . '">';

    if ($hasChildren) {
        // Parent menu with children - Support both Bootstrap 4 & 5
        $collapseId = 'menu-' . $item->id;
        echo '<a href="#" class="submenu-toggle" ';
        echo 'data-toggle="collapse" data-target="#' . $collapseId . '" ';  // Bootstrap 4
        echo 'data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" ';  // Bootstrap 5
        echo 'aria-expanded="' . ($isActive ? 'true' : 'false') . '">';
        echo '<i class="material-icons-two-tone">' . esc($item->icon ?? 'circle') . '</i>';
        echo esc($item->title);
        echo '<i class="material-icons has-sub-menu">keyboard_arrow_right</i>';
        echo '</a>';

        // Render children
        echo '<div class="collapse ' . ($isActive ? 'show' : '') . '" id="' . $collapseId . '">';
        echo '<ul class="sub-menu">';
        foreach ($item->children as $child) {
            renderMenuItem($child, $level + 1);
        }
        echo '</ul>';
        echo '</div>';
    } else {
        // Single menu item
        $url = !empty($item->url) ? base_url($item->url) : '#';
        $target = !empty($item->target) ? ' target="' . esc($item->target) . '"' : '';

        echo '<a href="' . $url . '"' . $target . ' class="' . ($isActive ? 'active' : '') . '">';
        echo '<i class="material-icons-two-tone">' . esc($item->icon ?? 'circle') . '</i>';
        echo esc($item->title);

        // Badge (if any)
        if (!empty($item->badge)) {
            echo '<span class="badge rounded-pill badge-' . ($item->badge_color ?? 'primary') . ' float-end">';
            echo esc($item->badge);
            echo '</span>';
        }

        echo '</a>';
    }

    echo '</li>';
}
?>

<div class="app-sidebar">
    <!-- Logo & Brand -->
    <div class="logo">
        <a href="<?= base_url($config['url']) ?>" class="logo-icon">
            <span class="logo-text"><?= $config['text'] ?></span>
        </a>

        <!-- User Profile Section -->
        <div class="sidebar-user-switcher user-activity-online">
            <a href="<?= base_url('member/profile') ?>">
                <?php if ($currentUser && !empty($currentUser->photo)): ?>
                    <img src="<?= base_url('uploads/photos/' . esc($currentUser->photo)) ?>" alt="User Photo">
                <?php else: ?>
                    <img src="<?= base_url('assets/images/avatars/avatar.png') ?>" alt="Default Avatar">
                <?php endif; ?>
                <span class="activity-indicator"></span>
                <span class="user-info-text">
                    <?= esc($currentUser->full_name ?? $currentUser->username ?? 'User') ?><br>
                    <span class="user-state-info">
                        <?php if ($sidebarType === 'super'): ?>
                            <span class="super-admin-badge"><?= $config['badge'] ?></span>
                        <?php else: ?>
                            <?= $config['badge'] ?>
                        <?php endif; ?>
                    </span>
                </span>
            </a>
        </div>
    </div>

    <!-- Menu Navigation -->
    <div class="app-menu">
        <ul class="accordion-menu">
            <?php if (empty($menuItems)): ?>
                <!-- Default fallback menu -->
                <li class="sidebar-title">Menu</li>
                <li class="<?= url_is($config['url']) ? 'active-page' : '' ?>">
                    <a href="<?= base_url($config['url']) ?>" class="<?= url_is($config['url']) ? 'active' : '' ?>">
                        <i class="material-icons-two-tone">dashboard</i>Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('member/profile') ?>">
                        <i class="material-icons-two-tone">person</i>Profil
                    </a>
                </li>
            <?php else: ?>
                <!-- Render dynamic menu items -->
                <?php foreach ($menuItems as $item): ?>
                    <?php if (isset($item->is_separator) && $item->is_separator): ?>
                        <li class="sidebar-title"><?= esc($item->title) ?></li>
                    <?php else: ?>
                        <?php renderMenuItem($item); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- View Switcher (Pengurus & Super Admin Only) -->
            <?php
            $canSwitchView = $currentUser->inGroup('pengurus') || $currentUser->inGroup('superadmin');
            $isInAdminArea = strpos(uri_string(), 'admin/') === 0 || strpos(uri_string(), 'super/') === 0;
            $isInMemberArea = strpos(uri_string(), 'member/') === 0;
            ?>
            <?php if ($canSwitchView && ($isInAdminArea || $isInMemberArea)): ?>
                <li class="sidebar-title">Beralih Tampilan</li>
                <?php if ($isInAdminArea): ?>
                    <!-- Link to Member View -->
                    <li>
                        <a href="<?= base_url('member/dashboard') ?>" class="view-switcher-link">
                            <i class="material-icons-two-tone">person_outline</i>Portal Anggota
                            <span class="badge rounded-pill badge-info float-end">Member</span>
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Link to Admin View -->
                    <li>
                        <a href="<?= base_url('admin/dashboard') ?>" class="view-switcher-link">
                            <i class="material-icons-two-tone">admin_panel_settings</i>Panel Admin
                            <span class="badge rounded-pill badge-primary float-end">Admin</span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Always show logout -->
            <li class="sidebar-title">Akun</li>
            <li>
                <a href="<?= base_url('member/profile/change-password') ?>">
                    <i class="material-icons-two-tone">lock</i>Ubah Password
                </a>
            </li>
            <li>
                <a href="<?= base_url('logout') ?>" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                    <i class="material-icons-two-tone">logout</i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>

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
        display: inline-block;
    }

    /* Active menu highlighting */
    .accordion-menu li.active-page>a {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border-left: 3px solid #667eea;
    }

    /* Sub-menu styling */
    .sub-menu {
        padding-left: 15px;
    }

    .sub-menu li a {
        padding-left: 45px;
        font-size: 14px;
    }

    /* Collapse animation */
    .collapse {
        transition: height 0.35s ease;
    }

    .collapse:not(.show) {
        display: none;
    }

    .collapsing {
        height: 0;
        overflow: hidden;
        transition: height 0.35s ease;
    }

    /* Submenu toggle arrow animation */
    .submenu-toggle .has-sub-menu {
        transition: transform 0.3s ease;
    }

    .submenu-toggle[aria-expanded="true"] .has-sub-menu {
        transform: rotate(90deg);
    }

    /* Hover effect for submenu toggle */
    .submenu-toggle:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    /* Menu icons */
    .accordion-menu i.material-icons-two-tone,
    .accordion-menu i.material-icons-outlined {
        margin-right: 10px;
        vertical-align: middle;
    }

    /* View Switcher Link in Sidebar */
    .view-switcher-link {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-left: 3px solid #667eea !important;
        font-weight: 500 !important;
    }

    .view-switcher-link:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
        border-left-color: #764ba2 !important;
    }

    .view-switcher-link .badge {
        font-size: 10px;
        padding: 4px 8px;
    }

    /* Sidebar user section */
    .sidebar-user-switcher {
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    .sidebar-user-switcher:hover {
        opacity: 0.8;
    }

    /* Activity indicator */
    .activity-indicator {
        width: 10px;
        height: 10px;
        background: #28a745;
        border-radius: 50%;
        position: absolute;
        bottom: 5px;
        right: 5px;
        border: 2px solid white;
    }
</style>

<script>
    // Use setTimeout to ensure DOM is fully loaded and theme JS has initialized
    setTimeout(function() {
        console.log('[Sidebar] Initializing submenu handlers...');

        // Auto-expand active parent menu
        const activeMenuItem = document.querySelector('.accordion-menu li.active-page');
        if (activeMenuItem) {
            const parentCollapse = activeMenuItem.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const parentToggle = document.querySelector('[data-target="#' + parentCollapse.id + '"], [data-bs-target="#' + parentCollapse.id + '"]');
                if (parentToggle) {
                    parentToggle.setAttribute('aria-expanded', 'true');
                }
            }
        }

        // Remove any existing click handlers from theme
        const submenuToggles = document.querySelectorAll('.submenu-toggle');
        console.log('[Sidebar] Found ' + submenuToggles.length + ' submenu toggles');

        submenuToggles.forEach(function(toggle, index) {
            // Clone and replace to remove all event listeners
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);

            // Add our custom click handler
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('[Sidebar] Toggle clicked:', this);

                // Get target ID
                const targetId = this.getAttribute('data-target') || this.getAttribute('data-bs-target');
                console.log('[Sidebar] Target ID:', targetId);

                if (!targetId) {
                    console.error('[Sidebar] No target ID found');
                    return false;
                }

                const target = document.querySelector(targetId);
                console.log('[Sidebar] Target element:', target);

                if (!target) {
                    console.error('[Sidebar] Target element not found:', targetId);
                    return false;
                }

                // Toggle collapse
                const isExpanded = target.classList.contains('show');
                console.log('[Sidebar] Is expanded:', isExpanded);

                if (isExpanded) {
                    // Close menu
                    target.classList.remove('show');
                    target.style.display = 'none';
                    this.setAttribute('aria-expanded', 'false');
                    this.classList.remove('active');
                    console.log('[Sidebar] Menu closed');
                } else {
                    // Close other open menus first (accordion behavior)
                    document.querySelectorAll('.accordion-menu .collapse.show').forEach(function(openMenu) {
                        if (openMenu !== target) {
                            openMenu.classList.remove('show');
                            openMenu.style.display = 'none';
                            const relatedToggle = document.querySelector('[data-target="#' + openMenu.id + '"], [data-bs-target="#' + openMenu.id + '"]');
                            if (relatedToggle) {
                                relatedToggle.setAttribute('aria-expanded', 'false');
                                relatedToggle.classList.remove('active');
                            }
                        }
                    });

                    // Open this menu
                    target.classList.add('show');
                    target.style.display = 'block';
                    this.setAttribute('aria-expanded', 'true');
                    this.classList.add('active');
                    console.log('[Sidebar] Menu opened');
                }

                return false;
            });

            console.log('[Sidebar] Handler attached to toggle ' + (index + 1));
        });

        console.log('[Sidebar] Submenu initialization complete');

    }, 500); // Wait 500ms for theme JS to initialize
</script>