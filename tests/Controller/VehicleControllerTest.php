<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VehicleControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->passwordHasher = $container->get('security.user_password_hasher');
    }

    public function testVehicleListPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vehicles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Vehicles');
    }

    public function testVehicleListWithFilters(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vehicles?type=car&brand=Toyota');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Vehicles');
    }

    public function testVehicleDetailsPage(): void
    {
        $client = static::createClient();
        
        // Create a test vehicle
        $vehicle = $this->createTestVehicle();
        
        $crawler = $client->request('GET', '/vehicle/' . $vehicle->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', $vehicle->getDisplayName());
    }

    public function testMerchantCanAccessVehicleManagement(): void
    {
        $client = static::createClient();
        
        // Create and login as merchant
        $merchant = $this->createTestMerchant();
        $client->loginUser($merchant);
        
        $crawler = $client->request('GET', '/merchant/vehicles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'My Vehicles');
    }

    public function testBuyerCanAccessFollowedVehicles(): void
    {
        $client = static::createClient();
        
        // Create and login as buyer
        $buyer = $this->createTestBuyer();
        $client->loginUser($buyer);
        
        $crawler = $client->request('GET', '/buyer/followed');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'My Followed Vehicles');
    }

    public function testUnauthorizedAccessToMerchantArea(): void
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/merchant/vehicles');
        $this->assertResponseRedirects('/login');
    }

    public function testUnauthorizedAccessToBuyerArea(): void
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/buyer/followed');
        $this->assertResponseRedirects('/login');
    }

    private function createTestMerchant(): User
    {
        $user = new User();
        $user->setEmail('merchant@test.com');
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
        $user->setEmail('buyer@test.com');
        $user->setFirstName('Test');
        $user->setLastName('Buyer');
        $user->setRoles([User::ROLE_BUYER]);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createTestVehicle(): Vehicle
    {
        $merchant = $this->createTestMerchant();
        
        $vehicle = new Car();
        $vehicle->setBrand('Test Brand');
        $vehicle->setModel('Test Model');
        $vehicle->setEngineCapacity('2.0');
        $vehicle->setColour('Red');
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
