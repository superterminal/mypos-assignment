<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Trailer extends Vehicle
{
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $loadCapacityKg = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $axles = null;

    public function getLoadCapacityKg(): ?string
    {
        return $this->loadCapacityKg;
    }

    public function setLoadCapacityKg(string $loadCapacityKg): static
    {
        $this->loadCapacityKg = $loadCapacityKg;

        return $this;
    }

    public function getAxles(): ?int
    {
        return $this->axles;
    }

    public function setAxles(int $axles): static
    {
        $this->axles = $axles;

        return $this;
    }

    public function getSpecificAttributes(): array
    {
        return [
            'load_capacity_kg' => $this->getLoadCapacityKg(),
            'axles' => $this->getAxles(),
        ];
    }

    public function getType(): string
    {
        return 'trailer';
    }
}
