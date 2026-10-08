<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id','title','description','assigned_to_id',
        'priority','start_date','due_date','status',
    ];
    protected $casts = ['start_date'=>'date','due_date'=>'date'];
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class,'assigned_to_id'); }
}
