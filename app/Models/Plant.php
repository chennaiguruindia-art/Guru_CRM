<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plant_code',
        'name',
        'botanical_name',
        'common_name',
        'category_id',
        'plant_type',
        'sunlight_requirement',
        'water_requirement',
        'height',
        'plant_size',
        'pot_size',
        'unit',
        'purchase_price',
        'selling_price',
        'tax_percent',
        'reorder_level',
        'status',
        'description',
        'image_path',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlantCategory::class, 'category_id');
    }

    public static function generateCode(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'PLANT-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
