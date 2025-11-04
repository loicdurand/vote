<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupeRepository::class)]
class Groupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, Candidat>
     */
    #[ORM\OneToMany(targetEntity: Candidat::class, mappedBy: 'groupe')]
    private Collection $candidats;

    /**
     * @var Collection<int, Election>
     */
    #[ORM\ManyToMany(targetEntity: Election::class, mappedBy: 'groupes_concernes')]
    private Collection $elections;

    #[ORM\Column(length: 10)]
    private ?string $shortName = null;

    #[ORM\Column(length: 11, nullable: true)]
    private ?string $nickname = null;

    public function __construct()
    {
        $this->candidats = new ArrayCollection();
        $this->elections = new ArrayCollection();
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
     * @return Collection<int, Candidat>
     */
    public function getCandidats(): Collection
    {
        return $this->candidats;
    }

    public function addCandidat(Candidat $candidat): static
    {
        if (!$this->candidats->contains($candidat)) {
            $this->candidats->add($candidat);
            $candidat->setGroupe($this);
        }

        return $this;
    }

    public function removeCandidat(Candidat $candidat): static
    {
        if ($this->candidats->removeElement($candidat)) {
            // set the owning side to null (unless already changed)
            if ($candidat->getGroupe() === $this) {
                $candidat->setGroupe(null);
            }
        }

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
            $election->addGroupesConcerne($this);
        }

        return $this;
    }

    public function removeElection(Election $election): static
    {
        if ($this->elections->removeElement($election)) {
            $election->removeGroupesConcerne($this);
        }

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }
}