<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\service\ConvertNumber;
use App\service\DateFormatterService;
use App\service\GeneratePdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VenteController extends AbstractController
{
    private DateFormatterService $dateFormatter;
    private ConvertNumber $convertNumber;
    private GeneratePdf $generatePdf;
    public function __construct(DateFormatterService $dateFormatter,GeneratePdf $generatePdf,ConvertNumber $convertNumber)
    {
        $this->dateFormatter = $dateFormatter;
        $this->convertNumber = $convertNumber;
        $this->generatePdf = $generatePdf;
    }
    #[Route('/ventes', name: 'app_ventes')]
    public function index(ContratRepository $contratRepository): Response
    {
        $ventes = $contratRepository->findVentes();
        return $this->render('ventes/listVentes.html.twig', [
            'ventes' => $ventes,
        ]);
    }
    #[Route('/ventes/new', name: 'app_add_new_vente')]
    public function new(Request $request, EntityManagerInterface $entityManager, PersonnePhysiqueRepository $personnePhysiqueRepository, PersonneMoraleRepository $personneMoraleRepository,): Response
    {
        $PersonnesPhysiques = $personnePhysiqueRepository->findAll();
        $PersonnesMorales = $personneMoraleRepository->findAll();
        $contrat = new Contrat();

        // Create the form with your form type
        $form = $this->createForm(ContratType::class, $contrat);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the data to the database
            // Handle new Personnes Physiques
//            foreach ($contrat->getPphysique() as $newPersonPhysique) {
//                // Persist the new PersonPhysique if it's not already managed
//                if (!$entityManager->contains($newPersonPhysique)) {
//                    $entityManager->persist($newPersonPhysique);
//                }
//            }
//
//            // Handle new Personnes Morales
//            foreach ($contrat->getPmorale() as $newPersonMorale) {
//                if (!$entityManager->contains($newPersonMorale)) {
//                    $entityManager->persist($newPersonMorale);
//                }
//            }

            // Handle selected persons
            $selectedPersons = json_decode($request->get('selectedPersons', '[]'), true);

            foreach ($selectedPersons as $personData) {
                $personId = $personData['id'];
                $personType = $personData['type'];

                if ($personType === 'PersonPhysique') {
                    $person = $entityManager->getRepository(PersonnePhysique::class)->find($personId);
                    if ($person) {
                        $contrat->addPphysique($person);
                    }
                } elseif ($personType === 'PersonMorale') {
                    $person = $entityManager->getRepository(PersonneMorale::class)->find($personId);
                    if ($person) {
                        $contrat->addPmorale($person);
                    }
                }
            }
            dd($contrat);
            $entityManager->persist($contrat);
            $entityManager->flush();

            // Redirect to a success page or show a message
            return $this->redirectToRoute('ventes/listVentes.html.twig');
        }

        // Render the form template
        return $this->render('Ventes/addNewVente.html.twig', [
            'form' => $form->createView(),
            'PersonnesPhysiques' => $PersonnesPhysiques,
            'PersonnesMorales' => $PersonnesMorales,
        ]);
    }

    // code for pdf document
    #[Route('/vente/{id}/pdf-eng', name: 'app_vente_pdf_eng')]
    #[Route('/vente/{id}/pdf', name: 'app_vente_pdf')]
    public function generateCompromisPdf(int $id, ContratRepository $contratRepository, Request $request): Response
    {
        // Fetch the ventes
        $vente = $contratRepository->find($id);
        if (!$vente) {
            throw $this->createNotFoundException('Ventes not found.');
        }

        // Determine the template based on the route name
        $routeName = $request->attributes->get('_route');
        $template = match ($routeName) {
            'app_vente_pdf_eng' => 'ventes/ventePdfPourEng.html.twig',
            'app_vente_pdf' => 'ventes/ventePdf.html.twig',
            default => throw new \LogicException('Unexpected route.'),
        };
        // Format the dates
        $formattedDatePromettant = $this->dateFormatter->formatDateTimeInFrench($vente->getDatePromettant());
        $formattedDateBeneficiaire = $this->dateFormatter->formatDateTimeInFrench($vente->getDateBeneficiaire());
        $formattedDateMaitre = $this->dateFormatter->formatDateTimeInFrench($vente->getDateMaitre());

        $MontantTTCconverted = $this->convertNumber->convertDecimalToWords($vente->getDesignation()->getMontantTTC());
        $MontantHTconverted = $this->convertNumber->convertDecimalToWords($vente->getDesignation()->getMontantHT());
        $MontantTVAconverted = $this->convertNumber->convertDecimalToWords(47340.70);
        $Delaiconverted = $this->convertNumber->convertDecimalToWords($vente->getDesignation()->getDelai());

        // Render the HTML for the template
        $html = $this->renderView($template, [
            'vente' => $vente,
            'formattedDatePromettant' => $formattedDatePromettant,
            'formattedDateBeneficiaire' => $formattedDateBeneficiaire,
            'formattedDateMaitre' => $formattedDateMaitre,
            'MontantTTCconverted'=> $MontantTTCconverted,
            'MontantHTconverted'=> $MontantHTconverted,
            'MontantTVAconverted'=> $MontantTVAconverted,
            'Delaiconverted'=> $Delaiconverted,

        ]);

        // Generate and return the PDF
        return $this->generatePdf->generatePdfResponse($html, 'vente.pdf');
    }
}
