<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VehicleControllerApplicationTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        // Don't boot kernel here - let createClient() handle it
        // Database schema will be created in each test method as needed
    }

    private function createDatabaseSchema($client): void
    {
        $container = $client->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager)) {
            $this->entityManager->close();
        }
        parent::tearDown();
    }

    // ==================== PERMISSION TESTS ====================

    public function testUnauthorizedAccessToMerchantArea(): void
    {
        $client = static::createClient();
        $this->createDatabaseSchema($client);
        
        $routes = [
            ['GET', '/merchant/vehicles'],
            ['GET', '/merchant/vehicle/new'],
            ['GET', '/merchant/vehicle/1/edit'],
            ['POST', '/merchant/vehicle/1/delete']
        ];
        
        foreach ($routes as [$method, $route]) {
            $client->request($method, $route);
            $this->assertResponseRedirects('/login');
        }
        
        $this->clearTestData($client);
    }

    public function testUnauthorizedAccessToBuyerArea(): void
    {
        $client = static::createClient();
        $this->createDatabaseSchema($client);
        
        $client->request('GET', '/buyer/followed');
        $this->assertResponseRedirects('/login');
        
        $client->request('POST', '/vehicle/1/follow');
        $this->assertResponseRedirects('/login');
        
        $client->request('POST', '/vehicle/1/unfollow');
        $this->assertResponseRedirects('/login');
        
        $this->clearTestData($client);
    }

    public function testBuyerCannotAccessMerchantRoutes(): void
    {
        $client = static::createClient();
        $this->createDatabaseSchema($client);
        $buyer = $this->createTestBuyer($client);
        $client->loginUser($buyer);
        
        $merchantRoutes = [
            ['GET', '/merchant/vehicles'],
            ['GET', '/merchant/vehicle/new'],
            ['GET', '/merchant/vehicle/1/edit'],
            ['POST', '/merchant/vehicle/1/delete']
        ];
        
        foreach ($merchantRoutes as [$method, $route]) {
            $client->request($method, $route);
            $this->assertEquals(403, $client->getResponse()->getStatusCode(), "Buyer should not access $route");
        }
    }

    public function testMerchantCannotAccessBuyerRoutes(): void
    {
        $client = static::createClient();
        $this->createDatabaseSchema($client);
        $merchant = $this->createTestMerchant($client);
        $client->loginUser($merchant);
        
        $client->request('GET', '/buyer/followed');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        $client->request('POST', '/vehicle/1/follow');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        $this->clearTestData($client);
    }

    // ==================== DATA INTEGRITY TESTS ====================

    public function testVehicleCreationDataIntegrity(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        $client->loginUser($merchant);
        
        $vehicleData = [
            'type' => 'car',
            'brand' => 'Toyota',
            'model' => 'Camry',
            'engine_capacity' => '2.5',
            'colour' => 'Blue',
            'price' => '25000.00',
            'quantity' => '5',
            'doors' => '4',
            'category' => 'Sedan'
        ];
        
        $client->request('POST', '/merchant/vehicle/new', $vehicleData);
        $this->assertResponseRedirects('/merchant/vehicles');
        
        // Verify vehicle was created with correct data
        $vehicle = $this->entityManager->getRepository(Vehicle::class)->findOneBy(['brand' => 'Toyota', 'model' => 'Camry']);
        $this->assertNotNull($vehicle);
        $this->assertEquals('Toyota', $vehicle->getBrand());
        $this->assertEquals('Camry', $vehicle->getModel());
        $this->assertEquals('2.5', $vehicle->getEngineCapacity());
        $this->assertEquals('Blue', $vehicle->getColour());
        $this->assertEquals('25000.00', $vehicle->getPrice());
        $this->assertEquals(5, $vehicle->getQuantity());
        $this->assertEquals($merchant, $vehicle->getMerchant());
        
        $this->clearTestData($client);
    }

    public function testVehicleUpdateDataIntegrity(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        $vehicle = $this->createTestCar($merchant, $client);
        $client->loginUser($merchant);
        
        $updateData = [
            'type' => 'car',
            'brand' => 'Honda',
            'model' => 'Accord',
            'engine_capacity' => '2.0',
            'colour' => 'Red',
            'price' => '28000.00',
            'quantity' => '3',
            'doors' => '4',
            'category' => 'Sedan'
        ];
        
        $client->request('POST', '/merchant/vehicle/' . $vehicle->getId() . '/edit', $updateData);
        $this->assertResponseRedirects('/merchant/vehicles');
        
        $this->entityManager->refresh($vehicle);
        
        $this->assertEquals('Honda', $vehicle->getBrand());
        $this->assertEquals('Accord', $vehicle->getModel());
        $this->assertEquals('2', $vehicle->getEngineCapacity());
        $this->assertEquals('Red', $vehicle->getColour());
        $this->assertEquals('28000', $vehicle->getPrice());
        $this->assertEquals(3, $vehicle->getQuantity());
        
        $this->clearTestData($client);
    }

    // ==================== PAGINATION AND FILTERS TESTS ====================

    public function testVehicleListPagination(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        
        // Create multiple vehicles for pagination testing
        for ($i = 1; $i <= 15; $i++) {
            $this->createTestCar($merchant, $client, "Brand$i", "Model$i");
        }
        
        // Test first page
        $crawler = $client->request('GET', '/vehicles?page=1&limit=10');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Vehicles');
        
        // Test second page
        $crawler = $client->request('GET', '/vehicles?page=2&limit=10');
        $this->assertResponseIsSuccessful();
        
        $this->clearTestData($client);
    }

    public function testVehicleListFilters(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        
        // Create vehicles with different attributes
        $this->createTestCar($merchant, $client, 'Toyota', 'Camry', 'Blue', '25000.00');
        $this->createTestCar($merchant, $client, 'Honda', 'Civic', 'Red', '22000.00');
        
        // Test type filter
        $crawler = $client->request('GET', '/vehicles?type=car');
        $this->assertResponseIsSuccessful();
        
        // Test brand filter
        $crawler = $client->request('GET', '/vehicles?brand=Toyota');
        $this->assertResponseIsSuccessful();
        
        // Test colour filter
        $crawler = $client->request('GET', '/vehicles?colour=Blue');
        $this->assertResponseIsSuccessful();
        
        // Test price range filter
        $crawler = $client->request('GET', '/vehicles?price_min=20000&price_max=30000');
        $this->assertResponseIsSuccessful();
        
        $this->clearTestData($client);
    }

    // ==================== FOLLOW/UNFOLLOW TESTS ====================

    public function testFollowVehicle(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        $buyer = $this->createTestBuyer($client);
        $vehicle = $this->createTestCar($merchant, $client);
        
        $client->loginUser($buyer);
        
        // Follow vehicle
        $client->request('POST', '/vehicle/' . $vehicle->getId() . '/follow');
        $this->assertResponseRedirects('/vehicle/' . $vehicle->getId());
        
        // Verify vehicle is followed
        $this->entityManager->refresh($vehicle);
        $this->assertTrue($vehicle->isFollowedBy($buyer));
        
        // Check flash message
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Vehicle added to your followed list!');
        
        $this->clearTestData($client);
    }

    public function testUnfollowVehicle(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        $buyer = $this->createTestBuyer($client);
        $vehicle = $this->createTestCar($merchant, $client);
        
        // First follow the vehicle
        $vehicle->addFollower($buyer);
        $this->entityManager->flush();
        
        $client->loginUser($buyer);
        
        // Unfollow vehicle
        $client->request('POST', '/vehicle/' . $vehicle->getId() . '/unfollow');
        $this->assertResponseRedirects('/vehicle/' . $vehicle->getId());
        
        // Verify vehicle is unfollowed
        $this->entityManager->refresh($vehicle);
        $this->assertFalse($vehicle->isFollowedBy($buyer));
        
        // Check flash message
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Vehicle removed from your followed list!');
        
        $this->clearTestData($client);
    }

    public function testFollowedVehiclesList(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        $buyer = $this->createTestBuyer($client);
        
        // Create and follow multiple vehicles
        $vehicle1 = $this->createTestCar($merchant, $client, 'Toyota', 'Camry');
        $vehicle2 = $this->createTestCar($merchant, $client, 'Honda', 'Civic');
        
        $vehicle1->addFollower($buyer);
        $vehicle2->addFollower($buyer);
        $this->entityManager->flush();
        
        $client->loginUser($buyer);
        
        $crawler = $client->request('GET', '/buyer/followed');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'My Followed Vehicles');
        
        // Check that followed vehicles are displayed
        $this->assertSelectorTextContains('body', 'Toyota Camry');
        $this->assertSelectorTextContains('body', 'Honda Civic');
    }

    // ==================== INTEGRATION TESTS ====================

    public function testCompleteVehicleWorkflow(): void
    {
        $client = $this->createClientWithDatabase();
        $merchant = $this->createTestMerchant($client);
        $buyer = $this->createTestBuyer($client);
        
        $client->loginUser($merchant);
        
        // 1. Create vehicle
        $vehicleData = [
            'type' => 'car',
            'brand' => 'BMW',
            'model' => 'X5',
            'engine_capacity' => '3.0',
            'colour' => 'White',
            'price' => '45000.00',
            'quantity' => '2',
            'doors' => '5',
            'category' => 'SUV'
        ];
        
        $client->request('POST', '/merchant/vehicle/new', $vehicleData);
        $this->assertResponseRedirects('/merchant/vehicles');
        
        // 2. Verify vehicle appears in merchant's list
        $crawler = $client->request('GET', '/merchant/vehicles');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'BMW X5');
        
        // 3. Switch to buyer and follow vehicle
        $client->loginUser($buyer);
        $vehicle = $this->entityManager->getRepository(Vehicle::class)->findOneBy(['brand' => 'BMW', 'model' => 'X5']);
        $client->request('POST', '/vehicle/' . $vehicle->getId() . '/follow');
        $this->assertResponseRedirects('/vehicle/' . $vehicle->getId());
        
        // 4. Verify vehicle appears in buyer's followed list
        $crawler = $client->request('GET', '/buyer/followed');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'BMW X5');
        
        $this->clearTestData($client);
    }

    // ==================== HELPER METHODS ====================
    
    private function createClientWithDatabase(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = static::createClient();
        $this->createDatabaseSchema($client);
        return $client;
    }


    private function createTestMerchant($client): User
    {
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        $user = new User();
        $user->setEmail('merchant@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Merchant');
        $user->setRoles([User::ROLE_MERCHANT]);
        
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();
        
        return $user;
    }

    private function createTestBuyer($client): User
    {
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        $user = new User();
        $user->setEmail('buyer@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Buyer');
        $user->setRoles([User::ROLE_BUYER]);
        
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();
        
        return $user;
    }

    private function createTestCar(User $merchant, $client, string $brand = 'Test Brand', string $model = 'Test Model', string $colour = 'Red', string $price = '25000.00'): Car
    {
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        
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

        $entityManager->persist($vehicle);
        $entityManager->flush();
        
        return $vehicle;
    }

    private function clearTestData($client): void
    {
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        
        $connection = $entityManager->getConnection();
        $connection->executeStatement('DELETE FROM vehicle_followers');
        $connection->executeStatement('DELETE FROM vehicle');
        $connection->executeStatement('DELETE FROM user');
    }
}
