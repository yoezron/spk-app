<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MemberProfileModel
 * 
 * Model untuk mengelola data profil anggota SPK
 * Menyimpan data lengkap anggota termasuk data kepegawaian dan kampus
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MemberProfileModel extends Model
{
    protected $table            = 'member_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'member_number',
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
        'basic_salary',
        'university_id',
        'study_program_id',
        'faculty',
        'department',
        'employee_number',
        'start_date',
        'appointment_letter_number',
        'expertise',
        'research_interest',
        'education_level',
        'photo_path',
        'payment_proof_path',
        'cv_path',
        'id_card_path',
        'join_date',
        'membership_status',
        'verified_at',
        'verified_by',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'     => 'required|is_natural_no_zero|is_unique[member_profiles.user_id,id,{id}]',
        'full_name'   => 'required|min_length[3]|max_length[255]',
        'gender'      => 'permit_empty|in_list[Laki-laki,Perempuan]',
        'phone'       => 'permit_empty|max_length[20]',
        'whatsapp'    => 'permit_empty|max_length[20]',
        'address'     => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required'   => 'User ID harus diisi',
            'is_unique'  => 'User sudah memiliki profil',
        ],
        'full_name' => [
            'required'   => 'Nama lengkap harus diisi',
            'min_length' => 'Nama lengkap minimal 3 karakter',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $beforeUpdate   = [];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get profile with user data
     * 
     * @return object
     */
    public function withUser()
    {
        return $this->select('member_profiles.*, users.username, users.active')
            ->join('users', 'users.id = member_profiles.user_id', 'left');
    }

    /**
     * Get profile with province
     * 
     * @return object
     */
    public function withProvince()
    {
        return $this->select('member_profiles.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left');
    }

    /**
     * Get profile with university
     * 
     * @return object
     */
    public function withUniversity()
    {
        return $this->select('member_profiles.*, universities.name as university_name')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left');
    }

    /**
     * Get profile with employment status
     * 
     * @return object
     */
    public function withEmploymentStatus()
    {
        return $this->select('member_profiles.*, employment_statuses.name as employment_status_name')
            ->join('employment_statuses', 'employment_statuses.id = member_profiles.employment_status_id', 'left');
    }

    /**
     * Get profile with all relations
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('member_profiles.*')
            ->select('users.username, users.active')
            ->select('provinces.name as province_name')
            ->select('universities.name as university_name')
            ->select('study_programs.name as study_program_name')
            ->select('employment_statuses.name as employment_status_name')
            ->select('salary_ranges.name as salary_range_name')
            ->join('users', 'users.id = member_profiles.user_id', 'left')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->join('study_programs', 'study_programs.id = member_profiles.study_program_id', 'left')
            ->join('employment_statuses', 'employment_statuses.id = member_profiles.employment_status_id', 'left')
            ->join('salary_ranges', 'salary_ranges.id = member_profiles.salary_range_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get active members only
     * 
     * @return object
     */
    public function activeMembers()
    {
        return $this->where('membership_status', 'active');
    }

    /**
     * Get pending members
     * 
     * @return object
     */
    public function pendingMembers()
    {
        return $this->where('membership_status', 'pending');
    }

    /**
     * Get verified members
     * 
     * @return object
     */
    public function verifiedMembers()
    {
        return $this->where('verified_at IS NOT NULL');
    }

    /**
     * Get members by province
     * 
     * @param int $provinceId Province ID
     * @return object
     */
    public function byProvince(int $provinceId)
    {
        return $this->where('province_id', $provinceId);
    }

    /**
     * Get members by university
     * 
     * @param int $universityId University ID
     * @return object
     */
    public function byUniversity(int $universityId)
    {
        return $this->where('university_id', $universityId);
    }

    /**
     * Get members by employment status
     * 
     * @param int $employmentStatusId Employment Status ID
     * @return object
     */
    public function byEmploymentStatus(int $employmentStatusId)
    {
        return $this->where('employment_status_id', $employmentStatusId);
    }

    /**
     * Search members by name, member number, or NIDN/NIP
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('full_name', $keyword)
            ->orLike('member_number', $keyword)
            ->orLike('nidn_nip', $keyword)
            ->orLike('nik', $keyword)
            ->groupEnd();
    }

    // ========================================
    // CUSTOM METHODS
    // ========================================

    /**
     * Find member by user ID
     * 
     * @param int $userId User ID
     * @return object|null
     */
    public function findByUserId(int $userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Find member by member number
     * 
     * @param string $memberNumber Member number
     * @return object|null
     */
    public function findByMemberNumber(string $memberNumber)
    {
        return $this->where('member_number', $memberNumber)->first();
    }

    /**
     * Find member by NIDN/NIP
     * 
     * @param string $nidnNip NIDN or NIP
     * @return object|null
     */
    public function findByNidnNip(string $nidnNip)
    {
        return $this->where('nidn_nip', $nidnNip)->first();
    }

    /**
     * Generate unique member number
     * Format: SPK-YYYY-XXXXX (e.g., SPK-2025-00001)
     * 
     * @return string
     */
    public function generateMemberNumber(): string
    {
        $year = date('Y');
        $prefix = "SPK-{$year}-";

        // Get last member number for current year
        $lastMember = $this->like('member_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastMember) {
            // Extract number part and increment
            $lastNumber = (int) substr($lastMember->member_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // First member of the year
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Verify member profile
     * 
     * @param int $profileId Profile ID
     * @param int $verifiedBy User ID who verified
     * @return bool
     */
    public function verifyMember(int $profileId, int $verifiedBy): bool
    {
        return $this->update($profileId, [
            'membership_status' => 'active',
            'verified_at'       => date('Y-m-d H:i:s'),
            'verified_by'       => $verifiedBy,
        ]);
    }

    /**
     * Suspend member
     * 
     * @param int $profileId Profile ID
     * @param string|null $notes Suspension notes
     * @return bool
     */
    public function suspendMember(int $profileId, ?string $notes = null): bool
    {
        $data = ['membership_status' => 'suspended'];

        if ($notes) {
            $data['notes'] = $notes;
        }

        return $this->update($profileId, $data);
    }

    /**
     * Reactivate member
     * 
     * @param int $profileId Profile ID
     * @return bool
     */
    public function reactivateMember(int $profileId): bool
    {
        return $this->update($profileId, [
            'membership_status' => 'active',
            'notes'             => null,
        ]);
    }

    /**
     * Update member photo
     * 
     * @param int $profileId Profile ID
     * @param string $photoPath Photo file path
     * @return bool
     */
    public function updatePhoto(int $profileId, string $photoPath): bool
    {
        return $this->update($profileId, ['photo_path' => $photoPath]);
    }

    /**
     * Get members count by status
     * 
     * @param string $status Membership status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return $this->where('membership_status', $status)->countAllResults(false);
    }

    /**
     * Get members joined in date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return object
     */
    public function joinedBetween(string $startDate, string $endDate)
    {
        return $this->where('join_date >=', $startDate)
            ->where('join_date <=', $endDate);
    }

    /**
     * Get recently joined members
     * 
     * @param int $days Number of days
     * @param int $limit Limit results
     * @return array
     */
    public function recentlyJoined(int $days = 7, int $limit = 10): array
    {
        return $this->where('join_date >=', date('Y-m-d', strtotime("-{$days} days")))
            ->orderBy('join_date', 'DESC')
            ->limit($limit)
            ->find();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get member statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total'              => $this->countAllResults(false),
            'active'             => $this->countByStatus('active'),
            'pending'            => $this->countByStatus('pending'),
            'suspended'          => $this->countByStatus('suspended'),
            'verified'           => $this->where('verified_at IS NOT NULL')->countAllResults(false),
            'not_verified'       => $this->where('verified_at IS NULL')->countAllResults(false),
            'joined_today'       => $this->where('join_date', date('Y-m-d'))->countAllResults(false),
            'joined_this_week'   => $this->joinedBetween(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'))->countAllResults(false),
            'joined_this_month'  => $this->joinedBetween(date('Y-m-01'), date('Y-m-d'))->countAllResults(false),
            'joined_this_year'   => $this->joinedBetween(date('Y-01-01'), date('Y-m-d'))->countAllResults(false),
        ];
    }

    /**
     * Get members distribution by province
     * 
     * @return array
     */
    public function getProvinceDistribution(): array
    {
        return $this->select('provinces.name as province_name, COUNT(member_profiles.id) as total')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->groupBy('member_profiles.province_id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get members distribution by university
     * 
     * @param int $limit Limit results
     * @return array
     */
    public function getUniversityDistribution(int $limit = 20): array
    {
        return $this->select('universities.name as university_name, COUNT(member_profiles.id) as total')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->groupBy('member_profiles.university_id')
            ->orderBy('total', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get members distribution by employment status
     * 
     * @return array
     */
    public function getEmploymentDistribution(): array
    {
        return $this->select('employment_statuses.name as status_name, COUNT(member_profiles.id) as total')
            ->join('employment_statuses', 'employment_statuses.id = member_profiles.employment_status_id', 'left')
            ->groupBy('member_profiles.employment_status_id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Before insert callback - generate member number
     * 
     * @param array $data
     * @return array
     */
    // protected function generateMemberNumber(array $data): array
    // {
    //     if (!isset($data['data']['member_number']) || empty($data['data']['member_number'])) {
    //         $data['data']['member_number'] = $this->generateMemberNumber();
    //     }

    //     return $data;
    // }
}
