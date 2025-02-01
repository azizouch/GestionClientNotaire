<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use App\Form\ContratType;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
