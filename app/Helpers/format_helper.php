<?php

/**
 * Format Helper
 * 
 * Helper functions untuk formatting data (currency, date, phone, text, dll)
 * Mempermudah formatting output di views
 * 
 * Load helper di controller: helper('format');
 * Load helper di view: sudah auto-loaded via Autoload.php
 * 
 * @package App\Helpers
 * @author  SPK Development Team
 * @version 1.0.0
 */

if (!function_exists('format_currency')) {
    /**
     * Format number as Indonesian Rupiah currency
     * 
     * @param float|int $amount Amount to format
     * @param bool $showSymbol Show Rp symbol
     * @return string Formatted currency
     */
    function format_currency($amount, bool $showSymbol = true): string
    {
        if ($amount === null || $amount === '') {
            return $showSymbol ? 'Rp 0' : '0';
        }

        $formatted = number_format($amount, 0, ',', '.');

        return $showSymbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_number')) {
    /**
     * Format number with thousand separator
     * 
     * @param float|int $number Number to format
     * @param int $decimals Number of decimal places
     * @return string Formatted number
     */
    function format_number($number, int $decimals = 0): string
    {
        if ($number === null || $number === '') {
            return '0';
        }

        return number_format($number, $decimals, ',', '.');
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date to Indonesian format
     * 
     * @param string $date Date string
     * @param string $format Format type: 'short', 'medium', 'long', or custom format
     * @return string Formatted date
     */
    function format_date(?string $date, string $format = 'medium'): string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '-';
        }

        switch ($format) {
            case 'short':
                // Format: 01/12/2024
                return date('d/m/Y', $timestamp);

            case 'medium':
                // Format: 01 Des 2024
                return format_date_indo($timestamp, 'medium');

            case 'long':
                // Format: 01 Desember 2024
                return format_date_indo($timestamp, 'long');

            case 'full':
                // Format: Senin, 01 Desember 2024
                return format_date_indo($timestamp, 'full');

            default:
                // Custom format
                return date($format, $timestamp);
        }
    }
}

if (!function_exists('format_date_indo')) {
    /**
     * Format date to Indonesian language
     * 
     * @param int $timestamp Unix timestamp
     * @param string $format Format type
     * @return string Formatted date in Indonesian
     */
    function format_date_indo(int $timestamp, string $format = 'medium'): string
    {
        $months = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        $monthsShort = [
            1 => 'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        ];

        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $day = date('d', $timestamp);
        $month = date('n', $timestamp);
        $year = date('Y', $timestamp);
        $dayName = $days[date('l', $timestamp)];

        switch ($format) {
            case 'medium':
                return $day . ' ' . $monthsShort[$month] . ' ' . $year;

            case 'long':
                return $day . ' ' . $months[$month] . ' ' . $year;

            case 'full':
                return $dayName . ', ' . $day . ' ' . $months[$month] . ' ' . $year;

            default:
                return $day . ' ' . $months[$month] . ' ' . $year;
        }
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime to Indonesian format
     * 
     * @param string $datetime Datetime string
     * @param bool $withSeconds Show seconds
     * @return string Formatted datetime
     */
    function format_datetime(?string $datetime, bool $withSeconds = false): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($datetime);

        if ($timestamp === false) {
            return '-';
        }

        $date = format_date_indo($timestamp, 'medium');
        $time = $withSeconds ? date('H:i:s', $timestamp) : date('H:i', $timestamp);

        return $date . ' ' . $time;
    }
}

if (!function_exists('format_time')) {
    /**
     * Format time
     * 
     * @param string $time Time string
     * @param bool $withSeconds Show seconds
     * @return string Formatted time
     */
    function format_time(?string $time, bool $withSeconds = true): string
    {
        if (empty($time)) {
            return '-';
        }

        $timestamp = strtotime($time);

        if ($timestamp === false) {
            return '-';
        }

        return $withSeconds ? date('H:i:s', $timestamp) : date('H:i', $timestamp);
    }
}

if (!function_exists('format_phone')) {
    /**
     * Format phone number to Indonesian format
     * 
     * @param string $phone Phone number
     * @return string Formatted phone
     */
    function format_phone(?string $phone): string
    {
        if (empty($phone)) {
            return '-';
        }

        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Format: 0812-3456-7890
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        }

        return $phone;
    }
}

if (!function_exists('format_whatsapp')) {
    /**
     * Format phone for WhatsApp link
     * Convert 08xx to 628xx
     * 
     * @param string $phone Phone number
     * @return string Formatted phone for WhatsApp
     */
    function format_whatsapp(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xx to 628xx
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}

if (!function_exists('whatsapp_link')) {
    /**
     * Generate WhatsApp chat link
     * 
     * @param string $phone Phone number
     * @param string $message Pre-filled message
     * @return string WhatsApp URL
     */
    function whatsapp_link(?string $phone, string $message = ''): string
    {
        if (empty($phone)) {
            return '#';
        }

        $formattedPhone = format_whatsapp($phone);
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$formattedPhone}" . ($message ? "?text={$encodedMessage}" : '');
    }
}

if (!function_exists('format_filesize')) {
    /**
     * Format file size to human readable
     * 
     * @param int $bytes File size in bytes
     * @param int $decimals Number of decimal places
     * @return string Formatted file size
     */
    function format_filesize(int $bytes, int $decimals = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $decimals) . ' ' . $units[$i];
    }
}

if (!function_exists('limit_text')) {
    /**
     * Limit text to specified length with ellipsis
     * 
     * @param string $text Text to limit
     * @param int $limit Character limit
     * @param string $end Ending string
     * @return string Limited text
     */
    function limit_text(?string $text, int $limit = 100, string $end = '...'): string
    {
        if (empty($text)) {
            return '';
        }

        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit) . $end;
    }
}

if (!function_exists('limit_words')) {
    /**
     * Limit text to specified number of words
     * 
     * @param string $text Text to limit
     * @param int $limit Word limit
     * @param string $end Ending string
     * @return string Limited text
     */
    function limit_words(?string $text, int $limit = 50, string $end = '...'): string
    {
        if (empty($text)) {
            return '';
        }

        $words = explode(' ', $text);

        if (count($words) <= $limit) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $limit)) . $end;
    }
}

if (!function_exists('status_badge')) {
    /**
     * Generate Bootstrap badge based on status
     * 
     * @param string $status Status value
     * @param array $map Status to badge class mapping
     * @return string HTML badge
     */
    function status_badge(?string $status, array $map = []): string
    {
        if (empty($status)) {
            return '<span class="badge bg-secondary">-</span>';
        }

        $defaultMap = [
            'active' => 'success',
            'inactive' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'suspended' => 'danger',
            'completed' => 'success',
            'processing' => 'info',
            'cancelled' => 'danger',
        ];

        $map = array_merge($defaultMap, $map);
        $class = $map[strtolower($status)] ?? 'secondary';
        $label = ucfirst($status);

        return "<span class=\"badge bg-{$class}\">{$label}</span>";
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename for safe file upload
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    function sanitize_filename(string $filename): string
    {
        // Remove special characters except dot, dash, underscore
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        return $filename;
    }
}

if (!function_exists('time_ago')) {
    /**
     * Get human readable time difference
     * 
     * @param string $datetime Datetime string
     * @return string Time ago text
     */
    function time_ago(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }

        $timestamp = strtotime($datetime);

        if ($timestamp === false) {
            return '-';
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'baru saja';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' menit yang lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari yang lalu';
        } else {
            return format_date($datetime, 'medium');
        }
    }
}
