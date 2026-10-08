<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceContract extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'amc_number','customer_id','start_date','end_date',
        'contract_value','billing_frequency','service_frequency',
        'assigned_team_lead_id','scope_of_work','status','renewal_date','notes',
    ];
    protected $casts = ['start_date'=>'date','end_date'=>'date','renewal_date'=>'date','contract_value'=>'decimal:2'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function teamLead(): BelongsTo { return $this->belongsTo(User::class,'assigned_team_lead_id'); }

    /**
     * AMC-2026-0001 — widened until it is free, since the column is unique
     * and rows can be soft-deleted after being counted.
     */
    public static function generateCode(): string
    {
        $year = date('Y');
        $next = ((int) static::withTrashed()->max('id')) + 1;

        do {
            $code = 'AMC-' . $year . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (static::withTrashed()->where('amc_number', $code)->exists());

        return $code;
    }
}
