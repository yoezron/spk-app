<?php

namespace App\Services\Member;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use App\Models\ProvinceModel;
use App\Models\UniversityModel;
use App\Models\StudyProgramModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * BulkImportService
 * 
 * Menangani import massal data anggota dari file Excel/CSV
 * Termasuk validasi, duplicate detection, dan error reporting
 * 
 * @package App\Services\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class BulkImportService
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberProfileModel
     */
    protected $memberModel;

    /**
     * @var ProvinceModel
     */
    protected $provinceModel;

    /**
     * @var UniversityModel
     */
    protected $universityModel;

    /**
     * @var StudyProgramModel
     */
    protected $prodiModel;

    /**
     * @var \App\Services\Communication\EmailService
     */
    protected $emailService;

    /**
     * @var \App\Models\ImportLogModel
     */
    protected $importLogModel;

    /**
     * Import results tracking
     */
    protected $results = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
        $this->provinceModel = new ProvinceModel();
        $this->universityModel = new UniversityModel();
        $this->prodiModel = new StudyProgramModel();
        $this->emailService = new \App\Services\Communication\EmailService();
        $this->importLogModel = model('ImportLogModel');
    }

    /**
     * Main import method
     * Process Excel/CSV file and import members
     * 
     * @param string $filePath Path to uploaded file
     * @param array $options Import options
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function import(string $filePath, array $options = []): array
    {
        try {
            // Reset results
            $this->resetResults();

            // Determine file type
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            // Read file based on extension
            if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
                $data = $this->readExcel($filePath);
            } elseif (strtolower($extension) === 'csv') {
                $data = $this->readCSV($filePath);
            } else {
                return [
                    'success' => false,
                    'message' => 'Format file tidak didukung. Gunakan Excel (.xlsx, .xls) atau CSV (.csv)',
                    'data' => null
                ];
            }

            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'File kosong atau tidak dapat dibaca',
                    'data' => null
                ];
            }

            // Validate headers
            $headerValidation = $this->validateHeaders($data[0]);
            if (!$headerValidation['success']) {
                return $headerValidation;
            }

            // Remove header row
            $headers = array_shift($data);

            // Process each row
            $this->results['total'] = count($data);

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because header is row 1, and array is 0-indexed

                // Map row data to associative array
                $rowData = $this->mapRowData($headers, $row);

                // Process row
                $result = $this->processRow($rowData, $rowNumber, $options);

                if (!$result['success']) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'data' => $rowData,
                        'error' => $result['message']
                    ];
                } else {
                    if ($result['duplicate']) {
                        $this->results['duplicates']++;
                    } else {
                        $this->results['success']++;
                    }
                }
            }

            // Generate report
            $report = $this->generateReport();

            return [
                'success' => $this->results['failed'] < $this->results['total'],
                'message' => sprintf(
                    'Import selesai: %d berhasil, %d gagal, %d duplikat dari %d data',
                    $this->results['success'],
                    $this->results['failed'],
                    $this->results['duplicates'],
                    $this->results['total']
                ),
                'data' => [
                    'results' => $this->results,
                    'report' => $report
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in BulkImportService::import: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error import: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Process single row of data
     * 
     * @param array $rowData Row data as associative array
     * @param int $rowNumber Row number for error reporting
     * @param array $options Processing options
     * @return array ['success' => bool, 'message' => string, 'duplicate' => bool]
     */
    protected function processRow(array $rowData, int $rowNumber, array $options = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Skip empty rows
            if ($this->isEmptyRow($rowData)) {
                $db->transComplete();
                return [
                    'success' => true,
                    'message' => 'Empty row skipped',
                    'duplicate' => false
                ];
            }

            // Validate row data
            $validation = $this->validateRowData($rowData, $rowNumber);
            if (!$validation['success']) {
                return $validation;
            }

            // Check for duplicates
            $duplicate = $this->checkDuplicate($rowData);
            if ($duplicate) {
                $db->transComplete();

                // Handle duplicate based on options
                if (isset($options['skip_duplicates']) && $options['skip_duplicates']) {
                    return [
                        'success' => true,
                        'message' => 'Duplicate skipped',
                        'duplicate' => true
                    ];
                } elseif (isset($options['update_duplicates']) && $options['update_duplicates']) {
                    // Update existing member
                    return $this->updateExistingMember($duplicate, $rowData);
                } else {
                    return [
                        'success' => false,
                        'message' => 'Email atau username sudah terdaftar',
                        'duplicate' => true
                    ];
                }
            }

            // Create user account
            $user = $this->createUserFromRow($rowData);
            if (!$user) {
                throw new \Exception('Gagal membuat user account');
            }

            // Create member profile
            $memberData = $this->prepareMemberDataFromRow($rowData, $user->id);
            $memberId = $this->memberModel->insert($memberData);

            if (!$memberId) {
                throw new \Exception('Gagal membuat member profile');
            }

            // Assign role based on options
            $role = $options['default_role'] ?? 'Anggota';
            $user->addGroup($role);

            // Set active status based on options
            if (isset($options['auto_activate']) && $options['auto_activate']) {
                $this->userModel->update($user->id, ['active' => 1]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Data berhasil diimport',
                'duplicate' => false
            ];
        } catch (\Exception $e) {
            $db->transRollback();

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'duplicate' => false
            ];
        }
    }

    /**
     * Validate row data
     * 
     * @param array $rowData Row data
     * @param int $rowNumber Row number
     * @return array ['success' => bool, 'message' => string]
     */
    protected function validateRowData(array $rowData, int $rowNumber): array
    {
        $errors = [];

        // Required fields
        $requiredFields = [
            'email' => 'Email',
            'username' => 'Username',
            'full_name' => 'Nama Lengkap',
            'phone' => 'Nomor Telepon',
            'whatsapp' => 'WhatsApp',
            'gender' => 'Jenis Kelamin',
            'address' => 'Alamat',
            'province_name' => 'Provinsi',
            'university_name' => 'Perguruan Tinggi'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($rowData[$field])) {
                $errors[] = "{$label} tidak boleh kosong";
            }
        }

        // Email validation
        if (!empty($rowData['email']) && !filter_var($rowData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid";
        }

        // Gender validation
        if (!empty($rowData['gender']) && !in_array($rowData['gender'], ['Laki-laki', 'Perempuan', 'L', 'P'])) {
            $errors[] = "Jenis kelamin harus 'Laki-laki' atau 'Perempuan' (atau L/P)";
        }

        // Phone validation
        if (!empty($rowData['phone']) && !preg_match('/^[0-9]{10,15}$/', $rowData['phone'])) {
            $errors[] = "Format nomor telepon tidak valid (10-15 digit)";
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode(', ', $errors),
                'duplicate' => false
            ];
        }

        return [
            'success' => true,
            'message' => 'Validation passed',
            'duplicate' => false
        ];
    }

    /**
     * Check for duplicate member
     * 
     * @param array $rowData Row data
     * @return object|null Existing user if duplicate found
     */
    protected function checkDuplicate(array $rowData): ?object
    {
        // Check by email
        $userByEmail = $this->userModel->findByEmail($rowData['email']);
        if ($userByEmail) {
            return $userByEmail;
        }

        // Check by username
        if ($this->userModel->usernameExists($rowData['username'])) {
            return $this->userModel->where('username', $rowData['username'])->first();
        }

        return null;
    }

    /**
     * Create user from row data
     * 
     * @param array $rowData Row data
     * @return object|null User entity
     */
    protected function createUserFromRow(array $rowData): ?object
    {
        try {
            $users = auth()->getProvider();

            $password = $rowData['password'] ?? $this->generateRandomPassword();

            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => $rowData['username'],
                'email'    => $rowData['email'],
                'password' => $password,
                'active'   => false, // Will be activated based on options
            ]);

            $users->save($user);

            // Create email identity
            $user->createEmailIdentity([
                'email' => $rowData['email'],
                'password' => $password
            ]);

            return $user;
        } catch (\Exception $e) {
            log_message('error', 'Error creating user from row: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Prepare member data from row
     * 
     * @param array $rowData Row data
     * @param int $userId User ID
     * @return array Member data
     */
    protected function prepareMemberDataFromRow(array $rowData, int $userId): array
    {
        // Normalize gender
        $gender = $rowData['gender'];
        if ($gender === 'L') {
            $gender = 'Laki-laki';
        } elseif ($gender === 'P') {
            $gender = 'Perempuan';
        }

        // Lookup IDs from names
        $provinceId = $this->lookupProvinceId($rowData['province_name']);
        $universityId = $this->lookupUniversityId($rowData['university_name']);
        $prodiId = !empty($rowData['prodi_name']) ? $this->lookupProdiId($rowData['prodi_name']) : null;

        return [
            'user_id'              => $userId,
            'member_number'        => $this->generateMemberNumber(),
            'full_name'            => $rowData['full_name'],
            'nik'                  => $rowData['nik'] ?? null,
            'nidn_nip'             => $rowData['nidn_nip'] ?? null,
            'gender'               => $gender,
            'birth_place'          => $rowData['birth_place'] ?? null,
            'birth_date'           => $rowData['birth_date'] ?? null,
            'phone'                => $rowData['phone'],
            'whatsapp'             => $rowData['whatsapp'],
            'address'              => $rowData['address'],
            'province_id'          => $provinceId,
            'regency_id'           => null, // Can be enhanced to lookup
            'employment_status_id' => null, // Can be enhanced to lookup
            'salary_range_id'      => null,
            'basic_salary'         => $rowData['basic_salary'] ?? null,
            'university_id'        => $universityId,
            'study_program_id'     => $prodiId,
            'faculty'              => $rowData['faculty'] ?? null,
            'department'           => $rowData['department'] ?? null,
            'employee_number'      => $rowData['employee_number'] ?? null,
            'start_date'           => $rowData['start_date'] ?? null,
            'expertise'            => $rowData['expertise'] ?? null,
            'research_interest'    => $rowData['research_interest'] ?? null,
            'education_level'      => $rowData['education_level'] ?? null,
            'join_date'            => date('Y-m-d'),
            'membership_status'    => 'active', // Imported members are active by default
        ];
    }

    /**
     * Read Excel file
     * 
     * @param string $filePath File path
     * @return array
     */
    protected function readExcel(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            return $data;
        } catch (\Exception $e) {
            log_message('error', 'Error reading Excel file: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Read CSV file
     * 
     * @param string $filePath File path
     * @return array
     */
    protected function readCSV(string $filePath): array
    {
        try {
            $data = [];

            if (($handle = fopen($filePath, 'r')) !== false) {
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', 'Error reading CSV file: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate file headers
     * 
     * @param array $headers Header row
     * @return array ['success' => bool, 'message' => string]
     */
    protected function validateHeaders(array $headers): array
    {
        $requiredHeaders = [
            'email',
            'username',
            'full_name',
            'phone',
            'whatsapp',
            'gender',
            'address',
            'province_name',
            'university_name'
        ];

        $missingHeaders = [];

        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                $missingHeaders[] = $required;
            }
        }

        if (!empty($missingHeaders)) {
            return [
                'success' => false,
                'message' => 'Header tidak lengkap. Header yang hilang: ' . implode(', ', $missingHeaders),
                'data' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Headers valid'
        ];
    }

    /**
     * Map row data to associative array
     * 
     * @param array $headers Headers
     * @param array $row Row data
     * @return array Associative array
     */
    protected function mapRowData(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }

    /**
     * Check if row is empty
     * 
     * @param array $rowData Row data
     * @return bool
     */
    protected function isEmptyRow(array $rowData): bool
    {
        $nonEmptyValues = array_filter($rowData, function ($value) {
            return !empty($value) && $value !== null;
        });

        return empty($nonEmptyValues);
    }

    /**
     * Generate random password
     * 
     * @return string
     */
    protected function generateRandomPassword(): string
    {
        return 'SPK' . rand(100000, 999999);
    }

    /**
     * Generate member number
     * 
     * @return string
     */
    protected function generateMemberNumber(): string
    {
        $year = date('Y');
        $prefix = 'SPK-' . $year . '-';

        $lastMember = $this->memberModel
            ->like('member_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastMember && isset($lastMember->member_number)) {
            $lastNumber = (int) substr($lastMember->member_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Lookup province ID by name
     * 
     * @param string $name Province name
     * @return int|null
     */
    protected function lookupProvinceId(string $name): ?int
    {
        $province = $this->provinceModel->where('name', $name)->first();
        return $province ? $province->id : null;
    }

    /**
     * Lookup university ID by name
     * 
     * @param string $name University name
     * @return int|null
     */
    protected function lookupUniversityId(string $name): ?int
    {
        $university = $this->universityModel->where('name', $name)->first();
        return $university ? $university->id : null;
    }

    /**
     * Lookup prodi ID by name
     * 
     * @param string $name Prodi name
     * @return int|null
     */
    protected function lookupProdiId(string $name): ?int
    {
        $prodi = $this->prodiModel->where('name', $name)->first();
        return $prodi ? $prodi->id : null;
    }

    /**
     * Update existing member
     * 
     * @param object $user Existing user
     * @param array $rowData New data
     * @return array
     */
    protected function updateExistingMember($user, array $rowData): array
    {
        try {
            $member = $this->memberModel->where('user_id', $user->id)->first();

            if ($member) {
                $updateData = $this->prepareMemberDataFromRow($rowData, $user->id);
                unset($updateData['user_id']); // Don't update user_id
                unset($updateData['member_number']); // Keep existing member number

                $this->memberModel->update($member->id, $updateData);
            }

            return [
                'success' => true,
                'message' => 'Data updated',
                'duplicate' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'duplicate' => true
            ];
        }
    }

    /**
     * Generate import report
     * 
     * @return string Report text
     */
    protected function generateReport(): string
    {
        $report = "=== LAPORAN IMPORT ANGGOTA ===\n\n";
        $report .= "Total Data: {$this->results['total']}\n";
        $report .= "Berhasil: {$this->results['success']}\n";
        $report .= "Gagal: {$this->results['failed']}\n";
        $report .= "Duplikat: {$this->results['duplicates']}\n\n";

        if (!empty($this->results['errors'])) {
            $report .= "=== ERROR DETAILS ===\n\n";

            foreach ($this->results['errors'] as $error) {
                $report .= "Baris {$error['row']}: {$error['error']}\n";
                $report .= "Data: " . json_encode($error['data']) . "\n\n";
            }
        }

        return $report;
    }

    /**
     * Reset results tracking
     * 
     * @return void
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
     * Process import with activation flow
     * Import members, generate activation token, and send email
     * 
     * @param string $filePath Path to uploaded file
     * @param int $importedBy User ID who imports
     * @param array $options Import options
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function importWithActivation(string $filePath, int $importedBy, array $options = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create import log
            $importLogId = $this->importLogModel->insert([
                'imported_by' => $importedBy,
                'filename' => basename($filePath),
                'status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Read and validate file
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
                $data = $this->readExcel($filePath);
            } elseif (strtolower($extension) === 'csv') {
                $data = $this->readCSV($filePath);
            } else {
                throw new \Exception('Format file tidak didukung');
            }

            if (empty($data)) {
                throw new \Exception('File kosong atau tidak dapat dibaca');
            }

            // Validate headers
            $headerValidation = $this->validateHeaders($data[0]);
            if (!$headerValidation['success']) {
                throw new \Exception($headerValidation['message']);
            }

            // Remove header row
            $headers = array_shift($data);
            $this->results['total'] = count($data);

            $importedUserIds = [];
            $errorDetails = [];

            // Process each row
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;
                $rowData = $this->mapRowData($headers, $row);

                if ($this->isEmptyRow($rowData)) {
                    continue;
                }

                // Validate and import
                $result = $this->processRowWithActivation($rowData, $rowNumber, $importLogId);

                if (!$result['success']) {
                    $this->results['failed']++;
                    $errorDetails[] = [
                        'row' => $rowNumber,
                        'data' => $rowData,
                        'error' => $result['message']
                    ];
                } else {
                    if ($result['duplicate']) {
                        $this->results['duplicates']++;
                    } else {
                        $this->results['success']++;
                        if (isset($result['user_id'])) {
                            $importedUserIds[] = $result['user_id'];
                        }
                    }
                }
            }

            // Update import log
            $this->importLogModel->update($importLogId, [
                'total_rows' => $this->results['total'],
                'success_count' => $this->results['success'],
                'failed_count' => $this->results['failed'],
                'duplicate_count' => $this->results['duplicates'],
                'error_details' => json_encode($errorDetails),
                'status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Send activation emails
            $emailResults = $this->sendActivationEmails($importedUserIds);

            return [
                'success' => true,
                'message' => sprintf(
                    'Import selesai: %d berhasil, %d gagal, %d duplikat dari %d data',
                    $this->results['success'],
                    $this->results['failed'],
                    $this->results['duplicates'],
                    $this->results['total']
                ),
                'data' => [
                    'results' => $this->results,
                    'import_log_id' => $importLogId,
                    'email_results' => $emailResults,
                ]
            ];
        } catch (\Exception $e) {
            $db->transRollback();

            // Update import log as failed
            if (isset($importLogId)) {
                $this->importLogModel->update($importLogId, [
                    'status' => 'failed',
                    'error_details' => json_encode(['error' => $e->getMessage()]),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            log_message('error', 'Error in importWithActivation: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error import: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Process single row with activation flow
     * 
     * @param array $rowData Row data
     * @param int $rowNumber Row number
     * @param int $importLogId Import log ID
     * @return array
     */
    protected function processRowWithActivation(array $rowData, int $rowNumber, int $importLogId): array
    {
        try {
            // Validate row
            $validation = $this->validateRowData($rowData, $rowNumber);
            if (!$validation['success']) {
                return $validation;
            }

            // Check duplicate
            $duplicate = $this->checkDuplicate($rowData);
            if ($duplicate) {
                return [
                    'success' => false,
                    'message' => 'Email atau username sudah terdaftar',
                    'duplicate' => true
                ];
            }

            // Create user with pending_activation status
            $user = $this->createUserWithActivation($rowData);
            if (!$user) {
                throw new \Exception('Gagal membuat user account');
            }

            // Create member profile
            $memberData = $this->prepareMemberDataFromRow($rowData, $user->id);
            $memberData['imported_at'] = date('Y-m-d H:i:s');
            $memberData['import_batch_id'] = $importLogId;
            $memberData['membership_status'] = 'pending_activation';

            $memberId = $this->memberModel->insert($memberData);

            if (!$memberId) {
                throw new \Exception('Gagal membuat member profile');
            }

            // Assign role 'anggota' (not calon_anggota)
            $user->addGroup('anggota');

            return [
                'success' => true,
                'message' => 'Data berhasil diimport',
                'duplicate' => false,
                'user_id' => $user->id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'duplicate' => false
            ];
        }
    }

    /**
     * Create user with activation token
     * 
     * @param array $rowData Row data
     * @return object|null User entity
     */
    protected function createUserWithActivation(array $rowData): ?object
    {
        try {
            $users = auth()->getProvider();

            // Generate random password (will be changed on activation)
            $tempPassword = $this->generateRandomPassword();

            // Generate activation token
            $activationToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => $rowData['username'],
                'email'    => $rowData['email'],
                'password' => $tempPassword,
                'active'   => false, // Will be activated after email confirmation
            ]);

            $users->save($user);

            // Create email identity
            $user->createEmailIdentity([
                'email' => $rowData['email'],
                'password' => $tempPassword
            ]);

            // Update user with activation token
            $this->userModel->update($user->id, [
                'activation_token' => $activationToken,
                'activation_token_expires_at' => $expiresAt,
            ]);

            // Reload user to get updated data
            return $users->findById($user->id);
        } catch (\Exception $e) {
            log_message('error', 'Error creating user with activation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send activation emails to imported members
     * 
     * @param array $userIds Array of user IDs
     * @return array ['sent' => int, 'failed' => int, 'errors' => array]
     */
    public function sendActivationEmails(array $userIds): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($userIds as $userId) {
            $user = $this->userModel->find($userId);

            if (!$user || empty($user->activation_token)) {
                $results['failed']++;
                $results['errors'][] = "User ID {$userId}: Token not found";
                continue;
            }

            try {
                // Generate activation link
                $activationLink = base_url("auth/activate/{$user->activation_token}");

                // Format expiry date using CI4 Time class
                $expiresAt = \CodeIgniter\I18n\Time::parse($user->activation_token_expires_at)
                    ->toLocalizedString('dd MMMM yyyy HH:mm');

                // Get member name
                $member = $this->memberModel->where('user_id', $userId)->first();
                $memberName = $member ? $member->full_name : $user->username;

                // Prepare email data
                $data = [
                    'subject' => 'Aktivasi Akun SPK Anda',
                    'name' => $memberName,
                    'activation_link' => $activationLink,
                    'expires_at' => $expiresAt,
                ];

                // Send email using sendTemplate method
                $emailResult = $this->emailService->sendTemplate(
                    $user->email,
                    'emails/activation',
                    $data
                );

                if ($emailResult['success']) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "User ID {$userId}: " . $emailResult['message'];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "User ID {$userId}: " . $e->getMessage();
                log_message('error', "Failed to send activation email to user {$userId}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Re-send activation email to specific member
     * 
     * @param int $userId User ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function resendActivationEmail(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            // Check if already activated
            if (!empty($user->activated_at)) {
                return [
                    'success' => false,
                    'message' => 'Akun sudah diaktivasi'
                ];
            }

            // Generate new token if expired
            if (empty($user->activation_token) || strtotime($user->activation_token_expires_at) < time()) {
                $activationToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

                $this->userModel->update($userId, [
                    'activation_token' => $activationToken,
                    'activation_token_expires_at' => $expiresAt,
                ]);

                $user = $this->userModel->find($userId);
            }

            // Send email
            $results = $this->sendActivationEmails([$userId]);

            if ($results['sent'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Email aktivasi berhasil dikirim ulang'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal mengirim email: ' . implode(', ', $results['errors'])
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error resending activation email: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get activation statistics for import batch
     * 
     * @param int $importLogId Import log ID
     * @return array
     */
    public function getActivationStats(int $importLogId): array
    {
        $members = $this->memberModel
            ->select('member_profiles.*, users.activated_at, users.active')
            ->join('users', 'users.id = member_profiles.user_id')
            ->where('member_profiles.import_batch_id', $importLogId)
            ->findAll();

        $stats = [
            'total' => count($members),
            'activated' => 0,
            'pending' => 0,
            'expired' => 0,
        ];

        foreach ($members as $member) {
            if (!empty($member->activated_at)) {
                $stats['activated']++;
            } else {
                // Check if token expired
                $user = $this->userModel->find($member->user_id);
                if ($user && strtotime($user->activation_token_expires_at) < time()) {
                    $stats['expired']++;
                } else {
                    $stats['pending']++;
                }
            }
        }

        $stats['activation_rate'] = $stats['total'] > 0
            ? round(($stats['activated'] / $stats['total']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Download import template
     * 
     * @param string $filePath Output file path
     * @return array ['success' => bool, 'message' => string, 'file_path' => string]
     */
    public function downloadTemplate(string $filePath): array
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $headers = [
                'email',
                'username',
                'password',
                'full_name',
                'nik',
                'nidn_nip',
                'gender',
                'birth_place',
                'birth_date',
                'phone',
                'whatsapp',
                'address',
                'province_name',
                'university_name',
                'prodi_name',
                'faculty',
                'department',
                'employee_number',
                'start_date',
                'basic_salary',
                'expertise',
                'research_interest',
                'education_level'
            ];

            // Set headers in bold
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:W1')->getFont()->setBold(true);

            // Add example data
            $exampleData = [
                'johndoe@example.com',
                'johndoe',
                'Password123',
                'John Doe',
                '3201234567890123',
                '1234567890',
                'Laki-laki',
                'Bandung',
                '1990-01-01',
                '081234567890',
                '081234567890',
                'Jl. Example No. 123',
                'Jawa Barat',
                'Universitas Padjadjaran',
                'Teknik Informatika',
                'Fakultas Teknik',
                'Teknik Informatika',
                'EMP001',
                '2020-01-01',
                '7500000',
                'PHP, Laravel, CodeIgniter',
                'Web Development, Cloud Computing',
                'S2'
            ];

            $sheet->fromArray([$exampleData], null, 'A2');

            // Auto-size columns
            foreach (range('A', 'W') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return [
                'success' => true,
                'message' => 'Template berhasil dibuat',
                'file_path' => $filePath
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error creating template: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error membuat template: ' . $e->getMessage(),
                'file_path' => null
            ];
        }
    }

    /**
     * Get import history
     * 
     * @return array
     */
    public function getImportHistory(): array
    {
        // This would typically query an import_logs table
        // For now, return empty array
        return [];
    }
}
