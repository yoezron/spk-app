<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PdfGenerator
 * 
 * Wrapper library untuk Dompdf - PDF generation
 * Digunakan untuk generate Kartu Anggota, Laporan, dan dokumen PDF lainnya
 * 
 * Usage:
 * $pdf = new \App\Libraries\PdfGenerator();
 * $pdf->loadView('member/card_pdf', $data);
 * $pdf->stream('kartu-anggota.pdf');
 * 
 * @package App\Libraries
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PdfGenerator
{
    /**
     * @var Dompdf
     */
    protected $dompdf;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var string
     */
    protected $paper = 'A4';

    /**
     * @var string
     */
    protected $orientation = 'portrait';

    /**
     * Constructor - Initialize Dompdf
     * 
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        // Set default options
        $this->options = new Options();
        $this->options->set('isRemoteEnabled', true);
        $this->options->set('isHtml5ParserEnabled', true);
        $this->options->set('isFontSubsettingEnabled', true);
        $this->options->set('defaultFont', 'Arial');

        // Apply custom config
        foreach ($config as $key => $value) {
            $this->options->set($key, $value);
        }

        // Initialize Dompdf
        $this->dompdf = new Dompdf($this->options);
    }

    /**
     * Load HTML content from view file
     * 
     * @param string $view View file name
     * @param array $data Data to pass to view
     * @return self
     */
    public function loadView(string $view, array $data = []): self
    {
        // Load view and get HTML content
        $html = view($view, $data);

        // Load HTML to Dompdf
        $this->dompdf->loadHtml($html);

        return $this;
    }

    /**
     * Load HTML content directly
     * 
     * @param string $html HTML content
     * @return self
     */
    public function loadHtml(string $html): self
    {
        $this->dompdf->loadHtml($html);

        return $this;
    }

    /**
     * Set paper size and orientation
     * 
     * @param string $paper Paper size (A4, Letter, Legal, etc)
     * @param string $orientation Orientation (portrait, landscape)
     * @return self
     */
    public function setPaper(string $paper = 'A4', string $orientation = 'portrait'): self
    {
        $this->paper = $paper;
        $this->orientation = $orientation;

        $this->dompdf->setPaper($paper, $orientation);

        return $this;
    }

    /**
     * Set custom paper size
     * 
     * @param float $width Width in points
     * @param float $height Height in points
     * @param string $orientation Orientation
     * @return self
     */
    public function setCustomPaper(float $width, float $height, string $orientation = 'portrait'): self
    {
        $this->dompdf->setPaper([0, 0, $width, $height], $orientation);

        return $this;
    }

    /**
     * Render the PDF
     * 
     * @return self
     */
    public function render(): self
    {
        $this->dompdf->render();

        return $this;
    }

    /**
     * Stream PDF to browser (inline display)
     * 
     * @param string $filename Filename
     * @param array $options Stream options
     * @return void
     */
    public function stream(string $filename = 'document.pdf', array $options = []): void
    {
        // Auto render if not rendered yet
        if (!$this->dompdf->getCanvas()) {
            $this->render();
        }

        $this->dompdf->stream($filename, $options);
    }

    /**
     * Download PDF file
     * 
     * @param string $filename Filename
     * @return void
     */
    public function download(string $filename = 'document.pdf'): void
    {
        $this->stream($filename, ['Attachment' => true]);
    }

    /**
     * Save PDF to file
     * 
     * @param string $filepath Full file path
     * @return bool Success status
     */
    public function save(string $filepath): bool
    {
        try {
            // Auto render if not rendered yet
            if (!$this->dompdf->getCanvas()) {
                $this->render();
            }

            // Get PDF output
            $output = $this->dompdf->output();

            // Create directory if not exists
            $directory = dirname($filepath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save to file
            return file_put_contents($filepath, $output) !== false;
        } catch (\Exception $e) {
            log_message('error', 'PDF Save Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get PDF output as string
     * 
     * @return string PDF binary content
     */
    public function output(): string
    {
        // Auto render if not rendered yet
        if (!$this->dompdf->getCanvas()) {
            $this->render();
        }

        return $this->dompdf->output();
    }

    /**
     * Get Dompdf instance
     * 
     * @return Dompdf
     */
    public function getDompdf(): Dompdf
    {
        return $this->dompdf;
    }

    /**
     * Add page number to PDF
     * 
     * @param string $text Page number text format (e.g., "Page {PAGE_NUM} of {PAGE_COUNT}")
     * @param string $position Position (top, bottom)
     * @param string $align Alignment (left, center, right)
     * @return self
     */
    public function addPageNumbers(string $text = 'Page {PAGE_NUM} of {PAGE_COUNT}', string $position = 'bottom', string $align = 'center'): self
    {
        // This requires custom implementation in the view
        // For now, just return self
        return $this;
    }

    /**
     * Generate PDF for member card (ID Card)
     * Helper method specifically for member cards
     * 
     * @param array $memberData Member data
     * @param bool $download Download or stream
     * @return void
     */
    public function generateMemberCard(array $memberData, bool $download = false): void
    {
        // Set card paper size (ID card: 85.6mm x 54mm)
        $this->setCustomPaper(242.64, 153.07); // Convert mm to points (1mm = 2.834pt)

        // Load member card view
        $this->loadView('member/card_pdf', $memberData);

        // Render
        $this->render();

        // Output
        $filename = 'kartu-anggota-' . ($memberData['member_number'] ?? 'unknown') . '.pdf';

        if ($download) {
            $this->download($filename);
        } else {
            $this->stream($filename);
        }
    }

    /**
     * Generate PDF report with header and footer
     * 
     * @param string $view View file name
     * @param array $data Data to pass to view
     * @param string $filename Output filename
     * @param bool $download Download or stream
     * @return void
     */
    public function generateReport(string $view, array $data, string $filename = 'report.pdf', bool $download = false): void
    {
        // Set A4 portrait
        $this->setPaper('A4', 'portrait');

        // Add report metadata to data
        $data['generated_at'] = date('Y-m-d H:i:s');
        $data['generated_by'] = auth()->user()->username ?? 'System';

        // Load view
        $this->loadView($view, $data);

        // Render
        $this->render();

        // Output
        if ($download) {
            $this->download($filename);
        } else {
            $this->stream($filename);
        }
    }

    /**
     * Generate PDF certificate
     * 
     * @param array $certificateData Certificate data
     * @param string $filename Output filename
     * @param bool $download Download or stream
     * @return void
     */
    public function generateCertificate(array $certificateData, string $filename = 'certificate.pdf', bool $download = false): void
    {
        // Set A4 landscape for certificate
        $this->setPaper('A4', 'landscape');

        // Load certificate view
        $this->loadView('certificates/template', $certificateData);

        // Render
        $this->render();

        // Output
        if ($download) {
            $this->download($filename);
        } else {
            $this->stream($filename);
        }
    }
}
