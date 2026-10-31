<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends BaseApiController
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with(['project', 'vendor', 'employee'])->latest('expense_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        if ($request->boolean('my_expenses')) {
            $query->where('employee_id', $request->user()->id);
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn ($item) => $this->formatExpense($item));

        return $this->paginatedResponse($paginator, 'Expenses retrieved successfully');
    }

    /**
     * Store a newly created expense with optional receipt photo.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'description' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $validated['expense_number'] = Expense::generateNumber();
        $validated['employee_id'] = $request->user()->id;
        $validated['status'] = 'Pending';

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $expense = Expense::create($validated);

        AuditLogger::log('create', 'expenses', $expense->id, null, $expense->toArray());

        return $this->successResponse(
            $this->formatExpense($expense->fresh(['project', 'vendor', 'employee'])),
            'Expense recorded successfully',
            201
        );
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense): JsonResponse
    {
        $expense->load(['project', 'vendor', 'employee']);

        return $this->successResponse($this->formatExpense($expense), 'Expense retrieved successfully');
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['sometimes', 'required', 'string'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'expense_date' => ['sometimes', 'required', 'date'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'description' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $old = $expense->toArray();
        $expense->update($validated);

        AuditLogger::log('update', 'expenses', $expense->id, $old, $expense->fresh()->toArray());

        return $this->successResponse($this->formatExpense($expense->fresh(['project', 'vendor', 'employee'])), 'Expense updated');
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $id = $expense->id;
        $expense->delete();

        AuditLogger::log('delete', 'expenses', $id);

        return $this->successResponse(null, 'Expense deleted successfully');
    }

    private function formatExpense(Expense $expense): array
    {
        $data = $expense->toArray();
        $data['receipt_url'] = $expense->receipt_path ? asset('storage/' . $expense->receipt_path) : null;
        return $data;
    }
}
