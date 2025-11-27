<?php

namespace App\Domain\Parking\Models;

use Illuminate\Support\Carbon;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Vehicle\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'spot_id',
        'entry_at',
        'paid_at',
        'exit_at',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'paid_at' => 'datetime',
        'exit_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento: Um ticket pertence a um veículo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Relacionamento: Um ticket pertence a uma vaga
     */
    public function parkingSpot(): BelongsTo
    {
        return $this->belongsTo(ParkingSpot::class, 'spot_id');
    }

    /**
     * Relacionamento: Um ticket pode ter vários pagamentos
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verifica se o ticket já está pago.
     */
    public function isPaid(): bool
    {
        return $this->status === TicketStatus::PAGO;
    }

    /**
     * Verifica se o ticket pode ser pago (não está cancelado nem finalizado).
     */
    public function canPay(): bool
    {
        return in_array($this->status, TicketStatus::canPay(), true);
    }

    /**
     * Verifica se está dentro da tolerância de saída após pagar.
     * Ex: 5 minutos.
     */
    public function hasExitTolerance(int $toleranceMinutes = 5): bool
    {
        if (!$this->isPaid() || !$this->paid_at) {
            return false;
        }

        // Se tempo atual for menor que (Hora Pagamento + 5min)
        return Carbon::now()->lessThanOrEqualTo(
            $this->paid_at->copy()->addMinutes($toleranceMinutes)
        );
    }
}
