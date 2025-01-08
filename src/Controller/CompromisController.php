<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\CompromisType;
use App\Form\ContratType;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompromisController extends AbstractController
{
    #[Route('/compromis', name: 'app_compromis')]
    public function index(ContratRepository $contratRepository): Response
    {
        $compromis = $contratRepository->findCompromis();
        return $this->render('compromis/listCompromis.html.twig', [
            'compromis' => $compromis,
        ]);
    }

    #[Route('/compromis/new', name: 'app_add_new_compromis')]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        PersonnePhysiqueRepository $personnePhysiqueRepository,
                        PersonneMoraleRepository $personneMoraleRepository,
    ): Response
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
}
