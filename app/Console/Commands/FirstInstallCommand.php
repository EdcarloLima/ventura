<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FirstInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:first-install {--force : Força a reinstalação mesmo se já existir dados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configuração inicial do sistema de estacionamento (MVP 0)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚗 Parking Ventura - First Install (MVP 0)');
        $this->newLine();

        // 1. Verificar se já existe dados
        if (!$this->option('force')) {
            try {
                $spotsCount = DB::table('parking_spots')->count();
                if ($spotsCount > 0) {
                    if (!$this->confirm("⚠️  Já existem {$spotsCount} vagas cadastradas. Deseja reinstalar?", false)) {
                        $this->warn('Instalação cancelada.');
                        return Command::FAILURE;
                    }
                }
            } catch (\Exception $e) {
                // Tabela ainda não existe, continua normalmente
            }
        }

        // 2. Limpar cache
        $this->info('🧹 Limpando cache...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        $this->info('✅ Cache limpo');
        $this->newLine();

        // 3. Executar migrations
        $this->info('📦 Executando migrations...');
        $this->newLine();
        
        if ($this->option('force')) {
            Artisan::call('migrate:fresh', ['--force' => true], $this->output);
        } else {
            Artisan::call('migrate', ['--force' => true], $this->output);
        }
        
        $this->newLine();
        $this->info('✅ Migrations executadas com sucesso');
        $this->newLine();

        // 4. Popular vagas de estacionamento
        $this->info('🅿️  Criando 100 vagas de estacionamento (A-01 até J-10)...');
        $this->withProgressBar(100, function () {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ParkingSpotSeeder',
                '--force' => true
            ]);
        });
        $this->newLine(2);
        $this->info('✅ 100 vagas criadas com sucesso!');
        $this->newLine();

        // 5. Verificar estrutura
        $this->info('🔍 Verificando estrutura do banco...');
        $this->displayDatabaseStats();
        $this->newLine();

        // 6. Informações importantes
        $this->displayImportantInfo();

        return Command::SUCCESS;
    }

    /**
     * Exibe estatísticas do banco de dados.
     */
    private function displayDatabaseStats(): void
    {
        try {
            $stats = [
                'Vagas de Estacionamento' => DB::table('parking_spots')->count(),
                'Veículos Cadastrados' => DB::table('vehicles')->count(),
                'Tickets Ativos' => DB::table('tickets')->whereIn('status', ['Aberto', 'Pagamento pendente', 'Pago'])->count(),
                'Tickets Totais' => DB::table('tickets')->count(),
            ];

            $this->table(
                ['Recurso', 'Quantidade'],
                collect($stats)->map(fn($value, $key) => [$key, $value])->toArray()
            );
        } catch (\Exception $e) {
            $this->error("Erro ao consultar estatísticas: {$e->getMessage()}");
        }
    }

    /**
     * Exibe informações importantes para o MVP.
     */
    private function displayImportantInfo(): void
    {
        $this->info('📋 INFORMAÇÕES IMPORTANTES - MVP 0');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->line('🌐 <fg=cyan>URLs de Acesso:</>');
        $this->line('   • Local:  http://localhost:8080');
        $this->line('   • Ngrok:  https://' . env('NGROK_DOMAIN', 'seu-dominio.ngrok-free.dev'));
        $this->line('   • Ngrok Dashboard: http://localhost:4040');
        $this->newLine();

        $this->line('🎯 <fg=cyan>Próximos Passos para Demonstração:</>');
        $this->line('   1. Criar rota POST /api/vehicles/entry');
        $this->line('   2. Testar registro de veículo com placa');
        $this->line('   3. Verificar criação automática de ticket');
        $this->line('   4. Validar alocação de vaga disponível');
        $this->newLine();

        $this->line('📝 <fg=cyan>Comando para Teste Rápido:</>');
        $this->line('   <fg=green>docker compose exec app php artisan tinker</>');
        $this->line('   <fg=yellow>>>> $action = app(App\Domain\Parking\Actions\RegisterEntryAction::class);</>');
        $this->line('   <fg=yellow>>>> $ticket = $action->execute("ABC1234", "entrada-1");</>');
        $this->line('   <fg=yellow>>>> dd($ticket);</>');
        $this->newLine();

        $this->line('🔧 <fg=cyan>Comandos Úteis:</>');
        $this->line('   • Ver logs: <fg=green>docker compose logs -f app</>');
        $this->line('   • Acessar container: <fg=green>docker compose exec app bash</>');
        $this->line('   • Ver filas: <fg=green>docker compose exec app php artisan queue:work</>');
        $this->line('   • Limpar tudo: <fg=green>docker compose exec app php artisan app:first-install --force</>');
        $this->newLine();

        $this->info('✨ Sistema pronto para demonstração do MVP 0!');
        $this->newLine();
    }
}
