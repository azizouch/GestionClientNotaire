<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    // Correct the constructor method name to __construct
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $password_hashed = $this->passwordHasher->hashPassword($user, 'Aziz1998@');
        $user->setUsername('abdelaziz');
        $user->setPassword($password_hashed);
        $manager->persist($user);

        $manager->flush();
    }
}
