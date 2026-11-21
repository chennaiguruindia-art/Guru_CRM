<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code', 'user_id', 'branch_id', 'name', 'phone', 'email', 'designation',
        'department', 'joining_date', 'employee_type', 'salary', 'status', 'address',
        'dob', 'blood_group', 'emergency_contact',
        'father_name', 'mother_name', 'father_phone', 'mother_phone',
        'father_occupation', 'mother_occupation', 'parent_address',
        'emergency_contact_relation', 'marital_status', 'spouse_name',
        'bank_name', 'bank_account_number', 'bank_ifsc', 'pan_number', 'uan_number'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'dob' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public static function generateCode(): string
    {
        // withTrashed(), matching Branch::generateCode(): employee_code is
        // unique and soft-deleted rows keep theirs, so counting only live rows
        // would hand back a code that is already taken.
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;

        return 'EMP-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
