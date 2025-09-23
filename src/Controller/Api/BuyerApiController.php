<?php

namespace App\Controller\Api;

use App\Service\VehicleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class BuyerApiController extends AbstractController
{
    public function __construct(
        private VehicleService $vehicleService
    ) {
    }

    #[Route('/buyer/followed-vehicles', name: 'app_api_buyer_followed_vehicles', methods: ['GET'])]
    #[IsGranted('ROLE_BUYER')]
    public function getFollowedVehicles(): JsonResponse
    {
        try {
            $user = $this->getUser();
            $vehicles = $this->vehicleService->getFollowedVehicles($user);
            
            $vehicleData = [];
            foreach ($vehicles as $vehicle) {
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
                    'merchant' => [
                        'id' => $vehicle->getMerchant()->getId(),
                        'fullName' => $vehicle->getMerchant()->getFullName(),
                        'email' => $vehicle->getMerchant()->getEmail()
                    ]
                ];

                // Add type-specific attributes
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

                $vehicleData[] = $data;
            }

            return new JsonResponse([
                'vehicles' => $vehicleData,
                'total' => count($vehicleData)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch followed vehicles: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
