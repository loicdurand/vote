<?php

namespace App\Entity;

use App\Repository\RegistreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistreRepository::class)]
class Registre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'registres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Election $election = null;

    #[ORM\ManyToOne(inversedBy: 'registres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTime $votedAt = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getVotedAt(): ?\DateTime
    {
        return $this->votedAt;
    }

    public function setVotedAt(\DateTime $votedAt): static
    {
        $this->votedAt = $votedAt;

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
