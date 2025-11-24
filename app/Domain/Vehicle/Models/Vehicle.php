<?php

namespace App\Domain\Vehicle\Models;

use App\Domain\Parking\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate',
        'type',
        'brand',
        'model',
        'color',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento: Um veículo pode ter vários tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
