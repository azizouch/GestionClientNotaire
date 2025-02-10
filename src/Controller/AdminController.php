<?php

namespace App\Controller;

use App\Entity\AuditLog;
use App\Entity\Contrat;
use App\Entity\Desistement;
use App\Entity\Procuration;
use App\Entity\User;

use App\Form\RegistrationFormType;
use App\Repository\ContratRepository;
use App\Repository\DesistementRepository;
use App\Repository\MessageRepository;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ProcurationRepository;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/admin', name: 'app_admin')]
    public function index(ContratRepository $contratRepository,DesistementRepository $desistementRepository,ProcurationRepository $procurationRepository,PersonneMoraleRepository $personMoraleRepository): Response
    {
        $ventes = $contratRepository->findVentes();
        $compromis = $contratRepository->findCompromis();
        $desistements = $desistementRepository->findAll();
        $procurations = $procurationRepository->findAll();

        $personMorales = $personMoraleRepository->findAllWithContractsCount();
        $totalContracts = $contratRepository->count([]); // Get the total number of contracts

        return $this->render('admin/index.html.twig', [
            'compromis' => $compromis,
            'procurations' => $procurations,
            'desistements' => $desistements,
            'ventes' => $ventes,
            'personMorales' => $personMorales,
            'totalContracts' => $totalContracts,
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
        $queryBuilder = $em->getRepository(AuditLog::class)->createQueryBuilder('a');

        // Apply filters
        if ($user = $request->query->get('user')) {
            $queryBuilder->join('a.user', 'u')
                ->andWhere('u.username LIKE :user')
                ->setParameter('user', '%' . $user . '%');
        }
        if ($action = $request->query->get('action')) {
            $queryBuilder->andWhere('a.action LIKE :action')
                ->setParameter('action', '%' . $action . '%');
        }
        if ($dateFrom = $request->query->get('dateFrom')) {
            $queryBuilder->andWhere('a.timestamp >= :dateFrom')
                ->setParameter('dateFrom', new \DateTime($dateFrom));
        }
        if ($dateTo = $request->query->get('dateTo')) {
            $queryBuilder->andWhere('a.timestamp <= :dateTo')
                ->setParameter('dateTo', new \DateTime($dateTo));
        }

        $auditLogs = $queryBuilder->orderBy('a.timestamp', 'DESC')->getQuery()->getResult();

        return $this->render('admin/audit_logs.html.twig', [
            'auditLogs' => $auditLogs,
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

}
