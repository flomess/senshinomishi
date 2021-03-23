<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="games1")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user1;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="games2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user2;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_crated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_ended;

    /**
     * @ORM\Column(type="boolean")
     */
    private $statut;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $winner;

    /**
     * @ORM\OneToMany(targetEntity=Round::class, mappedBy="game")
     */
    private $rounds;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?User
    {
        return $this->user1;
    }

    public function setUser1(?User $user1): self
    {
        $this->user1 = $user1;

        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): self
    {
        $this->user2 = $user2;

        return $this;
    }

    public function getDateCrated(): ?\DateTimeInterface
    {
        return $this->date_crated;
    }

    public function setDateCrated(\DateTimeInterface $date_crated): self
    {
        $this->date_crated = $date_crated;

        return $this;
    }

    public function getDateEnded(): ?\DateTimeInterface
    {
        return $this->date_ended;
    }

    public function setDateEnded(?\DateTimeInterface $date_ended): self
    {
        $this->date_ended = $date_ended;

        return $this;
    }

    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * @return Collection|Round[]
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): self
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds[] = $round;
            $round->setGame($this);
        }

        return $this;
    }

    public function removeRound(Round $round): self
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getGame() === $this) {
                $round->setGame(null);
            }
        }

        return $this;
    }
}
