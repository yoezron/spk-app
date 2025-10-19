<?php

namespace App\Services;

use App\Models\RegencyModel;
use App\Models\ProvinceModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * RegencyImportService
 * 
 * Service untuk handle import bulk regencies dari Excel/CSV
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class RegencyImportService
{
    protected $regencyModel;
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
        $this->regencyModel = new RegencyModel();
        $this->provinceModel = new ProvinceModel();
    }

    /**
     * Generate template Excel untuk import regencies
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
            $headers = ['Nama Provinsi*', 'Nama Kabupaten/Kota*', 'Kode'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $sheet->getStyle('A1:C1')->getFont()->setBold(true);
            $sheet->getStyle('A1:C1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCCCCC');

            // Get provinces for examples
            $provinces = $this->provinceModel->orderBy('name', 'ASC')->findAll();

            // Example data
            $examples = [
                ['Jawa Barat', 'Kota Bandung', 'BDG'],
                ['Jawa Barat', 'Kabupaten Bandung', 'KBBDG'],
                ['DKI Jakarta', 'Jakarta Pusat', 'JKTPST'],
                ['Jawa Tengah', 'Kota Semarang', 'SMG'],
            ];

            $row = 2;
            foreach ($examples as $example) {
                $sheet->fromArray($example, null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(35);
            $sheet->getColumnDimension('C')->setWidth(15);

            // Add notes
            $sheet->setCellValue('E2', 'CATATAN:');
            $sheet->setCellValue('E3', '1. Nama Provinsi & Kabupaten/Kota wajib diisi (*)');
            $sheet->setCellValue('E4', '2. Nama Provinsi harus PERSIS sama dengan master');
            $sheet->setCellValue('E5', '3. Kode kabupaten/kota opsional');
            $sheet->setCellValue('E6', '4. Hapus baris contoh sebelum upload');
            $sheet->setCellValue('E7', '');
            $sheet->setCellValue('E8', 'DAFTAR PROVINSI YANG VALID:');

            $sheet->getStyle('E2:E8')->getFont()->setBold(true);

            // List valid provinces
            $provinceRow = 9;
            foreach ($provinces as $province) {
                $sheet->setCellValue("E{$provinceRow}", $province->name);
                $provinceRow++;
            }

            $sheet->getStyle('E9:E' . ($provinceRow - 1))->getFont()->setItalic(true);

            // Save
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return [
                'success' => true,
                'message' => 'Template berhasil dibuat',
                'file_path' => $filePath
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error generating regency template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import regencies from Excel file
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

                $provinceName = trim($row[0]);
                $regencyName = trim($row[1]);
                $code = !empty($row[2]) ? trim($row[2]) : null;

                // Validate
                $validation = $this->validateRow($provinceName, $regencyName, $code, $rowNumber);
                if (!$validation['valid']) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'province' => $provinceName,
                        'regency' => $regencyName,
                        'errors' => $validation['errors']
                    ];
                    continue;
                }

                // Find province
                $province = $this->provinceModel->where('name', $provinceName)->first();
                if (!$province) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'province' => $provinceName,
                        'regency' => $regencyName,
                        'errors' => ["Provinsi '{$provinceName}' tidak ditemukan"]
                    ];
                    continue;
                }

                // Check duplicate
                $existing = $this->regencyModel
                    ->where('province_id', $province->id)
                    ->where('name', $regencyName)
                    ->first();

                if ($existing) {
                    $this->results['duplicates']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'province' => $provinceName,
                        'regency' => $regencyName,
                        'errors' => ['Kabupaten/Kota ini sudah ada di provinsi tersebut']
                    ];
                    continue;
                }

                // Insert
                try {
                    $this->regencyModel->insert([
                        'province_id' => $province->id,
                        'name' => $regencyName,
                        'code' => $code,
                        'type' => $this->detectType($regencyName)
                    ]);
                    $this->results['success']++;
                } catch (\Exception $e) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'province' => $provinceName,
                        'regency' => $regencyName,
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
            log_message('error', 'Error importing regencies: ' . $e->getMessage());
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
     * @param string $provinceName
     * @param string $regencyName
     * @param string|null $code
     * @param int $rowNumber
     * @return array
     */
    protected function validateRow(string $provinceName, string $regencyName, ?string $code, int $rowNumber): array
    {
        $errors = [];

        // Validate province name
        if (empty($provinceName)) {
            $errors[] = 'Nama provinsi wajib diisi';
        }

        // Validate regency name
        if (empty($regencyName)) {
            $errors[] = 'Nama kabupaten/kota wajib diisi';
        } elseif (strlen($regencyName) < 3) {
            $errors[] = 'Nama kabupaten/kota minimal 3 karakter';
        } elseif (strlen($regencyName) > 100) {
            $errors[] = 'Nama kabupaten/kota maksimal 100 karakter';
        }

        // Validate code if provided
        if ($code && strlen($code) > 10) {
            $errors[] = 'Kode maksimal 10 karakter';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Detect type (Kabupaten or Kota) from name
     * 
     * @param string $name
     * @return string
     */
    protected function detectType(string $name): string
    {
        $lowercaseName = strtolower($name);

        if (
            strpos($lowercaseName, 'kota ') === 0 ||
            strpos($lowercaseName, 'kota') !== false
        ) {
            return 'Kota';
        }

        return 'Kabupaten';
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
        $message .= "{$success} kabupaten/kota berhasil ditambahkan";

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
