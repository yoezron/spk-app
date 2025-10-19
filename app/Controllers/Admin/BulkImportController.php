<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Member\BulkImportService;
use App\Services\FileUploadService;
use App\Models\ImportLogModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * BulkImportController
 * 
 * Mengelola bulk import anggota dari Excel/CSV
 * Download template, upload, preview, validate, dan process import
 * Support error handling dan import history
 * 
 * @package App\Controllers\Admin
 * @author  SPK Development Team
 * @version 1.0.0
 */
class BulkImportController extends BaseController
{
    /**
     * @var BulkImportService
     */
    protected $importService;

    /**
     * @var FileUploadService
     */
    protected $fileUploadService;

    /**
     * @var ImportLogModel
     */
    protected $importLogModel;

    /**
     * Maximum file size in MB
     */
    protected const MAX_FILE_SIZE = 10;

    /**
     * Allowed file extensions
     */
    protected const ALLOWED_EXTENSIONS = ['xlsx', 'xls', 'csv'];

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->importService = new BulkImportService();
        $this->fileUploadService = new FileUploadService();
        $this->importLogModel = new ImportLogModel();
    }

    /**
     * Display bulk import page
     * Shows upload form and recent import history
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengimpor data anggota');
        }

        $user = auth()->user();

        // Get recent import history
        $recentImports = $this->importLogModel
            ->select('import_logs.*, users.email')
            ->join('users', 'users.id = import_logs.user_id')
            ->orderBy('import_logs.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $data = [
            'title' => 'Import Data Anggota',
            'recent_imports' => $recentImports,
            'max_file_size' => self::MAX_FILE_SIZE,
            'allowed_extensions' => self::ALLOWED_EXTENSIONS
        ];

        return view('admin/members/import/index', $data);
    }

    /**
     * Download Excel template for bulk import
     * Template includes headers, instructions, and sample data
     * 
     * @return ResponseInterface
     */
    public function downloadTemplate(): ResponseInterface
    {
        try {
            // Create new spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Anggota');

            // Define headers
            $headers = [
                'A' => 'email',
                'B' => 'password',
                'C' => 'full_name',
                'D' => 'phone',
                'E' => 'province_name',
                'F' => 'regency_name',
                'G' => 'university_name',
                'H' => 'study_program_name',
                'I' => 'position_type',
                'J' => 'employee_status',
                'K' => 'work_unit',
                'L' => 'nip',
                'M' => 'join_date',
                'N' => 'membership_status'
            ];

            // Set header row
            $row = 1;
            foreach ($headers as $col => $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
            }

            // Style header row
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ];
            $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

            // Add sample data
            $sampleData = [
                [
                    'anggota1@example.com',
                    'password123',
                    'John Doe',
                    '081234567890',
                    'DKI Jakarta',
                    'Jakarta Selatan',
                    'Universitas Indonesia',
                    'Teknik Informatika',
                    'Dosen',
                    'Tetap',
                    'Fakultas Ilmu Komputer',
                    '198501012010011001',
                    '2024-01-15',
                    'anggota'
                ],
                [
                    'anggota2@example.com',
                    'password456',
                    'Jane Smith',
                    '082345678901',
                    'Jawa Barat',
                    'Bandung',
                    'Institut Teknologi Bandung',
                    'Teknik Elektro',
                    'Tenaga Kependidikan',
                    'Kontrak',
                    'Bagian Akademik',
                    '',
                    '2024-02-20',
                    'anggota'
                ]
            ];

            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue("{$col}{$row}", $value);
                    $col++;
                }
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'N') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Add instructions sheet
            $instructionSheet = $spreadsheet->createSheet(1);
            $instructionSheet->setTitle('Instruksi');

            $instructions = [
                ['PANDUAN IMPORT DATA ANGGOTA SPK'],
                [''],
                ['Format File:'],
                ['- File harus dalam format Excel (.xlsx, .xls) atau CSV (.csv)'],
                ['- Ukuran maksimal: ' . self::MAX_FILE_SIZE . ' MB'],
                [''],
                ['Kolom Wajib Diisi:'],
                ['- email: Email anggota (harus unique)'],
                ['- password: Password default untuk anggota'],
                ['- full_name: Nama lengkap'],
                ['- phone: Nomor telepon'],
                ['- province_name: Nama provinsi (harus sesuai dengan master data)'],
                ['- university_name: Nama universitas/perguruan tinggi'],
                [''],
                ['Kolom Opsional:'],
                ['- regency_name: Nama kabupaten/kota'],
                ['- study_program_name: Program studi'],
                ['- position_type: Jenis kepegawaian (Dosen/Tenaga Kependidikan/Staff/dll)'],
                ['- employee_status: Status kepegawaian (Tetap/Kontrak/Honorer)'],
                ['- work_unit: Unit kerja'],
                ['- nip: Nomor Induk Pegawai'],
                ['- join_date: Tanggal bergabung (format: YYYY-MM-DD)'],
                ['- membership_status: Status keanggotaan (anggota/calon_anggota)'],
                [''],
                ['Catatan Penting:'],
                ['1. Pastikan nama provinsi dan universitas sesuai dengan master data yang ada'],
                ['2. Email harus unique, jika sudah ada akan di-update'],
                ['3. Password akan di-hash otomatis oleh sistem'],
                ['4. Format tanggal harus: YYYY-MM-DD (contoh: 2024-01-15)'],
                ['5. Hapus baris contoh sebelum mengimpor data sebenarnya'],
                ['6. Maksimal 1000 baris per import']
            ];

            $row = 1;
            foreach ($instructions as $instruction) {
                $instructionSheet->setCellValue("A{$row}", $instruction[0]);
                $row++;
            }

            // Style instruction title
            $instructionSheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '4472C4']
                ]
            ]);

            // Auto-size instruction column
            $instructionSheet->getColumnDimension('A')->setWidth(80);

            // Set active sheet back to data sheet
            $spreadsheet->setActiveSheetIndex(0);

            // Generate filename
            $filename = 'template_import_anggota_' . date('YmdHis') . '.xlsx';

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            log_message('error', 'Error in BulkImportController::downloadTemplate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    /**
     * Upload Excel/CSV file
     * Validate file and store temporarily for preview
     * 
     * @return ResponseInterface JSON response
     */
    public function uploadFile(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // Check permission
        if (!auth()->user()->can('member.import')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengimpor data'
            ])->setStatusCode(403);
        }

        try {
            $file = $this->request->getFile('file');

            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File tidak valid atau tidak ditemukan'
                ])->setStatusCode(400);
            }

            // Validate file extension
            $extension = $file->getExtension();
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format file tidak didukung. Gunakan: ' . implode(', ', self::ALLOWED_EXTENSIONS)
                ])->setStatusCode(400);
            }

            // Validate file size
            $fileSize = $file->getSize() / 1024 / 1024; // Convert to MB
            if ($fileSize > self::MAX_FILE_SIZE) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ukuran file terlalu besar. Maksimal: ' . self::MAX_FILE_SIZE . ' MB'
                ])->setStatusCode(400);
            }

            // Upload file to temp directory
            $result = $this->fileUploadService->upload($file, 'import');

            if (!$result['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ])->setStatusCode(400);
            }

            // Store file info in session for preview
            session()->set('import_file', [
                'filename' => $result['data']['filename'],
                'path' => $result['data']['path'],
                'original_name' => $file->getName(),
                'uploaded_at' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'File berhasil diunggah',
                'data' => [
                    'filename' => $result['data']['filename'],
                    'original_name' => $file->getName()
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in BulkImportController::uploadFile: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengunggah file: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Preview uploaded file data
     * Validate data and show preview before processing
     * 
     * @return string|ResponseInterface
     */
    public function preview()
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengimpor data anggota');
        }

        // Get file info from session
        $fileInfo = session()->get('import_file');

        if (!$fileInfo || !file_exists($fileInfo['path'])) {
            return redirect()->to('/admin/members/import')->with('error', 'File tidak ditemukan. Silakan upload ulang.');
        }

        try {
            // Parse and validate file
            $result = $this->importService->import($fileInfo['path'], ['preview_only' => true]);

            if (!$result['success']) {
                return redirect()->to('/admin/members/import')->with('error', $result['message']);
            }

            $data = [
                'title' => 'Preview Data Import',
                'file_info' => $fileInfo,
                'preview_data' => $result['data']['rows'] ?? [],
                'total_rows' => $result['data']['total'] ?? 0,
                'valid_rows' => $result['data']['success'] ?? 0,
                'invalid_rows' => $result['data']['failed'] ?? 0,
                'errors' => $result['data']['errors'] ?? []
            ];

            return view('admin/members/import/preview', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in BulkImportController::preview: ' . $e->getMessage());
            return redirect()->to('/admin/members/import')->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Process bulk import
     * Insert/update member data from uploaded file
     * 
     * @return ResponseInterface
     */
    public function process(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengimpor data');
        }

        // Get file info from session
        $fileInfo = session()->get('import_file');

        if (!$fileInfo || !file_exists($fileInfo['path'])) {
            return redirect()->to('/admin/members/import')->with('error', 'File tidak ditemukan. Silakan upload ulang.');
        }

        try {
            $user = auth()->user();

            // Process import using service
            $result = $this->importService->importWithActivation($fileInfo['path'], $user->id);

            // Log import activity
            $this->importLogModel->insert([
                'user_id' => $user->id,
                'filename' => $fileInfo['original_name'],
                'total_rows' => $result['data']['total'] ?? 0,
                'success_rows' => $result['data']['success'] ?? 0,
                'failed_rows' => $result['data']['failed'] ?? 0,
                'status' => $result['success'] ? 'completed' : 'failed',
                'error_log' => !empty($result['data']['errors']) ? json_encode($result['data']['errors']) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Clean up temp file
            if (file_exists($fileInfo['path'])) {
                unlink($fileInfo['path']);
            }

            // Clear session
            session()->remove('import_file');

            if ($result['success']) {
                $message = "Import berhasil! {$result['data']['success_count']} data berhasil diimpor";

                if (!empty($result['data']['failed_count'])) {
                    $message .= ", {$result['data']['failed_count']} data gagal";
                }

                return redirect()->to('/admin/members/import')->with('success', $message);
            }

            return redirect()->to('/admin/members/import')->with('error', $result['message']);
        } catch (\Exception $e) {
            log_message('error', 'Error in BulkImportController::process: ' . $e->getMessage());
            return redirect()->to('/admin/members/import')->with('error', 'Gagal memproses import: ' . $e->getMessage());
        }
    }

    /**
     * View import history
     * Show all past imports with details and logs
     * 
     * @return string|ResponseInterface
     */
    public function history()
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat riwayat import');
        }

        // Get search filter
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $builder = $this->importLogModel
            ->select('import_logs.*, users.email')
            ->join('users', 'users.id = import_logs.user_id')
            ->orderBy('import_logs.created_at', 'DESC');

        // Apply filters
        if (!empty($search)) {
            $builder->groupStart()
                ->like('import_logs.filename', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        if (!empty($status)) {
            $builder->where('import_logs.status', $status);
        }

        $imports = $builder->paginate(20);

        $data = [
            'title' => 'Riwayat Import',
            'imports' => $imports,
            'pager' => $this->importLogModel->pager,
            'search' => $search,
            'status' => $status
        ];

        return view('admin/members/import/history', $data);
    }

    /**
     * View import detail
     * Show detailed information about specific import
     * 
     * @param int $id Import log ID
     * @return string|ResponseInterface
     */
    public function detail(int $id)
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat detail import');
        }

        $import = $this->importLogModel
            ->select('import_logs.*, users.email')
            ->join('users', 'users.id = import_logs.user_id')
            ->find($id);

        if (!$import) {
            return redirect()->back()->with('error', 'Data import tidak ditemukan');
        }

        // Decode error log if exists
        $errors = [];
        if (!empty($import->error_log)) {
            $errors = json_decode($import->error_log, true);
        }

        $data = [
            'title' => 'Detail Import',
            'import' => $import,
            'errors' => $errors
        ];

        return view('admin/members/import/detail', $data);
    }

    /**
     * Cancel/delete import session
     * Clear uploaded file and session data
     * 
     * @return ResponseInterface
     */
    public function cancel(): ResponseInterface
    {
        $fileInfo = session()->get('import_file');

        // Clean up temp file if exists
        if ($fileInfo && !empty($fileInfo['path']) && file_exists($fileInfo['path'])) {
            unlink($fileInfo['path']);
        }

        // Clear session
        session()->remove('import_file');

        return redirect()->to('/admin/members/import')->with('info', 'Import dibatalkan');
    }

    /**
     * Process import with activation flow
     * Import members with pending_activation status and send activation emails
     * 
     * @return ResponseInterface
     */
    public function processWithActivation(): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengimpor data'
            ])->setStatusCode(403);
        }

        // Get file info from session
        $fileInfo = session()->get('import_file');

        if (!$fileInfo || !file_exists($fileInfo['path'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak ditemukan. Silakan upload ulang.'
            ])->setStatusCode(400);
        }

        try {
            $userId = auth()->user()->id;

            // Process import with activation flow
            $result = $this->importService->importWithActivation($fileInfo['path'], $userId);

            // Clean up temp file
            if (file_exists($fileInfo['path'])) {
                unlink($fileInfo['path']);
            }

            // Clear session
            session()->remove('import_file');

            if ($result['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'redirect_url' => site_url('admin/bulk_import/result/' . $result['data']['import_log_id'])
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => $result['message']
            ])->setStatusCode(400);
        } catch (\Exception $e) {
            log_message('error', 'Error in processWithActivation: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memproses import: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Show import result with activation stats
     * 
     * @param int $importLogId Import log ID
     * @return string|ResponseInterface
     */
    public function result(int $importLogId)
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }

        try {
            $importLog = $this->importLogModel->find($importLogId);

            if (!$importLog) {
                return redirect()->to('admin/bulk_import')
                    ->with('error', 'Import log tidak ditemukan');
            }

            // Get activation stats
            $activationStats = $this->importService->getActivationStats($importLogId);

            // Get error details
            $errorDetails = $this->importLogModel->getErrorDetails($importLogId);

            $data = [
                'title' => 'Hasil Import',
                'importLog' => $importLog,
                'activationStats' => $activationStats,
                'errorDetails' => $errorDetails,
            ];

            return view('admin/bulk_import/result', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error showing result: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Resend activation email to specific member
     * 
     * @param int $userId User ID
     * @return ResponseInterface
     */
    public function resendActivation(int $userId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // Check permission
        if (!auth()->user()->can('member.import')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses'
            ])->setStatusCode(403);
        }

        try {
            $result = $this->importService->resendActivationEmail($userId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error resending activation: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Download error report as CSV
     * 
     * @param int $importLogId Import log ID
     * @return ResponseInterface
     */
    public function downloadErrorReport(int $importLogId): ResponseInterface
    {
        // Check permission
        if (!auth()->user()->can('member.import')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }

        try {
            $importLog = $this->importLogModel->find($importLogId);

            if (!$importLog) {
                return redirect()->back()->with('error', 'Import log tidak ditemukan');
            }

            $errorDetails = $this->importLogModel->getErrorDetails($importLogId);

            if (empty($errorDetails)) {
                return redirect()->back()->with('error', 'Tidak ada error untuk diunduh');
            }

            // Generate CSV
            $filename = 'error_report_' . $importLogId . '_' . date('YmdHis') . '.csv';
            $filepath = WRITEPATH . 'uploads/' . $filename;

            $fp = fopen($filepath, 'w');

            // Header
            fputcsv($fp, ['Row', 'Error', 'Email', 'Username', 'Full Name']);

            // Data
            foreach ($errorDetails as $error) {
                fputcsv($fp, [
                    $error['row'],
                    $error['error'],
                    $error['data']['email'] ?? '',
                    $error['data']['username'] ?? '',
                    $error['data']['full_name'] ?? '',
                ]);
            }

            fclose($fp);

            return $this->response->download($filepath, null)->setFileName($filename);
        } catch (\Exception $e) {
            log_message('error', 'Error downloading error report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get activation statistics for AJAX
     * 
     * @param int $importLogId Import log ID
     * @return ResponseInterface
     */
    public function getActivationStats(int $importLogId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        // Check permission
        if (!auth()->user()->can('member.import')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses'
            ])->setStatusCode(403);
        }

        try {
            $stats = $this->importService->getActivationStats($importLogId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting activation stats: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
