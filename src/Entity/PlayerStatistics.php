<?php

namespace App\Entity;

use App\Repository\PlayerStatisticsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(name="player_statistics",
 *    uniqueConstraints={
 *        @UniqueConstraint(name="players_statistic",
 *            columns={"type", "date", "player_id"})
 *    }
 * )
 * @ORM\Entity(repositoryClass=PlayerStatisticsRepository::class)
 */
class PlayerStatistics
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $type;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $value;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="playerStatistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Player $player;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private ?string $season;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $date;

    /**
     * @ORM\ManyToOne(targetEntity=League::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?League $league;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(string $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLeague(): ?League
    {
        return $this->league;
    }

    public function setLeague(?League $league): self
    {
        $this->league = $league;

        return $this;
    }
}
