<?php

namespace App\Entity;

use App\Repository\RoundRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoundRepository::class)
 */
class Round
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_ended;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class, inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\Column(type="array")
     */
    private $user1_cards = [];

    /**
     * @ORM\Column(type="array")
     */
    private $user2_cards = [];

    /**
     * @ORM\Column(type="array")
     */
    private $board = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $removed_card;

    /**
     * @ORM\Column(type="array")
     */
    private $user1_action = [];

    /**
     * @ORM\Column(type="array")
     */
    private $user2_action = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $round_number;

    /**
     * @ORM\Column(type="array")
     */
    private $user1_board = [];

    /**
     * @ORM\Column(type="array")
     */
    private $user2_board = [];

    /**
     * @ORM\Column(type="array")
     */
    private $stack = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getUser1Cards(): ?array
    {
        return $this->user1_cards;
    }

    public function setUser1Cards(array $user1_cards): self
    {
        $this->user1_cards = $user1_cards;

        return $this;
    }

    public function getUser2Cards(): ?array
    {
        return $this->user2_cards;
    }

    public function setUser2Cards(array $user2_cards): self
    {
        $this->user2_cards = $user2_cards;

        return $this;
    }

    public function getBoard(): ?array
    {
        return $this->board;
    }

    public function setBoard(array $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getRemovedCard(): ?int
    {
        return $this->removed_card;
    }

    public function setRemovedCard(int $removed_card): self
    {
        $this->removed_card = $removed_card;

        return $this;
    }

    public function getUser1Action(): ?array
    {
        return $this->user1_action;
    }

    public function setUser1Action(array $user1_action): self
    {
        $this->user1_action = $user1_action;

        return $this;
    }

    public function getUser2Action(): ?array
    {
        return $this->user2_action;
    }

    public function setUser2Action(array $user2_action): self
    {
        $this->user2_action = $user2_action;

        return $this;
    }

    public function getRoundNumber(): ?int
    {
        return $this->round_number;
    }

    public function setRoundNumber(int $round_number): self
    {
        $this->round_number = $round_number;

        return $this;
    }

    public function getUser1Board(): ?array
    {
        return $this->user1_board;
    }

    public function setUser1Board(array $user1_board): self
    {
        $this->user1_board = $user1_board;

        return $this;
    }

    public function getUser2Board(): ?array
    {
        return $this->user2_board;
    }

    public function setUser2Board(array $user2_board): self
    {
        $this->user2_board = $user2_board;

        return $this;
    }

    public function getStack(): ?array
    {
        return $this->stack;
    }

    public function setStack(array $stack): self
    {
        $this->stack = $stack;

        return $this;
    }
}
