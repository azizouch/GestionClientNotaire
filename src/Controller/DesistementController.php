<?php

namespace App\Controller;


use App\Entity\Desistement;
use App\Form\DesistementType;
use App\Repository\DesistementRepository;
use App\Repository\PersonnePhysiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DesistementController extends AbstractController
{
    #[Route('/desistements', name: 'app_desistements')]
    public function index(DesistementRepository $desistementRepository): Response
    {
        $desistements = $desistementRepository->findAll();
        return $this->render('desistements/listDesistements.html.twig', [
            'desistements' => $desistements,
        ]);
    }
    #[Route('/desistement/new', name: 'app_add_new_desistement')]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        PersonnePhysiqueRepository $personnePhysiqueRepository,
    ): Response
    {
        $Pphysique = $personnePhysiqueRepository->findAll();

        $desistement = new Desistement();

        // Create the form with your form type
        $form = $this->createForm(DesistementType::class, $desistement);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the data to the database
            dd($desistement);
            $entityManager->persist($desistement);
            $entityManager->flush();

            // Redirect to a success page or show a message
            return $this->redirectToRoute('app_desistements');
        }

        // Render the form template
        return $this->render('desistements/addNewDesistement.html.twig', [
            'form' => $form->createView(),
            'Pphysique' => $Pphysique,
        ]);
    }
}
