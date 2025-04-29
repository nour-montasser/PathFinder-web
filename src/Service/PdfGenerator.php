<?php
// src/Service/PdfGenerator.php

namespace App\Service;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class PdfGenerator
{
    private $pdf;
    private $twig;
    private $projectDir;

    public function __construct(Pdf $pdf, Environment $twig, string $projectDir)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    public function generateDashboardPdf(array $data, Request $request): Response
    {
        // Render the dashboard template to HTML
        $html = $this->twig->render('application_job/dashboard_pdf.html.twig', $data);
        
        // Generate PDF with knsnappy
        $filename = 'dashboard_report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Set PDF options
        $this->pdf->setOption('enable-local-file-access', true);
        $this->pdf->setOption('page-size', 'A4');
        $this->pdf->setOption('margin-top', '10mm');
        $this->pdf->setOption('margin-right', '10mm');
        $this->pdf->setOption('margin-bottom', '10mm');
        $this->pdf->setOption('margin-left', '10mm');
        $this->pdf->setOption('encoding', 'UTF-8');
        
        // Generate the PDF binary content
        $pdfContent = $this->pdf->getOutputFromHtml($html);
        
        // Create and return the response
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }
}