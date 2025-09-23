<?php

namespace App\Service;

use App\Entity\Vehicle;
use App\Entity\User;

class VehicleSerializer
{
    public function __construct(
        private VehicleService $vehicleService
    ) {
    }
    public function serializeVehicle(Vehicle $vehicle, ?User $currentUser = null): array
    {
        $data = [
            'id' => $vehicle->getId(),
            'type' => $vehicle->getType(),
            'brand' => $vehicle->getBrand(),
            'model' => $vehicle->getModel(),
            'colour' => $vehicle->getColour(),
            'price' => $vehicle->getPrice(),
            'quantity' => $vehicle->getQuantity(),
            'engineCapacity' => $vehicle->getEngineCapacity(),
            'displayName' => $vehicle->getDisplayName(),
        ];

        // Add merchant information if available
        try {
            $merchant = $vehicle->getMerchant();
            if ($merchant) {
                $data['merchant'] = [
                    'id' => $merchant->getId(),
                    'fullName' => $merchant->getFullName(),
                    'email' => $merchant->getEmail()
                ];
            }
        } catch (\Exception $e) {
            $data['merchant'] = null;
        }

        // Add type-specific attributes
        $this->addTypeSpecificAttributes($data, $vehicle);

        // Add follow status for buyers
        if ($currentUser && $currentUser->isBuyer()) {
            $data['isFollowed'] = $this->isVehicleFollowed($vehicle, $currentUser);
        } else {
            $data['isFollowed'] = false;
        }

        return $data;
    }

    public function serializeVehicleList(array $vehicles, ?User $currentUser = null): array
    {
        $vehicleData = [];
        $followedVehicleIds = [];

        // Get followed vehicles if user is logged in and is a buyer
        if ($currentUser && $currentUser->isBuyer()) {
            try {
                $followedVehicles = $this->vehicleService->getFollowedVehicles($currentUser);
                $followedVehicleIds = array_map(fn($v) => $v->getId(), $followedVehicles);
            } catch (\Exception $e) {
                $followedVehicleIds = [];
            }
        }

        foreach ($vehicles as $vehicle) {
            try {
                $data = $this->serializeVehicle($vehicle, $currentUser);
                $data['isFollowed'] = in_array($vehicle->getId(), $followedVehicleIds);
                $vehicleData[] = $data;
            } catch (\Exception $e) {
                // Skip this vehicle if there's an error processing it
                continue;
            }
        }

        return $vehicleData;
    }

    private function addTypeSpecificAttributes(array &$data, Vehicle $vehicle): void
    {
        switch ($vehicle->getType()) {
            case 'car':
                if ($vehicle instanceof \App\Entity\Car) {
                    $data['doors'] = $vehicle->getDoors();
                    $data['category'] = $vehicle->getCategory();
                }
                break;
            case 'truck':
                if ($vehicle instanceof \App\Entity\Truck) {
                    $data['beds'] = $vehicle->getBeds();
                }
                break;
            case 'trailer':
                if ($vehicle instanceof \App\Entity\Trailer) {
                    $data['loadCapacityKg'] = $vehicle->getLoadCapacityKg();
                    $data['axles'] = $vehicle->getAxles();
                }
                break;
        }
    }

    private function isVehicleFollowed(Vehicle $vehicle, User $user): bool
    {
        try {
            $followedVehicles = $this->vehicleService->getFollowedVehicles($user);
            $followedVehicleIds = array_map(fn($v) => $v->getId(), $followedVehicles);
            return in_array($vehicle->getId(), $followedVehicleIds);
        } catch (\Exception $e) {
            return false;
        }
    }
}
