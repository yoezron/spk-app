<?php

namespace App\Services\Member;

use App\Models\UserModel;
use App\Models\MemberProfileModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * CardGeneratorService
 * 
 * Menangani pembuatan kartu anggota digital dalam format PDF
 * Termasuk QR code untuk verifikasi dan data member lengkap
 * 
 * @package App\Services\Member
 * @author  SPK Development Team
 * @version 1.0.0
 */
class CardGeneratorService
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
     * @var Dompdf
     */
    protected $dompdf;

    /**
     * Card dimensions (in mm)
     */
    const CARD_WIDTH = 85.6;
    const CARD_HEIGHT = 53.98;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->memberModel = new MemberProfileModel();
        $this->initializeDompdf();
    }

    /**
     * Initialize Dompdf with configuration
     * 
     * @return void
     */
    protected function initializeDompdf(): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('dpi', 300);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate member card PDF
     * 
     * @param int $userId User ID
     * @param array $options Generation options
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function generate(int $userId, array $options = []): array
    {
        try {
            // Get member data
            $cardData = $this->getCardData($userId);

            if (!$cardData['success']) {
                return $cardData;
            }

            $member = $cardData['data'];

            // Generate QR code
            $qrCode = $this->generateQRCode($member);

            if (!$qrCode['success']) {
                return $qrCode;
            }

            // Get card template (front and back)
            $htmlFront = $this->getCardTemplateFront($member, $qrCode['data']);
            $htmlBack = $this->getCardTemplateBack($member);

            // Combine front and back
            $html = $this->combineCardTemplates($htmlFront, $htmlBack);

            // Load HTML to Dompdf
            $this->dompdf->loadHtml($html);

            // Set paper size (ID card size: 85.6mm x 53.98mm)
            $this->dompdf->setPaper([0, 0, 242.65, 153], 'landscape'); // Convert mm to points

            // Render PDF
            $this->dompdf->render();

            // Get PDF output
            $output = $this->dompdf->output();

            // Save to file if requested
            if (isset($options['save']) && $options['save']) {
                $filePath = $this->saveCard($userId, $output);

                return [
                    'success' => true,
                    'message' => 'Kartu anggota berhasil dibuat dan disimpan',
                    'data' => [
                        'file_path' => $filePath,
                        'pdf_content' => $output,
                        'member' => $member
                    ]
                ];
            }

            return [
                'success' => true,
                'message' => 'Kartu anggota berhasil dibuat',
                'data' => [
                    'pdf_content' => $output,
                    'member' => $member
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in CardGeneratorService::generate: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat kartu anggota: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Download member card as PDF
     * 
     * @param int $userId User ID
     * @param string|null $filename Custom filename
     * @return array ['success' => bool, 'message' => string]
     */
    public function download(int $userId, ?string $filename = null): array
    {
        try {
            $result = $this->generate($userId);

            if (!$result['success']) {
                return $result;
            }

            $member = $result['data']['member'];
            $pdfContent = $result['data']['pdf_content'];

            // Generate filename if not provided
            if (!$filename) {
                $filename = 'Kartu_Anggota_' . $member->member_number . '.pdf';
            }

            // Output PDF for download
            $this->dompdf->stream($filename, ['Attachment' => true]);

            return [
                'success' => true,
                'message' => 'Kartu anggota berhasil diunduh'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error downloading card: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengunduh kartu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get member card data
     * 
     * @param int $userId User ID
     * @return array ['success' => bool, 'data' => object]
     */
    public function getCardData(int $userId): array
    {
        try {
            // Get member with complete data
            $member = $this->memberModel
                ->select('member_profiles.*, users.username, users.email')
                ->join('users', 'users.id = member_profiles.user_id')
                ->where('member_profiles.user_id', $userId)
                ->first();

            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Data anggota tidak ditemukan',
                    'data' => null
                ];
            }

            // Check if member is active
            if ($member->membership_status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Hanya anggota aktif yang dapat mencetak kartu',
                    'data' => null
                ];
            }

            // Get additional data
            $member->province_name = $this->getProvinceName($member->province_id);
            $member->university_name = $this->getUniversityName($member->university_id);
            $member->validity_date = $this->calculateValidityDate($member->join_date);

            return [
                'success' => true,
                'message' => 'Data anggota ditemukan',
                'data' => $member
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting card data: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error mengambil data: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Generate QR code for member verification
     * 
     * @param object $member Member data
     * @return array ['success' => bool, 'data' => string]
     */
    protected function generateQRCode($member): array
    {
        try {
            // QR code content: verification URL with member number
            $verificationUrl = base_url('verify/' . $member->member_number);

            // Use simple base64 QR code generation
            // In production, use library like chillerlan/php-qrcode or endroid/qr-code
            $qrCodeData = $this->generateQRCodeBase64($verificationUrl);

            return [
                'success' => true,
                'data' => $qrCodeData
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error generating QR code: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat QR code',
                'data' => null
            ];
        }
    }

    /**
     * Generate QR code as base64 image
     * Uses Google Charts API as fallback (in production, use proper QR library)
     * 
     * @param string $data Data to encode
     * @return string Base64 encoded image
     */
    protected function generateQRCodeBase64(string $data): string
    {
        // Using Google Charts API for simplicity
        // In production, replace with proper QR code library
        $qrUrl = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($data);

        try {
            $imageData = file_get_contents($qrUrl);
            return 'data:image/png;base64,' . base64_encode($imageData);
        } catch (\Exception $e) {
            // Fallback: return placeholder
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        }
    }

    /**
     * Get card template HTML - Front side
     * 
     * @param object $member Member data
     * @param string $qrCode Base64 QR code
     * @return string HTML template
     */
    protected function getCardTemplateFront($member, string $qrCode): string
    {
        $photoUrl = $this->getMemberPhotoBase64($member);
        $logoUrl = $this->getLogoBase64();

        return <<<HTML
        <div class="card-front" style="
            width: 85.6mm;
            height: 53.98mm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            padding: 15px;
            box-sizing: border-box;
            color: white;
            font-family: Arial, sans-serif;
            position: relative;
            overflow: hidden;
        ">
            <!-- Background Pattern -->
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMiIgZmlsbD0iI2ZmZiIvPjwvc3ZnPg==');"></div>
            
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; position: relative; z-index: 1;">
                <div>
                    <img src="{$logoUrl}" alt="SPK Logo" style="height: 30px; width: auto;">
                </div>
                <div style="text-align: right; font-size: 7px; line-height: 1.3;">
                    <strong>SERIKAT PEKERJA KAMPUS</strong><br>
                    <span style="font-size: 6px;">Indonesia</span>
                </div>
            </div>

            <!-- Content -->
            <div style="display: flex; gap: 10px; position: relative; z-index: 1;">
                <!-- Photo -->
                <div style="flex-shrink: 0;">
                    <img src="{$photoUrl}" alt="Photo" style="
                        width: 60px;
                        height: 75px;
                        object-fit: cover;
                        border-radius: 5px;
                        border: 2px solid white;
                    ">
                </div>

                <!-- Member Info -->
                <div style="flex-grow: 1; font-size: 8px; line-height: 1.4;">
                    <div style="margin-bottom: 3px;">
                        <strong style="font-size: 9px;">{$member->full_name}</strong>
                    </div>
                    <div style="margin-bottom: 2px;">
                        <strong>No. Anggota:</strong> {$member->member_number}
                    </div>
                    <div style="margin-bottom: 2px; font-size: 7px;">
                        {$member->university_name}
                    </div>
                    <div style="font-size: 7px;">
                        {$member->province_name}
                    </div>
                </div>

                <!-- QR Code -->
                <div style="flex-shrink: 0; text-align: center;">
                    <img src="{$qrCode}" alt="QR Code" style="
                        width: 50px;
                        height: 50px;
                        background: white;
                        padding: 3px;
                        border-radius: 5px;
                    ">
                    <div style="font-size: 5px; margin-top: 2px;">SCAN</div>
                </div>
            </div>

            <!-- Footer -->
            <div style="position: absolute; bottom: 8px; left: 15px; right: 15px; font-size: 6px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 5px; z-index: 1;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Berlaku s/d: <strong>{$member->validity_date}</strong></span>
                    <span>ID: {$member->id}</span>
                </div>
            </div>
        </div>
HTML;
    }

    /**
     * Get card template HTML - Back side
     * 
     * @param object $member Member data
     * @return string HTML template
     */
    protected function getCardTemplateBack($member): string
    {
        return <<<HTML
        <div class="card-back" style="
            width: 85.6mm;
            height: 53.98mm;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            position: relative;
        ">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #667eea;">
                <h3 style="margin: 0; font-size: 10px; color: #667eea;">KARTU ANGGOTA SPK</h3>
                <p style="margin: 3px 0 0 0; font-size: 7px; color: #666;">Serikat Pekerja Kampus Indonesia</p>
            </div>

            <!-- Information -->
            <div style="font-size: 7px; line-height: 1.6; color: #333;">
                <div style="margin-bottom: 5px;">
                    <strong>Hak & Kewajiban Anggota:</strong>
                </div>
                <ul style="margin: 0; padding-left: 15px; font-size: 6px;">
                    <li>Mengikuti kegiatan organisasi</li>
                    <li>Mendapat perlindungan hukum & advokasi</li>
                    <li>Menyampaikan aspirasi & keluhan</li>
                    <li>Membayar iuran rutin</li>
                </ul>

                <div style="margin-top: 8px; font-size: 6px; padding: 5px; background: #fff; border-left: 3px solid #667eea;">
                    <strong>Kontak SPK:</strong><br>
                    Email: info@spk.or.id<br>
                    Website: www.spk.or.id<br>
                    WhatsApp: +62 812-3456-7890
                </div>
            </div>

            <!-- Footer -->
            <div style="position: absolute; bottom: 8px; left: 15px; right: 15px; text-align: center; font-size: 6px; color: #999;">
                Kartu ini adalah bukti keanggotaan resmi SPK
            </div>
        </div>
HTML;
    }

    /**
     * Combine front and back card templates
     * 
     * @param string $htmlFront Front side HTML
     * @param string $htmlBack Back side HTML
     * @return string Combined HTML
     */
    protected function combineCardTemplates(string $htmlFront, string $htmlBack): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    margin: 10mm;
                }
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                }
                .page-break {
                    page-break-after: always;
                }
            </style>
        </head>
        <body>
            {$htmlFront}
            <div class="page-break"></div>
            {$htmlBack}
        </body>
        </html>
HTML;
    }

    /**
     * Save card to storage
     * 
     * @param int $userId User ID
     * @param string $pdfContent PDF content
     * @return string File path
     */
    protected function saveCard(int $userId, string $pdfContent): string
    {
        $uploadPath = WRITEPATH . 'uploads/cards/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = 'card_' . $userId . '_' . time() . '.pdf';
        $filePath = $uploadPath . $filename;

        file_put_contents($filePath, $pdfContent);

        return 'uploads/cards/' . $filename;
    }

    /**
     * Get member photo as base64
     * 
     * @param object $member Member data
     * @return string Base64 encoded image
     */
    protected function getMemberPhotoBase64($member): string
    {
        if (!empty($member->photo_path) && file_exists(FCPATH . $member->photo_path)) {
            $imageData = file_get_contents(FCPATH . $member->photo_path);
            $imageType = pathinfo($member->photo_path, PATHINFO_EXTENSION);
            return 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);
        }

        // Default avatar based on gender
        $defaultAvatar = $member->gender === 'Perempuan' ? 'female-avatar.png' : 'male-avatar.png';
        $avatarPath = FCPATH . 'assets/images/avatars/' . $defaultAvatar;

        if (file_exists($avatarPath)) {
            $imageData = file_get_contents($avatarPath);
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        // Fallback: simple colored rectangle
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="60" height="75" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="75" fill="#cccccc"/></svg>');
    }

    /**
     * Get SPK logo as base64
     * 
     * @return string Base64 encoded image
     */
    protected function getLogoBase64(): string
    {
        $logoPath = FCPATH . 'assets/images/spk-logo.png';

        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        // Fallback: simple text logo
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="100" height="30" xmlns="http://www.w3.org/2000/svg"><text x="5" y="20" font-family="Arial" font-size="18" font-weight="bold" fill="#ffffff">SPK</text></svg>');
    }

    /**
     * Get province name
     * 
     * @param int|null $provinceId Province ID
     * @return string Province name
     */
    protected function getProvinceName(?int $provinceId): string
    {
        if (!$provinceId) {
            return 'N/A';
        }

        $provinceModel = new \App\Models\ProvinceModel();
        $province = $provinceModel->find($provinceId);

        return $province ? $province->name : 'N/A';
    }

    /**
     * Get university name
     * 
     * @param int|null $universityId University ID
     * @return string University name
     */
    protected function getUniversityName(?int $universityId): string
    {
        if (!$universityId) {
            return 'N/A';
        }

        $universityModel = new \App\Models\UniversityModel();
        $university = $universityModel->find($universityId);

        return $university ? $university->name : 'N/A';
    }

    /**
     * Calculate card validity date (2 years from join date)
     * 
     * @param string $joinDate Join date
     * @return string Validity date (formatted)
     */
    protected function calculateValidityDate(string $joinDate): string
    {
        $date = new \DateTime($joinDate);
        $date->modify('+2 years');

        return $date->format('d/m/Y');
    }

    /**
     * Batch generate cards for multiple members
     * 
     * @param array $userIds Array of user IDs
     * @return array ['success' => bool, 'data' => array]
     */
    public function batchGenerate(array $userIds): array
    {
        $results = [
            'total' => count($userIds),
            'success' => 0,
            'failed' => 0,
            'cards' => []
        ];

        foreach ($userIds as $userId) {
            $result = $this->generate($userId, ['save' => true]);

            if ($result['success']) {
                $results['success']++;
                $results['cards'][$userId] = $result['data']['file_path'];
            } else {
                $results['failed']++;
            }
        }

        return [
            'success' => $results['failed'] === 0,
            'message' => sprintf(
                'Berhasil: %d, Gagal: %d dari %d kartu',
                $results['success'],
                $results['failed'],
                $results['total']
            ),
            'data' => $results
        ];
    }

    /**
     * Check if card needs renewal
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function needsRenewal(int $userId): bool
    {
        $cardData = $this->getCardData($userId);

        if (!$cardData['success']) {
            return false;
        }

        $member = $cardData['data'];
        $validityDate = new \DateTime($this->calculateValidityDate($member->join_date));
        $now = new \DateTime();

        // Check if card will expire in 30 days
        $diff = $now->diff($validityDate);

        return $diff->days <= 30 && $diff->invert == 0;
    }
}
