<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SurveyService;
use App\Services\Communication\NotificationService;
use App\Models\SurveyModel;
use App\Models\SurveyQuestionModel;
use App\Models\SurveyResponseModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * SurveyController (Admin)
 * 
 * Mengelola survey dan polling untuk anggota
 * CRUD surveys, manage questions, view responses, export data
 * Support survey analytics dan statistics
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SurveyController extends BaseController
{
    /**
     * @var SurveyService
     */
    protected $surveyService;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var SurveyModel
     */
    protected $surveyModel;

    /**
     * @var SurveyQuestionModel
     */
    protected $questionModel;

    /**
     * @var SurveyResponseModel
     */
    protected $responseModel;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->surveyService = new SurveyService();
        $this->notificationService = new NotificationService();
        $this->surveyModel = new SurveyModel();
        $this->questionModel = new SurveyQuestionModel();
        $this->responseModel = new SurveyResponseModel();
    }

    /**
     * Display list of all surveys
     * Shows all surveys with statistics
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengelola survey');
        }

        // Get filters from request
        $filters = [
            'status' => $this->request->getGet('status'),
            'search' => $this->request->getGet('search')
        ];

        // Build query
        $builder = $this->surveyModel
            ->select('surveys.*, auth_identities.secret as created_by_email, member_profiles.full_name as created_by_name, COUNT(DISTINCT survey_responses.user_id) as response_count')
            ->join('users', 'users.id = surveys.created_by')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('survey_responses', 'survey_responses.survey_id = surveys.id', 'left')
            ->groupBy('surveys.id');

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('surveys.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('surveys.title', $search)
                ->orLike('surveys.description', $search)
                ->groupEnd();
        }

        // Get paginated results
        $surveys = $builder
            ->orderBy('surveys.created_at', 'DESC')
            ->paginate(20);

        $data = [
            'title' => 'Kelola Survey',
            'surveys' => $surveys,
            'pager' => $this->surveyModel->pager,
            'filters' => $filters
        ];

        return view('admin/surveys/index', $data);
    }

    /**
     * Show create survey form
     * Display form to create new survey
     * 
     * @return string|ResponseInterface
     */
    public function create()
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat survey');
        }

        $data = [
            'title' => 'Buat Survey Baru'
        ];

        return view('admin/surveys/create', $data);
    }

    /**
     * Store new survey
     * Save survey with questions and options
     * 
     * @return ResponseInterface
     */
    public function store(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat survey');
        }

        // Validate input
        $rules = [
            'title' => 'required|min_length[5]|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'status' => 'required|in_list[draft,published,closed]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $surveyData = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'start_date' => $this->request->getPost('start_date'),
                'end_date' => $this->request->getPost('end_date'),
                'status' => $this->request->getPost('status'),
                'allow_multiple_responses' => $this->request->getPost('allow_multiple_responses') ? 1 : 0,
                'show_results' => $this->request->getPost('show_results') ? 1 : 0,
                'created_by' => auth()->id()
            ];

            // Get questions data
            $questions = $this->request->getPost('questions');

            // Create survey using service
            $result = $this->surveyService->createSurvey($surveyData, $questions);

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            // Send notification if published
            if ($surveyData['status'] === 'published') {
                $this->notificationService->sendNewSurveyNotification($result['data']['survey_id']);
            }

            return redirect()->to('/admin/surveys')->with('success', 'Survey berhasil dibuat');
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::store: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal membuat survey: ' . $e->getMessage());
        }
    }

    /**
     * Show edit survey form
     * Display form to edit existing survey
     * 
     * @param int $id Survey ID
     * @return string|ResponseInterface
     */
    public function edit(int $id)
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit survey');
        }

        // Get survey with questions
        $survey = $this->surveyModel->find($id);

        if (!$survey) {
            return redirect()->back()->with('error', 'Survey tidak ditemukan');
        }

        // Get questions
        $questions = $this->questionModel
            ->where('survey_id', $id)
            ->orderBy('order_number', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Edit Survey',
            'survey' => $survey,
            'questions' => $questions
        ];

        return view('admin/surveys/edit', $data);
    }

    /**
     * Update survey
     * Update survey data, questions, and options
     * 
     * @param int $id Survey ID
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit survey');
        }

        // Validate input
        $rules = [
            'title' => 'required|min_length[5]|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'status' => 'required|in_list[draft,published,closed]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $survey = $this->surveyModel->find($id);

            if (!$survey) {
                return redirect()->back()->with('error', 'Survey tidak ditemukan');
            }

            $surveyData = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'start_date' => $this->request->getPost('start_date'),
                'end_date' => $this->request->getPost('end_date'),
                'status' => $this->request->getPost('status'),
                'allow_multiple_responses' => $this->request->getPost('allow_multiple_responses') ? 1 : 0,
                'show_results' => $this->request->getPost('show_results') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Get questions data
            $questions = $this->request->getPost('questions');

            // Update survey using service
            $result = $this->surveyService->updateSurvey($id, $surveyData, $questions);

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            // Send notification if status changed to published
            if ($survey->status !== 'published' && $surveyData['status'] === 'published') {
                $this->notificationService->sendNewSurveyNotification($id);
            }

            return redirect()->to('/admin/surveys')->with('success', 'Survey berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate survey: ' . $e->getMessage());
        }
    }

    /**
     * Delete survey
     * Soft delete survey and all related data
     * 
     * @param int $id Survey ID
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus survey');
        }

        try {
            $survey = $this->surveyModel->find($id);

            if (!$survey) {
                return redirect()->back()->with('error', 'Survey tidak ditemukan');
            }

            // Delete survey (soft delete if implemented)
            $this->surveyModel->delete($id);

            // Log activity
            log_message('info', "Survey ID {$id} ({$survey->title}) deleted by user " . auth()->id());

            return redirect()->to('/admin/surveys')->with('success', 'Survey berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::delete: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus survey: ' . $e->getMessage());
        }
    }

    /**
     * View survey responses
     * Display all responses with statistics
     * 
     * @param int $id Survey ID
     * @return string|ResponseInterface
     */
    public function responses(int $id)
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat hasil survey');
        }

        // Get survey
        $survey = $this->surveyModel->find($id);

        if (!$survey) {
            return redirect()->back()->with('error', 'Survey tidak ditemukan');
        }

        // Get survey statistics
        $stats = $this->surveyService->getSurveyStatistics($id);

        // Get recent responses
        $recentResponses = $this->responseModel
            ->select('survey_responses.*, auth_identities.secret as email, member_profiles.full_name, member_profiles.province_id, provinces.name as province_name')
            ->join('users', 'users.id = survey_responses.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
            ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
            ->where('survey_responses.survey_id', $id)
            ->orderBy('survey_responses.submitted_at', 'DESC')
            ->paginate(20);

        $data = [
            'title' => 'Hasil Survey - ' . $survey->title,
            'survey' => $survey,
            'stats' => $stats,
            'recent_responses' => $recentResponses,
            'pager' => $this->responseModel->pager
        ];

        return view('admin/surveys/responses', $data);
    }

    /**
     * Export survey responses to Excel
     * Download all responses with complete data
     * 
     * @param int $id Survey ID
     * @return ResponseInterface
     */
    public function export(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengekspor data survey');
        }

        try {
            // Get survey
            $survey = $this->surveyModel->find($id);

            if (!$survey) {
                return redirect()->back()->with('error', 'Survey tidak ditemukan');
            }

            // Get questions
            $questions = $this->questionModel
                ->where('survey_id', $id)
                ->orderBy('order_number', 'ASC')
                ->findAll();

            // Get all responses
            $responses = $this->responseModel
                ->select('survey_responses.*, auth_identities.secret as email, member_profiles.full_name, member_profiles.phone, provinces.name as province_name')
                ->join('users', 'users.id = survey_responses.user_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->join('member_profiles', 'member_profiles.user_id = users.id', 'left')
                ->join('provinces', 'provinces.id = member_profiles.province_id', 'left')
                ->where('survey_responses.survey_id', $id)
                ->orderBy('survey_responses.submitted_at', 'DESC')
                ->findAll();

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Hasil Survey');

            // Set headers
            $headers = ['No', 'Nama', 'Email', 'Provinsi', 'Tanggal Submit'];

            // Add question titles as headers
            foreach ($questions as $question) {
                $headers[] = $question->question_text;
            }

            $sheet->fromArray($headers, null, 'A1');

            // Style header row
            $lastColumn = chr(64 + count($headers));
            $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
            $sheet->getStyle("A1:{$lastColumn}1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $sheet->getStyle("A1:{$lastColumn}1")->getFont()->getColor()->setRGB('FFFFFF');

            // Fill data
            $row = 2;
            foreach ($responses as $index => $response) {
                $rowData = [
                    $index + 1,
                    $response->full_name ?? '-',
                    $response->email,
                    $response->province_name ?? '-',
                    date('d/m/Y H:i', strtotime($response->submitted_at))
                ];

                // Get answers for this response
                $answers = json_decode($response->answers, true);

                foreach ($questions as $question) {
                    $answer = $answers[$question->id] ?? '-';

                    // Format answer based on question type
                    if (is_array($answer)) {
                        $answer = implode(', ', $answer);
                    }

                    $rowData[] = $answer;
                }

                $sheet->fromArray($rowData, null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            for ($col = 'A'; $col <= $lastColumn; $col++) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Generate filename
            $filename = 'survey_' . $survey->id . '_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::export: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data survey: ' . $e->getMessage());
        }
    }

    /**
     * Publish survey
     * Change status to published and notify members
     * 
     * @param int $id Survey ID
     * @return ResponseInterface
     */
    public function publish(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mempublikasi survey');
        }

        try {
            $survey = $this->surveyModel->find($id);

            if (!$survey) {
                return redirect()->back()->with('error', 'Survey tidak ditemukan');
            }

            // Check if survey has questions
            $questionCount = $this->questionModel
                ->where('survey_id', $id)
                ->countAllResults();

            if ($questionCount === 0) {
                return redirect()->back()->with('error', 'Survey harus memiliki minimal 1 pertanyaan sebelum dipublikasi');
            }

            // Update status
            $this->surveyModel->update($id, [
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification to all members
            $this->notificationService->sendNewSurveyNotification($id);

            // Log activity
            log_message('info', "Survey ID {$id} published by user " . auth()->id());

            return redirect()->back()->with('success', 'Survey berhasil dipublikasi dan notifikasi telah dikirim');
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::publish: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mempublikasi survey: ' . $e->getMessage());
        }
    }

    /**
     * Close survey
     * Change status to closed and prevent new responses
     * 
     * @param int $id Survey ID
     * @return ResponseInterface
     */
    public function close(int $id): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('survey.manage')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menutup survey');
        }

        try {
            $survey = $this->surveyModel->find($id);

            if (!$survey) {
                return redirect()->back()->with('error', 'Survey tidak ditemukan');
            }

            // Update status
            $this->surveyModel->update($id, [
                'status' => 'closed',
                'closed_at' => date('Y-m-d H:i:s')
            ]);

            // Log activity
            log_message('info', "Survey ID {$id} closed by user " . auth()->id());

            return redirect()->back()->with('success', 'Survey berhasil ditutup');
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::close: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menutup survey: ' . $e->getMessage());
        }
    }

    /**
     * Get survey statistics (AJAX endpoint)
     * Returns detailed statistics for charts
     * 
     * @param int $id Survey ID
     * @return ResponseInterface JSON response
     */
    public function getStatistics(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        try {
            $stats = $this->surveyService->getSurveyStatistics($id);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyController::getStatistics: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
