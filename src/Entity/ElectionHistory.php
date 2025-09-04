<?php

namespace App\Entity;

use App\Repository\ElectionHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElectionHistoryRepository::class)]
class ElectionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'electionHistory', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Election $current = null;

    #[ORM\OneToOne(inversedBy: 'electionHistory', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Election $previous = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrent(): ?Election
    {
        return $this->current;
    }

    public function setCurrent(Election $current): static
    {
        $this->current = $current;

        return $this;
    }

    public function getPrevious(): ?Election
    {
        return $this->previous;
    }

    public function setPrevious(Election $previous): static
    {
        $this->previous = $previous;

        return $this;
    }
}
