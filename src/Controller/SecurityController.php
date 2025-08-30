<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\SsoUser as User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Doctrine\Persistence\ManagerRegistry;


use App\Security\SsoService;
use App\Security\SsoServiceDEV;

class SecurityController extends AbstractController
{
    private $requestStack, $session, $env;
    public $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(#[CurrentUser] ?User $user, AuthenticationUtils $authenticationUtils, ManagerRegistry $doctrine): Response
    {
        $this->env = $this->getParameter('app.env');

        if ($this->env === 'prod') {

            $sso = new SsoService(true);
            $usr = $sso::user();

            // /* paramètres session */
            if (is_null($user))
                $user = new User();

            $roles = ['ROLE_USER'];
            // if ($usr->unite === 'SEL BSF COMGENDGP')
            //     $roles[] = 'ROLE_SEL';

            // if (in_array($usr->unite, ['SOLC SAJ COMGENDGP', 'DSOLC BAIE-MAHAULT', 'DSOLC ST-MARTIN']))
            //     $roles[] = 'ROLE_SIC';

            $this->session->set('HTTP_NIGEND', $usr->uid);
            $this->session->set('HTTP_NOM', $usr->displayname);
            $this->session->set('HTTP_UNITE', $usr->unite);
            $this->session->set('HTTP_CODEUNITE', $usr->codeunite);
            $this->session->set('HTTP_SPECIALITE', $usr->specialite);
            // $this->session->set('HTTP_ROLES', $roles);

        } elseif (is_null($user)) {

            $sso = new SsoServiceDEV();
            $usr = $sso::user();

            // if (is_null($user))
            //     $user = new User();

            // $roles = ['ROLE_USER'];
            // dd($roles);
            // if ($usr->unite === 'SEL BSF COMGENDGP')
            //     $roles[] = 'ROLE_SEL';

            // if (in_array($usr->unite, ['SOLC SAJ COMGENDGP', 'DSOLC BAIE-MAHAULT', 'DSOLC ST-MARTIN']))
            //     $roles[] = 'ROLE_SIC';

            $this->session->set('HTTP_NIGEND', $usr->uid);
            $this->session->set('HTTP_NOM', $usr->displayname);
            $this->session->set('HTTP_UNITE', $usr->unite);
            $this->session->set('HTTP_CODEUNITE', $usr->codeunite);
            $this->session->set('HTTP_SPECIALITE', $usr->specialite);

            // $this->session->set('HTTP_ROLES', $roles);
        }

        // // get the login error if there is one
        // $error = $authenticationUtils->getLastAuthenticationError();

        // // last username entered by the user
        // $lastUsername = $authenticationUtils->getLastUsername();

        return $this->redirectToRoute('app_index');

        // return $this->render('security/login.html.twig', [
        //     'last_username' => $lastUsername,
        //     'error' => $error,
        // ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
