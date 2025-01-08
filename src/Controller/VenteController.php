<?php

namespace App\Controller;

use App\Repository\ContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VenteController extends AbstractController
{
    #[Route('/ventes', name: 'app_ventes')]
    public function index(): Response
    {

        return $this->render('ventes/listVentes.html.twig', [

        ]);
    }
}
