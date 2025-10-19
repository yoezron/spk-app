<?php

namespace App\Services;

use App\Models\StudyProgramModel;
use App\Models\UniversityModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * StudyProgramImportService
 * 
 * Service untuk handle import bulk study programs dari Excel/CSV
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class StudyProgramImportService
{
    protected $studyProgramModel;
    protected $universityModel;

    protected $results = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    // Valid levels
    protected $validLevels = ['D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'];

    public function __construct()
    {
        $this->studyProgramModel = new StudyProgramModel();
        $this->universityModel = new UniversityModel();
    }

    /**
     * Generate template Excel untuk import study programs
     * 
     * @param string $filePath
     * @return array
     */
    public function generateTemplate(string $filePath): array
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $headers = ['Nama Perguruan Tinggi*', 'Nama Program Studi*', 'Jenjang*', 'Kode'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCCCCC');

            // Example data
            $examples = [
                ['Universitas Padjadjaran', 'Teknik Informatika', 'S1', 'IF'],
                ['Universitas Padjadjaran', 'Ilmu Komunikasi', 'S1', 'ILKOM'],
                ['Institut Teknologi Bandung', 'Teknik Elektro', 'S1', 'TE'],
                ['Institut Teknologi Bandung', 'Magister Teknik Informatika', 'S2', 'MTI'],
                ['Politeknik Negeri Bandung', 'Teknik Komputer', 'D3', 'TK'],
            ];

            $row = 2;
            foreach ($examples as $example) {
                $sheet->fromArray($example, null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            $sheet->getColumnDimension('A')->setWidth(35);
            $sheet->getColumnDimension('B')->setWidth(40);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(12);

            // Add notes
            $sheet->setCellValue('F2', 'CATATAN:');
            $sheet->setCellValue('F3', '1. Nama PT, Nama Prodi & Jenjang wajib diisi (*)');
            $sheet->setCellValue('F4', '2. Nama PT harus PERSIS sama dengan master');
            $sheet->setCellValue('F5', '3. Kode program studi opsional');
            $sheet->setCellValue('F6', '4. Hapus baris contoh sebelum upload');
            $sheet->setCellValue('F7', '');
            $sheet->setCellValue('F8', 'JENJANG YANG VALID:');
            $sheet->setCellValue('F9', '- D3 (Diploma 3)');
            $sheet->setCellValue('F10', '- D4 (Diploma 4)');
            $sheet->setCellValue('F11', '- S1 (Sarjana)');
            $sheet->setCellValue('F12', '- S2 (Magister)');
            $sheet->setCellValue('F13', '- S3 (Doktor)');
            $sheet->setCellValue('F14', '- Profesi');
            $sheet->setCellValue('F15', '');
            $sheet->setCellValue('F16', 'DAFTAR PERGURUAN TINGGI:');

            $sheet->getStyle('F2:F16')->getFont()->setBold(true);
            $sheet->getStyle('F9:F14')->getFont()->setItalic(true);

            // List valid universities
            $universities = $this->universityModel->orderBy('name', 'ASC')->findAll();
            $universityRow = 17;
            foreach ($universities as $university) {
                $sheet->setCellValue("F{$universityRow}", $university->name);
                $universityRow++;
            }

            $sheet->getStyle("F17:F{$universityRow}")->getFont()->setItalic(true);

            // Save
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return [
                'success' => true,
                'message' => 'Template berhasil dibuat',
                'file_path' => $filePath
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error generating study program template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import study programs from Excel file
     * 
     * @param string $filePath
     * @return array
     */
    public function import(string $filePath): array
    {
        try {
            $this->resetResults();

            // Read Excel
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header
            array_shift($rows);

            $this->results['total'] = count($rows);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) {
                    $this->results['total']--;
                    continue;
                }

                $universityName = trim($row[0]);
                $programName = trim($row[1]);
                $level = trim($row[2]);
                $code = !empty($row[3]) ? trim($row[3]) : null;

                // Validate
                $validation = $this->validateRow($universityName, $programName, $level, $code, $rowNumber);
                if (!$validation['valid']) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'university' => $universityName,
                        'program' => $programName,
                        'level' => $level,
                        'errors' => $validation['errors']
                    ];
                    continue;
                }

                // Find university
                $university = $this->universityModel->where('name', $universityName)->first();
                if (!$university) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'university' => $universityName,
                        'program' => $programName,
                        'level' => $level,
                        'errors' => ["Perguruan Tinggi '{$universityName}' tidak ditemukan"]
                    ];
                    continue;
                }

                // Check duplicate
                $existing = $this->studyProgramModel
                    ->where('university_id', $university->id)
                    ->where('name', $programName)
                    ->where('level', $level)
                    ->first();

                if ($existing) {
                    $this->results['duplicates']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'university' => $universityName,
                        'program' => $programName,
                        'level' => $level,
                        'errors' => ['Program Studi ini sudah ada di universitas tersebut']
                    ];
                    continue;
                }

                // Insert
                try {
                    $this->studyProgramModel->insert([
                        'university_id' => $university->id,
                        'name' => $programName,
                        'level' => $level,
                        'code' => $code,
                        'is_active' => 1
                    ]);
                    $this->results['success']++;
                } catch (\Exception $e) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'university' => $universityName,
                        'program' => $programName,
                        'level' => $level,
                        'errors' => ['Gagal menyimpan: ' . $e->getMessage()]
                    ];
                }
            }

            return [
                'success' => true,
                'message' => $this->getResultMessage(),
                'data' => $this->results
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error importing study programs: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal import data: ' . $e->getMessage(),
                'data' => $this->results
            ];
        }
    }

    /**
     * Validate row data
     * 
     * @param string $universityName
     * @param string $programName
     * @param string $level
     * @param string|null $code
     * @param int $rowNumber
     * @return array
     */
    protected function validateRow(string $universityName, string $programName, string $level, ?string $code, int $rowNumber): array
    {
        $errors = [];

        // Validate university name
        if (empty($universityName)) {
            $errors[] = 'Nama perguruan tinggi wajib diisi';
        }

        // Validate program name
        if (empty($programName)) {
            $errors[] = 'Nama program studi wajib diisi';
        } elseif (strlen($programName) < 3) {
            $errors[] = 'Nama program studi minimal 3 karakter';
        } elseif (strlen($programName) > 255) {
            $errors[] = 'Nama program studi maksimal 255 karakter';
        }

        // Validate level
        if (empty($level)) {
            $errors[] = 'Jenjang wajib diisi';
        } elseif (!in_array($level, $this->validLevels)) {
            $errors[] = "Jenjang tidak valid. Harus: " . implode(', ', $this->validLevels);
        }

        // Validate code if provided
        if ($code && strlen($code) > 20) {
            $errors[] = 'Kode maksimal 20 karakter';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Reset results
     */
    protected function resetResults(): void
    {
        $this->results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'duplicates' => 0,
            'errors' => []
        ];
    }

    /**
     * Get result message
     * 
     * @return string
     */
    protected function getResultMessage(): string
    {
        $success = $this->results['success'];
        $failed = $this->results['failed'];
        $duplicates = $this->results['duplicates'];

        $message = "Import selesai. ";
        $message .= "{$success} program studi berhasil ditambahkan";

        if ($failed > 0) {
            $message .= ", {$failed} gagal";
        }

        if ($duplicates > 0) {
            $message .= ", {$duplicates} duplikat";
        }

        return $message;
    }

    /**
     * Get import results
     * 
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
