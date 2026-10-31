<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends BaseApiController
{
    public function index(Request $request): JsonResponse
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

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Branches retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['code'] = Branch::generateCode();

        $branch = Branch::create($validated);
        AuditLogger::log('create', 'branches', $branch->id, null, ['name' => $branch->name]);

        return $this->successResponse($branch, 'Branch created successfully', 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        $branch->loadCount('employees');
        return $this->successResponse($branch, 'Branch retrieved');
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ]);

        $old = $branch->toArray();
        $branch->update($validated);

        AuditLogger::log('update', 'branches', $branch->id, $old, $branch->fresh()->toArray());

        return $this->successResponse($branch, 'Branch updated successfully');
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $id = $branch->id;
        $branch->delete();

        AuditLogger::log('delete', 'branches', $id);

        return $this->successResponse(null, 'Branch deleted');
    }
}
