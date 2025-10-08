<?php

namespace App\Libraries;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Color\Color;

class QrCodeGenerator
{
    /**
     * Generate QR Code for member
     *
     * @param string $data Data to encode (usually member number)
     * @param string $filename Output filename
     * @return string Path to saved QR code
     */
    public function generateMemberQrCode(string $data, string $filename): string
    {
        $qrCode = new QrCode($data);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
        $qrCode->setForegroundColor(new Color(0, 0, 0));
        $qrCode->setBackgroundColor(new Color(255, 255, 255));
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Save to writable directory
        $uploadPath = WRITEPATH . 'uploads/qrcodes/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filePath = $uploadPath . $filename;
        $result->saveToFile($filePath);
        
        return 'uploads/qrcodes/' . $filename;
    }
    
    /**
     * Generate QR Code and return as base64 string
     *
     * @param string $data
     * @return string Base64 encoded image
     */
    public function generateQrCodeBase64(string $data): string
    {
        $qrCode = new QrCode($data);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return base64_encode($result->getString());
    }
}
