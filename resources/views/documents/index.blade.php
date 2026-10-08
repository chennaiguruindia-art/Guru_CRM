@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-file-earmark-text me-2 text-success"></i>Documents
            </h1>
            <p class="text-muted mb-0 small">Manage uploaded project and client documents</p>
        </div>
        <a href="{{ route('documents.create') }}" class="btn btn-success">
            <i class="bi bi-cloud-upload me-1"></i> Upload Document
        </a>
    </div>

    {{-- Search & Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('documents.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="search" class="form-label small text-muted mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="search"
                               name="search"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search by title or uploader..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label for="category" class="form-label small text-muted mb-1">Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(['Contract','Quotation','Invoice','Site Plan','Photo','Legal','Other'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
                @if(request()->hasAny(['search','category']))
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Documents Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 fw-semibold text-muted small text-uppercase">Title</th>
                                <th class="py-3 fw-semibold text-muted small text-uppercase">Category</th>
                                <th class="py-3 fw-semibold text-muted small text-uppercase">File Type</th>
                                <th class="py-3 fw-semibold text-muted small text-uppercase">File Size</th>
                                <th class="py-3 fw-semibold text-muted small text-uppercase">Uploaded By</th>
                                <th class="py-3 fw-semibold text-muted small text-uppercase">Date</th>
                                <th class="pe-4 py-3 fw-semibold text-muted small text-uppercase text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr>
                                    {{-- Title --}}
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $ext = strtolower(pathinfo($document->file_path ?? '', PATHINFO_EXTENSION));
                                                $iconClass = match($ext) {
                                                    'pdf'           => 'bi-file-earmark-pdf text-danger',
                                                    'doc','docx'    => 'bi-file-earmark-word text-primary',
                                                    'xls','xlsx'    => 'bi-file-earmark-excel text-success',
                                                    'jpg','jpeg','png','gif','webp' => 'bi-file-earmark-image text-warning',
                                                    'zip','rar'     => 'bi-file-earmark-zip text-secondary',
                                                    default         => 'bi-file-earmark text-muted',
                                                };
                                            @endphp
                                            <i class="bi {{ $iconClass }} fs-5"></i>
                                            <span class="fw-medium text-dark">{{ $document->title }}</span>
                                        </div>
                                    </td>

                                    {{-- Category Badge --}}
                                    <td class="py-3">
                                        @php
                                            $catColor = match($document->category ?? '') {
                                                'Contract'   => 'primary',
                                                'Quotation'  => 'info',
                                                'Invoice'    => 'warning',
                                                'Site Plan'  => 'success',
                                                'Photo'      => 'pink',
                                                'Legal'      => 'danger',
                                                default      => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $catColor === 'pink' ? 'secondary' : $catColor }} bg-opacity-10 text-{{ $catColor === 'pink' ? 'secondary' : $catColor }} border border-{{ $catColor === 'pink' ? 'secondary' : $catColor }} border-opacity-25 rounded-pill px-2">
                                            {{ $document->category ?? 'Other' }}
                                        </span>
                                    </td>

                                    {{-- File Type Badge --}}
                                    <td class="py-3">
                                        <span class="badge bg-light text-dark border text-uppercase small">
                                            {{ strtoupper($ext ?: 'N/A') }}
                                        </span>
                                    </td>

                                    {{-- File Size --}}
                                    <td class="py-3 text-muted small">
                                        @php
                                            $bytes = $document->file_size ?? 0;
                                            if ($bytes >= 1048576) {
                                                $size = number_format($bytes / 1048576, 2) . ' MB';
                                            } elseif ($bytes >= 1024) {
                                                $size = number_format($bytes / 1024, 1) . ' KB';
                                            } else {
                                                $size = $bytes . ' B';
                                            }
                                        @endphp
                                        {{ $size }}
                                    </td>

                                    {{-- Uploaded By --}}
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width:30px;height:30px;">
                                                <i class="bi bi-person-fill text-success small"></i>
                                            </div>
                                            <span class="small text-dark">{{ $document->uploadedBy->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="py-3 text-muted small">
                                        {{ $document->created_at ? $document->created_at->format('d M Y') : 'N/A' }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="pe-4 py-3 text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            {{-- Download --}}
                                            <a href="{{ asset('storage/' . $document->file_path) }}"
                                               download
                                               class="btn btn-sm btn-outline-success"
                                               title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>

                                            {{-- Delete --}}
                                            <form method="POST"
                                                  action="{{ route('documents.destroy', $document) }}"
                                                  onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($documents->hasPages())
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
                        <p class="text-muted small mb-0">
                            Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }}
                            of {{ $documents->total() }} documents
                        </p>
                        <div>
                            {{ $documents->withQueryString()->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif

            @else
                {{-- Empty State --}}
                <div class="text-center py-5 my-3">
                    <i class="bi bi-folder2-open display-1 text-muted opacity-25"></i>
                    <h5 class="mt-3 text-muted fw-semibold">No Documents Found</h5>
                    <p class="text-muted small mb-4">
                        @if(request()->hasAny(['search','category']))
                            No documents match your current filters. Try adjusting your search.
                        @else
                            No documents have been uploaded yet. Get started by uploading your first document.
                        @endif
                    </p>
                    @if(request()->hasAny(['search','category']))
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </a>
                    @endif
                    <a href="{{ route('documents.create') }}" class="btn btn-success">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Document
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
