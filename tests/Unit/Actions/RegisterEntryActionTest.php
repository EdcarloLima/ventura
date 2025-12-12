<?php

namespace Tests\Unit\Actions;

use App\Domain\Parking\Actions\RegisterEntryAction;
use App\Domain\Parking\Contracts\TicketRepositoryInterface;
use App\Domain\Parking\Contracts\ParkingSpotRepositoryInterface;
use App\Domain\Vehicle\Contracts\VehicleRepositoryInterface;
use App\Domain\Parking\DTOs\TicketDTO;
use App\Domain\Parking\DTOs\TicketWithRelationsDTO;
use App\Domain\Parking\DTOs\ParkingSpotDTO;
use App\Domain\Vehicle\DTOs\VehicleDTO;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Jobs\FetchVehicleDetailsJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Exception;

class RegisterEntryActionTest extends TestCase
{
    private TicketRepositoryInterface $ticketRepository;
    private VehicleRepositoryInterface $vehicleRepository;
    private ParkingSpotRepositoryInterface $spotRepository;
    private RegisterEntryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticketRepository = $this->createMock(TicketRepositoryInterface::class);
        $this->vehicleRepository = $this->createMock(VehicleRepositoryInterface::class);
        $this->spotRepository = $this->createMock(ParkingSpotRepositoryInterface::class);

        $this->action = new RegisterEntryAction(
            $this->ticketRepository,
            $this->vehicleRepository,
            $this->spotRepository
        );
    }

    /** @test */
    public function it_registers_a_new_vehicle_entry_successfully()
    {
        Queue::fake();

        $plate = 'ABC1234';
        $gateId = 'entrada-1';

        $this->vehicleRepository
            ->expects($this->once())
            ->method('findByPlate')
            ->with($plate)
            ->willReturn(null);

        $this->ticketRepository
            ->expects($this->once())
            ->method('countActiveTickets')
            ->willReturn(50);

        $vehicleDTO = new VehicleDTO(
            id: 1,
            plate: $plate,
            type: 'Carro',
            brand: null,
            model: null,
            color: null,
            wasRecentlyCreated: true,
            createdAt: now(),
            updatedAt: now()
        );

        $this->vehicleRepository
            ->expects($this->once())
            ->method('firstOrCreate')
            ->willReturn($vehicleDTO);

        $spotDTO = new ParkingSpotDTO(
            id: 1,
            code: 'A-01',
            status: ParkingSpotStatus::DISPONIVEL
        );

        $this->spotRepository
            ->expects($this->once())
            ->method('findAvailableSpot')
            ->willReturn($spotDTO);

        $this->spotRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($spotDTO->id, ParkingSpotStatus::OCUPADO);

        $ticketDTO = new TicketDTO(
            id: 'test-uuid',
            vehicleId: 1,
            spotId: 1,
            entryAt: now(),
            status: TicketStatus::ABERTO,
            gateId: $gateId,
            exitAt: null,
            createdAt: now(),
            updatedAt: now()
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($ticketDTO);

        $ticketWithRelations = new TicketWithRelationsDTO(
            ticket: $ticketDTO,
            vehicle: $vehicleDTO,
            spot: $spotDTO
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('findByIdWithRelations')
            ->with($ticketDTO->id)
            ->willReturn($ticketWithRelations);

        $result = $this->action->execute($plate, $gateId);

        $this->assertInstanceOf(TicketWithRelationsDTO::class, $result);
        $this->assertEquals($plate, $result->vehicle->plate);
        $this->assertEquals(TicketStatus::ABERTO, $result->ticket->status);
        $this->assertEquals($gateId, $result->ticket->gateId);

        Queue::assertPushed(FetchVehicleDetailsJob::class, function ($job) use ($vehicleDTO) {
            return $job->vehicleId === $vehicleDTO->id;
        });
    }

    /** @test */
    public function it_registers_existing_vehicle_without_model_data()
    {
        Queue::fake();

        $plate = 'XYZ9999';
        $gateId = 'entrada-2';

        $existingVehicle = new VehicleDTO(
            id: 5,
            plate: $plate,
            type: 'Carro',
            brand: null,
            model: null,
            color: null,
            wasRecentlyCreated: false,
            createdAt: now()->subDays(1),
            updatedAt: now()->subDays(1)
        );

        $this->vehicleRepository
            ->expects($this->once())
            ->method('findByPlate')
            ->with($plate)
            ->willReturn($existingVehicle);

        $this->ticketRepository
            ->expects($this->once())
            ->method('findActiveTicketForVehicle')
            ->with($existingVehicle->id)
            ->willReturn(null);

        $this->ticketRepository
            ->expects($this->once())
            ->method('countActiveTickets')
            ->willReturn(30);

        $this->vehicleRepository
            ->expects($this->once())
            ->method('firstOrCreate')
            ->willReturn($existingVehicle);

        $spotDTO = new ParkingSpotDTO(
            id: 2,
            code: 'A-02',
            status: ParkingSpotStatus::DISPONIVEL
        );

        $this->spotRepository
            ->expects($this->once())
            ->method('findAvailableSpot')
            ->willReturn($spotDTO);

        $this->spotRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($spotDTO->id, ParkingSpotStatus::OCUPADO);

        $ticketDTO = new TicketDTO(
            id: 'test-uuid-2',
            vehicleId: $existingVehicle->id,
            spotId: $spotDTO->id,
            entryAt: now(),
            status: TicketStatus::ABERTO,
            gateId: $gateId,
            exitAt: null,
            createdAt: now(),
            updatedAt: now()
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($ticketDTO);

        $ticketWithRelations = new TicketWithRelationsDTO(
            ticket: $ticketDTO,
            vehicle: $existingVehicle,
            spot: $spotDTO
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('findByIdWithRelations')
            ->willReturn($ticketWithRelations);

        $result = $this->action->execute($plate, $gateId);

        $this->assertEquals($existingVehicle->id, $result->vehicle->id);

        Queue::assertPushed(FetchVehicleDetailsJob::class);
    }

    /** @test */
    public function it_does_not_dispatch_job_when_vehicle_has_complete_data()
    {
        Queue::fake();

        $plate = 'DEF5678';
        $gateId = 'entrada-1';

        $existingVehicle = new VehicleDTO(
            id: 10,
            plate: $plate,
            type: 'Carro',
            brand: 'Toyota',
            model: 'Corolla',
            color: 'Preto',
            wasRecentlyCreated: false,
            createdAt: now()->subDays(5),
            updatedAt: now()->subDays(5)
        );

        $this->vehicleRepository
            ->expects($this->once())
            ->method('findByPlate')
            ->willReturn($existingVehicle);

        $this->ticketRepository
            ->expects($this->once())
            ->method('findActiveTicketForVehicle')
            ->willReturn(null);

        $this->ticketRepository
            ->expects($this->once())
            ->method('countActiveTickets')
            ->willReturn(25);

        $this->vehicleRepository
            ->expects($this->once())
            ->method('firstOrCreate')
            ->willReturn($existingVehicle);

        $spotDTO = new ParkingSpotDTO(
            id: 3,
            code: 'B-01',
            status: ParkingSpotStatus::DISPONIVEL
        );

        $this->spotRepository
            ->expects($this->once())
            ->method('findAvailableSpot')
            ->willReturn($spotDTO);

        $this->spotRepository
            ->expects($this->once())
            ->method('updateStatus');

        $ticketDTO = new TicketDTO(
            id: 'test-uuid-3',
            vehicleId: $existingVehicle->id,
            spotId: $spotDTO->id,
            entryAt: now(),
            status: TicketStatus::ABERTO,
            gateId: $gateId,
            exitAt: null,
            createdAt: now(),
            updatedAt: now()
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($ticketDTO);

        $ticketWithRelations = new TicketWithRelationsDTO(
            ticket: $ticketDTO,
            vehicle: $existingVehicle,
            spot: $spotDTO
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('findByIdWithRelations')
            ->willReturn($ticketWithRelations);

        $result = $this->action->execute($plate, $gateId);

        Queue::assertNotPushed(FetchVehicleDetailsJob::class);
    }

    /** @test */
    public function it_throws_exception_when_vehicle_already_in_parking()
    {
        $plate = 'GHI9012';
        $gateId = 'entrada-1';

        $existingVehicle = new VehicleDTO(
            id: 15,
            plate: $plate,
            type: 'Carro',
            brand: 'Honda',
            model: 'Civic',
            color: 'Branco',
            wasRecentlyCreated: false,
            createdAt: now()->subDays(2),
            updatedAt: now()->subDays(2)
        );

        $this->vehicleRepository
            ->expects($this->once())
            ->method('findByPlate')
            ->willReturn($existingVehicle);

        $activeTicket = new TicketDTO(
            id: 'active-ticket-uuid',
            vehicleId: $existingVehicle->id,
            spotId: 5,
            entryAt: now()->subHour(),
            status: TicketStatus::ABERTO,
            gateId: 'entrada-1',
            exitAt: null,
            createdAt: now()->subHour(),
            updatedAt: now()->subHour()
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('findActiveTicketForVehicle')
            ->with($existingVehicle->id)
            ->willReturn($activeTicket);

        $this->ticketRepository
            ->expects($this->never())
            ->method('countActiveTickets');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Veículo placa {$plate} já está no pátio.");
        $this->expectExceptionCode(409);

        $this->action->execute($plate, $gateId);
    }

    /** @test */
    public function it_throws_exception_when_parking_is_full()
    {
        $plate = 'JKL3456';
        $gateId = 'entrada-1';

        $this->vehicleRepository
            ->expects($this->once())
            ->method('findByPlate')
            ->willReturn(null);

        $this->ticketRepository
            ->expects($this->once())
            ->method('countActiveTickets')
            ->willReturn(100); // MAX_CAPACITY = 100

        $this->vehicleRepository
            ->expects($this->never())
            ->method('firstOrCreate');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Estacionamento lotado.");
        $this->expectExceptionCode(422);

        $this->action->execute($plate, $gateId);
    }

    /** @test */
    public function it_throws_exception_when_no_spot_available()
    {
        $plate = 'MNO7890';
        $gateId = 'entrada-1';

        $this->vehicleRepository
            ->expects($this->once())
            ->method('findByPlate')
            ->willReturn(null);

        $this->ticketRepository
            ->expects($this->once())
            ->method('countActiveTickets')
            ->willReturn(95);

        $vehicleDTO = new VehicleDTO(
            id: 20,
            plate: $plate,
            type: 'Carro',
            brand: null,
            model: null,
            color: null,
            wasRecentlyCreated: true,
            createdAt: now(),
            updatedAt: now()
        );

        $this->vehicleRepository
            ->expects($this->once())
            ->method('firstOrCreate')
            ->willReturn($vehicleDTO);

        $this->spotRepository
            ->expects($this->once())
            ->method('findAvailableSpot')
            ->willReturn(null);

        $this->spotRepository
            ->expects($this->never())
            ->method('updateStatus');

        $this->ticketRepository
            ->expects($this->never())
            ->method('create');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Nenhuma vaga disponível no momento.");
        $this->expectExceptionCode(422);

        $this->action->execute($plate, $gateId);
    }
}
