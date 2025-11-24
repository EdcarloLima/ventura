<?php

namespace App\Domain\Parking\Models;

use App\Domain\Payment\Models\Payment;
use App\Domain\Vehicle\Models\Vehicle;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
