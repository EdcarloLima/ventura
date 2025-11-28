# 🚗 Parking Ventura - MVP 0

Sistema de Gerenciamento de Estacionamento com Laravel 11 + Docker

## 🎯 Sobre o MVP 0

Este é o MVP inicial desenvolvido, demonstrando:

- ✅ Registro de entrada de veículos pela placa
- ✅ Alocação automática de vagas
- ✅ Criação de tickets
- ✅ Busca assíncrona de dados do veículo (Detran/BrasilAPI)
- ✅ Arquitetura DDD (Domain-Driven Design)
- ✅ 100 vagas (A-01 até J-10)

## 🚀 First Install

### 1. Clone o Repositório
```bash
git clone https://github.com/EdcarloLima/ventura.git
cd ventura
```

### 2. Configure o Ambiente
```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite as variáveis necessárias (se precisar)
nano .env
```

**Variáveis importantes:**
```bash
DB_DATABASE=parking
DB_USERNAME=root
DB_PASSWORD=root

QUEUE_CONNECTION=redis

NGROK_AUTHTOKEN=seu_token_aqui
NGROK_DOMAIN=seu-dominio.ngrok-free.dev
```

### 3. Suba os Containers Docker
```bash
docker compose up -d
```

Aguarde os containers subirem (pode levar alguns minutos na primeira vez).

### 4. Instale as Dependências
```bash
docker compose exec app composer install
```

### 5. Gere a Application Key
```bash
docker compose exec app php artisan key:generate
```

### 6. Execute o First Install
```bash
docker compose exec app php artisan app:first-install
```

Este comando irá:
- ✅ Limpar cache
- ✅ Executar todas as migrations
- ✅ Criar 100 vagas de estacionamento (A-01 até J-10)
- ✅ Exibir estatísticas do sistema

### 7. Verifique os Containers
```bash
docker compose ps
```

Todos devem estar **Up**:
- `parking-app` - Aplicação Laravel
- `parking-nginx` - Servidor web
- `parking-db` - MySQL
- `parking-redis` - Cache e Filas
- `parking-queue` - Worker de filas
- `parking-ngrok` - Túnel público

## 🧪 Testando o MVP 0

### Teste 1: Registro de Entrada via Tinker

```bash
docker compose exec app php artisan tinker
```

```php
// Criar instância da Action
$action = app(App\Domain\Parking\Actions\RegisterEntryAction::class);

// Registrar entrada de um veículo
$ticket = $action->execute("ABC1234", "entrada-1");

// Ver resultado
dd($ticket->toArray());
```

**Resultado esperado:**
```php
[
    "id" => "uuid-do-ticket",
    "vehicle_id" => 1,
    "spot_id" => 1,  // Vaga A-01 alocada
    "entry_at" => "2025-11-28 10:30:00",
    "status" => "Aberto",
    "vehicle" => [
        "plate" => "ABC1234",
        "type" => "Carro"
    ],
    "parking_spot" => [
        "code" => "A-01",
        "status" => "Ocupado"
    ]
]
```

### Teste 2: Via API (Criar Rota)

Crie uma rota de teste rápida:

**`routes/api.php`**
```php
use App\Domain\Parking\Actions\RegisterEntryAction;

Route::post('/vehicles/entry', function (Request $request, RegisterEntryAction $action) {
    $validated = $request->validate([
        'plate' => 'required|string|size:7',
        'gate_id' => 'nullable|string',
    ]);

    $ticket = $action->execute(
        $validated['plate'],
        $validated['gate_id'] ?? 'entrada-1'
    );

    return response()->json([
        'success' => true,
        'ticket' => $ticket,
        'vehicle' => $ticket->vehicle,
        'spot' => $ticket->parkingSpot,
    ]);
});
```

**Teste com cURL:**
```bash
curl -X POST http://localhost:8080/api/vehicles/entry \
  -H "Content-Type: application/json" \
  -d '{"plate":"XYZ9876"}'
```

**Ou via Postman/Insomnia:**
- URL: `http://localhost:8080/api/vehicles/entry`
- Method: `POST`
- Body (JSON):
```json
{
  "plate": "XYZ9876"
}
```

### Teste 3: Verificar Vaga Alocada

```bash
docker compose exec app php artisan tinker
```

```php
use App\Domain\Parking\Models\ParkingSpot;

// Ver todas as vagas
ParkingSpot::all()->pluck('status', 'code');

// Ver vagas ocupadas
ParkingSpot::where('status', 'Ocupado')->get();

// Ver vagas disponíveis
ParkingSpot::where('status', 'Disponível')->count();
```

## 📊 Estrutura do Banco de Dados

```
parking_spots (100 registros)
├── id
├── code (A-01 até J-10)
├── status (Disponível/Ocupado/Manutenção)
└── type

vehicles
├── id
├── plate (ABC1234)
├── type (Carro, Moto, etc)
├── brand (populado via job assíncrono)
└── model

tickets
├── id (UUID)
├── vehicle_id
├── spot_id
├── entry_at
├── exit_at
├── status (Aberto/Pagamento pendente/Pago/Concluído)
└── total_amount

payments
├── id
├── ticket_id
├── amount
├── method (Pix/Crédito/Débito/Dinheiro)
└── status (Pendente/Aprovado/Erro)
```

## 🔍 Monitoramento

### Ver Logs em Tempo Real
```bash
docker compose logs -f app
```

### Ver Fila de Jobs
```bash
docker compose logs -f queue
```

### Acessar MySQL
```bash
docker compose exec db mysql -u root -proot parking
```

```sql
SELECT * FROM parking_spots WHERE status = 'Ocupado';
SELECT * FROM tickets ORDER BY entry_at DESC LIMIT 5;
SELECT * FROM vehicles;
```

## 🌐 URLs de Acesso

- **Local:** http://localhost:8080
- **Ngrok:** https://seu-dominio.ngrok-free.dev
- **Ngrok Dashboard:** http://localhost:4040
- **MySQL:** localhost:3307

## 🎬 Demonstração para a Mentoria

### Roteiro de Apresentação:

1. **Mostrar First Install**
   ```bash
   docker compose exec app php artisan app:first-install
   ```

2. **Mostrar Estrutura do Banco**
   ```bash
   docker compose exec app php artisan tinker
   # ParkingSpot::count() // 100 vagas
   # ParkingSpot::first() // Vaga A-01
   ```

3. **Registrar Primeiro Veículo**
   ```bash
   # Via Tinker ou API
   $ticket = $action->execute("ABC1234", "entrada-1");
   ```

4. **Mostrar Alocação Automática**
   - Vaga automaticamente alocada (A-01)
   - Status da vaga mudou para "Ocupado"
   - Ticket criado com status "Aberto"

5. **Mostrar Job Assíncrono**
   ```bash
   docker compose logs -f queue
   # Verá: "Buscando detalhes para a placa ABC1234"
   ```

6. **Registrar Mais Veículos**
   - Demonstrar que próxima vaga (A-02) é alocada
   - Sistema valida veículo duplicado
   - Sistema verifica capacidade máxima

## 🐛 Solução de Problemas

### Containers não sobem
```bash
docker compose down
docker compose up -d --build
```

### Erro de permissão
```bash
sudo chmod -R 777 storage bootstrap/cache
docker compose exec app php artisan cache:clear
```

### Migrations não executam
```bash
docker compose exec app php artisan migrate:fresh --seed
```

### Refazer instalação
```bash
docker compose exec app php artisan app:first-install --force
```

## 📝 Próximos Passos (Pós-MVP 0)

- [ ] Criar controller e rotas REST completas
- [ ] Implementar cálculo de preço e checkout
- [ ] Integrar pagamento PIX (MercadoPago)
- [ ] Webhook de confirmação de pagamento
- [ ] Controle de cancelas (hardware)
- [ ] Dashboard administrativo
- [ ] Relatórios e métricas

## 👨‍💻 Desenvolvedor

**Edcarlo Lima**
- GitHub: [@EdcarloLima](https://github.com/EdcarloLima)

## 📄 Licença

Este projeto foi desenvolvido como desafio de mentoria.

---

**🎉 Sistema pronto para apresentação do MVP 0!**
