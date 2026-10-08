<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_code', 'name', 'contact_person', 'phone', 'email',
        'gst_number', 'pan', 'address', 'city', 'state', 'payment_terms',
        'status', 'notes'
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public static function generateCode(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'VND-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
