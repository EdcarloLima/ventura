<?php

namespace App\Domain\Payment\Models;

use App\Domain\Parking\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'amount',
        'method',
        'gateway_transaction_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento: Um pagamento pertence a um ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
