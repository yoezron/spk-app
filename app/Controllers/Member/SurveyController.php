<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Services\Content\SurveyService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * SurveyController (Member Area)
 * 
 * Menangani survei untuk anggota
 * List surveys, view detail, submit responses, view results
 * 
 * @package App\Controllers\Member
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
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->surveyService = new SurveyService();
    }

    /**
     * Display available surveys list
     * Shows active surveys that user can participate in
     * 
     * @return string
     */
    public function index(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Get active surveys
            $activeSurveys = $this->surveyService->getActiveSurveys([
                'user_id' => $userId
            ]);

            // Get completed surveys by user
            $completedSurveys = $this->surveyService->getUserCompletedSurveys($userId);

            // Get upcoming surveys
            $upcomingSurveys = $this->surveyService->getUpcomingSurveys();

            $data = [
                'title' => 'Survei - Serikat Pekerja Kampus',
                'pageTitle' => 'Survei',

                // Surveys
                'activeSurveys' => $activeSurveys['data'] ?? [],
                'completedSurveys' => $completedSurveys['data'] ?? [],
                'upcomingSurveys' => $upcomingSurveys['data'] ?? [],

                // Stats
                'totalCompleted' => count($completedSurveys['data'] ?? []),
                'totalActive' => count($activeSurveys['data'] ?? [])
            ];

            return view('member/survey/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading surveys: ' . $e->getMessage());

            return view('member/survey/index', [
                'title' => 'Survei',
                'pageTitle' => 'Survei',
                'activeSurveys' => [],
                'completedSurveys' => [],
                'upcomingSurveys' => [],
                'totalCompleted' => 0,
                'totalActive' => 0
            ]);
        }
    }

    /**
     * Display survey detail with questions
     * 
     * @param int $surveyId Survey ID
     * @return string|RedirectResponse
     */
    public function show(int $surveyId)
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Get survey with questions
            $result = $this->surveyService->getSurveyWithQuestions($surveyId);

            if (!$result['success'] || !$result['data']) {
                return redirect()->to('/member/surveys')
                    ->with('error', 'Survei tidak ditemukan.');
            }

            $survey = $result['data'];

            // Check if survey is active
            if ($survey->status !== 'active') {
                return redirect()->to('/member/surveys')
                    ->with('error', 'Survei ini sudah tidak aktif.');
            }

            // Check if user has already completed this survey
            $hasCompleted = $this->surveyService->hasUserCompletedSurvey($surveyId, $userId);

            if ($hasCompleted['completed']) {
                // Redirect to results page
                return redirect()->to('/member/surveys/' . $surveyId . '/results')
                    ->with('info', 'Anda sudah mengisi survei ini.');
            }

            $data = [
                'title' => $survey->title . ' - Survei SPK',
                'pageTitle' => $survey->title,
                'survey' => $survey,
                'questions' => $survey->questions ?? []
            ];

            return view('member/survey/show', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading survey: ' . $e->getMessage());

            return redirect()->to('/member/surveys')
                ->with('error', 'Terjadi kesalahan saat memuat survei.');
        }
    }

    /**
     * Submit survey response
     * 
     * @param int $surveyId Survey ID
     * @return RedirectResponse
     */
    public function submit(int $surveyId): RedirectResponse
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Check if survey exists and is active
            $surveyModel = model('SurveyModel');
            $survey = $surveyModel->find($surveyId);

            if (!$survey) {
                return redirect()->to('/member/surveys')
                    ->with('error', 'Survei tidak ditemukan.');
            }

            if ($survey->status !== 'active') {
                return redirect()->to('/member/surveys')
                    ->with('error', 'Survei ini sudah tidak aktif.');
            }

            // Check if user has already completed
            $hasCompleted = $this->surveyService->hasUserCompletedSurvey($surveyId, $userId);

            if ($hasCompleted['completed']) {
                return redirect()->to('/member/surveys/' . $surveyId . '/results')
                    ->with('info', 'Anda sudah mengisi survei ini sebelumnya.');
            }

            // Get answers from POST data
            $answers = $this->request->getPost('answers');

            if (empty($answers)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Silakan jawab minimal satu pertanyaan.');
            }

            // Validate required questions
            $validation = $this->validateSurveyAnswers($surveyId, $answers);

            if (!$validation['valid']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $validation['message']);
            }

            // Submit survey response
            $responseData = [
                'survey_id' => $surveyId,
                'user_id' => $userId,
                'answers' => $answers,
                'completed_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->surveyService->submitResponse($responseData);

            if ($result['success']) {
                return redirect()->to('/member/surveys/' . $surveyId . '/results')
                    ->with('success', 'Terima kasih! Jawaban Anda telah tersimpan.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error submitting survey: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan jawaban.');
        }
    }

    /**
     * Display survey results
     * Shows aggregated results if allowed
     * 
     * @param int $surveyId Survey ID
     * @return string|RedirectResponse
     */
    public function results(int $surveyId)
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Get survey
            $surveyModel = model('SurveyModel');
            $survey = $surveyModel->find($surveyId);

            if (!$survey) {
                return redirect()->to('/member/surveys')
                    ->with('error', 'Survei tidak ditemukan.');
            }

            // Check if user has completed the survey
            $hasCompleted = $this->surveyService->hasUserCompletedSurvey($surveyId, $userId);

            if (!$hasCompleted['completed']) {
                return redirect()->to('/member/surveys/' . $surveyId)
                    ->with('error', 'Anda harus mengisi survei terlebih dahulu.');
            }

            // Check if results are public
            if (!$survey->show_results && !auth()->user()->can('survey.view_results')) {
                return redirect()->to('/member/surveys')
                    ->with('info', 'Hasil survei ini tidak dapat ditampilkan.');
            }

            // Get survey results
            $results = $this->surveyService->getSurveyResults($surveyId);

            // Get user's own response
            $userResponse = $this->surveyService->getUserResponse($surveyId, $userId);

            $data = [
                'title' => 'Hasil Survei: ' . $survey->title,
                'pageTitle' => 'Hasil Survei',
                'survey' => $survey,
                'results' => $results['data'] ?? [],
                'userResponse' => $userResponse['data'] ?? null,
                'totalResponses' => $results['total_responses'] ?? 0
            ];

            return view('member/survey/results', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading survey results: ' . $e->getMessage());

            return redirect()->to('/member/surveys')
                ->with('error', 'Terjadi kesalahan saat memuat hasil survei.');
        }
    }

    /**
     * Display user's survey response
     * Shows what user answered
     * 
     * @param int $surveyId Survey ID
     * @return string|RedirectResponse
     */
    public function myResponse(int $surveyId)
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            // Check if user has completed
            $hasCompleted = $this->surveyService->hasUserCompletedSurvey($surveyId, $userId);

            if (!$hasCompleted['completed']) {
                return redirect()->to('/member/surveys/' . $surveyId)
                    ->with('error', 'Anda belum mengisi survei ini.');
            }

            // Get survey
            $surveyModel = model('SurveyModel');
            $survey = $surveyModel->find($surveyId);

            // Get user's response with questions
            $userResponse = $this->surveyService->getUserResponseWithDetails($surveyId, $userId);

            $data = [
                'title' => 'Jawaban Saya: ' . $survey->title,
                'pageTitle' => 'Jawaban Saya',
                'survey' => $survey,
                'response' => $userResponse['data'] ?? null
            ];

            return view('member/survey/my_response', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading user response: ' . $e->getMessage());

            return redirect()->to('/member/surveys')
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Validate survey answers
     * Check if all required questions are answered
     * 
     * @param int $surveyId Survey ID
     * @param array $answers User answers
     * @return array
     */
    protected function validateSurveyAnswers(int $surveyId, array $answers): array
    {
        try {
            // Get survey questions
            $questionModel = model('SurveyQuestionModel');
            $questions = $questionModel->where('survey_id', $surveyId)
                ->where('is_required', 1)
                ->findAll();

            // Check if all required questions are answered
            foreach ($questions as $question) {
                if (!isset($answers[$question->id]) || empty($answers[$question->id])) {
                    return [
                        'valid' => false,
                        'message' => 'Pertanyaan "' . $question->question . '" harus dijawab.'
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'Valid'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error validating survey answers: ' . $e->getMessage());

            return [
                'valid' => false,
                'message' => 'Terjadi kesalahan validasi.'
            ];
        }
    }

    /**
     * Get survey completion statistics (AJAX)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getStats()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        $userId = auth()->id();

        try {
            // Get user's survey stats
            $responseModel = model('SurveyResponseModel');

            $totalCompleted = $responseModel->where('user_id', $userId)
                ->countAllResults();

            $completedThisMonth = $responseModel->where('user_id', $userId)
                ->where('completed_at >=', date('Y-m-01'))
                ->countAllResults();

            $surveyModel = model('SurveyModel');
            $totalActive = $surveyModel->where('status', 'active')
                ->where('end_date >=', date('Y-m-d'))
                ->countAllResults();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'total_completed' => $totalCompleted,
                    'completed_this_month' => $completedThisMonth,
                    'total_active' => $totalActive,
                    'completion_rate' => $totalActive > 0 ? round(($totalCompleted / $totalActive) * 100, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting survey stats: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting statistics'
            ]);
        }
    }

    /**
     * Display completed surveys history
     * 
     * @return string
     */
    public function history(): string
    {
        // Check authentication
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        try {
            $page = (int) ($this->request->getGet('page') ?? 1);

            $completedSurveys = $this->surveyService->getUserCompletedSurveys($userId, [
                'page' => $page,
                'limit' => 15
            ]);

            $data = [
                'title' => 'Riwayat Survei - SPK',
                'pageTitle' => 'Riwayat Survei',
                'surveys' => $completedSurveys['data'] ?? [],
                'pager' => $completedSurveys['pager'] ?? null,
                'total' => $completedSurveys['total'] ?? 0
            ];

            return view('member/survey/history', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading survey history: ' . $e->getMessage());

            return redirect()->to('/member/surveys')
                ->with('error', 'Terjadi kesalahan.');
        }
    }
}
