<?php

namespace App\service;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class GeneratePdf{
    public function generatePdfResponse(string $html, string $filename): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
    public function generateRepertoir(?string $lastRepertoir): string
    {
        if (!$lastRepertoir) {
            return '1/' . date('Y'); // Default starting value if no previous "vente" exists
        }

        // Split the last "repertoir" into the number and year parts
        list($number, $year) = explode('/', $lastRepertoir);

        // Increment the number by 1
        $number = (int)$number + 1;

        // Return the new "repertoir" in the same format
        return $number . '/' . $year;
    }
}