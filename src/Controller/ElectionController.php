<?php

namespace App\Controller;

use App\Entity\Election;
use App\Entity\ElectionHistory;
use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use App\Entity\Candidat;

use App\Form\ElectionType;

final class ElectionController extends AbstractController
{

    #[Route('/election/dashboard', name: 'app_election_dashboard')]
    public function dashboard(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {

        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $elections = $entityManager->getRepository(Election::class)->findBy(['unite' => $user->getUnite(), 'isCancelled' => false]);

        return $this->render('election/dashboard.html.twig', [
            'user' => $user,
            'elections' => $elections
        ]);
    }

    #[Route('/election/action/{action}/{election_id}', name: 'app_election_prepare')]
    public function prepare(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager, string $action, string $election_id = '0'): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $status = "";

        // $action = ('create'|'clone'|'edit'|'cancel')

        $election = new Election();
        $prev_election = new Election();
        $election->setUser($user);
        $election->setUnite($user->getUnite());

        if ($action !== 'create') {
            /**
             * Si édition ou suppression, on marque l'ancienne élection comme annulée (on ne la supprime pas réellement).
             * En cas d'édition, on crée une nouvelle élection ayant l'ID de l'ancienne dans son historique
             */
            $prev_election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
            if ($action === 'cancel')
                $election = $prev_election;
            $election->setStartDate($prev_election->getStartDate());
            $election->setEndDate($prev_election->getEndDate());
            $election->setTitle($prev_election->getTitle());
            $election->setExplaination(is_null($prev_election->getExplaination()) ? '' : $prev_election->getExplaination());
            $groupes_concernes = $prev_election->getGroupesConcernes();
            foreach ($groupes_concernes as $grp)
                $election->addGroupesConcerne($grp);

            $unites_concernees = $prev_election->getUnitesConcernees();
            foreach ($unites_concernees as $unt)
                $election->addUnitesConcernee($unt);
        }

        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $checkboxValue = $form->get('one_election_by_group')->getData();

            if ($form->isValid()) {
                // Si on a coché la case "une élection par corps d'appartenance"
                if ($checkboxValue) {
                    $elections = $this->createElections($data, $user, $entityManager);
                    foreach ($elections as $election) {
                        $election->addCandidat($this->createCandidatBlanc($election));
                        $entityManager->persist($election);
                        $entityManager->flush();
                    }
                } else {
                    if ($action === 'cancel') {
                        $data->setIsCancelled(true);
                        $data->setDeletedAt();
                        $entityManager->persist($data);
                    } else if ($action === 'edit') {
                        $copy = $this->copy_election($data, $user);
                        $candidats = $copy->getCandidats();
                        foreach ($candidats as $candidat)
                            $copy->removeCandidat($candidat);
                        $copy->addCandidat($this->createCandidatBlanc($election));
                        $prev_election->setIsCancelled(true);
                        $history = new ElectionHistory();
                        $history->setCurrent($copy);
                        $history->setPrevious($prev_election);
                        $entityManager->persist($history);
                        $entityManager->persist($copy);
                        $entityManager->persist($prev_election);
                    } else if ($action === 'clone') {
                        // $grps = $data->getGroupesConcernes();
                        // foreach ($grps as $grp) {
                        $copy = $this->copy_election($data, $user);
                        $candidats = $copy->getCandidats();
                        foreach ($candidats as $candidat)
                            $copy->removeCandidat($candidat);
                        $copy->addCandidat($this->createCandidatBlanc($election));
                        $entityManager->persist($copy);
                        // }
                    } else {
                        $data->setUser($user);
                        $data->setUnite($user->getUnite());
                        $data->setCreatedAt();
                        $data->addCandidat($this->createCandidatBlanc($data));
                        $entityManager->persist($data);
                    }
                    $entityManager->flush();
                }
                return $this->redirectToRoute('app_election_dashboard');
            } else {
                $form = $this->createForm(ElectionType::class, $data);
                $status = "error";
            }
        }

        return $this->render('election/create.html.twig', [
            'user' => $user,
            'form' => $form,
            'status' => $status,
            'is_clone' => false,
            'disable' => $action === 'cancel'
        ]);
    }

    #[Route('/election/candidats/{election_id}', name: 'app_election_candidats')]
    public function candidats(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager, string $election_id = '0'): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);

        return $this->render('election/candidats.html.twig', [
            'user' => $user,
            'election' => $election,
            'spontanee' => false
        ]);
    }

    #[Route('/election/candidature/submit/{election_id}', name: 'app_candidature_submit')]
    public function candidature_submit(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager, string $election_id = '0'): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);

        return $this->render('election/candidats.html.twig', [
            'user' => $user,
            'election' => $election,
            'spontanee' => true
        ]);
    }

    #[Route("/create/candidat/{election_id}", name: "create_candidat", methods: ["POST"])]
    public function create_candidat(EntityManagerInterface $entityManager, string $election_id = '0'): JsonResponse
    {
        $request = Request::createFromGlobals();
        $data = (array) json_decode($request->getContent());
        $nigend = $data['nigend'];
        $displayname = $data['displayname'];
        $mail = $data['mail'];

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $candidats = $election->getCandidats();

        $exists = false;
        foreach ($candidats as $c) {
            if ($nigend == $c->getUserId())
                $exists = true;
        }

        $result = [
            'nigend' => $nigend,
            'displayname' => $displayname,
            'mail' => $mail
        ];

        if (!$exists) {
            $candidat = new Candidat();
            $candidat->setElection($election);
            $candidat->setUserId($nigend);
            $candidat->setDisplayname($displayname);
            $candidat->setMail($mail);
            $election->addCandidat($candidat);

            $entityManager->persist($election);
            $entityManager->flush();
            $result['success'] = true;
        } else {
            $result['success'] = false;
            $result['error'] = 'La candidat a déjà été inscrit.';
        }

        return new JsonResponse($result);
    }

    #[Route("/remove/candidat/{election_id}", name: "remove_candidat", methods: ["POST"])]
    public function remove_candidat(EntityManagerInterface $entityManager, string $election_id = '0'): JsonResponse
    {
        $request = Request::createFromGlobals();
        $data = (array) json_decode($request->getContent());
        $nigend = $data['nigend'];

        $candidat = $entityManager->getRepository(Candidat::class)->findOneBy(['userId' => $nigend]);
        if (is_null($candidat))
            return new JsonResponse(['success' => false]);

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $election->removeCandidat($candidat);

        $entityManager->persist($election);
        $entityManager->remove($candidat);
        $entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    #[Route("/setcandidaturesspontanees/{election_id}", name: "setcandidaturesspontanees", methods: ["POST"])]
    public function setcandidaturesspontanees(EntityManagerInterface $entityManager, string $election_id = '0'): JsonResponse
    {
        $request = Request::createFromGlobals();
        $data = (array) json_decode($request->getContent());
        $value = $data['value'];

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $election->setCandidaturesLibres($value);

        $entityManager->persist($election);
        $entityManager->flush();
        return new JsonResponse(['value' => $value]);
    }

    private function createElections($data, $user, $entityManager)
    {
        $elections = [];
        $groupes = $entityManager->getRepository(Groupe::class)->findAll();
        foreach ($groupes as $grp) {
            $elections[] = $this->copy_election($data, $user, $grp);
        }
        return $elections;
    }

    private function copy_election($data, $user, Groupe | false $grp = false)
    {
        $election = new Election();
        $election->setUser($user);
        $election->setUnite($user->getUnite());

        $election->setStartDate($data->getStartDate());
        $election->setEndDate($data->getEndDate());
        $election->setTitle($data->getTitle());
        $election->setExplaination(is_null($data->getExplaination()) ? '' : $data->getExplaination());
        if ($grp) {
            $election->addGroupesConcerne($grp);
        } else {
            $groupes  = $data->getGroupesConcernes();
            foreach ($groupes as $grp)
                $election->addGroupesConcerne($grp);
        }
        $unites_concernees = $data->getUnitesConcernees();
        foreach ($unites_concernees as $unt)
            $election->addUnitesConcernee($unt);
        $election->setCreatedAt();
        $candidats = $data->getCandidats();
        foreach ($candidats as $candidat)
            $election->addCandidat($candidat);
        return $election;
    }

    private function createCandidatBlanc(Election $election)
    {
        $candidat = new Candidat();
        $candidat->setElection($election);
        $candidat->setUserId("");
        $candidat->setDisplayname("VOTE BLANC");
        $candidat->setMail("");
        return $candidat;
    }
}
