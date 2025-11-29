<?php

/**
 * Settings Helper
 *
 * Helper functions to access system settings easily
 *
 * @package App\Helpers
 */

if (!function_exists('get_setting')) {
    /**
     * Get a setting value by class and key
     *
     * @param string $class Setting class (e.g., 'App\\Config\\General')
     * @param string $key Setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed
     */
    function get_setting(string $class, string $key, $default = null)
    {
        $settingModel = new \App\Models\SettingModel();
        return $settingModel->getValue($class, $key, $default);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get application name
     *
     * @return string
     */
    function app_name(): string
    {
        return get_setting('App\\Config\\General', 'app_name', 'SPK System');
    }
}

if (!function_exists('app_logo')) {
    /**
     * Get application logo path or return default
     *
     * @param bool $fullUrl Return full URL instead of path
     * @return string|null
     */
    function app_logo(bool $fullUrl = true): ?string
    {
        $logoPath = get_setting('App\\Config\\General', 'logo_path');

        if (!$logoPath) {
            return null;
        }

        return $fullUrl ? base_url($logoPath) : $logoPath;
    }
}

if (!function_exists('app_tagline')) {
    /**
     * Get application tagline
     *
     * @return string
     */
    function app_tagline(): string
    {
        return get_setting('App\\Config\\General', 'app_tagline', 'Sistem Informasi Serikat Pekerja Kampus');
    }
}

if (!function_exists('maintenance_mode')) {
    /**
     * Check if maintenance mode is enabled
     *
     * @return bool
     */
    function maintenance_mode(): bool
    {
        return (bool) get_setting('App\\Config\\General', 'maintenance_mode', false);
    }
}

if (!function_exists('registration_enabled')) {
    /**
     * Check if public registration is enabled
     *
     * @return bool
     */
    function registration_enabled(): bool
    {
        return (bool) get_setting('App\\Config\\General', 'registration_enabled', true);
    }
}
