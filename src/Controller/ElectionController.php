<?php

namespace App\Controller;

use App\Entity\Election;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\SsoUser as User;
use App\Form\ElectionType;

final class ElectionController extends AbstractController
{
    private $env;

    #[Route('/election/create', name: 'app_election')]
    public function index(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
            // $user = new User();
        }
        // $session = $request->getSession();

        // $this->env = $this->getParameter('app.env');
        // if ($this->env == 'prod' && $session->get('HTTP_LOGIN')) {
        //     $user->setUserId($session->get('HTTP_LOGIN'));
        //     $user->setRoles($session->get('HTTP_ROLES'));
        // }

        /**
         * @Todo: Pré-sélectonner les uniés selon la config de l'user
         */
        //$election = new Election();

        $form = $this->createForm(ElectionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $entityManager->persist($data);
            $entityManager->flush();
        }

        return $this->render('election/create.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
