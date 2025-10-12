<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UniversityModel
 * 
 * Model untuk mengelola data perguruan tinggi
 * Digunakan untuk master data kampus dan relasi dengan anggota & program studi
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class UniversityModel extends Model
{
    protected $table            = 'universities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'short_name',
        'university_type_id',
        'province_id',
        'regency_id',
        'address',
        'phone',
        'email',
        'website',
        'accreditation',
        'npsn',
        'status',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'               => 'required|min_length[3]|max_length[255]',
        'short_name'         => 'permit_empty|max_length[50]',
        'university_type_id' => 'required|integer|is_not_unique[university_types.id]',
        'province_id'        => 'permit_empty|integer|is_not_unique[provinces.id]',
        'regency_id'         => 'permit_empty|integer|is_not_unique[regencies.id]',
        'email'              => 'permit_empty|valid_email',
        'website'            => 'permit_empty|valid_url_strict',
        'accreditation'      => 'permit_empty|in_list[A,B,C,Unggul,Baik Sekali,Baik]',
        'status'             => 'permit_empty|in_list[Aktif,Non-Aktif,Tutup]',
        'is_active'          => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama perguruan tinggi harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 255 karakter',
        ],
        'university_type_id' => [
            'required'      => 'Jenis PT harus dipilih',
            'integer'       => 'ID Jenis PT tidak valid',
            'is_not_unique' => 'Jenis PT tidak ditemukan',
        ],
        'email' => [
            'valid_email' => 'Format email tidak valid',
        ],
        'website' => [
            'valid_url_strict' => 'Format website tidak valid',
        ],
        'accreditation' => [
            'in_list' => 'Akreditasi tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateShortName'];
    protected $beforeUpdate   = ['generateShortName'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get university with type data
     * 
     * @return object
     */
    public function withType()
    {
        return $this->select('universities.*, university_types.name as type_name, university_types.category as type_category')
            ->join('university_types', 'university_types.id = universities.university_type_id', 'left');
    }

    /**
     * Get university with province data
     * 
     * @return object
     */
    public function withProvince()
    {
        return $this->select('universities.*, provinces.name as province_name')
            ->join('provinces', 'provinces.id = universities.province_id', 'left');
    }

    /**
     * Get university with regency data
     * 
     * @return object
     */
    public function withRegency()
    {
        return $this->select('universities.*, regencies.name as regency_name, regencies.type as regency_type')
            ->join('regencies', 'regencies.id = universities.regency_id', 'left');
    }

    /**
     * Get university with programs count
     * 
     * @return object
     */
    public function withProgramsCount()
    {
        return $this->select('universities.*')
            ->select('(SELECT COUNT(*) FROM study_programs WHERE study_programs.university_id = universities.id) as programs_count');
    }

    /**
     * Get university with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('universities.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.university_id = universities.id) as members_count');
    }

    /**
     * Get university with complete data
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('universities.*')
            ->select('university_types.name as type_name, university_types.category as type_category')
            ->select('provinces.name as province_name')
            ->select('regencies.name as regency_name, regencies.type as regency_type')
            ->join('university_types', 'university_types.id = universities.university_type_id', 'left')
            ->join('provinces', 'provinces.id = universities.province_id', 'left')
            ->join('regencies', 'regencies.id = universities.regency_id', 'left');
    }

    /**
     * Get university with complete statistics
     * 
     * @return object
     */
    public function withStats()
    {
        return $this->select('universities.*')
            ->select('university_types.name as type_name, university_types.category as type_category')
            ->select('provinces.name as province_name')
            ->select('(SELECT COUNT(*) FROM study_programs WHERE study_programs.university_id = universities.id) as programs_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.university_id = universities.id) as members_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.university_id = universities.id AND member_profiles.membership_status = "active") as active_members_count')
            ->join('university_types', 'university_types.id = universities.university_type_id', 'left')
            ->join('provinces', 'provinces.id = universities.province_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get universities by type ID
     * 
     * @param int $typeId University type ID
     * @return object
     */
    public function byType(int $typeId)
    {
        return $this->where('university_type_id', $typeId);
    }

    /**
     * Get universities by type category (Negeri/Swasta)
     * 
     * @param string $category Category: 'Negeri' or 'Swasta'
     * @return object
     */
    public function byCategory(string $category)
    {
        return $this->select('universities.*')
            ->join('university_types', 'university_types.id = universities.university_type_id', 'inner')
            ->where('university_types.category', $category);
    }

    /**
     * Get PTN (Perguruan Tinggi Negeri) only
     * 
     * @return object
     */
    public function negeri()
    {
        return $this->byCategory('Negeri');
    }

    /**
     * Get PTS (Perguruan Tinggi Swasta) only
     * 
     * @return object
     */
    public function swasta()
    {
        return $this->byCategory('Swasta');
    }

    /**
     * Get universities by province
     * 
     * @param int $provinceId Province ID
     * @return object
     */
    public function byProvince(int $provinceId)
    {
        return $this->where('province_id', $provinceId);
    }

    /**
     * Get universities by regency
     * 
     * @param int $regencyId Regency ID
     * @return object
     */
    public function byRegency(int $regencyId)
    {
        return $this->where('regency_id', $regencyId);
    }

    /**
     * Get universities by accreditation
     * 
     * @param string $accreditation Accreditation level
     * @return object
     */
    public function byAccreditation(string $accreditation)
    {
        return $this->where('accreditation', $accreditation);
    }

    /**
     * Get active universities only
     * 
     * @return object
     */
    public function active()
    {
        return $this->where('is_active', 1)
            ->where('status', 'Aktif');
    }

    /**
     * Get universities that have members
     * 
     * @return object
     */
    public function hasMembers()
    {
        return $this->select('universities.*')
            ->join('member_profiles', 'member_profiles.university_id = universities.id', 'inner')
            ->groupBy('universities.id');
    }

    /**
     * Search universities
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('name', $keyword)
            ->orLike('short_name', $keyword)
            ->orLike('npsn', $keyword)
            ->groupEnd();
    }

    /**
     * Order by name ascending
     * 
     * @return object
     */
    public function ordered()
    {
        return $this->orderBy('name', 'ASC');
    }

    // ========================================
    // CUSTOM METHODS
    // ========================================

    /**
     * Get all universities ordered by name
     * 
     * @return array
     */
    public function getAllOrdered(): array
    {
        return $this->active()->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get university by name
     * 
     * @param string $name University name
     * @return object|null
     */
    public function getByName(string $name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Get university by NPSN
     * 
     * @param string $npsn NPSN code
     * @return object|null
     */
    public function getByNPSN(string $npsn)
    {
        return $this->where('npsn', $npsn)->first();
    }

    /**
     * Get universities by province
     * 
     * @param int $provinceId Province ID
     * @return array
     */
    public function getByProvinceId(int $provinceId): array
    {
        return $this->where('province_id', $provinceId)
            ->active()
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get universities with statistics
     * 
     * @param array $filters Filters: type_id, province_id, category, accreditation
     * @return array
     */
    public function getAllWithStats(array $filters = []): array
    {
        $builder = $this->withStats();

        if (!empty($filters['type_id'])) {
            $builder->where('universities.university_type_id', $filters['type_id']);
        }

        if (!empty($filters['province_id'])) {
            $builder->where('universities.province_id', $filters['province_id']);
        }

        if (!empty($filters['category'])) {
            $builder->where('university_types.category', $filters['category']);
        }

        if (!empty($filters['accreditation'])) {
            $builder->where('universities.accreditation', $filters['accreditation']);
        }

        if (isset($filters['is_active'])) {
            $builder->where('universities.is_active', $filters['is_active']);
        }

        return $builder->orderBy('universities.name', 'ASC')->findAll();
    }

    /**
     * Get top universities by members count
     * 
     * @param int $limit Number of results
     * @param array $filters Optional filters
     * @return array
     */
    public function getTopByMembers(int $limit = 10, array $filters = []): array
    {
        $builder = $this->withMembersCount()->withComplete();

        if (!empty($filters['province_id'])) {
            $builder->where('universities.province_id', $filters['province_id']);
        }

        if (!empty($filters['category'])) {
            $builder->where('university_types.category', $filters['category']);
        }

        return $builder->orderBy('members_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get universities for dropdown
     * Format: ['id' => 'name (type)']
     * 
     * @param array $filters Optional filters: province_id, type_id, category
     * @return array
     */
    public function getDropdown(array $filters = []): array
    {
        $builder = $this->withType()->active()->orderBy('universities.name', 'ASC');

        if (!empty($filters['province_id'])) {
            $builder->where('universities.province_id', $filters['province_id']);
        }

        if (!empty($filters['type_id'])) {
            $builder->where('universities.university_type_id', $filters['type_id']);
        }

        if (!empty($filters['category'])) {
            $builder->where('university_types.category', $filters['category']);
        }

        $universities = $builder->findAll();

        $dropdown = [];
        foreach ($universities as $university) {
            $label = $university->name;
            if (!empty($university->type_name)) {
                $label .= ' (' . $university->type_name . ')';
            }
            $dropdown[$university->id] = $label;
        }

        return $dropdown;
    }

    /**
     * Get universities grouped by province
     * 
     * @param string|null $category Filter by category (Negeri/Swasta)
     * @return array
     */
    public function getGroupedByProvince(?string $category = null): array
    {
        $builder = $this->withComplete()->active();

        if ($category) {
            $builder->where('university_types.category', $category);
        }

        $universities = $builder->orderBy('provinces.name', 'ASC')
            ->orderBy('universities.name', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($universities as $university) {
            $provinceName = $university->province_name ?? 'Unknown';

            if (!isset($grouped[$provinceName])) {
                $grouped[$provinceName] = [];
            }

            $grouped[$provinceName][] = $university;
        }

        return $grouped;
    }

    /**
     * Get study programs by university
     * 
     * @param int $universityId University ID
     * @return array
     */
    public function getPrograms(int $universityId): array
    {
        return $this->db->table('study_programs')
            ->where('university_id', $universityId)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get members by university
     * 
     * @param int $universityId University ID
     * @param int $limit Number of results
     * @return array
     */
    public function getMembers(int $universityId, int $limit = 100): array
    {
        return $this->db->table('member_profiles')
            ->select('member_profiles.*, users.username, users.active')
            ->join('users', 'users.id = member_profiles.user_id', 'left')
            ->where('member_profiles.university_id', $universityId)
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get university statistics
     * 
     * @param int $universityId University ID
     * @return array
     */
    public function getStats(int $universityId): array
    {
        $university = $this->withStats()->find($universityId);

        if (!$university) {
            return [];
        }

        return [
            'university_name'      => $university->name,
            'short_name'           => $university->short_name,
            'type_name'            => $university->type_name,
            'type_category'        => $university->type_category,
            'province_name'        => $university->province_name,
            'accreditation'        => $university->accreditation,
            'programs_count'       => $university->programs_count ?? 0,
            'members_count'        => $university->members_count ?? 0,
            'active_members_count' => $university->active_members_count ?? 0,
        ];
    }

    /**
     * Get count by category (Negeri vs Swasta)
     * 
     * @param int|null $provinceId Filter by province
     * @return array
     */
    public function getCountByCategory(?int $provinceId = null): array
    {
        $builder = $this->select('university_types.category, COUNT(*) as count')
            ->join('university_types', 'university_types.id = universities.university_type_id', 'inner')
            ->groupBy('university_types.category');

        if ($provinceId) {
            $builder->where('universities.province_id', $provinceId);
        }

        $results = $builder->findAll();

        $counts = ['Negeri' => 0, 'Swasta' => 0];
        foreach ($results as $result) {
            $counts[$result->category] = (int)$result->count;
        }

        return $counts;
    }

    /**
     * Bulk insert universities
     * 
     * @param array $universities Array of universities data
     * @return bool
     */
    public function bulkInsert(array $universities): bool
    {
        if (empty($universities)) {
            return false;
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        foreach ($universities as &$university) {
            $university['created_at'] = $now;
            $university['updated_at'] = $now;
        }

        return $this->insertBatch($universities);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate short name if not provided
     * 
     * @param array $data
     * @return array
     */
    protected function generateShortName(array $data): array
    {
        // Only generate if short_name is empty
        if (empty($data['data']['short_name']) && !empty($data['data']['name'])) {
            // Extract acronym from name
            $words = explode(' ', $data['data']['name']);
            $shortName = '';

            foreach ($words as $word) {
                if (strlen($word) > 2 && !in_array(strtolower($word), ['dan', 'di', 'atau', 'dengan'])) {
                    $shortName .= strtoupper($word[0]);
                }
            }

            // Limit to 10 characters
            $data['data']['short_name'] = substr($shortName, 0, 10);
        }

        return $data;
    }

    // ========================================
    // EXPORT & IMPORT
    // ========================================

    /**
     * Export universities to array for Excel/CSV
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function exportData(array $filters = []): array
    {
        $universities = $this->getAllWithStats($filters);

        $export = [];
        foreach ($universities as $university) {
            $export[] = [
                'ID'              => $university->id,
                'Nama PT'         => $university->name,
                'Singkatan'       => $university->short_name,
                'Jenis PT'        => $university->type_name,
                'Kategori'        => $university->type_category,
                'Provinsi'        => $university->province_name,
                'Alamat'          => $university->address,
                'Telepon'         => $university->phone,
                'Email'           => $university->email,
                'Website'         => $university->website,
                'Akreditasi'      => $university->accreditation,
                'NPSN'            => $university->npsn,
                'Status'          => $university->status,
                'Jumlah Prodi'    => $university->programs_count ?? 0,
                'Jumlah Anggota'  => $university->members_count ?? 0,
                'Anggota Aktif'   => $university->active_members_count ?? 0,
            ];
        }

        return $export;
    }
}
