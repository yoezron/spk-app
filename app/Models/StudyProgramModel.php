<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * StudyProgramModel
 * 
 * Model untuk mengelola data program studi
 * Digunakan untuk master data prodi dan relasi dengan perguruan tinggi & anggota
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class StudyProgramModel extends Model
{
    protected $table            = 'study_programs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'university_id',
        'name',
        'code',
        'level',
        'faculty',
        'accreditation',
        'description',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'university_id' => 'required|integer|is_not_unique[universities.id]',
        'name'          => 'required|min_length[3]|max_length[255]',
        'code'          => 'permit_empty|max_length[50]|is_unique[study_programs.code,id,{id}]',
        'level'         => 'required|in_list[D3,D4,S1,S2,S3,Profesi,Sp-1,Sp-2]',
        'accreditation' => 'permit_empty|in_list[A,B,C,Unggul,Baik Sekali,Baik,Belum Terakreditasi]',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'university_id' => [
            'required'      => 'Perguruan tinggi harus dipilih',
            'integer'       => 'ID perguruan tinggi tidak valid',
            'is_not_unique' => 'Perguruan tinggi tidak ditemukan',
        ],
        'name' => [
            'required'   => 'Nama program studi harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 255 karakter',
        ],
        'code' => [
            'is_unique' => 'Kode program studi sudah digunakan',
        ],
        'level' => [
            'required' => 'Jenjang harus dipilih',
            'in_list'  => 'Jenjang tidak valid',
        ],
        'accreditation' => [
            'in_list' => 'Akreditasi tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateCode'];
    protected $beforeUpdate   = ['generateCode'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get study program with university data
     * 
     * @return object
     */
    public function withUniversity()
    {
        return $this->select('study_programs.*, universities.name as university_name, universities.short_name as university_short_name')
            ->join('universities', 'universities.id = study_programs.university_id', 'left');
    }

    /**
     * Get study program with university and type
     * 
     * @return object
     */
    public function withUniversityComplete()
    {
        return $this->select('study_programs.*')
            ->select('universities.name as university_name, universities.short_name as university_short_name')
            ->select('university_types.name as university_type_name, university_types.category as university_category')
            ->select('provinces.name as province_name')
            ->join('universities', 'universities.id = study_programs.university_id', 'left')
            ->join('university_types', 'university_types.id = universities.university_type_id', 'left')
            ->join('provinces', 'provinces.id = universities.province_id', 'left');
    }

    /**
     * Get study program with members count
     * 
     * @return object
     */
    public function withMembersCount()
    {
        return $this->select('study_programs.*')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.study_program_id = study_programs.id) as members_count');
    }

    /**
     * Get study program with complete statistics
     * 
     * @return object
     */
    public function withStats()
    {
        return $this->select('study_programs.*')
            ->select('universities.name as university_name, universities.short_name as university_short_name')
            ->select('provinces.name as province_name')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.study_program_id = study_programs.id) as members_count')
            ->select('(SELECT COUNT(*) FROM member_profiles WHERE member_profiles.study_program_id = study_programs.id AND member_profiles.membership_status = "active") as active_members_count')
            ->join('universities', 'universities.id = study_programs.university_id', 'left')
            ->join('provinces', 'provinces.id = universities.province_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get study programs by university
     * 
     * @param int $universityId University ID
     * @return object
     */
    public function byUniversity(int $universityId)
    {
        return $this->where('university_id', $universityId);
    }

    /**
     * Get study programs by level
     * 
     * @param string $level Level: D3, D4, S1, S2, S3, Profesi, etc
     * @return object
     */
    public function byLevel(string $level)
    {
        return $this->where('level', $level);
    }

    /**
     * Get S1 programs only
     * 
     * @return object
     */
    public function s1()
    {
        return $this->where('level', 'S1');
    }

    /**
     * Get S2 programs only
     * 
     * @return object
     */
    public function s2()
    {
        return $this->where('level', 'S2');
    }

    /**
     * Get S3 programs only
     * 
     * @return object
     */
    public function s3()
    {
        return $this->where('level', 'S3');
    }

    /**
     * Get diploma programs (D3, D4)
     * 
     * @return object
     */
    public function diploma()
    {
        return $this->whereIn('level', ['D3', 'D4']);
    }

    /**
     * Get study programs by accreditation
     * 
     * @param string $accreditation Accreditation level
     * @return object
     */
    public function byAccreditation(string $accreditation)
    {
        return $this->where('accreditation', $accreditation);
    }

    /**
     * Get active study programs only
     * 
     * @return object
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /**
     * Get study programs that have members
     * 
     * @return object
     */
    public function hasMembers()
    {
        return $this->select('study_programs.*')
            ->join('member_profiles', 'member_profiles.study_program_id = study_programs.id', 'inner')
            ->groupBy('study_programs.id');
    }

    /**
     * Search study programs
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('name', $keyword)
            ->orLike('code', $keyword)
            ->orLike('faculty', $keyword)
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
     * Get all study programs ordered by name
     * 
     * @return array
     */
    public function getAllOrdered(): array
    {
        return $this->active()->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get study programs by university ID
     * 
     * @param int $universityId University ID
     * @param string|null $level Filter by level (optional)
     * @return array
     */
    public function getByUniversityId(int $universityId, ?string $level = null): array
    {
        $builder = $this->where('university_id', $universityId)->active();

        if ($level) {
            $builder->where('level', $level);
        }

        return $builder->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get study program by name and university
     * 
     * @param string $name Program name
     * @param int $universityId University ID
     * @return object|null
     */
    public function getByNameAndUniversity(string $name, int $universityId)
    {
        return $this->where('name', $name)
            ->where('university_id', $universityId)
            ->first();
    }

    /**
     * Get study program by code
     * 
     * @param string $code Program code
     * @return object|null
     */
    public function getByCode(string $code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get study programs with statistics
     * 
     * @param array $filters Filters: university_id, level, accreditation, province_id
     * @return array
     */
    public function getAllWithStats(array $filters = []): array
    {
        $builder = $this->withStats();

        if (!empty($filters['university_id'])) {
            $builder->where('study_programs.university_id', $filters['university_id']);
        }

        if (!empty($filters['level'])) {
            $builder->where('study_programs.level', $filters['level']);
        }

        if (!empty($filters['accreditation'])) {
            $builder->where('study_programs.accreditation', $filters['accreditation']);
        }

        if (!empty($filters['province_id'])) {
            $builder->where('universities.province_id', $filters['province_id']);
        }

        if (isset($filters['is_active'])) {
            $builder->where('study_programs.is_active', $filters['is_active']);
        }

        return $builder->orderBy('study_programs.name', 'ASC')->findAll();
    }

    /**
     * Get top study programs by members count
     * 
     * @param int $limit Number of results
     * @param array $filters Optional filters
     * @return array
     */
    public function getTopByMembers(int $limit = 10, array $filters = []): array
    {
        $builder = $this->withMembersCount()->withUniversity();

        if (!empty($filters['university_id'])) {
            $builder->where('study_programs.university_id', $filters['university_id']);
        }

        if (!empty($filters['level'])) {
            $builder->where('study_programs.level', $filters['level']);
        }

        return $builder->orderBy('members_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get study programs for dropdown
     * Format: ['id' => 'name (level) - University']
     * 
     * @param int|null $universityId Filter by university (optional)
     * @param string|null $level Filter by level (optional)
     * @return array
     */
    public function getDropdown(?int $universityId = null, ?string $level = null): array
    {
        $builder = $this->withUniversity()->active()->orderBy('study_programs.name', 'ASC');

        if ($universityId) {
            $builder->where('study_programs.university_id', $universityId);
        }

        if ($level) {
            $builder->where('study_programs.level', $level);
        }

        $programs = $builder->findAll();

        $dropdown = [];
        foreach ($programs as $program) {
            $label = $program->name . ' (' . $program->level . ')';
            if (!empty($program->university_short_name)) {
                $label .= ' - ' . $program->university_short_name;
            }
            $dropdown[$program->id] = $label;
        }

        return $dropdown;
    }

    /**
     * Get study programs grouped by university
     * 
     * @param string|null $level Filter by level (optional)
     * @return array
     */
    public function getGroupedByUniversity(?string $level = null): array
    {
        $builder = $this->withUniversity()->active();

        if ($level) {
            $builder->where('study_programs.level', $level);
        }

        $programs = $builder->orderBy('universities.name', 'ASC')
            ->orderBy('study_programs.name', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($programs as $program) {
            $universityName = $program->university_name ?? 'Unknown';

            if (!isset($grouped[$universityName])) {
                $grouped[$universityName] = [];
            }

            $grouped[$universityName][] = $program;
        }

        return $grouped;
    }

    /**
     * Get study programs grouped by level
     * 
     * @param int|null $universityId Filter by university (optional)
     * @return array
     */
    public function getGroupedByLevel(?int $universityId = null): array
    {
        $builder = $this->active();

        if ($universityId) {
            $builder->where('university_id', $universityId);
        }

        $programs = $builder->orderBy('level', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($programs as $program) {
            $level = $program->level ?? 'Unknown';

            if (!isset($grouped[$level])) {
                $grouped[$level] = [];
            }

            $grouped[$level][] = $program;
        }

        return $grouped;
    }

    /**
     * Get members by study program
     * 
     * @param int $programId Study program ID
     * @param int $limit Number of results
     * @return array
     */
    public function getMembers(int $programId, int $limit = 100): array
    {
        return $this->db->table('member_profiles')
            ->select('member_profiles.*, users.username, users.active')
            ->join('users', 'users.id = member_profiles.user_id', 'left')
            ->where('member_profiles.study_program_id', $programId)
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get study program statistics
     * 
     * @param int $programId Study program ID
     * @return array
     */
    public function getStats(int $programId): array
    {
        $program = $this->withStats()->find($programId);

        if (!$program) {
            return [];
        }

        return [
            'program_name'         => $program->name,
            'program_code'         => $program->code,
            'level'                => $program->level,
            'faculty'              => $program->faculty,
            'accreditation'        => $program->accreditation,
            'university_name'      => $program->university_name,
            'province_name'        => $program->province_name,
            'members_count'        => $program->members_count ?? 0,
            'active_members_count' => $program->active_members_count ?? 0,
        ];
    }

    /**
     * Get count by level
     * 
     * @param int|null $universityId Filter by university (optional)
     * @return array
     */
    public function getCountByLevel(?int $universityId = null): array
    {
        $builder = $this->select('level, COUNT(*) as count')
            ->groupBy('level');

        if ($universityId) {
            $builder->where('university_id', $universityId);
        }

        $results = $builder->findAll();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result->level] = (int)$result->count;
        }

        return $counts;
    }

    /**
     * Get count by accreditation
     * 
     * @param int|null $universityId Filter by university (optional)
     * @return array
     */
    public function getCountByAccreditation(?int $universityId = null): array
    {
        $builder = $this->select('accreditation, COUNT(*) as count')
            ->groupBy('accreditation');

        if ($universityId) {
            $builder->where('university_id', $universityId);
        }

        $results = $builder->findAll();

        $counts = [];
        foreach ($results as $result) {
            $accreditation = $result->accreditation ?? 'Belum Terakreditasi';
            $counts[$accreditation] = (int)$result->count;
        }

        return $counts;
    }

    /**
     * Bulk insert study programs
     * 
     * @param array $programs Array of programs data
     * @return bool
     */
    public function bulkInsert(array $programs): bool
    {
        if (empty($programs)) {
            return false;
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        foreach ($programs as &$program) {
            $program['created_at'] = $now;
            $program['updated_at'] = $now;
        }

        return $this->insertBatch($programs);
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate program code if not provided
     * 
     * @param array $data
     * @return array
     */
    protected function generateCode(array $data): array
    {
        // Only generate if code is empty
        if (empty($data['data']['code']) && !empty($data['data']['name']) && !empty($data['data']['university_id'])) {
            // Get university short name
            $university = $this->db->table('universities')
                ->select('short_name')
                ->where('id', $data['data']['university_id'])
                ->get()
                ->getRow();

            if ($university) {
                // Generate code: UNIV_SHORT + LEVEL + random
                $level = $data['data']['level'] ?? 'S1';
                $name = strtoupper($data['data']['name']);
                $code = $university->short_name . '-' . $level . '-' . substr($name, 0, 3) . rand(10, 99);
                $data['data']['code'] = $code;
            }
        }

        return $data;
    }

    // ========================================
    // EXPORT & IMPORT
    // ========================================

    /**
     * Export study programs to array for Excel/CSV
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function exportData(array $filters = []): array
    {
        $programs = $this->getAllWithStats($filters);

        $export = [];
        foreach ($programs as $program) {
            $export[] = [
                'ID'              => $program->id,
                'Perguruan Tinggi' => $program->university_name,
                'Nama Prodi'      => $program->name,
                'Kode'            => $program->code,
                'Jenjang'         => $program->level,
                'Fakultas'        => $program->faculty,
                'Akreditasi'      => $program->accreditation,
                'Provinsi'        => $program->province_name,
                'Jumlah Anggota'  => $program->members_count ?? 0,
                'Anggota Aktif'   => $program->active_members_count ?? 0,
                'Status'          => $program->is_active ? 'Aktif' : 'Tidak Aktif',
            ];
        }

        return $export;
    }
}
