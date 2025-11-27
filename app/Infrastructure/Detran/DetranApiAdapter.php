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

            $response = Http::withToken($this->token)
                ->timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/veiculos/{$plate}");

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
                category: $this->normalizeType($data['tipo_veiculo'] ?? 'CARRO')
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