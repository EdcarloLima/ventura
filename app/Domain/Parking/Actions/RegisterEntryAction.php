<?php

namespace App\Domain\Parking\Actions;

use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Domain\Parking\Models\ParkingSpot;
use App\Domain\Parking\Models\Ticket;
use App\Domain\Vehicle\Enums\VehicleType;
use App\Domain\Vehicle\Models\Vehicle;
use App\Jobs\FetchVehicleDetailsJob;
use Illuminate\Support\Facades\DB;
use Exception;

class RegisterEntryAction
{
    // TO-DO: Move to config)
    private const MAX_CAPACITY = 100;

    public function execute(string $plate, string $gateId): Ticket
    {
        return DB::transaction(function () use ($plate, $gateId) {
            
            // 1. Verifica se existe algum ticket ativo para esta placa
            $vehicle = Vehicle::where('plate', $plate)->first();
            
            if ($vehicle) {
                $activeTicket = Ticket::where('vehicle_id', $vehicle->id)
                    ->whereIn('status', [
                        TicketStatus::ABERTO,
                        TicketStatus::PAGAMENTO_PENDENTE,
                        TicketStatus::PAGO
                    ])
                    ->first();

                if ($activeTicket) {
                    throw new Exception("Veículo placa {$plate} já está no pátio.", 409);
                }
            }

            // 2. Validação de Capacidade
            $currentCount = Ticket::whereIn('status', [
                TicketStatus::ABERTO,
                TicketStatus::PAGAMENTO_PENDENTE,
                TicketStatus::PAGO
            ])->count();

            if ($currentCount >= self::MAX_CAPACITY) {
                throw new Exception("Estacionamento lotado.", 422);
            }

            // 3. Cria ou Recupera o Veículo
            $vehicle = Vehicle::firstOrCreate(
                ['plate' => $plate]
            );

            // 4. Dispara Job Assíncrono
            // Apenas se o veículo for novo ou tiver dados incompletos
            if ($vehicle->wasRecentlyCreated || is_null($vehicle->model)) {
                FetchVehicleDetailsJob::dispatch($vehicle);
            }

            // 5. Busca uma Vaga Disponível
            $parkingSpot = $this->findAvailableSpot();

            if (!$parkingSpot) {
                throw new Exception("Nenhuma vaga disponível no momento.", 422);
            }

            // 6. Atualiza o status da vaga para OCUPADO
            $parkingSpot->update(['status' => ParkingSpotStatus::OCUPADO]);

            // 7. Gera o Ticket de Entrada
            $ticket = Ticket::create([
                'vehicle_id' => $vehicle->id,
                'spot_id'    => $parkingSpot->id,
                'entry_at'   => now(),
                'status'     => TicketStatus::ABERTO,
                'gate_id'    => $gateId
            ]);

            return $ticket;
        });
    }

    /**
     * Encontra uma vaga disponível.
     * Prioriza vagas com status DISPONIVEL e sem tickets ativos.
     */
    private function findAvailableSpot(): ?ParkingSpot
    {
        return ParkingSpot::where('status', ParkingSpotStatus::DISPONIVEL)
            ->whereDoesntHave('tickets', function ($query) {
                $query->whereIn('status', [
                    TicketStatus::ABERTO,
                    TicketStatus::PAGAMENTO_PENDENTE,
                    TicketStatus::PAGO
                ]);
            })
            ->lockForUpdate()
            ->first();
    }
}