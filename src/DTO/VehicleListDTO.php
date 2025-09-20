<?php

namespace App\DTO;

use App\Entity\Vehicle;

class VehicleListDTO
{
    /**
     * @param Vehicle[] $vehicles
     */
    public function __construct(
        public readonly array $vehicles,
        public readonly int $total,
        public readonly int $page,
        public readonly int $limit,
        public readonly int $totalPages
    ) {
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->page + 1 : null;
    }

    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->page - 1 : null;
    }
}
