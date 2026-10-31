<?php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['uploadedBy', 'customer', 'project'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn ($doc) => $this->formatDocument($doc));

        return $this->paginatedResponse($paginator, 'Documents retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'file' => ['required', 'file', 'max:10240'], // 10MB
            'customer_id' => ['nullable', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $doc = Document::create([
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'customer_id' => $request->customer_id,
            'project_id' => $request->project_id,
            'uploaded_by_id' => $request->user()->id,
        ]);

        AuditLogger::log('create', 'documents', $doc->id, null, $doc->toArray());

        return $this->successResponse(
            $this->formatDocument($doc->fresh(['uploadedBy', 'customer', 'project'])),
            'Document uploaded successfully',
            201
        );
    }

    public function show(Document $document): JsonResponse
    {
        $document->load(['uploadedBy', 'customer', 'project']);

        return $this->successResponse($this->formatDocument($document), 'Document retrieved');
    }

    public function destroy(Document $document): JsonResponse
    {
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $id = $document->id;
        $document->delete();

        AuditLogger::log('delete', 'documents', $id);

        return $this->successResponse(null, 'Document deleted');
    }

    private function formatDocument(Document $doc): array
    {
        $data = $doc->toArray();
        $data['file_url'] = $doc->file_path ? asset('storage/' . $doc->file_path) : null;
        return $data;
    }
}
