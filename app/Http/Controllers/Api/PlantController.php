<?php

namespace App\Http\Controllers\Api;

use App\Models\Plant;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Plant::with('category')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('botanical_name', 'like', "%{$search}%")
                  ->orWhere('plant_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('plant_type')) {
            $query->where('plant_type', $request->input('plant_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn ($plant) => $this->formatPlant($plant));

        return $this->paginatedResponse($paginator, 'Plants retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:plant_categories,id'],
            'plant_type' => ['nullable', 'string'],
            'sunlight_requirement' => ['nullable', 'string'],
            'water_requirement' => ['nullable', 'string'],
            'height' => ['nullable', 'string'],
            'pot_size' => ['nullable', 'string'],
            'plant_size' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $validated['plant_code'] = Plant::generateCode();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('plants', 'public');
        }

        $plant = Plant::create($validated);

        AuditLogger::log('create', 'plants', $plant->id, null, $plant->toArray());

        return $this->successResponse(
            $this->formatPlant($plant->fresh('category')),
            'Plant created successfully',
            201
        );
    }

    public function show(Plant $plant): JsonResponse
    {
        $plant->load('category');

        return $this->successResponse($this->formatPlant($plant), 'Plant retrieved successfully');
    }

    public function update(Request $request, Plant $plant): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:plant_categories,id'],
            'plant_type' => ['nullable', 'string'],
            'sunlight_requirement' => ['nullable', 'string'],
            'water_requirement' => ['nullable', 'string'],
            'height' => ['nullable', 'string'],
            'pot_size' => ['nullable', 'string'],
            'plant_size' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($plant->image_path) {
                Storage::disk('public')->delete($plant->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('plants', 'public');
        }

        $old = $plant->toArray();
        $plant->update($validated);

        AuditLogger::log('update', 'plants', $plant->id, $old, $plant->fresh()->toArray());

        return $this->successResponse($this->formatPlant($plant->fresh('category')), 'Plant updated successfully');
    }

    public function destroy(Plant $plant): JsonResponse
    {
        $id = $plant->id;
        $plant->delete();

        AuditLogger::log('delete', 'plants', $id);

        return $this->successResponse(null, 'Plant deleted successfully');
    }

    private function formatPlant(Plant $plant): array
    {
        $data = $plant->toArray();
        $data['image_url'] = $plant->image_path ? asset('storage/' . $plant->image_path) : null;
        return $data;
    }
}
