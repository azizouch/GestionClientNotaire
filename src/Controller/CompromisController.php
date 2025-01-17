<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\CompromisType;
use App\Form\ContratType;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ContratRepository;
use App\Repository\ProcurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\service\DateFormatterService;

class CompromisController extends AbstractController
{
    private DateFormatterService $dateFormatter;
    public function __construct(DateFormatterService $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
    }
    #[Route('/compromis', name: 'app_compromis')]
    public function index(ContratRepository $contratRepository): Response
    {
        $compromis = $contratRepository->findCompromis();
        return $this->render('compromis/listCompromis.html.twig', [
            'compromis' => $compromis,
        ]);
    }
    #[Route('/compromis/new', name: 'app_add_new_compromis')]
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
            dd($contrat);
            $entityManager->persist($contrat);
            $entityManager->flush();

            // Redirect to a success page or show a message
            return $this->redirectToRoute('compromis/listCompromis.html.twig');
        }

        // Render the form template
        return $this->render('compromis/addNewCompromis.html.twig', [
            'form' => $form->createView(),
            'PersonnesPhysiques' => $PersonnesPhysiques,
            'PersonnesMorales' => $PersonnesMorales,
        ]);
    }
    // code for pdf document
    #[Route('/compromis/{id}/pdf-eng', name: 'app_compromis_pdf_eng')]
    #[Route('/compromis/{id}/pdf', name: 'app_compromis_pdf')]
    public function generateCompromisPdf(int $id, ContratRepository $contratRepository, Request $request): Response
    {
        // Fetch the compromis
        $compromis = $contratRepository->find($id);
        if (!$compromis) {
            throw $this->createNotFoundException('Compromis not found.');
        }

        // Determine the template based on the route name
        $routeName = $request->attributes->get('_route');
        $template = match ($routeName) {
            'app_compromis_pdf_eng' => 'compromis/compromisPdfPourEng.html.twig',
            'app_compromis_pdf' => 'compromis/compromisPdf.html.twig',
            default => throw new \LogicException('Unexpected route.'),
        };
        // Format the dates
        $formattedDateMaitre = $this->dateFormatter->formatDateTimeInFrench($compromis->getDateMaitre());
        $formattedDatePromettant = $this->dateFormatter->formatDateTimeInFrench($compromis->getDatePromettant());
        $formattedDateBeneficiaire = $this->dateFormatter->formatDateTimeInFrench($compromis->getDateBeneficiaire());

        // Render the HTML for the template
        $html = $this->renderView($template, [
            'compromis' => $compromis,
            'formattedDateMaitre' => $formattedDateMaitre,
            'formattedDatePromettant' => $formattedDatePromettant,
            'formattedDateBeneficiaire' => $formattedDateBeneficiaire,
        ]);

        // Generate and return the PDF
        return $this->generatePdfResponse($html, 'compromis.pdf');
    }
}
