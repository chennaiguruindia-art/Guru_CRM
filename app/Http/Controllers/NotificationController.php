<?php

namespace App\Http\Controllers;

use App\Services\TodayService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Real, permission-agnostic "needs attention" feed for the navbar bell.
     * Fetched on demand so it never slows down normal page loads.
     */
    public function __invoke(TodayService $today): JsonResponse
    {
        $all = $today->items(50);

        return response()->json([
            'success' => true,
            'count' => min(count($all), 99),
            'items' => array_slice($all, 0, 5),
        ]);
    }
}
