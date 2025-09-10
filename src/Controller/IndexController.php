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
    private const ERROR_DEJA_VOTE = <<<TXT
                <p class="fr-mt-2w">
                    Vous avez déjà participé au vote pour cette élection. Vous ne pouvez pas voter à nouveau.
                </p>
                <p class="fr-mt-2w">
                    Si vous détenez toujours la clé que nous vous avons fournie, vous pouvez consultez le contenu de votre vote précédent sur
                    <a href="/index/verify" class="fr-link">la page dédiée à la vérification</a> d'intégrité.
                </p>
TXT;

    #[Route('/', name: 'app_index')]
    public function index(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {

        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $elections = $entityManager->getRepository(Election::class)->findBy(['isCancelled' => false]);

        foreach ($elections as $election) {
            $aDejaVote = $entityManager->getRepository(Registre::class)->findOneBy([
                'election' => $election->getId(),
                'user' => $user->getId()
            ]);

            if ($aDejaVote)
                $election->a_deja_vote = true;
            else
                $election->a_deja_vote = false;
        }

        $groupe_id = $user->getGroupe()->getId();
        $unite_id = $user->getUnite()->getId();

        return $this->render('index/index.html.twig', [
            'user' => $user,
            'user_groupe_id' => $groupe_id,
            'user_unite_id' => $unite_id,
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

        $aDejaVote = $entityManager->getRepository(Registre::class)->findOneBy([
            'election' => $election->getId(),
            'user' => $user->getId()
        ]);

        if ($aDejaVote)
            return $this->render('index/error_deja_vote.html.twig', ['error' => $this::ERROR_DEJA_VOTE]);

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

        $aDejaVote = $entityManager->getRepository(Registre::class)->findOneBy([
            'election' => $election->getId(),
            'user' => $user->getId()
        ]);

        if ($aDejaVote)
            return $this->render('index/error_deja_vote.html.twig', ['error' => $this::ERROR_DEJA_VOTE]);

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

        $secretKey = bin2hex(random_bytes(16));

        $candidat = $entityManager->getRepository(Candidat::class)->findOneBy(['id' => $candidat_id]);
        $election = $candidat->getElection();

        $aDejaVote = $entityManager->getRepository(Registre::class)->findOneBy([
            'election' => $election->getId(),
            'user' => $user->getId()
        ]);

        if ($aDejaVote)
            return $this->render('index/error_deja_vote.html.twig', ['error' => $this::ERROR_DEJA_VOTE]);

        $registreHash = hash('sha256', $secretKey . 'registre');
        $voteHash = hash('sha256', $secretKey . 'vote');

        $vote = new Vote();
        $vote->setElection($election);
        $vote->setCandidat($candidat);
        $vote->setVerificationHash($voteHash);

        $registre = new Registre();
        $registre->setElection($election);
        $registre->setUser($user);
        $registre->setVotedAt(new \Datetime('now'));
        $registre->setVerificationHash($registreHash);

        $entityManager->persist($vote);
        $entityManager->persist($registre);
        $entityManager->flush();

        return $this->render('index/confirm.html.twig', [
            'user' => $user,
            'election' => $election,
            'secret' => $secretKey
        ]);
    }

    #[Route("/index/verify", name: "app_verify")]
    public function verify(#[CurrentUser] ?User $user): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        return $this->render('index/verify.html.twig', ['user' => $user]);
    }

    #[Route("/index/retrieve-data", name: "app_retrieve_data", methods: ["POST"])]
    public function retrieve_data(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $request = Request::createFromGlobals();
        $data = (array) json_decode($request->getContent());
        $secret = $data['secret'] ?? $data['secret'];
        $registreHash = hash('sha256', $secret . 'registre');
        $voteHash = hash('sha256', $secret . 'vote');

        // Recherche dans table "a voté"
        $registre = $entityManager->getRepository(Registre::class)->findOneBy([
            'verification_hash' => $registreHash
        ]);

        // Recherche dans table des votes
        $vote = $entityManager->getRepository(Vote::class)->findOneBy([
            'verification_hash' => $voteHash
        ]);

        if (!$registre || !$vote) {
            // Affiche "Vote non trouvé ou clé invalide."
            return $this->render('partials/tables-verif.html.twig', ['err' => 'not_found']);
        }

        // Vérifie que les election_id matchent .
        if ($registre->getElection()->getId() !== $vote->getElection()->getId()) {
            return $this->render('partials/tables-verif.html.twig', ['err' => 'broken']);
        }

        return $this->render('partials/tables-verif.html.twig', [
            'registre' => $registre,
            'vote' => $vote
        ]);
    }
}
