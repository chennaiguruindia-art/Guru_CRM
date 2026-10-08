<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'ticket_number','customer_id','project_id','subject','description',
        'priority','assigned_to_id','status','resolution_notes','resolved_at','created_by_id',
    ];
    protected $casts = ['resolved_at'=>'datetime'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class,'assigned_to_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by_id'); }

    public static function generateNumber(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'TKT-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
