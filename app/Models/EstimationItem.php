<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimationItem extends Model
{
    protected $fillable = [
        'estimation_id', 'item_type', 'item_name', 'quantity', 'unit', 'rate', 'amount', 'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class);
    }
}
