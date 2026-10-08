<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estimation extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimation_number', 'customer_id', 'title', 'date',
        'plants_total', 'materials_total', 'labour_total', 'transport_total',
        'other_total', 'subtotal', 'profit_margin_percent', 'profit_margin_amount',
        'discount_amount', 'tax_percent', 'tax_amount', 'grand_total',
        'status', 'notes', 'created_by_id'
    ];

    protected $casts = [
        'date' => 'date',
        'plants_total' => 'decimal:2',
        'materials_total' => 'decimal:2',
        'labour_total' => 'decimal:2',
        'transport_total' => 'decimal:2',
        'other_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'profit_margin_percent' => 'decimal:2',
        'profit_margin_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimationItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public static function generateNumber(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'EST-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
