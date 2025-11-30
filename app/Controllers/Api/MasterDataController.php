<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UniversityModel;
use App\Models\StudyProgramModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API Master Data Controller
 *
 * Provides API endpoints for cascading dropdowns in registration form
 * - Get universities by province
 * - Get study programs by university
 *
 * @package App\Controllers\Api
 */
class MasterDataController extends BaseController
{
    protected $universityModel;
    protected $studyProgramModel;

    public function __construct()
    {
        $this->universityModel = new UniversityModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    /**
     * Get universities by province ID
     *
     * @return ResponseInterface
     */
    public function getUniversities(): ResponseInterface
    {
        try {
            $provinceId = $this->request->getGet('province_id');

            if (!$provinceId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Province ID is required',
                    'data' => []
                ]);
            }

            $universities = $this->universityModel
                ->select('id, name, type')
                ->where('province_id', $provinceId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Universities retrieved successfully',
                'data' => $universities
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching universities: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching universities',
                'data' => []
            ]);
        }
    }

    /**
     * Get study programs by university ID
     *
     * @return ResponseInterface
     */
    public function getStudyPrograms(): ResponseInterface
    {
        try {
            $universityId = $this->request->getGet('university_id');

            if (!$universityId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'University ID is required',
                    'data' => []
                ]);
            }

            $studyPrograms = $this->studyProgramModel
                ->select('id, name, level, faculty')
                ->where('university_id', $universityId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Study programs retrieved successfully',
                'data' => $studyPrograms
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching study programs: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching study programs',
                'data' => []
            ]);
        }
    }

    /**
     * Get study programs by university ID (URL parameter version)
     * Used by member profile edit page
     *
     * @param int $universityId University ID from URL segment
     * @return ResponseInterface
     */
    public function getStudyProgramsByUniversity(int $universityId): ResponseInterface
    {
        try {
            if (!$universityId) {
                return $this->response->setJSON([]);
            }

            $studyPrograms = $this->studyProgramModel
                ->select('id, name, level, faculty')
                ->where('university_id', $universityId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();

            // Return simple array format expected by the frontend
            return $this->response->setJSON($studyPrograms);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching study programs by university: ' . $e->getMessage());

            return $this->response->setJSON([]);
        }
    }
}
