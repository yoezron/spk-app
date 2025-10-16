<?php

/**
 * Menu Helper
 * 
 * Helper functions untuk dynamic menu generation dengan permission checking
 * Mempermudah rendering menu di layouts berdasarkan user permissions
 * 
 * Load helper di controller: helper('menu');
 * Load helper di view: sudah auto-loaded via Autoload.php
 * 
 * @package App\Helpers
 * @author  SPK Development Team
 * @version 1.0.0
 */

if (!function_exists('render_menu')) {
    /**
     * Render menu with permission checking
     * 
     * @param string $menuType Menu type: 'sidebar', 'header', 'footer'
     * @param string $layout Layout type: 'admin', 'member', 'super', 'public'
     * @return string HTML menu
     */
    function render_menu(string $menuType = 'sidebar', string $layout = 'admin'): string
    {
        $menuService = new \App\Services\MenuService();
        $result = $menuService->getMenusForUser($menuType, $layout);

        if (!$result['success'] || empty($result['data'])) {
            return '';
        }

        $menus = $result['data'];

        return build_menu_html($menus, $menuType, $layout);
    }
}

if (!function_exists('build_menu_html')) {
    /**
     * Build HTML structure for menu items
     * 
     * @param array $menus Menu items array
     * @param string $menuType Menu type
     * @param string $layout Layout type
     * @return string HTML menu
     */
    function build_menu_html(array $menus, string $menuType = 'sidebar', string $layout = 'admin'): string
    {
        if (empty($menus)) {
            return '';
        }

        $html = '';

        foreach ($menus as $menu) {
            // Check permission
            if (!empty($menu['permission_key']) && !has_permission($menu['permission_key'])) {
                continue;
            }

            // Check if menu is header/separator
            if ($menu['is_header']) {
                $html .= '<li class="sidebar-title">' . esc($menu['title']) . '</li>';
                continue;
            }

            // Check if menu has children
            $hasChildren = !empty($menu['children']);
            $isActive = is_menu_active($menu);

            if ($hasChildren) {
                $html .= build_parent_menu($menu, $isActive);
            } else {
                $html .= build_single_menu($menu, $isActive);
            }
        }

        return $html;
    }
}

if (!function_exists('build_parent_menu')) {
    /**
     * Build parent menu with children
     * 
     * @param array $menu Menu item
     * @param bool $isActive Is menu active
     * @return string HTML menu item
     */
    function build_parent_menu(array $menu, bool $isActive = false): string
    {
        $activeClass = $isActive ? 'active-page' : '';
        $icon = !empty($menu['icon']) ? '<i class="material-icons-two-tone">' . esc($menu['icon']) . '</i>' : '';

        $html = '<li class="' . $activeClass . '">';
        $html .= '<a href="">';
        $html .= $icon . esc($menu['title']);
        $html .= '<i class="material-icons has-sub-menu">keyboard_arrow_right</i>';
        $html .= '</a>';
        $html .= '<ul class="sub-menu">';

        // Render children
        foreach ($menu['children'] as $child) {
            // Check permission for child
            if (!empty($child['permission_key']) && !has_permission($child['permission_key'])) {
                continue;
            }

            if ($child['is_divider']) {
                $html .= '<li class="sub-menu-divider"></li>';
                continue;
            }

            $childActive = is_menu_active($child);
            $childUrl = get_menu_url($child);
            $childIcon = !empty($child['icon']) ? '<i class="material-icons-outlined">' . esc($child['icon']) . '</i> ' : '';
            $badge = build_menu_badge($child);

            $html .= '<li>';
            $html .= '<a href="' . $childUrl . '" class="' . ($childActive ? 'active' : '') . '">';
            $html .= $childIcon . esc($child['title']) . $badge;
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</li>';

        return $html;
    }
}

if (!function_exists('build_single_menu')) {
    /**
     * Build single menu without children
     * 
     * @param array $menu Menu item
     * @param bool $isActive Is menu active
     * @return string HTML menu item
     */
    function build_single_menu(array $menu, bool $isActive = false): string
    {
        $activeClass = $isActive ? 'active-page' : '';
        $linkActiveClass = $isActive ? 'active' : '';
        $url = get_menu_url($menu);
        $icon = !empty($menu['icon']) ? '<i class="material-icons-two-tone">' . esc($menu['icon']) . '</i>' : '';
        $badge = build_menu_badge($menu);
        $target = !empty($menu['target']) ? ' target="' . esc($menu['target']) . '"' : '';

        $html = '<li class="' . $activeClass . '">';
        $html .= '<a href="' . $url . '" class="' . $linkActiveClass . '"' . $target . '>';
        $html .= $icon . esc($menu['title']) . $badge;
        $html .= '</a>';
        $html .= '</li>';

        return $html;
    }
}

if (!function_exists('build_menu_badge')) {
    /**
     * Build menu badge HTML
     * 
     * @param array $menu Menu item
     * @return string HTML badge
     */
    function build_menu_badge(array $menu): string
    {
        if (empty($menu['badge_text'])) {
            return '';
        }

        $badgeColor = !empty($menu['badge_color']) ? $menu['badge_color'] : 'primary';

        return ' <span class="badge bg-' . esc($badgeColor) . '">' . esc($menu['badge_text']) . '</span>';
    }
}

if (!function_exists('get_menu_url')) {
    /**
     * Get menu URL
     * 
     * @param array $menu Menu item
     * @return string Menu URL
     */
    function get_menu_url(array $menu): string
    {
        // Priority: route > url > #
        if (!empty($menu['route'])) {
            return route_to($menu['route']);
        }

        if (!empty($menu['url'])) {
            // Check if absolute URL
            if (preg_match('/^https?:\/\//', $menu['url'])) {
                return $menu['url'];
            }

            // Relative URL
            return base_url($menu['url']);
        }

        return '#';
    }
}

if (!function_exists('is_menu_active')) {
    /**
     * Check if menu is active based on current URL
     * 
     * @param array $menu Menu item
     * @return bool
     */
    function is_menu_active(array $menu): bool
    {
        $currentUrl = current_url();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);

        // Check route
        if (!empty($menu['route'])) {
            $menuUrl = route_to($menu['route']);
            $menuPath = parse_url($menuUrl, PHP_URL_PATH);

            if ($currentPath === $menuPath) {
                return true;
            }
        }

        // Check URL
        if (!empty($menu['url'])) {
            $menuUrl = base_url($menu['url']);
            $menuPath = parse_url($menuUrl, PHP_URL_PATH);

            // Exact match
            if ($currentPath === $menuPath) {
                return true;
            }

            // Starts with (for parent menus)
            if (strpos($currentPath, $menuPath) === 0) {
                return true;
            }
        }

        // Check children
        if (!empty($menu['children'])) {
            foreach ($menu['children'] as $child) {
                if (is_menu_active($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('active_link')) {
    /**
     * Get active class if URL matches
     * 
     * @param string|array $urls URL or array of URLs to check
     * @param string $activeClass Active class name
     * @return string Active class or empty string
     */
    function active_link($urls, string $activeClass = 'active'): string
    {
        $currentUrl = current_url();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);

        $urls = is_array($urls) ? $urls : [$urls];

        foreach ($urls as $url) {
            $checkUrl = base_url($url);
            $checkPath = parse_url($checkUrl, PHP_URL_PATH);

            if ($currentPath === $checkPath || strpos($currentPath, $checkPath) === 0) {
                return $activeClass;
            }
        }

        return '';
    }
}

if (!function_exists('active_class')) {
    /**
     * Get active class if URL matches (alias for active_link)
     * 
     * @param string|array $urls URL or array of URLs to check
     * @param string $activeClass Active class name
     * @return string Active class or empty string
     */
    function active_class($urls, string $activeClass = 'active'): string
    {
        return active_link($urls, $activeClass);
    }
}

if (!function_exists('breadcrumb')) {
    /**
     * Generate breadcrumb HTML
     * 
     * @param array $items Breadcrumb items [['title' => 'Home', 'url' => '/'], ...]
     * @return string HTML breadcrumb
     */
    function breadcrumb(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $html = '<nav aria-label="breadcrumb">';
        $html .= '<ol class="breadcrumb">';

        $totalItems = count($items);

        foreach ($items as $index => $item) {
            $isLast = ($index === $totalItems - 1);

            if ($isLast) {
                $html .= '<li class="breadcrumb-item active" aria-current="page">' . esc($item['title']) . '</li>';
            } else {
                $url = isset($item['url']) ? $item['url'] : '#';
                $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . esc($item['title']) . '</a></li>';
            }
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
    }
}

if (!function_exists('page_title')) {
    /**
     * Generate page title with optional breadcrumb
     * 
     * @param string $title Page title
     * @param array $breadcrumbs Breadcrumb items
     * @param string $description Optional description
     * @return string HTML page title
     */
    function page_title(string $title, array $breadcrumbs = [], string $description = ''): string
    {
        $html = '<div class="page-header">';

        if (!empty($breadcrumbs)) {
            $html .= breadcrumb($breadcrumbs);
        }

        $html .= '<div class="page-title">';
        $html .= '<h3>' . esc($title) . '</h3>';

        if (!empty($description)) {
            $html .= '<p class="text-muted">' . esc($description) . '</p>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
