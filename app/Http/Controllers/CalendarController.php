<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\MaintenanceSchedule;
use App\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $tasks = ProjectTask::with('project')->whereNotNull('due_date')->get();
        $schedules = MaintenanceSchedule::with(['contract.customer'])->get();
        $followUps = Lead::whereNotNull('follow_up_date')->where('follow_up_date', '>=', now()->subDays(15))->get();

        $events = [];

        foreach ($tasks as $t) {
            $events[] = [
                'title' => 'Task Due: ' . $t->title,
                'date' => $t->due_date->format('Y-m-d'),
                'type' => 'task',
                'color' => '#0ea5e9',
            ];
        }

        foreach ($schedules as $s) {
            $events[] = [
                'title' => 'AMC: ' . ($s->contract?->amc_number ?? 'Maintenance'),
                'date' => $s->scheduled_date->format('Y-m-d'),
                'type' => 'amc',
                'color' => '#f59e0b',
            ];
        }

        foreach ($followUps as $f) {
            $events[] = [
                'title' => 'Visit Follow-up: ' . $f->name,
                'date' => $f->follow_up_date->format('Y-m-d'),
                'type' => 'lead',
                'color' => '#fb923c',
            ];
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'events' => $events]);
        }

        return view('calendar.index', compact('events'));
    }
}
