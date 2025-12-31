<?php

namespace Database\Factories;

use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Domain\Parking\Models\ParkingSpot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Parking\Models\ParkingSpot>
 */
class ParkingSpotFactory extends Factory
{
    /**
     * O nome do model correspondente da factory.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ParkingSpot::class;

    /**
     * Contador para gerar códigos sequenciais.
     */
    protected static int $counter = 0;

    /**
     * Número de vagas por setor (padrão: 1000)
     */
    protected static int $spotsPerSector = 1000;

    /**
     * Define o estado padrão do model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->generateCode();

        return [
            'code' => $code,
            'status' => ParkingSpotStatus::DISPONIVEL,
        ];
    }

    /**
     * Gera o código da vaga no formato A-1, A-2, ..., Z-999, AA-1, etc.
     * Suporta quantidade ilimitada de vagas.
     */
    protected function generateCode(): string
    {
        $sectorIndex = floor(self::$counter / self::$spotsPerSector);
        $spotNumber = (self::$counter % self::$spotsPerSector) + 1;
        
        // Gera o código do setor (A, B, C, ..., Z, AA, AB, ...)
        $sectorCode = $this->getSectorCode($sectorIndex);
        
        self::$counter++;
        
        return "{$sectorCode}-{$spotNumber}";
    }

    /**
     * Converte um índice numérico em código de setor (A, B, ..., Z, AA, AB, ...)
     */
    protected function getSectorCode(int $index): string
    {
        $code = '';
        
        do {
            $code = chr(65 + ($index % 26)) . $code;
            $index = floor($index / 26) - 1;
        } while ($index >= 0);
        
        return $code;
    }

    /**
     * Define quantas vagas cada setor terá.
     */
    public function spotsPerSector(int $spots): static
    {
        self::$spotsPerSector = $spots;
        return $this;
    }

    /**
     * Indica que a vaga está ocupada.
     */
    public function ocupado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ParkingSpotStatus::OCUPADO,
        ]);
    }

    /**
     * Indica que a vaga está em manutenção.
     */
    public function manutencao(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ParkingSpotStatus::MANUTENCAO,
        ]);
    }

    /**
     * Reseta o contador para gerar códigos desde o início.
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }
}
