<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?Election $election = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?Candidat $candidat = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $userId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $receiptCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $votedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getElection(): ?Election
    {
        return $this->election;
    }

    public function setElection(?Election $election): static
    {
        $this->election = $election;

        return $this;
    }

    public function getCandidat(): ?Candidat
    {
        return $this->candidat;
    }

    public function setCandidat(?Candidat $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getReceiptCode(): ?string
    {
        return $this->receiptCode;
    }

    public function setReceiptCode(?string $receiptCode): static
    {
        $this->receiptCode = $receiptCode;

        return $this;
    }

    public function getVotedAt(): ?\DateTime
    {
        return $this->votedAt;
    }

    public function setVotedAt(?\DateTime $votedAt): static
    {
        $this->votedAt = $votedAt;

        return $this;
    }
}
