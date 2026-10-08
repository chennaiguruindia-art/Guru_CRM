<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'estimation_id',
        'date',
        'valid_until',
        'sales_person_id',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'grand_total',
        'terms_conditions',
        'notes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = static::withTrashed()->where('quotation_number', 'like', "QT-{$year}-%")->latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'QT-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
