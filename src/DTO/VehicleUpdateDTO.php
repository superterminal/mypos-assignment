<?php

namespace App\DTO;

use App\Entity\Vehicle;
use Symfony\Component\Validator\Constraints as Assert;

class VehicleUpdateDTO
{
    #[Assert\NotBlank(message: 'Brand is required')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Brand must be at least {{ limit }} characters', maxMessage: 'Brand cannot exceed {{ limit }} characters')]
    public string $brand = '';

    #[Assert\NotBlank(message: 'Model is required')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Model must be at least {{ limit }} characters', maxMessage: 'Model cannot exceed {{ limit }} characters')]
    public string $model = '';

    #[Assert\NotBlank(message: 'Colour is required')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Colour must be at least {{ limit }} characters', maxMessage: 'Colour cannot exceed {{ limit }} characters')]
    public string $colour = '';

    #[Assert\NotBlank(message: 'Price is required')]
    #[Assert\Positive(message: 'Price must be a positive number')]
    #[Assert\Type(type: 'numeric', message: 'Price must be a valid number')]
    public string $price = '';

    #[Assert\NotBlank(message: 'Quantity is required')]
    #[Assert\PositiveOrZero(message: 'Quantity must be zero or positive')]
    #[Assert\Type(type: 'integer', message: 'Quantity must be a valid integer')]
    public int $quantity = 0;

    // Car-specific fields
    #[Assert\Type(type: 'integer', message: 'Doors must be a valid integer', groups: ['car'])]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Doors must be between {{ min }} and {{ max }}', groups: ['car'])]
    #[Assert\NotBlank(message: 'Doors is required for cars', groups: ['car'])]
    public ?int $doors = null;

    #[Assert\Length(max: 50, maxMessage: 'Category cannot exceed {{ limit }} characters', groups: ['car'])]
    #[Assert\NotBlank(message: 'Category is required for cars', groups: ['car'])]
    public ?string $category = null;

    // Truck-specific fields
    #[Assert\Type(type: 'integer', message: 'Beds must be a valid integer', groups: ['truck'])]
    #[Assert\Positive(message: 'Beds must be a positive number', groups: ['truck'])]
    #[Assert\NotBlank(message: 'Beds is required for trucks', groups: ['truck'])]
    public ?int $beds = null;

    // Trailer-specific fields
    #[Assert\Type(type: 'numeric', message: 'Load capacity must be a valid number', groups: ['trailer'])]
    #[Assert\Positive(message: 'Load capacity must be a positive number', groups: ['trailer'])]
    #[Assert\NotBlank(message: 'Load capacity is required for trailers', groups: ['trailer'])]
    public ?string $loadCapacityKg = null;

    #[Assert\Type(type: 'integer', message: 'Axles must be a valid integer', groups: ['trailer'])]
    #[Assert\Positive(message: 'Axles must be a positive number', groups: ['trailer'])]
    #[Assert\NotBlank(message: 'Axles is required for trailers', groups: ['trailer'])]
    public ?int $axles = null;

    // Engine capacity (common field)
    #[Assert\Type(type: 'numeric', message: 'Engine capacity must be a valid number')]
    #[Assert\Positive(message: 'Engine capacity must be a positive number')]
    public ?string $engineCapacity = null;

    private string $vehicleType;

    public function __construct(string $vehicleType)
    {
        $this->vehicleType = $vehicleType;
    }

    public function getValidationGroups(): array
    {
        $groups = ['Default'];
        
        switch ($this->vehicleType) {
            case 'car':
                $groups[] = 'car';
                break;
            case 'truck':
                $groups[] = 'truck';
                break;
            case 'trailer':
                $groups[] = 'trailer';
                break;
        }
        
        return $groups;
    }

    public function toArray(): array
    {
        $data = [
            'brand' => $this->brand,
            'model' => $this->model,
            'colour' => $this->colour,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'engine_capacity' => $this->engineCapacity,
        ];

        // Add type-specific fields
        switch ($this->vehicleType) {
            case 'car':
                if ($this->doors !== null) $data['doors'] = $this->doors;
                if ($this->category !== null) $data['category'] = $this->category;
                break;
            case 'truck':
                if ($this->beds !== null) $data['beds'] = $this->beds;
                break;
            case 'trailer':
                if ($this->loadCapacityKg !== null) $data['load_capacity_kg'] = $this->loadCapacityKg;
                if ($this->axles !== null) $data['axles'] = $this->axles;
                break;
        }

        return $data;
    }

    public static function fromVehicle(Vehicle $vehicle): self
    {
        $dto = new self($vehicle->getType());
        
        $dto->brand = $vehicle->getBrand() ?? '';
        $dto->model = $vehicle->getModel() ?? '';
        $dto->colour = $vehicle->getColour() ?? '';
        $dto->price = $vehicle->getPrice() ?? '';
        $dto->quantity = $vehicle->getQuantity() ?? 0;
        $dto->engineCapacity = $vehicle->getEngineCapacity();

        // Set type-specific fields using proper type checking
        switch ($vehicle->getType()) {
            case 'car':
                if ($vehicle instanceof \App\Entity\Car) {
                    $dto->doors = $vehicle->getDoors();
                    $dto->category = $vehicle->getCategory();
                }
                break;
            case 'truck':
                if ($vehicle instanceof \App\Entity\Truck) {
                    $dto->beds = $vehicle->getBeds();
                }
                break;
            case 'trailer':
                if ($vehicle instanceof \App\Entity\Trailer) {
                    $dto->loadCapacityKg = $vehicle->getLoadCapacityKg();
                    $dto->axles = $vehicle->getAxles();
                }
                break;
        }

        return $dto;
    }
}
