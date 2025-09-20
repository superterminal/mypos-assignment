<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class VehicleFilterDTO
{
    #[Assert\Choice(choices: ['motorcycle', 'car', 'truck', 'trailer'])]
    public ?string $type = null;

    #[Assert\Length(max: 255)]
    public ?string $brand = null;

    #[Assert\Length(max: 255)]
    public ?string $model = null;

    #[Assert\Length(max: 50)]
    public ?string $colour = null;

    #[Assert\PositiveOrZero]
    public ?float $priceMin = null;

    #[Assert\PositiveOrZero]
    public ?float $priceMax = null;

    #[Assert\Positive]
    public int $page = 1;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 10;

    public function toArray(): array
    {
        $filters = [];

        if ($this->type) {
            $filters['type'] = $this->type;
        }

        if ($this->brand) {
            $filters['brand'] = $this->brand;
        }

        if ($this->model) {
            $filters['model'] = $this->model;
        }

        if ($this->colour) {
            $filters['colour'] = $this->colour;
        }

        if ($this->priceMin !== null) {
            $filters['price_min'] = $this->priceMin;
        }

        if ($this->priceMax !== null) {
            $filters['price_max'] = $this->priceMax;
        }

        return $filters;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
