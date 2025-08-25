<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\SsoUser as User;
use App\Entity\Groupe;

class SsoSimulator
{
    private $manager;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManager();
    }

    public function loadUserByIdentifier(string $identifier): User
    {
        // Simulate get_user_data (in real: call API or session)
        $ssoData = $this->getUserData($identifier); // Fake method

        // Deduce group from grade (your logic)
        $groupe = $this->deduceGroupFromGrade($ssoData['grade']);

        // Load roles from DB
        // ... Use repo to find UserRole by userId
        $user = new User();
        $user->setUserId($ssoData['user_id']);
        $user->setUniteId($ssoData['unite_id']);
        $user->setGrade($ssoData['grade']);
        $user->setGroupe($groupe);

        return $user;
    }

    private function getUserData(string $token): array
    {
        // Fake: in real, HTTP request or whatever
        return [
            'user_id' => '00249205',
            'unite_id' => '00086977',
            'grade' => 'adjudant' // SSO gives grade, you deduce group
        ];
    }

    private function deduceGroupFromGrade(string $grade): Groupe
    {
        $search = "Sous-Officier";
        $groups = $this->manager->getRepository(Groupe::class)->findAll();
        $groupe = $groups[0];
        foreach ($groups as $grp) {
            if ($grp->getName() == $search) {
                $groupe = $grp;
            }
        }

        // Your mapping: ex A/B/C -> Nord group1, etc.
        return $groupe; // Example
    }

    // Other methods: refreshUser, supportsClass...
}