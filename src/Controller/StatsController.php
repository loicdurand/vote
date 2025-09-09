<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use App\Entity\Election;
use App\Entity\Candidat;
use App\Entity\Vote;
use App\Entity\Registre;

use App\Service\LdapService;

final class StatsController extends AbstractController
{
    #[Route('/stats/afficher/{election_id}', name: 'app_stats')]
    public function app_stats(string $election_id = '0', #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $ldap = new LdapService();
        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $unites = $election->getUnitesConcernees();

        // nombre de total de participants potentiels
        // @TODO trier par catégorie/corps
        $invitedCount = 0;
        foreach ($unites as $unite) {
            $invitedCount += $ldap->countGroupMembers($unite->getName());
        }

        $voterCount = $entityManager->getRepository(Vote::class)->count(['election' => $election]);
        $participationRate = $voterCount / $invitedCount * 100;

        $voteDistribution = $entityManager->getRepository(Vote::class)->createQueryBuilder('v')
            ->select('c.displayname AS candidat, COUNT(v.id) AS votes')
            ->join('v.candidat', 'c')
            ->where('v.election = :election')
            ->setParameter('election', $election)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();

        dd($voteDistribution);

        return $this->render('stats/index.html.twig', [
            'user' => $user,
            'election' => $election,
            'invitedCount' => $invitedCount,
            'voterCount' => $voterCount,
            'participationRate' => $participationRate
        ]);
    }
}
