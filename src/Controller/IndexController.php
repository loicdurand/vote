<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use App\Entity\Election;
use App\Entity\Candidat;
use App\Entity\Vote;
use App\Entity\Registre;

final class IndexController extends AbstractController
{

    #[Route('/', name: 'app_index')]
    public function dashboard(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $elections = $entityManager->getRepository(Election::class)->findBy(['unite' => $user->getUnite(), 'isCancelled' => false]);

        $groupe_id = $user->getGroupe()->getId();
        return $this->render('index/index.html.twig', [
            'user' => $user,
            'user_groupe_id' => $groupe_id,
            'elections' => $elections
        ]);
    }

    #[Route("/index/candidat/{election_id}", name: "app_candidat")]
    public function candidat(string $election_id = '0', #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);

        return $this->render('index/candidat.html.twig', [
            'user' => $user,
            'election' => $election
        ]);
    }

    #[Route("/index/cgv/{election_id}", name: "app_cgv")]
    public function cgv(string $election_id = '0', #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);

        return $this->render('index/cgv.html.twig', [
            'user' => $user,
            'election' => $election
        ]);
    }

    #[Route("/index/vote/{election_id}", name: "app_vote")]
    public function vote(string $election_id = '0', #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);

        return $this->render('index/vote.html.twig', [
            'user' => $user,
            'election' => $election
        ]);
    }

    #[Route("/index/confirm/{candidat_id}", name: "app_confirm")]
    public function confirm(string $candidat_id = '0', #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $candidat = $entityManager->getRepository(Candidat::class)->findOneBy(['id' => $candidat_id]);
        $election = $candidat->getElection();
        $vote = new Vote();
        $vote->setElection($election);
        $vote->setCandidat($candidat);

        $registre = new Registre();
        $registre->setElection($election);
        $registre->setUser($user);
        $registre->setVotedAt(new \Datetime('now'));

        $entityManager->persist($vote);
        $entityManager->persist($registre);
        $entityManager->flush();

        return $this->render('index/vote.html.twig', [
            'user' => $user,
            'election' => $election
        ]);
    }
}
