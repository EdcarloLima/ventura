<?php

namespace App\Domain\Parking\Actions;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\Pricing\Contracts\PricingStrategyInterface;

class PerformCheckoutAction
{
    public function __construct(
        protected PricingStrategyInterface $pricingStrategy,
        protected PaymentGatewayInterface $paymentGateway
    ) {}

    public function execute(string $ticketId): array
    {
        $ticket = Ticket::findOrFail($ticketId);

        if ($ticket->status === TicketStatus::PAGO) {
            throw new Exception("Ticket já pago. Saída liberada.", 400);
        }

        if (!$ticket->canPay()) {
            throw new Exception("Ticket não permite pagamento no status atual: {$ticket->status}", 400);
        }
        
        // Se já gerou QR Code recente (menos de 10 min), retorna o mesmo
        return DB::transaction(function () use ($ticket) {
            
            // 1. Define horário de saída
            $exitTime = Carbon::now();

            // 2. Calcula Preço (Usa a Strategy)
            $amount = $this->pricingStrategy->calculate($ticket->entry_at, $exitTime);

            // 3. Atualiza o Ticket com o valor devido
            $ticket->update([
                'total_amount' => $amount,
                'status' => TicketStatus::PAGAMENTO_PENDENTE
            ]);

            // 4. Gera o Pix no Gateway
            $paymentIntent = $this->paymentGateway->createPixPayment(
                amount: $amount,
                description: "Estacionamento Placa {$ticket->vehicle->plate}",
                payerEmail: "cliente@anonimo.com" 
            );

            // 5. Salva a transação no banco
            $ticket->payments()->create([
                'amount' => $amount,
                'method' => PaymentMethod::PIX,
                'gateway_transaction_id' => $paymentIntent->gatewayId,
                'status' => PaymentStatus::PENDENTE
            ]);

            return [
                'ticket_id' => $ticket->id,
                'amount' => $amount,
                'pix' => $paymentIntent->pixPayload,
                'qrcode' => $paymentIntent->qrCodeBase64,
                'calculated_at' => $exitTime->format('Y-m-d H:i:s')
            ];
        });
    }
}