<?php

namespace App\Services;

use App\Models\UniversityModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * UniversityImportService
 * 
 * Service untuk handle import bulk universities dari Excel/CSV
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class UniversityImportService
{
    protected $universityModel;

    protected $results = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    // Valid university types
    protected $validTypes = ['Negeri', 'Swasta', 'Kedinasan'];

    public function __construct()
    {
        $this->universityModel = new UniversityModel();
    }

    /**
     * Generate template Excel untuk import universities
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
            $headers = ['Nama Perguruan Tinggi*', 'Jenis PT*', 'Kode', 'Alamat'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCCCCC');

            // Example data
            $examples = [
                ['Universitas Padjadjaran', 'Negeri', 'UNPAD', 'Jl. Raya Bandung-Sumedang KM.21, Jatinangor'],
                ['Universitas Telkom', 'Swasta', 'TELKOM', 'Jl. Telekomunikasi No.1, Bandung'],
                ['Institut Teknologi Bandung', 'Negeri', 'ITB', 'Jl. Ganesa No.10, Bandung'],
                ['Politeknik Negeri Bandung', 'Negeri', 'POLBAN', 'Jl. Gegerkalong Hilir, Bandung'],
                ['STTN BATAN', 'Kedinasan', 'STTN', 'Jl. Babarsari, Yogyakarta'],
            ];

            $row = 2;
            foreach ($examples as $example) {
                $sheet->fromArray($example, null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            $sheet->getColumnDimension('A')->setWidth(40);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(50);

            // Add notes
            $sheet->setCellValue('F2', 'CATATAN:');
            $sheet->setCellValue('F3', '1. Nama PT & Jenis PT wajib diisi (*)');
            $sheet->setCellValue('F4', '2. Kode & Alamat opsional');
            $sheet->setCellValue('F5', '3. Nama PT harus unik');
            $sheet->setCellValue('F6', '4. Hapus baris contoh sebelum upload');
            $sheet->setCellValue('F7', '');
            $sheet->setCellValue('F8', 'JENIS PT YANG VALID:');
            $sheet->setCellValue('F9', '- Negeri');
            $sheet->setCellValue('F10', '- Swasta');
            $sheet->setCellValue('F11', '- Kedinasan');

            $sheet->getStyle('F2:F11')->getFont()->setBold(true);
            $sheet->getStyle('F9:F11')->getFont()->setItalic(true);

            // Save
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return [
                'success' => true,
                'message' => 'Template berhasil dibuat',
                'file_path' => $filePath
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error generating university template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import universities from Excel file
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

                $name = trim($row[0]);
                $type = trim($row[1]);
                $code = !empty($row[2]) ? trim($row[2]) : null;
                $address = !empty($row[3]) ? trim($row[3]) : null;

                // Validate
                $validation = $this->validateRow($name, $type, $code, $rowNumber);
                if (!$validation['valid']) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $name,
                        'type' => $type,
                        'errors' => $validation['errors']
                    ];
                    continue;
                }

                // Check duplicate
                $existing = $this->universityModel->where('name', $name)->first();
                if ($existing) {
                    $this->results['duplicates']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $name,
                        'type' => $type,
                        'errors' => ['Perguruan Tinggi dengan nama ini sudah ada']
                    ];
                    continue;
                }

                // Insert
                try {
                    $this->universityModel->insert([
                        'name' => $name,
                        'type' => $type,
                        'code' => $code,
                        'address' => $address,
                        'is_active' => 1
                    ]);
                    $this->results['success']++;
                } catch (\Exception $e) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $name,
                        'type' => $type,
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
            log_message('error', 'Error importing universities: ' . $e->getMessage());
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
     * @param string $name
     * @param string $type
     * @param string|null $code
     * @param int $rowNumber
     * @return array
     */
    protected function validateRow(string $name, string $type, ?string $code, int $rowNumber): array
    {
        $errors = [];

        // Validate name
        if (empty($name)) {
            $errors[] = 'Nama perguruan tinggi wajib diisi';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Nama minimal 3 karakter';
        } elseif (strlen($name) > 255) {
            $errors[] = 'Nama maksimal 255 karakter';
        }

        // Validate type
        if (empty($type)) {
            $errors[] = 'Jenis PT wajib diisi';
        } elseif (!in_array($type, $this->validTypes)) {
            $errors[] = "Jenis PT tidak valid. Harus: " . implode(', ', $this->validTypes);
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
        $message .= "{$success} perguruan tinggi berhasil ditambahkan";

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
