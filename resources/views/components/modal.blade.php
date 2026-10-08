@props([
    'id',
    'title' => 'Modal Title',
    'size' => '', // modal-sm, modal-lg, modal-xl
    'centered' => true,
    'scrollable' => false,
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size }} {{ $centered ? 'modal-dialog-centered' : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }}">
        <div class="modal-content shadow border-0">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-semibold" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer border-top bg-light">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
