<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\SsoUser as User;
use App\Entity\Groupe;
use App\Entity\Organizer;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        $restos = ['SCL', 'DUG'];
        foreach ($restos as $name) {
            $organizer = new Organizer();
            $organizer->setName($name);
            $manager->persist($organizer);
        }

        $groups = ['Officier', 'Sous-Officier', 'Volontaire', 'Civil'];
        foreach ($groups as $grp) {
            $groupe = new Groupe();
            $groupe->setName($grp);
            $manager->persist($groupe);
            if ($grp == 'Sous-Officier') {
                $user = new User();
                $user->setUserId('00249205');
                $user->setUniteId('00086977');
                $user->setGrade('adjudant');
                $user->setGroupe($groupe);
                $user->setRoles(['ROLE_USER']);
                $password = $this->hasher->hashPassword($user, 'secret');
                $user->setPassword($password);
                $manager->persist($user);

            }
        }

        $manager->flush();
    }
}
