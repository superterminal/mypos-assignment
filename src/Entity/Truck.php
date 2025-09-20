<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Truck extends Vehicle
{
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $beds = null;

    public function getBeds(): ?int
    {
        return $this->beds;
    }

    public function setBeds(int $beds): static
    {
        $this->beds = $beds;

        return $this;
    }

    public function getSpecificAttributes(): array
    {
        return [
            'engine_capacity' => $this->getEngineCapacity(),
            'colour' => $this->getColour(),
            'beds' => $this->getBeds(),
        ];
    }

    public function getType(): string
    {
        return 'truck';
    }
}
