<?php
namespace App\Service;

use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;

class PdfGenerator
{
    private ParameterBagInterface $params;
    private Environment $twig;

    public function __construct(ParameterBagInterface $params, Environment $twig)
    {
        $this->params = $params;
        $this->twig = $twig;
    }

    public function generateDashboardPdf(array $data, Request $request): Response
    {
        // Generate logo base64
        $logoPath = $this->params->get('kernel.project_dir') . '/public/build/images/logo/pathfinder_logo_white.png';
        $logoBase64 = base64_encode(file_get_contents($logoPath));
   // Add current_user to the data array
   $data = array_merge($data, [
    'current_user' => $data['current_user'] ?? null, // Make sure to pass this from controller
    'base_url' => $request->getSchemeAndHttpHost(),
    'logo_base64' => 'data:image/png;base64,' . $logoBase64,
    'is_pdf_export' => true
]);
        // Render HTML
        $html = $this->twig->render('application_job/dashboard_pdf.html.twig', array_merge($data, [
            'base_url' => $request->getSchemeAndHttpHost(),
            'logo_base64' => 'data:image/png;base64,' . $logoBase64,
            'is_pdf_export' => true
        ]));

        $tempHtml = tempnam(sys_get_temp_dir(), 'dashboard_') . '.html';
        file_put_contents($tempHtml, $html);

        try {
            $pdfPath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';

            Browsershot::html(file_get_contents($tempHtml))
                ->setIncludePath(getenv('PATH'))
                ->waitUntilNetworkIdle()
                ->timeout(120)
                ->format('A4')
                ->landscape()
                ->margins(15, 10, 15, 10)
                ->showBackground()
                ->addChromiumArguments([
                    'disable-dev-shm-usage',
                    'no-sandbox'
                ])
                ->save($pdfPath);

            $response = new Response(
                file_get_contents($pdfPath),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => sprintf(
                        'attachment; filename="dashboard_report_%s.pdf"',
                        date('Y-m-d_H-i')
                    )
                ]
            );

        } finally {
            @unlink($tempHtml);
            @unlink($pdfPath);
        }

        return $response;
    }
}