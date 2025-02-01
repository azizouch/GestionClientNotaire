<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use App\Repository\PersonneMoraleRepository;
use App\Repository\PersonnePhysiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VenteController extends AbstractController
{
    #[Route('/ventes', name: 'app_ventes')]
    public function index(ContratRepository $contratRepository): Response
    {
        $ventes = $contratRepository->findVentes();
        return $this->render('ventes/listVentes.html.twig', [
            'ventes' => $ventes,
        ]);
    }
    #[Route('/ventes/new', name: 'app_add_new_vente')]
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
            return $this->redirectToRoute('ventes/listVentes.html.twig');
        }

        // Render the form template
        return $this->render('Ventes/addNewVente.html.twig', [
            'form' => $form->createView(),
            'PersonnesPhysiques' => $PersonnesPhysiques,
            'PersonnesMorales' => $PersonnesMorales,
        ]);
    }
}
