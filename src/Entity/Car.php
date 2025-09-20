<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Car extends Vehicle
{
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 2, max: 5)]
    private ?int $doors = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $category = null;

    public function getDoors(): ?int
    {
        return $this->doors;
    }

    public function setDoors(int $doors): static
    {
        $this->doors = $doors;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSpecificAttributes(): array
    {
        return [
            'engine_capacity' => $this->getEngineCapacity(),
            'colour' => $this->getColour(),
            'doors' => $this->getDoors(),
            'category' => $this->getCategory(),
        ];
    }

    public function getType(): string
    {
        return 'car';
    }
}
