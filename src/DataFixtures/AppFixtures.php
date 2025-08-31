<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Groupe;
use App\Entity\Unite;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        // $restos = ['SCL', 'DUG'];
        // foreach ($restos as $name) {
        //     $unite = new Unite();
        //     $unite->setName($name);
        //     $manager->persist($unite);
        // }

        $groups = [
            [
                'OG',
                'Officier'
            ],
            [
                'SOG',
                'Sous-Officier'
            ],
            [
                'GAV',
                'Volontaire'
            ],
            [
                'CIV',
                'Civil'
            ]
        ];
        foreach ($groups as $grp) {
            $groupe = new Groupe();
            $groupe->setShortName($grp[0]);
            $groupe->setName($grp[1]);
            $manager->persist($groupe);
        }

        $manager->flush();
    }
}
