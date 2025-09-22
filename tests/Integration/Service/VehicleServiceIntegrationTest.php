<?php

namespace App\Tests\Integration\Service;

use App\DTO\VehicleCreateDTO;
use App\DTO\VehicleFilterDTO;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Car;
use App\Entity\Motorcycle;
use App\Service\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VehicleServiceIntegrationTest extends KernelTestCase
{
    private VehicleService $vehicleService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        
        $this->vehicleService = $container->get(VehicleService::class);
        $this->entityManager = $container->get('doctrine')->getManager();
        
        $this->createDatabaseSchema();
    }

    private function createDatabaseSchema(): void
    {
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);
    }

    protected function tearDown(): void
    {
        $this->clearTestData();
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testCreateAndRetrieveVehicle(): void
    {
        $merchant = $this->createTestMerchant();
        
        // Create DTO
        $dto = new VehicleCreateDTO();
        $dto->type = 'car';
        $dto->brand = 'Toyota';
        $dto->model = 'Camry';
        $dto->engineCapacity = '2.5';
        $dto->colour = 'Blue';
        $dto->price = '25000.00';
        $dto->quantity = 5;
        $dto->doors = 4;
        $dto->category = 'Sedan';
        
        $vehicle = $this->vehicleService->createVehicle($dto, $merchant);
        
        $retrievedVehicle = $this->vehicleService->getVehicleById($vehicle->getId());
        
        $this->assertNotNull($retrievedVehicle);
        $this->assertEquals('Toyota', $retrievedVehicle->getBrand());
        $this->assertEquals('Camry', $retrievedVehicle->getModel());
        $this->assertEquals($merchant->getId(), $retrievedVehicle->getMerchant()->getId());
    }

    public function testVehicleFilteringAndPagination(): void
    {
        $merchant = $this->createTestMerchant();
        
        // Create test vehicles
        $this->createTestCar($merchant, 'Toyota', 'Camry', 'Blue', '25000.00');
        $this->createTestCar($merchant, 'Honda', 'Civic', 'Red', '22000.00');
        $this->createTestMotorcycle($merchant, 'Yamaha', 'R1', 'Black', '15000.00');
        
        // Test type filter
        $filterDTO = new VehicleFilterDTO();
        $filterDTO->type = 'car';
        $filterDTO->page = 1;
        $filterDTO->limit = 10;
        
        $result = $this->vehicleService->getVehicles($filterDTO);
        
        $this->assertCount(2, $result->vehicles);
        $this->assertEquals(2, $result->total);
        $this->assertEquals(1, $result->totalPages);
        
        // Test brand filter
        $filterDTO->type = null;
        $filterDTO->brand = 'Toyota';
        
        $result = $this->vehicleService->getVehicles($filterDTO);
        
        $this->assertCount(1, $result->vehicles);
        $this->assertEquals('Toyota', $result->vehicles[0]->getBrand());
    }

    public function testFollowUnfollowVehicleWorkflow(): void
    {
        $merchant = $this->createTestMerchant();
        $buyer = $this->createTestBuyer();
        $vehicle = $this->createTestCar($merchant);
        
        // Test follow
        $result = $this->vehicleService->followVehicle($vehicle, $buyer);
        $this->assertTrue($result);
        
        // Verify follow relationship
        $this->entityManager->refresh($vehicle);
        $this->assertTrue($vehicle->isFollowedBy($buyer));
        
        // Test unfollow
        $result = $this->vehicleService->unfollowVehicle($vehicle, $buyer);
        $this->assertTrue($result);
        
        // Verify unfollow relationship
        $this->entityManager->refresh($vehicle);
        $this->assertFalse($vehicle->isFollowedBy($buyer));
    }

    public function testGetFollowedVehicles(): void
    {
        $merchant = $this->createTestMerchant();
        $buyer = $this->createTestBuyer();
        
        // Create and follow multiple vehicles
        $vehicle1 = $this->createTestCar($merchant, 'Toyota', 'Camry');
        $vehicle2 = $this->createTestCar($merchant, 'Honda', 'Civic');
        $vehicle3 = $this->createTestMotorcycle($merchant, 'Yamaha', 'R1');
        
        $this->vehicleService->followVehicle($vehicle1, $buyer);
        $this->vehicleService->followVehicle($vehicle2, $buyer);
        $this->vehicleService->followVehicle($vehicle3, $buyer);
        
        $followedVehicles = $this->vehicleService->getFollowedVehicles($buyer);
        
        $this->assertCount(3, $followedVehicles);
        $this->assertContains($vehicle1, $followedVehicles);
        $this->assertContains($vehicle2, $followedVehicles);
        $this->assertContains($vehicle3, $followedVehicles);
    }

    public function testGetMerchantVehicles(): void
    {
        $merchant = $this->createTestMerchant();
        
        // Create multiple vehicles for merchant
        $vehicle1 = $this->createTestCar($merchant, 'Toyota', 'Camry');
        $vehicle2 = $this->createTestCar($merchant, 'Honda', 'Civic');
        $vehicle3 = $this->createTestMotorcycle($merchant, 'Yamaha', 'R1');
        
        $merchantVehicles = $this->vehicleService->getMerchantVehicles($merchant);
        
        $this->assertCount(3, $merchantVehicles);
        $this->assertContains($vehicle1, $merchantVehicles);
        $this->assertContains($vehicle2, $merchantVehicles);
        $this->assertContains($vehicle3, $merchantVehicles);
    }

    public function testGetFilterOptions(): void
    {
        $merchant = $this->createTestMerchant();
        
        // Create vehicles with different attributes
        $this->createTestCar($merchant, 'Toyota', 'Camry', 'Blue', '25000.00');
        $this->createTestCar($merchant, 'Honda', 'Civic', 'Red', '22000.00');
        $this->createTestMotorcycle($merchant, 'Yamaha', 'R1', 'Black', '15000.00');
        
        $filterOptions = $this->vehicleService->getFilterOptions();
        
        $this->assertArrayHasKey('types', $filterOptions);
        $this->assertArrayHasKey('brands', $filterOptions);
        $this->assertArrayHasKey('colours', $filterOptions);
        
        $this->assertContains('car', $filterOptions['types']);
        $this->assertContains('motorcycle', $filterOptions['types']);
        $this->assertContains('Toyota', $filterOptions['brands']);
        $this->assertContains('Honda', $filterOptions['brands']);
        $this->assertContains('Yamaha', $filterOptions['brands']);
    }


    private function createTestMerchant(): User
    {
        $user = new User();
        $user->setEmail('merchant@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Merchant');
        $user->setRoles([User::ROLE_MERCHANT]);
        $user->setPassword('hashed_password');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function createTestBuyer(): User
    {
        $user = new User();
        $user->setEmail('buyer@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Buyer');
        $user->setRoles([User::ROLE_BUYER]);
        $user->setPassword('hashed_password');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function createTestCar(User $merchant, string $brand = 'Test Brand', string $model = 'Test Model', string $colour = 'Red', string $price = '25000.00'): Car
    {
        $vehicle = new Car();
        $vehicle->setBrand($brand);
        $vehicle->setModel($model);
        $vehicle->setEngineCapacity('2.0');
        $vehicle->setColour($colour);
        $vehicle->setPrice($price);
        $vehicle->setQuantity(5);
        $vehicle->setDoors(4);
        $vehicle->setCategory('Sedan');
        $vehicle->setMerchant($merchant);

        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();
        
        return $vehicle;
    }

    private function createTestMotorcycle(User $merchant, string $brand = 'Test Brand', string $model = 'Test Model', string $colour = 'Red', string $price = '15000.00'): Motorcycle
    {
        $vehicle = new Motorcycle();
        $vehicle->setBrand($brand);
        $vehicle->setModel($model);
        $vehicle->setEngineCapacity('1.0');
        $vehicle->setColour($colour);
        $vehicle->setPrice($price);
        $vehicle->setQuantity(3);
        $vehicle->setMerchant($merchant);

        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();
        
        return $vehicle;
    }

    private function clearTestData(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM vehicle_followers');
        $connection->executeStatement('DELETE FROM vehicle');
        $connection->executeStatement('DELETE FROM user');
    }
}
