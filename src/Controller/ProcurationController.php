<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Entity\PersonnePhysique;
use App\Entity\Procuration;
use App\Entity\Role;
use App\Form\ProcurationType;
use App\Repository\DossierRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ProcurationRepository;
use App\service\AuditLogger;
use App\service\ConvertNumber;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
use App\service\GeneratePdf;



class ProcurationController extends AbstractController
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
        $this->generatePdf = $generatePdf;
        $this->auditLogger = $auditLogger;
        $this->security = $security;
        $this->paginator = $paginator;
    }
    #[Route('/procurations', name: 'app_procurations')]
    public function index(Request $request,ProcurationRepository $procurationRepository): Response
    {
        $searchQuery = $request->query->get('search', '');
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $procurations = $procurationRepository->searchProcurations($searchQuery);

        // Pagination
        $pagination = $this->paginator->paginate(
            $procurations,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('procurations/listProcurations.html.twig', [
            'procurations' => $procurationRepository->findAll(),
            'pagination' => $pagination,
            'itemsPerPage' => $itemsPerPage,
            'searchQuery' => $searchQuery
        ]);
    }

    #[Route('/procuration/new', name: 'app_add_new_procuration')]
    public function new(Request $request, EntityManagerInterface $entityManager, PersonnePhysiqueRepository $personnePhysiqueRepository, DossierRepository $dossierRepository): Response
    {
        $Persons = $personnePhysiqueRepository->findAll();
        $procuration = new Procuration();
        $dossier = new Dossier();
        $dossier->setDevis(0);
        $dossier->setStatut("Active");
        $dossier->setSuivie("Procuration créé");
        $dossier->setCreatedAt(new \DateTime());
        $dossier->setUpdatedAt(new \DateTime());
        $dossier->setRepertoir($dossierRepository->generateRepertoir($entityManager));

        // Create the form with your form type
        $form = $this->createForm(ProcurationType::class, $procuration);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $procuration->setCreatedAt(new \DateTime());
            $procuration->setUpdatedAt(new \DateTime());
            $procuration->setRepertoir($this->generatePdf->generateRepertoir($entityManager));
            $procuration->setDossier($dossier);
            $dossier->setProcuration($procuration);

            dd($procuration);
            $entityManager->persist($dossier);  // Persist the Dossier
            $entityManager->persist($procuration);  // Persist the Procuration
            $entityManager->flush();  // Save to the database

            // Log the action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

            $this->auditLogger->log(
                $user,
                'Création',
                sprintf('Une nouvelle procuration avec Rep: %s a été ajouté par %s.', $procuration->getRepertoir(), $username)
            );

            // Redirect to a success page or show a message
            return $this->redirectToRoute('app_procurations');
        }

        // Render the form template
        return $this->render('procurations/addNewProcuration.html.twig', [
            'form' => $form->createView(),
            'Persons' => $Persons,
        ]);
    }

    #[Route('/procuration/update/{id}', name: 'app_update_procuration')]
    public function update(Procuration $procuration, Request $request, EntityManagerInterface $entityManager,PersonnePhysiqueRepository $personnePhysiqueRepository): Response
    {
        // Create the form with existing procuration data
        $Persons = $personnePhysiqueRepository->findAll();
        $form = $this->createForm(ProcurationType::class, $procuration);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle selected persons
            $selectedPersons = json_decode($form->get('selectedPersons')->getData() ?? '[]', true);
            if($selectedPersons){
                //            dd($selectedPersons);
                foreach ($selectedPersons as $personData) {
                    $personId = $personData['id'];
                    $roleName = $personData['role'];

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
                        $procuration->addPerson($person);
                    }
                }
            }

            dd($procuration);
            $entityManager->flush();

            // Log the update action
            $user = $this->security->getUser();
            $username = $user ? $user->getUsername() : 'Utilisateur inconnu';

            $this->auditLogger->log(
                $user,
                'Modification',
                sprintf('Le procuration avec Rep: %s a été modifié par %s.', $procuration->getRepertoir(), $username)
            );

            // Redirect after updating
            return $this->redirectToRoute('app_procurations');
        }

        return $this->render('procurations/updateProcuration.html.twig', [
            'form' => $form->createView(),
            'procuration' => $procuration,
            'Persons' => $Persons,
        ]);
    }

    // code for pdf document
    #[Route('/procuration/{id}/pdf-eng', name: 'app_procuration_pdf_eng')]
    #[Route('/procuration/{id}/pdf', name: 'app_procuration_pdf')]
    public function generateProcurationPdf(int $id, ProcurationRepository $procurationRepository, Request $request): Response {
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
        return $this->generatePdf->generatePdfResponse($html, 'procuration.pdf');
    }

    // code for word document
    #[Route('/procuration/{id}/word-eng', name: 'app_procuration_word_eng')]
    #[Route('/procuration/{id}/word', name: 'app_procuration_word')]
    public function generateProcurationWord(int $id, ProcurationRepository $procurationRepository, Request $request): Response {
        // Fetch the procuration
        $procuration = $procurationRepository->find($id);
        if (!$procuration) {
            throw $this->createNotFoundException('Procuration not found.');
        }

        // Determine the template based on the route name
        $routeName = $request->attributes->get('_route');
        $template = match ($routeName) {
            'app_procuration_word_eng' => 'procurations/procurationPdfPourEng.html.twig',
            'app_procuration_word' => 'procurations/procurationPdf.html.twig',
            default => throw new \LogicException('Unexpected route.'),
        };

        // Format the dates
        $formattedDateMaitre = $this->formatDateTimeInFrench($procuration->getDateMaitre());
        $formattedDateMandant = $this->formatDateTimeInFrench($procuration->getDateMandant());
        $formattedDateMandataire = $this->formatDateTimeInFrench($procuration->getDateMandataire());

        // Render the HTML content using the Twig template
        $htmlContent = $this->renderView($template, [
            'procuration' => $procuration,
            'formattedDateMaitre' => $formattedDateMaitre,
            'formattedDateMandant' => $formattedDateMandant,
            'formattedDateMandataire' => $formattedDateMandataire,
        ]);

        // Generate the Word document
        return $this->generateWordResponse($htmlContent, 'procuration.docx');
    }
}
