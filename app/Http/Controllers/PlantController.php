<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantCategory;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlantController extends Controller
{
    public function index(Request $request): View|JsonResponse
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

        $plants = $query->paginate(15)->withQueryString();
        $categories = PlantCategory::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $plants,
            ]);
        }

        return view('plants.index', compact('plants', 'categories'));
    }

    public function create(): View
    {
        $categories = PlantCategory::orderBy('name')->get();
        return view('plants.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:plant_categories,id'],
            'plant_type' => ['required', 'string'],
            'sunlight_requirement' => ['nullable', 'string'],
            'water_requirement' => ['nullable', 'string'],
            'height' => ['nullable', 'string'],
            'plant_size' => ['nullable', 'string'],
            'pot_size' => ['nullable', 'string'],
            'unit' => ['required', 'string'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['plant_code'] = Plant::generateCode();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('plants', 'public');
        }

        $plant = Plant::create($validated);

        AuditLogger::log('create', 'plants', $plant->id, null, $plant->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Plant added to master catalog',
                'data' => $plant,
            ], 201);
        }

        return redirect()->route('plants.index')->with('success', 'Plant added to master catalog successfully!');
    }

    public function show(Plant $plant): View
    {
        $plant->load('category');
        return view('plants.show', compact('plant'));
    }

    public function edit(Plant $plant): View
    {
        $categories = PlantCategory::orderBy('name')->get();
        return view('plants.edit', compact('plant', 'categories'));
    }

    public function update(Request $request, Plant $plant): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:plant_categories,id'],
            'plant_type' => ['required', 'string'],
            'sunlight_requirement' => ['nullable', 'string'],
            'water_requirement' => ['nullable', 'string'],
            'height' => ['nullable', 'string'],
            'plant_size' => ['nullable', 'string'],
            'pot_size' => ['nullable', 'string'],
            'unit' => ['required', 'string'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('plants', 'public');
        }

        $plant->update($validated);

        AuditLogger::log('update', 'plants', $plant->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Plant updated successfully',
                'data' => $plant,
            ]);
        }

        return redirect()->route('plants.index')->with('success', 'Plant catalog updated successfully!');
    }

    public function destroy(Request $request, Plant $plant): RedirectResponse|JsonResponse
    {
        $id = $plant->id;
        $plant->delete();

        AuditLogger::log('delete', 'plants', $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Plant deleted successfully',
            ]);
        }

        return redirect()->route('plants.index')->with('success', 'Plant removed from catalog.');
    }
}
