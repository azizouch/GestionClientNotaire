<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\Dossier;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use App\Entity\Role;
use App\Form\ContratType;
use App\Repository\DossierRepository;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\service\DateFormatterService;
use App\service\GeneratePdf;
use App\service\ConvertNumber;
use App\service\AuditLogger;
use Symfony\Bundle\SecurityBundle\Security;
class CompromisController extends AbstractController
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
    #[Route('/compromis', name: 'app_compromis')]
    public function index(Request $request,ContratRepository $contratRepository,PersonneMoraleRepository $personneMoraleRepository): Response
    {

        $searchQuery = $request->query->get('search', '');
        $PmoraleName = $request->query->get('PmoraleName', '');
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $compromis = $contratRepository->searchContrat($searchQuery, $PmoraleName,'compromis');

        // Pagination
        $pagination = $this->paginator->paginate(
            $compromis,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('compromis/listCompromis.html.twig', [
            'pagination' => $pagination,
            'compromis' => $contratRepository->findCompromis(),
            'Pmorales' => $personneMoraleRepository->findAll(),
            'itemsPerPage' => $itemsPerPage,
            'searchQuery' => $searchQuery,
            'PmoraleName' => $PmoraleName
        ]);
    }

    #[Route('/compromis/new', name: 'app_add_new_compromis')]
    public function new(Request $request, EntityManagerInterface $entityManager, PersonnePhysiqueRepository $personnePhysiqueRepository, PersonneMoraleRepository $personneMoraleRepository, DossierRepository $dossierRepository): Response
    {
        $PersonnesPhysiques = $personnePhysiqueRepository->findAll();
        $PersonnesMorales = $personneMoraleRepository->findAll();

        $compromis = new Contrat();
        $dossier = new Dossier();

        $dossier->setDevis(0);
        $dossier->setStatut("Active");
        $dossier->setSuivie("Compromis créé");
        $dossier->setCreatedAt(new \DateTime());
        $dossier->setUpdatedAt(new \DateTime());
        $dossier->setRepertoir($dossierRepository->generateRepertoir($entityManager));

        $compromis->setCreatedAt(new \DateTime());
        $compromis->setUpdatedAt(new \DateTime());

        // Create the form with your form type
        $form = $this->createForm(ContratType::class, $compromis);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Handle selected persons
            $selectedPersons = json_decode($form->get('selectedPersons')->getData() ?? '[]', true);

            foreach ($selectedPersons as $personData) {
                $personId = $personData['id'];
                $personType = $personData['type'];
                $roleName = $personData['role'];

                if ($personType === 'personPhysique') {
                    $person = $entityManager->getRepository(PersonnePhysique::class)->find($personId);
                    if ($person) {
                        // Try to retrieve the existing role
                        $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);
//                        dd($role);
                        if (!$role) {
                            // If the role does not exist, create it
                            $role = new Role();
                            $role->setName($roleName);
                            $entityManager->persist($role); // Persist the new role
                        }

                        // Add the role to the person (this will not remove any existing roles)
                        $person->addRole($role);

                        // Add the person to the contract (as needed)
                        $compromis->addPphysique($person);
                    }
                } elseif ($personType === 'personMorale') {
                    $person = $entityManager->getRepository(PersonneMorale::class)->find($personId);
                    if ($person) {
                        // Try to retrieve the existing role
                        $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);

                        if (!$role) {
                            // If the role does not exist, create it
                            $role = new Role();
                            $role->setName($roleName);
                            $entityManager->persist($role); // Persist the new role
                        }

                        // Add the role to the person (this will not remove any existing roles)
                        $person->addRole($role);

                        // Add the person to the contract (as needed)
                        $compromis->addPmorale($person);
                    }
                }
            }

            $compromis->setType('compromis');
            $compromis->setRepertoir($this->generatePdf->generateRepertoir($entityManager));
            $compromis->setDossier($dossier);
            $dossier->setCompromis($compromis);


            dd($compromis);

            $entityManager->persist($compromis);
            $entityManager->persist($dossier);
            $entityManager->flush();

            // Log the action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

            $this->auditLogger->log(
                $user,
                'Création',
                sprintf('Un nouveau compromis avec Rep: %s a été ajouté par %s.', $compromis->getRepertoir(), $username)
            );

            return $this->redirectToRoute('app_compromis');
        }

        // Render the form template
        return $this->render('compromis/addNewCompromis.html.twig', [
            'form' => $form->createView(),
            'PersonnesPhysiques' => $PersonnesPhysiques,
            'PersonnesMorales' => $PersonnesMorales,
        ]);
    }

    #[Route('/compromis/update/{id}', name: 'app_compromis_update')]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, PersonnePhysiqueRepository $personnePhysiqueRepository, PersonneMoraleRepository $personneMoraleRepository): Response {
        $contrat = $entityManager->getRepository(Contrat::class)->find($id);

        if (!$contrat) {
            throw $this->createNotFoundException('Compromis not found');
        }

        $PersonnesPhysiques = $personnePhysiqueRepository->findAll();
        $PersonnesMorales = $personneMoraleRepository->findAll();

        // Create form with existing data
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle selected persons
            $selectedPersons = json_decode($form->get('selectedPersons')->getData() ?? '[]', true);
//            dd($selectedPersons);
            foreach ($selectedPersons as $personData) {
                $personId = $personData['id'];
                $personType = $personData['type'];
                $roleName = $personData['role'];

                if ($personType === 'personPhysique') {
                    $person = $entityManager->getRepository(PersonnePhysique::class)->find($personId);
                    if ($person) {
                        // Try to retrieve the existing role
                        $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);
//                        dd($role);
                        if (!$role) {
                            // If the role does not exist, create it
                            $role = new Role();
                            $role->setName($roleName);
                            $entityManager->persist($role); // Persist the new role
                        }

                        // Add the role to the person (this will not remove any existing roles)
                        $person->addRole($role);

                        // Add the person to the contract (as needed)
                        $contrat->addPphysique($person);
                    }
                } elseif ($personType === 'personMorale') {
                    $person = $entityManager->getRepository(PersonneMorale::class)->find($personId);
                    if ($person) {
                        // Try to retrieve the existing role
                        $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);

                        if (!$role) {
                            // If the role does not exist, create it
                            $role = new Role();
                            $role->setName($roleName);
                            $entityManager->persist($role); // Persist the new role
                        }

                        // Add the role to the person (this will not remove any existing roles)
                        $person->addRole($role);

                        // Add the person to the contract (as needed)
                        $contrat->addPmorale($person);
                    }
                }
            }
            dd($contrat);
            // Save updates
            $entityManager->flush();
            // Log the update action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

            $this->auditLogger->log(
                $user,
                'Modification',
                sprintf('La vente avec Rep: %s a été modifié par %s.', $vente->getRepertoir(), $username)
            );

            $this->addFlash('success', 'La vente a été mise à jour avec succès.');

            return $this->redirectToRoute('app_compromis');
        }

        return $this->render('compromis/updateCompromis.html.twig', [
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
        $formattedDatePromettant = $this->dateFormatter->formatDateTimeInFrench($compromis->getDatePromettant());
        $formattedDateBeneficiaire = $this->dateFormatter->formatDateTimeInFrench($compromis->getDateBeneficiaire());
        $formattedDateMaitre = $this->dateFormatter->formatDateTimeInFrench($compromis->getDateMaitre());

        $MontantTTCconverted = $this->convertNumber->convertDecimalToWords($compromis->getDesignation()->getMontantTTC());
        $MontantHTconverted = $this->convertNumber->convertDecimalToWords($compromis->getDesignation()->getMontantHT());
        $MontantTVAconverted = $this->convertNumber->convertDecimalToWords(47340.70);
        $Delaiconverted = $this->convertNumber->convertDecimalToWords($compromis->getDesignation()->getDelai());

        // Render the HTML for the template
        $html = $this->renderView($template, [
            'compromis' => $compromis,
            'formattedDatePromettant' => $formattedDatePromettant,
            'formattedDateBeneficiaire' => $formattedDateBeneficiaire,
            'formattedDateMaitre' => $formattedDateMaitre,
            'MontantTTCconverted'=> $MontantTTCconverted,
            'MontantHTconverted'=> $MontantHTconverted,
            'MontantTVAconverted'=> $MontantTVAconverted,
            'Delaiconverted'=> $Delaiconverted,

        ]);


        // Generate and return the PDF
        return $this->generatePdf->generatePdfResponse($html, 'compromis.pdf');
    }

    #[Route('/ajax/search-persons/{searchVal}', name: 'ajax_search_persons', methods: ['GET'])]
    public function searchPersons(Request $request, PersonnePhysiqueRepository $physiqueRepository,PersonneMoraleRepository $moraleRepository, ?string $searchVal = null): JsonResponse
    {
        $response = [];
        // If no search value is provided, fetch all persons
        if (empty($searchVal)) {
            // Fetch both types of persons
            $physiques = $physiqueRepository->findAll();
            $morales = $moraleRepository->findAll();

            // Combine results
            $persons = array_merge($physiques, $morales);
        } else {
            // Search logic
            $physiques = $physiqueRepository->findBySearch($searchVal);
            $morales = $moraleRepository->findBySearch($searchVal);

            // Combine results
            $persons = array_merge($physiques, $morales);
        }

        foreach ($persons as $person) {
            // Check the type of person and build the response accordingly
            if ($person instanceof PersonnePhysique) {
                // Retrieve partners for the current PersonnePhysique
                $partners = $person->getPartenaire();
                $roles = $person->getRoles();

                $partnersData = [];
                $rolesData =[];

                foreach ($roles as $role) {
                    $rolesData[] = $role->getName();
                }

                foreach ($partners as $partner) {
                    $partnersData[] = [
                        'id' => $partner->getId(),
                        'first_name' => $partner->getFirstName(),
                        'last_name' => $partner->getLastName(),
                        'marriage_year' => $partner->getMariageYear(),
                    ];
                }
                $response[] = [
                    'type' => 'PersonPhysique',
                    'id' => $person->getId(),
                    'first_name' => $person->getFirstName(),
                    'last_name' => $person->getLastName(),
                    'cin' => $person->getCin(),
                    'city' => $person->getCity(),
                    'phone' => $person->getTelephone(),
                    'address' => $person->getAddress(),
                    'situation' => $person->getSituation(),
                    'email' => $person->getEmail(),
                    'partners' => $partnersData,
                    'roles' => $rolesData,
                ];
            } elseif ($person instanceof PersonneMorale) {
                $roles = $person->getRoles();
                $rolesData =[];

                foreach ($roles as $role) {
                    $rolesData[] = $role->getName();
                }
                $response[] = [
                    'type' => 'PersonMorale',
                    'id' => $person->getId(),
                    'ice' => $person->getICE(),
                    'if'=> $person->getIdentifiantFiscal(),
                    'address'=> $person->getAdresse(),
                    'name' => $person->getName(),
                    'RC' => $person->getRC(),
                    'city'=> $person->getVille(),
                    'phone' => $person->getTelephone(),
                    'email' => $person->getEmail(),
                    'roles' => $rolesData,
                ];
            }
        }
        return new JsonResponse($response);
    }

    #[Route('/create-vente/{id}', name: 'app_createVente')]
    public function createVente(int $id, EntityManagerInterface $entityManager, ContratRepository $contratRepository): Response
    {
        // Step 1: Retrieve the existing "compromis" using the given $id
        $compromis = $contratRepository->find($id);

        if (!$compromis) {
            throw $this->createNotFoundException('No compromis found for id ' . $id);
        }

        // Get the existing Dossier from the Compromis
        $dossier = $compromis->getDossier();

        // Step 2: Check if the Dossier already has a Vente
        if ($dossier && $dossier->getVente()) {
            $this->addFlash('error', 'Ce dossier a déjà une vente associée.');
            return $this->redirectToRoute('app_compromis'); // Redirect to the vente list or another page
        }

        // Step 2: Create a new "vente" entity (assumed to be the same Contrat entity for both types)
        $vente = new Contrat();
        $vente->setRepertoir($this->generatePdf->generateRepertoir($entityManager));

        // Set date_beneficiaire to the current datetime
        $vente->setDateBeneficiaire(new \DateTime()); // Set to current datetime

        $vente->setDesignation($compromis->getDesignation());  // Adjust based on your entity structure
        // Copy other relevant properties from "compromis" to "vente"

        // Step 4: Set the type of the new contract to "vente"
        $vente->setType('vente');

        // Step 5: Persist the associated persons from the "compromis" to the "vente"
        foreach ($compromis->getPphysique() as $person) {
            $vente->addPphysique($person);
        }
        foreach ($compromis->getPmorale() as $person) {
            $vente->addPmorale($person);
        }
        // Get the existing Dossier from the Compromis
        $vente->setDossier($dossier);
        $dossier->setVente($vente);
//        dd($vente);
        // Step 6: Persist the new "vente" entity
        $entityManager->persist($vente);
        $entityManager->persist($dossier);
        $entityManager->flush();
        // Log the action
        $user = $this->security->getUser();
        $username = $user ? $user->getUsername() : 'Utilisateur inconnu'; // Get username or handle null

        $this->auditLogger->log(
            $user,
            'Création',
            sprintf('La vente avec Rep: %d a été créé par %s.', $vente->getRepertoir(), $username)
        );

        // Redirect to the appropriate route (for example, the "vente" listing or details page)
        return $this->redirectToRoute('app_ventes');  // Adjust as necessary
    }

    #[Route('/test-dossier', name: 'test_dossier')]
    public function createDossier(EntityManagerInterface $entityManager, DossierRepository $dossierRepository)
    {
        $dossier = new Dossier();
        $dossier->setRepertoir($dossierRepository->generateRepertoir($entityManager));

        dd($dossier);
    }

    #[Route('/test-rep', name: 'test_rep')]
    public function testRepertoir(EntityManagerInterface $entityManager): Response
    {
        $newRepertoir = $this->generatePdf->generateRepertoir($entityManager);

        return new Response("New Repertoir: " . $newRepertoir);
    }
}
