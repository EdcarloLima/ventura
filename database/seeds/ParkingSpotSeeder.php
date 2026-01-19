<?php

namespace Database\Seeders;

use App\Domain\Parking\Models\ParkingSpot;
use Database\Factories\ParkingSpotFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParkingSpotSeeder extends Seeder
{
    /**
     * Popula o banco de dados com as 100 vagas do estacionamento.
     * Padrão: A-01 até J-10 (10 linhas x 10 vagas = 100 vagas)
     */
    public function run(): void
    {
        // Reseta o contador antes de criar as vagas
        ParkingSpotFactory::resetCounter();

        // Cria 100 vagas sequencialmente (A-01 até J-10)
        ParkingSpot::factory()->count(10000)->create();

        //$this->command->info('✅ 100 vagas de estacionamento criadas com sucesso!');
    }
}
