<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $query = Branch::withCount('employees')->latest('id');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $branches = $query->paginate(15)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.form', ['branch' => new Branch()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBranch($request);

        $validated['code'] = Branch::generateCode();

        $branch = Branch::create($validated);
        AuditLogger::log('create', 'branches', $branch->id, null, ['name' => $branch->name]);

        return redirect()->route('branches.index')
            ->with('success', "Branch {$branch->code} created successfully!");
    }

    public function edit(Branch $branch): View
    {
        return view('branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $this->validateBranch($request);

        $branch->update($validated);
        AuditLogger::log('update', 'branches', $branch->id, null, ['name' => $branch->name]);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully!');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        AuditLogger::log('delete', 'branches', $branch->id, null, ['name' => $branch->name]);
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }

    /**
     * The code is generated, never taken from the request, so it stays unique
     * and immutable even if the form is tampered with.
     */
    private function validateBranch(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
