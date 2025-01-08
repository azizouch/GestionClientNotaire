<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/chats', name: 'app_chats')]
    public function chats(): Response
    {
        return $this->render('admin/chats.html.twig', [

        ]);
    }

    #[Route('/verify-password', name: 'verify_password')]
    public function verifyPassword(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => 'abdelaziz']);
        if ($user && $passwordHasher->isPasswordValid($user, 'Aziz1998@')) {
            return new Response('Password is valid');
        } else {
            return new Response('Invalid password');
        }
    }

}
