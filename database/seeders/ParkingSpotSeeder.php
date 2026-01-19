<?php

namespace Database\Seeders;

use App\Domain\Parking\Models\ParkingSpot;
use Database\Factories\ParkingSpotFactory;
use Illuminate\Database\Seeder;

class ParkingSpotSeeder extends Seeder
{
    /**
     * Quantidade total de vagas a criar (padrão: 10.000)
     */
    protected int $totalSpots = 10000;

    /**
     * Quantidade de vagas por setor (padrão: 1000)
     * Exemplo: 10.000 vagas / 1000 por setor = 10 setores (A, B, C, ..., J)
     */
    protected int $spotsPerSector = 1000;

    public function run(): void
    {
        $this->command->info("🚀 Criando {$this->totalSpots} vagas de estacionamento...");
        $this->command->info("📊 Distribuição: {$this->spotsPerSector} vagas por setor");

        // Limpar vagas existentes
        ParkingSpot::query()->delete();

        // Resetar o contador da factory
        ParkingSpotFactory::resetCounter();

        // Criar vagas usando a factory
        ParkingSpot::factory()
            ->spotsPerSector($this->spotsPerSector)
            ->count($this->totalSpots)
            ->create();

        $sectors = ceil($this->totalSpots / $this->spotsPerSector);
        
        $this->command->info("✅ {$this->totalSpots} vagas criadas com sucesso!");
        $this->command->info("🏢 {$sectors} setores criados");
    }

    /**
     * Define a quantidade total de vagas a criar
     */
    public function setTotalSpots(int $spots): self
    {
        $this->totalSpots = $spots;
        return $this;
    }

    /**
     * Define quantas vagas cada setor terá
     */
    public function setSpotsPerSector(int $spots): self
    {
        $this->spotsPerSector = $spots;
        return $this;
    }
}
