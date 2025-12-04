<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\Vehicle\Models\Vehicle;
use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Models\ParkingSpot;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Jobs\FetchVehicleDetailsJob;
use Illuminate\Support\Facades\Queue;

class RegisterEntryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar vagas para testes
        ParkingSpot::factory()->count(10)->create([
            'status' => ParkingSpotStatus::DISPONIVEL
        ]);
    }

    /** @test */
    public function it_registers_a_new_vehicle_entry_via_api()
    {
        Queue::fake();

        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'ABC1234',
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Veículo registrado com sucesso',
            ])
            ->assertJsonStructure([
                'data' => [
                    'ticket' => ['id', 'entry_at', 'status'],
                    'vehicle' => ['plate', 'type'],
                    'spot' => ['code', 'status'],
                ],
            ]);

        // Verificar se veículo foi criado no banco
        $this->assertDatabaseHas('vehicles', [
            'plate' => 'ABC1234',
            'type' => 'Carro',
        ]);

        // Verificar se ticket foi criado
        $this->assertDatabaseHas('tickets', [
            'status' => TicketStatus::ABERTO,
        ]);

        // Verificar se vaga foi ocupada
        $this->assertDatabaseHas('parking_spots', [
            'status' => ParkingSpotStatus::OCUPADO,
        ]);

        // Verificar se job foi disparado
        Queue::assertPushed(FetchVehicleDetailsJob::class);
    }

    /** @test */
    public function it_prevents_duplicate_entry_for_same_vehicle()
    {
        // Criar veículo e ticket ativo
        $vehicle = Vehicle::factory()->create(['plate' => 'XYZ9999']);
        $spot = ParkingSpot::first();
        
        Ticket::factory()->create([
            'vehicle_id' => $vehicle->id,
            'spot_id' => $spot->id,
            'status' => TicketStatus::ABERTO,
        ]);

        $spot->update(['status' => ParkingSpotStatus::OCUPADO]);

        // Tentar registrar novamente
        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'XYZ9999',
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Veículo placa XYZ9999 já está no pátio.',
            ]);
    }

    /** @test */
    public function it_allows_reentry_after_vehicle_exits()
    {
        Queue::fake();

        // Criar veículo com ticket fechado
        $vehicle = Vehicle::factory()->create(['plate' => 'DEF5678']);
        $spot = ParkingSpot::first();
        
        Ticket::factory()->create([
            'vehicle_id' => $vehicle->id,
            'spot_id' => $spot->id,
            'status' => TicketStatus::CONCLUIDO,
            'exit_at' => now()->subHour(),
        ]);

        $spot->update(['status' => ParkingSpotStatus::DISPONIVEL]);

        // Registrar entrada novamente
        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'DEF5678',
            'gate_id' => 'entrada-2',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Verificar que há 2 tickets para o mesmo veículo
        $this->assertEquals(2, Ticket::where('vehicle_id', $vehicle->id)->count());

        // Verificar que apenas 1 está ativo
        $this->assertEquals(1, Ticket::where('vehicle_id', $vehicle->id)
            ->where('status', TicketStatus::ABERTO)
            ->count());
    }

    /** @test */
    public function it_validates_plate_format()
    {
        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'INVALID',
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plate']);
    }

    /** @test */
    public function it_requires_plate_field()
    {
        $response = $this->postJson('/api/vehicles/entry', [
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plate']);
    }

    /** @test */
    public function it_handles_no_available_spots()
    {
        // Ocupar todas as vagas
        ParkingSpot::query()->update(['status' => ParkingSpotStatus::OCUPADO]);

        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'GHI9012',
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Nenhuma vaga disponível no momento.',
            ]);
    }

    /** @test */
    public function it_dispatches_job_only_for_vehicles_without_complete_data()
    {
        Queue::fake();

        // Criar veículo com dados completos
        $vehicle = Vehicle::factory()->create([
            'plate' => 'JKL3456',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'color' => 'Preto',
        ]);

        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'JKL3456',
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(201);

        // Job NÃO deve ser disparado
        Queue::assertNotPushed(FetchVehicleDetailsJob::class);
    }

    /** @test */
    public function it_uses_default_gate_when_not_provided()
    {
        Queue::fake();

        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'MNO7890',
        ]);

        $response->assertStatus(201);

        // Verificar que ticket foi criado
        $vehicle = Vehicle::where('plate', 'MNO7890')->first();
        $this->assertDatabaseHas('tickets', [
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::ABERTO,
        ]);
    }

    /** @test */
    public function it_prevents_entry_when_parking_is_full()
    {
        // Criar 100 tickets ativos (MAX_CAPACITY)
        $vehicles = Vehicle::factory()->count(100)->create();
        $spots = ParkingSpot::factory()->count(100)->create();

        foreach ($vehicles as $index => $vehicle) {
            Ticket::factory()->create([
                'vehicle_id' => $vehicle->id,
                'spot_id' => $spots[$index]->id,
                'status' => TicketStatus::ABERTO,
            ]);
        }

        $response = $this->postJson('/api/vehicles/entry', [
            'plate' => 'PQR1234',
            'gate_id' => 'entrada-1',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Estacionamento lotado.',
            ]);
    }
}
