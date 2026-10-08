<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number', 'vendor_id', 'po_date', 'delivery_date',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'status', 'notes', 'created_by_id'
    ];

    protected $casts = [
        'po_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public static function generateNumber(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'PO-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
