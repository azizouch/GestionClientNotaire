<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
        ]);
    }

    #[Route('/users', name: 'app_users')]
    public function users(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager){

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if($request->files->get('registration_form')['image']){
                $image = $request->files->get('registration_form')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $user->setImage($image_name);
            }
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Set the selected role
            // Set the selected role from the dropdown
            $userType = $request->request->get('userType'); // Get the value from the select field
            $user->setRoles([$userType]); // Set the selected role

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
//            dd($user);
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            // Redirect to the app_users route
            return new RedirectResponse($this->generateUrl('app_users'));
//            return $security->login($user, AppCustomAuthenticator::class, 'main');
        }
        $users = $userRepository->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'registrationForm' => $form,
        ]);
    }
}
