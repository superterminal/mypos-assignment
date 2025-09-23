<?php

namespace App\Controller\Api;

use App\DTO\VehicleCreateDTO;
use App\DTO\VehicleUpdateDTO;
use App\Entity\User;
use App\Service\VehicleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class VehicleApiController extends AbstractController
{
    public function __construct(
        private VehicleService $vehicleService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/vehicles', name: 'app_api_vehicles', methods: ['GET'])]
    public function getVehicles(Request $request): JsonResponse
    {
        try {
            // Get all vehicles using a query that handles missing merchants gracefully
            $vehicles = $this->vehicleService->getVehicleRepository()->findAllWithValidMerchants();
            
            $vehicleData = [];
            $followedVehicleIds = [];
            
            // Get followed vehicles if user is logged in and is a buyer
            if ($this->getUser() && $this->getUser()->isBuyer()) {
                try {
                    $followedVehicles = $this->vehicleService->getFollowedVehicles($this->getUser());
                    $followedVehicleIds = array_map(fn($v) => $v->getId(), $followedVehicles);
                } catch (\Exception $e) {
                    // If getting followed vehicles fails, just continue with empty array
                    $followedVehicleIds = [];
                }
            }

            foreach ($vehicles as $vehicle) {
                try {
                    $merchant = null;
                    try {
                        $merchantEntity = $vehicle->getMerchant();
                        if ($merchantEntity) {
                            $merchant = [
                                'id' => $merchantEntity->getId(),
                                'fullName' => $merchantEntity->getFullName(),
                                'email' => $merchantEntity->getEmail()
                            ];
                        }
                    } catch (\Exception $e) {
                        // If merchant is missing or corrupted, set to null
                        $merchant = null;
                    }

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
                        'isFollowed' => in_array($vehicle->getId(), $followedVehicleIds),
                        'merchant' => $merchant
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
                } catch (\Exception $e) {
                    // Skip this vehicle if there's an error processing it
                    continue;
                }
            }

            return new JsonResponse([
                'vehicles' => $vehicleData,
                'total' => count($vehicleData)
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch vehicles: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/vehicles/{id}', name: 'app_api_vehicle_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getVehicle(int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        $vehicleData = [
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
                    $vehicleData['doors'] = $vehicle->getDoors();
                    $vehicleData['category'] = $vehicle->getCategory();
                }
                break;
            case 'truck':
                if ($vehicle instanceof \App\Entity\Truck) {
                    $vehicleData['beds'] = $vehicle->getBeds();
                }
                break;
            case 'trailer':
                if ($vehicle instanceof \App\Entity\Trailer) {
                    $vehicleData['loadCapacityKg'] = $vehicle->getLoadCapacityKg();
                    $vehicleData['axles'] = $vehicle->getAxles();
                }
                break;
        }

        // Check if user is following this vehicle
        if ($this->getUser() && $this->getUser()->isBuyer()) {
            try {
                $followedVehicles = $this->vehicleService->getFollowedVehicles($this->getUser());
                $followedVehicleIds = array_map(fn($v) => $v->getId(), $followedVehicles);
                $vehicleData['isFollowed'] = in_array($vehicle->getId(), $followedVehicleIds);
            } catch (\Exception $e) {
                $vehicleData['isFollowed'] = false;
            }
        } else {
            $vehicleData['isFollowed'] = false;
        }

        return new JsonResponse($vehicleData);
    }

    #[Route('/vehicles', name: 'app_api_vehicle_create', methods: ['POST'])]
    #[IsGranted('ROLE_MERCHANT')]
    public function createVehicle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $dto = new VehicleCreateDTO();
        $dto->type = $data['type'] ?? '';
        $dto->brand = $data['brand'] ?? '';
        $dto->model = $data['model'] ?? '';
        $dto->colour = $data['colour'] ?? '';
        $dto->price = $data['price'] ?? '';
        $dto->quantity = (int) ($data['quantity'] ?? 0);
        $dto->engineCapacity = $data['engineCapacity'] ?? null;

        // Set type-specific fields
        switch ($dto->type) {
            case 'car':
                $dto->doors = isset($data['doors']) ? (int) $data['doors'] : null;
                $dto->category = $data['category'] ?? '';
                break;
            case 'truck':
                $dto->beds = isset($data['beds']) ? (int) $data['beds'] : null;
                break;
            case 'trailer':
                $dto->loadCapacityKg = $data['loadCapacityKg'] ?? '';
                $dto->axles = isset($data['axles']) ? (int) $data['axles'] : null;
                break;
        }

        // Validate the DTO
        $violations = $this->validator->validate($dto, null, $dto->getValidationGroups());
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->getUser();
            $vehicle = $this->vehicleService->createVehicle($dto, $user);
            
            return new JsonResponse([
                'id' => $vehicle->getId(),
                'message' => 'Vehicle created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to create vehicle: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/vehicles/{id}', name: 'app_api_vehicle_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MERCHANT')]
    public function updateVehicle(int $id, Request $request): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if user owns this vehicle
        if ($vehicle->getMerchant()->getId() !== $this->getUser()->getId()) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $dto = new VehicleUpdateDTO($vehicle->getType());
        $dto->brand = $data['brand'] ?? '';
        $dto->model = $data['model'] ?? '';
        $dto->colour = $data['colour'] ?? '';
        $dto->price = $data['price'] ?? '';
        $dto->quantity = (int) ($data['quantity'] ?? 0);
        $dto->engineCapacity = $data['engineCapacity'] ?? null;

        // Set type-specific fields
        switch ($vehicle->getType()) {
            case 'car':
                $dto->doors = isset($data['doors']) ? (int) $data['doors'] : null;
                $dto->category = $data['category'] ?? '';
                break;
            case 'truck':
                $dto->beds = isset($data['beds']) ? (int) $data['beds'] : null;
                break;
            case 'trailer':
                $dto->loadCapacityKg = $data['loadCapacityKg'] ?? '';
                $dto->axles = isset($data['axles']) ? (int) $data['axles'] : null;
                break;
        }

        // Validate the DTO
        $violations = $this->validator->validate($dto, null, $dto->getValidationGroups());
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->vehicleService->updateVehicle($vehicle, $dto);
            
            return new JsonResponse(['message' => 'Vehicle updated successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to update vehicle: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/vehicles/{id}', name: 'app_api_vehicle_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MERCHANT')]
    public function deleteVehicle(int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if user owns this vehicle
        if ($vehicle->getMerchant()->getId() !== $this->getUser()->getId()) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->vehicleService->deleteVehicle($vehicle);
            
            return new JsonResponse(['message' => 'Vehicle deleted successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete vehicle: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/vehicles/{id}/follow', name: 'app_api_vehicle_follow', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_BUYER')]
    public function followVehicle(int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            /** @var User $user */
            $user = $this->getUser();
            $success = $this->vehicleService->followVehicle($vehicle, $user);
            
            if ($success) {
                return new JsonResponse(['message' => 'Vehicle followed successfully']);
            } else {
                return new JsonResponse(['message' => 'Vehicle is already being followed']);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to follow vehicle: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/vehicles/{id}/follow', name: 'app_api_vehicle_unfollow', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_BUYER')]
    public function unfollowVehicle(int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            /** @var User $user */
            $user = $this->getUser();
            $success = $this->vehicleService->unfollowVehicle($vehicle, $user);
            
            if ($success) {
                return new JsonResponse(['message' => 'Vehicle unfollowed successfully']);
            } else {
                return new JsonResponse(['message' => 'Vehicle was not being followed']);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to unfollow vehicle: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/vehicles/filter-options', name: 'app_api_vehicle_filter_options', methods: ['GET'])]
    public function getFilterOptions(): JsonResponse
    {
        $options = $this->vehicleService->getFilterOptions();
        return new JsonResponse($options);
    }

    #[Route('/car-data', name: 'app_api_car_data')]
    public function getCarData(): JsonResponse
    {
        // Fetch car data from the external JSON file
        $carDataUrl = 'https://raw.githubusercontent.com/getFrontend/json-car-list/refs/heads/main/car-list.json';
        
        try {
            $jsonData = file_get_contents($carDataUrl);
            if ($jsonData === false) {
                throw new \Exception('Failed to fetch car data');
            }
            
            $carData = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
            }
            
            return new JsonResponse($carData);
        } catch (\Exception $e) {
            // Fallback data in case external API is unavailable
            $fallbackData = [
                ['brand' => 'Audi', 'models' => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q3', 'Q5', 'Q7', 'Q8', 'TT', 'R8']],
                ['brand' => 'BMW', 'models' => ['1 Series', '2 Series', '3 Series', '4 Series', '5 Series', '6 Series', '7 Series', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7', 'Z4', 'i3', 'i8']],
                ['brand' => 'Mercedes-Benz', 'models' => ['A-Class', 'B-Class', 'C-Class', 'CLA', 'CLS', 'E-Class', 'G-Class', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'S-Class', 'SL', 'SLC', 'V-Class']],
                ['brand' => 'Volkswagen', 'models' => ['Golf', 'Polo', 'Passat', 'Tiguan', 'Touareg', 'Arteon', 'ID.3', 'ID.4', 'ID.5', 'T-Cross', 'T-Roc', 'Touran', 'Sharan']],
                ['brand' => 'Toyota', 'models' => ['Yaris', 'Corolla', 'Camry', 'Prius', 'RAV4', 'Highlander', 'Land Cruiser', 'Hilux', 'Auris', 'Avensis', 'Aygo', 'C-HR', 'Supra']],
                ['brand' => 'Honda', 'models' => ['Civic', 'Accord', 'CR-V', 'HR-V', 'Pilot', 'Ridgeline', 'Insight', 'Fit', 'Odyssey', 'Passport']],
                ['brand' => 'Ford', 'models' => ['Fiesta', 'Focus', 'Mondeo', 'Mustang', 'Kuga', 'Explorer', 'Edge', 'F-150', 'Ranger', 'Transit', 'Galaxy', 'S-Max']],
                ['brand' => 'Nissan', 'models' => ['Micra', 'Sentra', 'Altima', 'Maxima', 'Juke', 'Qashqai', 'X-Trail', 'Pathfinder', 'Murano', '370Z', 'GT-R', 'Leaf']],
                ['brand' => 'Hyundai', 'models' => ['i10', 'i20', 'i30', 'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Kona', 'Ioniq', 'Nexo', 'Genesis']],
                ['brand' => 'Kia', 'models' => ['Picanto', 'Rio', 'Ceed', 'Optima', 'Sportage', 'Sorento', 'Stonic', 'Niro', 'Soul', 'Stinger']],
                ['brand' => 'Mazda', 'models' => ['Mazda2', 'Mazda3', 'Mazda6', 'CX-3', 'CX-5', 'CX-9', 'MX-5', 'MX-30']],
                ['brand' => 'Subaru', 'models' => ['Impreza', 'Legacy', 'Outback', 'Forester', 'XV', 'WRX', 'BRZ', 'Ascent']],
                ['brand' => 'Lexus', 'models' => ['IS', 'ES', 'GS', 'LS', 'NX', 'RX', 'GX', 'LX', 'LC', 'RC', 'UX']],
                ['brand' => 'Infiniti', 'models' => ['Q30', 'Q50', 'Q60', 'QX30', 'QX50', 'QX60', 'QX70', 'QX80']],
                ['brand' => 'Acura', 'models' => ['ILX', 'TLX', 'RLX', 'RDX', 'MDX', 'NSX']],
                ['brand' => 'Volvo', 'models' => ['V40', 'S60', 'S90', 'V60', 'V90', 'XC40', 'XC60', 'XC90']],
                ['brand' => 'Jaguar', 'models' => ['XE', 'XF', 'XJ', 'F-PACE', 'E-PACE', 'I-PACE', 'F-TYPE']],
                ['brand' => 'Land Rover', 'models' => ['Range Rover Evoque', 'Range Rover Velar', 'Range Rover Sport', 'Range Rover', 'Discovery', 'Discovery Sport', 'Defender']],
                ['brand' => 'Porsche', 'models' => ['718 Boxster', '718 Cayman', '911', 'Panamera', 'Macan', 'Cayenne', 'Taycan']],
                ['brand' => 'Tesla', 'models' => ['Model S', 'Model 3', 'Model X', 'Model Y', 'Roadster', 'Cybertruck']]
            ];
            
            return new JsonResponse($fallbackData);
        }
    }
}
