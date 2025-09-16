<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $grade = null;

    #[ORM\ManyToOne]
    private ?Groupe $groupe = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Unite $unite = null;

    /**
     * @var Collection<int, Election>
     */
    #[ORM\OneToMany(targetEntity: Election::class, mappedBy: 'user')]
    private Collection $elections;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    /**
     * @var Collection<int, Registre>
     */
    #[ORM\OneToMany(targetEntity: Registre::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $registres;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $departement = null;

    public function __construct()
    {
        $this->elections = new ArrayCollection();
        $this->registres = new ArrayCollection();
    }

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

    public function getUnite(): ?Unite
    {
        return $this->unite;
    }

    public function setUnite(?Unite $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    /**
     * @return Collection<int, Election>
     */
    public function getElections(): Collection
    {
        return $this->elections;
    }

    public function addElection(Election $election): static
    {
        if (!$this->elections->contains($election)) {
            $this->elections->add($election);
            $election->setUser($this);
        }

        return $this;
    }

    public function removeElection(Election $election): static
    {
        if ($this->elections->removeElement($election)) {
            // set the owning side to null (unless already changed)
            if ($election->getUser() === $this) {
                $election->setUser(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return Collection<int, Registre>
     */
    public function getRegistres(): Collection
    {
        return $this->registres;
    }

    public function addRegistre(Registre $registre): static
    {
        if (!$this->registres->contains($registre)) {
            $this->registres->add($registre);
            $registre->setUser($this);
        }

        return $this;
    }

    public function removeRegistre(Registre $registre): static
    {
        if ($this->registres->removeElement($registre)) {
            // set the owning side to null (unless already changed)
            if ($registre->getUser() === $this) {
                $registre->setUser(null);
            }
        }

        return $this;
    }

    public function getDepartement(): ?int
    {
        return $this->departement;
    }

    public function setDepartement(?int $departement): static
    {
        $this->departement = $departement;

        return $this;
    }
}
