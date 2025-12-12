<?php

namespace Database\Seeders;

use App\Domain\Parking\Models\ParkingSpot;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use Illuminate\Database\Seeder;

class ParkingSpotSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar vagas existentes
        ParkingSpot::query()->delete();

        $spots = [];
        
        // Setor A - 25 vagas
        for ($i = 1; $i <= 25; $i++) {
            $spots[] = [
                'code' => sprintf('A-%02d', $i),
                'status' => ParkingSpotStatus::DISPONIVEL,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Setor B - 25 vagas
        for ($i = 1; $i <= 25; $i++) {
            $spots[] = [
                'code' => sprintf('B-%02d', $i),
                'status' => ParkingSpotStatus::DISPONIVEL,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Setor C - 25 vagas
        for ($i = 1; $i <= 25; $i++) {
            $spots[] = [
                'code' => sprintf('C-%02d', $i),
                'status' => ParkingSpotStatus::DISPONIVEL,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Setor D - 25 vagas
        for ($i = 1; $i <= 25; $i++) {
            $spots[] = [
                'code' => sprintf('D-%02d', $i),
                'status' => ParkingSpotStatus::DISPONIVEL,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Inserir todas as vagas
        ParkingSpot::insert($spots);

        $this->command->info('✅ 100 vagas de estacionamento criadas com sucesso!');
    }
}
