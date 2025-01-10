<?php

namespace App\Controller;

use App\Entity\PersonnePhysique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PersonnePhysiqueRepository;
use NumberToWords\NumberToWords;
use App\Form\PersonnePhysiqueFormType;

class ClientController extends AbstractController
{
    private function convertDecimalToWords($number, $lang = 'fr')
    {
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer($lang);

        // Split the integer and fractional parts
        $parts = explode(',', number_format($number, 3, ',', ''));  // Ensure decimal uses ','

        $integerPart = (int) $parts[0]; // Integer part before the comma
        $fractionalPart = isset($parts[1]) ? (int) $parts[1] : 0; // Decimal part (if exists)

        // Convert both parts to words
        $integerWords = $numberTransformer->toWords($integerPart);
        $fractionalWords = $numberTransformer->toWords($fractionalPart);

        // Customize the output format (e.g., Euros and Cents)
        return trim($integerWords . ' dirhams ' . $fractionalWords . ' centimes ');
    }

    #[Route('/clients', name: 'app_clients')]
    public function index(PersonnePhysiqueRepository $personnePhysiqueRepository): Response
    {
        $number = $this->convertDecimalToWords(249234.68);

        $clients = $personnePhysiqueRepository->findAll();
        return $this->render('clients/listclients.html.twig', [
            'clients' => $clients,
            'number' => $number,
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
            dd($personnePhysique);
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
