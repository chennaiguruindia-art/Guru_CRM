@extends('layouts.app')

@section('title', 'Log Complaint')

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
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-ticket-perforated me-2 text-danger"></i>Log Complaint
            </h1>
            <p class="text-muted mb-0 small">Submit a new client complaint or support ticket.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Complaints
            </a>
        </div>
    </div>

    {{-- Validation Error Summary --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-pencil-square me-2 text-muted"></i>Complaint Details
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('complaints.store') }}" method="POST" id="complaintForm">
                @csrf

                <div class="row g-4">

                    {{-- Client --}}
                    <div class="col-12 col-md-6">
                        <label for="customer_id" class="form-label fw-semibold">
                            Client <span class="text-danger">*</span>
                        </label>
                        <select name="customer_id"
                                id="customer_id"
                                class="form-select @error('customer_id') is-invalid @enderror"
                                required>
                            <option value="">— Select Client —</option>
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

                    {{-- Priority --}}
                    <div class="col-12 col-md-6">
                        <label for="priority" class="form-label fw-semibold">
                            Priority <span class="text-danger">*</span>
                        </label>
                        <select name="priority"
                                id="priority"
                                class="form-select @error('priority') is-invalid @enderror"
                                required>
                            <option value="">— Select Priority —</option>
                            <option value="Critical" {{ old('priority') === 'Critical' ? 'selected' : '' }}>
                                🔴 Critical
                            </option>
                            <option value="High" {{ old('priority') === 'High' ? 'selected' : '' }}>
                                🟠 High
                            </option>
                            <option value="Medium" {{ old('priority') === 'Medium' ? 'selected' : '' }}>
                                🔵 Medium
                            </option>
                            <option value="Low" {{ old('priority') === 'Low' ? 'selected' : '' }}>
                                ⚪ Low
                            </option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Project --}}
                    <div class="col-12 col-md-6">
                        <label for="project_id" class="form-label fw-semibold">Project</label>
                        <select name="project_id"
                                id="project_id"
                                class="form-select @error('project_id') is-invalid @enderror">
                            <option value="">— Select Project (optional) —</option>
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

                    {{-- Subject --}}
                    <div class="col-12">
                        <label for="subject" class="form-label fw-semibold">
                            Subject <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="subject"
                               id="subject"
                               class="form-control @error('subject') is-invalid @enderror"
                               placeholder="Brief description of the complaint…"
                               value="{{ old('subject') }}"
                               required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Provide a detailed description of the complaint, including any relevant context…"
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Assigned To --}}
                    <div class="col-12 col-md-6">
                        <label for="assigned_to_id" class="form-label fw-semibold">Assign To</label>
                        <select name="assigned_to_id"
                                id="assigned_to_id"
                                class="form-select @error('assigned_to_id') is-invalid @enderror">
                            <option value="">— Unassigned —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('assigned_to_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- Form Actions --}}
                <hr class="my-4">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-ticket-perforated me-1"></i>Log Complaint
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

