<?php

namespace App\Controller;

use App\DTO\VehicleFilterDTO;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\VehicleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VehicleController extends AbstractController
{
    public function __construct(
        private VehicleService $vehicleService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/vehicles', name: 'app_vehicles')]
    public function index(Request $request): Response
    {
        $filterDTO = new VehicleFilterDTO();
        $filterDTO->type = $request->query->get('type');
        $filterDTO->brand = $request->query->get('brand');
        $filterDTO->model = $request->query->get('model');
        $filterDTO->colour = $request->query->get('colour');
        $filterDTO->priceMin = $request->query->get('price_min') ? (float) $request->query->get('price_min') : null;
        $filterDTO->priceMax = $request->query->get('price_max') ? (float) $request->query->get('price_max') : null;
        $filterDTO->page = (int) $request->query->get('page', 1);
        $filterDTO->limit = (int) $request->query->get('limit', 10);

        $vehicleList = $this->vehicleService->getVehicles($filterDTO);
        $filterOptions = $this->vehicleService->getFilterOptions();

        // Check which vehicles are followed by the current user
        $followedVehicleIds = [];
        if ($this->getUser() && $this->getUser()->isBuyer()) {
            $followedVehicleIds = $this->vehicleService->getFollowedVehicleIds($this->getUser());
        }

        return $this->render('vehicle/index.html.twig', [
            'vehicleList' => $vehicleList,
            'filterOptions' => $filterOptions,
            'currentFilters' => $filterDTO,
            'followedVehicleIds' => $followedVehicleIds,
        ]);
    }

    #[Route('/vehicle/{id}', name: 'app_vehicle_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        $isFollowed = false;
        if ($this->getUser() && $this->getUser()->isBuyer()) {
            $isFollowed = $vehicle->isFollowedBy($this->getUser());
        }

        return $this->render('vehicle/show.html.twig', [
            'vehicle' => $vehicle,
            'isFollowed' => $isFollowed,
        ]);
    }

    #[Route('/vehicle/{id}/follow', name: 'app_vehicle_follow', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function follow(int $id, Request $request): Response
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        
        if ($this->vehicleService->followVehicle($vehicle, $user)) {
            $this->addFlash('success', 'Vehicle added to your followed list!');
        } else {
            $this->addFlash('info', 'Vehicle is already in your followed list.');
        }

        return $this->redirectToRoute('app_vehicle_show', ['id' => $id]);
    }

    #[Route('/vehicle/{id}/unfollow', name: 'app_vehicle_unfollow', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function unfollow(int $id): Response
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        
        if ($this->vehicleService->unfollowVehicle($vehicle, $user)) {
            $this->addFlash('success', 'Vehicle removed from your followed list!');
        } else {
            $this->addFlash('info', 'Vehicle was not in your followed list.');
        }

        return $this->redirectToRoute('app_vehicle_show', ['id' => $id]);
    }

    #[Route('/merchant/vehicles', name: 'app_merchant_vehicles')]
    #[IsGranted('ROLE_MERCHANT')]
    public function merchantVehicles(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $vehicles = $this->vehicleService->getMerchantVehicles($user);

        return $this->render('vehicle/merchant_vehicles.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }

    #[Route('/merchant/vehicle/new', name: 'app_vehicle_new')]
    #[IsGranted('ROLE_MERCHANT')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            try {
                /** @var User $user */
                $user = $this->getUser();
                $vehicle = $this->vehicleService->createVehicle($data, $user);
                $this->addFlash('success', 'Vehicle created successfully!');
                return $this->redirectToRoute('app_merchant_vehicles');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create vehicle: ' . $e->getMessage());
            }
        }

        return $this->render('vehicle/new.html.twig');
    }

    #[Route('/merchant/vehicle/{id}/edit', name: 'app_vehicle_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MERCHANT')]
    public function edit(int $id, Request $request): Response
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($vehicle->getMerchant() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own vehicles');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            try {
                $this->vehicleService->updateVehicle($vehicle, $data);
                $this->addFlash('success', 'Vehicle updated successfully!');
                return $this->redirectToRoute('app_merchant_vehicles');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update vehicle: ' . $e->getMessage());
            }
        }

        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
        ]);
    }

    #[Route('/merchant/vehicle/{id}/delete', name: 'app_vehicle_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_MERCHANT')]
    public function delete(int $id): Response
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($vehicle->getMerchant() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own vehicles');
        }

        $this->vehicleService->deleteVehicle($vehicle);
        $this->addFlash('success', 'Vehicle deleted successfully!');

        return $this->redirectToRoute('app_merchant_vehicles');
    }

    #[Route('/buyer/followed', name: 'app_buyer_followed')]
    #[IsGranted('ROLE_BUYER')]
    public function followedVehicles(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $vehicles = $this->vehicleService->getFollowedVehicles($user);

        return $this->render('vehicle/followed.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }
}
