<?php

namespace App\Service;

use App\DTO\VehicleCreateDTO;
use App\DTO\VehicleFilterDTO;
use App\DTO\VehicleListDTO;
use App\DTO\VehicleUpdateDTO;
use App\Entity\Car;
use App\Entity\Motorcycle;
use App\Entity\Trailer;
use App\Entity\Truck;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VehicleService
{
    public function __construct(
        private VehicleRepository $vehicleRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    public function getVehicles(VehicleFilterDTO $filterDTO): VehicleListDTO
    {
        $filters = $filterDTO->toArray();
        $vehicles = $this->vehicleRepository->findByFilters($filters, $filterDTO->page, $filterDTO->limit);
        $total = $this->vehicleRepository->countByFilters($filters);
        $totalPages = (int) ceil($total / $filterDTO->limit);

        return new VehicleListDTO($vehicles, $total, $filterDTO->page, $filterDTO->limit, $totalPages);
    }

    public function getVehicleById(int $id): ?Vehicle
    {
        return $this->vehicleRepository->find($id);
    }

    public function createVehicle(VehicleCreateDTO $dto, User $merchant): Vehicle
    {
        // Validate the DTO
        $violations = $this->validator->validate($dto, null, $dto->getValidationGroups());
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        $data = $dto->toArray();
        $vehicle = $this->createVehicleByType($data['type'], $data);
        $vehicle->setMerchant($merchant);
        $vehicle->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();

        return $vehicle;
    }

    public function updateVehicle(Vehicle $vehicle, VehicleUpdateDTO $dto): Vehicle
    {
        // Validate the DTO
        $violations = $this->validator->validate($dto, null, $dto->getValidationGroups());
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        $data = $dto->toArray();
        $vehicle->setBrand($data['brand']);
        $vehicle->setModel($data['model']);
        $vehicle->setEngineCapacity($data['engine_capacity']);
        $vehicle->setColour($data['colour']);
        $vehicle->setPrice($data['price']);
        $vehicle->setQuantity($data['quantity']);
        $vehicle->setUpdatedAt(new \DateTime());

        // Update specific attributes based on type
        $this->updateSpecificAttributes($vehicle, $data);

        $this->entityManager->flush();

        return $vehicle;
    }

    public function deleteVehicle(Vehicle $vehicle): void
    {
        $this->vehicleRepository->remove($vehicle, true);
    }

    public function followVehicle(Vehicle $vehicle, User $user): bool
    {
        if ($vehicle->isFollowedBy($user)) {
            return false;
        }

        $vehicle->addFollower($user);
        $this->entityManager->flush();

        return true;
    }

    public function unfollowVehicle(Vehicle $vehicle, User $user): bool
    {
        if (!$vehicle->isFollowedBy($user)) {
            return false;
        }

        $vehicle->removeFollower($user);
        $this->entityManager->flush();

        return true;
    }

    public function getFollowedVehicles(User $user): array
    {
        return $this->vehicleRepository->findFollowedByUser($user);
    }

    public function getMerchantVehicles(User $merchant): array
    {
        return $this->vehicleRepository->findByMerchant($merchant);
    }

    public function getFilterOptions(): array
    {
        return [
            'types' => ['car', 'motorcycle', 'truck', 'trailer'],
            'brands' => $this->vehicleRepository->findDistinctBrands(),
            'colours' => $this->vehicleRepository->findDistinctColours(),
            'models' => $this->vehicleRepository->findDistinctModels(),
        ];
    }

    private function createVehicleByType(string $type, array $data): Vehicle
    {
        return match ($type) {
            'motorcycle' => $this->createMotorcycle($data),
            'car' => $this->createCar($data),
            'truck' => $this->createTruck($data),
            'trailer' => $this->createTrailer($data),
            default => throw new \InvalidArgumentException("Invalid vehicle type: {$type}")
        };
    }

    private function createMotorcycle(array $data): Motorcycle
    {
        $motorcycle = new Motorcycle();
        $motorcycle->setBrand($data['brand']);
        $motorcycle->setModel($data['model']);
        $motorcycle->setEngineCapacity($data['engine_capacity']);
        $motorcycle->setColour($data['colour']);
        $motorcycle->setPrice($data['price']);
        $motorcycle->setQuantity($data['quantity']);

        return $motorcycle;
    }

    private function createCar(array $data): Car
    {
        $car = new Car();
        $car->setBrand($data['brand']);
        $car->setModel($data['model']);
        $car->setEngineCapacity($data['engine_capacity']);
        $car->setColour($data['colour']);
        $car->setPrice($data['price']);
        $car->setQuantity($data['quantity']);
        $car->setDoors($data['doors']);
        $car->setCategory($data['category']);

        return $car;
    }

    private function createTruck(array $data): Truck
    {
        $truck = new Truck();
        $truck->setBrand($data['brand']);
        $truck->setModel($data['model']);
        $truck->setEngineCapacity($data['engine_capacity']);
        $truck->setColour($data['colour']);
        $truck->setPrice($data['price']);
        $truck->setQuantity($data['quantity']);
        $truck->setBeds($data['beds']);

        return $truck;
    }

    private function createTrailer(array $data): Trailer
    {
        $trailer = new Trailer();
        $trailer->setBrand($data['brand']);
        $trailer->setModel($data['model']);
        $trailer->setColour($data['colour']);
        $trailer->setPrice($data['price']);
        $trailer->setQuantity($data['quantity']);
        $trailer->setLoadCapacityKg($data['load_capacity_kg']);
        $trailer->setAxles($data['axles']);

        return $trailer;
    }

    private function updateSpecificAttributes(Vehicle $vehicle, array $data): void
    {
        if ($vehicle instanceof Car) {
            $vehicle->setDoors($data['doors']);
            $vehicle->setCategory($data['category']);
        } elseif ($vehicle instanceof Truck) {
            $vehicle->setBeds($data['beds']);
        } elseif ($vehicle instanceof Trailer) {
            $vehicle->setLoadCapacityKg($data['load_capacity_kg']);
            $vehicle->setAxles($data['axles']);
        }
    }

    public function getFollowedVehicleIds(User $user): array
    {
        $followedVehicles = $user->getFollowedVehicles();
        $vehicleIds = [];
        
        foreach ($followedVehicles as $vehicle) {
            $vehicleIds[] = $vehicle->getId();
        }
        
        return $vehicleIds;
    }
}
