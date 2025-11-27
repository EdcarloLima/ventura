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
            'type' => 'Padrão',
        ];
    }

    /**
     * Gera o código da vaga no formato A-01 até J-10.
     */
    protected function generateCode(): string
    {
        $letter = chr(65 + floor(self::$counter / 10));
        $number = str_pad((self::$counter % 10) + 1, 2, '0', STR_PAD_LEFT);
        
        self::$counter++;
        
        return "{$letter}-{$number}";
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
