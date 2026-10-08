<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    protected $fillable = ['work_order_id', 'item_description', 'quantity', 'unit', 'status'];
    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
}
