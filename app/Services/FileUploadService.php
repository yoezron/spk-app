<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * FileUploadService
 * 
 * Menangani file uploads dengan validation
 * Termasuk single/multiple upload, validation, resize, dan file operations
 * 
 * @package App\Services
 * @author  SPK Development Team
 * @version 1.0.0
 */
class FileUploadService
{
    /**
     * @var array Allowed file types configuration
     */
    protected $fileTypes = [
        'photo' => [
            'allowed' => ['jpg', 'jpeg', 'png'],
            'max_size' => 2048, // KB (2MB)
            'resize' => [800, 800]
        ],
        'cv' => [
            'allowed' => ['pdf', 'doc', 'docx'],
            'max_size' => 5120, // KB (5MB)
            'resize' => null
        ],
        'id_card' => [
            'allowed' => ['jpg', 'jpeg', 'png', 'pdf'],
            'max_size' => 2048, // KB (2MB)
            'resize' => null
        ],
        'document' => [
            'allowed' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'max_size' => 10240, // KB (10MB)
            'resize' => null
        ]
    ];

    /**
     * @var string Base upload directory
     */
    protected $uploadBasePath = WRITEPATH . 'uploads/';

    /**
     * Upload single file
     * Handles file upload with validation and optional resizing
     * 
     * @param UploadedFile|null $file Uploaded file object
     * @param string $type File type (photo, cv, id_card, document)
     * @param array $options Additional options (custom_name, no_resize, etc)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function upload($file, string $type, array $options = []): array
    {
        try {
            // Validate file exists
            if (!$file || !$file->isValid()) {
                return [
                    'success' => false,
                    'message' => 'File tidak valid atau tidak ditemukan',
                    'data' => null
                ];
            }

            // Validate file type configuration
            if (!isset($this->fileTypes[$type])) {
                return [
                    'success' => false,
                    'message' => "Tipe file '{$type}' tidak dikenali",
                    'data' => null
                ];
            }

            // Validate file
            $validation = $this->validate($file, $this->fileTypes[$type]);
            if (!$validation['success']) {
                return $validation;
            }

            // Generate filename
            $customName = $options['custom_name'] ?? null;
            $filename = $customName
                ? $customName . '.' . $file->getExtension()
                : $this->generateFileName($file->getName(), $type);

            // Get upload path
            $uploadPath = $options['upload_path'] ?? $this->getUploadPath($type);

            // Ensure directory exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file
            if (!$file->move($uploadPath, $filename)) {
                return [
                    'success' => false,
                    'message' => 'Gagal memindahkan file',
                    'data' => null
                ];
            }

            $filePath = $uploadPath . $filename;

            // Resize image if needed
            $shouldResize = isset($options['no_resize']) ? !$options['no_resize'] : true;
            if ($shouldResize && $this->fileTypes[$type]['resize'] && $this->isImage($file)) {
                $resizeResult = $this->resize(
                    $filePath,
                    $this->fileTypes[$type]['resize'][0],
                    $this->fileTypes[$type]['resize'][1]
                );

                if (!$resizeResult['success']) {
                    // Log resize failure but don't fail the upload
                    log_message('warning', 'Image resize failed: ' . $resizeResult['message']);
                }
            }

            return [
                'success' => true,
                'message' => 'File berhasil diupload',
                'data' => [
                    'filename' => $filename,
                    'filepath' => $filePath,
                    'relative_path' => str_replace(WRITEPATH, '', $filePath),
                    'size' => filesize($filePath),
                    'type' => $type,
                    'extension' => $file->getExtension()
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::upload: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal upload file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Upload multiple files
     * Handles batch file upload with validation
     * 
     * @param array $files Array of UploadedFile objects
     * @param string $type File type
     * @param array $options Additional options
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function uploadMultiple(array $files, string $type, array $options = []): array
    {
        try {
            if (empty($files)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada file untuk diupload',
                    'data' => null
                ];
            }

            $results = [
                'total' => count($files),
                'success' => 0,
                'failed' => 0,
                'files' => [],
                'errors' => []
            ];

            foreach ($files as $index => $file) {
                $result = $this->upload($file, $type, $options);

                if ($result['success']) {
                    $results['success']++;
                    $results['files'][] = $result['data'];
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'filename' => $file->getName(),
                        'error' => $result['message']
                    ];
                }
            }

            return [
                'success' => $results['failed'] === 0,
                'message' => sprintf(
                    'Berhasil: %d, Gagal: %d dari %d file',
                    $results['success'],
                    $results['failed'],
                    $results['total']
                ),
                'data' => $results
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::uploadMultiple: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal upload multiple files: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate file
     * Validates file size, type, and dimensions
     * 
     * @param UploadedFile $file File to validate
     * @param array $rules Validation rules
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function validate($file, array $rules): array
    {
        try {
            // Check if file has errors
            if ($file->getError() !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File melebihi ukuran maksimum yang diizinkan server',
                    UPLOAD_ERR_FORM_SIZE => 'File melebihi ukuran maksimum yang diizinkan form',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
                ];

                return [
                    'success' => false,
                    'message' => $errorMessages[$file->getError()] ?? 'Error upload tidak diketahui',
                    'data' => null
                ];
            }

            // Validate file extension
            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $rules['allowed'])) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Ekstensi file tidak diizinkan. Hanya: %s',
                        implode(', ', $rules['allowed'])
                    ),
                    'data' => null
                ];
            }

            // Validate file size
            $fileSizeKB = $file->getSize() / 1024;
            if ($fileSizeKB > $rules['max_size']) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Ukuran file terlalu besar. Maksimal: %s MB',
                        number_format($rules['max_size'] / 1024, 2)
                    ),
                    'data' => null
                ];
            }

            // Validate image dimensions if image file
            if ($this->isImage($file)) {
                $imageInfo = getimagesize($file->getTempName());

                if ($imageInfo === false) {
                    return [
                        'success' => false,
                        'message' => 'File bukan gambar yang valid',
                        'data' => null
                    ];
                }

                // Optional: Check minimum dimensions
                if (isset($rules['min_width']) && $imageInfo[0] < $rules['min_width']) {
                    return [
                        'success' => false,
                        'message' => sprintf('Lebar gambar minimal %d px', $rules['min_width']),
                        'data' => null
                    ];
                }

                if (isset($rules['min_height']) && $imageInfo[1] < $rules['min_height']) {
                    return [
                        'success' => false,
                        'message' => sprintf('Tinggi gambar minimal %d px', $rules['min_height']),
                        'data' => null
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'File valid',
                'data' => [
                    'size' => $fileSizeKB,
                    'extension' => $extension
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::validate: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal validasi file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Resize image
     * Resizes image to specified dimensions maintaining aspect ratio
     * 
     * @param string $filePath Path to image file
     * @param int $width Target width
     * @param int $height Target height
     * @param bool $maintainRatio Maintain aspect ratio (default: true)
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function resize(string $filePath, int $width, int $height, bool $maintainRatio = true): array
    {
        try {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                    'data' => null
                ];
            }

            // Use CodeIgniter Image library
            $image = \Config\Services::image();

            $image->withFile($filePath)
                ->resize($width, $height, $maintainRatio, 'auto')
                ->save($filePath);

            return [
                'success' => true,
                'message' => 'Gambar berhasil diresize',
                'data' => [
                    'filepath' => $filePath,
                    'width' => $width,
                    'height' => $height
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::resize: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal resize gambar: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete file
     * Removes file from filesystem
     * 
     * @param string $filePath Path to file
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function delete(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                    'data' => null
                ];
            }

            if (!unlink($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus file',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'File berhasil dihapus',
                'data' => [
                    'deleted_file' => $filePath
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::delete: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Move file
     * Moves file from one location to another
     * 
     * @param string $oldPath Source file path
     * @param string $newPath Destination file path
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function move(string $oldPath, string $newPath): array
    {
        try {
            if (!file_exists($oldPath)) {
                return [
                    'success' => false,
                    'message' => 'File sumber tidak ditemukan',
                    'data' => null
                ];
            }

            // Ensure destination directory exists
            $destinationDir = dirname($newPath);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            if (!rename($oldPath, $newPath)) {
                return [
                    'success' => false,
                    'message' => 'Gagal memindahkan file',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'File berhasil dipindahkan',
                'data' => [
                    'old_path' => $oldPath,
                    'new_path' => $newPath
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::move: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memindahkan file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get file information
     * Returns detailed information about a file
     * 
     * @param string $filePath Path to file
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function getFileInfo(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                    'data' => null
                ];
            }

            $info = [
                'path' => $filePath,
                'filename' => basename($filePath),
                'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
                'size' => filesize($filePath),
                'size_formatted' => $this->formatFileSize(filesize($filePath)),
                'mime_type' => mime_content_type($filePath),
                'created_at' => date('Y-m-d H:i:s', filectime($filePath)),
                'modified_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                'is_readable' => is_readable($filePath),
                'is_writable' => is_writable($filePath)
            ];

            // Add image-specific info if image
            if (strpos($info['mime_type'], 'image/') === 0) {
                $imageInfo = getimagesize($filePath);
                if ($imageInfo) {
                    $info['width'] = $imageInfo[0];
                    $info['height'] = $imageInfo[1];
                    $info['dimensions'] = $imageInfo[0] . 'x' . $imageInfo[1];
                }
            }

            return [
                'success' => true,
                'message' => 'Informasi file berhasil diambil',
                'data' => $info
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in FileUploadService::getFileInfo: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil info file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate unique filename
     * Creates unique filename with prefix and timestamp
     * 
     * @param string $originalName Original filename
     * @param string $prefix Filename prefix (typically file type)
     * @return string Generated unique filename
     */
    public function generateFileName(string $originalName, string $prefix = ''): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = time();
        $random = bin2hex(random_bytes(8));

        $prefix = $prefix ? $prefix . '_' : '';

        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Get upload path for file type
     * Returns the upload directory path for specific file type
     * 
     * @param string $type File type
     * @param int|null $userId User ID for subdirectory (for better security)
     * @return string Upload directory path
     */
    public function getUploadPath(string $type, ?int $userId = null): string
    {
        $paths = [
            'photo' => $this->uploadBasePath . 'photos/',
            'cv' => $this->uploadBasePath . 'cv/',
            'id_card' => $this->uploadBasePath . 'id_cards/',
            'document' => $this->uploadBasePath . 'documents/',
            'payment_proof' => $this->uploadBasePath . 'payments/'
        ];

        $basePath = $paths[$type] ?? $this->uploadBasePath . 'misc/';

        // Add user-based subdirectory for better security and organization
        if ($userId) {
            // Create subdirectory based on user ID hash to prevent path guessing
            $subDir = substr(md5($userId), 0, 2) . '/' . $userId . '/';
            $basePath .= $subDir;
        }

        return $basePath;
    }

    /**
     * Get secure upload path for user files
     * Creates a secure, user-specific upload path
     * 
     * @param int $userId User ID
     * @param string $type File type (photo, payment_proof, etc)
     * @return string Secure upload path
     */
    public function getSecureUploadPath(int $userId, string $type): string
    {
        return $this->getUploadPath($type, $userId);
    }

    /**
     * Check if file is an image
     * 
     * @param UploadedFile $file File to check
     * @return bool True if image, false otherwise
     */
    protected function isImage(UploadedFile $file): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array(strtolower($file->getExtension()), $imageExtensions);
    }

    /**
     * Format file size
     * Converts bytes to human-readable format
     * 
     * @param int $bytes File size in bytes
     * @return string Formatted file size
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
