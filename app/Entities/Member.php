<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * Member Entity
 * 
 * Representasi object-oriented dari member profile
 * Menyediakan business logic methods untuk data anggota
 * 
 * @package App\Entities
 * @author  SPK Development Team
 * @version 1.0.0
 */
class Member extends Entity
{
    /**
     * Data mapping (if column names differ from property names)
     */
    protected $datamap = [];

    /**
     * Define date fields for automatic Time conversion
     */
    protected $dates = [
        'birth_date',
        'join_date',
        'start_date',
        'verified_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Type casting for properties
     */
    protected $casts = [
        'id'                    => 'integer',
        'user_id'               => 'integer',
        'province_id'           => '?integer',
        'regency_id'            => '?integer',
        'district_id'           => '?integer',
        'village_id'            => '?integer',
        'employment_status_id'  => '?integer',
        'salary_range_id'       => '?integer',
        'university_id'         => '?integer',
        'study_program_id'      => '?integer',
        'verified_by'           => '?integer',
        'basic_salary'          => '?decimal',
    ];

    // ========================================
    // NAME & IDENTITY METHODS
    // ========================================

    /**
     * Get full name (already formatted)
     * 
     * @return string
     */
    public function getFullName(): string
    {
        return $this->attributes['full_name'] ?? 'Unknown Member';
    }

    /**
     * Get full name with title
     * 
     * @param string $title Title to prepend (e.g., 'Sdr.', 'Ibu', 'Bapak')
     * @return string
     */
    public function getFullNameWithTitle(string $title = ''): string
    {
        if (empty($title)) {
            $title = $this->getGender() === 'Perempuan' ? 'Ibu' : 'Bapak';
        }

        return $title . ' ' . $this->getFullName();
    }

    /**
     * Get membership number
     * 
     * @return string|null
     */
    public function getMemberNumber(): ?string
    {
        return $this->attributes['member_number'] ?? null;
    }

    /**
     * Get NIK (Nomor Induk Kependudukan)
     * 
     * @return string|null
     */
    public function getNik(): ?string
    {
        return $this->attributes['nik'] ?? null;
    }

    /**
     * Get NIDN/NIP
     * 
     * @return string|null
     */
    public function getNidnNip(): ?string
    {
        return $this->attributes['nidn_nip'] ?? null;
    }

    /**
     * Get employee number
     * 
     * @return string|null
     */
    public function getEmployeeNumber(): ?string
    {
        return $this->attributes['employee_number'] ?? null;
    }

    // ========================================
    // PERSONAL INFO METHODS
    // ========================================

    /**
     * Get gender
     * 
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->attributes['gender'] ?? null;
    }

    /**
     * Get gender label in Indonesian
     * 
     * @return string
     */
    public function getGenderLabel(): string
    {
        return $this->getGender() === 'Perempuan' ? 'Perempuan' : 'Laki-laki';
    }

    /**
     * Check if member is male
     * 
     * @return bool
     */
    public function isMale(): bool
    {
        return $this->getGender() === 'Laki-laki';
    }

    /**
     * Check if member is female
     * 
     * @return bool
     */
    public function isFemale(): bool
    {
        return $this->getGender() === 'Perempuan';
    }

    /**
     * Get birth place
     * 
     * @return string|null
     */
    public function getBirthPlace(): ?string
    {
        return $this->attributes['birth_place'] ?? null;
    }

    /**
     * Get birth date
     * 
     * @return Time|null
     */
    public function getBirthDate(): ?Time
    {
        return $this->attributes['birth_date'] ?? null;
    }

    /**
     * Get formatted birth date
     * 
     * @param string $format Date format (default: 'd F Y')
     * @return string|null
     */
    public function getFormattedBirthDate(string $format = 'd F Y'): ?string
    {
        $birthDate = $this->getBirthDate();

        if (!$birthDate) {
            return null;
        }

        return $birthDate->toLocalizedString($format);
    }

    /**
     * Get place and date of birth (TTL format)
     * 
     * @return string|null
     */
    public function getBirthInfo(): ?string
    {
        $place = $this->getBirthPlace();
        $date = $this->getFormattedBirthDate();

        if (!$place && !$date) {
            return null;
        }

        if (!$place) {
            return $date;
        }

        if (!$date) {
            return $place;
        }

        return $place . ', ' . $date;
    }

    /**
     * Calculate age from birth date
     * 
     * @return int|null Age in years
     */
    public function getAge(): ?int
    {
        $birthDate = $this->getBirthDate();

        if (!$birthDate) {
            return null;
        }

        return $birthDate->difference(Time::now())->getYears();
    }

    // ========================================
    // CONTACT METHODS
    // ========================================

    /**
     * Get phone number
     * 
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Get WhatsApp number
     * 
     * @return string|null
     */
    public function getWhatsApp(): ?string
    {
        return $this->attributes['whatsapp'] ?? null;
    }

    /**
     * Get formatted WhatsApp number for URL
     * 
     * @return string|null
     */
    public function getWhatsAppUrl(): ?string
    {
        $wa = $this->getWhatsApp();

        if (!$wa) {
            return null;
        }

        // Remove non-numeric characters
        $wa = preg_replace('/[^0-9]/', '', $wa);

        // Add 62 prefix if starts with 0
        if (substr($wa, 0, 1) === '0') {
            $wa = '62' . substr($wa, 1);
        }

        return 'https://wa.me/' . $wa;
    }

    /**
     * Get address
     * 
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->attributes['address'] ?? null;
    }

    // ========================================
    // LOCATION METHODS
    // ========================================

    /**
     * Get province name (from joined data)
     * 
     * @return string|null
     */
    public function getProvinceName(): ?string
    {
        return $this->attributes['province_name'] ?? null;
    }

    /**
     * Get regency name (from joined data)
     * 
     * @return string|null
     */
    public function getRegencyName(): ?string
    {
        return $this->attributes['regency_name'] ?? null;
    }

    /**
     * Get district name (from joined data)
     * 
     * @return string|null
     */
    public function getDistrictName(): ?string
    {
        return $this->attributes['district_name'] ?? null;
    }

    /**
     * Get village name (from joined data)
     * 
     * @return string|null
     */
    public function getVillageName(): ?string
    {
        return $this->attributes['village_name'] ?? null;
    }

    /**
     * Get complete address with location
     * 
     * @return string|null
     */
    public function getCompleteAddress(): ?string
    {
        $parts = array_filter([
            $this->getAddress(),
            $this->getVillageName(),
            $this->getDistrictName(),
            $this->getRegencyName(),
            $this->getProvinceName(),
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    /**
     * Get region name (for Koordinator Wilayah scope)
     * Alias for province name
     * 
     * @return string|null
     */
    public function getRegionName(): ?string
    {
        return $this->getProvinceName();
    }

    // ========================================
    // EMPLOYMENT METHODS
    // ========================================

    /**
     * Get employment status name (from joined data)
     * 
     * @return string|null
     */
    public function getEmploymentStatusName(): ?string
    {
        return $this->attributes['employment_status_name'] ?? null;
    }

    /**
     * Get salary range name (from joined data)
     * 
     * @return string|null
     */
    public function getSalaryRangeName(): ?string
    {
        return $this->attributes['salary_range_name'] ?? null;
    }

    /**
     * Get basic salary
     * 
     * @return float|null
     */
    public function getBasicSalary(): ?float
    {
        return $this->attributes['basic_salary'] ?? null;
    }

    /**
     * Get formatted basic salary (Rupiah)
     * 
     * @return string|null
     */
    public function getFormattedSalary(): ?string
    {
        $salary = $this->getBasicSalary();

        if (!$salary) {
            return null;
        }

        return 'Rp ' . number_format($salary, 0, ',', '.');
    }

    /**
     * Get start date (tanggal mulai bekerja)
     * 
     * @return Time|null
     */
    public function getStartDate(): ?Time
    {
        return $this->attributes['start_date'] ?? null;
    }

    /**
     * Get formatted start date
     * 
     * @param string $format Date format (default: 'd F Y')
     * @return string|null
     */
    public function getFormattedStartDate(string $format = 'd F Y'): ?string
    {
        $startDate = $this->getStartDate();

        if (!$startDate) {
            return null;
        }

        return $startDate->toLocalizedString($format);
    }

    /**
     * Get years of service
     * 
     * @return int|null
     */
    public function getYearsOfService(): ?int
    {
        $startDate = $this->getStartDate();

        if (!$startDate) {
            return null;
        }

        return $startDate->difference(Time::now())->getYears();
    }

    // ========================================
    // UNIVERSITY METHODS
    // ========================================

    /**
     * Get university name (from joined data)
     * 
     * @return string|null
     */
    public function getUniversityName(): ?string
    {
        return $this->attributes['university_name'] ?? null;
    }

    /**
     * Get study program name (from joined data)
     * 
     * @return string|null
     */
    public function getStudyProgramName(): ?string
    {
        return $this->attributes['study_program_name'] ?? null;
    }

    /**
     * Get faculty
     * 
     * @return string|null
     */
    public function getFaculty(): ?string
    {
        return $this->attributes['faculty'] ?? null;
    }

    /**
     * Get department
     * 
     * @return string|null
     */
    public function getDepartment(): ?string
    {
        return $this->attributes['department'] ?? null;
    }

    /**
     * Get education level
     * 
     * @return string|null
     */
    public function getEducationLevel(): ?string
    {
        return $this->attributes['education_level'] ?? null;
    }

    /**
     * Get expertise/specialization
     * 
     * @return string|null
     */
    public function getExpertise(): ?string
    {
        return $this->attributes['expertise'] ?? null;
    }

    /**
     * Get research interest
     * 
     * @return string|null
     */
    public function getResearchInterest(): ?string
    {
        return $this->attributes['research_interest'] ?? null;
    }

    // ========================================
    // MEMBERSHIP STATUS METHODS
    // ========================================

    /**
     * Get join date (tanggal bergabung SPK)
     * 
     * @return Time|null
     */
    public function getJoinDate(): ?Time
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

        return $joinDate->toLocalizedString($format);
    }

    /**
     * Get membership duration in years
     * 
     * @return int|null
     */
    public function getMembershipDuration(): ?int
    {
        $joinDate = $this->getJoinDate();

        if (!$joinDate) {
            return null;
        }

        return $joinDate->difference(Time::now())->getYears();
    }

    /**
     * Get membership status
     * 
     * @return string|null
     */
    public function getMembershipStatus(): ?string
    {
        return $this->attributes['membership_status'] ?? null;
    }

    /**
     * Check if member is verified
     * 
     * @return bool
     */
    public function isVerified(): bool
    {
        return !empty($this->attributes['verified_at']);
    }

    /**
     * Check if member is pending verification
     * 
     * @return bool
     */
    public function isPending(): bool
    {
        return !$this->isVerified();
    }

    /**
     * Get verified date
     * 
     * @return Time|null
     */
    public function getVerifiedAt(): ?Time
    {
        return $this->attributes['verified_at'] ?? null;
    }

    /**
     * Get verified by user ID
     * 
     * @return int|null
     */
    public function getVerifiedBy(): ?int
    {
        return $this->attributes['verified_by'] ?? null;
    }

    /**
     * Check if member has paid dues
     * Note: This is a placeholder - actual implementation needs PaymentModel integration
     * 
     * @return bool
     */
    public function hasPaidDues(): bool
    {
        // TODO: Implement actual payment check from payments table
        // For now, assume verified members have paid
        return $this->isVerified();
    }

    /**
     * Check if member has paid dues for current month
     * Note: This is a placeholder - actual implementation needs PaymentModel integration
     * 
     * @return bool
     */
    public function hasPaidCurrentMonthDues(): bool
    {
        // TODO: Implement actual payment check for current month
        // Query payments table for current month payment
        return $this->isVerified();
    }

    // ========================================
    // FILE & DOCUMENT METHODS
    // ========================================

    /**
     * Get photo path
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
        $defaultAvatar = $this->isFemale() ? 'female-avatar.png' : 'male-avatar.png';

        return base_url('assets/images/avatars/' . $defaultAvatar);
    }

    /**
     * Get CV path
     * 
     * @return string|null
     */
    public function getCvPath(): ?string
    {
        return $this->attributes['cv_path'] ?? null;
    }

    /**
     * Get CV URL
     * 
     * @return string|null
     */
    public function getCvUrl(): ?string
    {
        $cvPath = $this->getCvPath();

        if (!$cvPath) {
            return null;
        }

        return base_url($cvPath);
    }

    /**
     * Get ID card path
     * 
     * @return string|null
     */
    public function getIdCardPath(): ?string
    {
        return $this->attributes['id_card_path'] ?? null;
    }

    /**
     * Get ID card URL
     * 
     * @return string|null
     */
    public function getIdCardUrl(): ?string
    {
        $idCardPath = $this->getIdCardPath();

        if (!$idCardPath) {
            return null;
        }

        return base_url($idCardPath);
    }

    /**
     * Check if member has uploaded photo
     * 
     * @return bool
     */
    public function hasPhoto(): bool
    {
        return !empty($this->getPhotoPath());
    }

    /**
     * Check if member has uploaded CV
     * 
     * @return bool
     */
    public function hasCv(): bool
    {
        return !empty($this->getCvPath());
    }

    /**
     * Check if member has uploaded ID card
     * 
     * @return bool
     */
    public function hasIdCard(): bool
    {
        return !empty($this->getIdCardPath());
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get notes
     * 
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->attributes['notes'] ?? null;
    }

    /**
     * Check if profile is complete
     * Basic required fields check
     * 
     * @return bool
     */
    public function isProfileComplete(): bool
    {
        $requiredFields = [
            'full_name',
            'gender',
            'birth_place',
            'birth_date',
            'phone',
            'address',
            'province_id',
            'university_id'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->attributes[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get profile completion percentage
     * 
     * @return int Percentage (0-100)
     */
    public function getProfileCompletionPercentage(): int
    {
        $allFields = [
            'full_name',
            'nik',
            'nidn_nip',
            'gender',
            'birth_place',
            'birth_date',
            'phone',
            'whatsapp',
            'address',
            'province_id',
            'regency_id',
            'employment_status_id',
            'salary_range_id',
            'university_id',
            'study_program_id',
            'faculty',
            'expertise',
            'photo_path'
        ];

        $filledFields = 0;

        foreach ($allFields as $field) {
            if (!empty($this->attributes[$field])) {
                $filledFields++;
            }
        }

        return (int) round(($filledFields / count($allFields)) * 100);
    }

    /**
     * Get display name for lists
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        $name = $this->getFullName();
        $memberNumber = $this->getMemberNumber();

        if ($memberNumber) {
            return $name . ' (' . $memberNumber . ')';
        }

        return $name;
    }
}
