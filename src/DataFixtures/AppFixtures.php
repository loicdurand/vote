<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Categorie;
use App\Entity\Lien;
use App\Entity\User;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setLogin('root');
        $user->setRoles(['ROLE_USER', 'ROLE_SIC']);
        $password = $this->hasher->hashPassword($user, 'secret');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();
    }
}
