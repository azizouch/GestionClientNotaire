<?php

namespace App\Controller;

use App\Entity\PersonnePhysique;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PersonnePhysiqueRepository;
use App\Form\PersonnePhysiqueFormType;

class ClientController extends AbstractController
{
    private $paginator;
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
    #[Route('/clients', name: 'app_clients')]
    public function index(Request $request,PersonnePhysiqueRepository $personnePhysiqueRepository): Response
    {
        $searchQuery = $request->query->get('search', '');
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $clientsPagination = $personnePhysiqueRepository->searchClients($searchQuery);

        // Pagination
        $pagination = $this->paginator->paginate(
            $clientsPagination,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('clients/listclients.html.twig', [
            'clients' => $personnePhysiqueRepository->findAll(),
            'pagination' => $pagination,
            'itemsPerPage' => $itemsPerPage,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/Personne_physique/add', name: 'app_add_client')]
    public function addClient(Request $request, EntityManagerInterface $entityManager): Response
    {
        $Personneph = new PersonnePhysique();
        $form = $this->createForm(PersonnePhysiqueFormType::class, $Personneph);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Manually persist each partner
//            dd($Personneph);
            foreach ($Personneph->getPartenaire() as $partner) {
                $entityManager->persist($partner);
            }
            $entityManager->persist($Personneph);
            $entityManager->flush();

            $this->addFlash('success', 'Personne added successfully!');

            return $this->redirectToRoute('app_clients'); // Redirect to client list or another route
        }

        return $this->render('clients/addnewclient.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/client/update/{id}', name: 'app_update_client')]
    public function updateClient(PersonnePhysique $personnePhysique, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PersonnePhysiqueFormType::class, $personnePhysique);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $personnePhysique = $form->getData();
//            dd($personnePhysique);
            $entityManager->persist($personnePhysique);
            $entityManager->flush();

            $this->addFlash('success', 'Client updated successfully!');
            return $this->redirectToRoute('app_clients'); // Change to your client list route
        }

        return $this->render('clients/updateclient.html.twig', [
            'form' => $form->createView(),
            'client' => $personnePhysique,
        ]);
    }

    #[Route('/client/delete/{id}', name: 'app_delete_client')]
    public function deleteClient(PersonnePhysique $personnePhysique, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($personnePhysique);
        $entityManager->flush();
        return $this->redirectToRoute('app_clients'); // Change to your client list route
    }
}
