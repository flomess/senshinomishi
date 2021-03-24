<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository", repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"pseudo"}, message="There is already an account with this pseudo")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="date")
     */

    private $date_inscription;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $derniere_connexion;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_parties;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_victoires;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="user1")
     */
    private $games1;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="user2")
     */
    private $games2;

    /**
     * @ORM\Column(type="json")
     */
    private $role;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    public function __construct()
    {
        $this->games1 = new ArrayCollection();
        $this->games2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getDerniereConnexion(): ?\DateTimeInterface
    {
        return $this->derniere_connexion;
    }

    public function setDerniereConnexion(?\DateTimeInterface $derniere_connexion): self
    {
        $this->derniere_connexion = $derniere_connexion;

        return $this;
    }

    public function getNbParties(): ?int
    {
        return $this->nb_parties;
    }

    public function setNbParties(int $nb_parties): self
    {
        $this->nb_parties = $nb_parties;

        return $this;
    }

    public function getNbVictoires(): ?int
    {
        return $this->nb_victoires;
    }

    public function setNbVictoires(int $nb_victoires): self
    {
        $this->nb_victoires = $nb_victoires;

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames1(): Collection
    {
        return $this->games1;
    }

    public function addGames1(Game $games1): self
    {
        if (!$this->games1->contains($games1)) {
            $this->games1[] = $games1;
            $games1->setUser1($this);
        }

        return $this;
    }

    public function removeGames1(Game $games1): self
    {
        if ($this->games1->removeElement($games1)) {
            // set the owning side to null (unless already changed)
            if ($games1->getUser1() === $this) {
                $games1->setUser1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames2(): Collection
    {
        return $this->games2;
    }

    public function addGames2(Game $games2): self
    {
        if (!$this->games2->contains($games2)) {
            $this->games2[] = $games2;
            $games2->setUser2($this);
        }

        return $this;
    }

    public function removeGames2(Game $games2): self
    {
        if ($this->games2->removeElement($games2)) {
            // set the owning side to null (unless already changed)
            if ($games2->getUser2() === $this) {
                $games2->setUser2(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_JOUEUR';

        return array_unique($roles);
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
