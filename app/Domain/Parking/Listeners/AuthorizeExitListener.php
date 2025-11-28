<?php

namespace App\Domain\Parking\Listeners;

use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Infrastructure\Barrier\BarrierControlService;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domain\Payment\Events\PaymentConfirmed;

class AuthorizeExitListener implements ShouldQueue
{
    public function __construct(
        protected BarrierControlService $barrierService
    ) {}

    public function handle(PaymentConfirmed $event): void
    {
        try {
            Log::info("Processando pagamento confirmado", [
                'transaction_id' => $event->transactionId,
                'amount' => $event->amount,
            ]);

            // 1. Encontra o ticket pelo ID da transação externa
            $ticket = Ticket::whereHas('payments', function ($query) use ($event) {
                $query->where('gateway_transaction_id', $event->transactionId);
            })->with(['parkingSpot', 'vehicle'])->first();

            if (!$ticket) {
                Log::warning("Pagamento confirmado mas ticket não encontrado", [
                    'transaction_id' => $event->transactionId,
                ]);
                return;
            }

            Log::info("Ticket encontrado", [
                'ticket_id' => $ticket->id,
                'vehicle_plate' => $ticket->vehicle->plate ?? 'N/A',
                'spot_code' => $ticket->parkingSpot->code ?? 'N/A',
            ]);

            // 2. Atualiza status do Ticket
            $ticket->update([
                'status' => TicketStatus::PAGO,
                'paid_at' => now(),
            ]);

            // 3. Libera a vaga (atualiza status para DISPONIVEL)
            if ($ticket->parkingSpot) {
                $ticket->parkingSpot->update([
                    'status' => ParkingSpotStatus::DISPONIVEL,
                ]);

                Log::info("Vaga liberada", [
                    'spot_id' => $ticket->parkingSpot->id,
                    'spot_code' => $ticket->parkingSpot->code,
                ]);
            }

            // 4. Comando para Hardware: ABRIR CANCELA DE SAÍDA
            $gateId = $ticket->gate_id ?? 'default';
            $barrierOpened = $this->barrierService->openExitBarrier($gateId);

            if ($barrierOpened) {
                Log::info("Cancela de saída aberta com sucesso", [
                    'gate_id' => $gateId,
                    'ticket_id' => $ticket->id,
                ]);
            } else {
                Log::error("Falha ao abrir cancela de saída", [
                    'gate_id' => $gateId,
                    'ticket_id' => $ticket->id,
                ]);
            }

        } catch (\Throwable $e) {
            Log::error("Erro ao processar pagamento confirmado", [
                'transaction_id' => $event->transactionId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}