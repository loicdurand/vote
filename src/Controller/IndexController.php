<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\Categorie;

final class IndexController extends AbstractController
{
    private $env;

    #[Route('/', name: 'app_index')]
    public function index(#[CurrentUser] ?User $user, Request $request, ManagerRegistry $doctrine): Response
    {
        if (is_null($user)) {
            $user = new User();
        }
        $session = $request->getSession();
        $this->env = 'dev'; //$this->getParameter('app.env');
        if ($this->env === 'prod' &&  $session->get('HTTP_LOGIN')) {
            $user->setLogin($session->get('HTTP_LOGIN'));
            $user->setRoles($session->get('HTTP_ROLES'));
        }

        return $this->render('index/index.html.twig', [
            "user" => $user
        ]);
    }
}
