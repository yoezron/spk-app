<?php

namespace App\Services;

use App\Models\ProvinceModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ProvinceImportService
 * 
 * Service untuk handle import bulk provinces dari Excel/CSV
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class ProvinceImportService
{
    protected $provinceModel;

    protected $results = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    public function __construct()
    {
        $this->provinceModel = new ProvinceModel();
    }

    /**
     * Generate template Excel untuk import provinces
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
            $headers = ['Nama Provinsi*', 'Kode'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $sheet->getStyle('A1:B1')->getFont()->setBold(true);
            $sheet->getStyle('A1:B1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCCCCC');

            // Example data
            $examples = [
                ['Jawa Barat', 'JABAR'],
                ['DKI Jakarta', 'DKI'],
                ['Jawa Tengah', 'JATENG'],
            ];

            $row = 2;
            foreach ($examples as $example) {
                $sheet->fromArray($example, null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(15);

            // Add notes
            $sheet->setCellValue('D2', 'CATATAN:');
            $sheet->setCellValue('D3', '1. Nama Provinsi wajib diisi (*)');
            $sheet->setCellValue('D4', '2. Kode provinsi opsional');
            $sheet->setCellValue('D5', '3. Nama provinsi harus unik');
            $sheet->setCellValue('D6', '4. Hapus baris contoh sebelum upload');

            $sheet->getStyle('D2:D6')->getFont()->setItalic(true);

            // Save
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return [
                'success' => true,
                'message' => 'Template berhasil dibuat',
                'file_path' => $filePath
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error generating province template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import provinces from Excel file
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
                $rowNumber = $index + 2; // +2 karena index 0 + header di row 1

                // Skip empty rows
                if (empty($row[0])) {
                    continue;
                }

                $name = trim($row[0]);
                $code = !empty($row[1]) ? trim($row[1]) : null;

                // Validate
                $validation = $this->validateRow($name, $code, $rowNumber);
                if (!$validation['valid']) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $name,
                        'errors' => $validation['errors']
                    ];
                    continue;
                }

                // Check duplicate
                $existing = $this->provinceModel->where('name', $name)->first();
                if ($existing) {
                    $this->results['duplicates']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $name,
                        'errors' => ['Provinsi dengan nama ini sudah ada']
                    ];
                    continue;
                }

                // Insert
                try {
                    $this->provinceModel->insert([
                        'name' => $name,
                        'code' => $code
                    ]);
                    $this->results['success']++;
                } catch (\Exception $e) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $name,
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
            log_message('error', 'Error importing provinces: ' . $e->getMessage());
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
     * @param string|null $code
     * @param int $rowNumber
     * @return array
     */
    protected function validateRow(string $name, ?string $code, int $rowNumber): array
    {
        $errors = [];

        // Validate name
        if (empty($name)) {
            $errors[] = 'Nama provinsi wajib diisi';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Nama provinsi minimal 3 karakter';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Nama provinsi maksimal 100 karakter';
        }

        // Validate code if provided
        if ($code && strlen($code) > 10) {
            $errors[] = 'Kode provinsi maksimal 10 karakter';
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
        $message .= "{$success} provinsi berhasil ditambahkan";

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
