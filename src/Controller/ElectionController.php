<?php

namespace App\Controller;

use App\Entity\Election;
use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

use App\Form\ElectionType;
use phpDocumentor\Reflection\Types\Boolean;

final class ElectionController extends AbstractController
{

    #[Route('/election/create', name: 'app_election_create')]
    public function create(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $status = "";

        $election = new Election();
        $election->setUser($user);
        $election->setUnite($user->getUnite());

        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $checkboxValue = $form->get('one_election_by_group')->getData();

            if ($form->isValid()) {
                if ($checkboxValue) {
                    $this->createElections($data, $user, $entityManager);
                } else {
                    $entityManager->persist($data);
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
            'disable' => false
        ]);
    }

    #[Route('/election/edit/{election_id}/{form_cancel}', name: 'app_election_edit')]
    public function edit(string $election_id, string $form_cancel = 'false', #[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $status = "";

        $election = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $election->setUser($user);
        $election->setUnite($user->getUnite());
        $election->setStartDate($election->getStartDate());
        $election->setEndDate($election->getEndDate());
        $election->setTitle($election->getTitle());
        $election->setExplaination($election->getExplaination());
        $groupes_concernes = $election->getGroupesConcernes();
        foreach ($groupes_concernes as $grp)
            $election->addGroupesConcerne($grp);

        $unites_concernees = $election->getUnitesConcernees();
        foreach ($unites_concernees as $unt)
            $election->addUnitesConcernee($unt);

        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            if ($form->isValid()) {
                $entityManager->persist($data);
                $entityManager->flush();
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
            'disable' => boolval($form_cancel)
        ]);
    }

    #[Route('/election/clone/{election_id}', name: 'app_election_clone')]
    public function clone(string $election_id, #[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        $status = "";

        $election = new Election();
        $election->setUser($user);
        $election->setUnite($user->getUnite());

        $election_origine = $entityManager->getRepository(Election::class)->findOneBy(['id' => $election_id]);
        $election->setStartDate($election_origine->getStartDate());
        $election->setEndDate($election_origine->getEndDate());
        $election->setTitle($election_origine->getTitle());
        $election->setExplaination($election_origine->getExplaination());
        $groupes_concernes = $election_origine->getGroupesConcernes();
        foreach ($groupes_concernes as $grp)
            $election->addGroupesConcerne($grp);

        $unites_concernees = $election_origine->getUnitesConcernees();
        foreach ($unites_concernees as $unt)
            $election->addUnitesConcernee($unt);

        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            if ($form->isValid()) {
                $entityManager->persist($data);
                $entityManager->flush();
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
            'is_clone' => true,
            'disable' => false
        ]);
    }

    #[Route('/election/dashboard', name: 'app_election_dashboard')]
    public function dashboard(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {

        $elections = $entityManager->getRepository(Election::class)->findBy(['unite' => $user->getUnite()]);

        return $this->render('election/dashboard.html.twig', [
            'user' => $user,
            'elections' => $elections
        ]);
    }

    private function createElections($data, $user, $entityManager)
    {
        $groupes = $entityManager->getRepository(Groupe::class)->findAll();
        foreach ($groupes as $grp) {
            $election = new Election();
            $election->setUser($user);
            $election->setUnite($user->getUnite());

            $election->setStartDate($data->getStartDate());
            $election->setEndDate($data->getEndDate());
            $election->setTitle($data->getTitle());
            $election->setExplaination(is_null($data->getExplaination()) ? '' : $data->getExplaination());
            $election->addGroupesConcerne($grp);
            $unites_concernees = $data->getUnitesConcernees();
            foreach ($unites_concernees as $unt)
                $election->addUnitesConcernee($unt);

            $entityManager->persist($election);
            $entityManager->flush();
        }
    }
}
