<?php

namespace Tests\Unit\Actions;

use App\Domain\Parking\Actions\RegisterRandomVehicleAction;
use App\Domain\Parking\Actions\RegisterEntryAction;
use App\Domain\Vehicle\Services\PlateGeneratorService;
use App\Domain\Parking\DTOs\TicketWithRelationsDTO;
use App\Domain\Parking\DTOs\TicketDTO;
use App\Domain\Parking\DTOs\ParkingSpotDTO;
use App\Domain\Vehicle\DTOs\VehicleDTO;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use Tests\TestCase;

class RegisterRandomVehicleActionTest extends TestCase
{
    /** @test */
    public function it_generates_random_plate_and_registers_vehicle()
    {
        $plateGenerator = $this->createMock(PlateGeneratorService::class);
        $plateGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('ABC1234');

        $registerEntryAction = $this->createMock(RegisterEntryAction::class);
        
        $expectedResult = new TicketWithRelationsDTO(
            ticket: new TicketDTO(
                id: 'test-uuid',
                vehicleId: 1,
                spotId: 1,
                entryAt: now(),
                status: TicketStatus::ABERTO,
                gateId: 'entrada-1',
                exitAt: null,
                createdAt: now(),
                updatedAt: now()
            ),
            vehicle: new VehicleDTO(
                id: 1,
                plate: 'ABC1234',
                type: 'Carro',
                brand: null,
                model: null,
                color: null,
                wasRecentlyCreated: true,
                createdAt: now(),
                updatedAt: now()
            ),
            spot: new ParkingSpotDTO(
                id: 1,
                code: 'A-01',
                status: ParkingSpotStatus::OCUPADO
            )
        );

        $registerEntryAction
            ->expects($this->once())
            ->method('execute')
            ->with('ABC1234', 'entrada-1')
            ->willReturn($expectedResult);

        $action = new RegisterRandomVehicleAction($plateGenerator, $registerEntryAction);
        $result = $action->execute();

        $this->assertInstanceOf(TicketWithRelationsDTO::class, $result);
        $this->assertEquals('ABC1234', $result->vehicle->plate);
        $this->assertEquals(TicketStatus::ABERTO, $result->ticket->status);
    }

    /** @test */
    public function it_uses_custom_gate_id_when_provided()
    {
        $plateGenerator = $this->createMock(PlateGeneratorService::class);
        $plateGenerator->method('generate')->willReturn('XYZ9999');

        $registerEntryAction = $this->createMock(RegisterEntryAction::class);
        
        $expectedResult = new TicketWithRelationsDTO(
            ticket: new TicketDTO(
                id: 'test-uuid-2',
                vehicleId: 2,
                spotId: 2,
                entryAt: now(),
                status: TicketStatus::ABERTO,
                gateId: 'entrada-2',
                exitAt: null,
                createdAt: now(),
                updatedAt: now()
            ),
            vehicle: new VehicleDTO(
                id: 2,
                plate: 'XYZ9999',
                type: 'Carro',
                brand: null,
                model: null,
                color: null,
                wasRecentlyCreated: true,
                createdAt: now(),
                updatedAt: now()
            ),
            spot: new ParkingSpotDTO(
                id: 2,
                code: 'A-02',
                status: ParkingSpotStatus::OCUPADO
            )
        );

        $registerEntryAction
            ->expects($this->once())
            ->method('execute')
            ->with('XYZ9999', 'entrada-2')
            ->willReturn($expectedResult);

        $action = new RegisterRandomVehicleAction($plateGenerator, $registerEntryAction);
        $result = $action->execute('entrada-2');

        $this->assertEquals('XYZ9999', $result->vehicle->plate);
        $this->assertEquals('entrada-2', $result->ticket->gateId);
    }

    /** @test */
    public function it_uses_default_gate_when_not_provided()
    {
        $plateGenerator = $this->createMock(PlateGeneratorService::class);
        $plateGenerator->method('generate')->willReturn('DEF4567');

        $registerEntryAction = $this->createMock(RegisterEntryAction::class);
        
        $registerEntryAction
            ->expects($this->once())
            ->method('execute')
            ->with('DEF4567', 'entrada-1');

        $action = new RegisterRandomVehicleAction($plateGenerator, $registerEntryAction);
        $action->execute();
    }
}
