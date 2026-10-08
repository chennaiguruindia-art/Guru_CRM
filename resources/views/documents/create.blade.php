@extends('layouts.app')

@section('title', 'Upload Document')

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
                <i class="bi bi-cloud-upload me-2 text-success"></i>Upload Document
            </h1>
            <p class="text-muted mb-0 small">Add a new document to the system</p>
        </div>
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Documents
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="bi bi-file-earmark-plus me-2 text-success"></i>Document Details
                    </h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST"
                          action="{{ route('documents.store') }}"
                          enctype="multipart/form-data"
                          novalidate>
                        @csrf

                        {{-- Validation Errors Summary --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                                    <div>
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-1 ps-3">
                                            @foreach($errors->all() as $error)
                                                <li class="small">{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row g-4">

                            {{-- Title --}}
                            <div class="col-12">
                                <label for="title" class="form-label fw-semibold">
                                    Document Title <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="title"
                                       name="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}"
                                       placeholder="e.g. Site Plan - Block A, Q3 Invoice..."
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div class="col-12 col-sm-6">
                                <label for="category" class="form-label fw-semibold">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select id="category"
                                        name="category"
                                        class="form-select @error('category') is-invalid @enderror"
                                        required>
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                                    @foreach(['Contract','Quotation','Invoice','Site Plan','Photo','Legal','Other'] as $cat)
                                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Client --}}
                            <div class="col-12 col-sm-6">
                                <label for="customer_id" class="form-label fw-semibold">
                                    Related Client
                                </label>
                                <select id="customer_id"
                                        name="customer_id"
                                        class="form-select @error('customer_id') is-invalid @enderror">
                                    <option value="">— None —</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Project --}}
                            <div class="col-12 col-sm-6">
                                <label for="project_id" class="form-label fw-semibold">
                                    Related Project
                                </label>
                                <select id="project_id"
                                        name="project_id"
                                        class="form-select @error('project_id') is-invalid @enderror">
                                    <option value="">— None —</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}"
                                            {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- File Upload --}}
                            <div class="col-12">
                                <label for="file" class="form-label fw-semibold">
                                    File <span class="text-danger">*</span>
                                </label>

                                {{-- Drop Zone --}}
                                <div id="drop-zone"
                                     class="border border-2 border-dashed rounded-3 p-4 text-center position-relative @error('file') border-danger @enderror"
                                     style="border-color: #dee2e6 !important; cursor: pointer; transition: all .2s ease;">
                                    <input type="file"
                                           id="file"
                                           name="file"
                                           class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                           style="cursor: pointer;"
                                           required>
                                    <div id="drop-zone-content">
                                        <i class="bi bi-cloud-arrow-up display-5 text-muted mb-2"></i>
                                        <p class="mb-1 fw-semibold text-dark">Click to browse or drag &amp; drop</p>
                                        <p class="small text-muted mb-0">All file types accepted &mdash; Max size: <strong>10 MB</strong></p>
                                    </div>
                                    <div id="file-selected-info" class="d-none">
                                        <i class="bi bi-file-earmark-check display-5 text-success mb-2"></i>
                                        <p class="mb-1 fw-semibold text-dark" id="file-name-display">filename.pdf</p>
                                        <p class="small text-muted mb-0" id="file-size-display">0 KB</p>
                                    </div>
                                </div>

                                @error('file')
                                    <div class="text-danger small mt-1">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror

                                <p class="form-text text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Accepted: PDF, Word, Excel, images, ZIP and more. Maximum file size is <strong>10 MB</strong>.
                                </p>
                            </div>

                        </div>{{-- /row --}}

                        {{-- Form Actions --}}
                        <hr class="my-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-cloud-upload me-1"></i> Upload Document
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    (function () {
        const fileInput   = document.getElementById('file');
        const dropZone    = document.getElementById('drop-zone');
        const defaultView = document.getElementById('drop-zone-content');
        const selectedView = document.getElementById('file-selected-info');
        const nameDisplay  = document.getElementById('file-name-display');
        const sizeDisplay  = document.getElementById('file-size-display');

        function formatBytes(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024)    return (bytes / 1024).toFixed(1) + ' KB';
            return bytes + ' B';
        }

        function showFileInfo(file) {
            nameDisplay.textContent = file.name;
            sizeDisplay.textContent = formatBytes(file.size);
            defaultView.classList.add('d-none');
            selectedView.classList.remove('d-none');
            dropZone.style.borderColor = '#16a34a';
        }

        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                showFileInfo(this.files[0]);
            }
        });

        // Drag over highlight
        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('bg-light');
        });
        dropZone.addEventListener('dragleave', function () {
            this.classList.remove('bg-light');
        });
        dropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('bg-light');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                // Assign to input
                const dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                fileInput.files = dt.files;
                showFileInfo(e.dataTransfer.files[0]);
            }
        });
    })();
</script>
@endsection
