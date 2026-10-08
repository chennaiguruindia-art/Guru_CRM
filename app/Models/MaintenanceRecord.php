<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = [
        'schedule_id', 'customer_id', 'project_id', 'visit_date',
        'employee_id', 'activity_type', 'area_details', 'work_description',
        'before_photo', 'after_photo', 'customer_approval', 'remarks'
    ];

    protected $casts = [
        'visit_date' => 'date',
        'customer_approval' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
