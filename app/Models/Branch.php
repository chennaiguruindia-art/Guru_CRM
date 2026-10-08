<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'location', 'phone', 'email', 'status', 'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Human-friendly, collision-free branch code, e.g. BR-0001.
     */
    public static function generateCode(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;

        return 'BR-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
