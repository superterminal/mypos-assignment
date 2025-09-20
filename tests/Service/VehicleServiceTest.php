<?php

namespace App\Tests\Service;

use App\DTO\VehicleFilterDTO;
use App\Entity\User;
use App\Entity\Car;
use App\Service\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VehicleServiceTest extends KernelTestCase
{
    private VehicleService $vehicleService;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $this->vehicleService = $container->get(VehicleService::class);
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->passwordHasher = $container->get('security.user_password_hasher');
    }

    public function testCreateVehicle(): void
    {
        $merchant = $this->createTestMerchant();
        
        $data = [
            'type' => 'car',
            'brand' => 'Test Brand',
            'model' => 'Test Model',
            'engine_capacity' => '2.0',
            'colour' => 'Red',
            'price' => '25000.00',
            'quantity' => 5,
            'doors' => 4,
            'category' => 'Sedan',
        ];

        $vehicle = $this->vehicleService->createVehicle($data, $merchant);

        $this->assertInstanceOf(Car::class, $vehicle);
        $this->assertEquals('Test Brand', $vehicle->getBrand());
        $this->assertEquals('Test Model', $vehicle->getModel());
        $this->assertEquals('2.0', $vehicle->getEngineCapacity());
        $this->assertEquals('Red', $vehicle->getColour());
        $this->assertEquals('25000.00', $vehicle->getPrice());
        $this->assertEquals(5, $vehicle->getQuantity());
        $this->assertEquals(4, $vehicle->getDoors());
        $this->assertEquals('Sedan', $vehicle->getCategory());
        $this->assertEquals($merchant, $vehicle->getMerchant());
    }

    public function testGetVehiclesWithFilters(): void
    {
        // Create test vehicles
        $merchant = $this->createTestMerchant();
        $this->createTestCar($merchant, 'Toyota', 'Camry', 'Red');
        $this->createTestCar($merchant, 'Honda', 'Civic', 'Blue');

        $filterDTO = new VehicleFilterDTO();
        $filterDTO->brand = 'Toyota';
        $filterDTO->page = 1;
        $filterDTO->limit = 10;

        $vehicleList = $this->vehicleService->getVehicles($filterDTO);

        $this->assertEquals(1, $vehicleList->total);
        $this->assertEquals(1, count($vehicleList->vehicles));
        $this->assertEquals('Toyota', $vehicleList->vehicles[0]->getBrand());
    }

    public function testFollowUnfollowVehicle(): void
    {
        $merchant = $this->createTestMerchant();
        $buyer = $this->createTestBuyer();
        $vehicle = $this->createTestCar($merchant);

        // Test follow
        $result = $this->vehicleService->followVehicle($vehicle, $buyer);
        $this->assertTrue($result);
        $this->assertTrue($vehicle->isFollowedBy($buyer));

        // Test follow again (should return false)
        $result = $this->vehicleService->followVehicle($vehicle, $buyer);
        $this->assertFalse($result);

        // Test unfollow
        $result = $this->vehicleService->unfollowVehicle($vehicle, $buyer);
        $this->assertTrue($result);
        $this->assertFalse($vehicle->isFollowedBy($buyer));

        // Test unfollow again (should return false)
        $result = $this->vehicleService->unfollowVehicle($vehicle, $buyer);
        $this->assertFalse($result);
    }

    public function testGetFollowedVehicles(): void
    {
        $merchant = $this->createTestMerchant();
        $buyer = $this->createTestBuyer();
        
        $vehicle1 = $this->createTestCar($merchant, 'Toyota', 'Camry');
        $vehicle2 = $this->createTestCar($merchant, 'Honda', 'Civic');
        
        // Follow one vehicle
        $this->vehicleService->followVehicle($vehicle1, $buyer);
        
        $followedVehicles = $this->vehicleService->getFollowedVehicles($buyer);
        
        $this->assertCount(1, $followedVehicles);
        $this->assertEquals($vehicle1->getId(), $followedVehicles[0]->getId());
    }

    public function testGetMerchantVehicles(): void
    {
        $merchant = $this->createTestMerchant();
        $vehicle1 = $this->createTestCar($merchant, 'Toyota', 'Camry');
        $vehicle2 = $this->createTestCar($merchant, 'Honda', 'Civic');

        $merchantVehicles = $this->vehicleService->getMerchantVehicles($merchant);

        $this->assertCount(2, $merchantVehicles);
    }

    public function testUpdateVehicle(): void
    {
        $merchant = $this->createTestMerchant();
        $vehicle = $this->createTestCar($merchant);

        $updateData = [
            'brand' => 'Updated Brand',
            'model' => 'Updated Model',
            'engine_capacity' => '3.0',
            'colour' => 'Blue',
            'price' => '30000.00',
            'quantity' => 10,
            'doors' => 2,
            'category' => 'Coupe',
        ];

        $updatedVehicle = $this->vehicleService->updateVehicle($vehicle, $updateData);

        $this->assertEquals('Updated Brand', $updatedVehicle->getBrand());
        $this->assertEquals('Updated Model', $updatedVehicle->getModel());
        $this->assertEquals('3.0', $updatedVehicle->getEngineCapacity());
        $this->assertEquals('Blue', $updatedVehicle->getColour());
        $this->assertEquals('30000.00', $updatedVehicle->getPrice());
        $this->assertEquals(10, $updatedVehicle->getQuantity());
        $this->assertEquals(2, $updatedVehicle->getDoors());
        $this->assertEquals('Coupe', $updatedVehicle->getCategory());
    }

    public function testDeleteVehicle(): void
    {
        $merchant = $this->createTestMerchant();
        $vehicle = $this->createTestCar($merchant);
        $vehicleId = $vehicle->getId();

        $this->vehicleService->deleteVehicle($vehicle);

        $deletedVehicle = $this->entityManager->find(Car::class, $vehicleId);
        $this->assertNull($deletedVehicle);
    }

    private function createTestMerchant(): User
    {
        $user = new User();
        $user->setEmail('merchant' . uniqid() . '@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Merchant');
        $user->setRoles([User::ROLE_MERCHANT]);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createTestBuyer(): User
    {
        $user = new User();
        $user->setEmail('buyer' . uniqid() . '@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Buyer');
        $user->setRoles([User::ROLE_BUYER]);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createTestCar(User $merchant, string $brand = 'Test Brand', string $model = 'Test Model', string $colour = 'Red'): Car
    {
        $vehicle = new Car();
        $vehicle->setBrand($brand);
        $vehicle->setModel($model);
        $vehicle->setEngineCapacity('2.0');
        $vehicle->setColour($colour);
        $vehicle->setPrice('25000.00');
        $vehicle->setQuantity(5);
        $vehicle->setDoors(4);
        $vehicle->setCategory('Sedan');
        $vehicle->setMerchant($merchant);

        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();

        return $vehicle;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
