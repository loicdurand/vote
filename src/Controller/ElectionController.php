<?php

namespace App\Controller;

use App\Entity\Election;
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
    private $env;

    #[Route('/election/create', name: 'app_election_create')]
    public function create(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user))
            return $this->redirectToRoute('app_login');

        /**
         * @Todo: Pré-sélectonner les uniés selon la config de l'user
         */

        $status = "";
        $form = $this->createForm(ElectionType::class);
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
            'status' => $status
        ]);
    }

    #[Route('/election/dashboard', name: 'app_election_dashboard')]
    public function dashboard(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('election/dashboard.html.twig', [
            'user' => $user
        ]);
    }
}
