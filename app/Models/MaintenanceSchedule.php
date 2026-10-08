<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'contract_id', 'scheduled_date', 'status', 'assigned_employee_id', 'notes'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(MaintenanceContract::class, 'contract_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'schedule_id');
    }
}
