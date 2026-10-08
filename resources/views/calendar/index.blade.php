@extends('layouts.app')

@section('title', 'Calendar')

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
                <i class="bi bi-calendar3 me-2 text-success"></i>Calendar &amp; Schedule
            </h1>
            <p class="text-muted mb-0 small">View and manage all scheduled events, tasks and AMC schedules</p>
        </div>
    </div>

    {{-- Event Type Legend --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="text-muted small fw-semibold me-2">
                    <i class="bi bi-circle-fill me-1" style="font-size: .55rem;"></i>Event Types:
                </span>

                {{-- Task Due --}}
                <span class="d-inline-flex align-items-center gap-2 badge rounded-pill px-3 py-2 fw-normal"
                      style="background-color: rgba(14,165,233,.12); color: #075985; border: 1px solid rgba(14,165,233,.28);">
                    <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#0ea5e9;"></span>
                    Task Due
                </span>

                {{-- AMC Schedule --}}
                <span class="d-inline-flex align-items-center gap-2 badge rounded-pill px-3 py-2 fw-normal"
                      style="background-color: rgba(245,158,11,.14); color: #92400e; border: 1px solid rgba(245,158,11,.32);">
                    <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#f59e0b;"></span>
                    AMC Schedule
                </span>

                {{-- Visit Follow-up --}}
                <span class="d-inline-flex align-items-center gap-2 badge rounded-pill px-3 py-2 fw-normal"
                      style="background-color: rgba(251,146,60,.14); color: #9a3412; border: 1px solid rgba(251,146,60,.3);">
                    <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#fb923c;"></span>
                    Visit Follow-up
                </span>
            </div>
        </div>
    </div>

    {{-- Full Calendar Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <div id="calendar" style="min-height: 650px;"></div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
{{-- FullCalendar v6 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        var events = @json($events);

        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {

            initialView: 'dayGridMonth',

            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,listWeek'
            },

            events: events,

            eventDisplay: 'block',

            height: 650,

            // Style overrides for a clean look
            dayMaxEvents: 3,

            eventDidMount: function (info) {
                // Tooltip with full title
                info.el.setAttribute('title', info.event.title);
                info.el.style.cursor = 'pointer';
            },

            // Responsive button text
            buttonText: {
                today:    'Today',
                month:    'Month',
                week:     'Week',
                listWeek: 'Agenda'
            }
        });

        calendar.render();
    });
</script>

<style>
    /* FullCalendar style overrides */
    .fc {
        font-family: inherit;
    }
    .fc .fc-button-primary {
        background-color: #16a34a;
        border-color: #16a34a;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: none !important;
    }
    .fc .fc-button-primary:hover {
        background-color: #15803d;
        border-color: #15803d;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #166534;
        border-color: #166534;
    }
    .fc .fc-button-primary:focus {
        box-shadow: 0 0 0 0.2rem rgba(22, 163, 74, 0.28);
    }
    .fc-day-today {
        background-color: rgba(22, 163, 74, 0.06) !important;
    }
    .fc .fc-daygrid-day-number,
    .fc .fc-col-header-cell-cushion {
        color: #111827;
        text-decoration: none;
    }
    .fc .fc-event {
        border-radius: 6px;
        border: none;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 1px 5px;
    }
    .fc .fc-toolbar-title {
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    @media (max-width: 576px) {
        .fc .fc-toolbar {
            flex-direction: column;
            gap: .5rem;
        }
        .fc .fc-toolbar-title {
            font-size: 1rem;
        }
    }
</style>
@endsection
