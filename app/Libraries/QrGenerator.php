<?php

namespace App\Libraries;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;

/**
 * QrGenerator
 * 
 * Wrapper library untuk Endroid QR Code - QR code generation
 * Digunakan untuk generate QR code verifikasi Kartu Anggota
 * 
 * Usage:
 * $qr = new \App\Libraries\QrGenerator();
 * $qr->setText($verificationUrl);
 * $qr->setSize(200);
 * $qr->generate('member_card_qr.png');
 * 
 * @package App\Libraries
 * @author  SPK Development Team
 * @version 1.0.0
 */
class QrGenerator
{
    /**
     * @var QrCode
     */
    protected $qrCode;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $size = 300;

    /**
     * @var int
     */
    protected $margin = 10;

    /**
     * @var string
     */
    protected $format = 'png';

    /**
     * @var array
     */
    protected $foregroundColor = ['r' => 0, 'g' => 0, 'b' => 0];

    /**
     * @var array
     */
    protected $backgroundColor = ['r' => 255, 'g' => 255, 'b' => 255];

    /**
     * Constructor
     * 
     * @param string $text Text to encode in QR code
     * @param int $size QR code size in pixels
     */
    public function __construct(string $text = '', int $size = 300)
    {
        $this->text = $text;
        $this->size = $size;
    }

    /**
     * Set text to encode
     * 
     * @param string $text Text content
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set QR code size
     * 
     * @param int $size Size in pixels
     * @return self
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Set margin size
     * 
     * @param int $margin Margin size
     * @return self
     */
    public function setMargin(int $margin): self
    {
        $this->margin = $margin;
        return $this;
    }

    /**
     * Set output format
     * 
     * @param string $format Format (png, svg)
     * @return self
     */
    public function setFormat(string $format): self
    {
        $this->format = strtolower($format);
        return $this;
    }

    /**
     * Set foreground color
     * 
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     * @return self
     */
    public function setForegroundColor(int $r, int $g, int $b): self
    {
        $this->foregroundColor = ['r' => $r, 'g' => $g, 'b' => $b];
        return $this;
    }

    /**
     * Set background color
     * 
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     * @return self
     */
    public function setBackgroundColor(int $r, int $g, int $b): self
    {
        $this->backgroundColor = ['r' => $r, 'g' => $g, 'b' => $b];
        return $this;
    }

    /**
     * Build QR code instance
     * 
     * @return QrCode
     */
    protected function buildQrCode(): QrCode
    {
        $qrCode = QrCode::create($this->text)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize($this->size)
            ->setMargin($this->margin)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(
                $this->foregroundColor['r'],
                $this->foregroundColor['g'],
                $this->foregroundColor['b']
            ))
            ->setBackgroundColor(new Color(
                $this->backgroundColor['r'],
                $this->backgroundColor['g'],
                $this->backgroundColor['b']
            ));

        return $qrCode;
    }

    /**
     * Generate QR code and save to file
     * 
     * @param string $filepath Full file path
     * @param string $label Optional label text
     * @param string $logoPath Optional logo path
     * @return bool Success status
     */
    public function generate(string $filepath, string $label = '', string $logoPath = ''): bool
    {
        try {
            // Build QR code
            $qrCode = $this->buildQrCode();

            // Select writer based on format
            $writer = $this->format === 'svg' ? new SvgWriter() : new PngWriter();

            // Create result
            $result = $writer->write($qrCode);

            // Add label if provided
            if (!empty($label)) {
                $labelObj = Label::create($label)
                    ->setTextColor(new Color(0, 0, 0));

                $result = $writer->write($qrCode, null, $labelObj);
            }

            // Add logo if provided
            if (!empty($logoPath) && file_exists($logoPath)) {
                $logo = Logo::create($logoPath)
                    ->setResizeToWidth(50);

                $result = $writer->write($qrCode, $logo);
            }

            // Create directory if not exists
            $directory = dirname($filepath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save to file
            $result->saveToFile($filepath);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'QR Generation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate QR code and return as data URI
     * 
     * @param string $label Optional label text
     * @return string Data URI
     */
    public function getDataUri(string $label = ''): string
    {
        try {
            // Build QR code
            $qrCode = $this->buildQrCode();

            // Select writer
            $writer = $this->format === 'svg' ? new SvgWriter() : new PngWriter();

            // Add label if provided
            if (!empty($label)) {
                $labelObj = Label::create($label)
                    ->setTextColor(new Color(0, 0, 0));

                $result = $writer->write($qrCode, null, $labelObj);
            } else {
                $result = $writer->write($qrCode);
            }

            // Return as data URI
            return $result->getDataUri();
        } catch (\Exception $e) {
            log_message('error', 'QR Generation Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate QR code and output directly to browser
     * 
     * @param string $label Optional label text
     * @return void
     */
    public function output(string $label = ''): void
    {
        try {
            // Build QR code
            $qrCode = $this->buildQrCode();

            // Select writer
            $writer = $this->format === 'svg' ? new SvgWriter() : new PngWriter();

            // Add label if provided
            if (!empty($label)) {
                $labelObj = Label::create($label)
                    ->setTextColor(new Color(0, 0, 0));

                $result = $writer->write($qrCode, null, $labelObj);
            } else {
                $result = $writer->write($qrCode);
            }

            // Set headers
            $mimeType = $this->format === 'svg' ? 'image/svg+xml' : 'image/png';
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="qrcode.' . $this->format . '"');

            // Output
            echo $result->getString();
        } catch (\Exception $e) {
            log_message('error', 'QR Output Error: ' . $e->getMessage());
            echo 'Error generating QR code';
        }
    }

    /**
     * Generate QR code for member card verification
     * 
     * @param string $memberNumber Member number
     * @param string $uuid Verification UUID
     * @param string $savePath Optional save path
     * @return array ['success' => bool, 'path' => string, 'data_uri' => string]
     */
    public function generateMemberCardQR(string $memberNumber, string $uuid, string $savePath = ''): array
    {
        try {
            // Build verification URL
            $verificationUrl = base_url('verify/' . $uuid);

            // Set text
            $this->setText($verificationUrl);

            // Set size for member card (smaller)
            $this->setSize(150);
            $this->setMargin(5);

            // Get data URI for embedding in PDF
            $dataUri = $this->getDataUri('Verifikasi Kartu');

            // Save to file if path provided
            $filePath = '';
            if (!empty($savePath)) {
                $filename = 'qr_' . $memberNumber . '_' . time() . '.png';
                $filePath = rtrim($savePath, '/') . '/' . $filename;

                $this->generate($filePath, 'Verifikasi Kartu');
            }

            return [
                'success' => true,
                'url' => $verificationUrl,
                'path' => $filePath,
                'data_uri' => $dataUri,
                'member_number' => $memberNumber,
                'uuid' => $uuid
            ];
        } catch (\Exception $e) {
            log_message('error', 'Member Card QR Generation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate WhatsApp QR code for group invitation
     * 
     * @param string $whatsappUrl WhatsApp group URL
     * @param string $groupName Group name
     * @param string $savePath Save path
     * @return array Generation result
     */
    public function generateWhatsAppGroupQR(string $whatsappUrl, string $groupName, string $savePath): array
    {
        try {
            $this->setText($whatsappUrl);
            $this->setSize(300);

            $filename = 'wa_group_' . sanitize_filename($groupName) . '_' . time() . '.png';
            $filePath = rtrim($savePath, '/') . '/' . $filename;

            $success = $this->generate($filePath, $groupName);

            return [
                'success' => $success,
                'path' => $filePath,
                'url' => $whatsappUrl,
                'group_name' => $groupName
            ];
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp Group QR Generation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate event check-in QR code
     * 
     * @param string $eventCode Event code
     * @param string $eventName Event name
     * @param string $savePath Save path
     * @return array Generation result
     */
    public function generateEventQR(string $eventCode, string $eventName, string $savePath): array
    {
        try {
            $checkInUrl = base_url('event/checkin/' . $eventCode);

            $this->setText($checkInUrl);
            $this->setSize(400);

            $filename = 'event_' . $eventCode . '.png';
            $filePath = rtrim($savePath, '/') . '/' . $filename;

            $success = $this->generate($filePath, $eventName);

            return [
                'success' => $success,
                'path' => $filePath,
                'url' => $checkInUrl,
                'event_code' => $eventCode
            ];
        } catch (\Exception $e) {
            log_message('error', 'Event QR Generation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
