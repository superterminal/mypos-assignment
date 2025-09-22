<?php

namespace App\Tests\Unit\Service;

use App\DTO\VehicleCreateDTO;
use App\DTO\VehicleFilterDTO;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Car;
use App\Repository\VehicleRepository;
use App\Service\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VehicleServiceTest extends TestCase
{
    private VehicleService $vehicleService;
    private EntityManagerInterface|MockObject $entityManager;
    private VehicleRepository|MockObject $vehicleRepository;
    private ValidatorInterface|MockObject $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->vehicleRepository = $this->createMock(VehicleRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        
        $this->vehicleService = new VehicleService(
            $this->vehicleRepository,
            $this->entityManager,
            $this->validator
        );
    }

    public function testCreateVehicleWithCarData(): void
    {
        $merchant = $this->createMockUser();
        
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

        // Mock validation to return no violations
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        // Mock entity manager
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Car::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $vehicle = $this->vehicleService->createVehicle($dto, $merchant);

        $this->assertInstanceOf(Car::class, $vehicle);
        $this->assertEquals('Toyota', $vehicle->getBrand());
        $this->assertEquals('Camry', $vehicle->getModel());
        $this->assertEquals('2.5', $vehicle->getEngineCapacity());
        $this->assertEquals('Blue', $vehicle->getColour());
        $this->assertEquals('25000.00', $vehicle->getPrice());
        $this->assertEquals(5, $vehicle->getQuantity());
        $this->assertEquals(4, $vehicle->getDoors());
        $this->assertEquals('Sedan', $vehicle->getCategory());
        $this->assertEquals($merchant, $vehicle->getMerchant());
    }

    public function testGetVehicleByIdReturnsVehicle(): void
    {
        $vehicle = $this->createMockVehicle();
        $vehicleId = 1;

        $this->vehicleRepository
            ->expects($this->once())
            ->method('find')
            ->with($vehicleId)
            ->willReturn($vehicle);

        $result = $this->vehicleService->getVehicleById($vehicleId);

        $this->assertSame($vehicle, $result);
    }

    public function testGetVehicleByIdReturnsNullWhenNotFound(): void
    {
        $vehicleId = 999;

        $this->vehicleRepository
            ->expects($this->once())
            ->method('find')
            ->with($vehicleId)
            ->willReturn(null);

        $result = $this->vehicleService->getVehicleById($vehicleId);

        $this->assertNull($result);
    }

    public function testFollowVehicleReturnsTrueWhenNotAlreadyFollowed(): void
    {
        $vehicle = $this->createMockVehicle();
        $user = $this->createMockUser();

        $vehicle->expects($this->once())
            ->method('isFollowedBy')
            ->with($user)
            ->willReturn(false);

        $vehicle->expects($this->once())
            ->method('addFollower')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->vehicleService->followVehicle($vehicle, $user);

        $this->assertTrue($result);
    }

    public function testFollowVehicleReturnsFalseWhenAlreadyFollowed(): void
    {
        $vehicle = $this->createMockVehicle();
        $user = $this->createMockUser();

        $vehicle->expects($this->once())
            ->method('isFollowedBy')
            ->with($user)
            ->willReturn(true);

        $vehicle->expects($this->never())
            ->method('addFollower');

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $result = $this->vehicleService->followVehicle($vehicle, $user);

        $this->assertFalse($result);
    }

    public function testUnfollowVehicleReturnsTrueWhenFollowed(): void
    {
        $vehicle = $this->createMockVehicle();
        $user = $this->createMockUser();

        $vehicle->expects($this->once())
            ->method('isFollowedBy')
            ->with($user)
            ->willReturn(true);

        $vehicle->expects($this->once())
            ->method('removeFollower')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->vehicleService->unfollowVehicle($vehicle, $user);

        $this->assertTrue($result);
    }

    public function testUnfollowVehicleReturnsFalseWhenNotFollowed(): void
    {
        $vehicle = $this->createMockVehicle();
        $user = $this->createMockUser();

        $vehicle->expects($this->once())
            ->method('isFollowedBy')
            ->with($user)
            ->willReturn(false);

        $vehicle->expects($this->never())
            ->method('removeFollower');

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $result = $this->vehicleService->unfollowVehicle($vehicle, $user);

        $this->assertFalse($result);
    }

    public function testDeleteVehicle(): void
    {
        $vehicle = $this->createMockVehicle();

        $this->vehicleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($vehicle, true);

        $this->vehicleService->deleteVehicle($vehicle);
    }

    private function createMockUser(): User|MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getEmail')->willReturn('test@example.com');
        return $user;
    }

    private function createMockVehicle(): Vehicle|MockObject
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('getId')->willReturn(1);
        $vehicle->method('getBrand')->willReturn('Test Brand');
        $vehicle->method('getModel')->willReturn('Test Model');
        return $vehicle;
    }
}
