<?php

namespace App\Validation;

/**
 * Custom Validation Rules
 * 
 * Additional validation rules for the application
 * 
 * @package App\Validation
 */
class CustomRules
{
    /**
     * Strong Password Validation
     * 
     * Password must contain:
     * - At least 8 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
     * - At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)
     * 
     * @param string $value
     * @param string|null $error
     * @return bool
     */
    public function strong_password(string $value, ?string &$error = null): bool
    {
        // Check minimum length
        if (strlen($value) < 8) {
            $error = 'Password minimal 8 karakter';
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            $error = 'Password harus mengandung minimal 1 huruf besar';
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            $error = 'Password harus mengandung minimal 1 huruf kecil';
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $value)) {
            $error = 'Password harus mengandung minimal 1 angka';
            return false;
        }

        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $value)) {
            $error = 'Password harus mengandung minimal 1 karakter khusus (!@#$%^&* dll)';
            return false;
        }

        return true;
    }

    /**
     * Valid Phone Number (Indonesian format)
     * 
     * Accepts formats:
     * - 08xxxxxxxxxx (10-13 digits)
     * - 628xxxxxxxxxx (11-14 digits)
     * - +628xxxxxxxxxx (12-15 digits)
     * 
     * @param string $value
     * @param string|null $error
     * @return bool
     */
    public function valid_phone(string $value, ?string &$error = null): bool
    {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $value);

        // Check format
        if (preg_match('/^(\+62|62|0)8[0-9]{8,11}$/', $phone)) {
            return true;
        }

        $error = 'Format nomor telepon tidak valid. Gunakan format: 08xxxxxxxxxx';
        return false;
    }

    /**
     * Valid NIDN/NIP
     * 
     * NIDN: 10 digits
     * NIP: 18 digits
     * 
     * @param string $value
     * @param string|null $error
     * @return bool
     */
    public function valid_nidn_nip(string $value, ?string &$error = null): bool
    {
        // Remove spaces and dashes
        $cleaned = preg_replace('/[\s\-]/', '', $value);

        // Check if it's numeric
        if (!ctype_digit($cleaned)) {
            $error = 'NIDN/NIP hanya boleh berisi angka';
            return false;
        }

        // Check length (NIDN: 10, NIP: 18)
        $length = strlen($cleaned);
        if ($length !== 10 && $length !== 18) {
            $error = 'NIDN harus 10 digit atau NIP harus 18 digit';
            return false;
        }

        return true;
    }

    /**
     * Valid Image Dimensions
     * 
     * Check if uploaded image meets minimum dimension requirements
     * 
     * @param string $file
     * @param string $params Format: "width,height" (e.g., "300,400")
     * @param array $data
     * @param string|null $error
     * @return bool
     */
    public function min_image_dimensions(string $file, string $params, array $data, ?string &$error = null): bool
    {
        // Get the file from request
        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($file);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true; // Let other validation rules handle file validity
        }

        // Check if it's an image
        if (!str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            return true; // Let other validation rules handle file type
        }

        // Parse parameters
        [$minWidth, $minHeight] = explode(',', $params);

        // Get image dimensions
        $imageInfo = getimagesize($uploadedFile->getTempName());

        if ($imageInfo === false) {
            $error = 'File bukan gambar yang valid';
            return false;
        }

        [$width, $height] = $imageInfo;

        // Check dimensions
        if ($width < $minWidth || $height < $minHeight) {
            $error = "Resolusi gambar minimal {$minWidth}x{$minHeight}px. Gambar Anda: {$width}x{$height}px";
            return false;
        }

        return true;
    }

    /**
     * Maximum Image Dimensions
     * 
     * Check if uploaded image doesn't exceed maximum dimension requirements
     * 
     * @param string $file
     * @param string $params Format: "width,height" (e.g., "4000,3000")
     * @param array $data
     * @param string|null $error
     * @return bool
     */
    public function max_image_dimensions(string $file, string $params, array $data, ?string &$error = null): bool
    {
        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($file);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

        if (!str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            return true;
        }

        [$maxWidth, $maxHeight] = explode(',', $params);

        $imageInfo = getimagesize($uploadedFile->getTempName());

        if ($imageInfo === false) {
            $error = 'File bukan gambar yang valid';
            return false;
        }

        [$width, $height] = $imageInfo;

        if ($width > $maxWidth || $height > $maxHeight) {
            $error = "Resolusi gambar maksimal {$maxWidth}x{$maxHeight}px. Gambar Anda: {$width}x{$height}px";
            return false;
        }

        return true;
    }

    /**
     * Image Aspect Ratio
     * 
     * Check if image aspect ratio is within acceptable range
     * Useful for profile photos that need specific ratios
     * 
     * @param string $file
     * @param string $params Format: "min_ratio,max_ratio" (e.g., "0.7,1.5" for portrait/square)
     * @param array $data
     * @param string|null $error
     * @return bool
     */
    public function image_aspect_ratio(string $file, string $params, array $data, ?string &$error = null): bool
    {
        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($file);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

        if (!str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            return true;
        }

        [$minRatio, $maxRatio] = explode(',', $params);

        $imageInfo = getimagesize($uploadedFile->getTempName());

        if ($imageInfo === false) {
            $error = 'File bukan gambar yang valid';
            return false;
        }

        [$width, $height] = $imageInfo;
        $ratio = $width / $height;

        if ($ratio < $minRatio || $ratio > $maxRatio) {
            $error = sprintf(
                'Rasio aspek gambar tidak sesuai. Harus antara %.2f dan %.2f. Gambar Anda: %.2f',
                $minRatio,
                $maxRatio,
                $ratio
            );
            return false;
        }

        return true;
    }

    /**
     * Valid Image MIME Type (strict check)
     * 
     * Validates actual image MIME type, not just extension
     * Prevents malicious files with fake extensions
     * 
     * @param string $file
     * @param string $params Allowed MIME types (comma-separated)
     * @param array $data
     * @param string|null $error
     * @return bool
     */
    public function valid_image_mime(string $file, string $params, array $data, ?string &$error = null): bool
    {
        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($file);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

        // Get allowed MIME types
        $allowedMimes = array_map('trim', explode(',', $params));

        // Get actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile->getTempName());
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes, true)) {
            $error = 'Tipe file tidak valid. Format yang diizinkan: ' . implode(', ', $allowedMimes);
            return false;
        }

        // Additional check: validate with getimagesize for images
        if (str_starts_with($mimeType, 'image/')) {
            $imageInfo = getimagesize($uploadedFile->getTempName());
            if ($imageInfo === false) {
                $error = 'File rusak atau bukan gambar yang valid';
                return false;
            }
        }

        return true;
    }

    /**
     * Maximum File Size (more flexible than built-in)
     * 
     * @param string $file
     * @param string $params Max size in KB (e.g., "2048" for 2MB)
     * @param array $data
     * @param string|null $error
     * @return bool
     */
    public function max_file_size(string $file, string $params, array $data, ?string &$error = null): bool
    {
        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($file);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

        $maxSizeKB = (int) $params;
        $fileSizeKB = $uploadedFile->getSize() / 1024;

        if ($fileSizeKB > $maxSizeKB) {
            $maxSizeMB = $maxSizeKB / 1024;
            $fileSizeMB = $fileSizeKB / 1024;
            $error = sprintf(
                'Ukuran file terlalu besar. Maksimal: %.2f MB. File Anda: %.2f MB',
                $maxSizeMB,
                $fileSizeMB
            );
            return false;
        }

        return true;
    }

    /**
     * Unique NIDN/NIP in member_profiles
     * 
     * @param string $value
     * @param string|null $params User ID to exclude (for updates)
     * @param array $data
     * @param string|null $error
     * @return bool
     */
    public function unique_nidn_nip(string $value, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('member_profiles');

        $builder->where('nidn_nip', $value);

        // Exclude current user if updating
        if ($params) {
            $builder->where('user_id !=', $params);
        }

        $count = $builder->countAllResults();

        if ($count > 0) {
            $error = 'NIDN/NIP sudah terdaftar';
            return false;
        }

        return true;
    }
}
