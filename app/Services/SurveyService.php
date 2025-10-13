<?php

namespace App\Services;

use App\Models\SurveyModel;
use App\Models\SurveyQuestionModel;
use App\Models\SurveyResponseModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * SurveyService
 * 
 * Menangani survey creation, question management, response submission, dan analytics
 * Termasuk create survey, manage questions, submit responses, results, dan export
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class SurveyService
{
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
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->surveyModel = new SurveyModel();
        $this->questionModel = new SurveyQuestionModel();
        $this->responseModel = new SurveyResponseModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create new survey
     * Creates survey with metadata and optional questions
     * 
     * @param array $data Survey data (title, description, start_date, end_date, etc)
     * @param int $createdBy User ID of creator
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createSurvey(array $data, int $createdBy): array
    {
        $this->db->transStart();

        try {
            // Validate required fields
            if (empty($data['title'])) {
                return [
                    'success' => false,
                    'message' => 'Judul survey harus diisi',
                    'data' => null
                ];
            }

            // Prepare survey data
            $surveyData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'is_anonymous' => $data['is_anonymous'] ?? 0,
                'multiple_responses' => $data['multiple_responses'] ?? 0,
                'show_results' => $data['show_results'] ?? 1,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert survey
            $surveyId = $this->surveyModel->insert($surveyData);

            if (!$surveyId) {
                throw new \Exception('Gagal menyimpan survey: ' . json_encode($this->surveyModel->errors()));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Survey berhasil dibuat',
                'data' => [
                    'survey_id' => $surveyId,
                    'title' => $data['title'],
                    'status' => $surveyData['status']
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in SurveyService::createSurvey: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat survey: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Add question to survey
     * Adds new question with answer options
     * 
     * @param int $surveyId Survey ID
     * @param array $questionData Question data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function addQuestion(int $surveyId, array $questionData): array
    {
        $this->db->transStart();

        try {
            // Validate survey exists
            $survey = $this->surveyModel->find($surveyId);

            if (!$survey) {
                return [
                    'success' => false,
                    'message' => 'Survey tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate required fields
            if (empty($questionData['question_text'])) {
                return [
                    'success' => false,
                    'message' => 'Teks pertanyaan harus diisi',
                    'data' => null
                ];
            }

            // Auto-generate sort order if not provided
            if (empty($questionData['sort_order'])) {
                $maxSort = $this->questionModel
                    ->selectMax('sort_order')
                    ->where('survey_id', $surveyId)
                    ->first();

                $questionData['sort_order'] = ($maxSort->sort_order ?? 0) + 1;
            }

            // Prepare question data
            $data = [
                'survey_id' => $surveyId,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'] ?? 'text',
                'is_required' => $questionData['is_required'] ?? 0,
                'options' => isset($questionData['options']) ? json_encode($questionData['options']) : null,
                'sort_order' => $questionData['sort_order'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert question
            $questionId = $this->questionModel->insert($data);

            if (!$questionId) {
                throw new \Exception('Gagal menyimpan pertanyaan: ' . json_encode($this->questionModel->errors()));
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Pertanyaan berhasil ditambahkan',
                'data' => [
                    'question_id' => $questionId,
                    'survey_id' => $surveyId,
                    'sort_order' => $data['sort_order']
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in SurveyService::addQuestion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambah pertanyaan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update existing question
     * Updates question data including text, type, and options
     * 
     * @param int $questionId Question ID
     * @param array $questionData Update data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateQuestion(int $questionId, array $questionData): array
    {
        try {
            $question = $this->questionModel->find($questionId);

            if (!$question) {
                return [
                    'success' => false,
                    'message' => 'Pertanyaan tidak ditemukan',
                    'data' => null
                ];
            }

            // Prepare update data
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (isset($questionData['question_text'])) {
                $updateData['question_text'] = $questionData['question_text'];
            }

            if (isset($questionData['question_type'])) {
                $updateData['question_type'] = $questionData['question_type'];
            }

            if (isset($questionData['is_required'])) {
                $updateData['is_required'] = $questionData['is_required'];
            }

            if (isset($questionData['options'])) {
                $updateData['options'] = json_encode($questionData['options']);
            }

            if (isset($questionData['sort_order'])) {
                $updateData['sort_order'] = $questionData['sort_order'];
            }

            // Update question
            $updated = $this->questionModel->update($questionId, $updateData);

            if (!$updated) {
                throw new \Exception('Gagal update pertanyaan');
            }

            return [
                'success' => true,
                'message' => 'Pertanyaan berhasil diupdate',
                'data' => [
                    'question_id' => $questionId
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyService::updateQuestion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal update pertanyaan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete question from survey
     * Removes question and related responses
     * 
     * @param int $questionId Question ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function deleteQuestion(int $questionId): array
    {
        $this->db->transStart();

        try {
            $question = $this->questionModel->find($questionId);

            if (!$question) {
                return [
                    'success' => false,
                    'message' => 'Pertanyaan tidak ditemukan',
                    'data' => null
                ];
            }

            // Delete question (responses will be handled by CASCADE or manually if needed)
            $this->questionModel->delete($questionId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Pertanyaan berhasil dihapus',
                'data' => [
                    'question_id' => $questionId,
                    'survey_id' => $question->survey_id
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in SurveyService::deleteQuestion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal hapus pertanyaan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Submit survey response
     * Saves user's answers to survey questions
     * 
     * @param int $surveyId Survey ID
     * @param int $userId User ID (null if anonymous)
     * @param array $answers Array of question_id => answer
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function submitResponse(int $surveyId, ?int $userId, array $answers): array
    {
        $this->db->transStart();

        try {
            // Validate survey exists and is open
            $survey = $this->surveyModel->find($surveyId);

            if (!$survey) {
                return [
                    'success' => false,
                    'message' => 'Survey tidak ditemukan',
                    'data' => null
                ];
            }

            if ($survey->status !== 'published') {
                return [
                    'success' => false,
                    'message' => 'Survey tidak tersedia untuk diisi',
                    'data' => null
                ];
            }

            // Check if survey is still open
            if ($survey->end_date && strtotime($survey->end_date) < time()) {
                return [
                    'success' => false,
                    'message' => 'Survey sudah ditutup',
                    'data' => null
                ];
            }

            // Check if user already responded (if not allowing multiple responses)
            if ($userId && !$survey->multiple_responses) {
                $existing = $this->responseModel
                    ->where('survey_id', $surveyId)
                    ->where('user_id', $userId)
                    ->first();

                if ($existing) {
                    return [
                        'success' => false,
                        'message' => 'Anda sudah mengisi survey ini',
                        'data' => null
                    ];
                }
            }

            // Get all questions for validation
            $questions = $this->questionModel
                ->where('survey_id', $surveyId)
                ->findAll();

            // Validate required questions
            foreach ($questions as $question) {
                if ($question->is_required && empty($answers[$question->id])) {
                    return [
                        'success' => false,
                        'message' => 'Pertanyaan wajib belum dijawab: ' . $question->question_text,
                        'data' => null
                    ];
                }
            }

            // Create response record
            $responseData = [
                'survey_id' => $surveyId,
                'user_id' => $userId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'submitted_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $responseId = $this->responseModel->insert($responseData);

            if (!$responseId) {
                throw new \Exception('Gagal menyimpan response');
            }

            // Save answers
            $answersTable = $this->db->table('survey_answers');
            foreach ($answers as $questionId => $answer) {
                $answerData = [
                    'response_id' => $responseId,
                    'question_id' => $questionId,
                    'answer_text' => is_array($answer) ? json_encode($answer) : $answer,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $answersTable->insert($answerData);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Terima kasih! Jawaban Anda telah tersimpan',
                'data' => [
                    'response_id' => $responseId,
                    'survey_id' => $surveyId,
                    'answers_count' => count($answers)
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in SurveyService::submitResponse: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get survey results with statistics
     * Returns comprehensive survey analytics
     * 
     * @param int $surveyId Survey ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getResults(int $surveyId): array
    {
        try {
            $survey = $this->surveyModel->find($surveyId);

            if (!$survey) {
                return [
                    'success' => false,
                    'message' => 'Survey tidak ditemukan',
                    'data' => null
                ];
            }

            // Get total responses
            $totalResponses = $this->responseModel
                ->where('survey_id', $surveyId)
                ->countAllResults();

            // Get questions with answers
            $questions = $this->questionModel
                ->where('survey_id', $surveyId)
                ->orderBy('sort_order', 'ASC')
                ->findAll();

            $questionsWithStats = [];

            foreach ($questions as $question) {
                $questionStats = [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'total_answers' => 0,
                    'answers' => []
                ];

                // Get all answers for this question
                $answers = $this->db->table('survey_answers')
                    ->select('answer_text')
                    ->where('question_id', $question->id)
                    ->get()
                    ->getResult();

                $questionStats['total_answers'] = count($answers);

                // Process answers based on question type
                if (in_array($question->question_type, ['multiple_choice', 'checkbox', 'radio'])) {
                    // Count frequency for choice-based questions
                    $frequency = [];

                    foreach ($answers as $answer) {
                        $answerValue = $answer->answer_text;

                        // Handle array answers (checkboxes)
                        if ($this->isJson($answerValue)) {
                            $values = json_decode($answerValue, true);
                            foreach ($values as $val) {
                                $frequency[$val] = ($frequency[$val] ?? 0) + 1;
                            }
                        } else {
                            $frequency[$answerValue] = ($frequency[$answerValue] ?? 0) + 1;
                        }
                    }

                    // Calculate percentages
                    foreach ($frequency as $option => $count) {
                        $questionStats['answers'][] = [
                            'option' => $option,
                            'count' => $count,
                            'percentage' => $totalResponses > 0 ? round(($count / $totalResponses) * 100, 2) : 0
                        ];
                    }
                } else {
                    // For text-based questions, just list answers
                    foreach ($answers as $answer) {
                        $questionStats['answers'][] = [
                            'answer' => $answer->answer_text
                        ];
                    }
                }

                $questionsWithStats[] = $questionStats;
            }

            return [
                'success' => true,
                'message' => 'Hasil survey berhasil diambil',
                'data' => [
                    'survey' => [
                        'id' => $survey->id,
                        'title' => $survey->title,
                        'description' => $survey->description,
                        'status' => $survey->status,
                        'start_date' => $survey->start_date,
                        'end_date' => $survey->end_date
                    ],
                    'total_responses' => $totalResponses,
                    'questions' => $questionsWithStats
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyService::getResults: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil hasil survey: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Export survey results to Excel
     * Generates Excel file with survey responses
     * 
     * @param int $surveyId Survey ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function exportResults(int $surveyId): array
    {
        try {
            $results = $this->getResults($surveyId);

            if (!$results['success']) {
                return $results;
            }

            $data = $results['data'];

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set title
            $sheet->setCellValue('A1', 'Survey: ' . $data['survey']['title']);
            $sheet->setCellValue('A2', 'Total Responses: ' . $data['total_responses']);
            $sheet->setCellValue('A3', 'Exported at: ' . date('Y-m-d H:i:s'));

            $row = 5;

            // Add questions and answers
            foreach ($data['questions'] as $question) {
                $sheet->setCellValue('A' . $row, $question['question_text']);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;

                if (isset($question['answers'][0]['option'])) {
                    // Choice-based question
                    $sheet->setCellValue('A' . $row, 'Option');
                    $sheet->setCellValue('B' . $row, 'Count');
                    $sheet->setCellValue('C' . $row, 'Percentage');
                    $row++;

                    foreach ($question['answers'] as $answer) {
                        $sheet->setCellValue('A' . $row, $answer['option']);
                        $sheet->setCellValue('B' . $row, $answer['count']);
                        $sheet->setCellValue('C' . $row, $answer['percentage'] . '%');
                        $row++;
                    }
                } else {
                    // Text-based question
                    foreach ($question['answers'] as $answer) {
                        $sheet->setCellValue('A' . $row, $answer['answer']);
                        $row++;
                    }
                }

                $row += 2; // Add spacing
            }

            // Generate filename
            $filename = 'survey_' . $surveyId . '_results_' . date('Ymd_His') . '.xlsx';
            $filepath = WRITEPATH . 'uploads/exports/' . $filename;

            // Ensure directory exists
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            // Save file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            return [
                'success' => true,
                'message' => 'Hasil survey berhasil diexport',
                'data' => [
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'download_url' => base_url('admin/surveys/download/' . $filename)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyService::exportResults: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal export hasil: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Publish survey
     * Changes survey status to published
     * 
     * @param int $surveyId Survey ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function publishSurvey(int $surveyId): array
    {
        try {
            $survey = $this->surveyModel->find($surveyId);

            if (!$survey) {
                return [
                    'success' => false,
                    'message' => 'Survey tidak ditemukan',
                    'data' => null
                ];
            }

            // Check if survey has questions
            $questionCount = $this->questionModel
                ->where('survey_id', $surveyId)
                ->countAllResults();

            if ($questionCount === 0) {
                return [
                    'success' => false,
                    'message' => 'Survey harus memiliki minimal 1 pertanyaan',
                    'data' => null
                ];
            }

            // Update status
            $this->surveyModel->update($surveyId, [
                'status' => 'published',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Survey berhasil dipublikasikan',
                'data' => [
                    'survey_id' => $surveyId,
                    'status' => 'published'
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyService::publishSurvey: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal publish survey: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Close survey
     * Changes survey status to closed
     * 
     * @param int $surveyId Survey ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function closeSurvey(int $surveyId): array
    {
        try {
            $survey = $this->surveyModel->find($surveyId);

            if (!$survey) {
                return [
                    'success' => false,
                    'message' => 'Survey tidak ditemukan',
                    'data' => null
                ];
            }

            // Update status
            $this->surveyModel->update($surveyId, [
                'status' => 'closed',
                'end_date' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Survey berhasil ditutup',
                'data' => [
                    'survey_id' => $surveyId,
                    'status' => 'closed'
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in SurveyService::closeSurvey: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal close survey: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if string is valid JSON
     * Helper method for answer parsing
     * 
     * @param string $string String to check
     * @return bool True if valid JSON
     */
    protected function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
