<?php

namespace App\Controller;

use App\Entity\AuditLog;
use App\Entity\Contrat;
use App\Entity\Desistement;
use App\Entity\Dossier;
use App\Entity\Paiement;
use App\Entity\Procuration;
use App\Entity\User;

use App\Form\RegistrationFormType;
use App\Repository\AuditLogRepository;
use App\Repository\ContratRepository;
use App\Repository\DesistementRepository;
use App\Repository\DossierRepository;
use App\Repository\MessageRepository;
use App\Repository\PaiementRepository;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ProcurationRepository;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use App\service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    private Security $security;
    private $paginator;
    private AuditLogger $auditLogger;

    public function __construct(PaginatorInterface $paginator,AuditLogger $auditLogger,Security $security)
    {
        $this->security = $security;
        $this->paginator = $paginator;
        $this->auditLogger = $auditLogger;
    }
    #[Route('/admin', name: 'app_admin')]
    public function index(DossierRepository $dossierRepository,ContratRepository $contratRepository,DesistementRepository $desistementRepository,ProcurationRepository $procurationRepository,PersonneMoraleRepository $personMoraleRepository,AuditLogRepository $auditLogRepository): Response
    {
        $ventes = $contratRepository->findVentes();
        $compromis = $contratRepository->findCompromis();
        $desistements = $desistementRepository->findAll();
        $procurations = $procurationRepository->findAll();

        // Fetch the last 6 audit logs
        $logs = $auditLogRepository->findBy([], ['timestamp' => 'DESC'], 6);
//        $dossiers = $dossierRepository->findBy([], ['CreatedAt' => 'DESC'], 6);
        $personMorales = $personMoraleRepository->findAllWithContractsCount();
        $totalContracts = $contratRepository->count([]); // Get the total number of contracts

        return $this->render('admin/index.html.twig', [
            'compromis' => $compromis,
            'procurations' => $procurations,
            'desistements' => $desistements,
            'ventes' => $ventes,
            'personMorales' => $personMorales,
            'totalContracts' => $totalContracts,
            'logs' => $logs,
//            'dossiers' => $dossiers,
        ]);
    }

    #[Route('/users', name: 'app_users')]
    public function users(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager){

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if($request->files->get('registration_form')['image']){
                $image = $request->files->get('registration_form')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $user->setImage($image_name);
            }
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Set the selected role
            // Set the selected role from the dropdown
            $userType = $request->request->get('userType'); // Get the value from the select field
            $user->setRoles([$userType]); // Set the selected role

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
//            dd($user);
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            // Redirect to the app_users route
            return new RedirectResponse($this->generateUrl('app_users'));
//            return $security->login($user, AppCustomAuthenticator::class, 'main');
        }
        $users = $userRepository->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'registrationForm' => $form,
        ]);
    }

    #[Route('/dossiers', name: 'app_dossiers')]
    public function dossiers(Request $request,EntityManagerInterface $entityManager): Response
    {
        // Fetch the number of items per page from the request, defaulting to 10
        $itemsPerPage = $request->query->getInt('itemsPerPage', 10);

        // Fetch all dossiers
        $dossiers = $entityManager->getRepository(Dossier::class)->findBy([], ['id' => 'DESC']);
        $pagination = $this->paginator->paginate(
            $dossiers,
            $request->query->getInt('page', 1),
            $itemsPerPage // Number of dossier per page from the request
        );

        return $this->render('admin/dossiers.html.twig', [
            'pagination' => $pagination,
            'itemsPerPage' => $itemsPerPage, // Pass the items per page to the view
        ]);
    }

    #[Route('/api/statistics', name: 'api_statistics')]
    public function getStatistics(DesistementRepository $desistementRepository,ProcurationRepository $procurationRepository,ContratRepository $contratRepository): JsonResponse
    {
        $year = (int) date('Y');

        // Fetch data using repository methods
        $desistements = $desistementRepository->countByMonth($year);
        $procurations = $procurationRepository->countByMonth($year);
        $compromis = $contratRepository->countByMonth($year, 'compromis');

        return new JsonResponse([
            'desistements' => $desistements,
            'procurations' => $procurations,
            'compromis' => $compromis,
        ]);
    }

    #[Route('/admin/audit-logs', name: 'admin_audit_logs')]
    public function viewAuditLogs(Request $request, EntityManagerInterface $em): Response
    {
        // Get date filters from the request
        $dateFromString = $request->query->get('dateFrom');
        $dateToString = $request->query->get('dateTo');

        // Validate and convert dates
        function validateAndCreateDate($dateString)
        {
            if (!$dateString) {
                return null; // Allow empty values (to show all logs)
            }

            $date = \DateTime::createFromFormat('d/m/Y', $dateString);
            return ($date && $date->format('d/m/Y') === $dateString) ? $date : null;
        }

        $dateFrom = validateAndCreateDate($dateFromString);
        $dateTo = validateAndCreateDate($dateToString);

        // Fetch distinct users
        $users = $em->createQueryBuilder()
            ->select('u.username')
            ->from(User::class, 'u')
            ->join(AuditLog::class, 'a', 'WITH', 'a.user = u')
            ->distinct()
            ->getQuery()
            ->getResult();

        // Fetch distinct actions
        $actions = $em->createQueryBuilder()
            ->select('DISTINCT a.action')
            ->from(AuditLog::class, 'a')
            ->getQuery()
            ->getResult();

        // Initialize QueryBuilder
        $queryBuilder = $em->getRepository(AuditLog::class)->createQueryBuilder('a');

        // Apply user filter
        if ($user = $request->query->get('user')) {
            $queryBuilder->join('a.user', 'u')
                ->andWhere('u.username LIKE :user')
                ->setParameter('user', '%' . $user . '%');
        }

        // Apply action filter
        if ($action = $request->query->get('action')) {
            $queryBuilder->andWhere('a.action LIKE :action')
                ->setParameter('action', '%' . $action . '%');
        }

        // Apply date filters
        if ($dateFrom && $dateTo) {
            // Apply date range filter
            $queryBuilder->andWhere('a.timestamp BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom->setTime(0, 0, 0))
                ->setParameter('dateTo', $dateTo->setTime(23, 59, 59));
        } elseif ($dateFrom) {
            // Apply filter for start date only
            $queryBuilder->andWhere('a.timestamp >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom->setTime(0, 0, 0));
        } elseif ($dateTo) {
            // Apply filter for end date only
            $queryBuilder->andWhere('a.timestamp <= :dateTo')
                ->setParameter('dateTo', $dateTo->setTime(23, 59, 59));
        }

        // Fetch results
        $auditLogs = $queryBuilder->orderBy('a.timestamp', 'DESC')->getQuery()->getResult();
        // Fetch the number of items per page from the request, defaulting to 10
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $pagination = $this->paginator->paginate(
            $auditLogs,
            $request->query->getInt('page', 1),
            $itemsPerPage // Number of dossier per page from the request
        );

        return $this->render('admin/audit_logs.html.twig', [
            'pagination' => $pagination,
            'itemsPerPage' => $itemsPerPage,
            'users' => $users,
            'actions' => $actions,
        ]);
    }

    #[Route('/search/client', name: 'search_client', methods: ['GET'])]
    public function searchPersonPhysique(Request $request, PersonnePhysiqueRepository $personPhysiqueRepository): Response
    {
        $searchTerm = $request->query->get('query');

        // If search term is empty, redirect to admin page
        if (empty($searchTerm)) {
            return $this->redirectToRoute('app_admin'); // Change 'app_admin' to your actual admin route name
        }

        // Fetch persons with related contrats, procurations, and desistements
        $persons = $personPhysiqueRepository->searchPersonPhysiqueWithRelations($searchTerm);

        return $this->render('admin/searchClients.html.twig', [
            'persons' => $persons,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/dossier/update', name: 'dossier_update', methods: ['POST'])]
    public function updateDossier(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['devis'], $data['suivie'], $data['dossierId'])) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $dossier = $entityManager->getRepository(Dossier::class)->find($data['dossierId']);

        if (!$dossier) {
            return new JsonResponse(['success' => false, 'message' => 'Dossier not found'], 404);
        }

        $dossier->setDevis($data['devis']);
        $dossier->setSuivie($data['suivie']);
        $entityManager->flush();
        // Log the action
        $user = $this->security->getUser();
        $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

        $this->auditLogger->log(
            $user,
            'Modification',
            sprintf('Le dossier avec Rep: %s a été modifié par %s.', $dossier->getRepertoir(), $username)
        );

        return new JsonResponse(['success' => true, 'message' => 'Dossier updated successfully'], Response::HTTP_OK);
    }

    #[Route('/dossier/annuler/{id}', name: 'annuler_dossier', methods: ['POST'])]
    public function annulerDossier(int $id, EntityManagerInterface $entityManager): Response
    {
        $dossier = $entityManager->getRepository(Dossier::class)->find($id);

        if (!$dossier) {
            throw $this->createNotFoundException('Dossier not found');
        }

        // Change the status
        $dossier->setStatut('Annuler');

        // Save the change
        $entityManager->flush();
        // Log the action
        $user = $this->security->getUser();
        $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

        $this->auditLogger->log(
            $user,
            'Annulation',
            sprintf('Le dossier avec Rep: %s a été annulé par %s.', $dossier->getRepertoir(), $username)
        );

        return $this->redirectToRoute('app_dossiers'); // Redirect after update
    }

    #[Route('/desannuler/{id}', name: 'desannuler_dossier', methods: ['POST'])]
    public function desannulerDossier(int $id, EntityManagerInterface $em): Response
    {
        $dossier = $em->getRepository(Dossier::class)->find($id);
        if (!$dossier) {
            $this->addFlash('error', 'Dossier introuvable.');
            return $this->redirectToRoute('app_dossiers');
        }

        // Change statut to Active
        $dossier->setStatut('Active');
        $em->flush();
        // Log the action
        $user = $this->security->getUser();
        $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

        $this->auditLogger->log(
            $user,
            'Reactivation',
            sprintf('Le dossier avec Rep: %s a été reactivé par %s.', $dossier->getRepertoir(), $username)
        );
        return $this->redirectToRoute('app_dossiers');
    }

    #[Route('/paiement/add', name: 'app_paiement_add', methods: ['POST'])]
    public function addPaiement(Request $request, EntityManagerInterface $entityManager, DossierRepository $dossierRepository): JsonResponse
    {
        if($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            if (!$data || !isset($data['dossierId'], $data['name'], $data['type'], $data['montant'], $data['date'])) {
                return new JsonResponse(['success' => false, 'message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
            }

            $dossier = $dossierRepository->find($data['dossierId']);
            if (!$dossier) {
                return new JsonResponse(['success' => false, 'message' => 'Dossier not found'], Response::HTTP_NOT_FOUND);
            }

            $formatPaiement = "";
            if($data['name'] == "Avance"){
                $formatPaiement = "une Avance de frais de " . (float) $data['montant'] . " DH";
            }
            elseif ($data['name'] == "Complement"){
                $formatPaiement = "Un Complement de frais de " . (float) $data['montant'] . " DH";
            }
            else{
                $formatPaiement = "une Note de frais de " . (float) $data['montant'] . " DH";
            }

            // Create new paiement
            $paiement = new Paiement();
            $paiement->setName($data['name']);
            $paiement->setType($data['type']);
            $paiement->setMontant((float) $data['montant']);
            $paiement->setDate(new \DateTime($data['date']));
            $paiement->setDossier($dossier);
            // Save to database
            $entityManager->persist($paiement);
            $entityManager->flush();
            // Log the action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';


            $this->auditLogger->log(
                $user,
                'Création',
                sprintf('%s a ajouté %s pour le dossier Rep: %s.',$username,$formatPaiement ,$dossier->getRepertoir())
            );
            return new JsonResponse(['paiementId' => $paiement->getId()]);
        }

        return new JsonResponse(['error' => 'Cet appel doit être effectué via AJAX.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/paiement/delete/{id}', name: 'app_paiement_delete', methods: ['DELETE'])]
    public function deletePaiement(int $id, EntityManagerInterface $entityManager, PaiementRepository $paiementRepository): JsonResponse
    {
        $paiement = $paiementRepository->find($id);

        if (!$paiement) {
            return new JsonResponse(['success' => false, 'message' => 'Paiement non trouvé'], Response::HTTP_NOT_FOUND);
        }
        $formatPaiement = "";
        if($paiement->getName() == "Avance"){
            $formatPaiement = "une Avance de frais de " . (float) $paiement->getMontant() . " DH";
        }
        elseif ($paiement->getName() == "Complement"){
            $formatPaiement = "Un Complement de frais de " . (float) $paiement->getMontant() . " DH";
        }
        else{
            $formatPaiement = "une Note de frais de " . (float) $paiement->getMontant() . " DH";
        }

        $entityManager->remove($paiement);
        $entityManager->flush();

        // Log the action
        $user = $this->security->getUser();
        $username = $user ? $user->getUsername() : 'Utilisateur inconnu';


        $this->auditLogger->log(
            $user,
            'Suppression',
            sprintf('%s a supprimé %s pour le dossier Rep: %s.',$username,$formatPaiement ,$paiement->getDossier()->getRepertoir())
        );

        return new JsonResponse(['success' => true, 'message' => 'Paiement supprimé avec succès']);
    }
}
