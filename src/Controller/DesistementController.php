<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DesistementController extends AbstractController
{
    #[Route('/desistements', name: 'app_desistements')]
    public function index(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findAll();
        return $this->render('desistements/listDesistements.html.twig', [
            'clients' => $clients,
        ]);
    }
}
