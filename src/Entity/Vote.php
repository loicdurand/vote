<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::TEXT)]
    private ?string $verification_hash = null;

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

    public function getVerificationHash(): ?string
    {
        return $this->verification_hash;
    }

    public function setVerificationHash(string $verification_hash): static
    {
        $this->verification_hash = $verification_hash;

        return $this;
    }
}
