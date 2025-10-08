<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    protected $dompdf;
    
    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $this->dompdf = new Dompdf($options);
    }
    
    /**
     * Generate Member Card PDF
     *
     * @param array $memberData Member data
     * @param string $qrCodeBase64 Base64 encoded QR code
     * @return string PDF content
     */
    public function generateMemberCard(array $memberData, string $qrCodeBase64): string
    {
        $html = $this->getMemberCardTemplate($memberData, $qrCodeBase64);
        
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper([0, 0, 243, 153], 'landscape'); // Credit card size: 85.6mm x 53.98mm
        $this->dompdf->render();
        
        return $this->dompdf->output();
    }
    
    /**
     * Save PDF to file
     *
     * @param string $pdfContent
     * @param string $filename
     * @return string Path to saved file
     */
    public function savePdf(string $pdfContent, string $filename): string
    {
        $uploadPath = WRITEPATH . 'uploads/cards/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filePath = $uploadPath . $filename;
        file_put_contents($filePath, $pdfContent);
        
        return 'uploads/cards/' . $filename;
    }
    
    /**
     * Get Member Card HTML Template
     *
     * @param array $data
     * @param string $qrCodeBase64
     * @return string
     */
    private function getMemberCardTemplate(array $data, string $qrCodeBase64): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 10px;
                }
                .card {
                    width: 243px;
                    height: 153px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 10px;
                    position: relative;
                }
                .header {
                    text-align: center;
                    border-bottom: 1px solid rgba(255,255,255,0.3);
                    padding-bottom: 5px;
                    margin-bottom: 8px;
                }
                .logo {
                    font-size: 14px;
                    font-weight: bold;
                    letter-spacing: 1px;
                }
                .subtitle {
                    font-size: 8px;
                    opacity: 0.9;
                }
                .content {
                    display: table;
                    width: 100%;
                }
                .left {
                    display: table-cell;
                    width: 140px;
                    vertical-align: top;
                }
                .right {
                    display: table-cell;
                    width: 93px;
                    text-align: center;
                    vertical-align: middle;
                }
                .field {
                    margin-bottom: 4px;
                }
                .label {
                    font-size: 8px;
                    opacity: 0.8;
                }
                .value {
                    font-size: 10px;
                    font-weight: bold;
                }
                .qr-code {
                    width: 70px;
                    height: 70px;
                    background: white;
                    padding: 3px;
                    border-radius: 4px;
                }
                .footer {
                    position: absolute;
                    bottom: 5px;
                    left: 10px;
                    right: 10px;
                    font-size: 7px;
                    text-align: center;
                    opacity: 0.7;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="header">
                    <div class="logo">SPK</div>
                    <div class="subtitle">Serikat Pekerja Kampus</div>
                </div>
                <div class="content">
                    <div class="left">
                        <div class="field">
                            <div class="label">No. Anggota</div>
                            <div class="value">' . htmlspecialchars($data['member_number']) . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Nama</div>
                            <div class="value">' . htmlspecialchars($data['full_name']) . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Wilayah</div>
                            <div class="value">' . htmlspecialchars($data['region_name']) . '</div>
                        </div>
                        <div class="field">
                            <div class="label">Status</div>
                            <div class="value">' . strtoupper(htmlspecialchars($data['status'])) . '</div>
                        </div>
                    </div>
                    <div class="right">
                        <img src="data:image/png;base64,' . $qrCodeBase64 . '" class="qr-code" />
                    </div>
                </div>
                <div class="footer">
                    Valid from: ' . date('d/m/Y', strtotime($data['join_date'])) . '
                </div>
            </div>
        </body>
        </html>
        ';
    }
}
