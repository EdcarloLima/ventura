# 🚗 Parking Ventura - Sistema de Gerenciamento de Estacionamento

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Redis-Cache-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis">
</p>

Sistema completo de gerenciamento de estacionamento desenvolvido com Laravel 11, Docker, e arquitetura DDD (Domain-Driven Design).

---

## 🎯 MVP 0 - Funcionalidades

- ✅ **Registro de entrada** de veículos pela placa
- ✅ **Alocação automática** de vagas (A-01 até J-10)
- ✅ **Criação automática** de tickets
- ✅ **Busca assíncrona** de dados do veículo (Detran/BrasilAPI)
- ✅ **API REST** completa
- ✅ **100 vagas** de estacionamento
- ✅ **Arquitetura DDD** com enums e actions
- ✅ **Filas assíncronas** com Redis

---

## 🚀 Instalação Rápida (First Install)

### 1. Clone o Repositório
```bash
git clone https://github.com/EdcarloLima/ventura.git
cd ventura
```

### 2. Configure o Ambiente
```bash
cp .env.example .env
```

**Edite as variáveis importantes no `.env`:**
```bash
DB_DATABASE=parking
DB_USERNAME=root
DB_PASSWORD=root

QUEUE_CONNECTION=redis

NGROK_AUTHTOKEN=seu_token_aqui
NGROK_DOMAIN=seu-dominio.ngrok-free.dev
```

### 3. Suba os Containers
```bash
docker compose up -d
```

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
- ✅ Executar migrations
- ✅ Criar 100 vagas (A-01 até J-10)
- ✅ Exibir estatísticas

### 7. Verifique os Containers
```bash
docker compose ps
```

Todos devem estar **Up**:
- `parking-app` - Laravel
- `parking-nginx` - Servidor web
- `parking-db` - MySQL
- `parking-redis` - Cache/Filas
- `parking-queue` - Worker
- `parking-ngrok` - Túnel público

---

## 🧪 Testando o Sistema

### Via API (Postman/Insomnia)

**Importe a coleção:** `Parking-Ventura-MVP0.postman_collection.json`

Ou use diretamente:

**Registrar Veículo:**
```bash
curl -X POST http://localhost:8080/api/vehicles/entry \
  -H "Content-Type: application/json" \
  -d '{"plate":"ABC1234"}'
```

**Ver Estatísticas:**
```bash
curl http://localhost:8080/api/stats
```

**Listar Tickets Ativos:**
```bash
curl http://localhost:8080/api/tickets/active
```

**Listar Vagas Disponíveis:**
```bash
curl http://localhost:8080/api/spots/available
```

### Via Tinker (Laravel)

```bash
docker compose exec app php artisan tinker
```

```php
$action = app(App\Domain\Parking\Actions\RegisterEntryAction::class);
$ticket = $action->execute("ABC1234", "entrada-1");
dd($ticket->parkingSpot->code); // "A-01"
```

---

## 📋 API Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/vehicles/entry` | Registrar entrada de veículo |
| GET | `/api/stats` | Estatísticas do sistema |
| GET | `/api/spots/available` | Listar vagas disponíveis |
| GET | `/api/tickets/active` | Listar tickets ativos |
| GET | `/api/tickets/{id}` | Consultar ticket específico |

---

## 🏗️ Arquitetura

### Estrutura DDD

```
app/
├── Domain/
│   ├── Parking/
│   │   ├── Actions/          # RegisterEntryAction, PerformCheckoutAction
│   │   ├── Enums/            # TicketStatus, ParkingSpotStatus, VehicleType
│   │   ├── Models/           # Ticket, ParkingSpot
│   │   ├── Listeners/        # AuthorizeExitListener
│   │   └── Events/           # PaymentConfirmed
│   ├── Payment/
│   │   ├── Enums/            # PaymentMethod, PaymentStatus
│   │   ├── Gateways/         # MercadoPagoGateway
│   │   └── Models/           # Payment
│   └── Vehicle/
│       ├── Enums/            # VehicleType
│       ├── Models/           # Vehicle
│       └── Jobs/             # FetchVehicleDetailsJob
├── Infrastructure/
│   ├── Adapters/             # DetranApiAdapter
│   ├── Barrier/              # BarrierControlService
│   └── DTOs/                 # VehicleDto, PaymentRegisterDto
```

### Tecnologias

- **Backend:** Laravel 11 + PHP 8.3
- **Banco de Dados:** MySQL 8.0
- **Cache/Filas:** Redis
- **Servidor Web:** Nginx
- **Containerização:** Docker + Docker Compose
- **Túnel:** Ngrok (webhooks)
- **Pagamentos:** MercadoPago SDK v3
- **API Externa:** BrasilAPI (Detran)

---

## 📊 Banco de Dados

```sql
parking_spots (100 registros)
├── code (A-01 até J-10)
├── status (Disponível/Ocupado/Manutenção)
└── type

vehicles
├── plate
├── type (enums)
├── brand (via job assíncrono)
└── model

tickets (UUID)
├── vehicle_id
├── spot_id
├── entry_at
├── exit_at
├── status (enums)
└── total_amount

payments
├── ticket_id
├── amount
├── method (enums)
├── status (enums)
└── gateway_transaction_id
```

---

## 🔍 Monitoramento

### Logs em Tempo Real
```bash
docker compose logs -f app
docker compose logs -f queue
```

### Acessar MySQL
```bash
docker compose exec db mysql -u root -proot parking
```

### Comandos Úteis
```bash
# Limpar cache
docker compose exec app php artisan cache:clear

# Reexecutar migrations
docker compose exec app php artisan migrate:fresh --seed

# Resetar instalação
docker compose exec app php artisan app:first-install --force
```

---

## 🌐 URLs de Acesso

- **Local:** http://localhost:8080
- **Ngrok:** https://seu-dominio.ngrok-free.dev
- **Ngrok Dashboard:** http://localhost:4040
- **MySQL:** localhost:3307

---

## 📦 Postman Collection

A coleção do Postman está incluída no projeto:

📄 **`Parking-Ventura-MVP0.postman_collection.json`**

**7 requisições prontas:**
1. Registrar Entrada - ABC1234
2. Registrar Entrada - XYZ9876
3. Estatísticas do Sistema
4. Listar Vagas Disponíveis
5. Listar Tickets Ativos
6. Consultar Ticket Específico
7. Teste Placa Inválida (422)

### Como Importar:

**No Postman VS Code:**
1. Clique no ícone 📮 Postman na barra lateral
2. Clique em **Import**
3. Selecione: `Parking-Ventura-MVP0.postman_collection.json`
4. Clique em **Import**

**Ou use o Postman Desktop:**
1. Abra o Postman
2. File → Import
3. Selecione o arquivo
4. Pronto!

---

## 📚 Documentação Completa

- 📖 **[MVP0-README.md](MVP0-README.md)** - Guia completo do MVP 0
- 🧪 **[TESTE_FUNCIONAL_RESULTADO.md](TESTE_FUNCIONAL_RESULTADO.md)** - Relatório de testes
- 📮 **[POSTMAN_GUIA.md](POSTMAN_GUIA.md)** - Guia do Postman
- 📝 **[POSTMAN_MANUAL.md](POSTMAN_MANUAL.md)** - Criar requisições manualmente

---

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

---

## 📝 Próximos Passos (Roadmap)

- [ ] Implementar cálculo de preço por tempo
- [ ] Sistema completo de checkout
- [ ] Integração PIX (MercadoPago)
- [ ] Webhooks de pagamento
- [ ] Controle de cancelas (hardware)
- [ ] Dashboard administrativo
- [ ] Relatórios e métricas
- [ ] API de relatórios
- [ ] Notificações (email/SMS)
- [ ] App mobile

---

## 👨‍💻 Desenvolvedor

**Edcarlo Lima**
- GitHub: [@EdcarloLima](https://github.com/EdcarloLima)
- Projeto: Desafio de Mentoria

---

## 📄 Licença

Este projeto foi desenvolvido como desafio de mentoria.

---

## 🚀 Stack Tecnológico

```
Laravel 11.46.1
PHP 8.3.28
MySQL 8.0
Redis (Cache/Queue)
Nginx
Docker & Docker Compose
MercadoPago SDK v3.7.1
BrasilAPI
Ngrok
```

---

**🎉 Sistema pronto para uso e demonstração!**

Para mais detalhes, consulte a [documentação completa](MVP0-README.md).
