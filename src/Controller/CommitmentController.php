<?php

namespace App\Controller;

use App\Repository\PersonnePhysiqueRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommitmentController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    //route for engagement de TVA pdf
    #[Route('/engagement/{id}/pdf', name: 'app_engagement_pdf')]
    public function generate_engagement_Pdf(int $id, PersonnePhysiqueRepository $personnePhysiqueRepository): Response
    {
        // Fetch the client from the repository
        $client = $personnePhysiqueRepository->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Client not found.');
        }

        // Render the HTML using the Twig template
        $projectDir = $this->getParameter('kernel.project_dir');
        $logoPath = $projectDir . '/public/asset/img/commitment/logo.jpg';
        $logoData = base64_encode(file_get_contents($logoPath));

        $html = $this->renderView('commitments/engagementPdf.html.twig', [
            'client' => $client,
            'today' => new \DateTime(),
            'logo_data' => $logoData,
        ]);

        // Configure Dompdf with options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with the options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Output the generated PDF as a response
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="commitments.pdf"',
        ]);
    }

    //route for demande de paiment de TVA <DOUJA>
    #[Route('/demande_douja/{id}/pdf', name: 'app_demande_douja_pdf')]
    public function generate_demande_douja_Pdf(int $id, PersonnePhysiqueRepository $personnePhysiqueRepository): Response
    {
        // Fetch the client from the repository
        $client = $personnePhysiqueRepository->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Client not found.');
        }

        // Render the HTML using the Twig template
        $projectDir = $this->getParameter('kernel.project_dir');
        $logoPath = $projectDir . '/public/img/logo.jpg';
        $logoData = base64_encode(file_get_contents($logoPath));

        $html = $this->renderView('commitments/demandeDouja.html.twig', [
            'client' => $client,
            'today' => new \DateTime(),
            'logo_data' => $logoData,
        ]);

        // Configure Dompdf with options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with the options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Output the generated PDF as a response
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="commitments.pdf"',
        ]);
    }
    //route for demande de paiment de TVA <IMMOLOG>
    #[Route('/demande_immolog/{id}/pdf', name: 'app_demande_immolog_pdf')]
    public function generate_demande_immolog_Pdf(int $id, PersonnePhysiqueRepository $personnePhysiqueRepository): Response
    {
        // Fetch the client from the repository
        $client = $personnePhysiqueRepository->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Client not found.');
        }

        // Render the HTML using the Twig template
        $projectDir = $this->getParameter('kernel.project_dir');
        $logoPath = $projectDir . '/public/img/logo.jpg';
        $logoData = base64_encode(file_get_contents($logoPath));

        $html = $this->renderView('commitments/demandeImmolog.html.twig', [
            'client' => $client,
            'today' => new \DateTime(),
            'logo_data' => $logoData,
        ]);

        // Configure Dompdf with options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with the options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Output the generated PDF as a response
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="commitments.pdf"',
        ]);
    }

}
