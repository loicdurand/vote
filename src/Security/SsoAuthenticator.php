<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Unite;
use App\Entity\Groupe;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Security\SsoService;
use App\Security\SsoServiceDEV;

class SsoAuthenticator extends AbstractAuthenticator
{
    private $entityManager;
    private $urlGenerator;
    private $parameterBag;

    private $sso;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->parameterBag = $parameterBag;

        $env = $this->parameterBag->get('app.env');
        if ($env === 'prod')
            $this->sso = new SsoService();
        else
            $this->sso = new SsoServiceDEV();
    }

    public function supports(Request $request): ?bool
    {
        // Détermine si cet authenticator doit être utilisé (ex. : sur une route spécifique)
        return $request->getPathInfo() === '/login';
    }

    public function authenticate(Request $request): Passport
    {
        if (!isset($_COOKIE[$this->sso::COOKIE_NAME])) {
            $this->sso::redirect();
        }

        // Récupère le token SSO
        $ssoToken = $_COOKIE[$this->sso::COOKIE_NAME];

        // Simule une requête au mock SSO pour récupérer les infos utilisateur
        $ssoData = $this->fetchSsoUserData($ssoToken); // Implémente cette méthode selon ton SSO

        if (!$ssoData) {
            throw new AuthenticationException('Invalid SSO token');
        }

        // Cherche ou crée l'unité dans la base
        $codeunite = $ssoData->codeunite;
        $unite = $this->entityManager->getRepository(Unite::class)->findOneBy(['codeunite' => $codeunite]);
        if (!$unite) {
            $unite = new Unite();
            $unite->setCodeunite($codeunite);
            $unite->setName($ssoData->unite);
            $this->entityManager->persist($unite);
            $this->entityManager->flush();
        }

        // Cherche ou crée l'utilisateur dans la base
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['userId' => $ssoData->nigend]);

        if (!$user) {

            $user = new User();
            $user->setUserId($ssoData->nigend);
            $user->setUniteId($codeunite);
            $user->setGrade($ssoData->title);
            $user->setTitle($ssoData->displayname);
            $user->setSpecialite($ssoData->specialite);
            $grp_shortname = $ssoData->employeeType;
            $groupe = $this->entityManager->getRepository(Groupe::class)->findOneBy(['shortName' => $grp_shortname]);
            $unite = $this->entityManager->getRepository(Unite::class)->findOneBy(['codeunite' => $codeunite]);
            $user->setUnite($unite);
            $user->setGroupe($groupe);
            $user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), fn() => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirige vers une page après authentification réussie
        return new RedirectResponse($this->urlGenerator->generate('app_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Redirige ou affiche une erreur en cas d'échec
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    private function fetchSsoUserData(?string $ssoToken): ?object
    {
        $usr = $this->sso::user();
        return $usr;
    }
}
