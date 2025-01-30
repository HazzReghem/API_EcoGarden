<?php

namespace App\Entity;

use App\Repository\MeteoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MeteoRepository::class)]
class Meteo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\Column]
    private ?\DateTime $last_fetched_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getLastFetchedAt(): ?\DateTime
    {
        return $this->last_fetched_at;
    }

    public function setLastFetchedAt(\DateTime $last_fetched_at): static
    {
        $this->last_fetched_at = $last_fetched_at;

        return $this;
    }
}
