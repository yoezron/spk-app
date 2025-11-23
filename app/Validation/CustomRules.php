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
     * Valid Gender (L or P)
     *
     * @param string $value
     * @param string|null $error
     * @return bool
     */
    public function valid_gender(string $value, ?string &$error = null): bool
    {
        // Trim whitespace and convert to uppercase
        $value = trim(strtoupper($value));

        if ($value === 'L' || $value === 'P') {
            return true;
        }

        $error = 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan)';
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
     * Minimum Image Dimensions
     *
     * Check if uploaded image meets minimum dimension requirements
     * Format: min_image_dimensions[fieldname,width,height]
     *
     * @param string|null $value The field value (not used for files)
     * @param string|null $params Format: "fieldname,width,height" (e.g., "photo,300,400")
     * @param array $data All form data
     * @param string|null $error Error message reference
     * @return bool
     */
    public function min_image_dimensions(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // Parse parameters: fieldname,width,height
        if (!$params) {
            return true;
        }

        $paramParts = explode(',', $params);
        if (count($paramParts) < 3) {
            return true;
        }

        [$fieldName, $minWidth, $minHeight] = $paramParts;

        // Get the file from request using field name from params
        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($fieldName);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true; // Let other validation rules handle file validity
        }

        // Check if it's an image
        if (!str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            return true; // Let other validation rules handle file type
        }

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
     * Format: max_image_dimensions[fieldname,width,height]
     *
     * @param string|null $value The field value (not used for files)
     * @param string|null $params Format: "fieldname,width,height" (e.g., "photo,4000,3000")
     * @param array $data All form data
     * @param string|null $error Error message reference
     * @return bool
     */
    public function max_image_dimensions(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // Parse parameters: fieldname,width,height
        if (!$params) {
            return true;
        }

        $paramParts = explode(',', $params);
        if (count($paramParts) < 3) {
            return true;
        }

        [$fieldName, $maxWidth, $maxHeight] = $paramParts;

        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($fieldName);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

        if (!str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            return true;
        }

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
     * Format: image_aspect_ratio[fieldname,min_ratio,max_ratio]
     *
     * @param string|null $value The field value (not used for files)
     * @param string|null $params Format: "fieldname,min_ratio,max_ratio" (e.g., "photo,0.7,1.5")
     * @param array $data All form data
     * @param string|null $error Error message reference
     * @return bool
     */
    public function image_aspect_ratio(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // Parse parameters: fieldname,min_ratio,max_ratio
        if (!$params) {
            return true;
        }

        $paramParts = explode(',', $params);
        if (count($paramParts) < 3) {
            return true;
        }

        [$fieldName, $minRatio, $maxRatio] = $paramParts;

        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($fieldName);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

        if (!str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            return true;
        }

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
     * Format: valid_image_mime[fieldname,mime1,mime2,...]
     *
     * @param string|null $value The field value (not used for files)
     * @param string|null $params Format: "fieldname,mime1,mime2,..." (e.g., "photo,image/jpeg,image/png")
     * @param array $data All form data
     * @param string|null $error Error message reference
     * @return bool
     */
    public function valid_image_mime(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // Parse parameters: fieldname,mime1,mime2,...
        if (!$params) {
            return true;
        }

        $paramParts = explode(',', $params);
        if (count($paramParts) < 2) {
            return true;
        }

        $fieldName = array_shift($paramParts); // First param is field name
        $allowedMimes = array_map('trim', $paramParts); // Rest are MIME types

        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($fieldName);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }

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
     * Format: max_file_size[fieldname,max_size_kb]
     *
     * @param string|null $value The field value (not used for files)
     * @param string|null $params Format: "fieldname,max_size_kb" (e.g., "photo,2048" for 2MB)
     * @param array $data All form data
     * @param string|null $error Error message reference
     * @return bool
     */
    public function max_file_size(?string $value = null, ?string $params = null, array $data = [], ?string &$error = null): bool
    {
        // Parse parameters: fieldname,max_size_kb
        if (!$params) {
            return true;
        }

        $paramParts = explode(',', $params);
        if (count($paramParts) < 2) {
            return true;
        }

        [$fieldName, $maxSizeKB] = $paramParts;
        $maxSizeKB = (int) $maxSizeKB;

        $request = \Config\Services::request();
        $uploadedFile = $request->getFile($fieldName);

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return true;
        }
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
