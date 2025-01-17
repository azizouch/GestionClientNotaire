<?php

namespace App\Controller;

use App\Entity\Procuration;
use App\Form\ProcurationType;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ProcurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Shared\ZipArchive;
use App\service\DateFormatterService;



class ProcurationController extends AbstractController
{
    private DateFormatterService $dateFormatter;

    public function __construct(DateFormatterService $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
    }
    #[Route('/procurations', name: 'app_procurations')]
    public function index(ProcurationRepository $procurationRepository): Response
    {
        $procurations = $procurationRepository->findAll();
        return $this->render('procurations/listProcurations.html.twig', [
            'procurations' => $procurations,
        ]);
    }
    #[Route('/procuration/new', name: 'app_add_new_procuration')]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        PersonnePhysiqueRepository $personnePhysiqueRepository,
    ): Response
    {
        $Persons = $personnePhysiqueRepository->findAll();
        $procuration = new Procuration();

        // Create the form with your form type
        $form = $this->createForm(ProcurationType::class, $procuration);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the data to the database
//            dd($procuration);
            $entityManager->persist($procuration);
            $entityManager->flush();

            // Redirect to a success page or show a message
            return $this->redirectToRoute('app_procurations');
        }

        // Render the form template
        return $this->render('procurations/addNewProcuration.html.twig', [
            'form' => $form->createView(),
            'Persons' => $Persons,
        ]);
    }
    // code for pdf document
    #[Route('/procuration/{id}/pdf-eng', name: 'app_procuration_pdf_eng')]
    #[Route('/procuration/{id}/pdf', name: 'app_procuration_pdf')]
    public function generateProcurationPdf(
        int $id,
        ProcurationRepository $procurationRepository,
        Request $request
    ): Response {
        // Fetch the procuration
        $procuration = $procurationRepository->find($id);
        if (!$procuration) {
            throw $this->createNotFoundException('Procuration not found.');
        }

        // Determine the template based on the route name
        $routeName = $request->attributes->get('_route');
        $template = match ($routeName) {
            'app_procuration_pdf_eng' => 'procurations/procurationPdfPourEng.html.twig',
            'app_procuration_pdf' => 'procurations/procurationPdf.html.twig',
            default => throw new \LogicException('Unexpected route.'),
        };
        // Format the dates
        $formattedDateMaitre = $this->dateFormatter->formatDateTimeInFrench($procuration->getDateMaitre());
        $formattedDateMandant = $this->dateFormatter->formatDateTimeInFrench($procuration->getDateMandant());
        $formattedDateMandataire = $this->dateFormatter->formatDateTimeInFrench($procuration->getDateMandataire());

        // Render the HTML for the template
        $html = $this->renderView($template, [
            'procuration' => $procuration,
            'formattedDateMaitre' => $formattedDateMaitre,
            'formattedDateMandant' => $formattedDateMandant,
            'formattedDateMandataire' => $formattedDateMandataire,
        ]);

        // Generate and return the PDF
        return $this->generatePdfResponse($html, 'procuration.pdf');
    }

    private function generatePdfResponse(string $html, string $filename): Response
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
    // code for word document

//    #[Route('/procuration/{id}/word-eng', name: 'app_procuration_word_eng')]
//    #[Route('/procuration/{id}/word', name: 'app_procuration_word')]
//    public function generateProcurationWord(
//        int $id,
//        ProcurationRepository $procurationRepository,
//        Request $request
//    ): Response {
//        // Fetch the procuration
//        $procuration = $procurationRepository->find($id);
//        if (!$procuration) {
//            throw $this->createNotFoundException('Procuration not found.');
//        }
//
//        // Determine the template based on the route name
//        $routeName = $request->attributes->get('_route');
//        $template = match ($routeName) {
//            'app_procuration_word_eng' => 'procurations/procurationPdfPourEng.html.twig',
//            'app_procuration_word' => 'procurations/procurationPdf.html.twig',
//            default => throw new \LogicException('Unexpected route.'),
//        };
//
//        // Format the dates
//        $formattedDateMaitre = $this->formatDateTimeInFrench($procuration->getDateMaitre());
//        $formattedDateMandant = $this->formatDateTimeInFrench($procuration->getDateMandant());
//        $formattedDateMandataire = $this->formatDateTimeInFrench($procuration->getDateMandataire());
//
//        // Render the HTML content using the Twig template
//        $htmlContent = $this->renderView($template, [
//            'procuration' => $procuration,
//            'formattedDateMaitre' => $formattedDateMaitre,
//            'formattedDateMandant' => $formattedDateMandant,
//            'formattedDateMandataire' => $formattedDateMandataire,
//        ]);
//
//        // Generate the Word document
//        return $this->generateWordResponse($htmlContent, 'procuration.docx');
//    }

    #[Route('/generate-word', name: 'generate_word')]
    public function generateWord(): Response
    {
        // Create a new Word document
        $phpWord = new PhpWord();

        // Add a new section to the document
        $section = $phpWord->addSection();

        // Add HTML content to the section
        $html = '<h1>Hello, World!</h1><p>This is a sample Word document created from Symfony!</p>';
        Html::addHtml($section, $html, false, false);

        // Save the document as a .docx file
        $fileName = 'sample_document.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword_') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        // Return the document as a response
        return new BinaryFileResponse($tempFile, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }


    // function to format the datetime

    private function cleanHtml(string $html): string
    {
        // Load the HTML into DOMDocument
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // Suppress errors

        // Load the HTML, assuming it's well-formed
        if (@$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            // Loop through all <style> tags and remove them
            foreach ($dom->getElementsByTagName('style') as $node) {
                $node->parentNode->removeChild($node);
            }

            // Return the cleaned HTML
            return $dom->saveHTML();
        }

        return ''; // Return an empty string if loading fails
    }

//    private function cleanHtml(string $html): string
//    {
//        $dom = new \DOMDocument();
//
//        // Suppress warnings and parse the HTML
//        libxml_use_internal_errors(true);
//        @$dom->loadHTML('<!DOCTYPE html><html><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
//        libxml_clear_errors();
//
//        // Extract the body content safely
//        $body = $dom->getElementsByTagName('body')->item(0);
//        if ($body === null) {
//            return ''; // Return an empty string if no body element is found
//        }
//
//        return $dom->saveHTML($body);
//    }

    #[Route('/procuration/{id}/word', name: 'app_procuration_word')]
    public function generateProcurationWord(
        int $id,
        ProcurationRepository $procurationRepository,
        Request $request
    ): Response {
        // Fetch the procuration
        $procuration = $procurationRepository->find($id);
        if (!$procuration) {
            throw $this->createNotFoundException('Procuration not found.');
        }

        // Format the dates
        $formattedDateMaitre = $this->formatDateTimeInFrench($procuration->getDateMaitre());
        $formattedDateMandant = $this->formatDateTimeInFrench($procuration->getDateMandant());
        $formattedDateMandataire = $this->formatDateTimeInFrench($procuration->getDateMandataire());

        // Render the HTML for the Word document
        $html = $this->renderView('procurations/procurationPdf.html.twig', [
            'procuration' => $procuration,
            'formattedDateMaitre' => $formattedDateMaitre,
            'formattedDateMandant' => $formattedDateMandant,
            'formattedDateMandataire' => $formattedDateMandataire,
        ]);

        // Generate and return the Word document
        return new Response($html, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="procuration.docx"',
        ]);
    }


}
