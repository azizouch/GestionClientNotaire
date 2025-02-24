<?php

namespace App\Controller;


use App\Entity\Desistement;
use App\Entity\Dossier;
use App\Form\DesistementType;
use App\Repository\DesistementRepository;
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

class DesistementController extends AbstractController
{
    private DateFormatterService $dateFormatter;
    private ConvertNumber $convertNumber;
    private GeneratePdf $generatePdf;
    private AuditLogger $auditLogger;
    private Security $security;
    private $paginator;
    public function __construct(DateFormatterService $dateFormatter,
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
    #[Route('/desistements', name: 'app_desistements')]
    public function index(Request $request, DesistementRepository $desistementRepository, PersonneMoraleRepository $personneMoraleRepository): Response
    {
        $searchQuery = $request->query->get('search', '');
        $PmoraleName = $request->query->get('PmoraleName', '');
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $desistements = $desistementRepository->searchDesistements($searchQuery,$PmoraleName);

        // Pagination
        $pagination = $this->paginator->paginate(
            $desistements,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('desistements/listDesistements.html.twig', [
            'pagination' => $pagination,
            'desistements' => $desistementRepository->findAll(),
            'Pmorales' => $personneMoraleRepository->findAll(),
            'itemsPerPage' => $itemsPerPage,
            'searchQuery' => $searchQuery,
            'PmoraleName' => $PmoraleName
        ]);
    }

    #[Route('/desistement/new', name: 'app_add_new_desistement')]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        PersonnePhysiqueRepository $personnePhysiqueRepository,
                        PersonneMoraleRepository $personneMoraleRepository,
                        DossierRepository $dossierRepository
    ): Response
    {
        $Pphysiques = $personnePhysiqueRepository->findAll();
        $Pmorales = $personneMoraleRepository->findAll();

        $desistement = new Desistement();
        $dossier = new Dossier();

        $dossier->setDevis(0);
        $dossier->setStatut("Active");
        $dossier->setSuivie("Desistement créé");
        $dossier->setCreatedAt(new \DateTime());
        $dossier->setUpdatedAt(new \DateTime());
        $dossier->setRepertoir($dossierRepository->generateRepertoir($entityManager));

        // Create the form with your form type
        $form = $this->createForm(DesistementType::class, $desistement);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the data to the database
            $desistement->setCreatedAt(new \DateTime());
            $desistement->setUpdatedAt(new \DateTime());
            $desistement->setRepertoir($this->generatePdf->generateRepertoir($entityManager));
            $desistement->setDossier($dossier);
            $dossier->setDesistement($desistement);

            dd($desistement);
            $entityManager->persist($desistement);
            $entityManager->flush();

            // Log the action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

            $this->auditLogger->log(
                $user,
                'Création',
                sprintf('Un nouveau desistement avec Rep: %s a été ajouté par %s.', $desistement->getRepertoir(), $username)
            );

            // Redirect to a success page or show a message
            return $this->redirectToRoute('app_desistements');
        }

        // Render the form template
        return $this->render('desistements/addNewDesistement.html.twig', [
            'form' => $form->createView(),
            'Pphysiques' => $Pphysiques,
            'Pmorales' => $Pmorales
        ]);
    }
}
