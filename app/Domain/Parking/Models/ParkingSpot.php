<?php

namespace App\Domain\Parking\Models;

use App\Domain\Vehicle\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSpot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'status',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento: Uma vaga pode ter vários tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'spot_id');
    }
}
