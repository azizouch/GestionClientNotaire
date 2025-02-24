<?php

namespace App\service;
use Doctrine\ORM\EntityManagerInterface;
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

    public function generateRepertoir(EntityManagerInterface $entityManager): string
    {
        // Initialize the maximum number variable
        $maxNumber = 0;

        // Query for "compromis" and "vente"
        $queryContrats = $entityManager->createQuery(
            'SELECT c.repertoir
        FROM App\Entity\Contrat c
        WHERE c.type IN (:types)'
        )->setParameter('types', ['compromis', 'vente']);

        $contrats = $queryContrats->getResult();

        // Extract the maximum number from the repertoir
        foreach ($contrats as $contrat) {
            if ($contrat['repertoir']) {
                list($number, $year) = explode('/', $contrat['repertoir']);
                $maxNumber = max($maxNumber, (int)$number);
            }
        }

        // Query for "procuration"
        $queryProcuration = $entityManager->createQuery(
            'SELECT p.repertoir
        FROM App\Entity\Procuration p'
        );

        $procurations = $queryProcuration->getResult();

        // Extract the maximum number from the repertoir for procurations
        foreach ($procurations as $procuration) {
            if ($procuration['repertoir']) {
                list($number, $year) = explode('/', $procuration['repertoir']);
                $maxNumber = max($maxNumber, (int)$number);
            }
        }

        // Query for "desistement"
        $queryDesistement = $entityManager->createQuery(
            'SELECT d.repertoir
        FROM App\Entity\Desistement d'
        );

        $desistements = $queryDesistement->getResult();

        // Extract the maximum number from the repertoir for desistements
        foreach ($desistements as $desistement) {
            if ($desistement['repertoir']) {
                list($number, $year) = explode('/', $desistement['repertoir']);
                $maxNumber = max($maxNumber, (int)$number);
            }
        }

        // Get the current year
        $currentYear = date('Y');

        // Increment the maximum number by 1 or start at 1 if none exist
        $newNumber = $maxNumber + 1;

        // Return the new repertory in the format "number/currentYear"
        return $newNumber . '/' . $currentYear;
    }
}