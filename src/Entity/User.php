<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    private ?string $userId = null;

    #[ORM\Column(length: 8)]
    private ?string $uniteId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $grade = null;

    #[ORM\ManyToOne]
    private ?Groupe $groupe = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUniteId(): ?string
    {
        return $this->uniteId;
    }

    public function setUniteId(string $uniteId): static
    {
        $this->uniteId = $uniteId;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(?string $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): static
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->userId;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return null; // Pas de mot de passe avec SSO
    }

    public function getSalt(): ?string
    {
        return null; // Pas de sel avec SSO
    }

    public function eraseCredentials(): void
    {
        // Rien à effacer dans ce cas
    }
}
