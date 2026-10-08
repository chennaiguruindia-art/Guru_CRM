<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number', 'project_id', 'employee_id', 'vendor_id',
        'category', 'amount', 'expense_date', 'description', 'receipt_path',
        'status', 'approved_by_id'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public static function generateNumber(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'EXP-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
