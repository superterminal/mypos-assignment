<?php

namespace App\Controller\Api;

use App\Service\VehicleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class MerchantApiController extends AbstractController
{
    public function __construct(
        private VehicleService $vehicleService
    ) {
    }

    #[Route('/merchant/vehicles', name: 'app_api_merchant_vehicles', methods: ['GET'])]
    #[IsGranted('ROLE_MERCHANT')]
    public function getMerchantVehicles(): JsonResponse
    {
        try {
            $user = $this->getUser();
            $vehicles = $this->vehicleService->getMerchantVehicles($user);
            
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
                    'createdAt' => $vehicle->getCreatedAt()->format('Y-m-d H:i:s')
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
            return new JsonResponse(['error' => 'Failed to fetch merchant vehicles: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
