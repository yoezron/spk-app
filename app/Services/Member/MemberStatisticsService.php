<?php

namespace App\Services\Member;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use App\Models\ProvinceModel;
use App\Models\UniversityModel;

/**
 * MemberStatisticsService
 * 
 * Menghitung statistik dan analytics anggota
 * Menyediakan data untuk dashboard, reports, dan visualisasi
 * 
 * @package App\Services\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class MemberStatisticsService
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var UniversityModel
     */
    protected $universityModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
        $this->provinceModel = new ProvinceModel();
        $this->universityModel = new UniversityModel();
    }

    /**
     * Get total members count
     * 
     * @param array $filters Optional filters (status, province_id, university_id, etc)
     * @return int Total members
     */
    public function getTotalMembers(array $filters = []): int
    {
        $builder = $this->memberModel->builder();

        // Apply filters
        if (isset($filters['status'])) {
            $builder->where('membership_status', $filters['status']);
        }

        if (isset($filters['province_id'])) {
            $builder->where('province_id', $filters['province_id']);
        }

        if (isset($filters['university_id'])) {
            $builder->where('university_id', $filters['university_id']);
        }

        if (isset($filters['gender'])) {
            $builder->where('gender', $filters['gender']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get active members count
     * 
     * @return int Active members count
     */
    public function getActiveMembers(): int
    {
        return $this->getTotalMembers(['status' => 'active']);
    }

    /**
     * Get pending members count
     * 
     * @return int Pending members count
     */
    public function getPendingMembers(): int
    {
        return $this->getTotalMembers(['status' => 'pending']);
    }

    /**
     * Get rejected members count
     * 
     * @return int Rejected members count
     */
    public function getRejectedMembers(): int
    {
        return $this->getTotalMembers(['status' => 'rejected']);
    }

    /**
     * Get members breakdown by province
     * 
     * @param int|null $limit Limit results
     * @return array Province breakdown
     */
    public function getByProvince(?int $limit = null): array
    {
        $builder = $this->memberModel->builder();

        $query = $builder
            ->select('provinces.id, provinces.name, COUNT(member_profiles.id) as total')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->where('member_profiles.membership_status', 'active')
            ->groupBy('provinces.id, provinces.name')
            ->orderBy('total', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->getResultArray();
    }

    /**
     * Get members breakdown by university
     * 
     * @param int|null $limit Limit results
     * @return array University breakdown
     */
    public function getByUniversity(?int $limit = null): array
    {
        $builder = $this->memberModel->builder();

        $query = $builder
            ->select('universities.id, universities.name, COUNT(member_profiles.id) as total')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->where('member_profiles.membership_status', 'active')
            ->groupBy('universities.id, universities.name')
            ->orderBy('total', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->getResultArray();
    }

    /**
     * Get members breakdown by gender
     * 
     * @return array Gender breakdown
     */
    public function getByGender(): array
    {
        return $this->memberModel
            ->select('gender, COUNT(id) as total')
            ->where('membership_status', 'active')
            ->groupBy('gender')
            ->findAll();
    }

    /**
     * Get members breakdown by employment status
     * 
     * @return array Employment status breakdown
     */
    public function getByEmploymentStatus(): array
    {
        $builder = $this->memberModel->builder();

        return $builder
            ->select('employment_statuses.name, COUNT(member_profiles.id) as total')
            ->join('employment_statuses', 'employment_statuses.id = member_profiles.employment_status_id', 'left')
            ->where('member_profiles.membership_status', 'active')
            ->groupBy('employment_statuses.id, employment_statuses.name')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get members breakdown by salary range
     * 
     * @return array Salary range breakdown
     */
    public function getBySalaryRange(): array
    {
        $builder = $this->memberModel->builder();

        return $builder
            ->select('salary_ranges.name, COUNT(member_profiles.id) as total')
            ->join('salary_ranges', 'salary_ranges.id = member_profiles.salary_range_id', 'left')
            ->where('member_profiles.membership_status', 'active')
            ->groupBy('salary_ranges.id, salary_ranges.name')
            ->orderBy('salary_ranges.min_salary', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get members breakdown by education level
     * 
     * @return array Education level breakdown
     */
    public function getByEducationLevel(): array
    {
        return $this->memberModel
            ->select('education_level, COUNT(id) as total')
            ->where('membership_status', 'active')
            ->whereNotNull('education_level')
            ->groupBy('education_level')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get member growth rate
     * Calculate growth percentage compared to previous period
     * 
     * @param string $period 'month', 'quarter', 'year'
     * @return array Growth rate data
     */
    public function getGrowthRate(string $period = 'month'): array
    {
        // Get current period data
        $currentStart = $this->getPeriodStartDate($period);
        $currentEnd = date('Y-m-d');

        $currentCount = $this->memberModel
            ->where('join_date >=', $currentStart)
            ->where('join_date <=', $currentEnd)
            ->where('membership_status', 'active')
            ->countAllResults();

        // Get previous period data
        $previousStart = $this->getPreviousPeriodStartDate($period);
        $previousEnd = date('Y-m-d', strtotime($currentStart . ' -1 day'));

        $previousCount = $this->memberModel
            ->where('join_date >=', $previousStart)
            ->where('join_date <=', $previousEnd)
            ->where('membership_status', 'active')
            ->countAllResults();

        // Calculate growth rate
        $growthRate = 0;
        if ($previousCount > 0) {
            $growthRate = (($currentCount - $previousCount) / $previousCount) * 100;
        }

        return [
            'period' => $period,
            'current_count' => $currentCount,
            'previous_count' => $previousCount,
            'growth_rate' => round($growthRate, 2),
            'growth_number' => $currentCount - $previousCount,
            'is_growing' => $growthRate > 0
        ];
    }

    /**
     * Get registration trend
     * Get member registration data over time
     * 
     * @param int $months Number of months to retrieve
     * @return array Trend data
     */
    public function getRegistrationTrend(int $months = 12): array
    {
        $builder = $this->memberModel->builder();

        $startDate = date('Y-m-01', strtotime("-{$months} months"));

        $results = $builder
            ->select("DATE_FORMAT(join_date, '%Y-%m') as month, COUNT(id) as total")
            ->where('join_date >=', $startDate)
            ->where('membership_status', 'active')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        return $results;
    }

    /**
     * Get approval trend
     * Get member approval data over time
     * 
     * @param int $months Number of months to retrieve
     * @return array Trend data
     */
    public function getApprovalTrend(int $months = 12): array
    {
        $builder = $this->memberModel->builder();

        $startDate = date('Y-m-01', strtotime("-{$months} months"));

        $results = $builder
            ->select("DATE_FORMAT(verified_at, '%Y-%m') as month, COUNT(id) as total")
            ->where('verified_at >=', $startDate)
            ->where('membership_status', 'active')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        return $results;
    }

    /**
     * Get comprehensive dashboard statistics
     * 
     * @return array Dashboard data
     */
    public function getDashboardStats(): array
    {
        return [
            'overview' => [
                'total_members' => $this->getTotalMembers(),
                'active_members' => $this->getActiveMembers(),
                'pending_members' => $this->getPendingMembers(),
                'rejected_members' => $this->getRejectedMembers(),
            ],
            'growth' => [
                'monthly' => $this->getGrowthRate('month'),
                'quarterly' => $this->getGrowthRate('quarter'),
                'yearly' => $this->getGrowthRate('year'),
            ],
            'demographics' => [
                'by_gender' => $this->getByGender(),
                'by_province' => $this->getByProvince(10), // Top 10
                'by_university' => $this->getByUniversity(10), // Top 10
                'by_education' => $this->getByEducationLevel(),
            ],
            'trends' => [
                'registration' => $this->getRegistrationTrend(12),
                'approval' => $this->getApprovalTrend(12),
            ],
            'recent' => [
                'new_members_today' => $this->getNewMembersCount('today'),
                'new_members_this_week' => $this->getNewMembersCount('week'),
                'new_members_this_month' => $this->getNewMembersCount('month'),
            ]
        ];
    }

    /**
     * Get top universities by member count
     * 
     * @param int $limit Number of universities to return
     * @return array Top universities
     */
    public function getTopUniversities(int $limit = 10): array
    {
        return $this->getByUniversity($limit);
    }

    /**
     * Get top provinces by member count
     * 
     * @param int $limit Number of provinces to return
     * @return array Top provinces
     */
    public function getTopProvinces(int $limit = 10): array
    {
        return $this->getByProvince($limit);
    }

    /**
     * Get demographics summary
     * 
     * @return array Demographics data
     */
    public function getDemographics(): array
    {
        $genderData = $this->getByGender();

        // Calculate gender percentages
        $total = array_sum(array_column($genderData, 'total'));
        $genderPercentages = [];

        foreach ($genderData as $gender) {
            $percentage = $total > 0 ? ($gender->total / $total) * 100 : 0;
            $genderPercentages[] = [
                'gender' => $gender->gender,
                'total' => $gender->total,
                'percentage' => round($percentage, 2)
            ];
        }

        return [
            'gender' => $genderPercentages,
            'employment_status' => $this->getByEmploymentStatus(),
            'salary_range' => $this->getBySalaryRange(),
            'education_level' => $this->getByEducationLevel(),
            'geographic' => [
                'by_province' => $this->getByProvince(),
                'total_provinces' => $this->getTotalProvincesCovered(),
            ]
        ];
    }

    /**
     * Get new members count for a specific period
     * 
     * @param string $period 'today', 'week', 'month', 'year'
     * @return int New members count
     */
    public function getNewMembersCount(string $period): int
    {
        $startDate = $this->getPeriodStartDate($period);

        return $this->memberModel
            ->where('join_date >=', $startDate)
            ->where('membership_status', 'active')
            ->countAllResults();
    }

    /**
     * Get average members per university
     * 
     * @return float Average
     */
    public function getAverageMembersPerUniversity(): float
    {
        $totalMembers = $this->getActiveMembers();
        $totalUniversities = $this->getTotalUniversitiesWithMembers();

        return $totalUniversities > 0 ? $totalMembers / $totalUniversities : 0;
    }

    /**
     * Get total universities with members
     * 
     * @return int Total universities
     */
    public function getTotalUniversitiesWithMembers(): int
    {
        return $this->memberModel
            ->select('university_id')
            ->where('membership_status', 'active')
            ->whereNotNull('university_id')
            ->groupBy('university_id')
            ->countAllResults();
    }

    /**
     * Get total provinces covered
     * 
     * @return int Total provinces
     */
    public function getTotalProvincesCovered(): int
    {
        return $this->memberModel
            ->select('province_id')
            ->where('membership_status', 'active')
            ->whereNotNull('province_id')
            ->groupBy('province_id')
            ->countAllResults();
    }

    /**
     * Get member age distribution
     * 
     * @return array Age distribution
     */
    public function getAgeDistribution(): array
    {
        $builder = $this->memberModel->builder();

        $results = $builder
            ->select('
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 25 THEN "Under 25"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 25 AND 35 THEN "25-35"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 36 AND 45 THEN "36-45"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 46 AND 55 THEN "46-55"
                    ELSE "Over 55"
                END as age_group,
                COUNT(id) as total
            ')
            ->where('membership_status', 'active')
            ->whereNotNull('birth_date')
            ->groupBy('age_group')
            ->orderBy('age_group', 'ASC')
            ->get()
            ->getResultArray();

        return $results;
    }

    /**
     * Get membership retention rate
     * Compare active vs total registered
     * 
     * @return array Retention data
     */
    public function getRetentionRate(): array
    {
        $totalRegistered = $this->memberModel->countAllResults(false);
        $activeMembers = $this->getActiveMembers();

        $retentionRate = $totalRegistered > 0 ? ($activeMembers / $totalRegistered) * 100 : 0;

        return [
            'total_registered' => $totalRegistered,
            'active_members' => $activeMembers,
            'inactive_members' => $totalRegistered - $activeMembers,
            'retention_rate' => round($retentionRate, 2)
        ];
    }

    /**
     * Get statistics by region (for Koordinator Wilayah)
     * 
     * @param int $provinceId Province ID
     * @return array Region statistics
     */
    public function getRegionStats(int $provinceId): array
    {
        return [
            'total_members' => $this->getTotalMembers(['province_id' => $provinceId, 'status' => 'active']),
            'pending_members' => $this->getTotalMembers(['province_id' => $provinceId, 'status' => 'pending']),
            'by_university' => $this->getUniversitiesByProvince($provinceId),
            'by_gender' => $this->getGenderByProvince($provinceId),
            'growth_rate' => $this->getProvinceGrowthRate($provinceId),
        ];
    }

    /**
     * Get universities in a province with member counts
     * 
     * @param int $provinceId Province ID
     * @return array Universities data
     */
    protected function getUniversitiesByProvince(int $provinceId): array
    {
        $builder = $this->memberModel->builder();

        return $builder
            ->select('universities.name, COUNT(member_profiles.id) as total')
            ->join('universities', 'universities.id = member_profiles.university_id', 'left')
            ->where('member_profiles.province_id', $provinceId)
            ->where('member_profiles.membership_status', 'active')
            ->groupBy('universities.id, universities.name')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get gender breakdown for a province
     * 
     * @param int $provinceId Province ID
     * @return array Gender breakdown
     */
    protected function getGenderByProvince(int $provinceId): array
    {
        return $this->memberModel
            ->select('gender, COUNT(id) as total')
            ->where('province_id', $provinceId)
            ->where('membership_status', 'active')
            ->groupBy('gender')
            ->findAll();
    }

    /**
     * Get province growth rate
     * 
     * @param int $provinceId Province ID
     * @return array Growth rate data
     */
    protected function getProvinceGrowthRate(int $provinceId): array
    {
        $currentMonth = date('Y-m');
        $previousMonth = date('Y-m', strtotime('-1 month'));

        $currentCount = $this->memberModel
            ->where('province_id', $provinceId)
            ->where('membership_status', 'active')
            ->like('join_date', $currentMonth, 'after')
            ->countAllResults();

        $previousCount = $this->memberModel
            ->where('province_id', $provinceId)
            ->where('membership_status', 'active')
            ->like('join_date', $previousMonth, 'after')
            ->countAllResults();

        $growthRate = $previousCount > 0 ? (($currentCount - $previousCount) / $previousCount) * 100 : 0;

        return [
            'current_month' => $currentCount,
            'previous_month' => $previousCount,
            'growth_rate' => round($growthRate, 2),
            'is_growing' => $growthRate > 0
        ];
    }

    /**
     * Get period start date
     * 
     * @param string $period Period type
     * @return string Date string
     */
    protected function getPeriodStartDate(string $period): string
    {
        return match ($period) {
            'today' => date('Y-m-d'),
            'week' => date('Y-m-d', strtotime('monday this week')),
            'month' => date('Y-m-01'),
            'quarter' => date('Y-m-01', strtotime('first day of -' . (date('n') % 3) . ' months')),
            'year' => date('Y-01-01'),
            default => date('Y-m-01'),
        };
    }

    /**
     * Get previous period start date
     * 
     * @param string $period Period type
     * @return string Date string
     */
    protected function getPreviousPeriodStartDate(string $period): string
    {
        return match ($period) {
            'today' => date('Y-m-d', strtotime('-1 day')),
            'week' => date('Y-m-d', strtotime('monday last week')),
            'month' => date('Y-m-01', strtotime('-1 month')),
            'quarter' => date('Y-m-01', strtotime('-3 months')),
            'year' => date('Y-01-01', strtotime('-1 year')),
            default => date('Y-m-01', strtotime('-1 month')),
        };
    }

    /**
     * Export statistics to array for reporting
     * 
     * @param array $options Export options
     * @return array Statistics data
     */
    public function exportStatistics(array $options = []): array
    {
        $includeAll = $options['include_all'] ?? true;

        $stats = [
            'generated_at' => date('Y-m-d H:i:s'),
            'overview' => [
                'total_members' => $this->getTotalMembers(),
                'active_members' => $this->getActiveMembers(),
                'pending_members' => $this->getPendingMembers(),
            ]
        ];

        if ($includeAll || in_array('demographics', $options['include'] ?? [])) {
            $stats['demographics'] = $this->getDemographics();
        }

        if ($includeAll || in_array('growth', $options['include'] ?? [])) {
            $stats['growth'] = [
                'monthly' => $this->getGrowthRate('month'),
                'quarterly' => $this->getGrowthRate('quarter'),
                'yearly' => $this->getGrowthRate('year'),
            ];
        }

        if ($includeAll || in_array('trends', $options['include'] ?? [])) {
            $stats['trends'] = [
                'registration' => $this->getRegistrationTrend(12),
            ];
        }

        if ($includeAll || in_array('retention', $options['include'] ?? [])) {
            $stats['retention'] = $this->getRetentionRate();
        }

        return $stats;
    }

    /**
     * Get public statistics for homepage
     * 
     * @return array
     */
    public function getPublicStatistics(): array
    {
        return [
            'total_members' => $this->getTotalMembers(),
            'active_members' => $this->getActiveMembers(),
            'total_provinces' => $this->getCoveredProvinces(),
            'total_universities' => $this->getCoveredUniversities(),
        ];
    }

    /**
     * Get covered provinces count
     * 
     * @return int
     */
    public function getCoveredProvinces(): int
    {
        return $this->memberModel
            ->select('province_id')
            ->distinct()
            ->where('province_id IS NOT NULL')
            ->countAllResults();
    }

    /**
     * Get covered universities count
     * 
     * @return int
     */
    public function getCoveredUniversities(): int
    {
        return $this->memberModel
            ->select('university_id')
            ->distinct()
            ->where('university_id IS NOT NULL')
            ->countAllResults();
    }

    /**
     * Get published posts with filters
     * 
     * @param array $filters Filters (limit, order_by, order_dir, category_id)
     * @return array
     */
    public function getPublishedPosts(array $filters = []): array
    {
        try {
            $postModel = new \App\Models\PostModel();

            $builder = $postModel
                ->select('posts.*, post_categories.name as category_name, users.username as author_name')
                ->join('post_categories', 'post_categories.id = posts.category_id', 'left')
                ->join('users', 'users.id = posts.author_id', 'left')
                ->where('posts.status', 'published')
                ->where('posts.published_at <=', date('Y-m-d H:i:s'));

            // Apply category filter
            if (isset($filters['category_id']) && !empty($filters['category_id'])) {
                $builder->where('posts.category_id', $filters['category_id']);
            }

            // Apply ordering
            $orderBy = $filters['order_by'] ?? 'published_at';
            $orderDir = $filters['order_dir'] ?? 'DESC';
            $builder->orderBy($orderBy, $orderDir);

            // Apply limit
            $limit = $filters['limit'] ?? 10;
            $builder->limit($limit);

            return $builder->find();
        } catch (\Exception $e) {
            log_message('error', 'Error getting published posts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured pages (Manifesto, Sejarah, AD/ART)
     * 
     * @return array
     */
    public function getFeaturedPages(): array
    {
        try {
            $pageModel = new \App\Models\PageModel();

            return $pageModel
                ->where('status', 'published')
                ->where('is_featured', 1)
                ->orderBy('display_order', 'ASC')
                ->limit(5)
                ->find();
        } catch (\Exception $e) {
            log_message('error', 'Error getting featured pages: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get latest announcements
     * 
     * @param int $limit Number of announcements
     * @return array
     */
    public function getLatestAnnouncements(int $limit = 5): array
    {
        try {
            // Assuming you have announcement functionality
            // Return empty for now if table doesn't exist
            return [];
        } catch (\Exception $e) {
            log_message('error', 'Error getting announcements: ' . $e->getMessage());
            return [];
        }
    }
}
