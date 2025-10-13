<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

/**
 * User Entity
 * 
 * Extended dari CodeIgniter Shield User Entity
 * Menambahkan custom methods untuk SPK functionality
 * 
 * @package App\Entities
 * @author  SPK Development Team
 * @version 1.0.0
 */
class User extends ShieldUser
{
    /**
     * Additional data mapping
     */
    protected $datamap = [];

    /**
     * Define date fields
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Type casting
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    // ========================================
    // STATUS CHECK METHODS
    // ========================================

    /**
     * Check if user is active
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->attributes['active'] ?? false;
    }

    /**
     * Check if user is inactive/suspended
     * 
     * @return bool
     */
    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    /**
     * Check if user is pending verification
     * User dengan role "Calon Anggota" dianggap pending
     * 
     * @return bool
     */
    public function isPending(): bool
    {
        // Check if user has "Calon Anggota" role
        return $this->hasRole('Calon Anggota');
    }

    /**
     * Check if user needs reactivation
     * User yang inactive dan bukan calon anggota perlu reaktivasi
     * 
     * @return bool
     */
    public function needsReactivation(): bool
    {
        return $this->isInactive() && !$this->isPending();
    }

    /**
     * Check if user is verified member
     * User dengan role selain "Calon Anggota"
     * 
     * @return bool
     */
    public function isVerifiedMember(): bool
    {
        return !$this->isPending() && $this->isActive();
    }

    // ========================================
    // ROLE & PERMISSION METHODS
    // ========================================

    /**
     * Check if user has specific role
     * 
     * @param string $role Role name (e.g., 'Super Admin', 'Pengurus')
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        // Shield provides inGroup() method
        return $this->inGroup($role);
    }

    /**
     * Check if user has any of the given roles
     * 
     * @param array $roles Array of role names
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given roles
     * 
     * @param array $roles Array of role names
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user has specific permission
     * 
     * @param string $permission Permission key (e.g., 'member.manage')
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Shield provides can() method
        return $this->can($permission);
    }

    /**
     * Check if user has any of the given permissions
     * 
     * @param array $permissions Array of permission keys
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is Super Admin
     * 
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Check if user is Pengurus (Pengurus or Super Admin)
     * 
     * @return bool
     */
    public function isPengurus(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Pengurus']);
    }

    /**
     * Check if user is Koordinator Wilayah
     * 
     * @return bool
     */
    public function isKoordinatorWilayah(): bool
    {
        return $this->hasRole('Koordinator Wilayah');
    }

    /**
     * Check if user is regular Anggota
     * 
     * @return bool
     */
    public function isAnggota(): bool
    {
        return $this->hasRole('Anggota');
    }

    /**
     * Get user's primary role
     * Returns the first/highest role
     * 
     * @return string|null
     */
    public function getPrimaryRole(): ?string
    {
        $groups = $this->getGroups();
        return !empty($groups) ? $groups[0] : null;
    }

    // ========================================
    // PROFILE RELATED METHODS
    // ========================================

    /**
     * Get full name from member profile
     * Returns username if profile not available
     * 
     * @return string
     */
    public function getFullName(): string
    {
        // Check if profile relationship is loaded
        if (isset($this->attributes['full_name'])) {
            return $this->attributes['full_name'];
        }

        // Fallback to username
        return $this->username ?? 'Unknown User';
    }

    /**
     * Get membership number from member profile
     * 
     * @return string|null
     */
    public function getMembershipNumber(): ?string
    {
        return $this->attributes['member_number'] ?? null;
    }

    /**
     * Get email from identities
     * 
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email ?? null;
    }

    /**
     * Get phone number from member profile
     * 
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Get WhatsApp number from member profile
     * 
     * @return string|null
     */
    public function getWhatsApp(): ?string
    {
        return $this->attributes['whatsapp'] ?? null;
    }

    /**
     * Get photo path from member profile
     * 
     * @return string|null
     */
    public function getPhotoPath(): ?string
    {
        return $this->attributes['photo_path'] ?? null;
    }

    /**
     * Get photo URL with default avatar fallback
     * 
     * @return string
     */
    public function getPhotoUrl(): string
    {
        $photoPath = $this->getPhotoPath();

        if ($photoPath && file_exists(FCPATH . $photoPath)) {
            return base_url($photoPath);
        }

        // Default avatar based on gender
        $gender = $this->attributes['gender'] ?? 'Laki-laki';
        $defaultAvatar = $gender === 'Perempuan' ? 'female-avatar.png' : 'male-avatar.png';

        return base_url('assets/images/avatars/' . $defaultAvatar);
    }

    // ========================================
    // DISPLAY METHODS
    // ========================================

    /**
     * Get user display name
     * Priority: Full Name > Username > Email
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        if (isset($this->attributes['full_name']) && !empty($this->attributes['full_name'])) {
            return $this->attributes['full_name'];
        }

        if (!empty($this->username)) {
            return $this->username;
        }

        return $this->getEmail() ?? 'Unknown User';
    }

    /**
     * Get user status badge label
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        if ($this->isActive()) {
            return 'Aktif';
        }

        if ($this->isPending()) {
            return 'Menunggu Verifikasi';
        }

        return 'Nonaktif';
    }

    /**
     * Get user status badge class for CSS
     * 
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        if ($this->isActive()) {
            return 'badge-success';
        }

        if ($this->isPending()) {
            return 'badge-warning';
        }

        return 'badge-danger';
    }

    /**
     * Get role badge class for CSS
     * 
     * @return string
     */
    public function getRoleBadgeClass(): string
    {
        $role = $this->getPrimaryRole();

        return match ($role) {
            'Super Admin' => 'badge-dark',
            'Pengurus' => 'badge-primary',
            'Koordinator Wilayah' => 'badge-info',
            'Anggota' => 'badge-success',
            'Calon Anggota' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get user ID as integer
     * 
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * Get username
     * 
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username ?? '';
    }

    /**
     * Check if user has profile data loaded
     * 
     * @return bool
     */
    public function hasProfileData(): bool
    {
        return isset($this->attributes['full_name']) || isset($this->attributes['member_number']);
    }

    /**
     * Get user's join date
     * 
     * @return string|null
     */
    public function getJoinDate(): ?string
    {
        return $this->attributes['join_date'] ?? null;
    }

    /**
     * Get formatted join date
     * 
     * @param string $format Date format (default: 'd F Y')
     * @return string|null
     */
    public function getFormattedJoinDate(string $format = 'd F Y'): ?string
    {
        $joinDate = $this->getJoinDate();

        if (!$joinDate) {
            return null;
        }

        return date($format, strtotime($joinDate));
    }

    /**
     * Check if user was created today
     * 
     * @return bool
     */
    public function isNewToday(): bool
    {
        if (!isset($this->created_at)) {
            return false;
        }

        return date('Y-m-d', strtotime($this->created_at)) === date('Y-m-d');
    }

    /**
     * Get days since registration
     * 
     * @return int
     */
    public function getDaysSinceRegistration(): int
    {
        if (!isset($this->created_at)) {
            return 0;
        }

        $createdDate = strtotime($this->created_at);
        $today = time();

        return (int) floor(($today - $createdDate) / 86400);
    }
}
