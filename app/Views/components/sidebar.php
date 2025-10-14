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
    $hasChildren = isset($item->children) && count($item->children) > 0;
    $isActive = isMenuActive($item->url ?? '');
    $activeClass = $isActive ? 'active-page' : '';

    // Check permission if required
    if (!empty($item->required_permission)) {
        if (!auth()->user()->can($item->required_permission)) {
            return '';
        }
    }

    echo '<li class="' . $activeClass . '">';

    if ($hasChildren) {
        // Parent menu with children
        $collapseId = 'menu-' . $item->id;
        echo '<a href="#" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '">';
        echo '<i class="' . esc($item->icon ?? 'material-icons-two-tone') . '">' . ($item->icon_text ?? 'circle') . '</i>';
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
        echo '<i class="' . esc($item->icon ?? 'material-icons-two-tone') . '">' . ($item->icon_text ?? 'circle') . '</i>';
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

            <!-- Always show logout -->
            <li class="sidebar-title">Akun</li>
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

    /* Menu icons */
    .accordion-menu i.material-icons-two-tone,
    .accordion-menu i.material-icons-outlined {
        margin-right: 10px;
        vertical-align: middle;
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
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-expand active parent menu
        const activeMenuItem = document.querySelector('.accordion-menu li.active-page');
        if (activeMenuItem) {
            const parentCollapse = activeMenuItem.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
            }
        }

        // Smooth scroll on menu click
        document.querySelectorAll('.app-menu a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    });
</script>