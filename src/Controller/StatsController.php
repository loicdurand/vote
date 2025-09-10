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

use App\Controller\UserSearchController;

final class StatsController extends AbstractController
{
    #[Route('/stats/index', name: 'app_stats_index')]
    public function stats_index(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {

        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $elections = $entityManager->getRepository(Election::class)->findBy(['isCancelled' => false]);
        $elections_passees = [];

        foreach ($elections as $election) {
            if ($election->getEndDate() < new \DateTime()) {
                $elections_passees[] = $election;
            }
        }

        $groupe_id = $user->getGroupe()->getId();
        return $this->render('stats/index.html.twig', [
            'user' => $user,
            'user_groupe_id' => $groupe_id,
            'elections' => $elections_passees
        ]);
    }

    #[Route('/stats/afficher/{election_id}', name: 'app_stats')]
    public function app_stats(string $election_id = '0', #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $unites = $election->getUnitesConcernees();

        // nombre de total de participants potentiels
        // @TODO trier par catégorie/corps
        $invitedCount = 0;
        foreach ($unites as $unite) {
            $invitedCount += UserSearchController::countGroupMembers($unite->getName());
        }

        $voterCount = $entityManager->getRepository(Vote::class)->count(['election' => $election]);
        $participationRate = $voterCount / $invitedCount * 100;

        $voteDistribution = $entityManager->getRepository(Vote::class)->getVoteRepartition($election);
        $winner = $this->findElectionWinner($voteDistribution);

        $dailyParticipation = $entityManager->getRepository(Registre::class)->getDailyParticipation($election);

        return $this->render('stats/afficher.html.twig', [
            'user' => $user,
            'election' => $election,
            'invitedCount' => $invitedCount,
            'voterCount' => $voterCount,
            'participationRate' => $participationRate,
            'voteDistribution' => $voteDistribution,
            'winner' => $winner,
            'dailyParticipation' => $dailyParticipation
        ]);
    }

    private function findElectionWinner(array $voteDistribution): ?array
    {
        $maxVotes = 0;
        $winner = null;

        foreach ($voteDistribution as $candidatData) {
            // Ignorer le vote blanc (insensible à la casse)
            if (strtolower($candidatData['candidat']) === 'vote blanc') {
                continue;
            }

            $votes = (int)$candidatData['votes'];

            // Mettre à jour le vainqueur si plus de votes
            if ($votes > $maxVotes) {
                $maxVotes = $votes;
                $winner = [
                    'candidat' => $candidatData['candidat'],
                    'votes' => $votes
                ];
            } elseif ($votes === $maxVotes && $votes > 0) {
                // Gérer les égalités : on peut réinitialiser le vainqueur si égalité
                $winner = false; // Pas de vainqueur clair en cas d'égalité
            }
        }

        return $winner;
    }
}
