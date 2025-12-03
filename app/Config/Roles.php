<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Roles Configuration
 *
 * Konfigurasi role/grup user untuk SPK
 * Menggunakan lowercase sesuai dengan database auth_groups
 *
 * @package Config
 * @author  SPK Development Team
 * @version 1.0.0
 */
class Roles extends BaseConfig
{
    /**
     * Role Constants
     * PENTING: Nilai harus sesuai dengan field 'group' di tabel auth_groups_users
     */
    public const SUPERADMIN = 'superadmin';
    public const PENGURUS = 'pengurus';
    public const KOORDINATOR = 'koordinator';
    public const ANGGOTA = 'anggota';
    public const CALON_ANGGOTA = 'calon_anggota';

    /**
     * Get all available roles
     *
     * @return array
     */
    public static function getAllRoles(): array
    {
        return [
            self::SUPERADMIN,
            self::PENGURUS,
            self::KOORDINATOR,
            self::ANGGOTA,
            self::CALON_ANGGOTA,
        ];
    }

    /**
     * Get roles that can approve members
     *
     * @return array
     */
    public static function getApproverRoles(): array
    {
        return [
            self::SUPERADMIN,
            self::PENGURUS,
        ];
    }

    /**
     * Get active member roles (excluding pending/calon)
     *
     * @return array
     */
    public static function getActiveMemberRoles(): array
    {
        return [
            self::SUPERADMIN,
            self::PENGURUS,
            self::KOORDINATOR,
            self::ANGGOTA,
        ];
    }

    /**
     * Get role label (Title Case) for display purposes
     *
     * @param string $role Role constant
     * @return string
     */
    public static function getRoleLabel(string $role): string
    {
        $labels = [
            self::SUPERADMIN => 'Super Admin',
            self::PENGURUS => 'Pengurus',
            self::KOORDINATOR => 'Koordinator Wilayah',
            self::ANGGOTA => 'Anggota',
            self::CALON_ANGGOTA => 'Calon Anggota',
        ];

        return $labels[$role] ?? ucwords(str_replace('_', ' ', $role));
    }

    /**
     * Check if role is valid
     *
     * @param string $role Role to check
     * @return bool
     */
    public static function isValidRole(string $role): bool
    {
        return in_array($role, self::getAllRoles(), true);
    }

    /**
     * Check if user can approve members based on role
     *
     * @param string $role Role to check
     * @return bool
     */
    public static function canApproveMembers(string $role): bool
    {
        return in_array($role, self::getApproverRoles(), true);
    }
}
