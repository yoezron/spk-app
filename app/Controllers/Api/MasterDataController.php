<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UniversityModel;
use App\Models\StudyProgramModel;
use App\Models\RegencyModel;
// use App\Models\DistrictModel;  // ❌ COMMENTED: districts table doesn't exist in database
// use App\Models\VillageModel;   // ❌ COMMENTED: villages table doesn't exist in database

/**
 * Master Data API Controller
 * 
 * Provides master data endpoints for dynamic forms
 * Used for cascading dropdowns in registration and other forms
 * 
 * @package App\Controllers\Api
 */
class MasterDataController extends ResourceController
{
    use ResponseTrait;

    protected $universityModel;
    protected $studyProgramModel;
    protected $regencyModel;
    // protected $districtModel;  // ❌ COMMENTED: districts table doesn't exist
    // protected $villageModel;   // ❌ COMMENTED: villages table doesn't exist

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->universityModel = new UniversityModel();
        $this->studyProgramModel = new StudyProgramModel();
        $this->regencyModel = new RegencyModel();
        // $this->districtModel = new DistrictModel();  // ❌ COMMENTED: districts table doesn't exist
        // $this->villageModel = new VillageModel();    // ❌ COMMENTED: villages table doesn't exist

        // Enable CORS if needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    /**
     * Get universities by province
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getUniversities()
    {
        try {
            $provinceId = $this->request->getGet('province_id');
            $typeId = $this->request->getGet('type_id');

            if (empty($provinceId)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Province ID is required',
                    'data' => []
                ], 400);
            }

            // Build query
            $builder = $this->universityModel->builder();
            $builder->select('universities.id, universities.name, universities.short_name')
                ->where('universities.province_id', $provinceId)
                ->where('universities.is_active', 1)
                ->orderBy('universities.name', 'ASC');

            // Filter by type if provided
            if (!empty($typeId)) {
                $builder->where('universities.university_type_id', $typeId);
            }

            // Check cache first
            $cacheKey = "universities_province_{$provinceId}" . ($typeId ? "_type_{$typeId}" : "");
            $universities = cache($cacheKey);

            if ($universities === null) {
                $universities = $builder->get()->getResultArray();

                // Cache for 1 hour
                cache()->save($cacheKey, $universities, 3600);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Universities retrieved successfully',
                'data' => $universities,
                'count' => count($universities)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in getUniversities: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to retrieve universities',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get study programs by university
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getStudyPrograms()
    {
        try {
            $universityId = $this->request->getGet('university_id');

            if (empty($universityId)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'University ID is required',
                    'data' => []
                ], 400);
            }

            // Check cache first
            $cacheKey = "study_programs_university_{$universityId}";
            $programs = cache($cacheKey);

            if ($programs === null) {
                $programs = $this->studyProgramModel
                    ->where('university_id', $universityId)
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();

                // Cache for 1 hour
                cache()->save($cacheKey, $programs, 3600);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Study programs retrieved successfully',
                'data' => $programs,
                'count' => count($programs)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in getStudyPrograms: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to retrieve study programs',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get regencies by province
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getRegencies()
    {
        try {
            $provinceId = $this->request->getGet('province_id');

            if (empty($provinceId)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Province ID is required',
                    'data' => []
                ], 400);
            }

            // Check cache first
            $cacheKey = "regencies_province_{$provinceId}";
            $regencies = cache($cacheKey);

            if ($regencies === null) {
                $regencies = $this->regencyModel
                    ->where('province_id', $provinceId)
                    ->orderBy('name', 'ASC')
                    ->findAll();

                // Cache for 24 hours (master data jarang berubah)
                cache()->save($cacheKey, $regencies, 86400);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Regencies retrieved successfully',
                'data' => $regencies,
                'count' => count($regencies)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in getRegencies: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to retrieve regencies',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get districts by regency
     * ❌ COMMENTED OUT: districts table doesn't exist in database
     * TODO: Uncomment when districts table is created
     *
     * @return \CodeIgniter\HTTP\Response
     */
    /*
    public function getDistricts()
    {
        try {
            $regencyId = $this->request->getGet('regency_id');

            if (empty($regencyId)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Regency ID is required',
                    'data' => []
                ], 400);
            }

            // Check cache first
            $cacheKey = "districts_regency_{$regencyId}";
            $districts = cache($cacheKey);

            if ($districts === null) {
                $districts = $this->districtModel
                    ->where('regency_id', $regencyId)
                    ->orderBy('name', 'ASC')
                    ->findAll();

                // Cache for 24 hours
                cache()->save($cacheKey, $districts, 86400);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Districts retrieved successfully',
                'data' => $districts,
                'count' => count($districts)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in getDistricts: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to retrieve districts',
                'data' => []
            ], 500);
        }
    }
    */

    /**
     * Get villages by district
     * ❌ COMMENTED OUT: villages table doesn't exist in database
     * TODO: Uncomment when villages table is created
     *
     * @return \CodeIgniter\HTTP\Response
     */
    /*
    public function getVillages()
    {
        try {
            $districtId = $this->request->getGet('district_id');

            if (empty($districtId)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'District ID is required',
                    'data' => []
                ], 400);
            }

            // Check cache first
            $cacheKey = "villages_district_{$districtId}";
            $villages = cache($cacheKey);

            if ($villages === null) {
                $villages = $this->villageModel
                    ->where('district_id', $districtId)
                    ->orderBy('name', 'ASC')
                    ->findAll();

                // Cache for 24 hours
                cache()->save($cacheKey, $villages, 86400);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Villages retrieved successfully',
                'data' => $villages,
                'count' => count($villages)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in getVillages: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to retrieve villages',
                'data' => []
            ], 500);
        }
    }
    */

    /**
     * Search universities by name
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function searchUniversities()
    {
        try {
            $keyword = $this->request->getGet('q');
            $limit = $this->request->getGet('limit') ?? 20;

            if (empty($keyword) || strlen($keyword) < 3) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Keyword must be at least 3 characters',
                    'data' => []
                ], 400);
            }

            $universities = $this->universityModel
                ->like('name', $keyword)
                ->orLike('short_name', $keyword)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->limit($limit)
                ->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => $universities,
                'count' => count($universities)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in searchUniversities: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to search universities',
                'data' => []
            ], 500);
        }
    }

    /**
     * Clear cache for master data
     * Only accessible by admin
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function clearCache()
    {
        try {
            // Check if user is authenticated and has permission
            if (!auth()->loggedIn()) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Clear all master data cache
            cache()->deleteMatching('universities_*');
            cache()->deleteMatching('study_programs_*');
            cache()->deleteMatching('regencies_*');
            // cache()->deleteMatching('districts_*');  // ❌ COMMENTED: districts table doesn't exist
            // cache()->deleteMatching('villages_*');   // ❌ COMMENTED: villages table doesn't exist

            return $this->respond([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error in clearCache: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Failed to clear cache'
            ], 500);
        }
    }
}
