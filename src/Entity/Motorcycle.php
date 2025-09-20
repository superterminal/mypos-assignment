<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Motorcycle extends Vehicle
{
    public function getSpecificAttributes(): array
    {
        return [
            'engine_capacity' => $this->getEngineCapacity(),
            'colour' => $this->getColour(),
        ];
    }

    public function getType(): string
    {
        return 'motorcycle';
    }
}
