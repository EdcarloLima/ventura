<?php

namespace App\Infrastructure\Detran;

use App\Domain\Vehicle\Contracts\VehicleLookupServiceInterface;
use App\Domain\Vehicle\DataTransferObjects\VehicleDto;
use App\Domain\Vehicle\Enums\VehicleType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DetranApiAdapter implements VehicleLookupServiceInterface
{
    public function __construct(
        private ?string $baseUrl = null,
        private ?string $token = null,
        private ?int $timeout = null,
        private ?int $retryTimes = null,
        private ?int $retrySleep = null,
    ) {
        $this->baseUrl ??= config('services.detran.url');
        $this->token ??= config('services.detran.token');
        $this->timeout ??= config('services.detran.timeout');
        $this->retryTimes ??= config('services.detran.retry_times');
        $this->retrySleep ??= config('services.detran.retry_sleep');
    }

    public function findByPlate(string $plate): ?VehicleDto
    {
        try {
            Log::info("Consultando Detran para a placa: {$plate}");

            // BrasilAPI não usa token de autenticação
            $httpClient = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep);

            // Adiciona token apenas se configurado (algumas APIs precisam, BrasilAPI não)
            if (!empty($this->token) && $this->token !== 'demo-token' && $this->token !== 'seutokenaqui') {
                $httpClient = $httpClient->withToken($this->token);
            }

            // BrasilAPI usa formato: /api/placa/v1/{placa}
            // Outras APIs podem usar: /veiculos/{placa}
            $url = $this->buildUrl($plate);
            $response = $httpClient->get($url);

            if ($response->failed()) {
                Log::warning("Detran API falhou ou não encontrou [{$plate}]. Status: " . $response->status());
                return null;
            }

            $data = $response->json();
            
            return new VehicleDto(
                plate: $plate,
                brand: $data['marca'] ?? 'Desconhecida',
                model: $data['modelo'] ?? 'Modelo Desconhecido',
                color: $data['cor'] ?? 'Indefinida',
                category: $this->normalizeType($data['tipo_veiculo'] ?? $data['tipo'] ?? 'CARRO')
            );

        } catch (\Throwable $e) {
            Log::error("Erro ao conectar com Detran", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'plate'   => $plate,
            ]);

            return null;
        }
    }

    /**
     * Constrói a URL correta baseado na API configurada.
     * BrasilAPI: https://brasilapi.com.br/api/placa/v1/{placa}
     * Outras: {baseUrl}/veiculos/{placa}
     */
    private function buildUrl(string $plate): string
    {
        // Se já termina com /v1, é BrasilAPI
        if (str_ends_with($this->baseUrl, '/v1')) {
            return "{$this->baseUrl}/{$plate}";
        }

        // Caso contrário, usa o padrão genérico
        return "{$this->baseUrl}/veiculos/{$plate}";
    }

    /**
     * Normaliza os tipos vindos do Detran para o Enum do nosso sistema.
     * Mapeia os nomes externos para as constantes do VehicleType.
     */
    private function normalizeType(string $externalType): string
    {
        $type = strtoupper(trim($externalType));

        return match (true) {
            str_contains($type, 'MOTO') && !str_contains($type, 'CICLO') => VehicleType::MOTO,
            str_contains($type, 'CICLOMOTOR') => VehicleType::CICLOMOTOR,
            str_contains($type, 'CAMINHAO') || str_contains($type, 'CAMINHÃO') => VehicleType::CAMINHAO,
            str_contains($type, 'CAMINHONETE') => VehicleType::CAMINHONETE,
            str_contains($type, 'ONIBUS') || str_contains($type, 'ÔNIBUS') => VehicleType::ONIBUS,
            str_contains($type, 'TRICICLO') => VehicleType::TRICICLO,
            str_contains($type, 'QUADRICICLO') => VehicleType::QUADRICICLO,
            str_contains($type, 'CARRO') || str_contains($type, 'AUTOMOVEL') || str_contains($type, 'AUTOMÓVEL') => VehicleType::CARRO,
            default => VehicleType::CARRO,
        };
    }
}