<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\Dossier;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use App\Repository\DossierRepository;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\service\AuditLogger;
use App\service\ConvertNumber;
use App\service\DateFormatterService;
use App\service\GeneratePdf;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VenteController extends AbstractController
{
    private DateFormatterService $dateFormatter;
    private ConvertNumber $convertNumber;
    private GeneratePdf $generatePdf;
    private AuditLogger $auditLogger;
    private Security $security;
    private $paginator;
    public function __construct(
        DateFormatterService $dateFormatter,
        GeneratePdf $generatePdf,
        ConvertNumber $convertNumber,
        AuditLogger $auditLogger,
        Security $security,
        PaginatorInterface $paginator)
    {
        $this->dateFormatter = $dateFormatter;
        $this->convertNumber = $convertNumber;
        $this->generatePdf = $generatePdf;
        $this->auditLogger = $auditLogger;
        $this->security = $security;
        $this->paginator = $paginator;
    }
    #[Route('/ventes', name: 'app_ventes')]
    public function index(
        Request $request,
        ContratRepository $contratRepository,
        PersonneMoraleRepository $personneMoraleRepository
    ): Response
    {
        $searchQuery = $request->query->get('search', '');
        $PmoraleName = $request->query->get('PmoraleName', '');
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $vente = $contratRepository->searchContrat($searchQuery, $PmoraleName,'vente');

        // Pagination
        $pagination = $this->paginator->paginate(
            $vente,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('ventes/listVentes.html.twig', [
            'ventes' => $contratRepository->findVentes(),
            'pagination' => $pagination,
            'Pmorales' => $personneMoraleRepository->findAll(),
            'itemsPerPage' => $itemsPerPage,
            'searchQuery' => $searchQuery,
            'PmoraleName' => $PmoraleName
        ]);
    }
    #[Route('/ventes/new', name: 'app_add_new_vente')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PersonnePhysiqueRepository $personnePhysiqueRepository,
        PersonneMoraleRepository $personneMoraleRepository,
        DossierRepository $dossierRepository
    ): Response
    {
        $PersonnesPhysiques = $personnePhysiqueRepository->findAll();
        $PersonnesMorales = $personneMoraleRepository->findAll();
        $vente = new Contrat();
        $dossier = new Dossier();

        $dossier->setDevis(0);
        $dossier->setStatut("Active");
        $dossier->setSuivie("Vente créé");
        $dossier->setCreatedAt(new \DateTime());
        $dossier->setUpdatedAt(new \DateTime());
        $dossier->setRepertoir($dossierRepository->generateRepertoir($entityManager));

        // Create the form with your form type
        $form = $this->createForm(ContratType::class, $vente);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Handle selected persons
            $selectedPersons = json_decode($request->get('selectedPersons', '[]'), true);

            foreach ($selectedPersons as $personData) {
                $personId = $personData['id'];
                $personType = $personData['type'];

                if ($personType === 'PersonPhysique') {
                    $person = $entityManager->getRepository(PersonnePhysique::class)->find($personId);
                    if ($person) {
                        $vente->addPphysique($person);
                    }
                } elseif ($personType === 'PersonMorale') {
                    $person = $entityManager->getRepository(PersonneMorale::class)->find($personId);
                    if ($person) {
                        $vente->addPmorale($person);
                    }
                }
            }

            $vente->setType('vente');
            $vente->setRepertoir($this->generatePdf->generateRepertoir($entityManager));
            $vente->setDossier($dossier);
            $dossier->setVente($vente);

            dd($vente);
            $entityManager->persist($vente);
            $entityManager->flush();

            // Log the action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

            $this->auditLogger->log(
                $user,
                'Création',
                sprintf('Une nouvelle vente avec Rep: %s a été ajouté par %s.', $vente->getRepertoir(), $username)
            );

            return $this->redirectToRoute('app_ventes');
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
    public function generateVentePdf(int $id, ContratRepository $contratRepository, Request $request): Response
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
