<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Project;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Document::with(['uploadedBy'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $documents = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $documents]);
        }

        return view('documents.index', compact('documents'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $projects = Project::all();
        return view('documents.create', compact('customers', 'projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'file' => ['required', 'file', 'max:10240'], // 10MB
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $doc = Document::create([
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by_id' => auth()->id(),
        ]);

        AuditLogger::log('create', 'documents', $doc->id);

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully!');
    }

    public function destroy(Document $document): RedirectResponse
    {
        AuditLogger::log('delete', 'documents', $document->id);
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted!');
    }
}
