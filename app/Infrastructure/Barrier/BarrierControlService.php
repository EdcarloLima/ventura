<?php

namespace App\Infrastructure\Barrier;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BarrierControlService
{
    public function __construct(
        private ?string $baseUrl = null,
        private ?string $apiKey = null,
        private ?int $timeout = null,
    ) {
        $this->baseUrl ??= config('services.barrier.url');
        $this->apiKey ??= config('services.barrier.api_key');
        $this->timeout ??= config('services.barrier.timeout', 10);
    }

    /**
     * Abre a cancela de entrada.
     *
     * @param string $gateId ID do portão/cancela
     * @return bool Sucesso ou falha
     */
    public function openEntryBarrier(string $gateId = 'default'): bool
    {
        return $this->sendCommand($gateId, 'entry', 'open');
    }

    /**
     * Abre a cancela de saída.
     *
     * @param string $gateId ID do portão/cancela
     * @return bool Sucesso ou falha
     */
    public function openExitBarrier(string $gateId = 'default'): bool
    {
        return $this->sendCommand($gateId, 'exit', 'open');
    }

    /**
     * Fecha a cancela (entrada ou saída).
     *
     * @param string $gateId ID do portão/cancela
     * @param string $type 'entry' ou 'exit'
     * @return bool Sucesso ou falha
     */
    public function closeBarrier(string $gateId, string $type = 'exit'): bool
    {
        return $this->sendCommand($gateId, $type, 'close');
    }

    /**
     * Verifica o status da cancela.
     *
     * @param string $gateId ID do portão/cancela
     * @return array Status da cancela (open, closed, error)
     */
    public function getBarrierStatus(string $gateId = 'default'): array
    {
        try {
            Log::info("Consultando status da cancela", ['gate_id' => $gateId]);

            // Se não houver URL configurada, retorna mock
            if (empty($this->baseUrl) || $this->baseUrl === 'mock://barrier') {
                return $this->mockResponse('status', true, [
                    'gate_id' => $gateId,
                    'status' => 'closed',
                    'last_action' => null,
                ]);
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/barriers/{$gateId}/status");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::warning("Falha ao consultar status da cancela", [
                'gate_id' => $gateId,
                'status' => $response->status(),
            ]);

            return ['success' => false, 'error' => 'API error'];

        } catch (\Throwable $e) {
            Log::error("Erro ao consultar status da cancela", [
                'gate_id' => $gateId,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envia comando para a cancela.
     *
     * @param string $gateId ID do portão/cancela
     * @param string $type 'entry' ou 'exit'
     * @param string $action 'open' ou 'close'
     * @return bool Sucesso ou falha
     */
    private function sendCommand(string $gateId, string $type, string $action): bool
    {
        try {
            Log::info("Enviando comando para cancela", [
                'gate_id' => $gateId,
                'type' => $type,
                'action' => $action,
            ]);

            // Se não houver URL configurada, simula sucesso (modo mock)
            if (empty($this->baseUrl) || $this->baseUrl === 'mock://barrier') {
                Log::info("Modo MOCK ativado - Simulando abertura de cancela", [
                    'gate_id' => $gateId,
                    'type' => $type,
                    'action' => $action,
                ]);
                return true;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/barriers/{$gateId}/{$action}", [
                    'type' => $type,
                    'timestamp' => now()->toIso8601String(),
                ]);

            if ($response->successful()) {
                Log::info("Comando executado com sucesso", [
                    'gate_id' => $gateId,
                    'action' => $action,
                ]);
                return true;
            }

            Log::warning("Falha ao executar comando na cancela", [
                'gate_id' => $gateId,
                'action' => $action,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error("Erro ao enviar comando para cancela", [
                'gate_id' => $gateId,
                'type' => $type,
                'action' => $action,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * Retorna resposta mock para testes/desenvolvimento.
     */
    private function mockResponse(string $action, bool $success, array $data = []): array
    {
        return [
            'success' => $success,
            'action' => $action,
            'data' => $data,
            'mock' => true,
        ];
    }
}
