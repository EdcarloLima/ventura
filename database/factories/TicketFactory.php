<?php

namespace Database\Factories;

use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => null, 
            'spot_id' => null, 
            'entry_at' => now(),
            'status' => TicketStatus::ABERTO,
            'exit_at' => null,
            'paid_at' => null,
            'total_amount' => null,
        ];
    }
}
