<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use App\Models\Vendor;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Expense::with(['project', 'vendor'])->latest('expense_date');

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

        $expenses = $query->paginate(15)->withQueryString();
        $totalExpenses = Expense::sum('amount');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $expenses]);
        }

        return view('expenses.index', compact('expenses', 'totalExpenses'));
    }

    public function create(): View
    {
        $projects = Project::where('status', '!=', 'Completed')->get();
        $vendors = Vendor::where('status', 'active')->get();
        return view('expenses.create', compact('projects', 'vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['expense_number'] = Expense::generateNumber();
        $validated['status'] = 'Approved';
        $validated['approved_by_id'] = auth()->id();

        $expense = Expense::create($validated);
        AuditLogger::log('create', 'expenses', $expense->id);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['project', 'vendor', 'employee']);
        return view('expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        AuditLogger::log('delete', 'expenses', $expense->id);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense record removed!');
    }
}
