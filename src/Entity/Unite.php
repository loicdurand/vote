<?php

namespace App\Entity;

use App\Repository\UniteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UniteRepository::class)]
class Unite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, Election>
     */
    #[ORM\OneToMany(targetEntity: Election::class, mappedBy: 'unite')]
    private Collection $elections;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'unite')]
    private Collection $userRoles;

    #[ORM\Column]
    private ?int $codeunite = null;

    public function __construct()
    {
        $this->elections = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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
            $election->setUnite($this);
        }

        return $this;
    }

    public function removeElection(Election $election): static
    {
        if ($this->elections->removeElement($election)) {
            // set the owning side to null (unless already changed)
            if ($election->getUnite() === $this) {
                $election->setUnite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUnite($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): static
    {
        if ($this->userRoles->removeElement($userRole)) {
            // set the owning side to null (unless already changed)
            if ($userRole->getUnite() === $this) {
                $userRole->setUnite(null);
            }
        }

        return $this;
    }

    public function getCodeunite(): ?int
    {
        return $this->codeunite;
    }

    public function setCodeunite(int $codeunite): static
    {
        $this->codeunite = $codeunite;

        return $this;
    }
}
