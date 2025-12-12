<?php

namespace App\Console\Commands;

use App\Domain\Parking\Actions\RegisterEntryAction;
use Illuminate\Console\Command;
use Exception;

class RegisterVehicleEntryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:entry 
                            {plate : Placa do veículo (formato: ABC1234)}
                            {--gate= : ID do portão de entrada (padrão: entrada-1)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registra a entrada de um veículo no estacionamento';

    /**
     * Execute the console command.
     */
    public function handle(RegisterEntryAction $action): int
    {
        $plate = strtoupper($this->argument('plate'));
        $gateId = $this->option('gate') ?? 'entrada-1';

        // Validar formato da placa
        if (!preg_match('/^[A-Z]{3}[0-9]{4}$/', $plate)) {
            $this->error('❌ Formato de placa inválido!');
            $this->warn('   Use o formato: ABC1234 (3 letras + 4 números)');
            return Command::FAILURE;
        }

        $this->info("🚗 Registrando entrada do veículo {$plate}...");

        try {
            $result = $action->execute($plate, $gateId);

            $this->newLine();
            $this->info('✅ Veículo registrado com sucesso!');
            $this->newLine();

            // Exibir informações do ticket
            $this->line('<fg=cyan>╔═══════════════════════════════════════════════╗</>');
            $this->line('<fg=cyan>║</> <options=bold>TICKET DE ENTRADA</>                        <fg=cyan>║</>');
            $this->line('<fg=cyan>╠═══════════════════════════════════════════════╣</>');
            $this->line(sprintf(
                '<fg=cyan>║</> Ticket ID: <fg=yellow>%-31s</> <fg=cyan>║</>',
                $result->ticket->id
            ));
            $this->line(sprintf(
                '<fg=cyan>║</> Placa:     <fg=white;options=bold>%-31s</> <fg=cyan>║</>',
                $result->vehicle->plate
            ));
            $this->line(sprintf(
                '<fg=cyan>║</> Tipo:      <fg=white>%-31s</> <fg=cyan>║</>',
                $result->vehicle->type
            ));
            $this->line(sprintf(
                '<fg=cyan>║</> Vaga:      <fg=green;options=bold>%-31s</> <fg=cyan>║</>',
                $result->spot->code
            ));
            $this->line(sprintf(
                '<fg=cyan>║</> Entrada:   <fg=white>%-31s</> <fg=cyan>║</>',
                $result->ticket->entryAt->format('d/m/Y H:i:s')
            ));
            $this->line(sprintf(
                '<fg=cyan>║</> Status:    <fg=green>%-31s</> <fg=cyan>║</>',
                $result->ticket->status
            ));
            $this->line(sprintf(
                '<fg=cyan>║</> Portão:    <fg=white>%-31s</> <fg=cyan>║</>',
                $result->ticket->gateId ?? 'N/A'
            ));
            $this->line('<fg=cyan>╚═══════════════════════════════════════════════╝</>');
            $this->newLine();

            if ($result->vehicle->wasRecentlyCreated || is_null($result->vehicle->model)) {
                $this->comment('ℹ️  Buscando dados adicionais do veículo...');
            }

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->newLine();
            $this->error('❌ ' . $e->getMessage());
            $this->newLine();
            return Command::FAILURE;
        }
    }
}
