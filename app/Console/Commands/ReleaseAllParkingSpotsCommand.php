<?php

namespace App\Console\Commands;

use App\Domain\Parking\Models\ParkingSpot;
use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Domain\Parking\Enums\TicketStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseAllParkingSpotsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parking:release-all
                          {--force : Força a liberação sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera todas as vagas do estacionamento, finalizando todos os tickets ativos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚗 Liberação de Vagas do Estacionamento');
        $this->newLine();

        // Contar tickets ativos
        $activeTicketsCount = Ticket::whereIn('status', [
            TicketStatus::ABERTO,
            TicketStatus::PAGAMENTO_PENDENTE,
            TicketStatus::PAGO
        ])->count();

        // Contar vagas ocupadas
        $occupiedSpotsCount = ParkingSpot::where('status', ParkingSpotStatus::OCUPADO)->count();

        if ($activeTicketsCount === 0 && $occupiedSpotsCount === 0) {
            $this->info('✅ Todas as vagas já estão disponíveis!');
            return self::SUCCESS;
        }

        // Mostrar estatísticas
        $this->table(
            ['Tipo', 'Quantidade'],
            [
                ['Tickets Ativos', $activeTicketsCount],
                ['Vagas Ocupadas', $occupiedSpotsCount],
            ]
        );

        // Confirmar ação (se não for --force)
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Deseja realmente liberar todas as vagas?', false)) {
                $this->warn('Operação cancelada.');
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('🔄 Processando liberação...');

        try {
            DB::transaction(function () use ($activeTicketsCount, $occupiedSpotsCount) {
                // 1. Finalizar todos os tickets ativos
                if ($activeTicketsCount > 0) {
                    $updatedTickets = Ticket::whereIn('status', [
                        TicketStatus::ABERTO,
                        TicketStatus::PAGAMENTO_PENDENTE,
                        TicketStatus::PAGO
                    ])->update([
                        'status' => TicketStatus::CONCLUIDO,
                        'exit_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->info("   ✓ {$updatedTickets} tickets finalizados");
                }

                // 2. Liberar todas as vagas ocupadas
                if ($occupiedSpotsCount > 0) {
                    $updatedSpots = ParkingSpot::where('status', ParkingSpotStatus::OCUPADO)
                        ->update([
                            'status' => ParkingSpotStatus::DISPONIVEL,
                            'updated_at' => now(),
                        ]);

                    $this->info("   ✓ {$updatedSpots} vagas liberadas");
                }
            });

            $this->newLine();
            $this->info('✅ Todas as vagas foram liberadas com sucesso!');
            
            // Mostrar estatísticas finais
            $this->newLine();
            $this->info('📊 Estatísticas Finais:');
            $totalSpots = ParkingSpot::count();
            $availableSpots = ParkingSpot::where('status', ParkingSpotStatus::DISPONIVEL)->count();
            
            $this->table(
                ['Tipo', 'Quantidade'],
                [
                    ['Total de Vagas', $totalSpots],
                    ['Vagas Disponíveis', $availableSpots],
                    ['Vagas Ocupadas', 0],
                    ['Tickets Ativos', 0],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao liberar vagas: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
