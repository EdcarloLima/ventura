<?php

namespace App\Infrastructure\MercadoPago;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use App\Domain\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\Payment\DataTransferObjects\PaymentRegisterDto;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    private PaymentClient $paymentClient;

    public function __construct(
        private ?string $accessToken = null,
    ) {
        $this->accessToken ??= config('services.mercadopago.access_token') ?? '';

        if (empty($this->accessToken)) {
            throw new Exception("MercadoPago access token não configurado. Defina MERCADOPAGO_ACCESS_TOKEN no .env");
        }

        // Configurar MercadoPago SDK v3
        MercadoPagoConfig::setAccessToken($this->accessToken);
        
        $this->paymentClient = new PaymentClient();
    }

    public function createPixPayment(float $amount, string $description, string $payerEmail): PaymentRegisterDto
    {
        try {
            Log::info("Criando pagamento PIX no MercadoPago", [
                'amount' => $amount,
                'email' => $payerEmail,
            ]);

            $request = [
                'transaction_amount' => $amount,
                'description' => $description,
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $payerEmail,
                ],
            ];

            $payment = $this->paymentClient->create($request);

            Log::info("Pagamento PIX criado com sucesso", [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            $transactionData = $payment->point_of_interaction->transaction_data;

            return new PaymentRegisterDto(
                pixPayload: $transactionData->qr_code,
                qrCodeBase64: $transactionData->qr_code_base64,
                gatewayId: (string) $payment->id
            );

        } catch (MPApiException $e) {
            Log::error("Erro na API do MercadoPago", [
                'message' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'api_response' => $e->getApiResponse(),
            ]);

            throw new Exception("Gateway Error: " . $e->getMessage(), $e->getStatusCode(), $e);
            
        } catch (\Throwable $e) {
            Log::error("Erro ao criar pagamento PIX", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }
}