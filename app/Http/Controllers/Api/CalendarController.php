<?php

namespace App\Http\Controllers\Api;

use App\Models\Lead;
use App\Models\MaintenanceSchedule;
use App\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(15)->toDateString());
        $endDate = $request->input('end_date', now()->addDays(45)->toDateString());

        $tasks = ProjectTask::with('project')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get();

        $schedules = MaintenanceSchedule::with(['contract.customer'])
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->get();

        $followUps = Lead::whereNotNull('follow_up_date')
            ->whereBetween('follow_up_date', [$startDate, $endDate])
            ->get();

        $events = [];

        foreach ($tasks as $t) {
            $events[] = [
                'id' => 'task-' . $t->id,
                'title' => 'Task Due: ' . $t->title,
                'date' => $t->due_date->format('Y-m-d'),
                'type' => 'task',
                'color' => '#0ea5e9',
                'meta' => [
                    'task_id' => $t->id,
                    'status' => $t->status,
                    'project' => $t->project?->name,
                ],
            ];
        }

        foreach ($schedules as $s) {
            $events[] = [
                'id' => 'amc-' . $s->id,
                'title' => 'AMC: ' . ($s->contract?->amc_number ?? 'Maintenance'),
                'date' => $s->scheduled_date->format('Y-m-d'),
                'type' => 'amc',
                'color' => '#f59e0b',
                'meta' => [
                    'schedule_id' => $s->id,
                    'customer' => $s->contract?->customer?->name,
                ],
            ];
        }

        foreach ($followUps as $f) {
            $events[] = [
                'id' => 'lead-' . $f->id,
                'title' => 'Visit Follow-up: ' . $f->name,
                'date' => $f->follow_up_date->format('Y-m-d'),
                'type' => 'lead',
                'color' => '#fb923c',
                'meta' => [
                    'lead_id' => $f->id,
                    'status' => $f->status,
                    'phone' => $f->phone,
                ],
            ];
        }

        usort($events, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $this->successResponse($events, 'Calendar events retrieved');
    }
}
