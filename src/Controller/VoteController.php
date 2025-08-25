<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Election;
use App\Form\VoteType; // Make:form
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class VoteController extends AbstractController
{
    #[Route('/election/{id}/vote', name: 'vote')]
    public function vote(Request $request, Election $election, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser(); // SsoUser
        if (!$election->isOpen() || $user->getRestaurant() != $election->getRestaurant()) { // Check user resto
            throw $this->createAccessDeniedException();
        }

        // Check if already voted
        $existingVote = $em->getRepository(Vote::class)->findOneBy(['election' => $election, 'userId' => $user->getUserId()]);
        if ($existingVote) {
            return $this->redirectToRoute('vote_receipt', ['code' => $existingVote->getReceiptCode()]);
        }

        // Form: select candidat from same group
        $form = $this->createForm(VoteType::class, null, ['group' => $user->getGroup()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vote = new Vote();
            $vote->setElection($election);
            $vote->setCandidat($form->get('candidat')->getData());
            $vote->setUserId($user->getUserId());
            $em->persist($vote);
            $em->flush();

            // Show receipt
            return $this->redirectToRoute('vote_receipt', ['code' => $vote->getReceiptCode()]);
        }

        return $this->render('vote/index.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/receipt/{code}', name: 'vote_receipt')]
    public function receipt(string $code, EntityManagerInterface $em): Response
    {
        $vote = $em->getRepository(Vote::class)->findOneBy(['receiptCode' => $code]);
        if (!$vote) {
            throw $this->createNotFoundException();
        }

        // Show vote details (candidat name) without user info
        return $this->render('vote/receipt.html.twig', ['vote' => $vote]);
    }
}