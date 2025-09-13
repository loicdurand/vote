<?php

namespace App\Controller;

use App\Form\UserForm;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'user_registration')]
    public function registerAction(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher)
    {

        $user = new User();
        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $entityManager = $doctrine->getManager();
            $exists = $entityManager->getRepository(User::class)->findOneBy(['login' => $user->getLogin()]);

            if ($form->isValid() or $exists) {

                $password = $hasher->hashPassword($user, $user->getPassword());

                if ($exists) {
                    $exists->setPassword($password);
                } else {
                    $user->setPassword($password);
                    $entityManager->persist($user);
                }
                $entityManager->flush();

                return $this->redirectToRoute('app_index');
            }
        }

        return $this->render(
            'registration/register.html.twig',
            [
                "user" => $user,
                "form" => $form
            ]
        );
    }
}
