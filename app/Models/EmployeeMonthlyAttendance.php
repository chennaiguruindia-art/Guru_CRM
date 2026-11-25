<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMonthlyAttendance extends Model
{
    use HasFactory;

    protected $table = 'employee_monthly_attendances';

    protected $fillable = [
        'user_id',
        'user_name',
        'employee_id',
        'employee_code',
        'branch_id',
        'branch',
        'month',
        'total_present',
        'total_absent',
        'no_of_permission',
    ];

    protected $casts = [
        'total_present' => 'float',
        'total_absent' => 'float',
        'no_of_permission' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branchModel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Compute and sync all active employees' monthly attendance data into this table for a given month.
     */
    public static function syncForMonth(string $salaryMonth, ?int $branchId = null): void
    {
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $salaryMonth, 'Asia/Kolkata');
        } catch (\Throwable $e) {
            $monthDate = now('Asia/Kolkata');
            $salaryMonth = $monthDate->format('Y-m');
        }

        $startOfMonth = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();
        $totalDaysInMonth = $monthDate->daysInMonth;

        $employeeQuery = Employee::with(['user', 'branch'])->where('status', 'active');
        if ($branchId) {
            $employeeQuery->where('branch_id', $branchId);
        }
        $employees = $employeeQuery->get();

        foreach ($employees as $employee) {
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->get()
                ->keyBy(fn ($a) => Carbon::parse($a->date)->toDateString());

            $workedDays = 0.0;
            $paidSundays = 0;
            $permissions = 0;

            for ($day = 1; $day <= $totalDaysInMonth; $day++) {
                $currentDayDate = $monthDate->copy()->day($day)->startOfDay();
                $dateKey = $currentDayDate->toDateString();

                if ($currentDayDate->isSunday()) {
                    // All Sundays are automatic paid holidays (treated as Present)
                    $paidSundays++;
                    continue;
                }

                $att = $attendances->get($dateKey);
                if ($att) {
                    if ($att->has_permission) {
                        $permissions++;
                    }
                    if ($att->status === 'Present') {
                        $workedDays += 1.0;
                    } elseif ($att->status === 'Half-Day') {
                        $workedDays += 0.5;
                    } elseif ($att->status === 'Leave') {
                        $workedDays += 1.0;
                    }
                }
            }

            // Total Present = actual punches + all Sundays
            $totalPresent = (float) ($workedDays + $paidSundays);
            // Total Absent = Calendar days - Total Present
            $totalAbsent = max(0, (float) ($totalDaysInMonth - $totalPresent));

            $userName = $employee->user?->name ?: $employee->name;
            $branchName = $employee->branch?->name ?: '—';

            static::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $salaryMonth,
                ],
                [
                    'user_id' => $employee->user_id,
                    'user_name' => $userName,
                    'employee_code' => $employee->employee_code,
                    'branch_id' => $employee->branch_id,
                    'branch' => $branchName,
                    'total_present' => $totalPresent,
                    'total_absent' => $totalAbsent,
                    'no_of_permission' => $permissions,
                ]
            );
        }
    }
}
