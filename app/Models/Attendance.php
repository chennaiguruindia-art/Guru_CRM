<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'check_in',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_address',
        'check_in_image',
        'check_out',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_address',
        'check_out_image',
        'worked_hours',
        'has_permission',
        'permission_reason',
        'is_auto_checkout',
        'auto_checkout_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'worked_hours' => 'float',
        'has_permission' => 'boolean',
        'is_auto_checkout' => 'boolean',
        'auto_checkout_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function checkInMapUrl(): ?string
    {
        if ($this->check_in_latitude && $this->check_in_longitude) {
            return "https://www.google.com/maps?q={$this->check_in_latitude},{$this->check_in_longitude}";
        }
        return null;
    }

    public function checkOutMapUrl(): ?string
    {
        if ($this->check_out_latitude && $this->check_out_longitude) {
            return "https://www.google.com/maps?q={$this->check_out_latitude},{$this->check_out_longitude}";
        }
        return null;
    }

    public function checkInImageUrl(): ?string
    {
        if ($this->check_in_image) {
            return Storage::disk('public')->url($this->check_in_image);
        }
        return null;
    }

    public function checkOutImageUrl(): ?string
    {
        if ($this->check_out_image) {
            return Storage::disk('public')->url($this->check_out_image);
        }
        return null;
    }
}
