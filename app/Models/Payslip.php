<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_month',
        'month_name',
        'basic_salary',
        'hra',
        'conveyance_allowance',
        'special_allowance',
        'other_allowance',
        'gross_salary',
        'pf_deduction',
        'esi_deduction',
        'professional_tax',
        'lop_days',
        'lop_deduction',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'total_working_days',
        'paid_days',
        'payment_status',
        'payment_date',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'conveyance_allowance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'esi_deduction' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'lop_days' => 'decimal:1',
        'lop_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_days' => 'decimal:1',
        'payment_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Convert number to words in Indian Rupees format (e.g., Twenty Thousand Rupees Only)
     */
    public function getNetSalaryInWordsAttribute(): string
    {
        $amount = (int) round($this->net_salary);
        if ($amount <= 0) {
            return 'Zero Rupees Only';
        }

        return self::numberToIndianWords($amount) . ' Rupees Only';
    }

    public static function numberToIndianWords(int $num): string
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
            15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
        ];

        if ($num < 20) {
            return $ones[$num];
        }
        if ($num < 100) {
            return $tens[(int)($num / 10)] . ($num % 10 !== 0 ? ' ' . $ones[$num % 10] : '');
        }
        if ($num < 1000) {
            return $ones[(int)($num / 100)] . ' Hundred' . ($num % 100 !== 0 ? ' and ' . self::numberToIndianWords($num % 100) : '');
        }
        if ($num < 100000) {
            return self::numberToIndianWords((int)($num / 1000)) . ' Thousand' . ($num % 1000 !== 0 ? ' ' . self::numberToIndianWords($num % 1000) : '');
        }
        if ($num < 10000000) {
            return self::numberToIndianWords((int)($num / 100000)) . ' Lakh' . ($num % 100000 !== 0 ? ' ' . self::numberToIndianWords($num % 100000) : '');
        }

        return self::numberToIndianWords((int)($num / 10000000)) . ' Crore' . ($num % 10000000 !== 0 ? ' ' . self::numberToIndianWords($num % 10000000) : '');
    }
}
