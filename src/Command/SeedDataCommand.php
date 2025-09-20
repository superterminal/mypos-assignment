<?php

namespace App\Command;

use App\Entity\Car;
use App\Entity\Motorcycle;
use App\Entity\Trailer;
use App\Entity\Truck;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed the database with default vehicle and user data',
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Seeding Database with Default Data');

        // Create test users
        $this->createUsers($io);
        
        // Create vehicles
        $this->createVehicles($io);

        $this->entityManager->flush();

        $io->success('Database seeded successfully!');
        $io->note('You can now login with:');
        $io->listing([
            'Merchant: merchant@test.com / password123',
            'Buyer: buyer@test.com / password123',
        ]);

        return Command::SUCCESS;
    }

    private function createUsers(SymfonyStyle $io): void
    {
        $io->section('Creating Users');

        // Create merchant
        $merchant = new User();
        $merchant->setEmail('merchant@test.com');
        $merchant->setFirstName('John');
        $merchant->setLastName('Dealer');
        $merchant->setRoles([User::ROLE_MERCHANT]);
        $merchant->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($merchant, 'password123');
        $merchant->setPassword($hashedPassword);

        $this->entityManager->persist($merchant);
        $io->text('✓ Created merchant: merchant@test.com');

        // Create buyer
        $buyer = new User();
        $buyer->setEmail('buyer@test.com');
        $buyer->setFirstName('Jane');
        $buyer->setLastName('Customer');
        $buyer->setRoles([User::ROLE_BUYER]);
        $buyer->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($buyer, 'password123');
        $buyer->setPassword($hashedPassword);

        $this->entityManager->persist($buyer);
        $io->text('✓ Created buyer: buyer@test.com');

        // Create another merchant
        $merchant2 = new User();
        $merchant2->setEmail('dealer@autos.com');
        $merchant2->setFirstName('Mike');
        $merchant2->setLastName('Auto');
        $merchant2->setRoles([User::ROLE_MERCHANT]);
        $merchant2->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($merchant2, 'password123');
        $merchant2->setPassword($hashedPassword);

        $this->entityManager->persist($merchant2);
        $io->text('✓ Created merchant: dealer@autos.com');

        // Store users for vehicle creation
        $this->merchant = $merchant;
        $this->merchant2 = $merchant2;
        $this->buyer = $buyer;
    }

    private function createVehicles(SymfonyStyle $io): void
    {
        $io->section('Creating Vehicles');

        // Cars
        $this->createCar('Toyota', 'Camry', '2.5', 'Silver', '28000.00', 3, 4, 'Sedan', $this->merchant);
        $this->createCar('Honda', 'Civic', '1.8', 'Blue', '25000.00', 2, 4, 'Sedan', $this->merchant);
        $this->createCar('BMW', 'X5', '3.0', 'Black', '65000.00', 1, 5, 'SUV', $this->merchant);
        $this->createCar('Ford', 'Mustang', '5.0', 'Red', '45000.00', 2, 2, 'Coupe', $this->merchant2);
        $this->createCar('Audi', 'A4', '2.0', 'White', '42000.00', 1, 4, 'Sedan', $this->merchant2);

        $io->text('✓ Created 5 cars');

        // Motorcycles
        $this->createMotorcycle('Honda', 'CBR600RR', '0.6', 'Red', '12000.00', 2, $this->merchant);
        $this->createMotorcycle('Yamaha', 'YZF-R1', '1.0', 'Blue', '18000.00', 1, $this->merchant);
        $this->createMotorcycle('Kawasaki', 'Ninja 650', '0.65', 'Green', '8500.00', 3, $this->merchant2);
        $this->createMotorcycle('Ducati', 'Panigale V4', '1.1', 'Red', '25000.00', 1, $this->merchant2);

        $io->text('✓ Created 4 motorcycles');

        // Trucks
        $this->createTruck('Ford', 'F-150', '3.5', 'White', '35000.00', 2, 2, $this->merchant);
        $this->createTruck('Chevrolet', 'Silverado', '5.3', 'Black', '38000.00', 1, 2, $this->merchant);
        $this->createTruck('Ram', '1500', '3.6', 'Gray', '32000.00', 3, 2, $this->merchant2);

        $io->text('✓ Created 3 trucks');

        // Trailers
        $this->createTrailer('Pace', 'American', '3500.00', 2, '8500.00', 1, 'White', $this->merchant);
        $this->createTrailer('Haulmark', 'Pro', '5000.00', 2, '12000.00', 2, 'Black', $this->merchant);
        $this->createTrailer('Wells', 'Cargo', '7000.00', 3, '15000.00', 1, 'Gray', $this->merchant2);

        $io->text('✓ Created 3 trailers');
    }

    private function createCar(string $brand, string $model, string $engineCapacity, string $colour, string $price, int $quantity, int $doors, string $category, User $merchant): void
    {
        $car = new Car();
        $car->setBrand($brand);
        $car->setModel($model);
        $car->setEngineCapacity($engineCapacity);
        $car->setColour($colour);
        $car->setPrice($price);
        $car->setQuantity($quantity);
        $car->setDoors($doors);
        $car->setCategory($category);
        $car->setMerchant($merchant);

        $this->entityManager->persist($car);
    }

    private function createMotorcycle(string $brand, string $model, string $engineCapacity, string $colour, string $price, int $quantity, User $merchant): void
    {
        $motorcycle = new Motorcycle();
        $motorcycle->setBrand($brand);
        $motorcycle->setModel($model);
        $motorcycle->setEngineCapacity($engineCapacity);
        $motorcycle->setColour($colour);
        $motorcycle->setPrice($price);
        $motorcycle->setQuantity($quantity);
        $motorcycle->setMerchant($merchant);

        $this->entityManager->persist($motorcycle);
    }

    private function createTruck(string $brand, string $model, string $engineCapacity, string $colour, string $price, int $quantity, int $beds, User $merchant): void
    {
        $truck = new Truck();
        $truck->setBrand($brand);
        $truck->setModel($model);
        $truck->setEngineCapacity($engineCapacity);
        $truck->setColour($colour);
        $truck->setPrice($price);
        $truck->setQuantity($quantity);
        $truck->setBeds($beds);
        $truck->setMerchant($merchant);

        $this->entityManager->persist($truck);
    }

    private function createTrailer(string $brand, string $model, string $loadCapacityKg, int $axles, string $price, int $quantity, string $colour, User $merchant): void
    {
        $trailer = new Trailer();
        $trailer->setBrand($brand);
        $trailer->setModel($model);
        $trailer->setLoadCapacityKg($loadCapacityKg);
        $trailer->setAxles($axles);
        $trailer->setPrice($price);
        $trailer->setQuantity($quantity);
        $trailer->setColour($colour);
        $trailer->setMerchant($merchant);
        // Trailers don't have engine capacity, so we leave it null

        $this->entityManager->persist($trailer);
    }

    private User $merchant;
    private User $merchant2;
    private User $buyer;
}
