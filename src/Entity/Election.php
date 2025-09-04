<?php

namespace App\Entity;

use App\Repository\ElectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElectionRepository::class)]
class Election
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'elections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'elections')]
    private ?Unite $unite = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $endDate = null;

    /**
     * @var Collection<int, Candidat>
     */
    #[ORM\OneToMany(targetEntity: Candidat::class, mappedBy: 'election')]
    private Collection $candidats;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'election')]
    private Collection $votes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $explaination = null;

    /**
     * @var Collection<int, Groupe>
     */
    #[ORM\ManyToMany(targetEntity: Groupe::class, inversedBy: 'elections')]
    private Collection $groupesConcernes;

    /**
     * @var Collection<int, Unite>
     */
    #[ORM\ManyToMany(targetEntity: Unite::class)]
    private Collection $unitesConcernees;

    #[ORM\Column]
    private ?bool $isCancelled = false;

    #[ORM\OneToOne(mappedBy: 'current', cascade: ['persist', 'remove'])]
    private ?ElectionHistory $electionHistory = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $deletedAt = null;

    public function __construct()
    {
        $this->candidats = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->groupesConcernes = new ArrayCollection();
        $this->unitesConcernees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

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
            $candidat->setElection($this);
        }

        return $this;
    }

    public function removeCandidat(Candidat $candidat): static
    {
        if ($this->candidats->removeElement($candidat)) {
            // set the owning side to null (unless already changed)
            if ($candidat->getElection() === $this) {
                $candidat->setElection(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setElection($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getElection() === $this) {
                $vote->setElection(null);
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

    public function getExplaination(): ?string
    {
        return $this->explaination;
    }

    public function setExplaination(string $explaination): static
    {
        $this->explaination = $explaination;

        return $this;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupesConcernes(): Collection
    {
        return $this->groupesConcernes;
    }

    public function addGroupesConcerne(Groupe $groupesConcerne): static
    {
        if (!$this->groupesConcernes->contains($groupesConcerne)) {
            $this->groupesConcernes->add($groupesConcerne);
        }

        return $this;
    }

    public function removeGroupesConcerne(Groupe $groupesConcerne): static
    {
        $this->groupesConcernes->removeElement($groupesConcerne);

        return $this;
    }

    /**
     * @return Collection<int, Unite>
     */
    public function getUnitesConcernees(): Collection
    {
        return $this->unitesConcernees;
    }

    public function addUnitesConcernee(Unite $unitesConcernee): static
    {
        if (!$this->unitesConcernees->contains($unitesConcernee)) {
            $this->unitesConcernees->add($unitesConcernee);
        }

        return $this;
    }

    public function removeUnitesConcernee(Unite $unitesConcernee): static
    {
        $this->unitesConcernees->removeElement($unitesConcernee);

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

    public function isNotOpenYet(): bool
    {
        $now = new \DateTime("now");
        return $now < $this->startDate;
    }

    public function isOpen(): bool
    {
        $now = new \DateTime("now");
        return $this->startDate < $now && $now < $this->endDate;
    }

    public function isClosed(): bool
    {
        $now = new \DateTime("now");
        return $this->startDate < $now;
    }

    public function isCancelled(): ?bool
    {
        return $this->isCancelled;
    }

    public function setIsCancelled(?bool $isCancelled = false): static
    {
        $this->isCancelled = $isCancelled;

        return $this;
    }

    public function getElectionHistory(): ?ElectionHistory
    {
        return $this->electionHistory;
    }

    public function setElectionHistory(ElectionHistory $electionHistory): static
    {
        // set the owning side of the relation if necessary
        if ($electionHistory->getCurrent() !== $this) {
            $electionHistory->setCurrent($this);
        }

        $this->electionHistory = $electionHistory;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): static
    {
        $this->createdAt = new \Datetime('now');

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(): static
    {
        $this->deletedAt = new \Datetime('now');

        return $this;
    }
}
