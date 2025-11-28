<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use App\Domain\Payment\Events\PaymentConfirmed;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info("Webhook MercadoPago recebido", [
                'query' => $request->query(),
                'body' => $request->all(),
            ]);

            // MercadoPago envia notificações em diferentes formatos
            // Formato 1: ?topic=payment&id=123456789
            // Formato 2: {"type": "payment", "data": {"id": "123456789"}}
            $topic = $request->query('topic') ?? $request->input('type');
            $paymentId = $request->query('id') ?? $request->input('data.id');

            if (!$paymentId) {
                Log::warning("Webhook sem payment ID", [
                    'request' => $request->all()
                ]);

                return response()->json(['status' => 'ignored'], 200);
            }

            // Apenas processar notificações de pagamento
            if ($topic !== 'payment') {
                Log::info("Webhook ignorado. Topic: {$topic}");
                return response()->json(['status' => 'ignored'], 200);
            }

            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
            $paymentClient = new PaymentClient();

            // Consulta o status atual na API para garantir autenticidade
            $payment = $paymentClient->get($paymentId);

            Log::info("Pagamento consultado via API", [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
            ]);

            if ($payment->status === 'approved') {
                Log::info("Pagamento Aprovado via Webhook", [
                    'payment_id' => $payment->id,
                    'amount' => $payment->transaction_amount,
                ]);

                PaymentConfirmed::dispatch(
                    (string) $payment->id,
                    (float) $payment->transaction_amount
                );

                return response()->json(['status' => 'processed'], 200);
            }

            Log::info("Pagamento não aprovado", [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            return response()->json(['status' => 'acknowledged'], 200);

        } catch (MPApiException $e) {
            Log::error("Erro na API do MercadoPago no webhook", [
                'message' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'api_response' => $e->getApiResponse(),
            ]);

            // Retorna 200 para não reenviar o webhook
            return response()->json(['status' => 'error', 'message' => 'API error'], 200);

        } catch (\Throwable $e) {
            Log::error("Erro ao processar webhook MercadoPago", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Retorna 200 para não reenviar o webhook
            return response()->json(['status' => 'error'], 200);
        }
    }
}