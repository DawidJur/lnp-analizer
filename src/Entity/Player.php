<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player implements PageLinkInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private ?string $firstName;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private ?string $lastName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $link;

    /**
     * @ORM\Column(type="integer")
     */
    private int $age = 0;

    /**
     * @ORM\ManyToMany(targetEntity=Team::class, inversedBy="players")
     */
    private Collection $teams;

    /**
     * @ORM\OneToMany(targetEntity=PlayerStatistics::class, mappedBy="player", orphanRemoval=true)
     */
    private Collection $playerStatistics;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->playerStatistics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    /**
     * @return Collection|PlayerStatistics[]
     */
    public function getPlayerStatistics(): Collection
    {
        return $this->playerStatistics;
    }

    public function addPlayerStatistic(PlayerStatistics $playerStatistic): self
    {
        if (!$this->playerStatistics->contains($playerStatistic)) {
            $this->playerStatistics[] = $playerStatistic;
            $playerStatistic->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerStatistic(PlayerStatistics $playerStatistic): self
    {
        if ($this->playerStatistics->contains($playerStatistic)) {
            $this->playerStatistics->removeElement($playerStatistic);
            // set the owning side to null (unless already changed)
            if ($playerStatistic->getPlayer() === $this) {
                $playerStatistic->setPlayer(null);
            }
        }

        return $this;
    }

    public function removeAllPlayerStatistics(): self
    {
        $this->playerStatistics = new ArrayCollection();

        return $this;
    }
}
