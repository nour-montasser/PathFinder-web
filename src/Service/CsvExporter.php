<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public function exportDashboardData(array $data): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($data) {
            $handle = fopen('php://output', 'w+');
            
            // Headers
            fputcsv($handle, ['Metric', 'Value']);
            
            // Stats
            foreach ($data['stats'] as $key => $value) {
                fputcsv($handle, [
                    $this->formatKey($key),
                    $this->formatValue($key, $value)
                ]);
            }
            
            fputcsv($handle, []); // Empty line
            
            // Job applications
            fputcsv($handle, ['Job Title', 'Applications']);
            foreach ($data['job_offers'] as $job) {
                fputcsv($handle, [
                    $job->getTitle(),
                    count($job->getApplications())
                ]);
            }
            
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="dashboard_export.csv"');
        
        return $response;
    }

    private function formatKey(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    private function formatValue(string $key, $value): string
    {
        if (strpos($key, 'change') !== false || strpos($key, 'rate') !== false) {
            return is_numeric($value) ? number_format($value, 2).'%' : $value;
        }
        return is_scalar($value) ? (string) $value : '';
    }
}