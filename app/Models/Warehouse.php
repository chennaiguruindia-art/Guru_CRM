<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['code', 'name', 'location', 'manager_name', 'phone', 'status'];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
