<?php

namespace App\Controller;

use App\Entity\Election;
use App\Entity\ElectionHistory;
use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

use App\Form\ElectionType;

final class ElectionController extends AbstractController
{

    #[Route('/election/dashboard', name: 'app_election_dashboard')]
    public function dashboard(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
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
    public function prepare(string $action, string $election_id = '0', #[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
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
                    $this->createElections($data, $user, $entityManager);
                } else {
                    if ($action === 'cancel') {
                        $data->setIsCancelled(true);
                        $data->setDeletedAt();
                        $entityManager->persist($data);
                    } else if ($action === 'edit') {
                        $copy = $this->copy_election($data, $user);
                        $prev_election->setIsCancelled(true);
                        $history = new ElectionHistory();
                        $history->setCurrent($copy);
                        $history->setPrevious($prev_election);
                        $entityManager->persist($history);
                        $entityManager->persist($copy);
                        $entityManager->persist($prev_election);
                    } else if ($action === 'clone') {
                        $grps = $data->getGroupesConcernes();
                        foreach ($grps as $grp) {
                            $copy = $this->copy_election($data, $user);
                            $entityManager->persist($copy);
                        }
                    } else {
                        $data->setCreatedAt();
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
    public function candidats(string $election_id = '0', #[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);

        return $this->render('election/candidats.html.twig', [
            'user' => $user,
            'election' => $election
        ]);
    }

    private function createElections($data, $user, $entityManager)
    {
        $groupes = $entityManager->getRepository(Groupe::class)->findAll();
        foreach ($groupes as $grp) {
            $election = $this->copy_election($data, $user, $grp);
            $entityManager->persist($election);
            $entityManager->flush();
        }
    }

    private function copy_election($data, $user, $grp = false)
    {
        $election = new Election();
        $election->setUser($user);
        $election->setUnite($user->getUnite());

        $election->setStartDate($data->getStartDate());
        $election->setEndDate($data->getEndDate());
        $election->setTitle($data->getTitle());
        $election->setExplaination(is_null($data->getExplaination()) ? '' : $data->getExplaination());
        if($grp){
            $election->addGroupesConcerne($grp);
        }else{
            $groupes  = $data->getGroupesConcernes();
            foreach ($groupes as $grp)
                $election->addGroupesConcerne($grp);
        }
        $unites_concernees = $data->getUnitesConcernees();
        foreach ($unites_concernees as $unt)
            $election->addUnitesConcernee($unt);
        $election->setCreatedAt();
        return $election;
    }
}
