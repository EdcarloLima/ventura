<?php

namespace Database\Factories;

use App\Domain\Vehicle\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'plate' => strtoupper($this->faker->bothify('???####')),
            'type' => 'Carro',
            'brand' => $this->faker->optional()->randomElement(['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Volkswagen']),
            'model' => $this->faker->optional()->randomElement(['Civic', 'Corolla', 'Focus', 'Onix', 'Gol']),
            'color' => $this->faker->optional()->randomElement(['Preto', 'Branco', 'Prata', 'Cinza', 'Azul']),
        ];
    }
}
